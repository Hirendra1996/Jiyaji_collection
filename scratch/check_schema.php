<?php
$conn = new mysqli('localhost', 'root', '', 'jiyaji_collection');
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SHOW TABLES");
echo "=== ALL TABLES ===\n";
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
    echo "  " . $row[0] . "\n";
}

echo "\n=== COUPON / PROMO / DISCOUNT TABLES ===\n";
$found = false;
foreach ($tables as $t) {
    if (preg_match('/coupon|promo|discount/i', $t)) {
        $found = true;
        echo "\nTable: $t\n";
        $cols = $conn->query("DESCRIBE `$t`");
        while ($c = $cols->fetch_assoc()) {
            echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
        }
    }
}
if (!$found) echo "  None found.\n";

echo "\n=== COUPON-RELATED COLS IN ORDERS TABLE ===\n";
$cols = $conn->query("DESCRIBE orders");
while ($c = $cols->fetch_assoc()) {
    if (preg_match('/coupon|discount|promo/i', $c['Field'])) {
        echo "  {$c['Field']} | {$c['Type']}\n";
    }
}
