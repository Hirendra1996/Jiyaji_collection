<?php

namespace App\Controllers\Portal;

use App\Config\Database;
use App\Middleware\PortalAuthMiddleware;
use PDO;
use Exception;

class PortalDashboardController {

    /**
     * Display the Role-Based Operations Portal Dashboard.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        $staff = auth_staff();
        $roleSlug = $staff['role_slug'] ?? 'staff';
        $roleName = $staff['role_name'] ?? 'Team Member';
        $permissions = $_SESSION['staff_permissions'] ?? [];

        // All system modules definitions
        $systemModules = [
            'orders' => [
                'title'       => 'Orders & Fulfillment',
                'category'    => 'Orders & Logistics',
                'icon'        => 'shopping-bag',
                'desc'        => 'Review incoming orders, generate invoices, and advance shipping fulfillment status.',
                'action_label'=> 'Manage Orders',
                'url'         => url('portal/orders'),
                'perm'        => 'orders',
                'badge'       => 'Core Ops',
                'color'       => '#2563EB',
                'bg_soft'     => '#EFF6FF',
            ],
            'shipments' => [
                'title'       => 'Shipments & AWBs',
                'category'    => 'Orders & Logistics',
                'icon'        => 'truck',
                'desc'        => 'Track transit milestones, generate courier air waybills, and monitor delivery SLAS.',
                'action_label'=> 'Open Shipments',
                'url'         => url('portal/shipments'),
                'perm'        => 'shipments',
                'badge'       => 'Logistics',
                'color'       => '#0D9488',
                'bg_soft'     => '#F0FDFA',
            ],
            'returns' => [
                'title'       => 'Returns & Exchanges',
                'category'    => 'Orders & Logistics',
                'icon'        => 'corner-down-left',
                'desc'        => 'Inspect customer return requests, verify warehouse receipt, and authorize refunds.',
                'action_label'=> 'Process Returns',
                'url'         => url('portal/returns'),
                'perm'        => 'returns',
                'badge'       => 'Logistics',
                'color'       => '#D97706',
                'bg_soft'     => '#FFFBEB',
            ],
            'products' => [
                'title'       => 'Catalog & Products',
                'category'    => 'Inventory & Merchandising',
                'icon'        => 'package',
                'desc'        => 'Catalog management, price matrices, inventory variants, SKU stock levels and media.',
                'action_label'=> 'Browse Catalog',
                'url'         => url('portal/products'),
                'perm'        => 'products',
                'badge'       => 'Catalog',
                'color'       => '#7C3AED',
                'bg_soft'     => '#F5F3FF',
            ],
            'categories' => [
                'title'       => 'Category Taxonomy',
                'category'    => 'Inventory & Merchandising',
                'icon'        => 'grid',
                'desc'        => 'Curate departmental navigation, seasonal collections, banners and hierarchical sub-categories.',
                'action_label'=> 'Curate Categories',
                'url'         => url('portal/categories'),
                'perm'        => 'categories',
                'badge'       => 'Catalog',
                'color'       => '#9333EA',
                'bg_soft'     => '#FAF5FF',
            ],
            'stock' => [
                'title'       => 'Stock & Replenishment',
                'category'    => 'Inventory & Merchandising',
                'icon'        => 'alert-triangle',
                'desc'        => 'Monitor live inventory health, critical low-stock alerts, restock variants, and audit every stock movement in real-time.',
                'action_label'=> 'View Inventory',
                'url'         => url('portal/stock'),
                'perm'        => 'products',
                'badge'       => 'Alerts',
                'color'       => '#DC2626',
                'bg_soft'     => '#FFF1F2',
            ],
            'customers' => [
                'title'       => 'Customer Directory',
                'category'    => 'Customer Experience',
                'icon'        => 'users',
                'desc'        => 'View shopper profiles, lifetime loyalty value, order history and saved delivery addresses.',
                'action_label'=> 'View Customers',
                'url'         => url('portal/customers'),
                'perm'        => 'customers',
                'badge'       => 'CRM',
                'color'       => '#0284C7',
                'bg_soft'     => '#F0F9FF',
            ],
            'reviews' => [
                'title'       => 'Product Reviews',
                'category'    => 'Customer Experience',
                'icon'        => 'star',
                'desc'        => 'Moderate verified customer reviews, buyer photos, feedback sentiments, and public replies.',
                'action_label'=> 'Moderate Reviews',
                'url'         => url('portal/reviews'),
                'perm'        => 'reviews',
                'badge'       => 'Engagement',
                'color'       => '#EAB308',
                'bg_soft'     => '#FEFCE8',
            ],
            'tickets' => [
                'title'       => 'Support Desk & Tickets',
                'category'    => 'Customer Experience',
                'icon'        => 'life-buoy',
                'desc'        => 'Respond to shopper inquiries, priority complaints, sizing questions and delivery escalations.',
                'action_label'=> 'Open Desk',
                'url'         => url('admin/tickets'),
                'perm'        => 'tickets',
                'badge'       => 'Support',
                'color'       => '#EC4899',
                'bg_soft'     => '#FDF2F8',
            ],
            'coupons' => [
                'title'       => 'Coupons & Discounts',
                'category'    => 'Marketing & Growth',
                'icon'        => 'tag',
                'desc'        => 'Configure promotional voucher codes, minimum cart rules, festive flash sales and BOGO offers.',
                'action_label'=> 'Manage Coupons',
                'url'         => url('admin/coupons'),
                'perm'        => 'coupons',
                'badge'       => 'Growth',
                'color'       => '#10B981',
                'bg_soft'     => '#ECFDF5',
            ],
            'analytics' => [
                'title'       => 'Analytics & Intelligence',
                'category'    => 'Reports & Insights',
                'icon'        => 'bar-chart-2',
                'desc'        => 'Comprehensive gross merchandise value (GMV), customer retention, AOV and sales trends.',
                'action_label'=> 'View Reports',
                'url'         => url('admin/analytics'),
                'perm'        => 'analytics',
                'badge'       => 'Finance',
                'color'       => '#059669',
                'bg_soft'     => '#F0FDF4',
            ],
            'gateways' => [
                'title'       => 'Payment Gateways',
                'category'    => 'System & Infrastructure',
                'icon'        => 'credit-card',
                'desc'        => 'Manage Razorpay, PhonePe, Cashfree, COD verification and live webhook endpoints.',
                'action_label'=> 'Configure Payments',
                'url'         => url('admin/gateways'),
                'perm'        => 'gateways',
                'badge'       => 'System',
                'color'       => '#6366F1',
                'bg_soft'     => '#EEF2FF',
            ],
            'shipping' => [
                'title'       => 'Shipping & Delivery Rules',
                'category'    => 'System & Infrastructure',
                'icon'        => 'map-pin',
                'desc'        => 'Manage service pincodes, delivery charges, express courier partners and transit zones.',
                'action_label'=> 'Configure Delivery',
                'url'         => url('admin/shipping'),
                'perm'        => 'shipping',
                'badge'       => 'System',
                'color'       => '#4F46E5',
                'bg_soft'     => '#EEF2FF',
            ],
            'staff' => [
                'title'       => 'Staff & Roles (RBAC)',
                'category'    => 'Security & Governance',
                'icon'        => 'shield',
                'desc'        => 'Control team accounts, assign granular role permissions and audit operations portal access.',
                'action_label'=> 'Manage Access',
                'url'         => url('admin/staff'),
                'perm'        => 'staff',
                'badge'       => 'Security',
                'color'       => '#DC2626',
                'bg_soft'     => '#FEF2F2',
            ],
            'pages' => [
                'title'       => 'CMS & Policy Pages',
                'category'    => 'Brand & Content',
                'icon'        => 'file-text',
                'desc'        => 'Maintain terms of service, privacy policy, return policies, about us, and brand FAQs.',
                'action_label'=> 'Edit Pages',
                'url'         => url('admin/pages'),
                'perm'        => 'pages',
                'badge'       => 'Content',
                'color'       => '#475569',
                'bg_soft'     => '#F8FAFC',
            ],
            'settings' => [
                'title'       => 'Store Master Settings',
                'category'    => 'System & Infrastructure',
                'icon'        => 'settings',
                'desc'        => 'Global branding, business contact metadata, currency, GST tax and SMS/Email notifications.',
                'action_label'=> 'System Settings',
                'url'         => url('admin/settings'),
                'perm'        => 'settings',
                'badge'       => 'Settings',
                'color'       => '#334155',
                'bg_soft'     => '#F1F5F9',
            ],
        ];

        // Filter modules by permissions
        $permittedModules = [];
        $restrictedCount = 0;

        foreach ($systemModules as $key => $mod) {
            if (staff_can($mod['perm'], 'view')) {
                $permittedModules[$key] = $mod;
            } else {
                $restrictedCount++;
            }
        }

        // Operational stats gathered dynamically based on permissions
        $stats = $this->gatherOperationalStats($permittedModules);

        include __DIR__ . '/../../Views/portal/dashboard/index.php';
    }

    /**
     * Gathers live operational stats for permitted modules safely.
     */
    private function gatherOperationalStats(array $permittedModules): array {
        $stats = [
            'total_permitted_systems' => count($permittedModules),
            'total_permissions'       => count($_SESSION['staff_permissions'] ?? []),
            'cards'                   => []
        ];

        try {
            $db = Database::connect();

            // Orders stat if permitted
            if (isset($permittedModules['orders'])) {
                $orderCount = 0;
                $pendingCount = 0;
                $res = $db->query("SELECT COUNT(*) FROM orders");
                if ($res) {
                    $orderCount = (int)($res->fetch_row()[0] ?? 0);
                }
                $resP = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' OR status = 'processing'");
                if ($resP) {
                    $pendingCount = (int)($resP->fetch_row()[0] ?? 0);
                }

                $stats['cards']['orders'] = [
                    'label'      => 'Total Orders',
                    'value'      => number_format($orderCount),
                    'subtitle'   => $pendingCount . ' awaiting fulfillment',
                    'badge'      => 'Orders',
                    'icon'       => 'shopping-bag',
                    'color'      => '#2563EB',
                ];
            }

            // Products stat if permitted
            if (isset($permittedModules['products'])) {
                $productCount = 0;
                $lowStockCount = 0;
                $res = $db->query("SELECT COUNT(*) FROM products");
                if ($res) {
                    $productCount = (int)($res->fetch_row()[0] ?? 0);
                }
                $resL = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5");
                if ($resL) {
                    $lowStockCount = (int)($resL->fetch_row()[0] ?? 0);
                }

                $stats['cards']['products'] = [
                    'label'      => 'Catalog Products',
                    'value'      => number_format($productCount),
                    'subtitle'   => $lowStockCount > 0 ? "{$lowStockCount} low stock alerts" : 'Stock levels healthy',
                    'badge'      => 'Catalog',
                    'icon'       => 'package',
                    'color'      => '#7C3AED',
                ];
            }

            // Customers stat if permitted
            if (isset($permittedModules['customers'])) {
                $customerCount = 0;
                $res = $db->query("SELECT COUNT(*) FROM users");
                if ($res) {
                    $customerCount = (int)($res->fetch_row()[0] ?? 0);
                }

                $stats['cards']['customers'] = [
                    'label'      => 'Registered Shoppers',
                    'value'      => number_format($customerCount),
                    'subtitle'   => 'Active shopper base',
                    'badge'      => 'CRM',
                    'icon'       => 'users',
                    'color'      => '#0284C7',
                ];
            }

            // Staff & Security stat if permitted
            if (isset($permittedModules['staff'])) {
                $staffCount = 0;
                $roleCount = 0;
                $resS = $db->query("SELECT COUNT(*) FROM admins");
                if ($resS) {
                    $staffCount = (int)($resS->fetch_row()[0] ?? 0);
                }
                $resR = $db->query("SELECT COUNT(*) FROM roles");
                if ($resR) {
                    $roleCount = (int)($resR->fetch_row()[0] ?? 0);
                }

                $stats['cards']['staff'] = [
                    'label'      => 'Authorized Staff',
                    'value'      => number_format($staffCount),
                    'subtitle'   => "{$roleCount} active security roles",
                    'badge'      => 'Security',
                    'icon'       => 'shield-check',
                    'color'      => '#059669',
                ];
            }

            // Support Tickets stat if permitted
            if (isset($permittedModules['tickets'])) {
                $ticketCount = 0;
                $openTickets = 0;
                $resT = $db->query("SELECT COUNT(*) FROM tickets");
                if ($resT) {
                    $ticketCount = (int)($resT->fetch_row()[0] ?? 0);
                }
                $resO = $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'open' OR status = 'in_progress'");
                if ($resO) {
                    $openTickets = (int)($resO->fetch_row()[0] ?? 0);
                }

                $stats['cards']['tickets'] = [
                    'label'      => 'Support Inquiries',
                    'value'      => number_format($ticketCount),
                    'subtitle'   => "{$openTickets} requiring attention",
                    'badge'      => 'Support',
                    'icon'       => 'life-buoy',
                    'color'      => '#EC4899',
                ];
            }

            // CMS Pages stat if permitted
            if (isset($permittedModules['pages'])) {
                $pagesCount = 0;
                $resP = $db->query("SELECT COUNT(*) FROM pages");
                if ($resP) {
                    $pagesCount = (int)($resP->fetch_row()[0] ?? 0);
                }

                $stats['cards']['pages'] = [
                    'label'      => 'CMS Policy Pages',
                    'value'      => number_format($pagesCount),
                    'subtitle'   => 'Published store pages',
                    'badge'      => 'Content',
                    'icon'       => 'file-text',
                    'color'      => '#D97706',
                ];
            }

        } catch (Exception $e) {
            // Silently fallback if any specific metric query fails
        }

        return $stats;
    }
}
