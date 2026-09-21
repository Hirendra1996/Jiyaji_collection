<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$isShipments = strpos($currentUri, 'admin/shipments') !== false;
$isReturns = !$isShipments && strpos($currentUri, 'admin/returns') !== false;
$isOrders = !$isShipments && !$isReturns && strpos($currentUri, 'admin/orders') !== false;
$isCategories = strpos($currentUri, 'admin/categories') !== false;
$isCustomers = strpos($currentUri, 'admin/customers') !== false;
$isReviews = strpos($currentUri, 'admin/reviews') !== false;
$isTickets = strpos($currentUri, 'admin/tickets') !== false;
$isCoupons = strpos($currentUri, 'admin/coupons') !== false;
$isSearchAnalytics = strpos($currentUri, 'admin/search-analytics') !== false;
$isSalesAnalytics = strpos($currentUri, 'admin/sales-analytics') !== false;
$isPaymentGateways = strpos($currentUri, 'admin/payment-gateways') !== false;
$isShippingPincodes = strpos($currentUri, 'admin/shipping-pincodes') !== false;
$isStaff = strpos($currentUri, 'admin/staff') !== false;
$isPages = strpos($currentUri, 'admin/pages') !== false;
$isStoreSettings = strpos($currentUri, 'admin/settings') !== false;
$isStockAlerts = strpos($currentUri, 'admin/products') !== false && (($_GET['status'] ?? '') === 'low_stock');
$isProducts = strpos($currentUri, 'admin/products') !== false && !$isStockAlerts;
$isDashboard = !$isOrders && !$isReturns && !$isShipments && !$isProducts && !$isCategories && !$isCustomers && !$isReviews && !$isTickets && !$isCoupons && !$isSearchAnalytics && !$isSalesAnalytics && !$isPaymentGateways && !$isShippingPincodes && !$isStaff && !$isPages && !$isStoreSettings && !$isStockAlerts && (strpos($currentUri, 'admin/dashboard') !== false || preg_match('#admin/?$#', $currentUri));
?>
<!-- Sidebar Backdrop (for mobile) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-badge">
            <div class="logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="12 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
            </div>
            <span>JIYAJI</span>
            <span class="lx-tag">LX</span>
        </div>
    </div>

    <div class="sidebar-content">
        <!-- 1. CORE SECTION -->
        <div class="nav-section-title">Core Management</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/dashboard') ?>" class="nav-item <?= $isDashboard ? 'active' : '' ?>" id="navDashboard">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Executive Dashboard</span>
                    </div>
                </a>
            </li>
        </ul>

        <!-- 2. ORDER & FULFILLMENT (Features 11, 18, 19, 21, 22) -->
        <div class="nav-section-title">Orders & Logistics</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/orders') ?>" class="nav-item <?= $isOrders ? 'active' : '' ?>" id="navOrders">
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
            <li>
                <a href="<?= url('admin/returns') ?>" class="nav-item <?= $isReturns ? 'active' : '' ?>" id="navReturns">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        <span>Returns & Exchanges</span>
                    </div>
                    <span class="badge-live" style="background: rgba(255, 81, 0, 0.1); color: var(--brand-orange); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Queue</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/shipments') ?>" class="nav-item <?= $isShipments ? 'active' : '' ?>" id="navShipments">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        <span>Shipments & AWBs</span>
                    </div>
                    <span class="badge-live" style="background: rgba(45, 130, 255, 0.1); color: var(--brand-blue); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
        </ul>

        <!-- 3. CATALOG & INVENTORY (Features 10, 25) -->
        <div class="nav-section-title">Catalog & Inventory</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/products') ?>" class="nav-item <?= $isProducts ? 'active' : '' ?>" id="navProducts">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <span>Products & SKUs</span>
                    </div>
                    <span class="badge-live" style="background: rgba(140, 48, 245, 0.1); color: var(--brand-purple); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/categories') ?>" class="nav-item <?= $isCategories ? 'active' : '' ?>" id="navCategories">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <span>Categories</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: #059669; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/products?status=low_stock') ?>" class="nav-item <?= $isStockAlerts ? 'active' : '' ?>" id="navStockAlerts">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="21 8 21 21 3 21 3 8"></polyline>
                            <rect x="1" y="3" width="22" height="5"></rect>
                            <line x1="10" y1="12" x2="14" y2="12"></line>
                        </svg>
                        <span>Stock & Alerts</span>
                    </div>
                    <span class="badge-live" style="background: rgba(255, 81, 0, 0.1); color: var(--brand-orange); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">&lt;10</span>
                </a>
            </li>
        </ul>

        <!-- 4. CUSTOMERS & FEEDBACK (Features 8, 24, 28) -->
        <div class="nav-section-title">Customers & Support</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/customers') ?>" class="nav-item <?= $isCustomers ? 'active' : '' ?>" id="navCustomers">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Customers</span>
                    </div>
                    <span class="badge-live" style="background: rgba(236, 72, 153, 0.1); color: #db2777; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/reviews') ?>" class="nav-item <?= $isReviews ? 'active' : '' ?>" id="navReviews">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Reviews Moderation</span>
                    </div>
                    <span class="badge-live" style="background: rgba(245, 158, 11, 0.1); color: #d97706; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/tickets') ?>" class="nav-item <?= $isTickets ? 'active' : '' ?>" id="navTickets">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span>Support Tickets</span>
                    </div>
                    <span class="badge-live" style="background: rgba(45, 130, 255, 0.1); color: var(--brand-blue); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
        </ul>

        <!-- 5. MARKETING & PROMOTIONS (Features 23, 26, 30) -->
        <div class="nav-section-title">Marketing & Growth</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/coupons') ?>" class="nav-item <?= $isCoupons ? 'active' : '' ?>" id="navCoupons">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <span>Coupons &amp; Promos</span>
                    </div>
                    <span class="badge-live" style="background: rgba(140, 48, 245, 0.1); color: var(--brand-purple); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/search-analytics') ?>" class="nav-item <?= $isSearchAnalytics ? 'active' : '' ?>" id="navSearchAnalytics" title="Search Keyword Analytics & Zero Results Intelligence">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Search Analytics</span>
                    </div>
                    <span class="badge-live" style="background: rgba(45, 130, 255, 0.1); color: var(--brand-blue); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
        </ul>

        <!-- 6. REPORTS & EXPORTS (Feature 26) -->
        <div class="nav-section-title">Reports & Finance</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/sales-analytics') ?>" class="nav-item <?= $isSalesAnalytics ? 'active' : '' ?>" id="navSalesAnalytics" title="Daily/Weekly/Monthly Sales Trends & CSV Exports">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span>Sales Analytics</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: var(--status-success); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
        </ul>

        <!-- 7. SYSTEM SETTINGS (Features 14, 16, 17, 20, 29) -->
        <div class="nav-section-title">Settings & Configuration</div>
        <ul class="sidebar-nav">
            <li>
                <a href="<?= url('admin/payment-gateways') ?>" class="nav-item <?= $isPaymentGateways ? 'active' : '' ?>" id="navPaymentGateways" title="Razorpay Keys, Webhooks, COD Pincode Rules">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span>Payment Gateways</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: var(--status-success); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/shipping-pincodes') ?>" class="nav-item <?= $isShippingPincodes ? 'active' : '' ?>" id="navShippingPincodes" title="Shipping Zones, Weight Slabs, Courier Partners, Free Shipping Threshold">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                        <span>Shipping &amp; Pincodes</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: var(--status-success); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/staff') ?>" class="nav-item <?= $isStaff ? 'active' : '' ?>" id="navStaffRbac" title="Staff Directory, Roles Taxonomy & Granular Permissions Matrix">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>Staff &amp; RBAC</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: var(--status-success); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/pages') ?>" class="nav-item <?= $isPages ? 'active' : '' ?>" id="navStaticPages" title="Brand Story, Legal Compliance, Policy Documents & Contact Inquiries">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <span>Static CMS Pages</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: var(--status-success); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/settings') ?>" class="nav-item <?= $isStoreSettings ? 'active' : '' ?>" id="navStoreSettings" title="Brand Identity, WhatsApp Concierge, Meta Pixel, Contact & Operations">
                    <div class="nav-link-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Store Settings</span>
                    </div>
                    <span class="badge-live" style="background: rgba(16, 185, 129, 0.1); color: var(--status-success); font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 999px;">Live</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-version-card">
            <span class="version-dot"></span>
            <div>
                <div class="version-text">Jiyaji LX v2.0</div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Enterprise Suite</div>
            </div>
        </div>
    </div>
</aside>
