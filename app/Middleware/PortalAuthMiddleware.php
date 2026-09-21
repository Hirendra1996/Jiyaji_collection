<?php

namespace App\Middleware;

class PortalAuthMiddleware {
    /**
     * Ensure the user is authenticated as a staff member for the portal.
     * Redirect to portal login page if unauthenticated.
     */
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (empty($_SESSION['staff_logged_in']) || empty($_SESSION['staff_user']['id'])) {
            set_flash('error', 'Please sign in to access the Staff & Operations Portal.');
            redirect('portal/login');
        }

        // The Staff Portal is reserved for operational staff, not super administrators
        if (!empty($_SESSION['staff_user']['role_name']) && $_SESSION['staff_user']['role_name'] === 'super_admin') {
            unset($_SESSION['staff_logged_in'], $_SESSION['staff_user'], $_SESSION['staff_permissions']);
            set_flash('error', 'The Staff Portal is reserved for operational staff roles. Super Administrators must use the Executive Admin Panel.');
            redirect('admin/dashboard');
        }
    }

    /**
     * Ensure the visitor is NOT already logged in as staff in the portal.
     * Redirect to portal dashboard if already authenticated.
     */
    public static function guest(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (!empty($_SESSION['staff_logged_in']) && !empty($_SESSION['staff_user']['id'])) {
            redirect('portal/dashboard');
        }
    }

    /**
     * Quick helper to verify if authenticated staff user possesses a specific permission.
     */
    public static function can(string $module, string $action = 'view'): bool {
        return staff_can($module, $action);
    }
}
