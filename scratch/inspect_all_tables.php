<?php
require_once __DIR__ . '/../vendor/autoload.php';
$d = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$d->load();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';

$db = App\Config\Database::connect();
$res = $db->query("SHOW TABLES");
echo "=== ALL DATABASE TABLES ===\n";
while ($row = $res->fetch_row()) {
    echo "- " . $row[0] . "\n";
}
