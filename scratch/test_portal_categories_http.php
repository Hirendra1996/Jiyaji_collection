<?php
/**
 * End-to-End HTTP Integration Test for Portal Categories Taxonomy
 */
$cookieFile = __DIR__ . '/test_portal_cat_cookie.txt';
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

echo "=== PORTAL CATEGORIES TAXONOMY HTTP INTEGRATION TEST ===\n\n";

// 1. Fetch portal login page and get CSRF token
$loginUrl = 'http://localhost/Jiyaji_collection/portal/login';
$loginPage = httpReq($loginUrl, 'GET', [], $cookieFile);
echo "[1] GET /portal/login -> HTTP " . $loginPage['code'] . "\n";

preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $matches);
$csrf = $matches[1] ?? '';
echo "    Found CSRF Token: " . ($csrf ? substr($csrf, 0, 16) . '...' : 'NONE') . "\n";

// 2. Login as Inventory Manager (inventory@jiyaji.com)
$authResp = httpReq($loginUrl, 'POST', [
    'email' => 'inventory@jiyaji.com',
    'password' => 'Admin@123',
    '_csrf_token' => $csrf
], $cookieFile);
echo "[2] POST /portal/login -> HTTP " . $authResp['code'] . " (Landed on: " . $authResp['url'] . ")\n";

// 3. GET /portal/categories
$categoriesUrl = 'http://localhost/Jiyaji_collection/portal/categories';
$catResp = httpReq($categoriesUrl, 'GET', [], $cookieFile);
echo "[3] GET /portal/categories -> HTTP " . $catResp['code'] . " (" . strlen($catResp['body']) . " bytes)\n";
$hasTitle = strpos($catResp['body'], 'Categories &amp; Taxonomy') !== false;
$hasKPI = strpos($catResp['body'], 'Total Collections') !== false;
$hasTable = strpos($catResp['body'], 'Root Collection') !== false || strpos($catResp['body'], 'Taxonomy Ledger') !== false;
echo "    Title Rendered: " . ($hasTitle ? 'YES' : 'NO') . "\n";
echo "    KPIs Rendered: " . ($hasKPI ? 'YES' : 'NO') . "\n";
echo "    Table Rendered: " . ($hasTable ? 'YES' : 'NO') . "\n";

// 4. GET /portal/categories/create
$createUrl = 'http://localhost/Jiyaji_collection/portal/categories/create';
$createResp = httpReq($createUrl, 'GET', [], $cookieFile);
echo "[4] GET /portal/categories/create -> HTTP " . $createResp['code'] . " (" . strlen($createResp['body']) . " bytes)\n";
$hasCreateTitle = strpos($createResp['body'], 'Add New Luxury Category') !== false;
$hasSlugField = strpos($createResp['body'], 'name="slug"') !== false;
echo "    Create Form Rendered: " . ($hasCreateTitle ? 'YES' : 'NO') . "\n";
echo "    Slug Input Rendered: " . ($hasSlugField ? 'YES' : 'NO') . "\n";

// 5. Find an encrypted category link in index page (excluding create and export)
preg_match('/href="([^"]*\/portal\/categories\/((?!create|export)[^"?\/]+))"/', $catResp['body'], $encMatches);
$catDetailUrl = $encMatches[1] ?? '';
$encryptedId = $encMatches[2] ?? '';

if ($catDetailUrl && $encryptedId && $encryptedId !== 'create' && $encryptedId !== 'export') {
    echo "[5] Found Category Encrypted Link: " . $catDetailUrl . " (ID: " . substr($encryptedId, 0, 16) . "...)\n";
    $showResp = httpReq($catDetailUrl, 'GET', [], $cookieFile);
    echo "    GET /portal/categories/{id} -> HTTP " . $showResp['code'] . " (" . strlen($showResp['body']) . " bytes)\n";
    $hasShowHero = strpos($showResp['body'], 'Collection Details') !== false || strpos($showResp['body'], 'Taxonomy Profile') !== false;
    echo "    Showcase Profile Rendered: " . ($hasShowHero ? 'YES' : 'NO') . "\n";

    // 6. GET /portal/categories/{id}/edit
    $editUrl = 'http://localhost/Jiyaji_collection/portal/categories/' . $encryptedId . '/edit';
    $editResp = httpReq($editUrl, 'GET', [], $cookieFile);
    echo "[6] GET /portal/categories/{id}/edit -> HTTP " . $editResp['code'] . " (" . strlen($editResp['body']) . " bytes)\n";
    $hasEditTitle = strpos($editResp['body'], 'Edit Category:') !== false;
    echo "    Edit Form Rendered: " . ($hasEditTitle ? 'YES' : 'NO') . "\n";
} else {
    echo "[5] Skipped show/edit HTTP test (no encrypted ID matched on index page)\n";
}

// 7. GET /portal/categories/export
$exportUrl = 'http://localhost/Jiyaji_collection/portal/categories/export';
$exportResp = httpReq($exportUrl, 'GET', [], $cookieFile);
echo "[7] GET /portal/categories/export -> HTTP " . $exportResp['code'] . " (" . strlen($exportResp['body']) . " bytes)\n";
echo "    Export Body Snippet:\n" . substr($exportResp['body'], 0, 180) . "\n";
$hasCsvHeader = strpos($exportResp['body'], 'Category ID') !== false && strpos($exportResp['body'], 'Slug') !== false;
echo "    CSV Export Header Valid: " . ($hasCsvHeader ? 'YES' : 'NO') . "\n";

if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n=== HTTP INTEGRATION TEST COMPLETE ===\n";
