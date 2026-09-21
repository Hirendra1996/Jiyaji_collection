<?php
// Simulate logged-in admin session and test page output
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = [
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@jiyaji.com',
    'role' => 'super_admin'
];

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');
define('BASE_URL', '/Jiyaji_collection');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/Jiyaji_collection/admin/search-analytics';

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/SearchAnalytics.php';
require_once __DIR__ . '/../app/Controllers/Admin/SearchAnalyticsController.php';

ob_start();
$ctrl = new App\Controllers\Admin\SearchAnalyticsController();
$ctrl->index();
$output = ob_get_clean();

echo "Page length: " . strlen($output) . " bytes\n";
if (stripos($output, 'Notice:') !== false || stripos($output, 'Warning:') !== false || stripos($output, 'Fatal error:') !== false) {
    echo "ERROR: PHP Notices or Warnings found in output!\n";
    // Show first 500 chars of warnings
    preg_match_all('/(Notice|Warning|Fatal error):.*/i', $output, $matches);
    print_r($matches[0]);
} else {
    echo "SUCCESS: Clean rendering with 0 PHP warnings or notices!\n";
}

if (stripos($output, 'Search Keyword Analytics & Zero Results') !== false) {
    echo "SUCCESS: Title found in output!\n";
}
if (stripos($output, 'Zero-Result Rate') !== false) {
    echo "SUCCESS: Zero-Result Rate KPI rendered!\n";
}
if (stripos($output, 'Top Search Terms') !== false) {
    echo "SUCCESS: Top Search Terms tab rendered!\n";
}

// Now test term detail view for 'Sherwani'
$_GET['keyword'] = 'Sherwani';
ob_start();
$ctrl->show('Sherwani');
$showOutput = ob_get_clean();

echo "\nTerm Show page length: " . strlen($showOutput) . " bytes\n";
if (stripos($showOutput, 'Notice:') !== false || stripos($showOutput, 'Warning:') !== false || stripos($showOutput, 'Fatal error:') !== false) {
    echo "ERROR: PHP Notices or Warnings in show page!\n";
} else {
    echo "SUCCESS: Show page rendered cleanly with 0 warnings!\n";
}

// Test zero-result term show page for 'Pashmina Shawl'
$_GET['keyword'] = 'Pashmina Shawl';
ob_start();
$ctrl->show('Pashmina Shawl');
$zeroShowOutput = ob_get_clean();

echo "Zero Term Show page length: " . strlen($zeroShowOutput) . " bytes\n";
if (stripos($zeroShowOutput, 'Zero Catalog Results') !== false) {
    echo "SUCCESS: Zero Catalog Results alert badge rendered on show page!\n";
}
