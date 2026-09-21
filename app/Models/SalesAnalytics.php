<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class SalesAnalytics {

    // =========================================================================
    // 1. EXECUTIVE FINANCIAL KPIS
    // =========================================================================

    /**
     * Compute financial KPIs across orders for a given time period or filters.
     */
    public static function getFinancialKPIs(array $filters = []): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? '30d';
            $dateClause = self::buildDateClause($period, 'placed_at', $filters);

            // Order status condition: exclude cancelled orders from revenue
            $sql = "
                SELECT
                    COUNT(*) AS total_orders,
                    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
                    SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END) AS gross_revenue,
                    SUM(CASE WHEN status != 'cancelled' THEN (subtotal - COALESCE(discount_amount, 0)) ELSE 0 END) AS net_revenue,
                    SUM(CASE WHEN status != 'cancelled' THEN COALESCE(discount_amount, 0) ELSE 0 END) AS total_discounts,
                    SUM(CASE WHEN status != 'cancelled' THEN COALESCE(tax_amount, 0) ELSE 0 END) AS total_tax,
                    SUM(CASE WHEN status != 'cancelled' THEN COALESCE(shipping_charge, 0) ELSE 0 END) AS total_shipping
                FROM orders
                WHERE $dateClause
            ";

            $res = $db->query($sql);
            $row = $res ? $res->fetch_assoc() : [];

            $totalOrders   = (int)($row['total_orders'] ?? 0);
            $paidOrders    = (int)($row['paid_orders'] ?? 0);
            $delivered     = (int)($row['delivered_orders'] ?? 0);
            $cancelled     = (int)($row['cancelled_orders'] ?? 0);
            $grossRevenue  = (float)($row['gross_revenue'] ?? 0);
            $netRevenue    = (float)($row['net_revenue'] ?? 0);
            $discounts     = (float)($row['total_discounts'] ?? 0);
            $tax           = (float)($row['total_tax'] ?? 0);
            $shipping      = (float)($row['total_shipping'] ?? 0);

            $validOrders = max(1, $totalOrders - $cancelled);
            $aov = $totalOrders > 0 ? round($grossRevenue / $validOrders) : 0;
            $paidRate = $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0.0;

            // Total units sold in this period
            $unitsSql = "
                SELECT COALESCE(SUM(oi.quantity), 0) AS total_units
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE o.status != 'cancelled' AND $dateClause
            ";
            $unitsRes = $db->query($unitsSql);
            $totalUnits = $unitsRes ? (int)$unitsRes->fetch_row()[0] : 0;
            $upt = $validOrders > 0 ? round($totalUnits / $validOrders, 1) : 0.0;

            // Today's revenue & orders
            $todayRow = $db->query("
                SELECT
                    COUNT(*) as cnt,
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) as rev
                FROM orders
                WHERE DATE(placed_at) = CURDATE()
            ")->fetch_assoc();

            // Yesterday's revenue & orders (for daily momentum comparison)
            $yestRow = $db->query("
                SELECT
                    COUNT(*) as cnt,
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) as rev
                FROM orders
                WHERE DATE(placed_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
            ")->fetch_assoc();

            $todayRev = (float)($todayRow['rev'] ?? 0);
            $yestRev  = (float)($yestRow['rev'] ?? 0);
            $revGrowth = $yestRev > 0 ? round((($todayRev - $yestRev) / $yestRev) * 100, 1) : 0;

            return [
                'gross_revenue'   => (int)round($grossRevenue),
                'net_revenue'     => (int)round($netRevenue),
                'total_orders'    => $totalOrders,
                'paid_orders'     => $paidOrders,
                'paid_rate'       => $paidRate,
                'delivered_orders'=> $delivered,
                'cancelled_orders'=> $cancelled,
                'total_discounts' => (int)round($discounts),
                'total_tax'       => (int)round($tax),
                'total_shipping'  => (int)round($shipping),
                'avg_order_value' => (int)$aov,
                'units_sold'      => $totalUnits,
                'units_per_order' => $upt,
                'today_revenue'   => (int)round($todayRev),
                'today_orders'    => (int)($todayRow['cnt'] ?? 0),
                'yesterday_revenue'=> (int)round($yestRev),
                'yesterday_orders'=> (int)($yestRow['cnt'] ?? 0),
                'revenue_growth'  => $revGrowth,
            ];
        } catch (Exception $e) {
            error_log("SalesAnalytics::getFinancialKPIs error: " . $e->getMessage());
            return [
                'gross_revenue'   => 0, 'net_revenue' => 0, 'total_orders' => 0,
                'paid_orders'     => 0, 'paid_rate' => 0.0, 'delivered_orders' => 0,
                'cancelled_orders'=> 0, 'total_discounts' => 0, 'total_tax' => 0,
                'total_shipping'  => 0, 'avg_order_value' => 0, 'units_sold' => 0,
                'units_per_order' => 0.0, 'today_revenue' => 0, 'today_orders' => 0,
                'yesterday_revenue'=> 0, 'yesterday_orders' => 0, 'revenue_growth' => 0,
            ];
        }
    }

    // =========================================================================
    // 2. SALES TRAJECTORY TREND (Daily / Weekly / Monthly SVG Data)
    // =========================================================================

    /**
     * Retrieve continuous sales trajectory points for interactive SVG spline charts.
     */
    public static function getSalesTrend(string $interval = 'daily', int $points = 14, array $filters = []): array {
        try {
            $db = Database::connect();

            if ($interval === 'monthly') {
                $points = max(3, min($points, 24));
                $sql = "
                    SELECT
                        DATE_FORMAT(placed_at, '%Y-%m') AS period_key,
                        DATE_FORMAT(placed_at, '%b %Y') AS formatted_label,
                        COUNT(*) AS orders_count,
                        COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) AS revenue,
                        COALESCE(SUM(CASE WHEN status != 'cancelled' THEN (subtotal - COALESCE(discount_amount, 0)) ELSE 0 END), 0) AS net_revenue
                    FROM orders
                    WHERE placed_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                    GROUP BY DATE_FORMAT(placed_at, '%Y-%m'), DATE_FORMAT(placed_at, '%b %Y')
                    ORDER BY period_key ASC
                ";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $points);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                $mapped = [];
                foreach ($rows as $r) {
                    $mapped[$r['period_key']] = [
                        'label'   => $r['formatted_label'],
                        'orders'  => (int)$r['orders_count'],
                        'revenue' => (int)round((float)$r['revenue']),
                        'net'     => (int)round((float)$r['net_revenue']),
                    ];
                }

                $timeline = [];
                for ($i = $points - 1; $i >= 0; $i--) {
                    $mKey = date('Y-m', strtotime("-$i months"));
                    $mLabel = date('M Y', strtotime("-$i months"));
                    $entry = $mapped[$mKey] ?? ['label' => $mLabel, 'orders' => 0, 'revenue' => 0, 'net' => 0];

                    $timeline[] = [
                        'key'       => $mKey,
                        'label'     => $mLabel,
                        'revenue'   => $entry['revenue'],
                        'orders'    => $entry['orders'],
                        'aov'       => $entry['orders'] > 0 ? (int)round($entry['revenue'] / $entry['orders']) : 0,
                    ];
                }
                return $timeline;

            } elseif ($interval === 'weekly') {
                $points = max(4, min($points, 26));
                $sql = "
                    SELECT
                        YEARWEEK(placed_at, 1) AS period_key,
                        MIN(DATE(placed_at)) AS week_start,
                        COUNT(*) AS orders_count,
                        COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) AS revenue
                    FROM orders
                    WHERE placed_at >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
                    GROUP BY YEARWEEK(placed_at, 1)
                    ORDER BY period_key ASC
                ";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $points);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                $mapped = [];
                foreach ($rows as $r) {
                    $mapped[$r['period_key']] = [
                        'orders'  => (int)$r['orders_count'],
                        'revenue' => (int)round((float)$r['revenue']),
                        'date'    => $r['week_start'],
                    ];
                }

                $timeline = [];
                for ($i = $points - 1; $i >= 0; $i--) {
                    $time = strtotime("-$i weeks");
                    $wKey = (int)date('oW', $time);
                    $wLabel = 'Wk ' . date('W', $time) . ' (' . date('M j', $time) . ')';
                    $entry = $mapped[$wKey] ?? ['orders' => 0, 'revenue' => 0];

                    $timeline[] = [
                        'key'     => (string)$wKey,
                        'label'   => $wLabel,
                        'revenue' => $entry['revenue'],
                        'orders'  => $entry['orders'],
                        'aov'     => $entry['orders'] > 0 ? (int)round($entry['revenue'] / $entry['orders']) : 0,
                    ];
                }
                return $timeline;

            } else {
                // Default: Daily interval
                $points = max(7, min($points, 60));
                $sql = "
                    SELECT
                        DATE(placed_at) AS log_date,
                        COUNT(*) AS orders_count,
                        COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) AS revenue,
                        COALESCE(SUM(CASE WHEN status != 'cancelled' THEN (subtotal - COALESCE(discount_amount, 0)) ELSE 0 END), 0) AS net_revenue
                    FROM orders
                    WHERE placed_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    GROUP BY DATE(placed_at)
                    ORDER BY log_date ASC
                ";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $points);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                $mapped = [];
                foreach ($rows as $r) {
                    $mapped[$r['log_date']] = [
                        'orders'  => (int)$r['orders_count'],
                        'revenue' => (int)round((float)$r['revenue']),
                        'net'     => (int)round((float)$r['net_revenue']),
                    ];
                }

                $timeline = [];
                for ($i = $points; $i >= 0; $i--) {
                    $dt = date('Y-m-d', strtotime("-$i days"));
                    $ts = strtotime($dt);
                    $entry = $mapped[$dt] ?? ['orders' => 0, 'revenue' => 0, 'net' => 0];

                    $timeline[] = [
                        'key'     => $dt,
                        'label'   => date('M j', $ts),
                        'day'     => date('D', $ts),
                        'revenue' => $entry['revenue'],
                        'orders'  => $entry['orders'],
                        'aov'     => $entry['orders'] > 0 ? (int)round($entry['revenue'] / $entry['orders']) : 0,
                    ];
                }
                return $timeline;
            }
        } catch (Exception $e) {
            error_log("SalesAnalytics::getSalesTrend error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // 3. CATEGORY PERFORMANCE BREAKDOWN
    // =========================================================================

    /**
     * Calculate sales and unit volume generated by each product category.
     */
    public static function getCategoryPerformance(array $filters = []): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? '30d';
            $dateClause = self::buildDateClause($period, 'o.placed_at', $filters);

            $sql = "
                SELECT
                    c.id AS category_id,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    COALESCE(SUM(oi.quantity), 0) AS units_sold,
                    COALESCE(SUM(oi.line_total), 0) AS revenue,
                    COUNT(DISTINCT o.id) AS orders_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                LEFT JOIN order_items oi ON (oi.product_name = p.name OR oi.variant_id IN (SELECT id FROM product_variants WHERE product_id = p.id))
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled' AND $dateClause
                GROUP BY c.id, c.name, c.slug
                ORDER BY revenue DESC, units_sold DESC
            ";

            $res = $db->query($sql);
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

            $totalRev = 0;
            foreach ($rows as $r) {
                $totalRev += (float)$r['revenue'];
            }

            $categories = [];
            foreach ($rows as $r) {
                $rev = (float)$r['revenue'];
                $share = $totalRev > 0 ? round(($rev / $totalRev) * 100, 1) : 0.0;
                $categories[] = [
                    'category_id'   => (int)$r['category_id'],
                    'category_name' => $r['category_name'],
                    'category_slug' => $r['category_slug'],
                    'units_sold'    => (int)$r['units_sold'],
                    'revenue'       => (int)round($rev),
                    'orders_count'  => (int)$r['orders_count'],
                    'share_pct'     => $share,
                ];
            }

            return $categories;
        } catch (Exception $e) {
            error_log("SalesAnalytics::getCategoryPerformance error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // 4. TOP SELLING LUXURY GARMENTS
    // =========================================================================

    /**
     * Retrieve best-selling products ranked by revenue and units.
     */
    public static function getTopSellingProducts(array $filters = [], int $limit = 10): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? '30d';
            $dateClause = self::buildDateClause($period, 'o.placed_at', $filters);

            $sql = "
                SELECT
                    oi.product_name,
                    MIN(oi.sku) AS sku,
                    COALESCE(p.id, 0) AS product_id,
                    COALESCE(p.slug, '') AS product_slug,
                    COALESCE(c.name, 'Luxury Wear') AS category_name,
                    SUM(oi.quantity) AS units_sold,
                    SUM(oi.line_total) AS gross_revenue,
                    ROUND(AVG(oi.unit_price)) AS avg_unit_price,
                    COUNT(DISTINCT o.id) AS orders_count
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                LEFT JOIN products p ON pv.product_id = p.id OR oi.product_name = p.name
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE o.status != 'cancelled' AND $dateClause
                GROUP BY oi.product_name, p.id, p.slug, c.name
                ORDER BY gross_revenue DESC, units_sold DESC
                LIMIT ?
            ";

            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $products = [];
            foreach ($rows as $r) {
                $products[] = [
                    'product_id'     => (int)$r['product_id'],
                    'product_name'   => $r['product_name'],
                    'product_slug'   => $r['product_slug'],
                    'sku'            => $r['sku'] ?? 'N/A',
                    'category_name'  => $r['category_name'],
                    'units_sold'     => (int)$r['units_sold'],
                    'gross_revenue'  => (int)round((float)$r['gross_revenue']),
                    'avg_unit_price' => (int)$r['avg_unit_price'],
                    'orders_count'   => (int)$r['orders_count'],
                ];
            }

            return $products;
        } catch (Exception $e) {
            error_log("SalesAnalytics::getTopSellingProducts error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // 5. PAYMENT & ORDER FUNNEL DISTRIBUTION
    // =========================================================================

    /**
     * Financial channel breakdown (Razorpay UPI, Cards, NetBanking, COD) and order lifecycle.
     */
    public static function getPaymentMethodBreakdown(array $filters = []): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? '30d';
            $dateClause = self::buildDateClause($period, 'placed_at', $filters);

            // 1. Payment Methods
            $methodSql = "
                SELECT
                    payment_method,
                    COUNT(*) AS count,
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) AS revenue
                FROM orders
                WHERE $dateClause
                GROUP BY payment_method
                ORDER BY revenue DESC
            ";
            $methodRows = $db->query($methodSql)->fetch_all(MYSQLI_ASSOC);

            // 2. Payment Status
            $statusSql = "
                SELECT
                    payment_status,
                    COUNT(*) AS count,
                    COALESCE(SUM(grand_total), 0) AS total_amount
                FROM orders
                WHERE $dateClause
                GROUP BY payment_status
            ";
            $statusRows = $db->query($statusSql)->fetch_all(MYSQLI_ASSOC);

            // 3. Order Lifecycle Status
            $lifecycleSql = "
                SELECT status, COUNT(*) AS count
                FROM orders
                WHERE $dateClause
                GROUP BY status
            ";
            $lifecycleRows = $db->query($lifecycleSql)->fetch_all(MYSQLI_ASSOC);

            return [
                'methods'    => $methodRows,
                'payment_statuses' => $statusRows,
                'order_statuses'   => $lifecycleRows,
            ];
        } catch (Exception $e) {
            error_log("SalesAnalytics::getPaymentMethodBreakdown error: " . $e->getMessage());
            return ['methods' => [], 'payment_statuses' => [], 'order_statuses' => []];
        }
    }

    // =========================================================================
    // 6. GEOGRAPHIC REGIONAL DEMAND (Cities & States)
    // =========================================================================

    /**
     * Sales distribution across high-value luxury delivery cities and states.
     */
    public static function getGeographicDistribution(array $filters = [], int $limit = 8): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? '30d';
            $dateClause = self::buildDateClause($period, 'placed_at', $filters);

            $sql = "
                SELECT
                    COALESCE(shipping_city, 'Other') AS city,
                    COALESCE(shipping_state, 'India') AS state,
                    COUNT(*) AS orders_count,
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN grand_total ELSE 0 END), 0) AS revenue
                FROM orders
                WHERE $dateClause
                GROUP BY shipping_city, shipping_state
                ORDER BY revenue DESC, orders_count DESC
                LIMIT ?
            ";

            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $totalRev = 0;
            foreach ($rows as $r) {
                $totalRev += (float)$r['revenue'];
            }

            $regions = [];
            foreach ($rows as $r) {
                $rev = (float)$r['revenue'];
                $regions[] = [
                    'city'         => $r['city'],
                    'state'        => $r['state'],
                    'orders_count' => (int)$r['orders_count'],
                    'revenue'      => (int)round($rev),
                    'share_pct'    => $totalRev > 0 ? round(($rev / $totalRev) * 100, 1) : 0.0,
                ];
            }

            return $regions;
        } catch (Exception $e) {
            error_log("SalesAnalytics::getGeographicDistribution error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // 7. DAILY SALES SUMMARY LEDGER & SYNC
    // =========================================================================

    /**
     * Retrieve paginated records from daily_sales_summary (or live orders).
     */
    public static function getDailySummaryTable(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();

            // Total count
            $countRes = $db->query("SELECT COUNT(*) FROM daily_sales_summary");
            $total = $countRes ? (int)$countRes->fetch_row()[0] : 0;

            if ($total === 0) {
                // Auto-sync if summary table is empty
                self::syncDailySummary();
                $countRes = $db->query("SELECT COUNT(*) FROM daily_sales_summary");
                $total = $countRes ? (int)$countRes->fetch_row()[0] : 0;
            }

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            $stmt = $db->prepare("
                SELECT *
                FROM daily_sales_summary
                ORDER BY summary_date DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $perPage, $offset);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return [
                'summaries' => $rows,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages,
                ]
            ];
        } catch (Exception $e) {
            error_log("SalesAnalytics::getDailySummaryTable error: " . $e->getMessage());
            return [
                'summaries'  => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false]
            ];
        }
    }

    /**
     * Synchronize and recalculate daily_sales_summary from orders and customers.
     */
    public static function syncDailySummary(): int {
        try {
            $db = Database::connect();

            // Aggregate orders grouped by date
            $sql = "
                SELECT
                    DATE(o.placed_at) AS s_date,
                    COUNT(o.id) AS total_orders,
                    COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.grand_total ELSE 0 END), 0) AS total_revenue,
                    COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.discount_amount ELSE 0 END), 0) AS total_discount,
                    ROUND(COALESCE(AVG(CASE WHEN o.status != 'cancelled' THEN o.grand_total ELSE NULL END), 0), 2) AS avg_order_value,
                    (SELECT COUNT(*) FROM customers WHERE DATE(created_at) = DATE(o.placed_at)) AS new_customers
                FROM orders o
                GROUP BY DATE(o.placed_at)
            ";

            $res = $db->query($sql);
            if (!$res) return 0;

            $syncedCount = 0;
            $stmt = $db->prepare("
                INSERT INTO daily_sales_summary (summary_date, total_orders, total_revenue, total_discount, avg_order_value, new_customers, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    total_orders = VALUES(total_orders),
                    total_revenue = VALUES(total_revenue),
                    total_discount = VALUES(total_discount),
                    avg_order_value = VALUES(avg_order_value),
                    new_customers = VALUES(new_customers),
                    updated_at = NOW()
            ");

            while ($r = $res->fetch_assoc()) {
                $sDate    = $r['s_date'];
                $orders   = (int)$r['total_orders'];
                $rev      = (float)$r['total_revenue'];
                $disc     = (float)$r['total_discount'];
                $aov      = (float)$r['avg_order_value'];
                $newCust  = (int)$r['new_customers'];

                $stmt->bind_param("sidddi", $sDate, $orders, $rev, $disc, $aov, $newCust);
                if ($stmt->execute()) {
                    $syncedCount++;
                }
            }
            $stmt->close();
            return $syncedCount;
        } catch (Exception $e) {
            error_log("SalesAnalytics::syncDailySummary error: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // HELPER: DATE CLAUSE BUILDER
    // =========================================================================

    /**
     * Generate SQL date range clause based on period identifier or custom date range.
     */
    public static function buildDateClause(string $period, string $column = 'placed_at', array $filters = []): string {
        // Custom date range support
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $from = addslashes($filters['date_from']);
            $to   = addslashes($filters['date_to']);
            return "DATE($column) BETWEEN '$from' AND '$to'";
        }

        return match ($period) {
            'today'      => "DATE($column) = CURDATE()",
            '7d'         => "$column >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            '30d'        => "$column >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            '90d'        => "$column >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            'this_month' => "MONTH($column) = MONTH(CURDATE()) AND YEAR($column) = YEAR(CURDATE())",
            'last_month' => "YEARWEEK($column, 1) >= YEARWEEK(DATE_SUB(NOW(), INTERVAL 1 MONTH), 1)",
            'year'       => "YEAR($column) = YEAR(CURDATE())",
            default      => "1=1",
        };
    }
}
