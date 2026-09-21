<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();

$tables = ['admins', 'roles', 'permissions', 'role_permissions'];

foreach ($tables as $t) {
    echo "=== TABLE: $t ===\n";
    $desc = $db->query("DESCRIBE $t");
    if ($desc) {
        while ($c = $desc->fetch_assoc()) {
            echo "  {$c['Field']} | {$c['Type']} | {$c['Null']} | {$c['Key']}\n";
        }
    } else {
        echo "Table does not exist!\n";
    }

    $rows = $db->query("SELECT * FROM $t LIMIT 10");
    echo "Sample data:\n";
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            // mask password if present
            if (isset($r['password'])) $r['password'] = substr($r['password'], 0, 15) . '...';
            echo "  " . json_encode($r) . "\n";
        }
    }
    echo "\n";
}

echo "Done!\n";
