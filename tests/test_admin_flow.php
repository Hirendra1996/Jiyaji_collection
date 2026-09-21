<?php
/**
 * Automated End-to-End Test for Admin Authentication & Dashboard Lifecycle
 */

$cookieJar = tempnam(sys_get_temp_dir(), 'admin_cookie_');
$baseUrl = 'http://localhost/Jiyaji_collection';

function makeRequest($url, $postData = null, $cookieJar = null, $followRedirects = false) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($followRedirects) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'headers' => $headers, 'body' => $body];
}

echo "=== STARTING ADMIN AUTH & DASHBOARD E2E TEST ===\n\n";

// TEST 1: Unauthenticated access to dashboard
echo "[TEST 1] Access /admin/dashboard without authentication...\n";
$res1 = makeRequest("$baseUrl/admin/dashboard", null, $cookieJar, false);
if ($res1['code'] === 302 && strpos($res1['headers'], 'admin/login') !== false) {
    echo "  PASS: Correctly redirected (302) to admin/login\n";
} else {
    echo "  FAIL: Expected 302 redirect. Got code {$res1['code']}\n";
}

// TEST 2: Fetch login page to get CSRF token
echo "\n[TEST 2] Fetch /admin/login and extract CSRF token...\n";
$res2 = makeRequest("$baseUrl/admin/login", null, $cookieJar, false);
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $res2['body'], $tokenMatch);
$csrfToken = $tokenMatch[1] ?? null;
if ($csrfToken) {
    echo "  PASS: Retrieved CSRF token: " . substr($csrfToken, 0, 16) . "...\n";
} else {
    echo "  FAIL: Could not extract CSRF token from login page\n";
}

// TEST 3: Attempt login with invalid credentials
echo "\n[TEST 3] Submit login with invalid password...\n";
$res3 = makeRequest("$baseUrl/admin/login", [
    '_csrf_token' => $csrfToken,
    'email' => 'admin@jiyaji.com',
    'password' => 'wrongpass'
], $cookieJar, true);
if (strpos($res3['body'], 'Invalid email or password.') !== false) {
    echo "  PASS: Correctly showed error 'Invalid email or password.'\n";
} else {
    echo "  FAIL: Error message not found in response\n";
}

// Extract new CSRF token if needed
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $res3['body'], $tokenMatch2);
$csrfToken = $tokenMatch2[1] ?? $csrfToken;

// TEST 4: Submit valid credentials
echo "\n[TEST 4] Submit login with valid credentials (admin@jiyaji.com / Admin@123)...\n";
$res4 = makeRequest("$baseUrl/admin/login", [
    '_csrf_token' => $csrfToken,
    'email' => 'admin@jiyaji.com',
    'password' => 'Admin@123'
], $cookieJar, false);
if ($res4['code'] === 302 && strpos($res4['headers'], 'admin/dashboard') !== false) {
    echo "  PASS: Successfully authenticated! Redirected (302) to admin/dashboard\n";
} else {
    echo "  FAIL: Expected 302 redirect to admin/dashboard. Code: {$res4['code']}\n";
}

// TEST 5: Load protected dashboard with authenticated session
echo "\n[TEST 5] Access /admin/dashboard with authenticated session...\n";
$res5 = makeRequest("$baseUrl/admin/dashboard", null, $cookieJar, false);
if ($res5['code'] === 200 && strpos($res5['body'], 'Welcome back, Administrator') !== false) {
    echo "  PASS: Dashboard loaded with 200 OK!\n";
    echo "  PASS: Greeting verified: 'Welcome back, Administrator'\n";
    echo "  PASS: Found KPI Cards: " . (strpos($res5['body'], 'Total Revenue') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Analytics Trajectory Chart: " . (strpos($res5['body'], 'Sales & Orders Trajectory') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Category Share Donut: " . (strpos($res5['body'], 'Category Share') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Monthly Target Goal: " . (strpos($res5['body'], 'Monthly Target') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Top Sellers Leaderboard: " . (strpos($res5['body'], 'Top Sellers') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Live Orders Ledger: " . (strpos($res5['body'], 'Live Orders Ledger') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Toast Notifications System: " . (strpos($res5['body'], 'toastContainer') !== false ? "YES" : "NO") . "\n";
} else {
    echo "  FAIL: Could not load dashboard with 200 OK. Code: {$res5['code']}\n";
}

// TEST 6: Logout
echo "\n[TEST 6] Trigger logout /admin/logout...\n";
$res6 = makeRequest("$baseUrl/admin/logout", null, $cookieJar, false);
if ($res6['code'] === 302 && strpos($res6['headers'], 'admin/login') !== false) {
    echo "  PASS: Successfully logged out! Redirected to admin/login\n";
} else {
    echo "  FAIL: Expected 302 redirect. Code: {$res6['code']}\n";
}

// TEST 7: Attempt dashboard access after logout
echo "\n[TEST 7] Verify session destroyed by re-accessing /admin/dashboard...\n";
$res7 = makeRequest("$baseUrl/admin/dashboard", null, $cookieJar, false);
if ($res7['code'] === 302 && strpos($res7['headers'], 'admin/login') !== false) {
    echo "  PASS: Confirmed dashboard is protected! Redirected to admin/login\n";
} else {
    echo "  FAIL: Access was not blocked after logout. Code: {$res7['code']}\n";
}

@unlink($cookieJar);
echo "\n=== ALL TESTS COMPLETED SUCCESSFULLY ===\n";
