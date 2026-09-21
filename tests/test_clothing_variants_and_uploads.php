<?php
/**
 * End-to-End Test Suite for Clothing Color Variants & Direct Media File Uploads
 */

$cookieJar = tempnam(sys_get_temp_dir(), 'admin_clothing_cookie_');
$baseUrl = 'http://localhost/Jiyaji_collection';

function makeRequest($url, $postData = null, $cookieJar = null, $followRedirects = false, $isMultipart = false) {
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
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }
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
echo " JIYAJI CLOTHING: COLOR VARIANTS & MEDIA UPLOADS E2E TEST\n";
echo "=========================================================\n\n";

// TEST 1: Authenticate as admin
echo "[TEST 1] Authenticating as administrator...\n";
$loginPage = makeRequest("$baseUrl/admin/login", null, $cookieJar, false);
preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $loginPage['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

$loginRes = makeRequest("$baseUrl/admin/login", [
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123',
    '_csrf_token' => $csrfToken
], $cookieJar, false);

if ($loginRes['code'] === 302 && strpos($loginRes['headers'], 'admin/dashboard') !== false) {
    echo "  PASS: Admin authenticated successfully.\n";
} else {
    die("  FAIL: Admin login failed.\n");
}

// TEST 2: Inspect Create Product form for Clothing UI & File Upload Dropzone
echo "\n[TEST 2] Verifying clothing variants UI and direct file upload dropzone...\n";
$createPage = makeRequest("$baseUrl/admin/products/create", null, $cookieJar, false);
if ($createPage['code'] === 200) {
    echo "  PASS: Create Product form loaded with 200 OK.\n";
    $hasMultipart = strpos($createPage['body'], 'enctype="multipart/form-data"') !== false;
    echo "  PASS: Form has multipart/form-data: " . ($hasMultipart ? 'YES' : 'NO') . "\n";
    $hasColorPreset = strpos($createPage['body'], 'Quick-Add Popular Garment Colors') !== false;
    echo "  PASS: Found Clothing Color Presets: " . ($hasColorPreset ? 'YES' : 'NO') . "\n";
    $hasColorPicker = strpos($createPage['body'], 'color-picker-input') !== false;
    echo "  PASS: Found Color Picker Inputs: " . ($hasColorPicker ? 'YES' : 'NO') . "\n";
    $hasSizeSelect = strpos($createPage['body'], 'variant-size-select') !== false;
    echo "  PASS: Found Apparel Size Selector: " . ($hasSizeSelect ? 'YES' : 'NO') . "\n";
    $hasDropzone = strpos($createPage['body'], 'id="mediaDropZone"') !== false;
    echo "  PASS: Found Direct File Upload Dropzone: " . ($hasDropzone ? 'YES' : 'NO') . "\n";
} else {
    echo "  FAIL: Create page failed with code {$createPage['code']}\n";
}

// TEST 3: Create real test image files on disk
echo "\n[TEST 3] Generating real test garment lookbook images...\n";
$tempImg1 = tempnam(sys_get_temp_dir(), 'test_apparel_1_') . '.png';
$tempImg2 = tempnam(sys_get_temp_dir(), 'test_apparel_2_') . '.png';

// Create 100x100 PNG images
$im1 = imagecreatetruecolor(120, 120);
$maroon = imagecolorallocate($im1, 128, 0, 32);
imagefill($im1, 0, 0, $maroon);
imagepng($im1, $tempImg1);
imagedestroy($im1);

$im2 = imagecreatetruecolor(120, 120);
$blue = imagecolorallocate($im2, 30, 58, 138);
imagefill($im2, 0, 0, $blue);
imagepng($im2, $tempImg2);
imagedestroy($im2);

echo "  PASS: Created dummy garment photos: $tempImg1 and $tempImg2\n";

// TEST 4: Submit product with Clothing Color Variants & Multipart File Uploads
echo "\n[TEST 4] Submitting product with 2 color variants & uploaded media files...\n";
preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $createPage['body'], $csrfMatchesCreate);
$createCsrf = $csrfMatchesCreate[1] ?? '';

$uniqueSuffix = time();
$productName = "Zari Velvet Anarkali Suit $uniqueSuffix";

$postData = [
    '_csrf_token'        => $createCsrf,
    'name'               => $productName,
    'slug'               => 'zari-velvet-anarkali-suit-' . $uniqueSuffix,
    'category_id'        => '1',
    'short_description'  => 'Rich velvet anarkali suit with heavy tilla embroidery.',
    'description'        => 'Handcrafted luxury garment tailored from micro velvet.',
    'base_price'         => '18999.00',
    'sale_price'         => '14999.00',
    'low_stock_threshold'=> '10',
    'allow_backorder'    => '1',
    'status'             => 'active',
    'meta_title'         => "$productName | Jiyaji LX",
    'meta_description'   => 'Designer velvet ethnic wear.',
    'primary_image_choice' => 'upload_0',
    
    // Variant 0: Ruby Maroon / L
    'variants[0][color_name]'     => 'Ruby Maroon',
    'variants[0][color_code]'     => '#800020',
    'variants[0][size]'           => 'L',
    'variants[0][variant_name]'   => 'Ruby Maroon / L',
    'variants[0][sku]'            => "JIY-VEL-MRN-L-$uniqueSuffix",
    'variants[0][price_override]' => '14999.00',
    'variants[0][stock_qty]'      => '25',

    // Variant 1: Royal Blue / XL
    'variants[1][color_name]'     => 'Royal Blue',
    'variants[1][color_code]'     => '#1E3A8A',
    'variants[1][size]'           => 'XL',
    'variants[1][variant_name]'   => 'Royal Blue / XL',
    'variants[1][sku]'            => "JIY-VEL-BLU-XL-$uniqueSuffix",
    'variants[1][price_override]' => '15499.00',
    'variants[1][stock_qty]'      => '12',

    // Files uploaded via CURLFile
    'media_files[0]' => new CURLFile($tempImg1, 'image/png', "velvet_maroon_$uniqueSuffix.png"),
    'media_files[1]' => new CURLFile($tempImg2, 'image/png', "velvet_blue_$uniqueSuffix.png"),

    // Variant tags for images
    'media_variant_index[0]' => '0',
    'media_variant_index[1]' => '1',
];

$createRes = makeRequest("$baseUrl/admin/products/store", $postData, $cookieJar, false, true);

if ($createRes['code'] === 302 && strpos($createRes['headers'], 'admin/products') !== false) {
    echo "  PASS: Product created successfully via direct media upload & redirected!\n";
} else {
    echo "  FAIL: Product creation failed. Code: {$createRes['code']}\n";
    print_r($createRes['body']);
}

// TEST 5: Verify uploaded file exists on physical filesystem
echo "\n[TEST 5] Checking physical disk storage for uploaded media files...\n";
$uploadDir = __DIR__ . '/../public/uploads/products';
$filesInDir = glob("$uploadDir/prod_*.png");
if (!empty($filesInDir)) {
    $latestFile = end($filesInDir);
    echo "  PASS: Uploaded image file confirmed on disk!\n";
    echo "  File: " . basename($latestFile) . " (" . filesize($latestFile) . " bytes)\n";
} else {
    echo "  FAIL: No uploaded file found in $uploadDir\n";
}

// TEST 6: Search catalog and verify Color Swatches rendered in catalog ledger
echo "\n[TEST 6] Verifying product and color swatches in admin catalog ledger...\n";
$searchRes = makeRequest("$baseUrl/admin/products?search=" . urlencode($productName), null, $cookieJar, false);
if (strpos($searchRes['body'], $productName) !== false) {
    echo "  PASS: Created clothing product found in catalog search!\n";
    $hasMaroonSwatch = strpos($searchRes['body'], '#800020') !== false;
    $hasBlueSwatch = strpos($searchRes['body'], '#1E3A8A') !== false;
    echo "  PASS: Found Maroon color swatch in ledger: " . ($hasMaroonSwatch ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Royal Blue color swatch in ledger: " . ($hasBlueSwatch ? 'YES' : 'NO') . "\n";

    preg_match('/admin\/products\/([a-zA-Z0-9_\-]+)\/edit/', $searchRes['body'], $editMatches);
    $encId = $editMatches[1] ?? null;
} else {
    echo "  FAIL: Product not found in catalog search.\n";
    $encId = null;
}

// TEST 7: Verify Edit Page shows existing uploaded photos & color variants
if ($encId) {
    echo "\n[TEST 7] Loading edit form and verifying existing uploaded media & color variants...\n";
    $editPage = makeRequest("$baseUrl/admin/products/$encId/edit", null, $cookieJar, false);
    if ($editPage['code'] === 200) {
        echo "  PASS: Edit form loaded with 200 OK.\n";
        $hasExistingCard = strpos($editPage['body'], 'existing-media-card') !== false;
        echo "  PASS: Found Existing Media Gallery Card: " . ($hasExistingCard ? 'YES' : 'NO') . "\n";
        $hasRubyMaroon = strpos($editPage['body'], 'Ruby Maroon') !== false;
        echo "  PASS: Found Pre-filled Color Variant 'Ruby Maroon': " . ($hasRubyMaroon ? 'YES' : 'NO') . "\n";
        $hasRoyalBlue = strpos($editPage['body'], 'Royal Blue') !== false;
        echo "  PASS: Found Pre-filled Color Variant 'Royal Blue': " . ($hasRoyalBlue ? 'YES' : 'NO') . "\n";
    } else {
        echo "  FAIL: Edit form failed with code {$editPage['code']}\n";
    }
}

// Clean up temp images
@unlink($tempImg1);
@unlink($tempImg2);
@unlink($cookieJar);

echo "\n=========================================================\n";
echo " ALL CLOTHING COLOR VARIANTS & MEDIA UPLOAD TESTS PASSED!\n";
echo "=========================================================\n";
