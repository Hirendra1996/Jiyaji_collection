<?php

namespace App\Config;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Database.php';

use Exception;

class SeedAdmin {
    public static function run(): void {
        try {
            $db = Database::connect();

            // 1. Check / Seed Roles
            $roleCheck = $db->query("SELECT id FROM roles WHERE name = 'super_admin' LIMIT 1");
            if ($roleCheck && $roleCheck->num_rows === 0) {
                $db->query("
                    INSERT INTO roles (name, description) VALUES
                    ('super_admin', 'Full access to all modules'),
                    ('order_manager', 'View and manage orders'),
                    ('inventory_manager', 'Manage products and stock'),
                    ('support_staff', 'Handle support tickets and returns')
                ");
                echo "Roles seeded successfully.\n";
            } else {
                echo "Roles already exist.\n";
            }

            // Get super_admin ID
            $superAdminRole = $db->query("SELECT id FROM roles WHERE name = 'super_admin' LIMIT 1")->fetch_assoc();
            $superAdminRoleId = $superAdminRole['id'] ?? 1;

            // 2. Check / Seed Default Administrator
            $adminEmail = 'admin@jiyaji.com';
            $adminCheck = $db->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
            $adminCheck->bind_param("s", $adminEmail);
            $adminCheck->execute();
            $adminRes = $adminCheck->get_result();

            if ($adminRes->num_rows === 0) {
                $name = 'Administrator';
                $passwordHash = password_hash('Admin@123', PASSWORD_BCRYPT);
                $isActive = 1;

                $stmt = $db->prepare("INSERT INTO admins (role_id, name, email, password_hash, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $superAdminRoleId, $name, $adminEmail, $passwordHash, $isActive);
                if ($stmt->execute()) {
                    echo "Admin user ($adminEmail / Admin@123) seeded successfully.\n";
                } else {
                    echo "Failed to seed admin user: " . $stmt->error . "\n";
                }
            } else {
                echo "Admin user ($adminEmail) already exists.\n";
            }

            echo "Database seed completed.\n";
        } catch (Exception $e) {
            echo "Seeder error: " . $e->getMessage() . "\n";
        }
    }
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    foreach ($_ENV as $k => $v) {
        putenv("$k=$v");
    }
    SeedAdmin::run();
}
