<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}
require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
$res = $db->query("
    SELECT r.name as role_name, p.module, p.action 
    FROM role_permissions rp 
    JOIN roles r ON rp.role_id = r.id 
    JOIN permissions p ON rp.permission_id = p.id 
    WHERE p.module = 'categories'
");
while ($r = $res->fetch_assoc()) {
    echo $r['role_name'] . " -> " . $r['module'] . ":" . $r['action'] . "\n";
}
