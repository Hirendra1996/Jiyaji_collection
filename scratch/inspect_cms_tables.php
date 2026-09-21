<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();

echo "--- Indexes on static_pages ---\n";
$res = $db->query("SHOW INDEX FROM static_pages");
while ($r = $res->fetch_assoc()) {
    echo " - {$r['Key_name']}: {$r['Column_name']} (Unique: " . ($r['Non_unique'] == 0 ? 'YES' : 'NO') . ")\n";
}
