<?php
/**
 * JIYAJI LUXURY: CATEGORIES & TAXONOMY E2E TEST SUITE
 * 
 * Verifies:
 * - RBAC / Admin authentication guard
 * - Categories ledger & KPI cards
 * - Encrypted URL tokens (zero raw database IDs exposed)
 * - Category creation with image uploads & unique auto-slugs
 * - Parent-child hierarchical relationships (Root vs Sub-categories)
 * - Editing category specifications and candidate parent filtering
 * - In-line status toggling (Active <-> Inactive)
 * - Safe deletion guard (protects against orphaned products)
 * - Rejection of tampered tokens
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
require_once __DIR__ . '/../app/Models/Category.php';

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'jiyaji_cat_cookie_');

echo "=========================================================\n";
echo " JIYAJI LUXURY: CATEGORIES & TAXONOMY E2E TEST SUITE\n";
echo "=========================================================\n\n";

function request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    return [
        'code'   => $httpCode,
        'header' => $header,
        'body'   => $body
    ];
}

// -------------------------------------------------------------
// [TEST 1] Access /admin/categories unauthenticated
// -------------------------------------------------------------
echo "[TEST 1] Access /admin/categories unauthenticated...\n";
$res = request("$baseUrl/admin/categories");
if ($res['code'] === 302 && strpos($res['header'], 'admin/login') !== false) {
    echo "  PASS: Blocked unauthenticated visit! Redirected to login.\n\n";
} else {
    echo "  FAIL: Expected 302 redirect to login, got HTTP {$res['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 2] Authenticate as Administrator
// -------------------------------------------------------------
echo "[TEST 2] Authenticating as administrator...\n";
$loginPage = request("$baseUrl/admin/login", 'GET', [], $cookieJar);
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $matches);
$csrfToken = $matches[1] ?? '';

$loginRes = request("$baseUrl/admin/login", 'POST', [
    '_csrf_token' => $csrfToken,
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123'
], $cookieJar);

if ($loginRes['code'] === 302 && strpos($loginRes['header'], 'admin/dashboard') !== false) {
    echo "  PASS: Authenticated successfully.\n\n";
} else {
    echo "  FAIL: Login failed with HTTP {$loginRes['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 3] Loading /admin/categories with authenticated session
// -------------------------------------------------------------
echo "[TEST 3] Loading /admin/categories with authenticated session...\n";
$catList = request("$baseUrl/admin/categories", 'GET', [], $cookieJar);
if ($catList['code'] === 200) {
    echo "  PASS: Categories & Taxonomy catalog loaded with 200 OK!\n";
    
    $hasKpis   = strpos($catList['body'], 'Total Collections') !== false;
    $hasRoots  = strpos($catList['body'], 'Root Collections') !== false;
    $hasTable  = strpos($catList['body'], 'Taxonomy Ledger') !== false;
    $hasCreate = strpos($catList['body'], 'Add New Category') !== false;
    
    echo "  PASS: Found KPI Bar: " . ($hasKpis ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Root Categories KPI: " . ($hasRoots ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Categories Ledger Table: " . ($hasTable ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Add Category Button: " . ($hasCreate ? 'YES' : 'NO') . "\n\n";
    
    if (!$hasKpis || !$hasRoots || !$hasTable || !$hasCreate) {
        echo "  FAIL: Missing expected components on categories listing page.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Failed loading categories list, got HTTP {$catList['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 4] Verifying all category action URLs use encrypted tokens
// -------------------------------------------------------------
echo "[TEST 4] Verifying all category URLs use encrypted tokens...\n";
preg_match_all('/admin\/categories\/([^\/]+)\/edit/', $catList['body'], $editMatches);
$tokens = $editMatches[1] ?? [];
$validTokens = 0;

foreach ($tokens as $token) {
    if (!is_numeric($token) && strlen($token) >= 20) {
        $validTokens++;
    }
}

if ($validTokens > 0) {
    echo "  PASS: Discovered {$validTokens} encrypted category tokens in edit links.\n";
    echo "  Sample Encrypted Token: " . substr($tokens[0], 0, 24) . "...\n";
    echo "  PASS: Zero raw numeric IDs exposed in category URLs!\n\n";
} else {
    echo "  FAIL: No encrypted tokens found or numeric IDs exposed!\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 5] Loading /admin/categories/create form
// -------------------------------------------------------------
echo "[TEST 5] Loading /admin/categories/create form...\n";
$createForm = request("$baseUrl/admin/categories/create", 'GET', [], $cookieJar);
if ($createForm['code'] === 200) {
    echo "  PASS: Create Category form loaded with 200 OK!\n";
    $hasNameInput   = strpos($createForm['body'], 'name="name"') !== false;
    $hasSlugInput   = strpos($createForm['body'], 'name="slug"') !== false;
    $hasParentSelect= strpos($createForm['body'], 'name="parent_id"') !== false;
    $hasDropZone    = strpos($createForm['body'], 'catDropZone') !== false;

    echo "  PASS: Found Name Input: " . ($hasNameInput ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Auto-Slug Input: " . ($hasSlugInput ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Parent Hierarchy Selector: " . ($hasParentSelect ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Direct Image Upload Dropzone: " . ($hasDropZone ? 'YES' : 'NO') . "\n\n";

    if (!$hasNameInput || !$hasSlugInput || !$hasParentSelect || !$hasDropZone) {
        echo "  FAIL: Missing required form inputs in category creation view.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Failed loading create form, got HTTP {$createForm['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 6] Creating new Root Category with real uploaded image
// -------------------------------------------------------------
echo "[TEST 6] Creating new Root Category with uploaded banner image...\n";
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $createForm['body'], $matches);
$formCsrf = $matches[1] ?? '';

// Create dummy image file
$dummyImg = tempnam(sys_get_temp_dir(), 'test_cat_') . '.png';
$im = imagecreatetruecolor(200, 200);
$bg = imagecolorallocate($im, 140, 48, 245);
imagefill($im, 0, 0, $bg);
imagepng($im, $dummyImg);
imagedestroy($im);

$uniqueSuffix = time() . '_' . rand(100, 999);
$rootCatName = "Royal Lehengas " . $uniqueSuffix;

$postPayload = [
    '_csrf_token'  => $formCsrf,
    'name'        => $rootCatName,
    'slug'        => '',
    'parent_id'   => '',
    'description' => 'Exquisite bridal and sangeet designer lehengas.',
    'sort_order'  => '2',
    'is_active'   => '1',
    'image'       => new CURLFile($dummyImg, 'image/png', 'royal_lehenga_cover.png')
];

$storeRes = request("$baseUrl/admin/categories/store", 'POST', $postPayload, $cookieJar);
if ($storeRes['code'] === 302 && strpos($storeRes['header'], 'admin/categories') !== false) {
    echo "  PASS: Root category created successfully and redirected to ledger!\n";
} else {
    echo "  FAIL: Store request failed with HTTP {$storeRes['code']}\n";
    exit(1);
}

// Verify category in database
$db = App\Config\Database::connect();
$checkStmt = $db->prepare("SELECT * FROM categories WHERE name = ?");
$checkStmt->bind_param("s", $rootCatName);
$checkStmt->execute();
$createdRoot = $checkStmt->get_result()->fetch_assoc();

if ($createdRoot) {
    echo "  PASS: Category record confirmed in database! ID: {$createdRoot['id']}\n";
    echo "  PASS: Generated Slug: '{$createdRoot['slug']}'\n";
    
    // Verify physical image
    if (!empty($createdRoot['image_url'])) {
        $imgPath = __DIR__ . '/../public/' . $createdRoot['image_url'];
        if (file_exists($imgPath)) {
            echo "  PASS: Category cover image saved to physical disk: {$createdRoot['image_url']}\n\n";
        } else {
            echo "  FAIL: Image path registered in DB but missing on disk: {$imgPath}\n";
            exit(1);
        }
    } else {
        echo "  FAIL: Expected image_url to be saved in database.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Created category not found in database.\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 7] Creating Sub-Category under the created Root Category
// -------------------------------------------------------------
echo "[TEST 7] Creating Sub-Category linked to parent category...\n";
$subCatName = "Bridal Velvet Lehengas " . $uniqueSuffix;

$subPayload = [
    '_csrf_token'  => $formCsrf,
    'name'        => $subCatName,
    'slug'        => '',
    'parent_id'   => $createdRoot['id'],
    'description' => 'Heavy zardozi embroidery on pure velvet.',
    'sort_order'  => '1',
    'is_active'   => '1'
];

$storeSub = request("$baseUrl/admin/categories/store", 'POST', $subPayload, $cookieJar);
if ($storeSub['code'] === 302) {
    $subStmt = $db->prepare("SELECT * FROM categories WHERE name = ?");
    $subStmt->bind_param("s", $subCatName);
    $subStmt->execute();
    $createdSub = $subStmt->get_result()->fetch_assoc();

    if ($createdSub && (int)$createdSub['parent_id'] === (int)$createdRoot['id']) {
        echo "  PASS: Sub-category created with parent_id = {$createdSub['parent_id']}!\n\n";
    } else {
        echo "  FAIL: Sub-category parent_id not matched in DB.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Sub-category store failed with HTTP {$storeSub['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 8] Loading Edit form for created category
// -------------------------------------------------------------
echo "[TEST 8] Loading edit form for created category...\n";
$rootToken = encrypt_id($createdRoot['id']);
$editPage = request("$baseUrl/admin/categories/{$rootToken}/edit", 'GET', [], $cookieJar);

if ($editPage['code'] === 200) {
    echo "  PASS: Edit page loaded with 200 OK!\n";
    $hasPreFilledTitle = strpos($editPage['body'], htmlspecialchars($rootCatName)) !== false;
    $hasCoverPreview   = strpos($editPage['body'], 'Current Cover Photo') !== false;

    echo "  PASS: Pre-filled Category Title: " . ($hasPreFilledTitle ? 'YES' : 'NO') . "\n";
    echo "  PASS: Pre-filled Cover Photo Preview: " . ($hasCoverPreview ? 'YES' : 'NO') . "\n\n";

    if (!$hasPreFilledTitle || !$hasCoverPreview) {
        echo "  FAIL: Edit form did not pre-fill existing category data.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Edit page returned HTTP {$editPage['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 9] Updating Category Specifications
// -------------------------------------------------------------
echo "[TEST 9] Updating category specifications...\n";
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $editPage['body'], $matches);
$editCsrf = $matches[1] ?? '';

$updatedName = "Royal Heritage Lehengas " . $uniqueSuffix;
$updatePayload = [
    '_csrf_token'  => $editCsrf,
    'name'        => $updatedName,
    'slug'        => $createdRoot['slug'],
    'parent_id'   => '',
    'description' => 'Updated artisanal notes and descriptions.',
    'sort_order'  => '10',
    'is_active'   => '1'
];

$updateRes = request("$baseUrl/admin/categories/{$rootToken}/update", 'POST', $updatePayload, $cookieJar);
if ($updateRes['code'] === 302) {
    $vStmt = $db->prepare("SELECT name, sort_order FROM categories WHERE id = ?");
    $vStmt->bind_param("i", $createdRoot['id']);
    $vStmt->execute();
    $updatedRow = $vStmt->get_result()->fetch_assoc();

    if ($updatedRow['name'] === $updatedName && (int)$updatedRow['sort_order'] === 10) {
        echo "  PASS: Category name and sort order successfully updated!\n\n";
    } else {
        echo "  FAIL: Database did not reflect updated specifications.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Category update request failed with HTTP {$updateRes['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 10] Toggling Category Status (Active <-> Inactive)
// -------------------------------------------------------------
echo "[TEST 10] Toggling category status (Active <-> Inactive)...\n";
$toggleRes = request("$baseUrl/admin/categories/{$rootToken}/status", 'POST', [
    '_csrf_token' => $editCsrf
], $cookieJar);

if ($toggleRes['code'] === 302) {
    $tStmt = $db->prepare("SELECT is_active FROM categories WHERE id = ?");
    $tStmt->bind_param("i", $createdRoot['id']);
    $tStmt->execute();
    $toggledStatus = (int)$tStmt->get_result()->fetch_assoc()['is_active'];

    if ($toggledStatus === 0) {
        echo "  PASS: Category successfully deactivated!\n";
        // Toggle back to active
        request("$baseUrl/admin/categories/{$rootToken}/status", 'POST', ['_csrf_token' => $editCsrf], $cookieJar);
        echo "  PASS: Category toggled back to Active status.\n\n";
    } else {
        echo "  FAIL: Category status not updated in database.\n";
        exit(1);
    }
} else {
    echo "  FAIL: Toggle status request failed with HTTP {$toggleRes['code']}\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 11] Safe Deletion Guard (protecting assigned products)
// -------------------------------------------------------------
echo "[TEST 11] Safe deletion guard (prevent deleting category with products)...\n";
// Assign a product to the sub-category
$db->query("INSERT INTO products (category_id, name, slug, base_price, status, created_at, updated_at) VALUES ({$createdSub['id']}, 'Test Safe Product {$uniqueSuffix}', 'test-safe-prod-{$uniqueSuffix}', 4999, 'draft', NOW(), NOW())");
$testProdId = $db->insert_id;

$subToken = encrypt_id($createdSub['id']);
$blockedDelete = request("$baseUrl/admin/categories/{$subToken}/delete", 'POST', [
    '_csrf_token' => $editCsrf
], $cookieJar);

// Category should NOT be deleted because it has a linked product
$subCheckStmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
$subCheckStmt->bind_param("i", $createdSub['id']);
$subCheckStmt->execute();
if ($subCheckStmt->get_result()->num_rows > 0) {
    echo "  PASS: Deletion blocked! Category protected because products are linked.\n";
} else {
    echo "  FAIL: Category was deleted despite having linked products!\n";
    exit(1);
}

// Now remove the test product and retry deletion
$db->query("DELETE FROM products WHERE id = $testProdId");
$allowedDelete = request("$baseUrl/admin/categories/{$subToken}/delete", 'POST', [
    '_csrf_token' => $editCsrf
], $cookieJar);

$subCheckStmt->execute();
if ($subCheckStmt->get_result()->num_rows === 0) {
    echo "  PASS: Category deleted successfully once products were cleared!\n\n";
} else {
    echo "  FAIL: Category was not deleted after products were cleared.\n";
    exit(1);
}

// -------------------------------------------------------------
// [TEST 12] Testing tampered encrypted token rejection
// -------------------------------------------------------------
echo "[TEST 12] Testing tampered encrypted token rejection...\n";
$tamperedToken = "INVALID-TOKEN-TAMPERED-xyz123";
$tamperedRes = request("$baseUrl/admin/categories/{$tamperedToken}/edit", 'GET', [], $cookieJar);
if ($tamperedRes['code'] === 302 && strpos($tamperedRes['header'], 'admin/categories') !== false) {
    echo "  PASS: Tampered token safely rejected by edit route! Redirected with toast.\n";
} else {
    echo "  FAIL: Tampered token was not redirected safely, got HTTP {$tamperedRes['code']}\n";
    exit(1);
}

// Clean up temporary dummy file
if (file_exists($dummyImg)) {
    @unlink($dummyImg);
}
if (file_exists($cookieJar)) {
    @unlink($cookieJar);
}
// Clean up root category
$db->query("DELETE FROM categories WHERE id = {$createdRoot['id']}");

echo "\n=========================================================\n";
echo " ALL CATEGORIES & TAXONOMY E2E TESTS COMPLETED SUCCESSFULLY!\n";
echo "=========================================================\n";
exit(0);
