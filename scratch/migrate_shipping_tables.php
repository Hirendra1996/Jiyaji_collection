<?php
/**
 * Shipping Zones & Rates Database Migration
 * Enhances shipping_zones and shipping_rates schemas with codes, descriptions, transit days, and indexes.
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
echo "Starting Shipping & Pincodes schema migration...\n";

// 1. Check existing columns in shipping_zones
$existingZoneCols = [];
$res = $db->query("DESCRIBE shipping_zones");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $existingZoneCols[] = strtolower($r['Field']);
    }
}

if (!in_array('zone_code', $existingZoneCols)) {
    echo "Adding zone_code column to shipping_zones...\n";
    $db->query("ALTER TABLE shipping_zones ADD COLUMN zone_code VARCHAR(30) NULL AFTER name");
}

if (!in_array('description', $existingZoneCols)) {
    echo "Adding description column to shipping_zones...\n";
    $db->query("ALTER TABLE shipping_zones ADD COLUMN description VARCHAR(255) NULL AFTER zone_code");
}

if (!in_array('is_active', $existingZoneCols)) {
    echo "Adding is_active column to shipping_zones...\n";
    $db->query("ALTER TABLE shipping_zones ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER pincodes");
}

if (!in_array('updated_at', $existingZoneCols)) {
    echo "Adding updated_at column to shipping_zones...\n";
    $db->query("ALTER TABLE shipping_zones ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
}

// Check index on shipping_zones
$zoneIndexes = [];
$zIdxRes = $db->query("SHOW INDEX FROM shipping_zones");
if ($zIdxRes) {
    while ($idx = $zIdxRes->fetch_assoc()) {
        $zoneIndexes[] = $idx['Key_name'];
    }
}
if (!in_array('idx_shipping_zones_active', $zoneIndexes)) {
    echo "Adding idx_shipping_zones_active index...\n";
    $db->query("CREATE INDEX idx_shipping_zones_active ON shipping_zones (is_active)");
}

// 2. Check existing columns in shipping_rates
$existingRateCols = [];
$res2 = $db->query("DESCRIBE shipping_rates");
if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        $existingRateCols[] = strtolower($r['Field']);
    }
}

if (!in_array('title', $existingRateCols)) {
    echo "Adding title column to shipping_rates...\n";
    $db->query("ALTER TABLE shipping_rates ADD COLUMN title VARCHAR(100) NULL AFTER method");
}

if (!in_array('estimated_days', $existingRateCols)) {
    echo "Adding estimated_days column to shipping_rates...\n";
    $db->query("ALTER TABLE shipping_rates ADD COLUMN estimated_days VARCHAR(60) NULL AFTER free_above_order_value");
}

if (!in_array('created_at', $existingRateCols)) {
    echo "Adding created_at column to shipping_rates...\n";
    $db->query("ALTER TABLE shipping_rates ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active");
}

if (!in_array('updated_at', $existingRateCols)) {
    echo "Adding updated_at column to shipping_rates...\n";
    $db->query("ALTER TABLE shipping_rates ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
}

// Check indexes on shipping_rates
$rateIndexes = [];
$rIdxRes = $db->query("SHOW INDEX FROM shipping_rates");
if ($rIdxRes) {
    while ($idx = $rIdxRes->fetch_assoc()) {
        $rateIndexes[] = $idx['Key_name'];
    }
}
if (!in_array('idx_shipping_rates_active', $rateIndexes)) {
    echo "Adding idx_shipping_rates_active index...\n";
    $db->query("CREATE INDEX idx_shipping_rates_active ON shipping_rates (is_active)");
}

echo "\nMigration complete! Current columns in shipping_zones:\n";
$verifyZ = $db->query("DESCRIBE shipping_zones");
while ($c = $verifyZ->fetch_assoc()) {
    echo "  - {$c['Field']} ({$c['Type']})\n";
}

echo "\nCurrent columns in shipping_rates:\n";
$verifyR = $db->query("DESCRIBE shipping_rates");
while ($c = $verifyR->fetch_assoc()) {
    echo "  - {$c['Field']} ({$c['Type']})\n";
}

echo "\nDone!\n";
