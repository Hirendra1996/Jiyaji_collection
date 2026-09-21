<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
$db = App\Config\Database::connect();

foreach (['payment_methods_config', 'payments', 'site_settings'] as $tbl) {
    echo "=== TABLE: $tbl ===\n";
    $res = $db->query("DESCRIBE `$tbl`");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  " . $r['Field'] . " | " . $r['Type'] . " | " . $r['Null'] . " | " . $r['Key'] . " | " . ($r['Default'] ?? 'NULL') . "\n";
        }
    } else {
        echo "  Table does not exist or error: " . $db->error . "\n";
    }

    $cntRes = $db->query("SELECT COUNT(*) FROM `$tbl`");
    if ($cntRes) {
        echo "  Row count: " . $cntRes->fetch_row()[0] . "\n";
    }

    $sample = $db->query("SELECT * FROM `$tbl` LIMIT 5");
    if ($sample && $sample->num_rows > 0) {
        echo "  Sample data:\n";
        while ($row = $sample->fetch_assoc()) {
            print_r($row);
        }
    }
    echo "\n";
}
