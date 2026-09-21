<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/SearchAnalytics.php';

use App\Models\SearchAnalytics;

echo "=== 1. KPIs ===\n";
$kpis = SearchAnalytics::getKPIs();
print_r($kpis);

echo "\n=== 2. Top Queries (5) ===\n";
$top = SearchAnalytics::getTopQueries([], 5);
print_r($top);

echo "\n=== 3. Zero-Result Queries (5) ===\n";
$zero = SearchAnalytics::getZeroResultQueries([], 5);
print_r($zero);

echo "\n=== 4. Volume Trend (last 7 days) ===\n";
$trend = SearchAnalytics::getVolumeTrend(7);
print_r($trend);

echo "\n=== 5. Query Details for 'Sherwani' ===\n";
$details = SearchAnalytics::getQueryDetails('Sherwani');
print_r([
    'keyword' => $details['keyword'],
    'total_searches' => $details['total_searches'],
    'avg_results' => $details['avg_results'],
    'devices' => $details['devices'],
    'catalog_total_matches' => $details['catalog_total_matches'],
]);

echo "\n=== 6. Query Details for zero-result term 'Pashmina Shawl' ===\n";
$zeroDetails = SearchAnalytics::getQueryDetails('Pashmina Shawl');
print_r([
    'keyword' => $zeroDetails['keyword'],
    'total_searches' => $zeroDetails['total_searches'],
    'zero_results_rate' => $zeroDetails['zero_results_rate'],
    'catalog_total_matches' => $zeroDetails['catalog_total_matches'],
]);
