<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = [
    'id' => 1,
    'name' => 'Administrator',
    'email' => 'admin@jiyaji.com',
    'role' => 'super_admin',
    'role_name' => 'super_admin'
];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/Staff.php';
require_once __DIR__ . '/../app/Models/Page.php';
require_once __DIR__ . '/../app/Controllers/Admin/PageController.php';

ob_start();
$controller = new \App\Controllers\Admin\PageController();
$controller->index();
$html = ob_get_clean();

if (preg_match_all('/openPreviewModal\([0-9]+\)/', $html, $matches)) {
    echo "SUCCESS: Found " . count($matches[0]) . " clean numeric openPreviewModal calls!\n";
    echo "Sample: " . $matches[0][0] . "\n";
} else {
    echo "ERROR: Did not find clean openPreviewModal calls.\n";
}

// Check if raw JSON text leaked into table actions
if (strpos($html, 'one-of-a-kind bridal and festive ensembles') !== false) {
    // In window.pagesCatalog it is expected, but not outside <script>
    $parts = explode('<script>', $html);
    $tablePart = $parts[0];
    if (strpos($tablePart, 'one-of-a-kind') !== false) {
        echo "ERROR: Leaked text still in table HTML!\n";
    } else {
        echo "SUCCESS: Table HTML is completely clean of leaked content!\n";
    }
} else {
    echo "Note: Content string not found.\n";
}
