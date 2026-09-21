<?php
/**
 * Sanity verification for Categories Taxonomy system in Staff Portal.
 */
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
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/Jiyaji_collection');
}

echo "=== STARTING PORTAL CATEGORIES SANITY TEST ===\n";

// 1. Authenticate as staff member with categories clearances
$_SESSION['staff_logged_in'] = true;
$_SESSION['staff_user'] = [
    'id'                => 10,
    'name'              => 'Taxonomy Specialist',
    'email'             => 'inventory@jiyaji.com',
    'role_name'         => 'inventory_specialist',
    'role_display_name' => 'Inventory Specialist'
];
$_SESSION['staff_permissions'] = [
    'categories:view',
    'categories:manage',
    'categories:create',
    'categories:edit',
    'categories:delete',
    'products:view'
];

echo "Mock staff session configured: " . $_SESSION['staff_user']['name'] . " (" . implode(', ', $_SESSION['staff_permissions']) . ")\n";

// 2. Test Category Model methods
echo "Testing Category Model KPIs...\n";
$kpis = App\Models\Category::getKPIs();
echo "KPIs: Total: {$kpis['total']}, Roots: {$kpis['root_categories']}, Subs: {$kpis['sub_categories']}, Active: {$kpis['active']}, Total Products: {$kpis['total_products']}\n";
assert(isset($kpis['total']), "KPI total must exist");

echo "Testing Category::getCandidateParents()...\n";
$candidateParents = App\Models\Category::getCandidateParents();
echo "Candidate Parents count: " . count($candidateParents) . "\n";

echo "Testing Category::getAll()...\n";
$categoriesData = App\Models\Category::getAll(['search' => '', 'parent_id' => 'all', 'status' => 'all'], 1, 10);
echo "Category::getAll found " . count($categoriesData['categories']) . " categories. Total: " . $categoriesData['pagination']['total_records'] . "\n";

$firstCat = $categoriesData['categories'][0] ?? null;
if (!$firstCat) {
    echo "No categories in DB, creating test category...\n";
    $testId = App\Models\Category::create([
        'name' => 'Sanity Test Collection',
        'slug' => 'sanity-test-collection',
        'parent_id' => null,
        'description' => 'Test collection for sanity check',
        'sort_order' => 99,
        'is_active' => 1
    ]);
    $firstCat = App\Models\Category::find($testId);
    $firstCat['encrypted_id'] = encrypt_id($testId);
}

$encryptedId = $firstCat['encrypted_id'] ?? encrypt_id($firstCat['id']);
echo "Testing with Category ID: {$firstCat['id']} (Encrypted: {$encryptedId})\n";

// 3. Test PortalCategoryController methods
$controller = new App\Controllers\Portal\PortalCategoryController();

// Test index()
echo "Testing PortalCategoryController::index()...\n";
$_GET = [
    'status'    => 'all',
    'parent_id' => 'all',
    'search'    => '',
    'sort'      => 'sort_order',
    'page'      => 1
];
$_SERVER['REQUEST_URI'] = '/portal/categories';
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
$controller->index();
$indexOutput = ob_get_clean();
assert(strpos($indexOutput, 'Categories &amp; Taxonomy') !== false, "Index heading missing");
assert(strpos($indexOutput, 'Root Collection') !== false || strpos($indexOutput, 'Primary Root') !== false || strpos($indexOutput, 'Primary Roots') !== false, "Index hierarchy missing");
echo "  -> index() rendered successfully (" . strlen($indexOutput) . " bytes)\n";

// Test show()
echo "Testing PortalCategoryController::show()...\n";
$_SERVER['REQUEST_URI'] = '/portal/categories/' . $encryptedId;
ob_start();
$controller->show($encryptedId);
$showOutput = ob_get_clean();
assert(strpos($showOutput, htmlspecialchars($firstCat['name'])) !== false, "Show must contain category name");
echo "  -> show() rendered successfully (" . strlen($showOutput) . " bytes)\n";

// Test create()
echo "Testing PortalCategoryController::create()...\n";
$_SERVER['REQUEST_URI'] = '/portal/categories/create';
ob_start();
$controller->create();
$createOutput = ob_get_clean();
assert(strpos($createOutput, 'Add New Luxury Category') !== false, "Create view must render correctly");
assert(strpos($createOutput, 'name="name"') !== false, "Create view must have name input");
assert(strpos($createOutput, 'name="slug"') !== false, "Create view must have slug input");
echo "  -> create() rendered successfully (" . strlen($createOutput) . " bytes)\n";

// Test edit()
echo "Testing PortalCategoryController::edit()...\n";
$_SERVER['REQUEST_URI'] = '/portal/categories/' . $encryptedId . '/edit';
ob_start();
$controller->edit($encryptedId);
$editOutput = ob_get_clean();
assert(strpos($editOutput, 'Edit Category:') !== false, "Edit view must render correctly");
assert(strpos($editOutput, htmlspecialchars($firstCat['name'])) !== false, "Edit view must contain category name");
echo "  -> edit() rendered successfully (" . strlen($editOutput) . " bytes)\n";

echo "\n=======================================================\n";
echo ">>> ALL CATEGORIES TAXONOMY TESTS PASSED WITH 0 WARNINGS! <<<\n";
echo "=======================================================\n";
