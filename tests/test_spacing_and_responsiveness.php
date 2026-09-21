<?php
/**
 * Verification Test: Field Spacing & Responsive Grid Integrity
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

$css = file_get_contents(__DIR__ . '/../public/assets/css/admin.css');

// Check 1: Old excessive left padding removed from .form-input
if (strpos($css, 'padding: 0 16px 0 44px;') !== false) {
    echo "FAIL: .form-input still contains the old 44px left padding!\n";
    exit(1);
}
echo "PASS: Old 44px left padding removed from global .form-input.\n";

// Check 2: Standard padding in place
if (strpos($css, 'padding: 0 14px;') === false) {
    echo "FAIL: .form-input does not have standard 0 14px padding!\n";
    exit(1);
}
echo "PASS: .form-input has clean standard padding (0 14px).\n";

// Check 3: Scoped input-icon padding
if (strpos($css, '.input-group .input-icon ~ .form-input') === false) {
    echo "FAIL: .input-group .input-icon selector missing!\n";
    exit(1);
}
echo "PASS: 44px icon padding scoped strictly to .input-group icons.\n";

// Check 4: Table inputs compact styling
if (strpos($css, '#variantsTable .form-input') === false) {
    echo "FAIL: #variantsTable .form-input compact styling missing!\n";
    exit(1);
}
echo "PASS: Variant table inputs have dedicated compact padding.\n";

// Check 5: Responsive layout classes
$requiredClasses = [
    '.product-form-grid',
    '.grid-2-cols',
    '.catalog-kpi-grid',
    '.catalog-tabs-bar',
    '.catalog-filter-form',
    '.media-preview-grid'
];
foreach ($requiredClasses as $cls) {
    if (strpos($css, $cls) === false) {
        echo "FAIL: Missing responsive class $cls in admin.css!\n";
        exit(1);
    }
}
echo "PASS: All responsive catalog and form classes defined.\n";

// Check 6: Responsive breakpoints
$requiredBreakpoints = [
    '@media (max-width: 1200px)',
    '@media (max-width: 1024px)',
    '@media (max-width: 768px)',
    '@media (max-width: 640px)',
    '@media (max-width: 480px)'
];
foreach ($requiredBreakpoints as $bp) {
    if (strpos($css, $bp) === false) {
        echo "FAIL: Missing media query breakpoint $bp in admin.css!\n";
        exit(1);
    }
}
echo "PASS: All responsive media queries (1200px, 1024px, 768px, 640px, 480px) in place.\n";

// Check 7: create.php uses responsive classes
$create = file_get_contents(__DIR__ . '/../app/Views/admin/products/create.php');
if (strpos($create, 'class="product-form-grid"') === false) {
    echo "FAIL: create.php does not use .product-form-grid!\n";
    exit(1);
}
if (strpos($create, 'class="grid-2-cols"') === false) {
    echo "FAIL: create.php does not use .grid-2-cols!\n";
    exit(1);
}
if (strpos($create, 'class="media-preview-grid"') === false) {
    echo "FAIL: create.php does not use .media-preview-grid!\n";
    exit(1);
}
echo "PASS: create.php integrated with responsive grid and media classes.\n";

// Check 8: edit.php uses responsive classes
$edit = file_get_contents(__DIR__ . '/../app/Views/admin/products/edit.php');
if (strpos($edit, 'class="product-form-grid"') === false) {
    echo "FAIL: edit.php does not use .product-form-grid!\n";
    exit(1);
}
if (strpos($edit, 'class="grid-2-cols"') === false) {
    echo "FAIL: edit.php does not use .grid-2-cols!\n";
    exit(1);
}
if (strpos($edit, 'class="media-preview-grid"') === false) {
    echo "FAIL: edit.php does not use .media-preview-grid!\n";
    exit(1);
}
echo "PASS: edit.php integrated with responsive grid and media classes.\n";

// Check 9: index.php uses responsive classes
$index = file_get_contents(__DIR__ . '/../app/Views/admin/products/index.php');
if (strpos($index, 'class="catalog-kpi-grid"') === false) {
    echo "FAIL: index.php does not use .catalog-kpi-grid!\n";
    exit(1);
}
if (strpos($index, 'class="catalog-tabs-bar"') === false) {
    echo "FAIL: index.php does not use .catalog-tabs-bar!\n";
    exit(1);
}
if (strpos($index, 'class="catalog-filter-form"') === false) {
    echo "FAIL: index.php does not use .catalog-filter-form!\n";
    exit(1);
}
echo "PASS: index.php integrated with responsive KPI, tabs, and filter classes.\n";

// Check 10: Product::getAll pagination keys & zero warnings test
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Models/Product.php';
$prodData = App\Models\Product::getAll(['status' => 'all'], 1, 15);
if (!array_key_exists('has_prev', $prodData['pagination'])) {
    echo "FAIL: Product::getAll pagination missing has_prev key!\n";
    exit(1);
}
if (!array_key_exists('has_next', $prodData['pagination'])) {
    echo "FAIL: Product::getAll pagination missing has_next key!\n";
    exit(1);
}
echo "PASS: Product::getAll pagination includes has_prev and has_next booleans.\n";

// Check 11: Render products/index.php view with simulated session and check for warnings
$_SESSION['admin_user'] = ['id' => 1, 'name' => 'Admin', 'email' => 'admin@jiyaji.com', 'role_name' => 'super_admin'];
$products = $prodData['products'];
$pagination = $prodData['pagination'];
$kpis = App\Models\Product::getKPIs();
$categories = App\Models\Product::getAllCategories();
$filters = ['status' => 'all', 'category_id' => 'all', 'search' => '', 'sort' => 'newest'];
$title = 'Products & SKUs Catalog';

// Test page 1 and page 2 rendering
foreach ([1, 2] as $testPage) {
    $pagination['current_page'] = $testPage;
    $pagination['total_pages'] = 3;
    $pagination['has_prev'] = ($testPage > 1);
    $pagination['has_next'] = ($testPage < 3);

    ob_start();
    include __DIR__ . '/../app/Views/admin/products/index.php';
    $renderedHtml = ob_get_clean();

    if (strpos($renderedHtml, 'Warning:') !== false || strpos($renderedHtml, 'has_prev') !== false || strpos($renderedHtml, 'has_next') !== false) {
        echo "FAIL: PHP Warning detected during page $testPage rendering!\n";
        exit(1);
    }
}
echo "PASS: products/index.php rendered across pages 1 and 2 with zero PHP warnings.\n";

echo "\n=========================================================\n";
echo " ALL FIELD SPACING & RESPONSIVENESS VERIFICATIONS PASSED!\n";
echo "=========================================================\n";
exit(0);
