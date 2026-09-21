<?php
/**
 * End-to-End HTTP cURL Integration Test for Payment Gateways
 */

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'admin_gw_cookie_');

function httpReq(string $url, ?array $post = null, ?string $cookieJar = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($resp, 0, $headerSize);
    $body = substr($resp, $headerSize);
    curl_close($ch);
    return ['code' => $code, 'headers' => $headers, 'body' => $body];
}

echo "=== E2E HTTP INTEGRATION TEST: Payment Gateways ===\n";

// 1. Get login page & CSRF token
$loginPage = httpReq("$baseUrl/admin/login", null, $cookieJar);
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $csrfM);
$csrf = $csrfM[1] ?? '';
echo "1. Fetched login page, CSRF token: " . substr($csrf, 0, 16) . "...\n";

// 2. Perform login
$loginPost = httpReq("$baseUrl/admin/login", [
    '_csrf_token' => $csrf,
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123',
], $cookieJar);
echo "2. Logged in, response code: {$loginPost['code']}\n";

// 3. Access Payment Gateways main page
$gwPage = httpReq("$baseUrl/admin/payment-gateways", null, $cookieJar);
echo "3. Accessed /admin/payment-gateways: HTTP {$gwPage['code']}, length: " . strlen($gwPage['body']) . " bytes\n";
if (strpos($gwPage['body'], 'Payment Gateways &amp; Settlement System') !== false) {
    echo "   ✓ Welcome banner verified\n";
} else {
    echo "   ✗ Banner text not found\n";
}
if (strpos($gwPage['body'], 'Razorpay Checkout') !== false && strpos($gwPage['body'], 'Cash on Delivery') !== false) {
    echo "   ✓ Gateway overview cards verified\n";
}

// 4. Test live pincode check via AJAX
$pinReq = httpReq("$baseUrl/admin/payment-gateways/check-pincode", [
    'pincode' => '400001',
    'amount'  => '5000'
], $cookieJar);
echo "4. AJAX check-pincode (400001): " . trim($pinReq['body']) . "\n";
$pinJson = json_decode($pinReq['body'], true);
if ($pinJson && !empty($pinJson['eligible'])) {
    echo "   ✓ Pincode 400001 is eligible for COD\n";
}

// 5. Test test-connection via AJAX
$testConnReq = httpReq("$baseUrl/admin/payment-gateways/test-connection", [
    'key_id' => 'rzp_test_9JiyajiLuxury2026',
    'key_secret' => 'sec_test_abc123'
], $cookieJar);
echo "5. AJAX test-connection: " . trim($testConnReq['body']) . "\n";
$connJson = json_decode($testConnReq['body'], true);
if ($connJson && !empty($connJson['valid'])) {
    echo "   ✓ Connection format validated as " . strtoupper($connJson['mode']) . "\n";
}

// 6. Access transactions tab
$txPage = httpReq("$baseUrl/admin/payment-gateways?tab=transactions", null, $cookieJar);
echo "6. Access /admin/payment-gateways?tab=transactions: HTTP {$txPage['code']}, length: " . strlen($txPage['body']) . " bytes\n";
if (strpos($txPage['body'], 'Payment Transactions &amp; Webhook Audit Ledger') !== false) {
    echo "   ✓ Transactions ledger table verified\n";
}

echo "\nE2E HTTP Integration Test Complete!\n";
