<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Admin {
    /**
     * Find admin record by email with role information.
     */
    public static function findByEmail(string $email): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT a.id, a.role_id, a.name, a.email, a.password_hash, a.is_active, a.created_at, r.name AS role_name
                FROM admins a
                LEFT JOIN roles r ON a.role_id = r.id
                WHERE a.email = ?
                LIMIT 1
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $admin = $res->fetch_assoc();
            return $admin ?: null;
        } catch (Exception $e) {
            error_log("Admin findByEmail error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find admin record by ID.
     */
    public static function findById(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT a.id, a.role_id, a.name, a.email, a.is_active, a.created_at, r.name AS role_name
                FROM admins a
                LEFT JOIN roles r ON a.role_id = r.id
                WHERE a.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $admin = $res->fetch_assoc();
            return $admin ?: null;
        } catch (Exception $e) {
            error_log("Admin findById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify credentials during login.
     */
    public static function verify(string $email, string $password): array {
        $admin = self::findByEmail($email);

        if (!$admin) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        if ((int)$admin['is_active'] !== 1) {
            return [
                'success' => false,
                'message' => 'Your account is deactivated. Please contact the store owner.'
            ];
        }

        if (!password_verify($password, $admin['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        unset($admin['password_hash']);

        return [
            'success' => true,
            'admin'   => $admin
        ];
    }

    /**
     * Fetch key KPI statistics and dynamic data for the Admin Dashboard.
     */
    public static function getDashboardStats(): array {
        $stats = [
            'total_revenue'      => 0.00,
            'revenue_growth'     => '+18.4%',
            'total_orders'       => 0,
            'orders_growth'      => '+12.5%',
            'total_customers'    => 0,
            'customers_growth'   => '+24.2%',
            'total_products'     => 0,
            'low_stock_count'    => 0,
            'pending_returns'    => 0,
            'monthly_target'     => [
                'target'         => 200000.00,
                'current'        => 0.00,
                'percentage'     => 0.0
            ],
            'category_breakdown' => [],
            'top_products'       => [],
            'live_activities'    => [],
            'notifications'      => [],
            'recent_orders'      => [],
            'analytics_series'   => [
                'labels'         => ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Current'],
                'revenue_path'   => 'M 40,165 Q 130,120 220,95 T 400,55 T 580,35',
                'orders_path'    => 'M 40,175 Q 130,145 220,130 T 400,95 T 580,75'
            ]
        ];

        try {
            $db = Database::connect();

            // 1. Total Gross Revenue (from paid orders)
            $revRes = $db->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM orders WHERE payment_status = 'paid'");
            if ($revRes) {
                $stats['total_revenue'] = (int)round((float)$revRes->fetch_assoc()['total']);
            }

            // 2. Total Orders
            $ordRes = $db->query("SELECT COUNT(*) as total FROM orders");
            if ($ordRes) {
                $stats['total_orders'] = (int)$ordRes->fetch_assoc()['total'];
            }

            // 3. Total Customers
            $custRes = $db->query("SELECT COUNT(*) as total FROM customers WHERE is_active = 1");
            if ($custRes) {
                $stats['total_customers'] = (int)$custRes->fetch_assoc()['total'];
            }

            // 4. Total Products
            $prodRes = $db->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
            if ($prodRes) {
                $stats['total_products'] = (int)$prodRes->fetch_assoc()['total'];
            }

            // 5. Low Stock Alerts
            $stockRes = $db->query("SELECT COUNT(*) as total FROM stock_alerts WHERE resolved = 0");
            if ($stockRes) {
                $stats['low_stock_count'] = (int)$stockRes->fetch_assoc()['total'];
            }

            // 6. Pending Returns
            $retRes = $db->query("SELECT COUNT(*) as total FROM returns WHERE status = 'requested'");
            if ($retRes) {
                $stats['pending_returns'] = (int)$retRes->fetch_assoc()['total'];
            }

            // 7. Monthly Target Goal (Target: ₹2,00,000)
            $targetGoal = 200000;
            $currentMonthRevenue = (int)round((float)$stats['total_revenue']);
            $pct = $targetGoal > 0 ? round(($currentMonthRevenue / $targetGoal) * 100, 1) : 0;
            $stats['monthly_target'] = [
                'target'     => $targetGoal,
                'current'    => $currentMonthRevenue,
                'percentage' => min(100.0, $pct)
            ];

            // 8. Dynamic Category Distribution
            $catSql = "
                SELECT c.id, c.name,
                       COALESCE(SUM(oi.line_total), 0) as revenue,
                       COUNT(DISTINCT p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                LEFT JOIN product_variants pv ON pv.product_id = p.id
                LEFT JOIN order_items oi ON (oi.variant_id = pv.id OR oi.product_name = p.name)
                WHERE c.is_active = 1
                GROUP BY c.id, c.name
                ORDER BY revenue DESC
            ";
            $catRes = $db->query($catSql);
            $totalCatRevenue = 0;
            $tempCats = [];
            if ($catRes) {
                while ($cRow = $catRes->fetch_assoc()) {
                    $rev = (float)$cRow['revenue'];
                    $totalCatRevenue += $rev;
                    $tempCats[] = [
                        'name'    => $cRow['name'],
                        'revenue' => $rev,
                        'count'   => (int)$cRow['product_count']
                    ];
                }
            }

            $colors = ['#2D82FF', '#8C30F5', '#FF5100', '#00B4D8', '#10B981'];
            $colorIdx = 0;
            $accumOffset = 0;
            foreach ($tempCats as $tc) {
                $percent = $totalCatRevenue > 0 ? round(($tc['revenue'] / $totalCatRevenue) * 100) : 20;
                $color = $colors[$colorIdx % count($colors)];
                $stats['category_breakdown'][] = [
                    'name'    => $tc['name'],
                    'percent' => $percent,
                    'amount'  => $tc['revenue'],
                    'color'   => $color,
                    'offset'  => $accumOffset
                ];
                $accumOffset += $percent;
                $colorIdx++;
            }

            // 9. Dynamic Top Performing Products Leaderboard
            $topSql = "
                SELECT p.id, p.name, p.base_price, c.name as category_name,
                       COALESCE(pv.sku, 'JLX-SKU') as sku,
                       COALESCE(pv.stock_qty, 0) as stock,
                       COALESCE(SUM(oi.quantity), 0) as total_sold,
                       COALESCE(SUM(oi.line_total), 0) as total_sales
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_variants pv ON pv.product_id = p.id
                LEFT JOIN order_items oi ON (oi.variant_id = pv.id OR oi.product_name = p.name)
                GROUP BY p.id, p.name, p.base_price, c.name, pv.sku, pv.stock_qty
                ORDER BY total_sold DESC, total_sales DESC
                LIMIT 4
            ";
            $topRes = $db->query($topSql);
            if ($topRes) {
                while ($pRow = $topRes->fetch_assoc()) {
                    $stock = (int)$pRow['stock'];
                    $status = $stock > 10 ? 'In Stock' : ($stock > 0 ? 'Low Stock' : 'Out of Stock');
                    $stats['top_products'][] = [
                        'name'     => $pRow['name'],
                        'sku'      => $pRow['sku'],
                        'category' => $pRow['category_name'],
                        'price'    => (float)$pRow['base_price'],
                        'sales'    => (int)$pRow['total_sold'],
                        'stock'    => $stock,
                        'status'   => $status
                    ];
                }
            }

            // 10. Dynamic Live Store Activities (Orders, Customers, Alerts)
            $activities = [];
            // Recent Orders
            $recentOrders = $db->query("
                SELECT order_number, grand_total, shipping_name, payment_method, placed_at
                FROM orders ORDER BY placed_at DESC LIMIT 3
            ");
            if ($recentOrders) {
                while ($o = $recentOrders->fetch_assoc()) {
                    $activities[] = [
                        'timestamp' => strtotime($o['placed_at']),
                        'title'     => 'New Order ' . $o['order_number'],
                        'desc'      => $o['shipping_name'] . ' &bull; ₹' . number_format((int)round((float)$o['grand_total'])) . ' (' . $o['payment_method'] . ')',
                        'time'      => self::timeAgo($o['placed_at']),
                        'icon'      => 'orange'
                    ];
                }
            }

            // Recent Customers
            $recentCust = $db->query("
                SELECT name, email, created_at FROM customers ORDER BY created_at DESC LIMIT 2
            ");
            if ($recentCust) {
                while ($c = $recentCust->fetch_assoc()) {
                    $activities[] = [
                        'timestamp' => strtotime($c['created_at']),
                        'title'     => 'New Customer Registration',
                        'desc'      => $c['name'] . ' &bull; ' . $c['email'],
                        'time'      => self::timeAgo($c['created_at']),
                        'icon'      => 'purple'
                    ];
                }
            }

            // Unresolved Stock Alerts
            $recentAlerts = $db->query("
                SELECT p.name, pv.sku, pv.stock_qty, sa.alerted_at
                FROM stock_alerts sa
                JOIN product_variants pv ON sa.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                WHERE sa.resolved = 0
                LIMIT 2
            ");
            if ($recentAlerts) {
                while ($sa = $recentAlerts->fetch_assoc()) {
                    $activities[] = [
                        'timestamp' => strtotime($sa['alerted_at']),
                        'title'     => 'Low Stock Warning',
                        'desc'      => $sa['name'] . ' (' . $sa['sku'] . ') &bull; ' . $sa['stock_qty'] . ' left',
                        'time'      => self::timeAgo($sa['alerted_at']),
                        'icon'      => 'orange'
                    ];
                }
            }

            // Sort activities by timestamp descending
            usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
            $stats['live_activities'] = array_slice($activities, 0, 5);

            // 11. Dynamic Topbar Notifications
            $notifications = [];
            // Unresolved stock alerts
            $stockNoticeRes = $db->query("
                SELECT p.name, pv.stock_qty FROM stock_alerts sa
                JOIN product_variants pv ON sa.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                WHERE sa.resolved = 0 LIMIT 2
            ");
            if ($stockNoticeRes) {
                while ($sn = $stockNoticeRes->fetch_assoc()) {
                    $notifications[] = [
                        'title' => 'Stock Alert: ' . $sn['name'],
                        'time'  => 'Only ' . $sn['stock_qty'] . ' units remaining',
                        'icon'  => 'orange'
                    ];
                }
            }

            // Latest high-value orders
            $highValRes = $db->query("
                SELECT order_number, grand_total, placed_at FROM orders WHERE payment_status = 'paid' ORDER BY placed_at DESC LIMIT 2
            ");
            if ($highValRes) {
                while ($ho = $highValRes->fetch_assoc()) {
                    $notifications[] = [
                        'title' => 'Order ' . $ho['order_number'] . ' (₹' . number_format((float)$ho['grand_total']) . ')',
                        'time'  => self::timeAgo($ho['placed_at']),
                        'icon'  => 'blue'
                    ];
                }
            }

            // Pending return notice
            if ($stats['pending_returns'] > 0) {
                $notifications[] = [
                    'title' => $stats['pending_returns'] . ' Pending Return Request(s)',
                    'time'  => 'Requires approval',
                    'icon'  => 'purple'
                ];
            }
            $stats['notifications'] = $notifications;

            // 12. Dynamic Recent Orders Ledger
            $ordersSql = "
                SELECT o.id, o.order_number, o.grand_total, o.status, o.payment_status, o.placed_at,
                       COALESCE(c.name, o.shipping_name, 'Customer') AS customer_name
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                ORDER BY o.placed_at DESC
                LIMIT 10
            ";
            $ordersRes = $db->query($ordersSql);
            if ($ordersRes) {
                while ($orow = $ordersRes->fetch_assoc()) {
                    $stats['recent_orders'][] = $orow;
                }
            }
        } catch (Exception $e) {
            error_log("Dashboard stats query error: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Helper to format relative time ago.
     */
    private static function timeAgo(string $datetime): string {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = max(1, round($diff / 60));
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hrs = round($diff / 3600);
            return $hrs . 'h ago';
        } elseif ($diff < 604800) {
            $days = round($diff / 86400);
            return $days . 'd ago';
        } else {
            return date('d M Y', $timestamp);
        }
    }
}
