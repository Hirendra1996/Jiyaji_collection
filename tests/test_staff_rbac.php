<?php
/**
 * Staff Accounts & Role-Based Access Control (RBAC) — Automated Test Suite
 * Validates Role & Staff Models, Schema, Taxonomy & Permissions Matrix,
 * Safeguards (Self-protection, Last Super Admin, System Roles), Controller, and View Rendering.
 */

// Bootstrap
define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = ['id' => 1, 'name' => 'Root Super Admin', 'email' => 'admin@jiyaji.com', 'role' => 'super_admin'];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/Role.php';
require_once __DIR__ . '/../app/Models/Staff.php';
require_once __DIR__ . '/../app/Controllers/Admin/StaffController.php';

use App\Config\Database;
use App\Models\Role;
use App\Models\Staff;
use App\Controllers\Admin\StaffController;

class StaffRBACTestSuite {
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];
    private \mysqli $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    private function assert(string $desc, bool $condition, string $details = ''): void {
        if ($condition) {
            $this->passed++;
            echo "  \033[32m✔ PASS:\033[0m {$desc}\n";
        } else {
            $this->failed++;
            $msg = "  \033[31m✘ FAIL:\033[0m {$desc}" . ($details ? " — {$details}" : "");
            echo "{$msg}\n";
            $this->errors[] = $msg;
        }
    }

    public function runAll(): void {
        echo "\n\033[1;34m============================================================\033[0m\n";
        echo "\033[1;34m  JIYAJI LX — STAFF ACCOUNTS & RBAC TEST SUITE (FEATURE 29) \033[0m\n";
        echo "\033[1;34m============================================================\033[0m\n\n";

        $this->testSchemaIntegrity();
        $this->testRoleDefaultsAndTaxonomy();
        $this->testCustomRoleLifecycle();
        $this->testStaffLifecycleAndHashing();
        $this->testPermissionResolutionEngine();
        $this->testSecuritySafeguards();
        $this->testStaffKPICalculations();
        $this->testControllerAndViews();

        echo "\n\033[1;34m============================================================\033[0m\n";
        echo "  Test Summary: \033[32m{$this->passed} Passed\033[0m, ";
        if ($this->failed > 0) {
            echo "\033[31m{$this->failed} Failed\033[0m\n";
            echo "\033[1;31mFailures:\033[0m\n" . implode("\n", $this->errors) . "\n";
        } else {
            echo "\033[32m0 Failed (100% Success Rate)\033[0m\n";
        }
        echo "\033[1;34m============================================================\033[0m\n\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    // =========================================================================
    // 1. SCHEMA INTEGRITY
    // =========================================================================
    private function testSchemaIntegrity(): void {
        echo "\033[1m[1/8] Testing Database Schema Integrity...\033[0m\n";

        // Admins columns
        $adminCols = [];
        $resA = $this->db->query("DESCRIBE admins");
        while ($row = $resA->fetch_assoc()) {
            $adminCols[] = $row['Field'];
        }
        $this->assert("admins table has phone column", in_array('phone', $adminCols));
        $this->assert("admins table has last_login_at column", in_array('last_login_at', $adminCols));
        $this->assert("admins table has role_id column", in_array('role_id', $adminCols));

        // Roles columns
        $roleCols = [];
        $resR = $this->db->query("DESCRIBE roles");
        while ($row = $resR->fetch_assoc()) {
            $roleCols[] = $row['Field'];
        }
        $this->assert("roles table has display_name column", in_array('display_name', $roleCols));
        $this->assert("roles table has is_system column", in_array('is_system', $roleCols));

        // Permissions columns
        $permCols = [];
        $resP = $this->db->query("DESCRIBE permissions");
        while ($row = $resP->fetch_assoc()) {
            $permCols[] = $row['Field'];
        }
        $this->assert("permissions table has label column", in_array('label', $permCols));
        $this->assert("permissions table has description column", in_array('description', $permCols));

        // role_permissions table
        $resRP = $this->db->query("SHOW TABLES LIKE 'role_permissions'");
        $this->assert("role_permissions table exists", $resRP && $resRP->num_rows > 0);
    }

    // =========================================================================
    // 2. ROLE DEFAULTS & TAXONOMY
    // =========================================================================
    private function testRoleDefaultsAndTaxonomy(): void {
        echo "\n\033[1m[2/8] Testing Baseline Roles & Permission Taxonomy...\033[0m\n";

        Role::ensureDefaults();

        $roles = Role::getRoles();
        $roleSlugs = array_column($roles, 'name');

        $this->assert("super_admin role seeded", in_array('super_admin', $roleSlugs));
        $this->assert("order_manager role seeded", in_array('order_manager', $roleSlugs));
        $this->assert("inventory_manager role seeded", in_array('inventory_manager', $roleSlugs));
        $this->assert("support_staff role seeded", in_array('support_staff', $roleSlugs));
        $this->assert("marketing_manager role seeded", in_array('marketing_manager', $roleSlugs));

        $groupedPerms = Role::getAllPermissions();
        $this->assert("Permissions span at least 10 modules", count($groupedPerms) >= 10, "Found " . count($groupedPerms) . " modules");
        $this->assert("orders module permissions exist", isset($groupedPerms['orders']));
        $this->assert("products module permissions exist", isset($groupedPerms['products']));
        $this->assert("shipping module permissions exist", isset($groupedPerms['shipping']));
        $this->assert("gateways module permissions exist", isset($groupedPerms['gateways']));
        $this->assert("staff module permissions exist", isset($groupedPerms['staff']));

        // Check super_admin has permissions assigned
        $superAdminRole = array_values(array_filter($roles, fn($r) => $r['name'] === 'super_admin'))[0] ?? null;
        $this->assert("super_admin role has 30+ permissions assigned", ($superAdminRole['permissions_count'] ?? 0) >= 30, "Found: " . ($superAdminRole['permissions_count'] ?? 0));
    }

    // =========================================================================
    // 3. CUSTOM ROLE LIFECYCLE
    // =========================================================================
    private function testCustomRoleLifecycle(): void {
        echo "\n\033[1m[3/8] Testing Custom Role Creation, Modification & Permissions Sync...\033[0m\n";

        // Pick 3 permission IDs
        $res = $this->db->query("SELECT id FROM permissions LIMIT 3");
        $permIds = [];
        while ($r = $res->fetch_assoc()) $permIds[] = (int)$r['id'];

        $testRoleData = [
            'display_name' => 'Test Quality Assurance Lead',
            'description'  => 'Automated test custom role for verification',
        ];

        $newRoleId = Role::createRole($testRoleData, $permIds);
        $this->assert("Custom role created with new ID", $newRoleId > 0, "ID: {$newRoleId}");

        $fetchedRole = Role::getRole($newRoleId);
        $this->assert("Fetched custom role matches display name", $fetchedRole && $fetchedRole['display_name'] === 'Test Quality Assurance Lead');
        $this->assert("Custom role marked is_system = false", $fetchedRole && $fetchedRole['is_system'] === false);
        $this->assert("Custom role has assigned permissions synced", count($fetchedRole['permission_ids'] ?? []) === 3);

        // Update Role
        $updated = Role::updateRole($newRoleId, [
            'display_name' => 'Test QA Principal Engineer',
            'description'  => 'Updated description for QA Principal',
        ], [$permIds[0]]);

        $this->assert("Custom role update returns true", $updated);
        $updatedRole = Role::getRole($newRoleId);
        $this->assert("Custom role display name updated", $updatedRole['display_name'] === 'Test QA Principal Engineer');
        $this->assert("Custom role permission IDs updated to 1", count($updatedRole['permission_ids']) === 1 && $updatedRole['permission_ids'][0] === $permIds[0]);

        // Cleanup custom role
        $delResult = Role::deleteRole($newRoleId);
        $this->assert("Custom role deleted successfully", $delResult['success']);
    }

    // =========================================================================
    // 4. STAFF LIFECYCLE & HASHING
    // =========================================================================
    private function testStaffLifecycleAndHashing(): void {
        echo "\n\033[1m[4/8] Testing Staff Member Account CRUD & Bcrypt Hashing...\033[0m\n";

        $testEmail = 'qa.tester.' . time() . '@jiyaji.com';
        $testPassword = 'SecurePassword@2026';

        // 1. Create Staff
        $createRes = Staff::createStaff([
            'name'       => 'Priya Test Engineer',
            'email'      => $testEmail,
            'phone'      => '+91 9988776655',
            'role_id'    => 2, // order_manager
            'password'   => $testPassword,
            'is_active'  => 1,
            'created_by' => 1,
        ]);

        $this->assert("Staff account created successfully", $createRes['success'], $createRes['message'] ?? '');
        $staffId = $createRes['id'] ?? 0;
        $this->assert("New staff ID > 0", $staffId > 0);

        // Verify password hash in DB
        $stmt = $this->db->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->bind_param("i", $staffId);
        $stmt->execute();
        $dbHash = $stmt->get_result()->fetch_assoc()['password_hash'] ?? '';
        $stmt->close();

        $this->assert("Password stored as bcrypt hash", password_verify($testPassword, $dbHash));
        $this->assert("Plaintext password not in DB", $dbHash !== $testPassword);

        // 2. Reject Duplicate Email
        $dupRes = Staff::createStaff([
            'name'     => 'Duplicate Email Tester',
            'email'    => $testEmail,
            'role_id'  => 2,
            'password' => 'AnotherPass123',
        ]);
        $this->assert("Duplicate email creation rejected", !$dupRes['success']);

        // 3. Update Staff Details
        $updateRes = Staff::updateStaff($staffId, [
            'name'      => 'Priya Senior Test Lead',
            'email'     => $testEmail,
            'phone'     => '+91 9988770000',
            'role_id'   => 4, // support_staff
            'is_active' => 1,
        ], 1);

        $this->assert("Staff update succeeded", $updateRes['success']);
        $member = Staff::getStaffMember($staffId);
        $this->assert("Updated name verified", $member['name'] === 'Priya Senior Test Lead');
        $this->assert("Updated role verified", (int)$member['role_id'] === 4);

        // 4. Update Password with Validation
        $shortPassRes = Staff::updateStaff($staffId, ['password' => '123'], 1);
        $this->assert("Password shorter than 6 chars rejected", !$shortPassRes['success']);

        $goodPassRes = Staff::updateStaff($staffId, ['password' => 'NewStrongPass@456'], 1);
        $this->assert("Valid password update accepted", $goodPassRes['success']);

        // 5. Cleanup test staff
        $delRes = Staff::deleteStaff($staffId, 1);
        $this->assert("Test staff deleted cleanly", $delRes['success']);
    }

    // =========================================================================
    // 5. PERMISSION RESOLUTION ENGINE
    // =========================================================================
    private function testPermissionResolutionEngine(): void {
        echo "\n\033[1m[5/8] Testing Permission Resolution Engine (Staff::hasPermission)...\033[0m\n";

        // Admin 1 is super_admin
        $this->assert("Super Admin has permission for orders.manage", Staff::hasPermission(1, 'orders', 'manage'));
        $this->assert("Super Admin has permission for arbitrary custom.action (bypass check)", Staff::hasPermission(1, 'arbitrary_module', 'arbitrary_action'));

        // Create temporary staff with support_staff role (role_id = 4)
        $tempEmail = 'support.perm.test.' . time() . '@jiyaji.com';
        $createRes = Staff::createStaff([
            'name'      => 'Concierge Test User',
            'email'     => $tempEmail,
            'role_id'   => 4, // support_staff
            'password'  => 'ConciergePass@123',
            'is_active' => 1,
        ]);
        $tempStaffId = $createRes['id'];

        // support_staff should have tickets.view and tickets.reply
        $canViewTickets = Staff::hasPermission($tempStaffId, 'tickets', 'view');
        $canReplyTickets = Staff::hasPermission($tempStaffId, 'tickets', 'reply');
        $this->assert("Support staff has permission tickets.view", $canViewTickets);
        $this->assert("Support staff has permission tickets.reply", $canReplyTickets);

        // support_staff should NOT have staff.delete or gateways.manage
        $canDeleteStaff = Staff::hasPermission($tempStaffId, 'staff', 'delete');
        $canManageGateways = Staff::hasPermission($tempStaffId, 'gateways', 'manage');
        $this->assert("Support staff is denied staff.delete", !$canDeleteStaff);
        $this->assert("Support staff is denied gateways.manage", !$canManageGateways);

        // Suspend user (is_active = 0) -> all permissions should immediately evaluate false
        Staff::toggleStatus($tempStaffId, false, 1);
        $canViewAfterSuspend = Staff::hasPermission($tempStaffId, 'tickets', 'view');
        $this->assert("Suspended staff member is denied tickets.view regardless of role", !$canViewAfterSuspend);

        // Cleanup
        Staff::deleteStaff($tempStaffId, 1);
    }

    // =========================================================================
    // 6. SECURITY SAFEGUARDS
    // =========================================================================
    private function testSecuritySafeguards(): void {
        echo "\n\033[1m[6/8] Testing Security Safeguards & Boundary Protections...\033[0m\n";

        // Safeguard 1: Self-deletion prevention
        $selfDelRes = Staff::deleteStaff(1, 1);
        $this->assert("Self-deletion prevented", !$selfDelRes['success']);

        // Safeguard 2: Self-deactivation prevention
        $selfToggleRes = Staff::toggleStatus(1, false, 1);
        $this->assert("Self-deactivation prevented", !$selfToggleRes['success']);

        // Safeguard 3: Last active Super Admin protection
        // Attempt to demote Super Admin (ID 1) to support_staff (ID 4)
        $demoteRes = Staff::updateStaff(1, ['role_id' => 4], 999);
        $this->assert("Demoting last Super Admin prevented", !$demoteRes['success']);

        // Safeguard 4: System roles cannot be deleted
        $delSuperAdminRole = Role::deleteRole(1);
        $this->assert("Deletion of system role 'super_admin' prevented", !$delSuperAdminRole['success']);

        $delOrderManagerRole = Role::deleteRole(2);
        $this->assert("Deletion of system role 'order_manager' prevented", !$delOrderManagerRole['success']);

        // Safeguard 5: Cannot delete role with active staff assigned
        // Create custom role, assign a staff member to it, then try to delete role
        $roleId = Role::createRole(['display_name' => 'Assigned Staff Test Role']);
        $staffRes = Staff::createStaff([
            'name'     => 'Assigned Staff Member',
            'email'    => 'assigned.' . time() . '@jiyaji.com',
            'role_id'  => $roleId,
            'password' => 'Pass@12345',
        ]);

        $delAssignedRole = Role::deleteRole($roleId);
        $this->assert("Role with assigned staff cannot be deleted", !$delAssignedRole['success']);

        // Cleanup: delete staff, then role can be deleted
        Staff::deleteStaff($staffRes['id'], 1);
        $delRoleAfterFree = Role::deleteRole($roleId);
        $this->assert("Role successfully deleted after staff reassigned/removed", $delRoleAfterFree['success']);
    }

    // =========================================================================
    // 7. STAFF KPI CALCULATIONS
    // =========================================================================
    private function testStaffKPICalculations(): void {
        echo "\n\033[1m[7/8] Testing Staff & RBAC KPI Calculations...\033[0m\n";

        $kpis = Staff::getStaffKPIs();

        $this->assert("KPI: total_staff is numeric and >= 1", isset($kpis['total_staff']) && $kpis['total_staff'] >= 1);
        $this->assert("KPI: active_staff is numeric and >= 1", isset($kpis['active_staff']) && $kpis['active_staff'] >= 1);
        $this->assert("KPI: total_roles >= 5 (baseline roles)", isset($kpis['total_roles']) && $kpis['total_roles'] >= 5);
        $this->assert("KPI: system_roles == 5", isset($kpis['system_roles']) && $kpis['system_roles'] === 5);
        $this->assert("KPI: active_super_admins >= 1", isset($kpis['active_super_admins']) && $kpis['active_super_admins'] >= 1);
    }

    // =========================================================================
    // 8. CONTROLLER & VIEW RENDERING
    // =========================================================================
    private function testControllerAndViews(): void {
        echo "\n\033[1m[8/8] Testing Controller Instantiation & View Rendering...\033[0m\n";

        $controller = new StaffController();
        $this->assert("StaffController instantiated successfully", $controller instanceof StaffController);

        // Test view file exists
        $viewPath = __DIR__ . '/../app/Views/admin/staff/index.php';
        $this->assert("Admin staff view file exists", file_exists($viewPath));

        // Test rendering view buffers cleanly without syntax/fatal errors
        $_GET['tab'] = 'staff';
        ob_start();
        try {
            $controller->index();
            $output = ob_get_clean();
            $this->assert("StaffController::index() renders without fatal error", strlen($output) > 0);
            $this->assert("Rendered view contains 'Staff Accounts & Access Control'", strpos($output, 'Staff Accounts &amp; Access Control') !== false || strpos($output, 'Staff Accounts & Access Control') !== false);
            $this->assert("Rendered view contains Staff Directory tab", strpos($output, 'Staff Directory') !== false);
            $this->assert("Rendered view contains modal dialogs", strpos($output, 'staffModal') !== false && strpos($output, 'roleModal') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("StaffController::index() execution threw exception", false, $t->getMessage() . " at " . $t->getFile() . ":" . $t->getLine());
        }

        // Test Tab 2 rendering
        $_GET['tab'] = 'roles';
        ob_start();
        try {
            $controller->index();
            $output2 = ob_get_clean();
            $this->assert("Roles tab renders cleanly", strpos($output2, 'Roles &amp; Permission Matrix') !== false || strpos($output2, 'Roles & Permission Matrix') !== false);
            $this->assert("Roles tab lists Permission Taxonomy", strpos($output2, 'Permission Taxonomy') !== false || strpos($output2, 'Granular Permission') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Roles tab rendering threw exception", false, $t->getMessage());
        }

        // Test Tab 3 rendering
        $_GET['tab'] = 'policy';
        ob_start();
        try {
            $controller->index();
            $output3 = ob_get_clean();
            $this->assert("Policy tab renders cleanly", strpos($output3, 'Security &amp; Access Policy') !== false || strpos($output3, 'Security & Access Policy') !== false);
            $this->assert("Policy tab explains Root Account Protections", strpos($output3, 'Root Account Protections') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Policy tab rendering threw exception", false, $t->getMessage());
        }
    }
}

// Run test suite
$suite = new StaffRBACTestSuite();
$suite->runAll();
