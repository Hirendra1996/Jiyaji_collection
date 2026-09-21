<?php
require_once __DIR__ . '/../vendor/autoload.php';
$d = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$d->load();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';

$db = App\Config\Database::connect();
$res = $db->query("SELECT a.id, a.name, a.email, a.is_active, r.name as role_name FROM admins a LEFT JOIN roles r ON a.role_id = r.id");
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . ' | ' . $row['name'] . ' | ' . $row['email'] . ' | Role: ' . $row['role_name'] . ' | Active: ' . $row['is_active'] . PHP_EOL;
}
