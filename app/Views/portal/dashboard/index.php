<?php
$title = 'Operations Dashboard | Jiyaji LX Staff Portal';
$staffRole = $staff['role_slug'] ?? $staff['role_name'] ?? 'staff';
$badgeClass = 'role-pill-orders';
if ($staffRole === 'inventory_manager') {
    $badgeClass = 'role-pill-inventory';
} elseif ($staffRole === 'support_staff') {
    $badgeClass = 'role-pill-support';
} elseif ($staffRole === 'marketing_manager') {
    $badgeClass = 'role-pill-marketing';
}
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="admin-content" style="padding: 1.75rem 2rem;">

            <!-- Welcome Header Hero -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 60%, #312E81 100%); border-radius: 16px; padding: 2rem 2.25rem; color: #FFFFFF; margin-bottom: 2rem; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);">
                <div style="position: absolute; right: -20px; bottom: -30px; opacity: 0.08; pointer-events: none;">
                    <svg width="240" height="240" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>

                <div style="position: relative; z-index: 2; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.6rem;">
                            <span class="portal-badge <?= $badgeClass ?>" style="font-size: 0.75rem; padding: 3px 10px; background: rgba(255,255,255,0.15); color: #FFFFFF; border-color: rgba(255,255,255,0.3);">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                                <?= htmlspecialchars($staff['role_display_name'] ?? $roleName) ?>
                            </span>
                            <span style="font-size: 0.8rem; color: #C7D2FE;">
                                Staff ID #<?= htmlspecialchars($staff['id'] ?? '1') ?>
                            </span>
                        </div>
                        <h1 style="font-size: 1.85rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 0.5rem 0;">
                            Welcome, <?= htmlspecialchars($staff['name'] ?? 'Staff Member') ?>!
                        </h1>
                        <p style="font-size: 0.92rem; color: #CBD5E1; margin: 0; max-width: 650px; line-height: 1.5;">
                            You have signed in to the Jiyaji LX Operations Portal. Your workspace is currently filtered to show only systems and actions authorized for your security role.
                        </p>
                    </div>

                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 0.75rem 1.25rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #FFFFFF; line-height: 1;">
                                <?= count($permittedModules) ?>
                            </div>
                            <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #C7D2FE; margin-top: 4px;">
                                Permitted Systems
                            </div>
                        </div>

                        <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 0.75rem 1.25rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #FFFFFF; line-height: 1;">
                                <?= count($permissions) ?>
                            </div>
                            <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #C7D2FE; margin-top: 4px;">
                                Active Rights
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational KPI Cards (Role-Filtered) -->
            <?php if (!empty($stats['cards'])): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
                    <?php foreach ($stats['cards'] as $cardKey => $card): ?>
                        <div class="stat-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: flex-start; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.04)';">
                            <div>
                                <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #64748B; margin-bottom: 6px;">
                                    <?= htmlspecialchars($card['label']) ?>
                                </div>
                                <div style="font-size: 1.75rem; font-weight: 800; color: #0F172A; letter-spacing: -0.02em; line-height: 1.2; margin-bottom: 4px;">
                                    <?= htmlspecialchars($card['value']) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: #64748B;">
                                    <?= htmlspecialchars($card['subtitle']) ?>
                                </div>
                            </div>
                            <div style="width: 44px; height: 44px; border-radius: 12px; background: <?= htmlspecialchars($card['color']) ?>15; color: <?= htmlspecialchars($card['color']) ?>; display: flex; align-items: center; justify-content: center;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <?php if ($card['icon'] === 'shopping-bag'): ?>
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    <?php elseif ($card['icon'] === 'package'): ?>
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    <?php elseif ($card['icon'] === 'users'): ?>
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <?php elseif ($card['icon'] === 'shield-check'): ?>
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                        <polyline points="9 12 11 14 15 10"></polyline>
                                    <?php elseif ($card['icon'] === 'life-buoy'): ?>
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    <?php else: ?>
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    <?php endif; ?>
                                </svg>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Permitted Store Systems Grid Section -->
            <div style="margin-bottom: 2.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin: 0 0 4px 0; letter-spacing: -0.01em;">
                            Authorized Operational Systems
                        </h2>
                        <p style="font-size: 0.85rem; color: #64748B; margin: 0;">
                            Direct launchpads into all modules unlocked by your security role policy.
                        </p>
                    </div>
                    <span style="font-size: 0.78rem; background: #EEF2FF; color: #4F46E5; font-weight: 700; padding: 4px 12px; border-radius: 9999px;">
                        <?= count($permittedModules) ?> Modules Unlocked
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
                    <?php foreach ($permittedModules as $modKey => $mod): ?>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.2s ease;" onmouseover="this.style.borderColor='<?= $mod['color'] ?>'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px -5px rgba(0,0,0,0.08)';" onmouseout="this.style.borderColor='#E2E8F0'; this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                                    <div style="width: 42px; height: 42px; border-radius: 10px; background: <?= $mod['bg_soft'] ?>; color: <?= $mod['color'] ?>; display: flex; align-items: center; justify-content: center;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <?php if ($mod['icon'] === 'shopping-bag'): ?>
                                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                                            <?php elseif ($mod['icon'] === 'truck'): ?>
                                                <rect x="1" y="3" width="15" height="13"></rect>
                                                <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                            <?php elseif ($mod['icon'] === 'corner-down-left'): ?>
                                                <polyline points="9 10 4 15 9 20"></polyline>
                                                <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                                            <?php elseif ($mod['icon'] === 'package'): ?>
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                            <?php elseif ($mod['icon'] === 'grid'): ?>
                                                <rect x="3" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="3" width="7" height="7"></rect>
                                                <rect x="14" y="14" width="7" height="7"></rect>
                                                <rect x="3" y="14" width="7" height="7"></rect>
                                            <?php elseif ($mod['icon'] === 'users'): ?>
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <?php elseif ($mod['icon'] === 'star'): ?>
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                            <?php elseif ($mod['icon'] === 'life-buoy'): ?>
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                            <?php elseif ($mod['icon'] === 'tag'): ?>
                                                <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                                <rect x="2" y="7" width="20" height="5"></rect>
                                                <line x1="12" y1="22" x2="12" y2="7"></line>
                                            <?php elseif ($mod['icon'] === 'bar-chart-2'): ?>
                                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                                <line x1="6" y1="20" x2="6" y2="14"></line>
                                            <?php elseif ($mod['icon'] === 'credit-card'): ?>
                                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                <line x1="1" y1="10" x2="23" y2="10"></line>
                                            <?php elseif ($mod['icon'] === 'map-pin'): ?>
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            <?php elseif ($mod['icon'] === 'shield'): ?>
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            <?php elseif ($mod['icon'] === 'file-text'): ?>
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            <?php else: ?>
                                                <circle cx="12" cy="12" r="3"></circle>
                                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                            <?php endif; ?>
                                        </svg>
                                    </div>
                                    <span style="font-size: 0.72rem; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 2px 8px; border-radius: 6px;">
                                        <?= htmlspecialchars($mod['category']) ?>
                                    </span>
                                </div>
                                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0F172A; margin: 0 0 6px 0;">
                                    <?= htmlspecialchars($mod['title']) ?>
                                </h3>
                                <p style="font-size: 0.82rem; color: #64748B; margin: 0 0 1.25rem 0; line-height: 1.45;">
                                    <?= htmlspecialchars($mod['desc']) ?>
                                </p>
                            </div>

                            <a href="<?= $mod['url'] ?>" style="display: inline-flex; align-items: center; justify-content: space-between; background: #F8FAFC; border: 1px solid #E2E8F0; color: #0F172A; text-decoration: none; font-size: 0.82rem; font-weight: 700; padding: 10px 14px; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='<?= $mod['color'] ?>'; this.style.borderColor='<?= $mod['color'] ?>'; this.style.color='#FFFFFF';" onmouseout="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0'; this.style.color='#0F172A';">
                                <span><?= htmlspecialchars($mod['action_label']) ?></span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Role Restriction Notice (if any modules are locked) -->
            <?php if ($restrictedCount > 0): ?>
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: flex-start; gap: 14px; margin-bottom: 2rem;">
                    <div style="color: #D97706; margin-top: 2px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: #92400E; margin: 0 0 4px 0;">
                            Role-Based Isolation Active (<?= $restrictedCount ?> Unassigned Systems Filtered Out)
                        </h4>
                        <p style="font-size: 0.82rem; color: #B45309; margin: 0; line-height: 1.5;">
                            In accordance with your assigned <strong><?= htmlspecialchars($staffRoleDisplay) ?></strong> policy, modules outside your operational remit (such as direct Master Settings, System Gateways, or RBAC controls) are automatically filtered out from your navigation and view. If your operational scope expands, your Super Administrator can assign additional rights from the Staff &amp; RBAC console.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Session & Security Audit Snapshot -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid #F1F5F9; padding-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <h4 style="font-size: 0.92rem; font-weight: 700; color: #0F172A; margin: 0;">
                            Portal Session &amp; RBAC Security Audit
                        </h4>
                    </div>
                    <span style="font-size: 0.72rem; color: #10B981; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #10B981;"></span>
                        Verified Session
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.82rem;">
                    <div>
                        <span style="color: #64748B; display: block; margin-bottom: 2px;">Authenticated Staff:</span>
                        <strong style="color: #0F172A;"><?= htmlspecialchars($staff['name'] ?? '') ?> (<?= htmlspecialchars($staff['email'] ?? '') ?>)</strong>
                    </div>
                    <div>
                        <span style="color: #64748B; display: block; margin-bottom: 2px;">Role Clearance:</span>
                        <strong style="color: #0F172A;"><?= htmlspecialchars($staffRoleDisplay) ?></strong>
                    </div>
                    <div>
                        <span style="color: #64748B; display: block; margin-bottom: 2px;">Enforcement Policy:</span>
                        <strong style="color: #4F46E5;">Strict RBAC Dynamic Filtering</strong>
                    </div>
                    <div>
                        <span style="color: #64748B; display: block; margin-bottom: 2px;">Session Started:</span>
                        <strong style="color: #0F172A;"><?= date('h:i A, d M Y') ?></strong>
                    </div>
                </div>
            </div>

        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
