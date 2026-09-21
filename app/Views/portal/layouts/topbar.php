<?php
$staff = auth_staff();
$staffName = $staff['name'] ?? 'Staff Member';
$staffEmail = $staff['email'] ?? 'staff@jiyaji.com';
$staffRole = $staff['role_name'] ?? 'staff';
$staffRoleDisplay = $staff['role_display_name'] ?? ucwords(str_replace('_', ' ', $staffRole));
$staffInitial = strtoupper(substr($staffName, 0, 1));
$permCount = count($_SESSION['staff_permissions'] ?? []);

// Determine role badge styling
$badgeClass = 'role-pill-orders';
$roleIcon = 'shopping-bag';

if ($staffRole === 'inventory_manager') {
    $badgeClass = 'role-pill-inventory';
    $roleIcon = 'package';
} elseif ($staffRole === 'support_staff') {
    $badgeClass = 'role-pill-support';
    $roleIcon = 'life-buoy';
} elseif ($staffRole === 'marketing_manager') {
    $badgeClass = 'role-pill-marketing';
    $roleIcon = 'tag';
} elseif ($staffRole === 'order_manager') {
    $badgeClass = 'role-pill-orders';
    $roleIcon = 'shopping-bag';
}
?>

<header class="portal-topbar">
    <!-- Left Section: Mobile Menu, Role Badge & Context -->
    <div class="portal-topbar-left">
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Navigation Sidebar" style="margin-right: 4px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Role Badge with Icon -->
        <span class="portal-badge <?= $badgeClass ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <?php if ($roleIcon === 'shopping-bag'): ?>
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                <?php elseif ($roleIcon === 'package'): ?>
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <?php elseif ($roleIcon === 'life-buoy'): ?>
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                <?php else: ?>
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <?php endif; ?>
            </svg>
            <span><?= htmlspecialchars($staffRoleDisplay) ?></span>
        </span>

        <span style="color: #CBD5E1; font-weight: 300; display: none;" class="d-md-inline">&vert;</span>

        <span style="font-size: 0.8rem; color: #64748B; font-weight: 600; display: none;" class="d-md-inline">
            Staff Operations Console
        </span>
    </div>

    <!-- Right Section: Store State, Date & User Actions -->
    <div class="portal-topbar-right">
        <!-- Store Status Pill -->
        <?php
        $isMaintMode = (store_setting('maintenance_mode', '0') === '1');
        $isOrderAcc  = (store_setting('order_acceptance', '1') === '1');
        ?>
        <div class="portal-store-pill" title="Current Storefront Status">
            <?php if ($isMaintMode): ?>
                <span class="status-dot" style="background:#F59E0B; box-shadow:0 0 0 3px rgba(245, 158, 11, 0.2);"></span>
                <span style="color:#B45309;">Maintenance</span>
            <?php elseif (!$isOrderAcc): ?>
                <span class="status-dot" style="background:#EF4444; box-shadow:0 0 0 3px rgba(239, 68, 68, 0.2);"></span>
                <span style="color:#DC2626;">Orders Paused</span>
            <?php else: ?>
                <span class="portal-pulse-dot"></span>
                <span>Store Online</span>
            <?php endif; ?>
        </div>

        <!-- Date Pill -->
        <div class="portal-header-pill" style="display: none;" class="d-md-flex">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748B;">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span><?= date('D, d M Y') ?></span>
        </div>

        <!-- Staff Member Profile Pill -->
        <div class="portal-user-card">
            <div class="portal-avatar-circle">
                <?= $staffInitial ?>
            </div>
            <div style="text-align: left; line-height: 1.25;">
                <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($staffName) ?>
                </div>
                <div style="font-size: 0.7rem; color: #64748B;">
                    <?= htmlspecialchars($staffRoleDisplay) ?>
                </div>
            </div>
            <a href="<?= url('portal/logout') ?>" class="portal-signout-btn" title="Sign Out of Operations Portal">
                <span>Sign Out</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </a>
        </div>
    </div>
</header>
