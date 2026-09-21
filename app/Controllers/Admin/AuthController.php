<?php

namespace App\Controllers\Admin;

use App\Models\Admin;
use App\Middleware\AuthMiddleware;

class AuthController {
    /**
     * Display the Admin Login page.
     */
    public function showLogin(): void {
        AuthMiddleware::guest();
        include __DIR__ . '/../../Views/admin/auth/login.php';
    }

    /**
     * Process Admin Login submission.
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/login');
        }

        // Validate CSRF token
        if (!csrf_verify()) {
            set_flash('error', 'Session expired or invalid security token. Please try again.');
            redirect('admin/login');
        }

        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            set_flash('error', 'Please enter both your email address and password.');
            set_flash('old_email', $email ?? '');
            redirect('admin/login');
        }

        $authResult = Admin::verify($email, $password);

        if (!$authResult['success']) {
            set_flash('error', $authResult['message']);
            set_flash('old_email', $email);
            redirect('admin/login');
        }

        // Authentication successful
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin'] = $authResult['admin'];

        set_flash('success', 'Welcome back, ' . htmlspecialchars($authResult['admin']['name']) . '!');
        redirect('admin/dashboard');
    }

    /**
     * Log out the current administrator.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin']);
        session_regenerate_id(true);

        set_flash('success', 'You have been logged out securely.');
        redirect('admin/login');
    }
}
