<?php
require_once __DIR__ . '/../vendor/autoload.php';
$d = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$d->load();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';

$db = App\Config\Database::connect();

foreach (['stock_movements', 'stock_alerts', 'product_variants', 'products'] as $table) {
    echo "=== SCHEMA: $table ===\n";
    $res = $db->query("DESCRIBE $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo sprintf("  %-25s %-20s %-8s %-8s %-15s %s\n", 
                $row['Field'], $row['Type'], $row['Null'], $row['Key'], $row['Default'] ?? 'NULL', $row['Extra']
            );
        }
    } else {
        echo "  Table not found or error: " . $db->error . "\n";
    }
    echo "\n";
}

echo "=== DATA COUNTS ===\n";
echo "stock_movements: " . $db->query("SELECT count(*) FROM stock_movements")->fetch_row()[0] . "\n";
echo "stock_alerts: " . $db->query("SELECT count(*) FROM stock_alerts")->fetch_row()[0] . "\n";
echo "product_variants: " . $db->query("SELECT count(*) FROM product_variants")->fetch_row()[0] . "\n";
echo "=== CREATE TABLE stock_movements ===\n";
echo $db->query("SHOW CREATE TABLE stock_movements")->fetch_row()[1] . "\n\n";

echo "=== CREATE TABLE stock_alerts ===\n";
echo $db->query("SHOW CREATE TABLE stock_alerts")->fetch_row()[1] . "\n\n";
