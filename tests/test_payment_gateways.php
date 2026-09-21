<?php
/**
 * Payment Gateways & COD Rules — Automated Test Suite
 * Validates PaymentGateway Model, Razorpay API credentials, COD anti-fraud limits,
 * Pincode Serviceability Engine, VIP Bank Wire transfers, Transactions Ledger, and Admin Views.
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
$_SESSION['admin'] = ['id' => 1, 'name' => 'Admin Tester', 'email' => 'admin@jiyaji.com', 'role' => 'super_admin'];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/PaymentGateway.php';
require_once __DIR__ . '/../app/Controllers/Admin/PaymentGatewayController.php';

use App\Models\PaymentGateway;
use App\Controllers\Admin\PaymentGatewayController;
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
echo " Payment Gateways & COD Rules Test Suite\n";
echo "====================================================\n\n";

// =============================================================================
// SUITE 1: Gateway Retrieval & Default Configurations
// =============================================================================
echo "--- Suite 1: Gateway Retrieval & Configuration Defaults ---\n";

PaymentGateway::ensureDefaults();
$gateways = PaymentGateway::getGateways();

test('getGateways returns non-empty array', is_array($gateways) && count($gateways) >= 3, "gateways count = " . count($gateways));
test('Razorpay gateway exists in catalog', isset($gateways['razorpay']), 'razorpay found');
test('COD gateway exists in catalog', isset($gateways['cod']), 'cod found');
test('VIP Bank Transfer gateway exists in catalog', isset($gateways['bank_transfer']), 'bank_transfer found');

$rzp = PaymentGateway::getGateway('razorpay');
test('getGateway(razorpay) returns valid config array', is_array($rzp) && isset($rzp['config']['key_id']), 'key_id present');
test('Razorpay has active payment rails defined', isset($rzp['config']['rails']['upi']), 'UPI rail defined');

$cod = PaymentGateway::getGateway('cod');
test('getGateway(cod) returns min order value and whitelist', isset($cod['cod_min_order_value']) && isset($cod['cod_pincode_whitelist']), 'COD thresholds');

$bank = PaymentGateway::getGateway('bank_transfer');
test('getGateway(bank_transfer) returns bank details', is_array($bank) && isset($bank['config']['bank_name']), 'bank_name present');

$kpis = PaymentGateway::getGatewayKPIs();
test('getGatewayKPIs returns valid metrics', is_array($kpis) && isset($kpis['active_gateways'], $kpis['success_rate']), "active = {$kpis['active_gateways']}, rate = {$kpis['success_rate']}%");
test('Gateway KPI success rate is between 0 and 100', $kpis['success_rate'] >= 0.0 && $kpis['success_rate'] <= 100.0, "rate = {$kpis['success_rate']}%");

// =============================================================================
// SUITE 2: Razorpay API Credentials, Rails & Validation
// =============================================================================
echo "\n--- Suite 2: Razorpay Credentials, Rails & Validation ---\n";

// Key validation tests
$validTest = PaymentGateway::validateKeys('rzp_test_9JiyajiLuxury2026', 'sec_test_abc123');
test('validateKeys recognizes rzp_test_ as test mode', $validTest['valid'] && $validTest['mode'] === 'test', $validTest['message']);

$validLive = PaymentGateway::validateKeys('rzp_live_9JiyajiLuxuryProd', 'sec_live_xyz987');
test('validateKeys recognizes rzp_live_ as live mode', $validLive['valid'] && $validLive['mode'] === 'live', $validLive['message']);

$invalidPrefix = PaymentGateway::validateKeys('invalid_key_1234567890', 'secret');
test('validateKeys rejects invalid key prefix', !$invalidPrefix['valid'], $invalidPrefix['message']);

$emptyKey = PaymentGateway::validateKeys('', '');
test('validateKeys rejects empty key ID', !$emptyKey['valid'], $emptyKey['message']);

// Update Razorpay settings
$updateData = [
    'is_enabled'     => '1',
    'mode'           => 'test',
    'key_id'         => 'rzp_test_LXAutomatedTestKey',
    'key_secret'     => 'sec_LXAutomatedSecret99',
    'webhook_secret' => 'whsec_test_7788',
    'auto_capture'   => '1',
    'rail_upi'       => '1',
    'rail_cards'     => '1',
    'rail_netbanking'=> '1',
    'rail_wallets'   => '0',
    'rail_emi'       => '1',
];
$rzpUpdated = PaymentGateway::updateRazorpay($updateData);
test('updateRazorpay executes successfully', $rzpUpdated, 'settings saved');

$verifyRzp = PaymentGateway::getGateway('razorpay');
test('Razorpay key_id matches updated value', $verifyRzp['config']['key_id'] === 'rzp_test_LXAutomatedTestKey', 'key_id synced');
test('Razorpay auto_capture is enabled', $verifyRzp['config']['auto_capture'] === 1, 'auto_capture = 1');
test('Razorpay wallet rail was disabled', empty($verifyRzp['config']['rails']['wallets']), 'wallets = false');

// Preserving masked secret
$maskedUpdate = [
    'is_enabled'     => '1',
    'mode'           => 'test',
    'key_id'         => 'rzp_test_LXAutomatedTestKey',
    'key_secret'     => '••••••••', // Masked secret submitted
    'webhook_secret' => 'whsec_test_7788',
    'auto_capture'   => '1',
    'rail_upi'       => '1',
    'rail_cards'     => '1',
    'rail_netbanking'=> '1',
];
PaymentGateway::updateRazorpay($maskedUpdate);
$verifyMasked = PaymentGateway::getGateway('razorpay');
test('updateRazorpay preserves existing secret when masked •••••••• is passed', $verifyMasked['config']['key_secret'] === 'sec_LXAutomatedSecret99', 'secret preserved');

// Verify site_settings sync
$db = Database::connect();
$setRes = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'razorpay_key_id'");
$settingVal = $setRes ? $setRes->fetch_row()[0] : '';
test('Razorpay key_id synchronized to site_settings table', $settingVal === 'rzp_test_LXAutomatedTestKey', "site_settings = $settingVal");

// =============================================================================
// SUITE 3: Cash on Delivery (COD) Anti-Fraud Rules
// =============================================================================
echo "\n--- Suite 3: Cash on Delivery (COD) Anti-Fraud Rules ---\n";

$codData = [
    'is_enabled'        => '1',
    'min_order_value'   => 1500.00,
    'max_order_value'   => 40000.00,
    'cod_fee'           => 75.00,
    'pincode_mode'      => 'whitelist',
    'pincode_whitelist' => "400001, 110001, 560001, 302001",
    'otp_verify'        => '1',
];
$codUpdated = PaymentGateway::updateCOD($codData);
test('updateCOD saves anti-fraud limits successfully', $codUpdated, 'COD rules saved');

$verifyCOD = PaymentGateway::getGateway('cod');
test('COD min order value is ₹1,500', (float)$verifyCOD['cod_min_order_value'] === 1500.00, "min = ₹{$verifyCOD['cod_min_order_value']}");
test('COD max order cap is ₹40,000', (float)$verifyCOD['config']['max_order_value'] === 40000.00, "max = ₹{$verifyCOD['config']['max_order_value']}");
test('COD fee is ₹75', (float)$verifyCOD['config']['cod_fee'] === 75.00, "fee = ₹{$verifyCOD['config']['cod_fee']}");
test('COD pincode mode is whitelist', $verifyCOD['config']['pincode_mode'] === 'whitelist', 'mode = whitelist');

// Verify site_settings sync for COD
$codSetRes = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'cod_min_order_value'");
$codSettingVal = $codSetRes ? $codSetRes->fetch_row()[0] : '';
test('COD min_order_value synchronized to site_settings table', (float)$codSettingVal === 1500.00, "site_settings = $codSettingVal");

// =============================================================================
// SUITE 4: Live COD Pincode Serviceability Engine
// =============================================================================
echo "\n--- Suite 4: Live COD Pincode Serviceability Engine ---\n";

// 1. Invalid PIN length
$invCheck = PaymentGateway::isPincodeEligibleForCOD('12345', 2000.00);
test('Pincode check rejects 5-digit PIN', !$invCheck['eligible'], $invCheck['reason']);

// 2. Below minimum order value
$minCheck = PaymentGateway::isPincodeEligibleForCOD('400001', 1000.00); // threshold is 1500
test('Pincode check rejects order below minimum threshold (₹1,000 < ₹1,500)', !$minCheck['eligible'], $minCheck['reason']);

// 3. Above maximum order cap (luxury insurance policy)
$maxCheck = PaymentGateway::isPincodeEligibleForCOD('400001', 65000.00); // cap is 40000
test('Pincode check rejects order exceeding maximum threshold (₹65,000 > ₹40,000)', !$maxCheck['eligible'], $maxCheck['reason']);

// 4. Whitelisted serviceable pincode with valid amount
$whiteValid = PaymentGateway::isPincodeEligibleForCOD('400001', 5000.00);
test('Pincode check approves whitelisted PIN (400001, ₹5,000)', $whiteValid['eligible'] && $whiteValid['fee'] === 75.0, "eligible = true, fee = ₹{$whiteValid['fee']}");

// 5. Non-whitelisted pincode in whitelist mode
$whiteBlocked = PaymentGateway::isPincodeEligibleForCOD('999999', 5000.00);
test('Pincode check rejects non-whitelisted PIN in whitelist mode', !$whiteBlocked['eligible'], $whiteBlocked['reason']);

// 6. Switch to All India mode
$codDataAllIndia = $codData;
$codDataAllIndia['pincode_mode'] = 'all';
PaymentGateway::updateCOD($codDataAllIndia);

$allIndiaCheck = PaymentGateway::isPincodeEligibleForCOD('999999', 5000.00);
test('Pincode check accepts any valid Indian PIN when pincode_mode = all', $allIndiaCheck['eligible'], $allIndiaCheck['reason']);

// 7. Master Disabled COD check
PaymentGateway::toggleGateway('cod', false);
$disabledCheck = PaymentGateway::isPincodeEligibleForCOD('400001', 5000.00);
test('Pincode check rejects when COD master status is disabled', !$disabledCheck['eligible'], $disabledCheck['reason']);

// Re-enable COD
PaymentGateway::toggleGateway('cod', true);

// =============================================================================
// SUITE 5: VIP Concierge Bank Wire Transfer Configuration
// =============================================================================
echo "\n--- Suite 5: VIP Bank Wire Transfer Configuration ---\n";

$bankData = [
    'is_enabled'     => '1',
    'bank_name'      => 'HDFC Bank Luxury Premier',
    'account_name'   => 'Jiyaji Luxury Collections Private Limited',
    'account_number' => '50200098765432',
    'ifsc_code'      => 'HDFC0000123',
    'branch'         => 'Nariman Point, Mumbai',
    'upi_id'         => 'jiyajilx@hdfcbank',
    'instructions'   => 'Please include your bespoke order number in the NEFT narration.',
];
$bankUpdated = PaymentGateway::updateBankTransfer($bankData);
test('updateBankTransfer saves bank coordinates successfully', $bankUpdated, 'bank wire saved');

$verifyBank = PaymentGateway::getGateway('bank_transfer');
test('Bank name matches updated value', $verifyBank['config']['bank_name'] === 'HDFC Bank Luxury Premier', 'bank_name synced');
test('Account number matches updated value', $verifyBank['config']['account_number'] === '50200098765432', 'account_number synced');
test('IFSC code is capitalized', $verifyBank['config']['ifsc_code'] === 'HDFC0000123', 'IFSC = HDFC0000123');

// Toggle gateway test
PaymentGateway::toggleGateway('bank_transfer', false);
$disabledBank = PaymentGateway::getGateway('bank_transfer');
test('toggleGateway disables bank_transfer', !$disabledBank['is_enabled'], 'bank_transfer disabled');

PaymentGateway::toggleGateway('bank_transfer', true);
$enabledBank = PaymentGateway::getGateway('bank_transfer');
test('toggleGateway re-enables bank_transfer', $enabledBank['is_enabled'], 'bank_transfer enabled');

// =============================================================================
// SUITE 6: Payment Ledger & Webhook Audit Records
// =============================================================================
echo "\n--- Suite 6: Payment Transactions Ledger & Webhooks ---\n";

// Fetch an existing order ID
$orderRow = $db->query("SELECT id, order_number FROM orders ORDER BY id DESC LIMIT 1")->fetch_assoc();
$testOrderId = (int)($orderRow['id'] ?? 1);

// Record a new transaction attempt
$recordSuccess = PaymentGateway::recordTransaction([
    'order_id'           => $testOrderId,
    'gateway'            => 'razorpay',
    'gateway_order_id'   => 'order_test_auto_999',
    'gateway_payment_id' => 'pay_test_auto_999',
    'gateway_signature'  => 'sig_test_hash_auto',
    'amount'             => 12500.00,
    'currency'           => 'INR',
    'status'             => 'success',
    'webhook_payload'    => ['event' => 'payment.captured', 'id' => 'pay_test_auto_999']
]);
test('recordTransaction creates payment record', $recordSuccess, 'record inserted');

// Retrieve transactions
$txData = PaymentGateway::getRecentTransactions([], 1, 10);
test('getRecentTransactions returns paginated array', is_array($txData['transactions']) && count($txData['transactions']) > 0, "tx count = " . count($txData['transactions']));
test('Pagination metadata is well-formed', isset($txData['pagination']['total'], $txData['pagination']['current_page']), "total = {$txData['pagination']['total']}");

// Filter by gateway
$rzpOnly = PaymentGateway::getRecentTransactions(['gateway' => 'razorpay'], 1, 10);
$allRzp = true;
foreach ($rzpOnly['transactions'] as $t) {
    if ($t['gateway'] !== 'razorpay') { $allRzp = false; break; }
}
test('Filter by gateway=razorpay returns only razorpay records', $allRzp && count($rzpOnly['transactions']) > 0, 'gateway filter');

// Filter by status
$successOnly = PaymentGateway::getRecentTransactions(['status' => 'success'], 1, 10);
$allSuccess = true;
foreach ($successOnly['transactions'] as $t) {
    if ($t['status'] !== 'success') { $allSuccess = false; break; }
}
test('Filter by status=success returns only success records', $allSuccess && count($successOnly['transactions']) > 0, 'status filter');

// Search by payment ID
$searchRes = PaymentGateway::getRecentTransactions(['search' => 'pay_test_auto_999'], 1, 5);
test('Search by gateway_payment_id finds newly inserted transaction', count($searchRes['transactions']) >= 1, 'search working');

// =============================================================================
// SUITE 7: Controller, Routes, Admin View Rendering & Navigation
// =============================================================================
echo "\n--- Suite 7: Controller, Routes, Admin View Rendering & Navigation ---\n";

test('PaymentGatewayController class exists', class_exists(PaymentGatewayController::class), 'controller exists');

$expectedMethods = ['index', 'update', 'toggle', 'checkPincode', 'testConnection'];
$allMethodsExist = true;
foreach ($expectedMethods as $em) {
    if (!method_exists(PaymentGatewayController::class, $em)) {
        $allMethodsExist = false;
        break;
    }
}
test('PaymentGatewayController implements all 5 required actions', $allMethodsExist, 'index, update, toggle, checkPincode, testConnection');

$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');
$hasAllRoutes = strpos($routesContent, 'admin/payment-gateways') !== false
             && strpos($routesContent, 'PaymentGatewayController@index') !== false
             && strpos($routesContent, 'PaymentGatewayController@update') !== false
             && strpos($routesContent, 'PaymentGatewayController@toggle') !== false
             && strpos($routesContent, 'PaymentGatewayController@checkPincode') !== false
             && strpos($routesContent, 'PaymentGatewayController@testConnection') !== false;
test('app/routes.php registers all 6 Payment Gateway endpoints', $hasAllRoutes, 'routes registered');

$controller = new PaymentGatewayController();
$tabs = ['overview', 'razorpay', 'cod', 'bank_transfer', 'transactions'];

foreach ($tabs as $tab) {
    $_GET['tab'] = $tab;
    ob_start();
    try {
        $controller->index();
        $html = ob_get_clean();
        $len = strlen($html);
        $noErrors = (stripos($html, 'Fatal error') === false && stripos($html, 'Parse error') === false && stripos($html, 'Warning:') === false);
        test("Payment Gateways tab '{$tab}' renders clean HTML ({$len} bytes)", $len > 1000 && $noErrors, "tab = $tab");
    } catch (Throwable $e) {
        ob_end_clean();
        test("Payment Gateways tab '{$tab}' renders clean HTML", false, "threw: " . $e->getMessage());
    }
}

// Check sidebar file contains the live navigation item
$sidebarContent = file_get_contents(__DIR__ . '/../app/Views/admin/layouts/sidebar.php');
$hasSidebarItem = strpos($sidebarContent, 'admin/payment-gateways') !== false && strpos($sidebarContent, 'badge-live') !== false;
test('Sidebar navigation contains Payment Gateways link with Live badge', $hasSidebarItem, 'sidebar link present');

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
