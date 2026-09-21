<?php

namespace App\Controllers\Admin;

use App\Models\Staff;
use App\Models\Role;
use App\Middleware\AuthMiddleware;
use Exception;

class StaffController {

    // =========================================================================
    // 1. INDEX — Staff Accounts & Access Control Command Center
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $activeTab = $_GET['tab'] ?? 'staff';
        $validTabs = ['staff', 'roles', 'policy'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'staff';
        }

        // Filters for staff directory
        $search = trim($_GET['search'] ?? '');
        $roleFilter = $_GET['role'] ?? 'all';
        $statusFilter = $_GET['status'] ?? 'all';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $filters = [
            'search' => $search,
            'role'   => $roleFilter,
            'status' => $statusFilter,
        ];

        // Seed default roles/permissions if not present
        Role::ensureDefaults();

        $staffData      = Staff::getStaff($filters, $page, $perPage);
        $roles          = Role::getRoles();
        $kpis           = Staff::getStaffKPIs();
        $allPermissions = Role::getAllPermissions();
        $currentAdminId = (int)($_SESSION['admin']['id'] ?? 1);

        $title = 'Staff Accounts & Access Control (RBAC) | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/staff/index.php';
    }

    // =========================================================================
    // 2. STAFF ACTIONS (SAVE, TOGGLE, DELETE)
    // =========================================================================

    public function saveStaff(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/staff?tab=staff');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid. Please try again.');
            redirect('admin/staff?tab=staff');
        }

        $currentAdminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($currentAdminId, 'staff', 'manage')) {
            set_toast('error', 'Access Denied', 'You do not have permission to manage staff accounts.');
            redirect('admin/staff?tab=staff');
        }

        $id       = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $name     = trim($_POST['name'] ?? '');
        $email    = trim(strtolower($_POST['email'] ?? ''));
        $phone    = trim($_POST['phone'] ?? '');
        $roleId   = (int)($_POST['role_id'] ?? 0);
        $password = trim($_POST['password'] ?? '');
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if ($name === '' || $email === '' || $roleId <= 0) {
            set_toast('error', 'Validation Error', 'Name, email, and assigned role are strictly required.');
            redirect('admin/staff?tab=staff');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_toast('error', 'Validation Error', 'Please enter a valid email address.');
            redirect('admin/staff?tab=staff');
        }

        if ($id === 0) {
            // New staff member requires password
            if (strlen($password) < 6) {
                set_toast('error', 'Validation Error', 'Password must be at least 6 characters long.');
                redirect('admin/staff?tab=staff');
            }

            $result = Staff::createStaff([
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'role_id'    => $roleId,
                'password'   => $password,
                'is_active'  => $isActive,
                'created_by' => $currentAdminId,
            ]);

            if ($result['success']) {
                set_toast('success', 'Staff Member Added', "Account for {$name} created successfully.");
            } else {
                set_toast('error', 'Creation Failed', $result['message']);
            }
        } else {
            // Existing staff member update
            $updateData = [
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'role_id'   => $roleId,
                'is_active' => $isActive,
            ];

            if ($password !== '') {
                if (strlen($password) < 6) {
                    set_toast('error', 'Validation Error', 'Password must be at least 6 characters long.');
                    redirect('admin/staff?tab=staff');
                }
                $updateData['password'] = $password;
            }

            $result = Staff::updateStaff($id, $updateData, $currentAdminId);
            if ($result['success']) {
                set_toast('success', 'Staff Member Updated', "Account for {$name} updated successfully.");
            } else {
                set_toast('error', 'Update Failed', $result['message']);
            }
        }

        redirect('admin/staff?tab=staff');
    }

    public function toggleStaff(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/staff?tab=staff');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/staff?tab=staff');
        }

        $currentAdminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($currentAdminId, 'staff', 'manage')) {
            set_toast('error', 'Access Denied', 'You do not have permission to change staff account statuses.');
            redirect('admin/staff?tab=staff');
        }

        $id     = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $active = !empty($_POST['is_active']) && $_POST['is_active'] === '1';

        $result = Staff::toggleStatus($id, $active, $currentAdminId);
        if ($result['success']) {
            $statusStr = $active ? 'activated' : 'deactivated';
            set_toast('success', 'Status Updated', "Staff account has been {$statusStr}.");
        } else {
            set_toast('error', 'Status Update Failed', $result['message']);
        }

        redirect('admin/staff?tab=staff');
    }

    public function deleteStaff(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/staff?tab=staff');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/staff?tab=staff');
        }

        $currentAdminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($currentAdminId, 'staff', 'delete')) {
            set_toast('error', 'Access Denied', 'You do not have permission to delete staff accounts.');
            redirect('admin/staff?tab=staff');
        }

        $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = Staff::deleteStaff($id, $currentAdminId);

        if ($result['success']) {
            set_toast('success', 'Staff Member Deleted', $result['message']);
        } else {
            set_toast('error', 'Deletion Failed', $result['message']);
        }

        redirect('admin/staff?tab=staff');
    }

    // =========================================================================
    // 3. ROLE ACTIONS (SAVE, DELETE)
    // =========================================================================

    public function saveRole(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/staff?tab=roles');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/staff?tab=roles');
        }

        $currentAdminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($currentAdminId, 'staff', 'manage')) {
            set_toast('error', 'Access Denied', 'You do not have permission to manage roles.');
            redirect('admin/staff?tab=roles');
        }

        $id          = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $displayName = trim($_POST['display_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = isset($_POST['permissions']) && is_array($_POST['permissions'])
            ? array_map('intval', $_POST['permissions'])
            : [];

        if ($displayName === '') {
            set_toast('error', 'Validation Error', 'Role display name is required.');
            redirect('admin/staff?tab=roles');
        }

        if ($id > 0) {
            $updated = Role::updateRole($id, [
                'display_name' => $displayName,
                'description'  => $description,
            ], $permissions);

            if ($updated) {
                set_toast('success', 'Role Updated', "Role '{$displayName}' and its permissions have been updated.");
            } else {
                set_toast('error', 'Update Failed', 'Could not update the role.');
            }
        } else {
            $newId = Role::createRole([
                'display_name' => $displayName,
                'description'  => $description,
            ], $permissions);

            if ($newId > 0) {
                set_toast('success', 'Role Created', "Custom role '{$displayName}' created with " . count($permissions) . " assigned permissions.");
            } else {
                set_toast('error', 'Creation Failed', 'Could not create the new role.');
            }
        }

        redirect('admin/staff?tab=roles');
    }

    public function deleteRole(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/staff?tab=roles');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/staff?tab=roles');
        }

        $currentAdminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($currentAdminId, 'staff', 'delete')) {
            set_toast('error', 'Access Denied', 'You do not have permission to delete roles.');
            redirect('admin/staff?tab=roles');
        }

        $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = Role::deleteRole($id);

        if ($result['success']) {
            set_toast('success', 'Role Deleted', $result['message']);
        } else {
            set_toast('error', 'Deletion Prevented', $result['message']);
        }

        redirect('admin/staff?tab=roles');
    }

    // =========================================================================
    // 4. PERMISSION CHECK / AJAX QUERY
    // =========================================================================

    public function checkPermission(): void {
        AuthMiddleware::check();

        header('Content-Type: application/json');
        $module = trim($_GET['module'] ?? '');
        $action = trim($_GET['action'] ?? '');
        $adminId = (int)($_GET['admin_id'] ?? ($_SESSION['admin']['id'] ?? 1));

        if ($module === '' || $action === '') {
            echo json_encode(['allowed' => false, 'error' => 'Missing module or action']);
            exit;
        }

        $allowed = Staff::hasPermission($adminId, $module, $action);
        echo json_encode(['allowed' => $allowed, 'admin_id' => $adminId, 'module' => $module, 'action' => $action]);
        exit;
    }
}
