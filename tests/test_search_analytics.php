<?php
/**
 * Search Analytics & Zero Results Intelligence — Automated Test Suite
 * Validates SearchAnalytics Model methods, DB aggregations, business rules, and controller logic.
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
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/SearchAnalytics.php';
require_once __DIR__ . '/../app/Controllers/Admin/SearchAnalyticsController.php';

use App\Models\SearchAnalytics;
use App\Controllers\Admin\SearchAnalyticsController;
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
echo " Search Analytics & Zero Results Test Suite\n";
echo "====================================================\n\n";

// =============================================================================
// SUITE 1: Logging Functionality & Edge Cases
// =============================================================================
echo "--- Suite 1: Log Recording & Data Sanitization ---\n";

$testKw = "Luxury Wedding Tuxedo " . uniqid();
$logged = SearchAnalytics::log($testKw, 4, null, '127.0.0.1', 'desktop');
test('log() successfully records search entry', $logged === true, 'insert standard query');

$emptyLogged = SearchAnalytics::log('', 0);
test('log() rejects empty keyword string', $emptyLogged === false, 'empty string guard');

$whitespaceLogged = SearchAnalytics::log('   ', 0);
test('log() rejects whitespace-only keyword', $whitespaceLogged === false, 'whitespace guard');

$invalidDeviceLogged = SearchAnalytics::log("Device Test " . uniqid(), 2, null, '127.0.0.1', 'smartwatch');
test('log() gracefully handles invalid device type', $invalidDeviceLogged === true, 'device fallback');

$hugeKeyword = str_repeat("Royal Silk Velvet ", 30);
$hugeLogged = SearchAnalytics::log($hugeKeyword, 1);
test('log() safely truncates/handles oversized keyword string', $hugeLogged === true, 'length limit guard');

// =============================================================================
// SUITE 2: Executive KPIs Calculation
// =============================================================================
echo "\n--- Suite 2: Executive KPIs Aggregation ---\n";

$kpis = SearchAnalytics::getKPIs('all');
test('getKPIs returns valid array', is_array($kpis), 'array structure');

$requiredKeys = [
    'total_searches', 'unique_keywords', 'zero_results_count',
    'zero_results_rate', 'avg_results_count', 'registered_searches',
    'guest_searches', 'searches_today', 'searches_yesterday',
    'top_keyword', 'top_keyword_count', 'top_zero_keyword', 'top_zero_count'
];
$hasAllKeys = true;
foreach ($requiredKeys as $k) {
    if (!array_key_exists($k, $kpis)) {
        $hasAllKeys = false;
        break;
    }
}
test('getKPIs contains all required metric keys', $hasAllKeys, 'KPI structure integrity');

test('total_searches is non-negative integer', is_int($kpis['total_searches']) && $kpis['total_searches'] >= 0, "total = {$kpis['total_searches']}");
test('unique_keywords <= total_searches', $kpis['unique_keywords'] <= $kpis['total_searches'], "unique = {$kpis['unique_keywords']}");
test('zero_results_rate is between 0 and 100', $kpis['zero_results_rate'] >= 0.0 && $kpis['zero_results_rate'] <= 100.0, "rate = {$kpis['zero_results_rate']}%");
test('registered + guest searches equals total searches', ($kpis['registered_searches'] + $kpis['guest_searches']) === $kpis['total_searches'], "member/guest breakdown");

// Test period filters
$todayKPIs = SearchAnalytics::getKPIs('today');
test('getKPIs handles period=today', is_array($todayKPIs) && isset($todayKPIs['total_searches']), "today period filter");

$sevenDaysKPIs = SearchAnalytics::getKPIs('7d');
test('getKPIs handles period=7d', is_array($sevenDaysKPIs) && $sevenDaysKPIs['total_searches'] <= $kpis['total_searches'], "7d period filter");

// =============================================================================
// SUITE 3: Top Search Queries
// =============================================================================
echo "\n--- Suite 3: Top Search Queries ---\n";

$top = SearchAnalytics::getTopQueries([], 5);
test('getTopQueries returns array of queries', is_array($top), 'top queries list');
test('getTopQueries respects limit parameter', count($top) <= 5, 'limit respected');

if (!empty($top)) {
    $first = $top[0];
    test('Top query contains required fields', isset($first['keyword'], $first['search_count'], $first['avg_results'], $first['unique_users']), 'query fields');
    
    // Test descending order
    $isSorted = true;
    for ($i = 0; $i < count($top) - 1; $i++) {
        if ($top[$i]['search_count'] < $top[$i + 1]['search_count']) {
            $isSorted = false;
            break;
        }
    }
    test('getTopQueries is ordered by search_count descending', $isSorted, 'sort order');
}

// Search filter test
$sherwaniSearch = SearchAnalytics::getTopQueries(['search' => 'Sherwani'], 10);
$allMatched = true;
foreach ($sherwaniSearch as $sq) {
    if (stripos($sq['keyword'], 'Sherwani') === false) {
        $allMatched = false;
        break;
    }
}
test('getTopQueries filters by search keyword accurately', $allMatched, 'keyword filter');

// =============================================================================
// SUITE 4: Zero Results Intelligence
// =============================================================================
echo "\n--- Suite 4: Zero Results Opportunity Queries ---\n";

$zeroList = SearchAnalytics::getZeroResultQueries([], 10);
test('getZeroResultQueries returns array', is_array($zeroList), 'zero-result list');

$allActuallyZero = true;
if (!empty($zeroList)) {
    $db = Database::connect();
    foreach ($zeroList as $zq) {
        // Verify from raw DB that these all have results_count = 0
        $stmt = $db->prepare("SELECT COUNT(*) FROM search_logs WHERE LOWER(TRIM(keyword)) = LOWER(?) AND results_count > 0");
        $stmt->bind_param("s", $zq['keyword']);
        $stmt->execute();
        $cnt = (int)$stmt->get_result()->fetch_row()[0];
        $stmt->close();
        if ($cnt > 0) {
            $allActuallyZero = false;
            break;
        }
    }
}
test('getZeroResultQueries contains only zero-result terms', $allActuallyZero, 'zero result purity');

// =============================================================================
// SUITE 5: Volume Trend Timeline (SVG Data)
// =============================================================================
echo "\n--- Suite 5: Volume Trend Timeline Data ---\n";

$trend = SearchAnalytics::getVolumeTrend(14);
test('getVolumeTrend returns timeline array', is_array($trend), 'trend array');
test('getVolumeTrend returns continuous 15 days (0 to 14 days ago)', count($trend) === 15, "day count = " . count($trend));

$trendValid = true;
foreach ($trend as $pt) {
    if (!isset($pt['date'], $pt['formatted_date'], $pt['day_name'], $pt['total_count'], $pt['zero_count'], $pt['success_count'])) {
        $trendValid = false;
        break;
    }
    if ($pt['total_count'] !== ($pt['zero_count'] + $pt['success_count'])) {
        $trendValid = false;
        break;
    }
}
test('Trend points contain valid schema and total == zero + success', $trendValid, 'point consistency');

// =============================================================================
// SUITE 6: Raw Activity Ledger & Pagination
// =============================================================================
echo "\n--- Suite 6: Activity Stream & Pagination ---\n";

$page1 = SearchAnalytics::getRecentLogs([], 1, 10);
test('getRecentLogs returns array with logs and pagination keys', isset($page1['logs'], $page1['pagination']), 'ledger structure');
test('Pagination metadata includes required keys', isset($page1['pagination']['total'], $page1['pagination']['total_pages'], $page1['pagination']['current_page']), 'pagination keys');
test('getRecentLogs respects perPage limit', count($page1['logs']) <= 10, 'perPage limit');

// Test device filtering
$desktopLogs = SearchAnalytics::getRecentLogs(['device' => 'desktop'], 1, 50);
$allDesktop = true;
foreach ($desktopLogs['logs'] as $dl) {
    if ($dl['device_type'] !== 'desktop') {
        $allDesktop = false;
        break;
    }
}
test('getRecentLogs filters by device=desktop accurately', $allDesktop, 'device filter');

// Test zero results filter
$zeroLogs = SearchAnalytics::getRecentLogs(['results_filter' => 'zero'], 1, 50);
$allZeroLogs = true;
foreach ($zeroLogs['logs'] as $zl) {
    if ((int)$zl['results_count'] !== 0) {
        $allZeroLogs = false;
        break;
    }
}
test('getRecentLogs filters by results_filter=zero accurately', $allZeroLogs && !empty($zeroLogs['logs']), 'results filter');

// =============================================================================
// SUITE 7: Term Deep Dive Intelligence
// =============================================================================
echo "\n--- Suite 7: Keyword Deep Dive Intelligence ---\n";

$details = SearchAnalytics::getQueryDetails('Sherwani');
test('getQueryDetails returns complete metrics array for valid term', is_array($details) && $details['keyword'] === 'Sherwani', 'valid term lookup');

if ($details) {
    test('Details contains devices breakdown', isset($details['devices']['desktop'], $details['devices']['mobile'], $details['devices']['tablet']), 'device breakdown');
    test('Details contains 15-day timeline', isset($details['timeline']) && count($details['timeline']) === 15, 'term timeline');
    test('Details contains catalog matches array', isset($details['catalog_matches']) && is_array($details['catalog_matches']), 'catalog matches array');
    test('Details contains recent logs', isset($details['recent_logs']) && is_array($details['recent_logs']), 'term recent logs');
}

$nonExistentDetails = SearchAnalytics::getQueryDetails('TotallyNonExistentGarmentXYZ987');
test('getQueryDetails returns null for non-existent keyword', $nonExistentDetails === null, 'null on non-existent');

// =============================================================================
// SUITE 8: Catalog Search Simulation
// =============================================================================
echo "\n--- Suite 8: Catalog Search Simulation ---\n";

$sim = SearchAnalytics::simulateCatalogSearch('Sherwani');
test('simulateCatalogSearch returns total and products', isset($sim['total'], $sim['products']), 'simulation output');
test('simulateCatalogSearch with empty keyword returns total 0', SearchAnalytics::simulateCatalogSearch('')['total'] === 0, 'empty simulation');

// =============================================================================
// SUITE 9: Maintenance & Deletion Operations
// =============================================================================
echo "\n--- Suite 9: Record Deletion & Maintenance Operations ---\n";

// Log a throwaway term to delete
$throwaway = "TermToDelete_" . uniqid();
SearchAnalytics::log($throwaway, 0);

$findDetails = SearchAnalytics::getQueryDetails($throwaway);
test('Throwaway query logged and verified in DB', $findDetails !== null, 'pre-delete check');

$delKeyword = SearchAnalytics::deleteByKeyword($throwaway);
test('deleteByKeyword() successfully purges keyword records', $delKeyword === true, 'delete by keyword');

$afterDel = SearchAnalytics::getQueryDetails($throwaway);
test('Verified keyword is completely purged from DB', $afterDel === null, 'post-delete verification');

// Single log delete
$tempTerm = "TempLog_" . uniqid();
SearchAnalytics::log($tempTerm, 5);
$tempLog = SearchAnalytics::getRecentLogs(['search' => $tempTerm], 1, 1);
$tempId = (int)$tempLog['logs'][0]['id'];

$delLog = SearchAnalytics::deleteLog($tempId);
test('deleteLog() successfully deletes single log entry', $delLog === true, 'delete single log');

$tempCheck = SearchAnalytics::getRecentLogs(['search' => $tempTerm], 1, 1);
test('Deleted log no longer exists in DB', empty($tempCheck['logs']), 'verified log gone');

// Test clearOldLogs
$cleared = SearchAnalytics::clearOldLogs(365);
test('clearOldLogs() executes without error', is_int($cleared) && $cleared >= 0, "cleared {$cleared} records");

// =============================================================================
// SUITE 10: Controller & Route Verification
// =============================================================================
echo "\n--- Suite 10: Controller & Route Integration ---\n";

test('SearchAnalyticsController class exists', class_exists(SearchAnalyticsController::class), 'controller class');

$controllerMethods = ['index', 'show', 'simulate', 'export', 'destroy', 'clearOld'];
$allMethodsExist = true;
foreach ($controllerMethods as $m) {
    if (!method_exists(SearchAnalyticsController::class, $m)) {
        $allMethodsExist = false;
        break;
    }
}
test('SearchAnalyticsController implements all required action methods', $allMethodsExist, 'action methods exist');

// Test routes file contains search-analytics routes
$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');
$hasRoutes = strpos($routesContent, 'admin/search-analytics') !== false
          && strpos($routesContent, 'SearchAnalyticsController@index') !== false
          && strpos($routesContent, 'SearchAnalyticsController@show') !== false
          && strpos($routesContent, 'SearchAnalyticsController@export') !== false
          && strpos($routesContent, 'SearchAnalyticsController@simulate') !== false;
test('app/routes.php registers all Search Analytics endpoints', $hasRoutes, 'routes registered');

// Test sidebar contains active search analytics link
$sidebarContent = file_get_contents(__DIR__ . '/../app/Views/admin/layouts/sidebar.php');
$hasSidebar = strpos($sidebarContent, '$isSearchAnalytics') !== false
           && strpos($sidebarContent, 'admin/search-analytics') !== false
           && strpos($sidebarContent, 'badge-live') !== false;
test('app/Views/admin/layouts/sidebar.php activates Search Analytics with Live badge', $hasSidebar, 'sidebar integration');

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
