<?php
/**
 * Search Analytics Database Migration
 * Enhances search_logs table with metadata columns and performance indexes.
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
echo "Starting search_logs schema migration...\n";

// 1. Check existing columns
$existingCols = [];
$res = $db->query("DESCRIBE search_logs");
if (!$res) {
    // If search_logs doesn't exist, create it from scratch
    echo "Creating search_logs table...\n";
    $createSql = "
        CREATE TABLE IF NOT EXISTS search_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id INT UNSIGNED NULL,
            keyword VARCHAR(255) NOT NULL,
            results_count INT UNSIGNED DEFAULT 0,
            ip_address VARCHAR(45) NULL,
            device_type ENUM('desktop','mobile','tablet') DEFAULT 'desktop',
            clicked_product_id INT UNSIGNED NULL,
            searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $db->query($createSql);
} else {
    while ($r = $res->fetch_assoc()) {
        $existingCols[] = strtolower($r['Field']);
    }

    // Add ip_address if missing
    if (!in_array('ip_address', $existingCols)) {
        echo "Adding ip_address column...\n";
        $db->query("ALTER TABLE search_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER results_count");
    }

    // Add device_type if missing
    if (!in_array('device_type', $existingCols)) {
        echo "Adding device_type column...\n";
        $db->query("ALTER TABLE search_logs ADD COLUMN device_type ENUM('desktop','mobile','tablet') DEFAULT 'desktop' AFTER ip_address");
    }

    // Add clicked_product_id if missing
    if (!in_array('clicked_product_id', $existingCols)) {
        echo "Adding clicked_product_id column...\n";
        $db->query("ALTER TABLE search_logs ADD COLUMN clicked_product_id INT UNSIGNED NULL AFTER device_type");
    }
}

// 2. Check and add indexes
$existingIndexes = [];
$idxRes = $db->query("SHOW INDEX FROM search_logs");
if ($idxRes) {
    while ($idx = $idxRes->fetch_assoc()) {
        $existingIndexes[] = $idx['Key_name'];
    }
}

if (!in_array('idx_search_keyword', $existingIndexes)) {
    echo "Adding idx_search_keyword index...\n";
    $db->query("CREATE INDEX idx_search_keyword ON search_logs (keyword)");
}

if (!in_array('idx_search_results', $existingIndexes)) {
    echo "Adding idx_search_results index...\n";
    $db->query("CREATE INDEX idx_search_results ON search_logs (results_count)");
}

if (!in_array('idx_search_date', $existingIndexes)) {
    echo "Adding idx_search_date index...\n";
    $db->query("CREATE INDEX idx_search_date ON search_logs (searched_at)");
}

echo "Migration complete! Current columns in search_logs:\n";
$verify = $db->query("DESCRIBE search_logs");
while ($col = $verify->fetch_assoc()) {
    echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " [Key: {$col['Key']}]\n";
}
