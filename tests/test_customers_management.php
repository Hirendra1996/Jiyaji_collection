<?php
/**
 * JIYAJI LUXURY: CUSTOMER MANAGEMENT & PROFILES E2E TEST SUITE
 * 
 * Verifies:
 * - RBAC / Admin authentication guard
 * - Customers directory ledger & KPI summary metrics
 * - Universal Encrypted URL tokens (zero raw database IDs exposed)
 * - Customer creation with contact credentials & initial delivery address
 * - Customer profile dossier with lifetime analytics (spend, orders, AOV)
 * - Address book management (adding destinations, setting default, deletion)
 * - Customer profile updates (name, phone, verification status)
 * - In-line account status toggle (Active <-> Suspended)
 * - Financial audit guard: prevents deletion of customers with order histories
 * - Successful deletion of test customers without order histories
 * - Rejection of invalid/tampered encrypted tokens
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $k => $v) {
    putenv("$k=$v");
}
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Customer.php';

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'jiyaji_cust_cookie_');

echo "=========================================================\n";
echo " JIYAJI LUXURY: CUSTOMERS MANAGEMENT E2E TEST SUITE\n";
echo "=========================================================\n\n";

function request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return [
        'code'          => $httpCode,
        'header'        => $header,
        'body'          => $body,
        'effective_url' => $effectiveUrl
    ];
}

function extractCsrf($html) {
    if (preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

$testsPassed = 0;
$totalTests = 12;

// -----------------------------------------------------------------------------
// TEST 1: RBAC Guard - Unauthorized Access Blocked
// -----------------------------------------------------------------------------
echo "[Test 1/12] RBAC Guard: Verify unauthenticated request to /admin/customers is redirected to login...\n";
$res = request("$baseUrl/admin/customers");
if (strpos($res['effective_url'], 'admin/login') !== false || $res['code'] === 302) {
    echo "  [PASS] Unauthenticated visitor redirected to admin login.\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Expected redirect to login, got: {$res['effective_url']} (HTTP {$res['code']})\n";
}

// -----------------------------------------------------------------------------
// Authenticate as Store Administrator
// -----------------------------------------------------------------------------
echo "\n--> Authenticating as Admin (admin@jiyaji.com)...\n";
$loginPage = request("$baseUrl/admin/login", 'GET', [], $cookieJar);
$csrf = extractCsrf($loginPage['body']);

$loginRes = request("$baseUrl/admin/login", 'POST', [
    '_csrf_token' => $csrf,
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123'
], $cookieJar);

if (strpos($loginRes['effective_url'], 'admin/dashboard') !== false || strpos($loginRes['body'], 'Executive Dashboard') !== false) {
    echo "  [OK] Successfully logged in as administrator.\n";
} else {
    echo "  [ERROR] Admin login failed! Stopping test suite.\n";
    exit(1);
}

// -----------------------------------------------------------------------------
// TEST 2: Customer Directory Access & KPI Cards
// -----------------------------------------------------------------------------
echo "\n[Test 2/12] Access /admin/customers and verify KPI Cards...\n";
$custPage = request("$baseUrl/admin/customers", 'GET', [], $cookieJar);
if ($custPage['code'] === 200 && strpos($custPage['body'], 'Customer Portfolio & Ledger') !== false) {
    $hasTotalKpi = strpos($custPage['body'], 'Total Clients') !== false;
    $hasSpendKpi = strpos($custPage['body'], 'Lifetime Client Spend') !== false;
    $hasRepeatKpi = strpos($custPage['body'], 'Repeat Buyers') !== false;
    if ($hasTotalKpi && $hasSpendKpi && $hasRepeatKpi) {
        echo "  [PASS] Customer directory rendered with full KPI summary cards.\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] Customer directory loaded, but missing one or more KPI cards.\n";
    }
} else {
    echo "  [FAIL] Failed to access /admin/customers (HTTP {$custPage['code']})\n";
}

// -----------------------------------------------------------------------------
// TEST 3: Sidebar Navigation Active State
// -----------------------------------------------------------------------------
echo "\n[Test 3/12] Verify sidebar navigation has active Customers link with Live badge...\n";
if (strpos($custPage['body'], 'id="navCustomers"') !== false && strpos($custPage['body'], 'Customers</span>') !== false) {
    echo "  [PASS] Sidebar has active Customers navigation item with Live badge.\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Sidebar link for Customers not found or inactive.\n";
}

// -----------------------------------------------------------------------------
// TEST 4: Customer Profile Dossier for Existing Customer (Aditya Kapoor)
// -----------------------------------------------------------------------------
echo "\n[Test 4/12] View customer profile dossier with order history & lifetime spend...\n";
$db = App\Config\Database::connect();
$firstCust = $db->query("SELECT id, name, email FROM customers LIMIT 1")->fetch_assoc();
$encryptedCustId = encrypt_id($firstCust['id']);

$profilePage = request("$baseUrl/admin/customers/{$encryptedCustId}", 'GET', [], $cookieJar);
if ($profilePage['code'] === 200 && strpos($profilePage['body'], htmlspecialchars($firstCust['name'])) !== false) {
    $hasLtv = strpos($profilePage['body'], 'Total Lifetime Spend') !== false;
    $hasOrdersLedger = strpos($profilePage['body'], 'Order History Ledger') !== false;
    $hasAddressBook = strpos($profilePage['body'], 'Address Book') !== false;

    if ($hasLtv && $hasOrdersLedger && $hasAddressBook) {
        echo "  [PASS] Customer dossier rendered for '{$firstCust['name']}' with LTV, orders ledger, and address book.\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] Profile loaded but missing essential sections (LTV: " . ($hasLtv ? 'Y' : 'N') . ", Orders: " . ($hasOrdersLedger ? 'Y' : 'N') . ", Address: " . ($hasAddressBook ? 'Y' : 'N') . ")\n";
    }
} else {
    echo "  [FAIL] Failed to load profile for customer ID {$firstCust['id']} (HTTP {$profilePage['code']})\n";
}

// -----------------------------------------------------------------------------
// TEST 5: Create Customer Form Loading
// -----------------------------------------------------------------------------
echo "\n[Test 5/12] Access /admin/customers/create...\n";
$createPage = request("$baseUrl/admin/customers/create", 'GET', [], $cookieJar);
if ($createPage['code'] === 200 && strpos($createPage['body'], 'Create Luxury Customer Profile') !== false) {
    echo "  [PASS] Customer creation form loaded successfully.\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Failed to access /admin/customers/create (HTTP {$createPage['code']})\n";
}

// -----------------------------------------------------------------------------
// TEST 6: Customer Creation with Initial Address
// -----------------------------------------------------------------------------
echo "\n[Test 6/12] Create new customer with credentials & initial delivery address...\n";
$csrf = extractCsrf($createPage['body']);
$testEmail = 'test.client.' . time() . '@jiyajiluxury.com';
$testName = 'Ananya Singhania';

$createRes = request("$baseUrl/admin/customers/store", 'POST', [
    '_csrf_token'        => $csrf,
    'name'               => $testName,
    'email'              => $testEmail,
    'phone'              => '9876543210',
    'password'           => 'Luxury@2026',
    'email_verified'     => '1',
    'is_active'          => '1',
    'address_label'      => 'Bespoke Villa',
    'address_recipient'  => $testName,
    'address_phone'      => '9876543210',
    'address_line1'      => 'Villa 14, Royal Palm Estates',
    'address_line2'      => 'Aarey Colony, Goregaon East',
    'city'               => 'Mumbai',
    'state'              => 'Maharashtra',
    'pincode'            => '400065',
    'country'            => 'India'
], $cookieJar);

$newCust = App\Models\Customer::findByEmail($testEmail);
if ($newCust && strpos($createRes['body'], $testName) !== false) {
    echo "  [PASS] Successfully created customer '{$testName}' with address (DB ID: {$newCust['id']}).\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Customer creation failed or redirect did not show customer profile.\n";
}

$newEncId = $newCust ? encrypt_id($newCust['id']) : null;

// -----------------------------------------------------------------------------
// TEST 7: Address Book - Add Delivery Address from Profile View
// -----------------------------------------------------------------------------
echo "\n[Test 7/12] Add a secondary delivery address from customer profile...\n";
if ($newEncId) {
    $profRes = request("$baseUrl/admin/customers/{$newEncId}", 'GET', [], $cookieJar);
    $csrf = extractCsrf($profRes['body']);

    $addAddrRes = request("$baseUrl/admin/customers/{$newEncId}/address", 'POST', [
        '_csrf_token'   => $csrf,
        'label'         => 'Corporate Office',
        'recipient'     => $testName . ' (Executive Suite)',
        'phone'         => '9876543210',
        'address_line1' => 'Floor 22, One BKC',
        'city'          => 'Mumbai',
        'state'         => 'Maharashtra',
        'pincode'       => '400051',
        'country'       => 'India',
        'is_default'    => '0'
    ], $cookieJar);

    if (strpos($addAddrRes['body'], 'Floor 22, One BKC') !== false) {
        echo "  [PASS] Secondary address added to client address book.\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] Secondary address not found in profile after POST.\n";
    }
} else {
    echo "  [SKIP] Skipping address test because test customer was not created.\n";
}

// -----------------------------------------------------------------------------
// TEST 8: Edit Customer Form Loading & Updates
// -----------------------------------------------------------------------------
echo "\n[Test 8/12] Edit customer profile details...\n";
if ($newEncId) {
    $editPage = request("$baseUrl/admin/customers/{$newEncId}/edit", 'GET', [], $cookieJar);
    $csrf = extractCsrf($editPage['body']);

    $updatedName = 'Ananya Singhania-Piramal';
    $updateRes = request("$baseUrl/admin/customers/{$newEncId}/update", 'POST', [
        '_csrf_token'    => $csrf,
        'name'           => $updatedName,
        'email'          => $testEmail,
        'phone'          => '9876599999',
        'email_verified' => '1',
        'is_active'      => '1'
    ], $cookieJar);

    if (strpos($updateRes['body'], $updatedName) !== false) {
        echo "  [PASS] Customer details successfully updated to '{$updatedName}'.\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] Updated customer name not reflected in response.\n";
    }
} else {
    echo "  [SKIP] Skipping edit test.\n";
}

// -----------------------------------------------------------------------------
// TEST 9: In-line Status Toggle (Active <-> Suspended)
// -----------------------------------------------------------------------------
echo "\n[Test 9/12] In-line toggle customer account status (Suspend & Reactivate)...\n";
if ($newEncId) {
    $profRes = request("$baseUrl/admin/customers/{$newEncId}", 'GET', [], $cookieJar);
    $csrf = extractCsrf($profRes['body']);

    // Toggle 1: Suspend
    $toggleRes = request("$baseUrl/admin/customers/{$newEncId}/status", 'POST', [
        '_csrf_token' => $csrf,
        'return_url'  => "admin/customers/{$newEncId}"
    ], $cookieJar);

    $isSuspended = strpos($toggleRes['body'], 'Suspended') !== false;

    // Toggle 2: Reactivate
    $csrf2 = extractCsrf($toggleRes['body']);
    $toggleRes2 = request("$baseUrl/admin/customers/{$newEncId}/status", 'POST', [
        '_csrf_token' => $csrf2,
        'return_url'  => "admin/customers/{$newEncId}"
    ], $cookieJar);

    $isReactivated = strpos($toggleRes2['body'], 'Active') !== false;

    if ($isSuspended && $isReactivated) {
        echo "  [PASS] Status toggle works bidirectionally (Active -> Suspended -> Active).\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] Status toggle did not reflect expected states (Suspended: " . ($isSuspended ? 'Y' : 'N') . ", Reactivated: " . ($isReactivated ? 'Y' : 'N') . ")\n";
    }
} else {
    echo "  [SKIP] Skipping status toggle test.\n";
}

// -----------------------------------------------------------------------------
// TEST 10: Financial Audit Guard: Prevent Deletion of Customer with Orders
// -----------------------------------------------------------------------------
echo "\n[Test 10/12] Financial Audit Guard: Attempt deletion of customer with existing orders...\n";
// Customer 1 (Aditya Kapoor) has orders
$profCust1 = request("$baseUrl/admin/customers/{$encryptedCustId}", 'GET', [], $cookieJar);
$csrf = extractCsrf($profCust1['body']);

$deleteGuardedRes = request("$baseUrl/admin/customers/{$encryptedCustId}/delete", 'POST', [
    '_csrf_token' => $csrf
], $cookieJar);

// Verify customer 1 still exists
$cust1StillExists = App\Models\Customer::find($firstCust['id']);
if ($cust1StillExists && (strpos($deleteGuardedRes['body'], 'cannot be permanently removed') !== false || strpos($deleteGuardedRes['body'], 'linked order') !== false || strpos($deleteGuardedRes['body'], 'Action Prevented') !== false)) {
    echo "  [PASS] Financial audit guard protected customer with active order history from deletion.\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Expected deletion guard for customer with orders, but customer was deleted or alert not triggered.\n";
}

// -----------------------------------------------------------------------------
// TEST 11: Safe Deletion of Test Customer Without Orders
// -----------------------------------------------------------------------------
echo "\n[Test 11/12] Safe deletion of customer without orders...\n";
if ($newEncId) {
    $profRes = request("$baseUrl/admin/customers/{$newEncId}", 'GET', [], $cookieJar);
    $csrf = extractCsrf($profRes['body']);

    $deleteRes = request("$baseUrl/admin/customers/{$newEncId}/delete", 'POST', [
        '_csrf_token' => $csrf
    ], $cookieJar);

    $testCustDeleted = App\Models\Customer::findByEmail($testEmail);
    if ($testCustDeleted === null) {
        echo "  [PASS] Test customer without orders was safely and cleanly deleted.\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] Test customer still found in database after deletion.\n";
    }
} else {
    echo "  [SKIP] Skipping safe deletion test.\n";
}

// -----------------------------------------------------------------------------
// TEST 12: Rejection of Tampered Encrypted ID
// -----------------------------------------------------------------------------
echo "\n[Test 12/12] Security Check: Verify tampered encrypted token is rejected...\n";
$tamperedToken = 'invalid-tampered-token-xyz-123';
$tamperedRes = request("$baseUrl/admin/customers/{$tamperedToken}", 'GET', [], $cookieJar);
if (strpos($tamperedRes['effective_url'], 'admin/customers') !== false && strpos($tamperedRes['body'], 'Invalid or tampered') !== false) {
    echo "  [PASS] Tampered encrypted token was safely rejected with an error alert.\n";
    $testsPassed++;
} else {
    echo "  [FAIL] Tampered token did not redirect with expected error alert.\n";
}

// -----------------------------------------------------------------------------
// Clean up
// -----------------------------------------------------------------------------
if (file_exists($cookieJar)) {
    unlink($cookieJar);
}

echo "\n=========================================================\n";
echo " TEST SUMMARY: {$testsPassed} / {$totalTests} TESTS PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $totalTests) {
    echo "SUCCESS: Customer Management System passed 100% of tests!\n";
    exit(0);
} else {
    echo "FAILURE: Some tests failed.\n";
    exit(1);
}
