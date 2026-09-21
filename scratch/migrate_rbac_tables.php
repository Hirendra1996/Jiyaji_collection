<?php
/**
 * Staff Accounts & RBAC Database Migration
 * Enhances admins, roles, permissions, and role_permissions tables.
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
echo "Starting Staff & RBAC schema migration...\n";

// 1. Check columns in admins table
$existingAdminCols = [];
$resA = $db->query("DESCRIBE admins");
if ($resA) {
    while ($r = $resA->fetch_assoc()) {
        $existingAdminCols[] = strtolower($r['Field']);
    }
}

if (!in_array('phone', $existingAdminCols)) {
    echo "Adding phone column to admins...\n";
    $db->query("ALTER TABLE admins ADD COLUMN phone VARCHAR(20) NULL AFTER email");
}

if (!in_array('last_login_at', $existingAdminCols)) {
    echo "Adding last_login_at column to admins...\n";
    $db->query("ALTER TABLE admins ADD COLUMN last_login_at TIMESTAMP NULL AFTER created_by");
}

// Check index on admins(role_id)
$adminIndexes = [];
$aIdxRes = $db->query("SHOW INDEX FROM admins");
if ($aIdxRes) {
    while ($idx = $aIdxRes->fetch_assoc()) {
        $adminIndexes[] = $idx['Key_name'];
    }
}
if (!in_array('idx_admins_role', $adminIndexes)) {
    echo "Adding idx_admins_role index...\n";
    $db->query("CREATE INDEX idx_admins_role ON admins (role_id)");
}

// 2. Check columns in roles table
$existingRoleCols = [];
$resR = $db->query("DESCRIBE roles");
if ($resR) {
    while ($r = $resR->fetch_assoc()) {
        $existingRoleCols[] = strtolower($r['Field']);
    }
}

if (!in_array('display_name', $existingRoleCols)) {
    echo "Adding display_name column to roles...\n";
    $db->query("ALTER TABLE roles ADD COLUMN display_name VARCHAR(100) NULL AFTER name");
}

if (!in_array('is_system', $existingRoleCols)) {
    echo "Adding is_system column to roles...\n";
    $db->query("ALTER TABLE roles ADD COLUMN is_system TINYINT(1) DEFAULT 0 AFTER description");
}

if (!in_array('updated_at', $existingRoleCols)) {
    echo "Adding updated_at column to roles...\n";
    $db->query("ALTER TABLE roles ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
}

// Update existing default roles with friendly display names and system flags
$db->query("UPDATE roles SET display_name = 'Super Administrator', is_system = 1 WHERE name = 'super_admin'");
$db->query("UPDATE roles SET display_name = 'Orders & Logistics Manager', is_system = 1 WHERE name = 'order_manager'");
$db->query("UPDATE roles SET display_name = 'Catalog & Inventory Specialist', is_system = 1 WHERE name = 'inventory_manager'");
$db->query("UPDATE roles SET display_name = 'Customer Support & Concierge', is_system = 1 WHERE name = 'support_staff'");

// 3. Check columns in permissions table
$existingPermCols = [];
$resP = $db->query("DESCRIBE permissions");
if ($resP) {
    while ($r = $resP->fetch_assoc()) {
        $existingPermCols[] = strtolower($r['Field']);
    }
}

if (!in_array('label', $existingPermCols)) {
    echo "Adding label column to permissions...\n";
    $db->query("ALTER TABLE permissions ADD COLUMN label VARCHAR(100) NULL AFTER action");
}

if (!in_array('description', $existingPermCols)) {
    echo "Adding description column to permissions...\n";
    $db->query("ALTER TABLE permissions ADD COLUMN description VARCHAR(255) NULL AFTER label");
}

// Check unique index on permissions(module, action)
$permIndexes = [];
$pIdxRes = $db->query("SHOW INDEX FROM permissions");
if ($pIdxRes) {
    while ($idx = $pIdxRes->fetch_assoc()) {
        $permIndexes[] = $idx['Key_name'];
    }
}
if (!in_array('uniq_permission_module_action', $permIndexes)) {
    echo "Adding uniq_permission_module_action index...\n";
    $db->query("CREATE UNIQUE INDEX uniq_permission_module_action ON permissions (module, action)");
}

echo "\nMigration complete! Current columns in admins:\n";
$vA = $db->query("DESCRIBE admins");
while ($c = $vA->fetch_assoc()) echo "  - {$c['Field']} ({$c['Type']})\n";

echo "\nCurrent columns in roles:\n";
$vR = $db->query("DESCRIBE roles");
while ($c = $vR->fetch_assoc()) echo "  - {$c['Field']} ({$c['Type']})\n";

echo "\nCurrent columns in permissions:\n";
$vP = $db->query("DESCRIBE permissions");
while ($c = $vP->fetch_assoc()) echo "  - {$c['Field']} ({$c['Type']})\n";

echo "\nDone!\n";
