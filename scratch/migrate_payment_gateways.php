<?php
/**
 * Payment Gateways Database Migration
 * Enhances payment_methods_config and payments tables with configuration columns and performance indexes.
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
echo "Starting payment_methods_config schema migration...\n";

// 1. Check existing columns in payment_methods_config
$existingCols = [];
$res = $db->query("DESCRIBE payment_methods_config");
if (!$res) {
    echo "Creating payment_methods_config table...\n";
    $createSql = "
        CREATE TABLE IF NOT EXISTS payment_methods_config (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            method VARCHAR(60) NOT NULL UNIQUE,
            title VARCHAR(100) NULL,
            description VARCHAR(255) NULL,
            is_enabled TINYINT(1) DEFAULT 1,
            cod_min_order_value DECIMAL(10,2) NULL,
            cod_pincode_whitelist TEXT NULL,
            config_data LONGTEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $db->query($createSql);
} else {
    while ($r = $res->fetch_assoc()) {
        $existingCols[] = strtolower($r['Field']);
    }

    if (!in_array('title', $existingCols)) {
        echo "Adding title column...\n";
        $db->query("ALTER TABLE payment_methods_config ADD COLUMN title VARCHAR(100) NULL AFTER method");
    }

    if (!in_array('description', $existingCols)) {
        echo "Adding description column...\n";
        $db->query("ALTER TABLE payment_methods_config ADD COLUMN description VARCHAR(255) NULL AFTER title");
    }

    if (!in_array('config_data', $existingCols)) {
        echo "Adding config_data column...\n";
        $db->query("ALTER TABLE payment_methods_config ADD COLUMN config_data LONGTEXT NULL AFTER cod_pincode_whitelist");
    }
}

// 2. Check indexes on payments table
$payIndexes = [];
$idxRes = $db->query("SHOW INDEX FROM payments");
if ($idxRes) {
    while ($idx = $idxRes->fetch_assoc()) {
        $payIndexes[] = $idx['Key_name'];
    }
}

if (!in_array('idx_payments_gateway', $payIndexes)) {
    echo "Adding idx_payments_gateway index...\n";
    $db->query("CREATE INDEX idx_payments_gateway ON payments (gateway)");
}

if (!in_array('idx_payments_status', $payIndexes)) {
    echo "Adding idx_payments_status index...\n";
    $db->query("CREATE INDEX idx_payments_status ON payments (status)");
}

echo "Migration complete! Current columns in payment_methods_config:\n";
$verify = $db->query("DESCRIBE payment_methods_config");
while ($col = $verify->fetch_assoc()) {
    echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " [Key: {$col['Key']}]\n";
}
