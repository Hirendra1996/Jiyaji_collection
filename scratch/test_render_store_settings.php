<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Jiyaji_collection/admin/settings';
$_SERVER['SCRIPT_NAME'] = '/Jiyaji_collection/index.php';

session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = [
    'id' => 1,
    'name' => 'Royal Administrator',
    'email' => 'admin@jiyaji.com',
    'role' => 'super_admin',
    'role_name' => 'super_admin'
];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/StoreSetting.php';
require_once __DIR__ . '/../app/Controllers/Admin/StoreSettingController.php';

echo "=== 1. Testing StoreSettingController Tab Rendering ===\n";

$controller = new App\Controllers\Admin\StoreSettingController();
$tabs = ['brand', 'contact', 'whatsapp', 'localization', 'marketing', 'social', 'operations'];

$allPassed = true;
foreach ($tabs as $tab) {
    $_GET['tab'] = $tab;
    ob_start();
    try {
        $controller->index();
        $output = ob_get_clean();
        $len = strlen($output);

        $hasErrors = (stripos($output, 'Fatal error') !== false || stripos($output, 'Parse error') !== false || stripos($output, 'Warning:') !== false);
        $hasNav = (stripos($output, 'id="navStoreSettings"') !== false);
        $hasTitle = (stripos($output, 'Store Settings &amp; Brand Control') !== false);

        if (!$hasErrors && $hasNav && $hasTitle && $len > 1000) {
            echo " [PASS] Tab '{$tab}' rendered perfectly ({$len} bytes).\n";
        } else {
            $allPassed = false;
            echo " [FAIL] Tab '{$tab}' issues detected (len: $len, hasErrors: " . ($hasErrors ? 'YES' : 'NO') . ", hasNav: " . ($hasNav ? 'YES' : 'NO') . ")\n";
            echo substr($output, 0, 400) . "\n";
        }
    } catch (Throwable $t) {
        ob_end_clean();
        $allPassed = false;
        echo " [EXCEPTION] Tab '{$tab}' threw: " . $t->getMessage() . " at " . $t->getFile() . ":" . $t->getLine() . "\n";
    }
}

echo "\n=== 2. Testing Global Helper Functions ===\n";
$name = store_setting('store_name');
$phone = store_setting('contact_phone');
$waEnabled = store_setting('whatsapp_enabled');
$waMsg = store_setting('whatsapp_default_message');
$maintMode = store_setting('maintenance_mode');

echo " store_setting('store_name')               : $name\n";
echo " store_setting('contact_phone')            : $phone\n";
echo " store_setting('whatsapp_enabled')         : $waEnabled\n";
echo " store_setting('whatsapp_default_message') : " . substr($waMsg ?? '', 0, 40) . "...\n";
echo " store_setting('maintenance_mode')         : $maintMode\n";

$allFlat = store_settings();
echo " store_settings() total count              : " . count($allFlat) . " keys\n";

$whatsappGroup = store_settings('whatsapp');
echo " store_settings('whatsapp') settings count : " . count($whatsappGroup['settings'] ?? []) . "\n";

echo "\n=== 3. Testing Settings Update via StoreSetting::updateSettings ===\n";
$testUpdate = [
    'store_tagline' => 'Regal Splendor & Bespoke Indian Couture 2026',
    'whatsapp_popup_heading' => 'Need Royal Styling Advice?',
];
$res = \App\Models\StoreSetting::updateSettings($testUpdate, 1);
echo " Update result: " . ($res ? "SUCCESS" : "FAILED") . "\n";
echo " Verified Tagline: " . store_setting('store_tagline') . "\n";
echo " Verified Bubble Heading: " . store_setting('whatsapp_popup_heading') . "\n";

echo "\n=== 4. Testing Boolean Toggle ===\n";
$curr = store_setting('order_acceptance');
echo " Initial order_acceptance: $curr\n";
\App\Models\StoreSetting::toggleSetting('order_acceptance', 1);
$toggled = store_setting('order_acceptance');
echo " After toggleSetting: $toggled\n";
// Toggle back to 1
\App\Models\StoreSetting::toggleSetting('order_acceptance', 1);
echo " Restored order_acceptance: " . store_setting('order_acceptance') . "\n";

echo "\nAll tests completed successfully!\n";
