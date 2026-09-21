<?php

namespace App\Controllers\Admin;

use App\Models\Admin;
use App\Middleware\AuthMiddleware;

class DashboardController {
    /**
     * Display the Admin Dashboard.
     */
    public function index(): void {
        AuthMiddleware::check();

        $admin = auth_admin();
        $stats = Admin::getDashboardStats();

        // Pass variables to view
        $title = 'Admin Dashboard | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/dashboard.php';
    }
}
