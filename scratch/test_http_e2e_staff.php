<?php
/**
 * End-to-End HTTP Integration Test for Staff & RBAC System
 * Simulates admin login, fetches /admin/staff tabs, and verifies HTTP 200 responses.
 */

$cookieFile = __DIR__ . '/test_staff_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function httpReq(string $url, string $method = 'GET', array $postData = [], string $cookieFile = ''): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) JiyajiE2E');

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $info['http_code'] ?? 0,
        'url'  => $info['url'] ?? '',
        'body' => $body ?: '',
        'err'  => $err
    ];
}

echo "=== Jiyaji LX — Staff & RBAC HTTP End-to-End Test ===\n\n";

// Step 1: Login
$loginUrl = 'http://localhost/Jiyaji_collection/admin/login';
$loginPage = httpReq($loginUrl, 'GET', [], $cookieFile);
echo "[1] GET /admin/login -> HTTP " . $loginPage['code'] . "\n";

// Extract CSRF token from login page
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $matches);
$csrf = $matches[1] ?? '';
echo "    Found CSRF: " . ($csrf ? substr($csrf, 0, 16) . '...' : 'NONE') . "\n";

$authResp = httpReq($loginUrl, 'POST', [
    'email' => 'admin@jiyaji.com',
    'password' => 'Admin@123',
    '_csrf_token' => $csrf
], $cookieFile);
echo "[2] POST /admin/login -> HTTP " . $authResp['code'] . " (Final URL: " . $authResp['url'] . ")\n";

// Step 2: Test Staff Directory tab
$staffResp = httpReq('http://localhost/Jiyaji_collection/admin/staff?tab=staff', 'GET', [], $cookieFile);
echo "[3] GET /admin/staff?tab=staff -> HTTP " . $staffResp['code'] . " (" . strlen($staffResp['body']) . " bytes)\n";
$hasStaffTitle = strpos($staffResp['body'], 'Staff Accounts &amp; Access Control') !== false || strpos($staffResp['body'], 'Staff Accounts & Access Control') !== false;
$hasStaffTable = strpos($staffResp['body'], 'Staff Member') !== false;
echo "    Title Present: " . ($hasStaffTitle ? 'YES' : 'NO') . "\n";
echo "    Table Present: " . ($hasStaffTable ? 'YES' : 'NO') . "\n";

// Step 3: Test Roles tab
$rolesResp = httpReq('http://localhost/Jiyaji_collection/admin/staff?tab=roles', 'GET', [], $cookieFile);
echo "[4] GET /admin/staff?tab=roles -> HTTP " . $rolesResp['code'] . " (" . strlen($rolesResp['body']) . " bytes)\n";
$hasRolesGrid = strpos($rolesResp['body'], 'Configured Access Roles') !== false;
$hasMatrix = strpos($rolesResp['body'], 'Granular Permission Taxonomy') !== false;
echo "    Roles Grid: " . ($hasRolesGrid ? 'YES' : 'NO') . "\n";
echo "    Permission Matrix: " . ($hasMatrix ? 'YES' : 'NO') . "\n";

// Step 4: Test Policy tab
$policyResp = httpReq('http://localhost/Jiyaji_collection/admin/staff?tab=policy', 'GET', [], $cookieFile);
echo "[5] GET /admin/staff?tab=policy -> HTTP " . $policyResp['code'] . " (" . strlen($policyResp['body']) . " bytes)\n";
$hasRootProtections = strpos($policyResp['body'], 'Root Account Protections') !== false;
echo "    Root Protections: " . ($hasRootProtections ? 'YES' : 'NO') . "\n";

// Step 5: Check AJAX check-permission endpoint
$ajaxResp = httpReq('http://localhost/Jiyaji_collection/admin/staff/check-permission?module=orders&action=manage', 'GET', [], $cookieFile);
echo "[6] GET /admin/staff/check-permission -> HTTP " . $ajaxResp['code'] . "\n";
echo "    Response: " . trim($ajaxResp['body']) . "\n";

if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n=== End-to-End HTTP Test Complete ===\n";
