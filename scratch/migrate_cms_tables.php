<?php
/**
 * Static CMS Pages Database Migration
 * Enhances static_pages table with excerpt, SEO fields, is_system flag, created_at, and indexes.
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
echo "Starting Static CMS Pages schema migration...\n";

// 1. Check existing columns in static_pages
$existingCols = [];
$res = $db->query("DESCRIBE static_pages");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $existingCols[] = strtolower($r['Field']);
    }
}
echo "Existing columns in static_pages: " . implode(', ', $existingCols) . "\n";

// Add excerpt
if (!in_array('excerpt', $existingCols)) {
    echo "Adding excerpt column...\n";
    $db->query("ALTER TABLE static_pages ADD COLUMN excerpt VARCHAR(500) NULL AFTER title");
}

// Add meta_title
if (!in_array('meta_title', $existingCols)) {
    echo "Adding meta_title column...\n";
    $db->query("ALTER TABLE static_pages ADD COLUMN meta_title VARCHAR(255) NULL AFTER content");
}

// Add meta_description
if (!in_array('meta_description', $existingCols)) {
    echo "Adding meta_description column...\n";
    $db->query("ALTER TABLE static_pages ADD COLUMN meta_description VARCHAR(500) NULL AFTER meta_title");
}

// Add meta_keywords
if (!in_array('meta_keywords', $existingCols)) {
    echo "Adding meta_keywords column...\n";
    $db->query("ALTER TABLE static_pages ADD COLUMN meta_keywords VARCHAR(255) NULL AFTER meta_description");
}

// Add is_system
if (!in_array('is_system', $existingCols)) {
    echo "Adding is_system column...\n";
    $db->query("ALTER TABLE static_pages ADD COLUMN is_system TINYINT(1) DEFAULT 0 AFTER is_active");
}

// Add created_at
if (!in_array('created_at', $existingCols)) {
    echo "Adding created_at column...\n";
    $db->query("ALTER TABLE static_pages ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER updated_at");
}

// Check index on updated_by
$existingIndexes = [];
$idxRes = $db->query("SHOW INDEX FROM static_pages");
if ($idxRes) {
    while ($r = $idxRes->fetch_assoc()) {
        $existingIndexes[] = $r['Key_name'];
    }
}
if (!in_array('idx_static_pages_updated_by', $existingIndexes) && !in_array('updated_by', $existingIndexes)) {
    echo "Adding index on updated_by...\n";
    $db->query("ALTER TABLE static_pages ADD INDEX idx_static_pages_updated_by (updated_by)");
}

echo "Static CMS Pages schema migration completed successfully!\n";
