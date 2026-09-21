<?php
/**
 * Shipping Zones, Rates & Delivery Logistics — Automated Test Suite
 * Validates ShippingZone Model, Zone CRUD, Weight Slabs, Carrier Profiles,
 * Live Rate & Pincode Calculator, Global Free Shipping Thresholds, Controller, and Admin Views.
 */

// Bootstrap
define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = ['id' => 1, 'name' => 'Admin Logistics Tester', 'email' => 'admin@jiyaji.com', 'role' => 'super_admin'];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/PaymentGateway.php';
require_once __DIR__ . '/../app/Models/ShippingZone.php';
require_once __DIR__ . '/../app/Controllers/Admin/ShippingController.php';

use App\Models\ShippingZone;
use App\Controllers\Admin\ShippingController;
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
echo " Shipping & Pincodes Logistics Test Suite\n";
echo "====================================================\n\n";

// =============================================================================
// SUITE 1: Delivery Zones CRUD & Management
// =============================================================================
echo "--- Suite 1: Delivery Zones CRUD & Management ---\n";

ShippingZone::ensureDefaults();
$zones = ShippingZone::getZones();

test('ensureDefaults initializes delivery zones', is_array($zones) && count($zones) >= 4, "zones count = " . count($zones));

// Verify specific default zones
$zoneCodes = array_column($zones, 'zone_code');
test('Default zones include METRO zone', in_array('METRO', $zoneCodes, true), 'METRO zone found');
test('Default zones include TIER1_2 zone', in_array('TIER1_2', $zoneCodes, true), 'TIER1_2 zone found');
test('Default zones include ROI (Rest of India) catch-all zone', in_array('ROI', $zoneCodes, true), 'ROI zone found');
test('Default zones include REMOTE zone', in_array('REMOTE', $zoneCodes, true), 'REMOTE zone found');

// Zone 1 details
$metroZone = null;
foreach ($zones as $z) {
    if ($z['zone_code'] === 'METRO') {
        $metroZone = $z;
        break;
    }
}
test('METRO zone has mapped PIN codes', $metroZone && $metroZone['pincode_count'] > 15, "pins count = {$metroZone['pincode_count']}");

// Test getZone($id)
$firstZone = ShippingZone::getZone($metroZone['id']);
test('getZone returns single zone with associated rates', is_array($firstZone) && isset($firstZone['rates']) && count($firstZone['rates']) >= 2, "rates count = " . count($firstZone['rates']));

// Test createZone()
$testZoneId = ShippingZone::createZone([
    'name'        => 'Custom Test Express Zone',
    'zone_code'   => 'TEST_EXP',
    'description' => 'Temporary automated testing territory',
    'pincodes'    => '110001, 110001, 110016, 560001, 560001', // duplicate pins to test cleaning
    'is_active'   => 1,
]);
test('createZone creates a new delivery zone', $testZoneId > 0, "created zone id = $testZoneId");

$fetchedTestZone = ShippingZone::getZone($testZoneId);
test('createZone cleans and deduplicates mapped PIN codes', $fetchedTestZone['pincode_count'] === 3, "expected 3 unique pins, got {$fetchedTestZone['pincode_count']}");

// Test updateZone()
$updateSuccess = ShippingZone::updateZone($testZoneId, [
    'name'        => 'Updated Custom Express Zone',
    'zone_code'   => 'TEST_UPD',
    'description' => 'Updated territory explanation',
    'pincodes'    => '400001, 400050',
    'is_active'   => 1,
]);
test('updateZone updates zone name and pincodes', $updateSuccess, 'zone updated');
$updatedTestZone = ShippingZone::getZone($testZoneId);
test('Updated zone reflects new code and name', $updatedTestZone['zone_code'] === 'TEST_UPD' && $updatedTestZone['pincode_count'] === 2, 'code = TEST_UPD, pins = 2');

// Test toggleZone()
ShippingZone::toggleZone($testZoneId, false);
$deactivatedZone = ShippingZone::getZone($testZoneId);
test('toggleZone deactivates zone', !$deactivatedZone['is_active'], 'is_active = false');

ShippingZone::toggleZone($testZoneId, true);
$reactivatedZone = ShippingZone::getZone($testZoneId);
test('toggleZone reactivates zone', $reactivatedZone['is_active'], 'is_active = true');

// Test deleteZone()
$deleteSuccess = ShippingZone::deleteZone($testZoneId);
test('deleteZone removes zone from database', $deleteSuccess, 'zone deleted');
$deletedZoneCheck = ShippingZone::getZone($testZoneId);
test('Deleted zone no longer exists', $deletedZoneCheck === null, 'zone is null');

// =============================================================================
// SUITE 2: Shipping Rates & Weight Slabs Matrix
// =============================================================================
echo "\n--- Suite 2: Shipping Rates & Weight Slabs Matrix ---\n";

$rates = ShippingZone::getRates();
test('getRates returns populated list of rate slabs', is_array($rates) && count($rates) >= 6, "rates count = " . count($rates));

$sampleRate = $rates[0];
test('Rate slab contains required fields (method, flat_rate, weight_from_g, weight_to_g, estimated_days)',
    isset($sampleRate['id'], $sampleRate['zone_id'], $sampleRate['method'], $sampleRate['flat_rate'], $sampleRate['estimated_days']),
    "method = {$sampleRate['method']}, flat = ₹{$sampleRate['flat_rate']}"
);

// Test createRate()
$metroId = $metroZone['id'];
$newRateId = ShippingZone::createRate([
    'zone_id'                => $metroId,
    'method'                 => 'express',
    'title'                  => 'VIP Same-Day Helicopter Delivery',
    'weight_from_g'          => 0,
    'weight_to_g'            => 10000,
    'flat_rate'              => 499.00,
    'free_above_order_value' => 15000.00,
    'estimated_days'         => 'Same Day (4 Hours)',
    'is_active'              => 1,
]);
test('createRate adds new rate slab', $newRateId > 0, "new rate id = $newRateId");

$fetchedRate = ShippingZone::getRate($newRateId);
test('getRate retrieves created rate slab details', $fetchedRate['flat_rate'] === 499.00 && $fetchedRate['title'] === 'VIP Same-Day Helicopter Delivery', 'rate verified');

// Test updateRate()
$updateRateSuccess = ShippingZone::updateRate($newRateId, [
    'zone_id'                => $metroId,
    'method'                 => 'express',
    'title'                  => 'VIP Same-Day Express Delivery',
    'weight_from_g'          => 0,
    'weight_to_g'            => 10000,
    'flat_rate'              => 399.00,
    'free_above_order_value' => 12000.00,
    'estimated_days'         => 'Within 6 Hours',
    'is_active'              => 1,
]);
test('updateRate modifies rate slab pricing', $updateRateSuccess, 'rate updated');
$updatedRate = ShippingZone::getRate($newRateId);
test('Updated rate matches new flat fee ₹399', $updatedRate['flat_rate'] === 399.00, "flat = ₹{$updatedRate['flat_rate']}");

// Test toggleRate()
ShippingZone::toggleRate($newRateId, false);
$deactivatedRate = ShippingZone::getRate($newRateId);
test('toggleRate deactivates rate slab', !$deactivatedRate['is_active'], 'is_active = false');

// Test deleteRate()
$deleteRateSuccess = ShippingZone::deleteRate($newRateId);
test('deleteRate removes rate slab from database', $deleteRateSuccess, 'rate deleted');
$deletedRateCheck = ShippingZone::getRate($newRateId);
test('Deleted rate no longer exists', $deletedRateCheck === null, 'rate is null');

// =============================================================================
// SUITE 3: Global Shipping Policy & site_settings Synchronization
// =============================================================================
echo "\n--- Suite 3: Global Shipping Policy & site_settings Sync ---\n";

$globalSettings = ShippingZone::getGlobalSettings();
test('getGlobalSettings returns valid configuration array', is_array($globalSettings) && isset($globalSettings['free_shipping_threshold']), "threshold = ₹{$globalSettings['free_shipping_threshold']}");

// Update settings
$newPolicy = [
    'free_shipping_threshold'    => 3499.00,
    'default_shipping_fee'       => 160.00,
    'default_express_fee'        => 280.00,
    'express_shipping_enabled'   => 1,
    'same_day_delivery_enabled'  => 1,
    'luxury_packaging_fee'       => 50.00,
];
$policyUpdated = ShippingZone::updateGlobalSettings($newPolicy);
test('updateGlobalSettings saves storewide shipping rules', $policyUpdated, 'policy saved');

$reloadedSettings = ShippingZone::getGlobalSettings();
test('free_shipping_threshold updated to ₹3,499', $reloadedSettings['free_shipping_threshold'] === 3499.00, "threshold = ₹{$reloadedSettings['free_shipping_threshold']}");
test('default_shipping_fee updated to ₹160', $reloadedSettings['default_shipping_fee'] === 160.00, "fee = ₹{$reloadedSettings['default_shipping_fee']}");
test('luxury_packaging_fee updated to ₹50', $reloadedSettings['luxury_packaging_fee'] === 50.00, "packaging = ₹{$reloadedSettings['luxury_packaging_fee']}");

// Reset back to standard default
ShippingZone::updateGlobalSettings([
    'free_shipping_threshold'    => 2999.00,
    'default_shipping_fee'       => 150.00,
    'default_express_fee'        => 250.00,
    'express_shipping_enabled'   => 1,
    'same_day_delivery_enabled'  => 0,
    'luxury_packaging_fee'       => 0.00,
]);

// =============================================================================
// SUITE 4: Carrier Partners & Tracking Profiles
// =============================================================================
echo "\n--- Suite 4: Carrier Partners & Tracking Profiles ---\n";

$couriers = ShippingZone::getCourierPartners();
test('getCourierPartners returns carrier catalog', is_array($couriers) && count($couriers) >= 4, "courier count = " . count($couriers));
test('BlueDart is configured in carrier catalog', isset($couriers['bluedart']), 'bluedart found');
test('Delhivery is configured in carrier catalog', isset($couriers['delhivery']), 'delhivery found');
test('Shiprocket is configured in carrier catalog', isset($couriers['shiprocket']), 'shiprocket found');
test('DTDC is configured in carrier catalog', isset($couriers['dtdc']), 'dtdc found');

// Update carrier profile
$updateCourierSuccess = ShippingZone::updateCourierPartner('bluedart', [
    'name'         => 'BlueDart Premier Aviation',
    'tracking_url' => 'https://www.bluedart.com/main-track?awb={AWB}',
    'is_active'    => 1,
    'is_default'   => 1,
]);
test('updateCourierPartner saves updated carrier profile', $updateCourierSuccess, 'carrier saved');
$reloadedCouriers = ShippingZone::getCourierPartners();
test('BlueDart tracking URL template updated', $reloadedCouriers['bluedart']['tracking_url'] === 'https://www.bluedart.com/main-track?awb={AWB}', 'tracking template verified');
test('BlueDart is marked as default carrier', !empty($reloadedCouriers['bluedart']['is_default']), 'is_default = true');

// =============================================================================
// SUITE 5: Live Rate & Pincode Calculator Engine
// =============================================================================
echo "\n--- Suite 5: Live Rate & Pincode Calculator Engine ---\n";

// 1. Invalid PIN length
$invCalc = ShippingZone::calculateShipping('12345', 1000.0, 500);
test('Calculator rejects non-6-digit PIN', !$invCalc['valid'], $invCalc['message']);

// 2. Metro PIN calculation (Mumbai 400001)
$metroCalc = ShippingZone::calculateShipping('400001', 1500.0, 800);
test('Calculator detects Metro Zone for 400001', $metroCalc['valid'] && $metroCalc['zone']['zone_code'] === 'METRO', "zone = {$metroCalc['zone']['name']}");
test('Calculator provides standard and express tiers for Metro', count($metroCalc['methods']) >= 2, "methods count = " . count($metroCalc['methods']));

// 3. Free shipping threshold qualification
$freeCalc = ShippingZone::calculateShipping('400001', 3500.0, 800); // 3500 >= 2999 threshold
$hasFree = false;
foreach ($freeCalc['methods'] as $m) {
    if ($m['is_free'] && $m['fee'] === 0.0) {
        $hasFree = true;
        break;
    }
}
test('Order above threshold (₹3,500 >= ₹2,999) qualifies for Free Shipping (₹0 fee)', $hasFree, 'free shipping applied');

// 4. Threshold progression message when below limit
$belowCalc = ShippingZone::calculateShipping('400001', 1500.0, 800);
test('Calculator returns informative threshold progression message', strpos($belowCalc['threshold_message'], 'Free Luxury Delivery') !== false, $belowCalc['threshold_message']);

// 5. Remote Zone calculation (Srinagar 190001)
$remoteCalc = ShippingZone::calculateShipping('190001', 1000.0, 500);
test('Calculator detects Remote Zone for 190001', $remoteCalc['valid'] && $remoteCalc['zone']['zone_code'] === 'REMOTE', "zone = {$remoteCalc['zone']['name']}");

// 6. Unmapped PIN fallback to Rest of India (999999)
$roiCalc = ShippingZone::calculateShipping('999999', 1200.0, 500);
test('Calculator falls back to Rest of India for unmapped PIN 999999', $roiCalc['valid'] && $roiCalc['zone']['zone_code'] === 'ROI', "zone = {$roiCalc['zone']['name']}");

// 7. Cash on Delivery cross-reference
test('Calculator cross-references COD eligibility seamlessly', isset($roiCalc['cod']['eligible']), "COD status = " . ($roiCalc['cod']['eligible'] ? 'Eligible' : 'Not Eligible'));

// =============================================================================
// SUITE 6: Executive Shipping KPIs
// =============================================================================
echo "\n--- Suite 6: Executive Shipping KPIs ---\n";

$kpis = ShippingZone::getShippingKPIs();
test('getShippingKPIs returns valid metrics structure', is_array($kpis) && isset($kpis['active_zones'], $kpis['free_shipping_limit']), 'structure valid');
test('active_zones is positive integer', $kpis['active_zones'] >= 4, "active zones = {$kpis['active_zones']}");
test('total_mapped_pincodes is positive integer', $kpis['total_mapped_pincodes'] >= 40, "mapped pins = {$kpis['total_mapped_pincodes']}");
test('active_rates is positive integer', $kpis['active_rates'] >= 6, "active rates = {$kpis['active_rates']}");
test('free_shipping_limit equals ₹2,999', $kpis['free_shipping_limit'] === 2999, "limit = ₹{$kpis['free_shipping_limit']}");

// =============================================================================
// SUITE 7: Controller, Routes, Admin View Rendering & Navigation
// =============================================================================
echo "\n--- Suite 7: Controller, Routes, Admin View Rendering & Navigation ---\n";

test('ShippingController class exists', class_exists(ShippingController::class), 'controller exists');

$expectedMethods = ['index', 'saveZone', 'deleteZone', 'toggleZone', 'saveRate', 'deleteRate', 'toggleRate', 'saveSettings', 'saveCourier', 'calculate'];
$allMethodsExist = true;
foreach ($expectedMethods as $m) {
    if (!method_exists(ShippingController::class, $m)) {
        $allMethodsExist = false;
        break;
    }
}
test('ShippingController implements all 10 required actions', $allMethodsExist, 'methods verified');

// Check routes in app/routes.php
$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');
$hasAllRoutes = strpos($routesContent, 'admin/shipping-pincodes') !== false
             && strpos($routesContent, 'ShippingController@index') !== false
             && strpos($routesContent, 'ShippingController@saveZone') !== false
             && strpos($routesContent, 'ShippingController@deleteZone') !== false
             && strpos($routesContent, 'ShippingController@toggleZone') !== false
             && strpos($routesContent, 'ShippingController@saveRate') !== false
             && strpos($routesContent, 'ShippingController@deleteRate') !== false
             && strpos($routesContent, 'ShippingController@toggleRate') !== false
             && strpos($routesContent, 'ShippingController@saveSettings') !== false
             && strpos($routesContent, 'ShippingController@saveCourier') !== false
             && strpos($routesContent, 'ShippingController@calculate') !== false;
test('app/routes.php registers all 11 Shipping & Pincodes endpoints', $hasAllRoutes, 'routes registered');

// Test view rendering across all tabs
$controller = new ShippingController();
$tabs = ['zones', 'rates', 'settings', 'couriers', 'calculator'];

foreach ($tabs as $tab) {
    $_GET['tab'] = $tab;
    ob_start();
    try {
        $controller->index();
        $html = ob_get_clean();
        $len = strlen($html);
        $noErrors = (stripos($html, 'Fatal error') === false && stripos($html, 'Parse error') === false && stripos($html, 'Warning:') === false);
        test("Shipping & Pincodes tab '{$tab}' renders clean HTML ({$len} bytes)", $len > 1000 && $noErrors, "tab = $tab");
    } catch (Throwable $e) {
        ob_end_clean();
        test("Shipping & Pincodes tab '{$tab}' renders clean HTML", false, "threw: " . $e->getMessage());
    }
}

// Check sidebar contains the live navigation item
$sidebarContent = file_get_contents(__DIR__ . '/../app/Views/admin/layouts/sidebar.php');
$hasSidebarItem = strpos($sidebarContent, 'admin/shipping-pincodes') !== false
               && strpos($sidebarContent, 'badge-live') !== false;
test('Sidebar navigation contains Shipping & Pincodes link with Live badge', $hasSidebarItem, 'sidebar link present');

// =============================================================================
// TEST SUMMARY & FINAL VERDICT
// =============================================================================
echo "\n====================================================\n";
echo " Test Results Summary\n";
echo "====================================================\n";
echo " Total Tests: " . count($tests) . "\n";
echo " \033[32mPassed: $pass\033[0m\n";
if ($fail > 0) {
    echo " \033[31mFailed: $fail\033[0m\n";
} else {
    echo " \033[32mALL TESTS PASSED (100% SUCCESS RATE)!\033[0m\n";
}
echo "====================================================\n\n";

if ($fail > 0) {
    exit(1);
}
exit(0);
