<?php

namespace App\Middleware;

class AuthMiddleware {
    /**
     * Ensure the user is authenticated as an admin.
     * Redirect to login page if unauthenticated.
     */
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin']['id'])) {
            set_flash('error', 'Please sign in to access the Admin Panel.');
            redirect('admin/login');
        }
    }

    /**
     * Ensure the visitor is NOT already logged in as admin.
     * Redirect to dashboard if already authenticated.
     */
    public static function guest(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin']['id'])) {
            redirect('admin/dashboard');
        }
    }
}
