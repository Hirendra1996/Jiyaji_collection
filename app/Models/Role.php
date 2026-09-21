<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Role {

    // =========================================================================
    // 1. ROLES RETRIEVAL & MANAGEMENT
    // =========================================================================

    /**
     * Retrieve all roles with staff count and assigned permissions count.
     */
    public static function getRoles(): array {
        self::ensureDefaults();

        try {
            $db = Database::connect();
            $sql = "
                SELECT 
                    r.*,
                    COUNT(DISTINCT a.id) AS staff_count,
                    COUNT(DISTINCT rp.permission_id) AS permissions_count
                FROM roles r
                LEFT JOIN admins a ON a.role_id = r.id
                LEFT JOIN role_permissions rp ON rp.role_id = r.id
                GROUP BY r.id
                ORDER BY r.id ASC
            ";
            $res = $db->query($sql);
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

            $roles = [];
            foreach ($rows as $r) {
                $roles[] = [
                    'id'                => (int)$r['id'],
                    'name'              => $r['name'],
                    'display_name'      => $r['display_name'] ?? ucwords(str_replace('_', ' ', $r['name'])),
                    'description'       => $r['description'] ?? '',
                    'is_system'         => (bool)$r['is_system'],
                    'staff_count'       => (int)$r['staff_count'],
                    'permissions_count' => (int)$r['permissions_count'],
                    'created_at'        => $r['created_at'],
                    'updated_at'        => $r['updated_at'] ?? $r['created_at'],
                ];
            }

            return $roles;
        } catch (Exception $e) {
            error_log("Role::getRoles error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve a specific role with its assigned permission IDs.
     */
    public static function getRole(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM roles WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $role = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$role) {
                return null;
            }

            $role['id'] = (int)$role['id'];
            $role['display_name'] = $role['display_name'] ?? ucwords(str_replace('_', ' ', $role['name']));
            $role['is_system'] = (bool)$role['is_system'];
            $role['permission_ids'] = self::getRolePermissions($id);

            return $role;
        } catch (Exception $e) {
            error_log("Role::getRole error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new custom role and assign permissions.
     */
    public static function createRole(array $data, array $permissionIds = []): int {
        try {
            $db = Database::connect();
            $displayName = trim($data['display_name'] ?? '');
            $name = trim($data['name'] ?? '');
            $description = trim($data['description'] ?? '');

            if ($displayName === '') {
                return 0;
            }

            if ($name === '') {
                $name = strtolower(preg_replace('/[^a-z0-9_]/', '_', str_replace(' ', '_', $displayName)));
            }

            // Ensure unique name
            $stmtC = $db->prepare("SELECT id FROM roles WHERE name = ?");
            $stmtC->bind_param("s", $name);
            $stmtC->execute();
            if ($stmtC->get_result()->num_rows > 0) {
                $name .= '_' . time();
            }
            $stmtC->close();

            $stmt = $db->prepare("
                INSERT INTO roles (name, display_name, description, is_system, created_at, updated_at)
                VALUES (?, ?, ?, 0, NOW(), NOW())
            ");
            $stmt->bind_param("sss", $name, $displayName, $description);
            $success = $stmt->execute();
            $newId = $success ? $db->insert_id : 0;
            $stmt->close();

            if ($newId > 0 && !empty($permissionIds)) {
                self::syncRolePermissions($newId, $permissionIds);
            }

            return $newId;
        } catch (Exception $e) {
            error_log("Role::createRole error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update an existing role and sync its permissions.
     */
    public static function updateRole(int $id, array $data, array $permissionIds = []): bool {
        try {
            $db = Database::connect();
            $displayName = trim($data['display_name'] ?? '');
            $description = trim($data['description'] ?? '');

            if ($displayName === '') {
                return false;
            }

            $stmt = $db->prepare("
                UPDATE roles
                SET display_name = ?, description = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $displayName, $description, $id);
            $success = $stmt->execute();
            $stmt->close();

            // Sync permissions
            self::syncRolePermissions($id, $permissionIds);

            return $success;
        } catch (Exception $e) {
            error_log("Role::updateRole error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a custom role (safeguarded against deleting system roles or roles with assigned staff).
     */
    public static function deleteRole(int $id): array {
        try {
            $db = Database::connect();
            $role = self::getRole($id);
            if (!$role) {
                return ['success' => false, 'message' => 'Role not found.'];
            }

            if (!empty($role['is_system'])) {
                return ['success' => false, 'message' => 'Built-in system roles cannot be deleted.'];
            }

            // Check if any staff members are assigned
            $checkStaff = $db->query("SELECT COUNT(*) FROM admins WHERE role_id = " . (int)$id)->fetch_row()[0] ?? 0;
            if ((int)$checkStaff > 0) {
                return [
                    'success' => false,
                    'message' => "Cannot delete role because {$checkStaff} staff member(s) are currently assigned to it. Reassign them first."
                ];
            }

            // Delete associations
            $stmtP = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmtP->bind_param("i", $id);
            $stmtP->execute();
            $stmtP->close();

            // Delete role
            $stmt = $db->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();

            return ['success' => $success, 'message' => $success ? 'Role deleted successfully.' : 'Failed to delete role.'];
        } catch (Exception $e) {
            error_log("Role::deleteRole error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // 2. PERMISSIONS TAXONOMY & SYNC
    // =========================================================================

    /**
     * Retrieve all system permissions grouped by functional module.
     */
    public static function getAllPermissions(): array {
        self::ensureDefaults();

        try {
            $db = Database::connect();
            $res = $db->query("SELECT * FROM permissions ORDER BY module ASC, id ASC");
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

            $grouped = [];
            foreach ($rows as $r) {
                $mod = $r['module'];
                if (!isset($grouped[$mod])) {
                    $grouped[$mod] = [
                        'module'      => $mod,
                        'title'       => self::getModuleTitle($mod),
                        'permissions' => []
                    ];
                }
                $grouped[$mod]['permissions'][] = [
                    'id'          => (int)$r['id'],
                    'action'      => $r['action'],
                    'label'       => $r['label'] ?? ucfirst($r['action']),
                    'description' => $r['description'] ?? '',
                ];
            }

            return $grouped;
        } catch (Exception $e) {
            error_log("Role::getAllPermissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve list of assigned permission IDs for a specific role.
     */
    public static function getRolePermissions(int $roleId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param("i", $roleId);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_NUM);
            $stmt->close();

            return array_map(fn($r) => (int)$r[0], $rows);
        } catch (Exception $e) {
            error_log("Role::getRolePermissions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Synchronize permission assignments for a role.
     */
    public static function syncRolePermissions(int $roleId, array $permissionIds): bool {
        try {
            $db = Database::connect();
            // Clear existing
            $stmtDel = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmtDel->bind_param("i", $roleId);
            $stmtDel->execute();
            $stmtDel->close();

            if (empty($permissionIds)) {
                return true;
            }

            // Insert newly assigned
            $stmtIns = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissionIds as $pId) {
                $pidInt = (int)$pId;
                if ($pidInt > 0) {
                    $stmtIns->bind_param("ii", $roleId, $pidInt);
                    $stmtIns->execute();
                }
            }
            $stmtIns->close();

            return true;
        } catch (Exception $e) {
            error_log("Role::syncRolePermissions error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 3. ENSURE DEFAULTS & SEEDING
    // =========================================================================

    /**
     * Ensure standard permissions, roles, and role-permissions exist in database.
     */
    public static function ensureDefaults(): void {
        try {
            $db = Database::connect();

            // 1. Seed Permissions Catalog
            $permCatalog = [
                ['dashboard', 'view', 'View Dashboard Analytics', 'Access executive revenue KPIs, live orders feed, and sales trajectory charts.'],
                ['orders', 'view', 'View Orders', 'View order listings, items, customer details, and invoices.'],
                ['orders', 'edit', 'Manage & Fulfill Orders', 'Update order statuses, assign couriers, and generate shipping labels.'],
                ['orders', 'export', 'Export Orders Ledger', 'Download financial order transaction reports in CSV format.'],
                ['shipments', 'view', 'View Shipments & AWBs', 'Inspect carrier tracking milestones, logistics partners, and delivery states.'],
                ['shipments', 'create', 'Create Shipments', 'Generate AWBs, assign carriers, and mark packages as dispatched.'],
                ['returns', 'view', 'View Returns & Exchanges', 'Review return requests, defect reasons, and customer notes.'],
                ['returns', 'process', 'Process Returns & Refunds', 'Approve, inspect, reject returns, and trigger refund workflows.'],
                ['products', 'view', 'View Catalog & Inventory', 'Browse garments, variations, stock levels, and category taxonomy.'],
                ['products', 'create', 'Create Garments & SKUs', 'Add bespoke attire, fabric options, sizes, and pricing.'],
                ['products', 'edit', 'Edit Products & Stock', 'Modify prices, variant stock counts, descriptions, and media.'],
                ['products', 'delete', 'Archive / Delete Products', 'Remove items from active customer view.'],
                ['categories', 'view', 'View Categories', 'Access collection taxonomies.'],
                ['categories', 'manage', 'Manage Categories', 'Create, update, reorder, and activate collection categories.'],
                ['customers', 'view', 'View Customer Directory', 'Inspect customer profiles, order history, and contact details.'],
                ['customers', 'edit', 'Manage Customer Accounts', 'Update customer details and toggle VIP account status.'],
                ['reviews', 'view', 'View Reviews', 'Inspect customer ratings and feedback.'],
                ['reviews', 'moderate', 'Moderate Reviews', 'Approve, reject, and feature verified reviews on storefront.'],
                ['tickets', 'view', 'View Support Tickets', 'Access customer care conversations and support queries.'],
                ['tickets', 'reply', 'Reply & Assist Clients', 'Send official concierge responses to customer inquiries.'],
                ['tickets', 'resolve', 'Resolve Tickets', 'Close, escalate, or reopen customer service cases.'],
                ['coupons', 'view', 'View Promotions & Coupons', 'Review active promo codes and usage limits.'],
                ['coupons', 'manage', 'Create & Manage Promos', 'Create percentage/flat discounts, basket rules, and expiry dates.'],
                ['analytics', 'view', 'View Analytics & Reports', 'Access search keyword intelligence and sales trajectory ledgers.'],
                ['analytics', 'export', 'Export Financial Data', 'Stream CSV financial records.'],
                ['gateways', 'view', 'View Payment Gateways', 'Inspect active payment methods, Razorpay credentials, and COD rules.'],
                ['gateways', 'manage', 'Configure Payment Settings', 'Modify Razorpay API keys, COD limits, and bank wire coordinates.'],
                ['shipping', 'view', 'View Delivery Logistics', 'Inspect shipping zones, rate slabs, and carrier profiles.'],
                ['shipping', 'manage', 'Manage Zones & Rates', 'Create delivery territories, dead weight slabs, and carrier integrations.'],
                ['staff', 'view', 'View Staff Directory', 'Inspect admin accounts, roles, and access privileges.'],
                ['staff', 'manage', 'Manage Staff & RBAC', 'Invite team members, assign roles, and configure permission matrix.'],
                ['pages', 'view', 'View Static CMS Pages', 'Inspect published brand story and legal compliance pages.'],
                ['pages', 'manage', 'Manage CMS Content', 'Create, edit, and publish static pages, FAQ items, and SEO metadata.'],
                ['settings', 'view', 'View Store Settings', 'Inspect brand profile, contact channels, localization, and marketing integrations.'],
                ['settings', 'manage', 'Configure Store Settings', 'Update store identity, WhatsApp float, notification channels, SEO defaults, and tracking pixels.'],
            ];

            $stmtP = $db->prepare("
                INSERT INTO permissions (module, action, label, description)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE label = VALUES(label), description = VALUES(description)
            ");
            foreach ($permCatalog as $p) {
                $stmtP->bind_param("ssss", $p[0], $p[1], $p[2], $p[3]);
                $stmtP->execute();
            }
            $stmtP->close();

            // 2. Fetch all permission IDs mapped by "module:action"
            $allPermsRes = $db->query("SELECT id, module, action FROM permissions");
            $permMap = [];
            while ($pRow = $allPermsRes->fetch_assoc()) {
                $permMap[$pRow['module'] . ':' . $pRow['action']] = (int)$pRow['id'];
            }

            // 3. Ensure Roles exist with Display Names
            $roleCatalog = [
                ['super_admin', 'Super Administrator', 'Full administrative authority with unrestricted access to all modules and settings.'],
                ['order_manager', 'Orders & Logistics Manager', 'Dedicated to processing orders, tracking shipments, and managing returns.'],
                ['inventory_manager', 'Catalog & Inventory Specialist', 'Manages products, variants, SKUs, categories, and stock replenishments.'],
                ['support_staff', 'Customer Concierge & Support', 'Handles customer care tickets, product inquiries, and review moderation.'],
                ['marketing_manager', 'Marketing & Growth Lead', 'Manages promotional discount coupons, search analytics, and customer campaigns.'],
            ];

            $stmtR = $db->prepare("
                INSERT INTO roles (name, display_name, description, is_system, created_at, updated_at)
                VALUES (?, ?, ?, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), description = VALUES(description), is_system = 1
            ");
            foreach ($roleCatalog as $rc) {
                $stmtR->bind_param("sss", $rc[0], $rc[1], $rc[2]);
                $stmtR->execute();
            }
            $stmtR->close();

            // 4. Map Roles to Default Permissions
            $rolesRes = $db->query("SELECT id, name FROM roles");
            $roleIdMap = [];
            while ($rr = $rolesRes->fetch_assoc()) {
                $roleIdMap[$rr['name']] = (int)$rr['id'];
            }

            // Super Admin: ALL permissions
            if (isset($roleIdMap['super_admin'])) {
                $saId = $roleIdMap['super_admin'];
                $checkSa = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = $saId")->fetch_row()[0] ?? 0;
                if ((int)$checkSa < count($permMap)) {
                    self::syncRolePermissions($saId, array_values($permMap));
                }
            }

            // Order Manager
            if (isset($roleIdMap['order_manager'])) {
                $omId = $roleIdMap['order_manager'];
                $checkOm = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = $omId")->fetch_row()[0] ?? 0;
                if ((int)$checkOm === 0) {
                    $omPerms = [
                        'dashboard:view', 'orders:view', 'orders:edit', 'orders:export',
                        'shipments:view', 'shipments:create', 'returns:view', 'returns:process',
                        'customers:view', 'tickets:view', 'tickets:reply'
                    ];
                    $pids = array_filter(array_map(fn($k) => $permMap[$k] ?? null, $omPerms));
                    self::syncRolePermissions($omId, $pids);
                }
            }

            // Inventory Manager
            if (isset($roleIdMap['inventory_manager'])) {
                $imId = $roleIdMap['inventory_manager'];
                $checkIm = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = $imId")->fetch_row()[0] ?? 0;
                if ((int)$checkIm === 0) {
                    $imPerms = [
                        'dashboard:view', 'products:view', 'products:create', 'products:edit', 'products:delete',
                        'categories:view', 'categories:manage', 'analytics:view'
                    ];
                    $pids = array_filter(array_map(fn($k) => $permMap[$k] ?? null, $imPerms));
                    self::syncRolePermissions($imId, $pids);
                }
            }

            // Support Staff
            if (isset($roleIdMap['support_staff'])) {
                $ssId = $roleIdMap['support_staff'];
                $checkSs = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = $ssId")->fetch_row()[0] ?? 0;
                if ((int)$checkSs === 0) {
                    $ssPerms = [
                        'dashboard:view', 'tickets:view', 'tickets:reply', 'tickets:resolve',
                        'reviews:view', 'reviews:moderate', 'returns:view', 'customers:view'
                    ];
                    $pids = array_filter(array_map(fn($k) => $permMap[$k] ?? null, $ssPerms));
                    self::syncRolePermissions($ssId, $pids);
                }
            }

            // Marketing Manager
            if (isset($roleIdMap['marketing_manager'])) {
                $mmId = $roleIdMap['marketing_manager'];
                $checkMm = $db->query("SELECT COUNT(*) FROM role_permissions WHERE role_id = $mmId")->fetch_row()[0] ?? 0;
                if ((int)$checkMm === 0) {
                    $mmPerms = [
                        'dashboard:view', 'coupons:view', 'coupons:manage',
                        'analytics:view', 'analytics:export', 'customers:view'
                    ];
                    $pids = array_filter(array_map(fn($k) => $permMap[$k] ?? null, $mmPerms));
                    self::syncRolePermissions($mmId, $pids);
                }
            }
        } catch (Exception $e) {
            error_log("Role::ensureDefaults error: " . $e->getMessage());
        }
    }

    /**
     * Helper for friendly module title.
     */
    private static function getModuleTitle(string $module): string {
        $titles = [
            'dashboard'  => 'Executive Dashboard',
            'orders'     => 'Orders & Fulfillment',
            'shipments'  => 'Shipments & Logistics',
            'returns'    => 'Returns & Exchanges',
            'products'   => 'Products & SKUs',
            'categories' => 'Collection Taxonomy',
            'customers'  => 'Customer Directory',
            'reviews'    => 'Reviews Moderation',
            'tickets'    => 'Customer Care & Tickets',
            'coupons'    => 'Promotions & Coupons',
            'analytics'  => 'Analytics & Reports',
            'gateways'   => 'Payment Gateways',
            'shipping'   => 'Shipping & Pincodes',
            'staff'      => 'Staff & Access Control',
            'pages'      => 'Static CMS Pages',
        ];
        return $titles[$module] ?? ucfirst($module);
    }
}
