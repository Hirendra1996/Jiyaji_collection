<?php
$adminUser = auth_admin();
$adminName = $adminUser['name'] ?? 'Administrator';
$adminEmail = $adminUser['email'] ?? 'admin@jiyaji.com';
$adminRole = !empty($adminUser['role_name']) ? ucwords(str_replace('_', ' ', $adminUser['role_name'])) : 'Super Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$notifications = $stats['notifications'] ?? [];
$notifCount = count($notifications);
?>

<header class="admin-topbar">
    <div class="topbar-left">
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <div class="topbar-search">
            <span class="search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" placeholder="Quick search..." id="adminSearchInput" readonly title="Search across orders and catalog">
        </div>
    </div>

    <div class="topbar-right">
        <?php
        $isMaintMode = (store_setting('maintenance_mode', '0') === '1');
        $isOrderAcc  = (store_setting('order_acceptance', '1') === '1');
        ?>
        <a href="<?= url('admin/settings?tab=operations') ?>" class="store-status-pill" style="text-decoration:none; cursor:pointer;" title="Click to configure Store Operations">
            <?php if ($isMaintMode): ?>
                <span class="status-dot" style="background:#f59e0b; box-shadow:0 0 0 3px rgba(245, 158, 11, 0.2);"></span>
                <span style="color:#d97706; font-weight:700;">Maintenance Mode</span>
            <?php elseif (!$isOrderAcc): ?>
                <span class="status-dot" style="background:#ef4444; box-shadow:0 0 0 3px rgba(239, 68, 68, 0.2);"></span>
                <span style="color:#dc2626; font-weight:700;">Orders Paused</span>
            <?php else: ?>
                <span class="status-dot"></span>
                <span>Store Online</span>
            <?php endif; ?>
        </a>

        <div class="date-pill">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span><?= date('D, d M Y') ?></span>
        </div>

        <!-- Dynamic Notification Bell Center -->
        <div class="notif-dropdown-wrapper">
            <button class="notif-btn" id="notifBtn" aria-label="Notifications" title="Store Notifications">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($notifCount > 0): ?>
                    <span class="notif-badge" id="notifBadge"><?= $notifCount ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-menu" id="notifMenu">
                <div class="notif-menu-header">
                    <h4>Notifications <?= $notifCount > 0 ? "($notifCount)" : '' ?></h4>
                    <?php if ($notifCount > 0): ?>
                        <button type="button" class="notif-clear-btn" id="clearNotifBtn">Mark all read</button>
                    <?php endif; ?>
                </div>
                <div class="notif-list">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notif-item">
                                <div class="notif-item-icon <?= $notif['icon'] ?>">
                                    <?php if ($notif['icon'] === 'orange'): ?>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                    <?php elseif ($notif['icon'] === 'purple'): ?>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                                    <?php else: ?>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="notif-item-title"><?= htmlspecialchars($notif['title']) ?></div>
                                    <div class="notif-item-time"><?= htmlspecialchars($notif['time']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                            All notifications are caught up!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="profile-dropdown-wrapper">
            <button class="profile-pill" id="profilePill" aria-haspopup="true" aria-expanded="false">
                <div class="profile-avatar"><?= htmlspecialchars($adminInitial) ?></div>
                <div class="profile-info">
                    <div class="profile-name"><?= htmlspecialchars($adminName) ?></div>
                    <div class="profile-role"><?= htmlspecialchars($adminRole) ?></div>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div class="profile-menu" id="profileMenu">
                <div class="menu-user-header">
                    <div class="profile-name" style="font-size: 0.9rem;"><?= htmlspecialchars($adminName) ?></div>
                    <div class="user-email"><?= htmlspecialchars($adminEmail) ?></div>
                </div>

                <a href="<?= url('admin/logout') ?>" class="menu-item logout" id="btnLogout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Sign Out</span>
                </a>
            </div>
        </div>
    </div>
</header>
