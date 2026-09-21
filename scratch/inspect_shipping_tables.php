<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
$res = $db->query('SHOW TABLES');
echo "=== ALL TABLES IN jiyaji_collection ===\n";
$tables = [];
while ($r = $res->fetch_row()) {
    $tables[] = $r[0];
    echo " - " . $r[0] . "\n";
}

echo "\n=== SEARCHING FOR SHIPPING / PINCODE / ZONE TABLES ===\n";
foreach ($tables as $t) {
    if (stripos($t, 'ship') !== false || stripos($t, 'pin') !== false || stripos($t, 'zone') !== false || stripos($t, 'courier') !== false || stripos($t, 'deliv') !== false || stripos($t, 'rate') !== false) {
        echo "Found table: $t\n";
        $desc = $db->query("DESCRIBE $t");
        while ($c = $desc->fetch_assoc()) {
            echo "   {$c['Field']} | {$c['Type']} | {$c['Null']} | {$c['Key']}\n";
        }
    }
}

echo "\n=== CHECKING site_settings FOR SHIPPING KEYS ===\n";
$setRes = $db->query("SELECT setting_key, setting_value, label FROM site_settings WHERE setting_key LIKE '%ship%' OR setting_key LIKE '%deliv%' OR setting_key LIKE '%pin%'");
if ($setRes) {
    while ($s = $setRes->fetch_assoc()) {
        echo " - {$s['setting_key']}: {$s['setting_value']} ({$s['label']})\n";
    }
}

echo "\n=== ROWS IN shipping_zones ===\n";
$zRes = $db->query("SELECT * FROM shipping_zones");
while ($z = $zRes->fetch_assoc()) {
    echo "Zone #{$z['id']}: {$z['name']} | Pincodes len: " . strlen($z['pincodes'] ?? '') . "\n";
}

echo "\n=== ROWS IN shipping_rates ===\n";
$rRes = $db->query("SELECT * FROM shipping_rates");
while ($r = $rRes->fetch_assoc()) {
    echo "Rate #{$r['id']}: Zone {$r['zone_id']} | Method: {$r['method']} | Flat: ₹{$r['flat_rate']} | Free above: ₹{$r['free_above_order_value']} | Active: {$r['is_active']}\n";
}

echo "\nDone!\n";
