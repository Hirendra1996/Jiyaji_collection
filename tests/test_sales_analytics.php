<?php
/**
 * Sales Analytics & Financial Reporting — Automated Test Suite
 * Validates SalesAnalytics Model calculations, aggregations, trends, intervals, filters, and controller routes.
 */

// Bootstrap
define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Models/SalesAnalytics.php';
require_once __DIR__ . '/../app/Controllers/Admin/SalesAnalyticsController.php';

use App\Models\SalesAnalytics;
use App\Controllers\Admin\SalesAnalyticsController;
use App\Config\Database;

$tests = [];
$pass  = 0;
$fail  = 0;

function test(string $name, bool $result, string $detail = ''): void {
    global $tests, $pass, $fail;
    $tests[] = ['name' => $name, 'result' => $result, 'detail' => $detail];
    $result ? $pass++ : $fail++;
    echo ($result ? " \033[32m✓\033[0m " : " \033[31m✗\033[0m ") . $name . ($detail ? " — $detail" : '') . "\n";
}

echo "====================================================\n";
echo " Sales Analytics & Financial Reporting Test Suite\n";
echo "====================================================\n\n";

// =============================================================================
// SUITE 1: Financial KPIs Aggregation
// =============================================================================
echo "--- Suite 1: Financial KPIs Calculations ---\n";

$kpis = SalesAnalytics::getFinancialKPIs(['period' => 'all']);
test('getFinancialKPIs returns valid array', is_array($kpis), 'array structure');

$requiredKeys = [
    'gross_revenue', 'net_revenue', 'total_orders', 'paid_orders', 'paid_rate',
    'delivered_orders', 'cancelled_orders', 'total_discounts', 'total_tax',
    'total_shipping', 'avg_order_value', 'units_sold', 'units_per_order',
    'today_revenue', 'today_orders', 'yesterday_revenue', 'yesterday_orders', 'revenue_growth'
];
$allKeysPresent = true;
foreach ($requiredKeys as $k) {
    if (!array_key_exists($k, $kpis)) {
        $allKeysPresent = false;
        break;
    }
}
test('getFinancialKPIs contains all required financial metrics', $allKeysPresent, 'KPI structure integrity');

test('gross_revenue is non-negative integer', is_int($kpis['gross_revenue']) && $kpis['gross_revenue'] >= 0, "gross = ₹" . number_format($kpis['gross_revenue']));
test('net_revenue <= gross_revenue', $kpis['net_revenue'] <= $kpis['gross_revenue'], "net = ₹" . number_format($kpis['net_revenue']));
test('total_orders is non-negative integer', is_int($kpis['total_orders']) && $kpis['total_orders'] > 0, "orders = {$kpis['total_orders']}");
test('paid_orders <= total_orders', $kpis['paid_orders'] <= $kpis['total_orders'], "paid = {$kpis['paid_orders']}");
test('paid_rate is between 0 and 100', $kpis['paid_rate'] >= 0.0 && $kpis['paid_rate'] <= 100.0, "paid_rate = {$kpis['paid_rate']}%");
test('avg_order_value is non-negative', is_int($kpis['avg_order_value']) && $kpis['avg_order_value'] >= 0, "AOV = ₹" . number_format($kpis['avg_order_value']));
test('units_sold is positive integer', is_int($kpis['units_sold']) && $kpis['units_sold'] >= $kpis['total_orders'], "units = {$kpis['units_sold']}");

// Test period presets
$todayKPIs = SalesAnalytics::getFinancialKPIs(['period' => 'today']);
test('getFinancialKPIs handles period=today', is_array($todayKPIs) && isset($todayKPIs['gross_revenue']), 'today period');

$monthKPIs = SalesAnalytics::getFinancialKPIs(['period' => 'this_month']);
test('getFinancialKPIs handles period=this_month', is_array($monthKPIs) && $monthKPIs['total_orders'] <= $kpis['total_orders'], 'month period');

$customKPIs = SalesAnalytics::getFinancialKPIs([
    'date_from' => date('Y-m-01'),
    'date_to'   => date('Y-m-d')
]);
test('getFinancialKPIs handles custom date_from and date_to', is_array($customKPIs) && isset($customKPIs['gross_revenue']), 'custom date range');

// =============================================================================
// SUITE 2: Sales Trajectory Trends (Daily, Weekly, Monthly)
// =============================================================================
echo "\n--- Suite 2: Sales Trajectory Trends (Daily, Weekly, Monthly) ---\n";

// Daily Trend
$dailyTrend = SalesAnalytics::getSalesTrend('daily', 14);
test('getSalesTrend(daily) returns array', is_array($dailyTrend), 'daily trend array');
test('getSalesTrend(daily) returns continuous 15 days (0 to 14 days ago)', count($dailyTrend) === 15, "points = " . count($dailyTrend));

$dailyPointValid = true;
foreach ($dailyTrend as $dp) {
    if (!isset($dp['key'], $dp['label'], $dp['day'], $dp['revenue'], $dp['orders'], $dp['aov'])) {
        $dailyPointValid = false;
        break;
    }
}
test('Daily trend points contain valid schema (key, label, day, revenue, orders, aov)', $dailyPointValid, 'daily point schema');

// Weekly Trend
$weeklyTrend = SalesAnalytics::getSalesTrend('weekly', 8);
test('getSalesTrend(weekly) returns 8 weekly points', count($weeklyTrend) === 8, "weekly points = " . count($weeklyTrend));
test('Weekly trend points contain formatted label', isset($weeklyTrend[0]['label']) && strpos($weeklyTrend[0]['label'], 'Wk') !== false, 'weekly label format');

// Monthly Trend
$monthlyTrend = SalesAnalytics::getSalesTrend('monthly', 6);
test('getSalesTrend(monthly) returns 6 monthly points', count($monthlyTrend) === 6, "monthly points = " . count($monthlyTrend));
test('Monthly trend points contain formatted month label', isset($monthlyTrend[0]['label']) && strlen($monthlyTrend[0]['label']) >= 7, 'monthly label format');

// =============================================================================
// SUITE 3: Category Performance Breakdown
// =============================================================================
echo "\n--- Suite 3: Category Performance Breakdown ---\n";

$categories = SalesAnalytics::getCategoryPerformance(['period' => 'all']);
test('getCategoryPerformance returns array of categories', is_array($categories) && !empty($categories), 'categories list');

if (!empty($categories)) {
    $firstCat = $categories[0];
    test('Category row contains required metrics', isset($firstCat['category_id'], $firstCat['category_name'], $firstCat['units_sold'], $firstCat['revenue'], $firstCat['share_pct']), 'category metrics schema');

    // Test descending order by revenue
    $isSorted = true;
    for ($i = 0; $i < count($categories) - 1; $i++) {
        if ($categories[$i]['revenue'] < $categories[$i + 1]['revenue']) {
            $isSorted = false;
            break;
        }
    }
    test('Category performance is ordered by revenue descending', $isSorted, 'revenue descending sort');

    $totalShare = 0;
    foreach ($categories as $c) {
        $totalShare += $c['share_pct'];
    }
    test('Total category market share sums to approx 100%', $totalShare >= 99.0 && $totalShare <= 101.0, "total share = {$totalShare}%");
}

// =============================================================================
// SUITE 4: Top Selling Luxury Products
// =============================================================================
echo "\n--- Suite 4: Best-Selling Luxury Garments ---\n";

$topProducts = SalesAnalytics::getTopSellingProducts(['period' => 'all'], 5);
test('getTopSellingProducts returns products array', is_array($topProducts) && !empty($topProducts), 'products list');
test('getTopSellingProducts respects limit parameter', count($topProducts) <= 5, 'limit respected');

if (!empty($topProducts)) {
    $firstProd = $topProducts[0];
    test('Product row contains required fields', isset($firstProd['product_name'], $firstProd['sku'], $firstProd['category_name'], $firstProd['units_sold'], $firstProd['gross_revenue'], $firstProd['avg_unit_price']), 'product fields');

    $isProdSorted = true;
    for ($i = 0; $i < count($topProducts) - 1; $i++) {
        if ($topProducts[$i]['gross_revenue'] < $topProducts[$i + 1]['gross_revenue']) {
            $isProdSorted = false;
            break;
        }
    }
    test('Top products ordered by gross_revenue descending', $isProdSorted, 'product revenue sort');
}

// =============================================================================
// SUITE 5: Payment Channels & Order Lifecycle Funnel
// =============================================================================
echo "\n--- Suite 5: Payment Methods & Order Lifecycle Funnel ---\n";

$payments = SalesAnalytics::getPaymentMethodBreakdown(['period' => 'all']);
test('getPaymentMethodBreakdown returns methods, payment_statuses, order_statuses', isset($payments['methods'], $payments['payment_statuses'], $payments['order_statuses']), 'payment breakdown schema');
test('Payment methods contains transactions', !empty($payments['methods']), 'methods populated');
test('Order statuses contains lifecycle stages', !empty($payments['order_statuses']), 'lifecycle stages populated');

// =============================================================================
// SUITE 6: Geographic Regional Demand Distribution
// =============================================================================
echo "\n--- Suite 6: Geographic Regional Demand ---\n";

$regions = SalesAnalytics::getGeographicDistribution(['period' => 'all'], 8);
test('getGeographicDistribution returns regions array', is_array($regions) && !empty($regions), 'regions list');

if (!empty($regions)) {
    $firstReg = $regions[0];
    test('Region row contains city, state, orders_count, revenue, share_pct', isset($firstReg['city'], $firstReg['state'], $firstReg['orders_count'], $firstReg['revenue'], $firstReg['share_pct']), 'region fields');
    test('Regions sorted by revenue descending', $regions[0]['revenue'] >= end($regions)['revenue'], 'regions revenue sort');
}

// =============================================================================
// SUITE 7: Daily Sales Summary Synchronization & Ledger
// =============================================================================
echo "\n--- Suite 7: Daily Sales Summary Synchronization & Ledger ---\n";

$syncedCount = SalesAnalytics::syncDailySummary();
test('syncDailySummary() executes and returns positive synced count', is_int($syncedCount) && $syncedCount > 0, "synced {$syncedCount} dates");

$ledger = SalesAnalytics::getDailySummaryTable([], 1, 10);
test('getDailySummaryTable returns summaries and pagination', isset($ledger['summaries'], $ledger['pagination']), 'ledger schema');
test('Pagination contains total, total_pages, current_page', isset($ledger['pagination']['total'], $ledger['pagination']['total_pages'], $ledger['pagination']['current_page']), 'pagination metadata');
test('Summaries contains records matching synced data', !empty($ledger['summaries']), 'summary rows populated');

if (!empty($ledger['summaries'])) {
    $row = $ledger['summaries'][0];
    test('Summary row has summary_date, total_orders, total_revenue', isset($row['summary_date'], $row['total_orders'], $row['total_revenue']), 'summary row structure');
}

// =============================================================================
// SUITE 8: Controller, Routes, and Sidebar Integration
// =============================================================================
echo "\n--- Suite 8: Controller, Routes, & Sidebar Integration ---\n";

test('SalesAnalyticsController class exists', class_exists(SalesAnalyticsController::class), 'controller class');

$methods = ['index', 'export', 'syncSummary'];
$allMethods = true;
foreach ($methods as $m) {
    if (!method_exists(SalesAnalyticsController::class, $m)) {
        $allMethods = false;
        break;
    }
}
test('SalesAnalyticsController implements index, export, and syncSummary', $allMethods, 'controller methods');

$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');
$hasRoutes = strpos($routesContent, 'admin/sales-analytics') !== false
          && strpos($routesContent, 'SalesAnalyticsController@index') !== false
          && strpos($routesContent, 'SalesAnalyticsController@export') !== false
          && strpos($routesContent, 'SalesAnalyticsController@syncSummary') !== false;
test('app/routes.php registers all Sales Analytics endpoints', $hasRoutes, 'routes registered');

$sidebarContent = file_get_contents(__DIR__ . '/../app/Views/admin/layouts/sidebar.php');
$hasSidebar = strpos($sidebarContent, '$isSalesAnalytics') !== false
           && strpos($sidebarContent, 'admin/sales-analytics') !== false
           && strpos($sidebarContent, 'badge-live') !== false;
test('app/Views/admin/layouts/sidebar.php activates Sales Analytics with Live badge', $hasSidebar, 'sidebar active with live badge');

// =============================================================================
// SUMMARY REPORT
// =============================================================================
echo "\n====================================================\n";
echo " Test Results: $pass Passed, $fail Failed\n";
echo "====================================================\n";

if ($fail > 0) {
    echo "\033[31mSOME TESTS FAILED!\033[0m\n";
    exit(1);
} else {
    echo "\033[32mALL TESTS PASSED WITH 100% SUCCESS RATE!\033[0m\n";
    exit(0);
}
