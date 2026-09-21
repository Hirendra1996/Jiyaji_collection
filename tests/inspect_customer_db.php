<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();

$tables = $db->query('SHOW TABLES');
echo "ALL TABLES:\n";
$tableList = [];
while ($row = $tables->fetch_row()) {
    $tableList[] = $row[0];
    echo "- " . $row[0] . "\n";
}

echo "\n";
foreach (['users', 'customers', 'user_addresses', 'customer_addresses', 'orders'] as $tbl) {
    if (in_array($tbl, $tableList)) {
        echo "TABLE: $tbl\n";
        $desc = $db->query("DESCRIBE $tbl");
        while ($col = $desc->fetch_assoc()) {
            echo "  {$col['Field']} | {$col['Type']} | {$col['Null']} | {$col['Key']} | " . ($col['Default'] ?? 'NULL') . "\n";
        }
        $count = $db->query("SELECT COUNT(*) as cnt FROM $tbl")->fetch_assoc()['cnt'];
        echo "  TOTAL ROWS: $count\n\n";

        if ($tbl === 'customers') {
            $res = $db->query("SELECT id, name, email, phone, email_verified, is_active, created_at FROM customers LIMIT 10");
            echo "EXISTING CUSTOMERS:\n";
            while ($row = $res->fetch_assoc()) {
                print_r($row);
            }
            echo "\n";
        }
    }
}
