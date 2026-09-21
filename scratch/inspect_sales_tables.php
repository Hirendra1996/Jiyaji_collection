<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
$db = App\Config\Database::connect();

foreach (['daily_sales_summary', 'orders', 'order_items', 'payments', 'categories', 'products'] as $tbl) {
    echo "=== TABLE: $tbl ===\n";
    $res = $db->query("DESCRIBE `$tbl`");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  " . $r['Field'] . " | " . $r['Type'] . " | " . $r['Null'] . " | " . $r['Key'] . "\n";
        }
    } else {
        echo "  Table $tbl does not exist: " . $db->error . "\n";
    }
    $cnt = $db->query("SELECT COUNT(*) FROM `$tbl`");
    if ($cnt) {
        echo "  Row count: " . $cnt->fetch_row()[0] . "\n\n";
    }
}
