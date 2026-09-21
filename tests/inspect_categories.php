<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
$res = $db->query('DESCRIBE categories');
echo "CATEGORIES TABLE COLUMNS:\n";
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . ' | ' . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\nCURRENT CATEGORIES DATA:\n";
$res2 = $db->query('SELECT * FROM categories');
while ($row = $res2->fetch_assoc()) {
    print_r($row);
}
