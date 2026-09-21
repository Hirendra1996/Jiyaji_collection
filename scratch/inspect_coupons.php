<?php
require_once __DIR__ . '/../app/Config/database.php';
$db = Database::getInstance()->getConnection();

// Check for coupon-related tables
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "All tables:\n";
foreach ($tables as $t) echo "  - $t\n";

// Check coupon tables specifically
echo "\nCoupon-related tables:\n";
foreach ($tables as $t) {
    if (stripos($t, 'coupon') !== false || stripos($t, 'promo') !== false || stripos($t, 'discount') !== false) {
        echo "  FOUND: $t\n";
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "    {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
        }
    }
}
