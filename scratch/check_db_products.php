<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}
require 'app/Config/Database.php';

$db = App\Config\Database::connect();
$res = $db->query("SELECT id, name, status FROM products");
$count = 0;
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Status: {$r['status']}\n";
    $count++;
}
echo "Total in DB: {$count}\n";
