<?php
/**
 * End-to-End HTTP cURL Integration Test for Shipping & Pincodes
 */

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'admin_ship_cookie_');

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

echo "=== E2E HTTP INTEGRATION TEST: Shipping & Pincodes ===\n";

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

// 3. Access Shipping & Pincodes main page
$shipPage = httpReq("$baseUrl/admin/shipping-pincodes", null, $cookieJar);
echo "3. Accessed /admin/shipping-pincodes: HTTP {$shipPage['code']}, length: " . strlen($shipPage['body']) . " bytes\n";
if (strpos($shipPage['body'], 'Shipping Zones &amp; Delivery Logistics') !== false) {
    echo "   ✓ Welcome banner verified\n";
} else {
    echo "   ✗ Banner text not found\n";
}
if (strpos($shipPage['body'], 'Metro Express Hubs') !== false) {
    echo "   ✓ Zone cards verified\n";
}

// 4. Test live calculator AJAX
$calcReq = httpReq("$baseUrl/admin/shipping-pincodes/calculate", [
    'pincode'      => '400001',
    'subtotal'     => '4500',
    'weight_grams' => '1200'
], $cookieJar);
echo "4. AJAX calculate (400001, ₹4,500, 1200g): HTTP {$calcReq['code']}\n";
$calcJson = json_decode($calcReq['body'], true);
if ($calcJson && !empty($calcJson['valid'])) {
    echo "   ✓ Matched Zone: " . $calcJson['zone']['name'] . " (" . $calcJson['zone']['zone_code'] . ")\n";
    echo "   ✓ Methods returned: " . count($calcJson['methods']) . "\n";
    foreach ($calcJson['methods'] as $m) {
        echo "      * {$m['title']}: " . ($m['is_free'] ? 'FREE' : '₹' . $m['fee']) . " ({$m['estimated_days']})\n";
    }
} else {
    echo "   ✗ Calculator failed: " . substr($calcReq['body'], 0, 200) . "\n";
}

// 5. Access rates matrix tab
$ratesPage = httpReq("$baseUrl/admin/shipping-pincodes?tab=rates", null, $cookieJar);
echo "5. Access /admin/shipping-pincodes?tab=rates: HTTP {$ratesPage['code']}, length: " . strlen($ratesPage['body']) . " bytes\n";
if (strpos($ratesPage['body'], 'Shipping Rates &amp; Weight Slabs Matrix') !== false) {
    echo "   ✓ Rate matrix table verified\n";
}

// 6. Access settings tab
$setPage = httpReq("$baseUrl/admin/shipping-pincodes?tab=settings", null, $cookieJar);
echo "6. Access /admin/shipping-pincodes?tab=settings: HTTP {$setPage['code']}, length: " . strlen($setPage['body']) . " bytes\n";
if (strpos($setPage['body'], 'Storewide Shipping &amp; Free Delivery Policy') !== false) {
    echo "   ✓ Global policy form verified\n";
}

echo "\nE2E HTTP Integration Test Complete!\n";
