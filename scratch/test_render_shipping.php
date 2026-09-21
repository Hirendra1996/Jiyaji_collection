<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = ['id' => 1, 'name' => 'Admin Tester', 'email' => 'admin@jiyaji.com', 'role' => 'super_admin'];

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Jiyaji_collection/admin/shipping-pincodes';
$_SERVER['SCRIPT_NAME'] = '/Jiyaji_collection/index.php';

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/PaymentGateway.php';
require_once __DIR__ . '/../app/Models/ShippingZone.php';
require_once __DIR__ . '/../app/Controllers/Admin/ShippingController.php';

echo "Testing ShippingController::index() across all tabs...\n";

$controller = new App\Controllers\Admin\ShippingController();
$tabs = ['zones', 'rates', 'settings', 'couriers', 'calculator'];

foreach ($tabs as $tab) {
    $_GET['tab'] = $tab;
    ob_start();
    try {
        $controller->index();
        $html = ob_get_clean();
        $len = strlen($html);
        if ($len > 1000 && stripos($html, 'Fatal error') === false && stripos($html, 'Parse error') === false && stripos($html, 'Warning:') === false) {
            echo "✓ Tab '{$tab}' rendered cleanly ({$len} bytes).\n";
        } else {
            echo "✗ Tab '{$tab}' returned error or notice:\n" . substr($html, 0, 500) . "\n";
        }
    } catch (Throwable $t) {
        ob_end_clean();
        echo "✗ Tab '{$tab}' threw exception: " . $t->getMessage() . " at " . $t->getFile() . ":" . $t->getLine() . "\n";
    }
}

echo "\nDone!\n";
