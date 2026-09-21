<?php
define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Models/Role.php';
require_once __DIR__ . '/../app/Models/Staff.php';

use App\Models\Role;
use App\Models\Staff;

echo "--- RBAC Sanity Test ---\n";

Role::ensureDefaults();
echo "[OK] Role::ensureDefaults() executed\n";

$kpis = Staff::getStaffKPIs();
echo "KPIs: " . json_encode($kpis, JSON_PRETTY_PRINT) . "\n";

$roles = Role::getRoles();
echo "Total Roles: " . count($roles) . "\n";
foreach ($roles as $r) {
    echo " - Role [{$r['id']}] {$r['name']} ({$r['display_name']}): is_system=" . ($r['is_system'] ? '1' : '0') . ", staff_count={$r['staff_count']}, perm_count={$r['permissions_count']}\n";
}

$staffData = Staff::getStaff();
$staff = $staffData['staff'];
echo "Total Staff: " . count($staff) . "\n";
foreach ($staff as $s) {
    echo " - Staff [{$s['id']}] {$s['name']} ({$s['email']}) - Role: {$s['role_name']} ({$s['role_display_name']}) - Status: " . ($s['is_active'] ? 'Active' : 'Inactive') . "\n";
}

$permsGrouped = Role::getAllPermissions();
echo "Modules with permissions: " . count($permsGrouped) . "\n";
foreach ($permsGrouped as $mod => $g) {
    echo " - Module '{$g['title']}' ({$g['module']}): " . count($g['permissions']) . " permissions\n";
}

// Check admin hasPermission
$admin = Staff::getStaffMember(1);
if ($admin) {
    $canManageOrders = Staff::hasPermission($admin['id'], 'orders', 'manage');
    $canDeleteStaff = Staff::hasPermission($admin['id'], 'staff', 'delete');
    echo "Admin ID 1 permission check: orders.manage = " . ($canManageOrders ? 'YES' : 'NO') . ", staff.delete = " . ($canDeleteStaff ? 'YES' : 'NO') . "\n";
}

echo "--- Sanity Completed Successfully ---\n";
