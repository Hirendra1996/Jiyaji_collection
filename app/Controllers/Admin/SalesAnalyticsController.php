<?php

namespace App\Controllers\Admin;

use App\Models\SalesAnalytics;
use App\Middleware\AuthMiddleware;
use App\Config\Database;
use Exception;

class SalesAnalyticsController {

    // =========================================================================
    // INDEX — Sales Analytics & Financial Command Center
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $period = $_GET['period'] ?? '30d';
        $validPeriods = ['today', '7d', '30d', '90d', 'this_month', 'last_month', 'year', 'all'];
        if (!in_array($period, $validPeriods, true)) {
            $period = '30d';
        }

        $interval = $_GET['interval'] ?? 'daily';
        if (!in_array($interval, ['daily', 'weekly', 'monthly'], true)) {
            $interval = 'daily';
        }

        $activeTab = $_GET['tab'] ?? 'overview';
        $validTabs = ['overview', 'products', 'categories', 'regions', 'payments', 'ledger'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'overview';
        }

        $filters = [
            'period'    => $period,
            'interval'  => $interval,
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

        // Auto-sync summary if empty
        $points = match ($interval) {
            'monthly' => 12,
            'weekly'  => 8,
            default   => ($period === '90d' ? 30 : 14),
        };

        $kpis         = SalesAnalytics::getFinancialKPIs($filters);
        $salesTrend   = SalesAnalytics::getSalesTrend($interval, $points, $filters);
        $categories   = SalesAnalytics::getCategoryPerformance($filters);
        $topProducts  = SalesAnalytics::getTopSellingProducts($filters, 15);
        $payments     = SalesAnalytics::getPaymentMethodBreakdown($filters);
        $regions      = SalesAnalytics::getGeographicDistribution($filters, 10);
        $ledgerData   = SalesAnalytics::getDailySummaryTable($filters, $page, 15);

        $summaries    = $ledgerData['summaries'];
        $pagination   = $ledgerData['pagination'];

        $title = 'Sales Analytics & Financial Reporting | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/sales_analytics/index.php';
    }

    // =========================================================================
    // EXPORT — Multi-Format CSV Financial Reports
    // =========================================================================

    public function export(): void {
        AuthMiddleware::check();

        $type = $_GET['export_type'] ?? 'daily_summary';
        $period = $_GET['period'] ?? 'all';
        $filters = [
            'period'    => $period,
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
        ];

        $filename = 'jiyaji_sales_' . $type . '_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'products') {
            // Products Sales Breakdown
            fputcsv($out, ['Product Name', 'SKU', 'Category', 'Units Sold', 'Gross Revenue (INR)', 'Average Selling Price (INR)', 'Orders Count']);
            $products = SalesAnalytics::getTopSellingProducts($filters, 500);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p['product_name'],
                    $p['sku'],
                    $p['category_name'],
                    $p['units_sold'],
                    $p['gross_revenue'],
                    $p['avg_unit_price'],
                    $p['orders_count'],
                ]);
            }
        } elseif ($type === 'categories') {
            // Category Performance Breakdown
            fputcsv($out, ['Category Name', 'Units Sold', 'Revenue (INR)', 'Orders Count', 'Market Share %']);
            $cats = SalesAnalytics::getCategoryPerformance($filters);
            foreach ($cats as $c) {
                fputcsv($out, [
                    $c['category_name'],
                    $c['units_sold'],
                    $c['revenue'],
                    $c['orders_count'],
                    $c['share_pct'] . '%',
                ]);
            }
        } elseif ($type === 'regions') {
            // Geographic Sales Breakdown
            fputcsv($out, ['City', 'State', 'Orders Count', 'Revenue (INR)', 'Market Share %']);
            $regs = SalesAnalytics::getGeographicDistribution($filters, 100);
            foreach ($regs as $r) {
                fputcsv($out, [
                    $r['city'],
                    $r['state'],
                    $r['orders_count'],
                    $r['revenue'],
                    $r['share_pct'] . '%',
                ]);
            }
        } elseif ($type === 'orders_ledger') {
            // Detailed Orders Financial Ledger
            fputcsv($out, ['Order Number', 'Date Placed', 'Customer / Recipient', 'City', 'Status', 'Payment Method', 'Payment Status', 'Subtotal', 'Discount', 'Tax', 'Shipping', 'Grand Total (INR)']);
            try {
                $db = Database::connect();
                $dateClause = SalesAnalytics::buildDateClause($period, 'placed_at', $filters);
                $res = $db->query("
                    SELECT order_number, placed_at, shipping_name, shipping_city, status, payment_method, payment_status, subtotal, discount_amount, tax_amount, shipping_charge, grand_total
                    FROM orders
                    WHERE $dateClause
                    ORDER BY placed_at DESC
                ");
                if ($res) {
                    while ($r = $res->fetch_assoc()) {
                        fputcsv($out, [
                            $r['order_number'],
                            $r['placed_at'],
                            $r['shipping_name'],
                            $r['shipping_city'],
                            ucfirst($r['status']),
                            $r['payment_method'],
                            ucfirst($r['payment_status']),
                            $r['subtotal'],
                            $r['discount_amount'] ?? '0.00',
                            $r['tax_amount'] ?? '0.00',
                            $r['shipping_charge'] ?? '0.00',
                            $r['grand_total'],
                        ]);
                    }
                }
            } catch (Exception $e) {
                error_log("Export orders_ledger error: " . $e->getMessage());
            }
        } else {
            // Default: Daily Sales Summary
            fputcsv($out, ['Summary Date', 'Total Orders', 'Gross Revenue (INR)', 'Total Discounts (INR)', 'Net Revenue (INR)', 'Average Order Value (INR)', 'New Customers Registered']);
            try {
                $db = Database::connect();
                $res = $db->query("SELECT * FROM daily_sales_summary ORDER BY summary_date DESC");
                if ($res) {
                    while ($r = $res->fetch_assoc()) {
                        $net = (float)$r['total_revenue'] - (float)$r['total_discount'];
                        fputcsv($out, [
                            $r['summary_date'],
                            $r['total_orders'],
                            $r['total_revenue'],
                            $r['total_discount'],
                            number_format($net, 2, '.', ''),
                            $r['avg_order_value'],
                            $r['new_customers'],
                        ]);
                    }
                }
            } catch (Exception $e) {
                error_log("Export daily_summary error: " . $e->getMessage());
            }
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // SYNC SUMMARY — Recalculate Daily Sales Aggregation
    // =========================================================================

    public function syncSummary(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/sales-analytics');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF verification token expired. Please try again.');
            redirect('admin/sales-analytics');
        }

        $count = SalesAnalytics::syncDailySummary();
        set_toast('success', 'Summary Synchronized', "Successfully recalculated {$count} daily financial summary records from store orders.");
        redirect('admin/sales-analytics');
    }
}
