<?php
require_once __DIR__ . '/../vendor/autoload.php';
$d = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$d->load();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';

$db = App\Config\Database::connect();
$res = $db->query("SELECT id, module, action, label, description FROM permissions ORDER BY module, action");
echo "=== ALL PERMISSIONS ===\n";
while ($row = $res->fetch_assoc()) {
    echo sprintf("  [%-15s : %-15s] %s\n", $row['module'], $row['action'], $row['label'] ?? '');
}
