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
$_SERVER['REQUEST_URI'] = '/Jiyaji_collection/admin/sales-analytics';

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/SalesAnalytics.php';
require_once __DIR__ . '/../app/Controllers/Admin/SalesAnalyticsController.php';

$tabs = ['overview', 'products', 'categories', 'regions', 'payments'];
$intervals = ['daily', 'weekly', 'monthly'];

$ctrl = new App\Controllers\Admin\SalesAnalyticsController();

foreach ($tabs as $t) {
    foreach ($intervals as $inv) {
        $_GET['tab'] = $t;
        $_GET['interval'] = $inv;
        $_GET['period'] = '30d';

        ob_start();
        $ctrl->index();
        $output = ob_get_clean();

        if (stripos($output, 'Notice:') !== false || stripos($output, 'Warning:') !== false || stripos($output, 'Fatal error:') !== false) {
            echo "ERROR in tab=$t, interval=$inv!\n";
            preg_match_all('/(Notice|Warning|Fatal error):.*/i', $output, $matches);
            print_r($matches[0]);
            exit(1);
        }
    }
    echo "PASS: Tab '$t' rendered cleanly across daily/weekly/monthly.\n";
}

echo "\nAll tabs and chart intervals rendered cleanly with 0 PHP warnings or notices!\n";
