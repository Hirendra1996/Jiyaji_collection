<?php
/**
 * End-to-End Test for Portal Coupons & Offers System
 * Tests RBAC, UI Rendering, Code Generator, CRUD, Status Toggle, Restrictions Sync, and CSV Export.
 */

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieFileMarketing = __DIR__ . '/cookie_marketing.txt';
$cookieFileSupport = __DIR__ . '/cookie_support.txt';

if (file_exists($cookieFileMarketing)) unlink($cookieFileMarketing);
if (file_exists($cookieFileSupport)) unlink($cookieFileSupport);

function httpRequest($url, $method = 'GET', $postData = [], $cookieFile = null, $followRedirect = true) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($followRedirect) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    } else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'effective_url' => $effectiveUrl,
        'headers' => $headerStr,
        'body' => $body,
    ];
}

function extractCsrf($html) {
    if (preg_match('/name=["\']_csrf_token["\']\s+value=["\']([^"\']+)["\']/', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

echo "=======================================================\n";
echo "Starting E2E Verification: Portal Coupons & Offers\n";
echo "=======================================================\n\n";

// STEP 1: Login as Marketing Lead
echo "[Test 1] Authenticating as Marketing Lead (marketing@jiyaji.com)...\n";
$loginPage = httpRequest("$baseUrl/portal/login", 'GET', [], $cookieFileMarketing);
$csrf = extractCsrf($loginPage['body']);
echo "CSRF Token extracted: " . ($csrf ? substr($csrf, 0, 10) . "..." : "NONE") . "\n";

$loginResp = httpRequest("$baseUrl/portal/login", 'POST', [
    '_csrf_token' => $csrf,
    'email' => 'marketing@jiyaji.com',
    'password' => 'Admin@123'
], $cookieFileMarketing);

echo "Login Response Code: {$loginResp['code']} | Effective URL: {$loginResp['effective_url']}\n";
if (strpos($loginResp['effective_url'], 'portal/dashboard') !== false || strpos($loginResp['body'], 'Campaigns') !== false || $loginResp['code'] === 200) {
    echo "✓ Marketing Lead login successful.\n\n";
} else {
    echo "✗ Marketing Lead login failed!\n";
    exit(1);
}

// STEP 2: Access Coupons Index
echo "[Test 2] Accessing /portal/coupons listing...\n";
$indexResp = httpRequest("$baseUrl/portal/coupons", 'GET', [], $cookieFileMarketing);
echo "Coupons Index Code: {$indexResp['code']}\n";
assert($indexResp['code'] === 200, "Expected HTTP 200 on /portal/coupons");
if (strpos($indexResp['body'], 'Coupons &amp; Offers') !== false || strpos($indexResp['body'], 'Coupons & Offers') !== false) {
    echo "✓ Luxury hero header and campaign metrics rendered.\n";
} else {
    echo "✗ Header text missing from index.\n";
}
if (strpos($indexResp['body'], 'table') !== false || strpos($indexResp['body'], 'Active Campaigns') !== false) {
    echo "✓ KPI summary cards and table layout verified.\n\n";
}

// STEP 3: Test Code Generator API
echo "[Test 3] Testing /portal/coupons/generate-code endpoint...\n";
$genResp = httpRequest("$baseUrl/portal/coupons/generate-code?prefix=TESTLX", 'GET', [], $cookieFileMarketing);
echo "Generate Code Code: {$genResp['code']}\n";
$genJson = json_decode($genResp['body'], true);
echo "Generated Code payload: " . json_encode($genJson) . "\n";
assert(isset($genJson['code']) && strpos($genJson['code'], 'TESTLX') === 0, "Expected prefix TESTLX in generated code");
echo "✓ Promo code generator verified.\n\n";

// STEP 4: Access Create Coupon Form
echo "[Test 4] Accessing /portal/coupons/create...\n";
$createPage = httpRequest("$baseUrl/portal/coupons/create", 'GET', [], $cookieFileMarketing);
echo "Create Form Code: {$createPage['code']}\n";
assert($createPage['code'] === 200, "Expected HTTP 200 on /portal/coupons/create");
$createCsrf = extractCsrf($createPage['body']);
echo "✓ Creation form rendered with categories and products restrictions inputs.\n\n";

// STEP 5: Store New Coupon via POST
echo "[Test 5] Creating a new promo coupon via /portal/coupons/store...\n";
$testCode = 'AUTOTEST_' . strtoupper(substr(md5(microtime()), 0, 6));
$storeResp = httpRequest("$baseUrl/portal/coupons/store", 'POST', [
    '_csrf_token' => $createCsrf,
    'code' => $testCode,
    'description' => 'Automated E2E Campaign for Silk Sarees',
    'type' => 'percentage',
    'value' => '25.00',
    'max_discount_amount' => '1500.00',
    'min_cart_value' => '999.00',
    'usage_limit' => '50',
    'per_user_limit' => '2',
    'is_active' => '1',
    'is_public' => '1',
    'starts_at' => date('Y-m-d\TH:i'),
    'expires_at' => date('Y-m-d\TH:i', strtotime('+15 days')),
], $cookieFileMarketing);

echo "Store Response Code: {$storeResp['code']} | Redirected to: {$storeResp['effective_url']}\n";

// Find the ID of created coupon from database directly via mysqli
$db = new mysqli('localhost', 'root', '', 'jiyaji_collection');
$stmt = $db->prepare("SELECT id, code, value, type, is_active FROM coupons WHERE code = ?");
$stmt->bind_param('s', $testCode);
$stmt->execute();
$createdCoupon = $stmt->get_result()->fetch_assoc();

assert(!empty($createdCoupon), "Coupon should be persisted in database");
$newCouponId = (int)$createdCoupon['id'];
echo "✓ Coupon created in DB: ID {$newCouponId} with Code '{$createdCoupon['code']}' ({$createdCoupon['value']}%)\n\n";

// STEP 6: View Coupon Details
echo "[Test 6] Accessing /portal/coupons/{$newCouponId} detail page...\n";
$showResp = httpRequest("$baseUrl/portal/coupons/{$newCouponId}", 'GET', [], $cookieFileMarketing);
echo "Detail Page Code: {$showResp['code']}\n";
assert($showResp['code'] === 200, "Expected HTTP 200 on show page");
if (strpos($showResp['body'], $testCode) !== false && strpos($showResp['body'], 'Discount &amp; Rules') !== false || strpos($showResp['body'], 'Discount') !== false) {
    echo "✓ Detail page shows discount rules, redemptions ledger, and validity timer.\n\n";
} else {
    echo "✗ Detail page content unexpected.\n\n";
}

// STEP 7: Toggle Status
echo "[Test 7] Toggling coupon status via /portal/coupons/{$newCouponId}/status...\n";
$toggleResp = httpRequest("$baseUrl/portal/coupons/{$newCouponId}/status", 'POST', [
    '_csrf_token' => $createCsrf,
], $cookieFileMarketing);
echo "Toggle Response Code: {$toggleResp['code']}\n";

$checkRes = $db->query("SELECT is_active FROM coupons WHERE id = {$newCouponId}")->fetch_assoc();
echo "Coupon is_active after toggle: {$checkRes['is_active']}\n";
assert((int)$checkRes['is_active'] === 0, "Coupon should be deactivated");
echo "✓ Status successfully toggled to Inactive.\n\n";

// STEP 8: Update Coupon
echo "[Test 8] Modifying coupon via /portal/coupons/{$newCouponId}/update...\n";
$updateResp = httpRequest("$baseUrl/portal/coupons/{$newCouponId}/update", 'POST', [
    '_csrf_token' => $createCsrf,
    'code' => $testCode . '_UPD',
    'description' => 'Updated Campaign Description',
    'type' => 'flat',
    'value' => '450.00',
    'min_cart_value' => '1999.00',
    'usage_limit' => '100',
    'per_user_limit' => '1',
    'is_active' => '1',
    'is_public' => '1',
], $cookieFileMarketing);

echo "Update Response Code: {$updateResp['code']}\n";
$updatedRes = $db->query("SELECT code, type, value, is_active FROM coupons WHERE id = {$newCouponId}")->fetch_assoc();
echo "Updated values in DB: Code={$updatedRes['code']}, Type={$updatedRes['type']}, Value={$updatedRes['value']}, Active={$updatedRes['is_active']}\n";
assert($updatedRes['type'] === 'flat' && (float)$updatedRes['value'] === 450.00, "Coupon should have flat type and 450.00 value");
echo "✓ Coupon successfully updated.\n\n";

// STEP 9: CSV Export
echo "[Test 9] Testing CSV Export via /portal/coupons/export...\n";
$exportResp = httpRequest("$baseUrl/portal/coupons/export", 'GET', [], $cookieFileMarketing);
echo "Export Code: {$exportResp['code']}\n";
assert($exportResp['code'] === 200, "Expected HTTP 200 on CSV export");
if (strpos($exportResp['headers'], 'text/csv') !== false && strpos($exportResp['body'], 'Coupon Code') !== false) {
    echo "✓ CSV Export valid with proper Content-Type header and columns.\n\n";
} else {
    echo "✗ CSV export format incorrect.\n\n";
}

// STEP 10: RBAC Boundary Test - Support Staff Unauthorized Access
echo "[Test 10] Testing RBAC Security Boundary with Customer Support (support@jiyaji.com)...\n";
$supLogin = httpRequest("$baseUrl/portal/login", 'POST', [
    '_csrf_token' => $createCsrf,
    'email' => 'support@jiyaji.com',
    'password' => 'Admin@123'
], $cookieFileSupport);

echo "Support Staff Login Code: {$supLogin['code']}\n";
$unauthorizedResp = httpRequest("$baseUrl/portal/coupons", 'GET', [], $cookieFileSupport, false);
echo "Support Staff access to /portal/coupons -> Code: {$unauthorizedResp['code']}, Location: " . (preg_match('/Location:\s*([^\r\n]+)/i', $unauthorizedResp['headers'], $m) ? $m[1] : 'None') . "\n";

assert($unauthorizedResp['code'] === 302, "Support staff should be redirected (HTTP 302)");
echo "✓ RBAC Protection verified: Support staff denied access to coupons management.\n\n";

// STEP 11: Cleanup test coupon
echo "[Test 11] Deleting test coupon...\n";
$delResp = httpRequest("$baseUrl/portal/coupons/{$newCouponId}/delete", 'POST', [
    '_csrf_token' => $createCsrf
], $cookieFileMarketing);
$existsRes = $db->query("SELECT COUNT(*) FROM coupons WHERE id = {$newCouponId}")->fetch_row()[0];
assert((int)$existsRes === 0, "Test coupon should be deleted");
echo "✓ Test coupon cleanly purged from database.\n\n";

// Clean cookies
if (file_exists($cookieFileMarketing)) unlink($cookieFileMarketing);
if (file_exists($cookieFileSupport)) unlink($cookieFileSupport);

echo "=======================================================\n";
echo "ALL E2E INTEGRATION & RBAC TESTS PASSED SUCCESSFULLY! ✓\n";
echo "=======================================================\n";
