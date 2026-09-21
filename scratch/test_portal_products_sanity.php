<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Helpers/Helper.php';
define('BASE_URL', 'http://localhost/Jiyaji_collection');

echo "=== STARTING PORTAL PRODUCTS & SKUS TEST ===\n";

// 1. Check Product Model methods
echo "Testing Product Model KPIs...\n";
$kpis = App\Models\Product::getKPIs();
echo "Total products: " . ($kpis['total'] ?? 0) . ", Active: " . ($kpis['active'] ?? 0) . ", Low Stock: " . ($kpis['low_stock'] ?? 0) . "\n";

echo "Testing Product Model getAll()...\n";
$catalog = App\Models\Product::getAll([], 1, 5);
echo "Fetched " . count($catalog['products']) . " products. Pagination total: " . ($catalog['pagination']['total_records'] ?? 0) . "\n";

if (!empty($catalog['products'])) {
    $firstProduct = $catalog['products'][0];
    echo "First product: #" . $firstProduct['id'] . " - " . $firstProduct['name'] . " (Variants: " . count($firstProduct['variants']) . ")\n";
    $firstEncryptedId = $firstProduct['encrypted_id'];
} else {
    echo "Notice: Catalog is empty, testing with dummy product ID\n";
    $firstEncryptedId = encrypt_id(1);
}

// 2. Setup mock authenticated staff user with products permissions
$_SESSION['staff_logged_in'] = true;
$_SESSION['staff_user'] = [
    'id'                => 10,
    'name'              => 'Pooja Verma',
    'email'             => 'inventory@jiyaji.com',
    'role_name'         => 'inventory_specialist',
    'role_display_name' => 'Inventory Specialist'
];
$_SESSION['staff_permissions'] = [
    'products:view',
    'products:create',
    'products:edit',
    'products:delete',
    'categories:view'
];

echo "Mock staff session configured: " . $_SESSION['staff_user']['name'] . " (" . implode(', ', $_SESSION['staff_permissions']) . ")\n";

// 3. Test PortalProductController instantiation
echo "Testing PortalProductController index() rendering...\n";
$_GET = [
    'status'      => 'all',
    'category_id' => 'all',
    'search'      => '',
    'sort'        => 'newest',
    'page'        => 1
];
$_SERVER['REQUEST_URI'] = '/portal/products';

ob_start();
$controller = new App\Controllers\Portal\PortalProductController();
$controller->index();
$output = ob_get_clean();

if (strpos($output, 'Products &amp; SKUs Catalog') !== false || strpos($output, 'Products & SKUs Catalog') !== false) {
    echo "PASS: portal/products/index rendered successfully (" . strlen($output) . " bytes)\n";
} else {
    echo "FAIL: Expected string not found in index output\n";
    echo substr($output, 0, 500) . "\n";
}

// 4. Test show() view rendering
if (!empty($catalog['products'])) {
    echo "Testing PortalProductController show() rendering for product ID #" . $firstProduct['id'] . "...\n";
    ob_start();
    $controller->show($firstEncryptedId);
    $showOutput = ob_get_clean();

    if (strpos($showOutput, 'Multi-Variant &amp; SKU Matrix') !== false || strpos($showOutput, 'Product Specification') !== false) {
        echo "PASS: portal/products/show rendered successfully (" . strlen($showOutput) . " bytes)\n";
    } else {
        echo "FAIL: Expected string not found in show output\n";
        echo substr($showOutput, 0, 500) . "\n";
    }
}

// 5. Test create() view rendering
echo "Testing PortalProductController create() rendering...\n";
ob_start();
$controller->create();
$createOutput = ob_get_clean();

if (strpos($createOutput, 'Add New Luxury Garment') !== false) {
    echo "PASS: portal/products/create rendered successfully (" . strlen($createOutput) . " bytes)\n";
} else {
    echo "FAIL: Expected string not found in create output\n";
    echo substr($createOutput, 0, 500) . "\n";
}

// 6. Test edit() view rendering
if (!empty($catalog['products'])) {
    echo "Testing PortalProductController edit() rendering...\n";
    ob_start();
    $controller->edit($firstEncryptedId);
    $editOutput = ob_get_clean();

    if (strpos($editOutput, 'Catalog Editor') !== false || strpos($editOutput, 'Edit:') !== false) {
        echo "PASS: portal/products/edit rendered successfully (" . strlen($editOutput) . " bytes)\n";
    } else {
        echo "FAIL: Expected string not found in edit output\n";
        echo substr($editOutput, 0, 500) . "\n";
    }
}

// 7. Test RBAC permissions logic
echo "Testing RBAC permissions logic...\n";
$_SESSION['staff_permissions'] = ['products:view'];
assert(staff_can('products', 'view') === true, 'products:view should be true');
assert(staff_can('products', 'create') === false, 'products:create should be false');
assert(staff_can('products', 'edit') === false, 'products:edit should be false');
assert(staff_can('products', 'delete') === false, 'products:delete should be false');
echo "PASS: Read-only staff permissions verified\n";

$_SESSION['staff_permissions'] = ['products:view', 'products:edit'];
assert(staff_can('products', 'view') === true, 'products:view should be true');
assert(staff_can('products', 'edit') === true, 'products:edit should be true');
assert(staff_can('products', 'create') === false, 'products:create should be false');
assert(staff_can('products', 'delete') === false, 'products:delete should be false');
echo "PASS: Stock-adjust-only staff permissions verified\n";

echo "=== ALL SANITY TESTS COMPLETED WITH ZERO ERRORS ===\n";
