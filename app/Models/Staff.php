<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Staff {

    // =========================================================================
    // 1. STAFF DIRECTORY RETRIEVAL
    // =========================================================================

    /**
     * Retrieve paginated and filtered staff directory.
     */
    public static function getStaff(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($filters['role']) && $filters['role'] !== 'all') {
                $whereClauses[] = "a.role_id = ?";
                $params[] = (int)$filters['role'];
                $types .= "i";
            }

            if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
                $whereClauses[] = "a.is_active = ?";
                $params[] = $filters['status'] === 'active' ? 1 : 0;
                $types .= "i";
            }

            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(a.name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)";
                $params[] = $wild;
                $params[] = $wild;
                $params[] = $wild;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Count query
            $countSql = "SELECT COUNT(*) FROM admins a WHERE $whereSql";
            $stmtC = $db->prepare($countSql);
            if ($types !== "") {
                $stmtC->bind_param($types, ...$params);
            }
            $stmtC->execute();
            $total = (int)$stmtC->get_result()->fetch_row()[0];
            $stmtC->close();

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Fetch records
            $selectSql = "
                SELECT 
                    a.id, a.role_id, a.name, a.email, a.phone, a.is_active,
                    a.last_login_at, a.created_at, a.updated_at,
                    r.name AS role_name,
                    r.display_name AS role_display_name,
                    r.is_system AS role_is_system,
                    creator.name AS creator_name
                FROM admins a
                LEFT JOIN roles r ON a.role_id = r.id
                LEFT JOIN admins creator ON a.created_by = creator.id
                WHERE $whereSql
                ORDER BY a.role_id ASC, a.id ASC
                LIMIT ? OFFSET ?
            ";

            $pagParams = array_merge($params, [$perPage, $offset]);
            $pagTypes = $types . "ii";

            $stmt = $db->prepare($selectSql);
            $stmt->bind_param($pagTypes, ...$pagParams);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return [
                'staff'      => $rows,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages,
                ]
            ];
        } catch (Exception $e) {
            error_log("Staff::getStaff error: " . $e->getMessage());
            return [
                'staff'      => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false]
            ];
        }
    }

    /**
     * Retrieve single staff member details by ID.
     */
    public static function getStaffMember(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT 
                    a.id, a.role_id, a.name, a.email, a.phone, a.is_active,
                    a.last_login_at, a.created_at, a.updated_at,
                    r.name AS role_name,
                    r.display_name AS role_display_name
                FROM admins a
                LEFT JOIN roles r ON a.role_id = r.id
                WHERE a.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $member = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return $member ?: null;
        } catch (Exception $e) {
            error_log("Staff::getStaffMember error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // 2. CREATE, UPDATE, DELETE & TOGGLE
    // =========================================================================

    /**
     * Create a new staff administrator account.
     */
    public static function createStaff(array $data, int $creatorId = 1): array {
        try {
            $db = Database::connect();
            $name = trim($data['name'] ?? '');
            $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $phone = trim($data['phone'] ?? '');
            $roleId = (int)($data['role_id'] ?? 0);
            $password = $data['password'] ?? '';
            $isActive = !empty($data['is_active']) ? 1 : 0;

            if ($name === '') {
                return ['success' => false, 'message' => 'Staff member name is required.'];
            }

            if (!$email) {
                return ['success' => false, 'message' => 'A valid email address is required.'];
            }

            if ($roleId <= 0) {
                return ['success' => false, 'message' => 'Please assign a role to this staff member.'];
            }

            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
            }

            // Check email uniqueness
            $checkEmail = $db->prepare("SELECT id FROM admins WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            if ($checkEmail->get_result()->num_rows > 0) {
                $checkEmail->close();
                return ['success' => false, 'message' => "An account with email '{$email}' already exists."];
            }
            $checkEmail->close();

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO admins (role_id, name, email, phone, password_hash, is_active, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("issssii", $roleId, $name, $email, $phone, $hash, $isActive, $creatorId);
            $success = $stmt->execute();
            $newId = $success ? $db->insert_id : 0;
            $stmt->close();

            return [
                'success' => $success,
                'id'      => $newId,
                'message' => $success ? 'Staff account created successfully.' : 'Failed to create staff account.'
            ];
        } catch (Exception $e) {
            error_log("Staff::createStaff error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update an existing staff member's profile and optionally reset password.
     */
    public static function updateStaff(int $id, array $data, int $currentAdminId = 1): array {
        try {
            $db = Database::connect();
            $existing = self::getStaffMember($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Staff member not found.'];
            }

            $name = trim($data['name'] ?? $existing['name']);
            $emailStr = trim($data['email'] ?? $existing['email']);
            $email = filter_var($emailStr, FILTER_VALIDATE_EMAIL);
            $phone = trim($data['phone'] ?? ($existing['phone'] ?? ''));
            $roleId = (int)($data['role_id'] ?? $existing['role_id']);
            $isActive = isset($data['is_active']) ? (!empty($data['is_active']) ? 1 : 0) : $existing['is_active'];
            $password = trim($data['password'] ?? '');

            if ($name === '' || !$email) {
                return ['success' => false, 'message' => 'Name and valid email address are required.'];
            }

            // Check email uniqueness
            $checkEmail = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $checkEmail->bind_param("si", $email, $id);
            $checkEmail->execute();
            if ($checkEmail->get_result()->num_rows > 0) {
                $checkEmail->close();
                return ['success' => false, 'message' => "Another staff account is already using email '{$email}'."];
            }
            $checkEmail->close();

            // Guard: Cannot demote the last active super_admin
            if ($existing['role_name'] === 'super_admin' && $roleId !== (int)$existing['role_id']) {
                $superAdminCount = self::getActiveSuperAdminCount();
                if ($superAdminCount <= 1) {
                    return ['success' => false, 'message' => 'Security Error: You cannot demote the only remaining Super Administrator.'];
                }
            }

            if ($password !== '') {
                if (strlen($password) < 6) {
                    return ['success' => false, 'message' => 'New password must be at least 6 characters long.'];
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE admins
                    SET name = ?, email = ?, phone = ?, role_id = ?, password_hash = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("sssisii", $name, $email, $phone, $roleId, $hash, $isActive, $id);
            } else {
                $stmt = $db->prepare("
                    UPDATE admins
                    SET name = ?, email = ?, phone = ?, role_id = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("sssiii", $name, $email, $phone, $roleId, $isActive, $id);
            }

            $success = $stmt->execute();
            $stmt->close();

            return [
                'success' => $success,
                'message' => $success ? 'Staff member profile updated successfully.' : 'Failed to update profile.'
            ];
        } catch (Exception $e) {
            error_log("Staff::updateStaff error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Toggle active/suspended status of a staff member.
     */
    public static function toggleStatus(int $id, bool $active, int $currentAdminId): array {
        try {
            // Guard: Cannot deactivate oneself
            if ($id === $currentAdminId) {
                return ['success' => false, 'message' => 'Security Notice: You cannot suspend your own active administrator account.'];
            }

            $member = self::getStaffMember($id);
            if (!$member) {
                return ['success' => false, 'message' => 'Staff member not found.'];
            }

            // Guard: Cannot deactivate the last active super_admin
            if (!$active && $member['role_name'] === 'super_admin') {
                if (self::getActiveSuperAdminCount() <= 1) {
                    return ['success' => false, 'message' => 'Security Error: Cannot suspend the only remaining Super Administrator.'];
                }
            }

            $db = Database::connect();
            $val = $active ? 1 : 0;
            $stmt = $db->prepare("UPDATE admins SET is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $val, $id);
            $success = $stmt->execute();
            $stmt->close();

            $actionStr = $active ? 'activated' : 'suspended';
            return [
                'success' => $success,
                'message' => $success ? "Staff account '{$member['name']}' has been {$actionStr}." : 'Status update failed.'
            ];
        } catch (Exception $e) {
            error_log("Staff::toggleStatus error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete a staff account with safety checks.
     */
    public static function deleteStaff(int $id, int $currentAdminId): array {
        try {
            // Guard: Cannot delete oneself
            if ($id === $currentAdminId) {
                return ['success' => false, 'message' => 'Security Notice: You cannot delete your own active administrator account.'];
            }

            $member = self::getStaffMember($id);
            if (!$member) {
                return ['success' => false, 'message' => 'Staff member not found.'];
            }

            // Guard: Cannot delete the last active super_admin
            if ($member['role_name'] === 'super_admin') {
                if (self::getActiveSuperAdminCount() <= 1) {
                    return ['success' => false, 'message' => 'Security Error: Cannot delete the only remaining Super Administrator.'];
                }
            }

            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();

            return [
                'success' => $success,
                'message' => $success ? "Staff account '{$member['name']}' was deleted successfully." : 'Failed to delete staff account.'
            ];
        } catch (Exception $e) {
            error_log("Staff::deleteStaff error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // 3. RBAC PERMISSION VERIFICATION ENGINE
    // =========================================================================

    /**
     * Check whether an admin user has permission for a specific module and action.
     */
    public static function hasPermission(int $adminId, string $module, string $action = 'view'): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT r.name AS role_name, r.id AS role_id
                FROM admins a
                JOIN roles r ON a.role_id = r.id
                WHERE a.id = ? AND a.is_active = 1
            ");
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row) {
                return false;
            }

            // Super Admin always has unrestricted access
            if ($row['role_name'] === 'super_admin') {
                return true;
            }

            // Query role_permissions join permissions
            $stmtP = $db->prepare("
                SELECT 1
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = ? AND p.module = ? AND p.action = ?
                LIMIT 1
            ");
            $stmtP->bind_param("iss", $row['role_id'], $module, $action);
            $stmtP->execute();
            $has = $stmtP->get_result()->num_rows > 0;
            $stmtP->close();

            return $has;
        } catch (Exception $e) {
            error_log("Staff::hasPermission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record login timestamp.
     */
    public static function recordLogin(int $adminId): void {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Staff::recordLogin error: " . $e->getMessage());
        }
    }

    // =========================================================================
    // 4. EXECUTIVE STAFF KPIS
    // =========================================================================

    /**
     * Calculate executive summary statistics for staff and roles.
     */
    public static function getStaffKPIs(): array {
        try {
            $db = Database::connect();

            // Staff metrics
            $staffRow = $db->query("
                SELECT 
                    COUNT(*) AS total_staff,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_staff,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive_staff
                FROM admins
            ")->fetch_assoc();

            // Roles metrics
            $roleRow = $db->query("
                SELECT 
                    COUNT(*) AS total_roles,
                    SUM(CASE WHEN is_system = 1 THEN 1 ELSE 0 END) AS system_roles,
                    SUM(CASE WHEN is_system = 0 THEN 1 ELSE 0 END) AS custom_roles
                FROM roles
            ")->fetch_assoc();

            $superAdmins = self::getActiveSuperAdminCount();

            return [
                'total_staff'         => (int)($staffRow['total_staff'] ?? 1),
                'active_staff'        => (int)($staffRow['active_staff'] ?? 1),
                'inactive_staff'      => (int)($staffRow['inactive_staff'] ?? 0),
                'total_roles'         => (int)($roleRow['total_roles'] ?? 5),
                'system_roles'        => (int)($roleRow['system_roles'] ?? 5),
                'custom_roles'        => (int)($roleRow['custom_roles'] ?? 0),
                'active_super_admins' => $superAdmins,
            ];
        } catch (Exception $e) {
            error_log("Staff::getStaffKPIs error: " . $e->getMessage());
            return [
                'total_staff'         => 1,
                'active_staff'        => 1,
                'inactive_staff'      => 0,
                'total_roles'         => 5,
                'system_roles'        => 5,
                'custom_roles'        => 0,
                'active_super_admins' => 1,
            ];
        }
    }

    /**
     * Helper to count active super administrators.
     */
    private static function getActiveSuperAdminCount(): int {
        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT COUNT(*) 
                FROM admins a
                JOIN roles r ON a.role_id = r.id
                WHERE r.name = 'super_admin' AND a.is_active = 1
            ");
            return $res ? (int)$res->fetch_row()[0] : 1;
        } catch (Exception $e) {
            return 1;
        }
    }
}
