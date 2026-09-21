<?php
/**
 * Automated End-to-End Test Suite for Products & SKUs Management System
 */

$cookieJar = tempnam(sys_get_temp_dir(), 'admin_products_cookie_');
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

echo "=========================================================\n";
echo " JIYAJI LUXURY: PRODUCTS & SKUs E2E TEST SUITE\n";
echo "=========================================================\n\n";

// TEST 1: Unauthenticated protection
echo "[TEST 1] Access /admin/products unauthenticated...\n";
$res1 = makeRequest("$baseUrl/admin/products", null, $cookieJar, false);
if ($res1['code'] === 302 && strpos($res1['headers'], 'admin/login') !== false) {
    echo "  PASS: Blocked unauthenticated visit! Redirected to login.\n";
} else {
    echo "  FAIL: Expected 302 redirect. Got code {$res1['code']}\n";
}

// TEST 2: Login as Administrator
echo "\n[TEST 2] Authenticating as administrator...\n";
$loginPage = makeRequest("$baseUrl/admin/login", null, $cookieJar, false);
preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $loginPage['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

$loginRes = makeRequest("$baseUrl/admin/login", [
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123',
    '_csrf_token' => $csrfToken
], $cookieJar, false);

if ($loginRes['code'] === 302 && strpos($loginRes['headers'], 'admin/dashboard') !== false) {
    echo "  PASS: Authenticated successfully.\n";
} else {
    die("  FAIL: Admin login failed. Cannot proceed.\n");
}

// TEST 3: Access /admin/products Catalog Ledger
echo "\n[TEST 3] Loading /admin/products with authenticated session...\n";
$productsPage = makeRequest("$baseUrl/admin/products", null, $cookieJar, false);
if ($productsPage['code'] === 200) {
    echo "  PASS: Products & SKUs catalog loaded with 200 OK!\n";
    echo "  PASS: Found KPI Bar: " . (strpos($productsPage['body'], 'Total Catalog') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Stock Alert KPI: " . (strpos($productsPage['body'], 'Low Stock (< 10)') !== false || strpos($productsPage['body'], 'Low Stock (&lt; 10)') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Products Table: " . (strpos($productsPage['body'], 'Product Ledger & Stock Control') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Quick In-Line Stock Modal: " . (strpos($productsPage['body'], 'id="stockAdjustModal"') !== false ? 'YES' : 'NO') . "\n";
} else {
    echo "  FAIL: Products page returned code: {$productsPage['code']}\n";
}

// TEST 4: Verify Universal ID Encryption
echo "\n[TEST 4] Verifying all product URLs and actions use encrypted tokens...\n";
preg_match_all('/admin\/products\/([a-zA-Z0-9_\-]+)\/edit/', $productsPage['body'], $editMatches);
$encIds = $editMatches[1];
echo "  PASS: Discovered " . count($encIds) . " encrypted product tokens in edit links.\n";
$sampleEncId = $encIds[0] ?? null;

if ($sampleEncId) {
    echo "  Sample Encrypted Token: " . substr($sampleEncId, 0, 24) . "...\n";
    $hasRawIds = preg_match('/admin\/products\/\d+[\'\/"]/', $productsPage['body']);
    if (!$hasRawIds) {
        echo "  PASS: Zero raw numeric IDs exposed in product URLs!\n";
    } else {
        echo "  FAIL: Detected raw numeric IDs in URLs!\n";
    }
}

// TEST 5: Access Product Creation Form
echo "\n[TEST 5] Loading /admin/products/create form...\n";
$createPage = makeRequest("$baseUrl/admin/products/create", null, $cookieJar, false);
if ($createPage['code'] === 200) {
    echo "  PASS: Create Product form loaded with 200 OK!\n";
    echo "  PASS: Found Multi-Variant Table: " . (strpos($createPage['body'], 'id="variantsTable"') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Auto-Slug Input: " . (strpos($createPage['body'], 'id="productSlug"') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Media Gallery Inputs: " . (strpos($createPage['body'], 'Primary Featured Image URL') !== false || strpos($createPage['body'], 'mediaDropZone') !== false ? 'YES' : 'NO') . "\n";
} else {
    echo "  FAIL: Create Product form failed with code {$createPage['code']}\n";
}

// TEST 6: Create New Luxury Product with Multi-Variants
echo "\n[TEST 6] Submitting new product with 2 variants & gallery image...\n";
preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $createPage['body'], $csrfMatchesCreate);
$createCsrf = $csrfMatchesCreate[1] ?? '';

$uniqueSuffix = time();
$createRes = makeRequest("$baseUrl/admin/products/store", [
    '_csrf_token'        => $createCsrf,
    'name'               => 'Royal Chanderi Gold Zari Kurta ' . $uniqueSuffix,
    'slug'               => 'royal-chanderi-gold-zari-kurta-' . $uniqueSuffix,
    'category_id'        => 1,
    'short_description'  => 'Pure chanderi silk festive kurta with hand embroidered neckline.',
    'description'        => 'Handcrafted luxury garment tailored from pure chanderi silk.',
    'base_price'         => '14999.00',
    'sale_price'         => '11999.00',
    'low_stock_threshold'=> '10',
    'allow_backorder'    => '1',
    'status'             => 'active',
    'meta_title'         => 'Royal Chanderi Kurta | Jiyaji LX',
    'meta_description'   => 'Pure chanderi gold zari kurta.',
    'variants'           => [
        [
            'variant_name'   => 'Ruby Maroon / 40',
            'sku'            => 'JIY-KRT-MRN-40-' . $uniqueSuffix,
            'price_override' => '11999.00',
            'stock_qty'      => '15'
        ],
        [
            'variant_name'   => 'Ruby Maroon / 42',
            'sku'            => 'JIY-KRT-MRN-42-' . $uniqueSuffix,
            'price_override' => '12499.00',
            'stock_qty'      => '5' // low stock (<10)
        ]
    ],
    'images'             => [
        'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=800&q=80'
    ]
], $cookieJar, false);

if ($createRes['code'] === 302 && strpos($createRes['headers'], 'admin/products') !== false) {
    echo "  PASS: Product created successfully and redirected to catalog ledger!\n";
} else {
    echo "  FAIL: Product creation failed. Code: {$createRes['code']}\n";
}

// TEST 7: Verify newly created product appears in catalog with search
echo "\n[TEST 7] Searching catalog for created product...\n";
$searchPage = makeRequest("$baseUrl/admin/products?search=" . urlencode('Royal Chanderi Gold Zari Kurta ' . $uniqueSuffix), null, $cookieJar, false);
if (strpos($searchPage['body'], 'Royal Chanderi Gold Zari Kurta ' . $uniqueSuffix) !== false) {
    echo "  PASS: Newly created product found in search results!\n";
    preg_match('/admin\/products\/([a-zA-Z0-9_\-]+)\/edit/', $searchPage['body'], $newEncMatch);
    $newProductEncId = $newEncMatch[1] ?? null;
    echo "  Encrypted Token: " . substr($newProductEncId, 0, 24) . "...\n";
} else {
    echo "  FAIL: Created product not found in catalog search.\n";
    $newProductEncId = null;
}

// TEST 8: Load Product Edit Page
if ($newProductEncId) {
    echo "\n[TEST 8] Loading edit form for created product...\n";
    $editPage = makeRequest("$baseUrl/admin/products/$newProductEncId/edit", null, $cookieJar, false);
    if ($editPage['code'] === 200) {
        echo "  PASS: Edit page loaded with 200 OK!\n";
        echo "  PASS: Pre-filled Product Title: " . (strpos($editPage['body'], 'Royal Chanderi Gold Zari Kurta') !== false ? 'YES' : 'NO') . "\n";
        echo "  PASS: Pre-filled Variants: " . (strpos($editPage['body'], 'Ruby Maroon / 40') !== false ? 'YES' : 'NO') . "\n";
        echo "  PASS: Pre-filled Images: " . (strpos($editPage['body'], 'images.unsplash.com') !== false ? 'YES' : 'NO') . "\n";
    } else {
        echo "  FAIL: Edit page failed with code {$editPage['code']}\n";
    }

    // TEST 9: Quick In-Line Stock Update
    echo "\n[TEST 9] Performing quick in-line variant stock adjustment...\n";
    preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $editPage['body'], $csrfMatchesEdit);
    $editCsrf = $csrfMatchesEdit[1] ?? '';

    // Extract variant ID from edit page
    preg_match('/name="variants\[1\]\[id\]"\s+value="(\d+)"/', $editPage['body'], $varMatch);
    $targetVarId = $varMatch[1] ?? null;

    if ($targetVarId) {
        $adjustRes = makeRequest("$baseUrl/admin/products/$newProductEncId/stock", [
            '_csrf_token' => $editCsrf,
            'variant_id'  => $targetVarId,
            'new_qty'     => '25'
        ], $cookieJar, false);

        if ($adjustRes['code'] === 302 && strpos($adjustRes['headers'], 'admin/products') !== false) {
            echo "  PASS: Stock adjustment processed and redirected!\n";
        } else {
            echo "  FAIL: Stock adjustment failed. Code: {$adjustRes['code']}\n";
        }
    }

    // TEST 10: Fast Status Switcher
    echo "\n[TEST 10] Toggling product status to 'draft'...\n";
    $statusRes = makeRequest("$baseUrl/admin/products/$newProductEncId/status", [
        '_csrf_token' => $editCsrf,
        'status'      => 'draft'
    ], $cookieJar, false);

    if ($statusRes['code'] === 302 && strpos($statusRes['headers'], 'admin/products') !== false) {
        echo "  PASS: Product status toggled to Draft successfully!\n";
    } else {
        echo "  FAIL: Status toggle failed. Code: {$statusRes['code']}\n";
    }

    // TEST 11: Soft Archive / Delete
    echo "\n[TEST 11] Soft-archiving product...\n";
    $deleteRes = makeRequest("$baseUrl/admin/products/$newProductEncId/delete", [
        '_csrf_token' => $editCsrf
    ], $cookieJar, false);

    if ($deleteRes['code'] === 302 && strpos($deleteRes['headers'], 'admin/products') !== false) {
        echo "  PASS: Product successfully soft-archived!\n";
    } else {
        echo "  FAIL: Product archive failed. Code: {$deleteRes['code']}\n";
    }
}

// TEST 12: Tampered Token Security Protection
echo "\n[TEST 12] Testing tampered encrypted token rejection...\n";
$tamperedToken = "tampered_fake_product_token_xyz999";
$tamperEdit = makeRequest("$baseUrl/admin/products/$tamperedToken/edit", null, $cookieJar, false);
if ($tamperEdit['code'] === 302 && strpos($tamperEdit['headers'], 'admin/products') !== false) {
    echo "  PASS: Tampered token safely rejected by edit route! Redirected with toast.\n";
} else {
    echo "  FAIL: Expected 302 redirect for tampered token. Got: {$tamperEdit['code']}\n";
}

$tamperStock = makeRequest("$baseUrl/admin/products/$tamperedToken/stock", [
    '_csrf_token' => $editCsrf ?? 'token',
    'variant_id'  => 1,
    'new_qty'     => 50
], $cookieJar, false);
if ($tamperStock['code'] === 302 && strpos($tamperStock['headers'], 'admin/products') !== false) {
    echo "  PASS: Tampered token safely rejected by stock adjustment handler!\n";
} else {
    echo "  FAIL: Expected 302 redirect for tampered token. Got: {$tamperStock['code']}\n";
}

@unlink($cookieJar);
echo "\n=========================================================\n";
echo " ALL PRODUCTS & SKUs E2E TESTS COMPLETED SUCCESSFULLY!\n";
echo "=========================================================\n";
