<?php

namespace App\Controllers\Portal;

use App\Config\Database;
use App\Middleware\PortalAuthMiddleware;
use App\Models\Staff;
use App\Models\Role;
use Exception;

class PortalAuthController {

    /**
     * Display the Staff Portal Login screen.
     */
    public function showLogin(): void {
        PortalAuthMiddleware::guest();
        include __DIR__ . '/../../Views/portal/auth/login.php';
    }

    /**
     * Process staff authentication.
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('portal/login');
        }

        if (!csrf_verify()) {
            set_flash('error', 'Session expired or security token invalid. Please try again.');
            redirect('portal/login');
        }

        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            set_flash('error', 'Please enter both your work email and password.');
            set_flash('old_email', $email ?? '');
            redirect('portal/login');
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT a.id, a.role_id, a.name, a.email, a.phone, a.password_hash, a.is_active,
                       r.name AS role_name, r.display_name AS role_display_name, r.description AS role_description
                FROM admins a
                LEFT JOIN roles r ON a.role_id = r.id
                WHERE a.email = ?
                LIMIT 1
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user) {
                set_flash('error', 'No staff account found with this email address.');
                set_flash('old_email', $email);
                redirect('portal/login');
            }

            if ((int)$user['is_active'] !== 1) {
                set_flash('error', 'Your staff account is currently deactivated. Please contact your system administrator.');
                redirect('portal/login');
            }

            if (!password_verify($password, $user['password_hash'])) {
                set_flash('error', 'Invalid password. Please verify your credentials.');
                set_flash('old_email', $email);
                redirect('portal/login');
            }

            // Staff Portal is exclusively for non-super-admin staff roles
            if ($user['role_name'] === 'super_admin') {
                set_flash('error', 'This portal is for operational staff members only. Super Administrators must use the Executive Admin Panel at /admin/login.');
                set_flash('old_email', $email);
                redirect('portal/login');
            }

            // Remove password hash from memory
            unset($user['password_hash']);

            // Resolve assigned permissions list ["module:action"] for staff role
            $permissionsList = [];
            $roleId = (int)$user['role_id'];
            $stmtP = $db->prepare("
                SELECT p.module, p.action
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = ?
            ");
            $stmtP->bind_param("i", $roleId);
            $stmtP->execute();
            $pRes = $stmtP->get_result();
            while ($pRow = $pRes->fetch_assoc()) {
                $permissionsList[] = $pRow['module'] . ':' . $pRow['action'];
            }
            $stmtP->close();

            // Record login timestamp
            if (class_exists('App\\Models\\Staff')) {
                Staff::recordLogin((int)$user['id']);
            }

            // Establish staff portal session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);

            $_SESSION['staff_logged_in']   = true;
            $_SESSION['staff_user']        = $user;
            $_SESSION['staff_permissions'] = $permissionsList;

            set_flash('success', 'Welcome, ' . htmlspecialchars($user['name']) . '! Signed in as ' . htmlspecialchars($user['role_display_name'] ?? 'Staff'));
            redirect('portal/dashboard');

        } catch (Exception $e) {
            error_log("PortalAuthController::login error: " . $e->getMessage());
            set_flash('error', 'A system error occurred during authentication. Please try again.');
            redirect('portal/login');
        }
    }

    /**
     * Terminate the staff portal session.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['staff_logged_in']);
        unset($_SESSION['staff_user']);
        unset($_SESSION['staff_permissions']);
        session_regenerate_id(true);

        set_flash('success', 'You have been securely signed out of the Staff Portal.');
        redirect('portal/login');
    }
}
