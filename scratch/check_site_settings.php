<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
$res = $db->query("SELECT setting_key, label, setting_value FROM site_settings ORDER BY setting_key");
echo "Found " . $res->num_rows . " settings:\n";
while ($r = $res->fetch_assoc()) {
    echo "• " . str_pad($r['setting_key'], 30) . " | " . str_pad($r['label'] ?? '', 30) . " | " . substr($r['setting_value'] ?? '', 0, 50) . "\n";
}
