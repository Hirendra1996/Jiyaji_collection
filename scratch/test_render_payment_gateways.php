<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Jiyaji_collection/admin/payment-gateways';
$_SERVER['SCRIPT_NAME'] = '/Jiyaji_collection/index.php';

session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = ['id' => 1, 'name' => 'Admin', 'email' => 'admin@jiyaji.com', 'role' => 'super_admin'];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/PaymentGateway.php';
require_once __DIR__ . '/../app/Controllers/Admin/PaymentGatewayController.php';

echo "Testing PaymentGatewayController::index() tabs render...\n";

$controller = new App\Controllers\Admin\PaymentGatewayController();

$tabs = ['overview', 'razorpay', 'cod', 'bank_transfer', 'transactions'];

foreach ($tabs as $tab) {
    $_GET['tab'] = $tab;
    ob_start();
    try {
        $controller->index();
        $output = ob_get_clean();
        $len = strlen($output);
        if ($len > 500 && stripos($output, 'Fatal error') === false && stripos($output, 'Parse error') === false) {
            echo "✓ Tab '{$tab}' rendered successfully ({$len} bytes).\n";
        } else {
            echo "✗ Tab '{$tab}' failed or returned error:\n" . substr($output, 0, 500) . "\n";
        }
    } catch (Throwable $t) {
        ob_end_clean();
        echo "✗ Tab '{$tab}' threw exception: " . $t->getMessage() . " at " . $t->getFile() . ":" . $t->getLine() . "\n";
    }
}

echo "\nDone!\n";
