<?php
$staff = auth_staff();
$staffName = $staff['name'] ?? 'Staff Member';
$staffRole = $staff['role_name'] ?? 'staff';
$staffRoleDisplay = $staff['role_display_name'] ?? ucwords(str_replace('_', ' ', $staffRole));
$staffInitial = strtoupper(substr($staffName, 0, 1));
$permCount = count($_SESSION['staff_permissions'] ?? []);

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$isDashboard = strpos($currentUri, 'portal/dashboard') !== false || preg_match('#portal/?$#', $currentUri);
$isOrders = strpos($currentUri, 'portal/orders') !== false || strpos($currentUri, 'admin/orders') !== false;
$isReturns = strpos($currentUri, 'portal/returns') !== false || strpos($currentUri, 'admin/returns') !== false;
$isShipments = strpos($currentUri, 'portal/shipments') !== false || strpos($currentUri, 'admin/shipments') !== false;
$isProducts = strpos($currentUri, 'portal/products') !== false || strpos($currentUri, 'admin/products') !== false;
$isCategories = strpos($currentUri, 'portal/categories') !== false || strpos($currentUri, 'admin/categories') !== false;
$isStock = strpos($currentUri, 'portal/stock') !== false;
$isCustomers = strpos($currentUri, 'portal/customers') !== false || strpos($currentUri, 'admin/customers') !== false;
$isReviews = strpos($currentUri, 'portal/reviews') !== false || strpos($currentUri, 'admin/reviews') !== false;
$isTickets = strpos($currentUri, 'admin/tickets') !== false;
$isCoupons = strpos($currentUri, 'admin/coupons') !== false;
$isAnalytics = strpos($currentUri, 'admin/analytics') !== false || strpos($currentUri, 'admin/sales-analytics') !== false || strpos($currentUri, 'admin/search-analytics') !== false;
$isGateways = strpos($currentUri, 'admin/gateways') !== false || strpos($currentUri, 'admin/payment-gateways') !== false;
$isShipping = strpos($currentUri, 'admin/shipping') !== false || strpos($currentUri, 'admin/shipping-pincodes') !== false;
$isStaff = strpos($currentUri, 'admin/staff') !== false;
$isPages = strpos($currentUri, 'admin/pages') !== false;
$isSettings = strpos($currentUri, 'admin/settings') !== false;

// Determine role badge style
$badgeClass = 'role-pill-orders';
if ($staffRole === 'order_manager') {
    $badgeClass = 'role-pill-orders';
} elseif ($staffRole === 'inventory_manager') {
    $badgeClass = 'role-pill-inventory';
} elseif ($staffRole === 'support_staff') {
    $badgeClass = 'role-pill-support';
} elseif ($staffRole === 'marketing_manager') {
    $badgeClass = 'role-pill-marketing';
}
?>

<!-- Mobile Sidebar Backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Staff Operations Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand Header -->
    <div class="sidebar-brand-portal">
        <div class="brand-badge">
            <div class="logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="12 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
            </div>
            <div>
                <span style="font-weight: 800; letter-spacing: -0.02em; font-size: 1.05rem;">JIYAJI</span>
                <span class="lx-tag">LX</span>
            </div>
        </div>
        <span class="portal-subtag">Portal</span>
    </div>

    <!-- Scrollable Navigation Matrix -->
    <div class="sidebar-content">

        <!-- 1. CORE EXECUTIVE MANAGEMENT -->
        <?php if (staff_can('dashboard', 'view')): ?>
            <div class="nav-section-title">Core Operations</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="<?= url('portal/dashboard') ?>" class="nav-item <?= $isDashboard ? 'active' : '' ?>" id="navPortalDashboard">
                        <div class="nav-link-content">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span>Operations Dashboard</span>
                        </div>
                    </a>
                </li>
            </ul>
        <?php endif; ?>

        <!-- 2. ORDERS & LOGISTICS -->
        <?php if (staff_can('orders', 'view') || staff_can('returns', 'view') || staff_can('shipments', 'view')): ?>
            <div class="nav-section-title">Orders &amp; Logistics</div>
            <ul class="sidebar-nav">
                <?php if (staff_can('orders', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/orders') ?>" class="nav-item <?= $isOrders ? 'active' : '' ?>" id="navOrders">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                                <span>All Orders</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('returns', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/returns') ?>" class="nav-item <?= $isReturns ? 'active' : '' ?>" id="navReturns">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="1 4 1 10 7 10"></polyline>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                </svg>
                                <span>Returns &amp; Exchanges</span>
                            </div>
                            <span class="badge-live" style="background: rgba(217, 119, 6, 0.1); color: #D97706; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Queue</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('shipments', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/shipments') ?>" class="nav-item <?= $isShipments ? 'active' : '' ?>" id="navShipments">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <span>Shipments &amp; AWBs</span>
                            </div>
                            <span class="badge-live" style="background: rgba(13, 148, 136, 0.1); color: #0D9488; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Tracking</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <!-- 3. CATALOG & MERCHANDISING -->
        <?php if (staff_can('products', 'view') || staff_can('categories', 'view')): ?>
            <div class="nav-section-title">Catalog &amp; Inventory</div>
            <ul class="sidebar-nav">
                <?php if (staff_can('products', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/products') ?>" class="nav-item <?= $isProducts ? 'active' : '' ?>" id="navProducts">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                                <span>Products &amp; SKUs</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('categories', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/categories') ?>" class="nav-item <?= $isCategories ? 'active' : '' ?>" id="navCategories">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span>Categories Taxonomy</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('products', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/stock') ?>" class="nav-item <?= $isStock ? 'active' : '' ?>" id="navStockAlerts">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                    <line x1="12" y1="9" x2="12" y2="13"></line>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                <span>Stock &amp; Replenishment</span>
                            </div>
                            <?php
                            $stockAlertCount = 0;
                            try {
                                $db = App\Config\Database::connect();
                                $alertRes = $db->query("SELECT COUNT(*) FROM stock_alerts WHERE resolved = 0");
                                $stockAlertCount = $alertRes ? (int)$alertRes->fetch_row()[0] : 0;
                            } catch (Exception $e) { $stockAlertCount = 0; }
                            ?>
                            <?php if ($stockAlertCount > 0): ?>
                                <span class="badge-live" style="background: rgba(220, 38, 38, 0.1); color: #DC2626; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;"><?= $stockAlertCount ?> Alert<?= $stockAlertCount > 1 ? 's' : '' ?></span>
                            <?php else: ?>
                                <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: #10B981; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Healthy</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <!-- 4. CUSTOMER EXPERIENCE & CONCIERGE -->
        <?php if (staff_can('customers', 'view') || staff_can('reviews', 'view') || staff_can('tickets', 'view')): ?>
            <div class="nav-section-title">Customers &amp; Support</div>
            <ul class="sidebar-nav">
                <?php if (staff_can('customers', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/customers') ?>" class="nav-item <?= $isCustomers ? 'active' : '' ?>" id="navCustomers">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Customer Directory</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('reviews', 'view')): ?>
                    <li>
                        <a href="<?= url('portal/reviews') ?>" class="nav-item <?= $isReviews ? 'active' : '' ?>" id="navReviews">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <span>Customer Reviews</span>
                            </div>
                            <?php
                            $pendingReviewCount = 0;
                            try {
                                $db = App\Config\Database::connect();
                                $pRevRes = $db->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'");
                                $pendingReviewCount = $pRevRes ? (int)$pRevRes->fetch_row()[0] : 0;
                            } catch (Exception $e) { $pendingReviewCount = 0; }
                            ?>
                            <?php if ($pendingReviewCount > 0): ?>
                                <span class="badge-live" style="background: rgba(245, 158, 11, 0.15); color: #D97706; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;"><?= $pendingReviewCount ?> New</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('tickets', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/tickets') ?>" class="nav-item <?= $isTickets ? 'active' : '' ?>" id="navTickets">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                <span>Support Desk</span>
                            </div>
                            <span class="badge-live" style="background: rgba(236, 72, 153, 0.1); color: #EC4899; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Desk</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <!-- 5. MARKETING & GROWTH -->
        <?php if (staff_can('coupons', 'view') || staff_can('analytics', 'view')): ?>
            <div class="nav-section-title">Marketing &amp; Growth</div>
            <ul class="sidebar-nav">
                <?php if (staff_can('coupons', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/coupons') ?>" class="nav-item <?= $isCoupons ? 'active' : '' ?>" id="navCoupons">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                    <rect x="2" y="7" width="20" height="5"></rect>
                                    <line x1="12" y1="22" x2="12" y2="7"></line>
                                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                                </svg>
                                <span>Coupons &amp; Offers</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('analytics', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/search-analytics') ?>" class="nav-item <?= $isAnalytics ? 'active' : '' ?>" id="navSearchAnalytics">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span>Search &amp; Intent Analytics</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <!-- 6. FINANCIAL & SALES INTELLIGENCE -->
        <?php if (staff_can('analytics', 'view')): ?>
            <div class="nav-section-title">Reports &amp; Intelligence</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="<?= url('admin/sales-analytics') ?>" class="nav-item <?= $isAnalytics ? 'active' : '' ?>" id="navSalesAnalytics">
                        <div class="nav-link-content">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                            <span>Sales &amp; Revenue Analytics</span>
                        </div>
                    </a>
                </li>
            </ul>
        <?php endif; ?>

        <!-- 7. SYSTEM & GOVERNANCE -->
        <?php if (staff_can('gateways', 'view') || staff_can('shipping', 'view') || staff_can('staff', 'view') || staff_can('pages', 'view') || staff_can('settings', 'view')): ?>
            <div class="nav-section-title">System &amp; Settings</div>
            <ul class="sidebar-nav">
                <?php if (staff_can('gateways', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/payment-gateways') ?>" class="nav-item <?= $isGateways ? 'active' : '' ?>" id="navGateways">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                <span>Payment Gateways</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('shipping', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/shipping-pincodes') ?>" class="nav-item <?= $isShipping ? 'active' : '' ?>" id="navShipping">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                                <span>Shipping &amp; Pincodes</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('staff', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/staff') ?>" class="nav-item <?= $isStaff ? 'active' : '' ?>" id="navStaff">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                                <span>Staff &amp; RBAC Access</span>
                            </div>
                            <span class="badge-live" style="background: rgba(220, 38, 38, 0.1); color: #DC2626; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Secured</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('pages', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/pages') ?>" class="nav-item <?= $isPages ? 'active' : '' ?>" id="navPages">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span>Static CMS Pages</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (staff_can('settings', 'view')): ?>
                    <li>
                        <a href="<?= url('admin/settings') ?>" class="nav-item <?= $isSettings ? 'active' : '' ?>" id="navSettings">
                            <div class="nav-link-content">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                                <span>Store Master Settings</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

    </div> <!-- /.sidebar-content -->

    <!-- Sidebar Bottom Staff Session Footer -->
    <div class="sidebar-footer" style="padding: 1rem; border-top: 1px solid var(--border-color, #E2E8F0); background: #FAFBFD;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #4F46E5, #3B82F6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">
                    <?= $staffInitial ?>
                </div>
                <div>
                    <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($staffName) ?>
                    </div>
                    <div style="font-size: 0.7rem; color: #64748B;">
                        <?= $permCount ?> permissions
                    </div>
                </div>
            </div>
            <a href="<?= url('portal/logout') ?>" title="Sign Out of Portal" style="color: #94A3B8; transition: color 0.2s;" onmouseover="this.style.color='#DC2626'" onmouseout="this.style.color='#94A3B8'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </a>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span class="portal-badge <?= $badgeClass ?>" style="font-size: 0.68rem; padding: 2px 8px;">
                <?= htmlspecialchars($staffRoleDisplay) ?>
            </span>
            <span style="font-size: 0.68rem; color: #94A3B8;">RBAC Mode</span>
        </div>
    </div>
</aside>
