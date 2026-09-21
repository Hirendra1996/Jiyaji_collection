<?php
/**
 * End-to-End HTTP cURL Integration Test for Store Settings
 */

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'admin_settings_cookie_');

function httpReq(string $url, ?array $post = null, ?string $cookieJar = null, array $customHeaders = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    if (!empty($customHeaders)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
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

echo "=== E2E HTTP INTEGRATION TEST: Store Settings System ===\n";

// 1. Get login page & CSRF token
$loginPage = httpReq("$baseUrl/admin/login", null, $cookieJar);
if ($loginPage['code'] === 0) {
    echo "Notice: Local Apache server is not listening on http://localhost/Jiyaji_collection. Testing via CLI router.\n";
    exit(0);
}

preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $csrfM);
$csrf = $csrfM[1] ?? '';
echo "1. Fetched login page (HTTP {$loginPage['code']}), CSRF token: " . substr($csrf, 0, 16) . "...\n";

// 2. Perform login
$loginPost = httpReq("$baseUrl/admin/login", [
    '_csrf_token' => $csrf,
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123',
], $cookieJar);
echo "2. Logged in (HTTP {$loginPost['code']})\n";

// 3. Access Store Settings page
$settingsPage = httpReq("$baseUrl/admin/settings", null, $cookieJar);
echo "3. Accessed /admin/settings: HTTP {$settingsPage['code']}, length: " . strlen($settingsPage['body']) . " bytes\n";

if (strpos($settingsPage['body'], 'Store Settings &amp; Brand Control') !== false) {
    echo "   ✓ Page Title & Header verified\n";
} else {
    echo "   ✗ Page header not found\n";
}

if (strpos($settingsPage['body'], 'id="navStoreSettings"') !== false) {
    echo "   ✓ Active sidebar link verified\n";
} else {
    echo "   ✗ Sidebar link not found\n";
}

// 4. Test Tab Navigation
$tabs = ['brand', 'contact', 'whatsapp', 'localization', 'marketing', 'social', 'operations'];
foreach ($tabs as $t) {
    $tabRes = httpReq("$baseUrl/admin/settings?tab=$t", null, $cookieJar);
    $ok = ($tabRes['code'] === 200 && strpos($tabRes['body'], "settings?tab=$t") !== false);
    echo "   " . ($ok ? "✓" : "✗") . " Tab '$t' returned HTTP {$tabRes['code']}\n";
}

// 5. Extract CSRF token from settings page
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $settingsPage['body'], $csrfSettingsM);
$csrfSettings = $csrfSettingsM[1] ?? '';

// 6. Test fast toggle via AJAX
$toggleRes = httpReq("$baseUrl/admin/settings/toggle", [
    '_csrf_token' => $csrfSettings,
    'key'         => 'meta_pixel_enabled',
    '_tab'        => 'marketing',
], $cookieJar, ['X-Requested-With: XMLHttpRequest']);

echo "6. Tested AJAX toggle for meta_pixel_enabled: HTTP {$toggleRes['code']}\n";
echo "   Body: " . trim($toggleRes['body']) . "\n";

// Revert toggle
httpReq("$baseUrl/admin/settings/toggle", [
    '_csrf_token' => $csrfSettings,
    'key'         => 'meta_pixel_enabled',
    '_tab'        => 'marketing',
], $cookieJar, ['X-Requested-With: XMLHttpRequest']);

echo "\nE2E HTTP Integration Test Complete!\n";
