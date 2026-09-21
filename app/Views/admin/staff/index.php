<?php
use App\Models\Role;
use App\Models\Staff;

include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$activeTab = $activeTab ?? 'staff';
$staffList = $staffData['staff'] ?? [];
$pagination = $staffData['pagination'] ?? ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false];
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">

            <!-- ============================================================ -->
            <!-- PAGE HEADER & BREADCRUMB                                     -->
            <!-- ============================================================ -->
            <div class="welcome-banner" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color:var(--brand-blue); text-decoration:none;">Dashboard</a>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span>Settings</span>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Staff &amp; RBAC</span>
                    </div>
                    <h1 class="welcome-title">Staff Accounts &amp; Access Control (RBAC)</h1>
                    <p class="welcome-subtitle">Administer team member credentials, configure custom role taxonomies, manage granular module-level permissions, and enforce administrative safety policies.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Create Role Trigger -->
                    <button type="button" onclick="openCreateRoleModal()"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface-secondary); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); cursor:pointer; transition:var(--transition);"
                            onmouseover="this.style.background='var(--border-color)'"
                            onmouseout="this.style.background='var(--bg-surface-secondary)'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="11" r="3"/></svg>
                        + Create Custom Role
                    </button>

                    <!-- Add Staff Trigger -->
                    <button type="button" onclick="openCreateStaffModal()"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); border:none; cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                            onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.opacity='1'; this.style.transform=''">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        + Add Staff Member
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- EXECUTIVE KPIS SUMMARY CARDS (5 CARDS)                       -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
                <!-- KPI 1: Total Staff -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:var(--brand-blue);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="kpi-label">Total Staff</div>
                    <div class="kpi-val"><?= (int)($kpis['total_staff'] ?? 0) ?> Members</div>
                    <div class="kpi-subtext" style="color:var(--status-success); font-weight:600;">
                        <?= (int)($kpis['active_staff'] ?? 0) ?> Active Accounts
                    </div>
                </div>

                <!-- KPI 2: Active Super Admins -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(239, 68, 68, 0.1); color:#ef4444;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    </div>
                    <div class="kpi-label">Super Administrators</div>
                    <div class="kpi-val"><?= (int)($kpis['active_super_admins'] ?? 1) ?> Active</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Full administrative bypass
                    </div>
                </div>

                <!-- KPI 3: Total Roles -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(139, 92, 246, 0.1); color:var(--brand-purple);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="kpi-label">Access Roles</div>
                    <div class="kpi-val"><?= (int)($kpis['total_roles'] ?? 0) ?> Roles</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        <?= (int)($kpis['system_roles'] ?? 0) ?> System / <?= (int)($kpis['custom_roles'] ?? 0) ?> Custom
                    </div>
                </div>

                <!-- KPI 4: System Roles -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--status-success);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    </div>
                    <div class="kpi-label">System Baselines</div>
                    <div class="kpi-val"><?= (int)($kpis['system_roles'] ?? 0) ?> Built-in</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Protected core hierarchy
                    </div>
                </div>

                <!-- KPI 5: Custom Roles -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <div class="kpi-label">Custom Roles</div>
                    <div class="kpi-val"><?= (int)($kpis['custom_roles'] ?? 0) ?> Custom</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Tailored permission sets
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SUB-TABS NAVIGATION CONTROLS                                 -->
            <!-- ============================================================ -->
            <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:24px; gap:8px; overflow-x:auto;">
                <a href="<?= url('admin/staff?tab=staff') ?>"
                   class="tab-btn <?= $activeTab === 'staff' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'staff' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'staff' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Staff Directory (<?= $pagination['total'] ?>)
                </a>

                <a href="<?= url('admin/staff?tab=roles') ?>"
                   class="tab-btn <?= $activeTab === 'roles' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'roles' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'roles' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="11" r="3"/></svg>
                    Roles &amp; Permission Matrix (<?= count($roles) ?>)
                </a>

                <a href="<?= url('admin/staff?tab=policy') ?>"
                   class="tab-btn <?= $activeTab === 'policy' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'policy' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'policy' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Security &amp; Access Policy
                </a>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: STAFF DIRECTORY                                       -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'staff'): ?>
                <!-- Search & Filters Toolbar -->
                <div class="card" style="padding:16px 20px; margin-bottom:20px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                    <form method="GET" action="<?= url('admin/staff') ?>" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <input type="hidden" name="tab" value="staff">

                        <!-- Search text -->
                        <div style="flex:1; min-width:220px; position:relative;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by name, email, or phone..."
                                   style="width:100%; padding:9px 12px 9px 36px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                        </div>

                        <!-- Role filter -->
                        <div style="min-width:170px;">
                            <select name="role" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                                <option value="all">All Roles</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($filters['role'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                        <?= e($r['display_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status filter -->
                        <div style="min-width:140px;">
                            <select name="status" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                                <option value="all">All Statuses</option>
                                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <button type="submit" style="padding:9px 18px; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-md); font-weight:600; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Filter
                        </button>

                        <?php if (!empty($filters['search']) || ($filters['role'] ?? 'all') !== 'all' || ($filters['status'] ?? 'all') !== 'all'): ?>
                            <a href="<?= url('admin/staff?tab=staff') ?>" style="padding:9px 14px; background:var(--bg-surface-secondary); color:var(--text-muted); border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.88rem; text-decoration:none;">
                                Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Staff Table Card -->
                <div class="card" style="padding:0; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
                            <thead>
                                <tr style="background:var(--bg-surface-secondary); border-bottom:1px solid var(--border-color); color:var(--text-secondary); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                                    <th style="padding:14px 20px; font-weight:600;">Staff Member</th>
                                    <th style="padding:14px 16px; font-weight:600;">Role &amp; Privilege</th>
                                    <th style="padding:14px 16px; font-weight:600;">Status</th>
                                    <th style="padding:14px 16px; font-weight:600;">Last Active</th>
                                    <th style="padding:14px 16px; font-weight:600;">Created</th>
                                    <th style="padding:14px 20px; font-weight:600; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($staffList)): ?>
                                    <tr>
                                        <td colspan="6" style="padding:48px 20px; text-align:center; color:var(--text-muted);">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px; opacity:0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            <div style="font-weight:600; color:var(--text-primary); font-size:0.95rem; margin-bottom:4px;">No Staff Accounts Found</div>
                                            <div style="font-size:0.82rem;">No staff members match the specified filters. Try resetting search criteria or add a new team member.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($staffList as $s): 
                                        $initials = strtoupper(substr($s['name'] ?? 'U', 0, 1));
                                        $isCurrentUser = ($s['id'] === $currentAdminId);
                                    ?>
                                        <tr style="border-bottom:1px solid var(--border-color); transition:background 0.15s;" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                            <!-- Staff Member & Contact -->
                                            <td style="padding:14px 20px;">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div style="width:38px; height:38px; border-radius:50%; background:<?= $s['role_name'] === 'super_admin' ? 'linear-gradient(135deg, #ef4444, #b91c1c)' : 'linear-gradient(135deg, var(--brand-blue), #1e40af)' ?>; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.95rem; box-shadow:0 2px 4px rgba(0,0,0,0.1); flex-shrink:0;">
                                                        <?= $initials ?>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight:600; color:var(--text-primary); display:flex; align-items:center; gap:6px;">
                                                            <?= e($s['name']) ?>
                                                            <?php if ($isCurrentUser): ?>
                                                                <span style="font-size:0.65rem; background:rgba(59, 130, 246, 0.15); color:var(--brand-blue); padding:1px 6px; border-radius:999px; font-weight:700; text-transform:uppercase;">You</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="font-size:0.8rem; color:var(--text-muted); display:flex; align-items:center; gap:8px;">
                                                            <span><?= e($s['email']) ?></span>
                                                            <?php if (!empty($s['phone'])): ?>
                                                                <span>&bull;</span>
                                                                <span><?= e($s['phone']) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Role & Privilege -->
                                            <td style="padding:14px 16px;">
                                                <div style="display:inline-flex; align-items:center; gap:6px; background:<?= $s['role_name'] === 'super_admin' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(59, 130, 246, 0.08)' ?>; color:<?= $s['role_name'] === 'super_admin' ? '#ef4444' : 'var(--brand-blue)' ?>; padding:4px 10px; border-radius:999px; font-size:0.8rem; font-weight:600;">
                                                    <?php if ($s['role_name'] === 'super_admin'): ?>
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                                                    <?php else: ?>
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                                    <?php endif; ?>
                                                    <span><?= e($s['role_display_name'] ?? ucwords(str_replace('_', ' ', $s['role_name'] ?? 'Staff'))) ?></span>
                                                </div>
                                                <?php if (!empty($s['role_is_system'])): ?>
                                                    <span style="font-size:0.68rem; color:var(--text-muted); display:block; margin-top:3px; margin-left:4px;">Built-in System Role</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Status -->
                                            <td style="padding:14px 16px;">
                                                <?php if ($s['is_active']): ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(16, 185, 129, 0.12); color:var(--status-success); font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:999px;">
                                                        <span style="width:6px; height:6px; border-radius:50%; background:var(--status-success);"></span>
                                                        Active
                                                    </span>
                                                <?php else: ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(239, 68, 68, 0.12); color:var(--status-error); font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:999px;">
                                                        <span style="width:6px; height:6px; border-radius:50%; background:var(--status-error);"></span>
                                                        Suspended
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Last Active -->
                                            <td style="padding:14px 16px; color:var(--text-secondary); font-size:0.82rem;">
                                                <?php $loginTs = validTs($s['last_login_at']); ?>
                                                <?php if ($loginTs): ?>
                                                    <div style="font-weight:500; color:var(--text-primary);"><?= date('M d, Y', $loginTs) ?></div>
                                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= date('h:i A', $loginTs) ?></div>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-style:italic;">Never logged in</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Created At -->
                                            <td style="padding:14px 16px; color:var(--text-secondary); font-size:0.82rem;">
                                                <?php $createdTs = validTs($s['created_at']); ?>
                                                <div><?= $createdTs ? date('M d, Y', $createdTs) : '—' ?></div>
                                                <?php if (!empty($s['creator_name'])): ?>
                                                    <div style="font-size:0.73rem; color:var(--text-muted);">by <?= e($s['creator_name']) ?></div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Actions -->
                                            <td style="padding:14px 20px; text-align:right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">
                                                    <!-- Edit Button -->
                                                    <button type="button" class="btn-icon" title="Edit Staff Member"
                                                            onclick='openEditStaffModal(<?= json_encode([
                                                                'id'        => $s['id'],
                                                                'name'      => $s['name'],
                                                                'email'     => $s['email'],
                                                                'phone'     => $s['phone'] ?? '',
                                                                'role_id'   => $s['role_id'],
                                                                'is_active' => (int)$s['is_active']
                                                            ]) ?>)'
                                                            style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-primary); cursor:pointer; transition:var(--transition);"
                                                            onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                                                            onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>

                                                    <!-- Toggle Status Form -->
                                                    <?php if (!$isCurrentUser): ?>
                                                        <form method="POST" action="<?= url('admin/staff/toggle') ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to <?= $s['is_active'] ? 'deactivate' : 'activate' ?> this account?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                            <input type="hidden" name="is_active" value="<?= $s['is_active'] ? '0' : '1' ?>">
                                                            <button type="submit" class="btn-icon" title="<?= $s['is_active'] ? 'Suspend Account' : 'Activate Account' ?>"
                                                                    style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:<?= $s['is_active'] ? 'var(--status-warning)' : 'var(--status-success)' ?>; cursor:pointer; transition:var(--transition);"
                                                                    onmouseover="this.style.borderColor='currentColor'"
                                                                    onmouseout="this.style.borderColor='var(--border-color)'">
                                                                <?php if ($s['is_active']): ?>
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                                <?php else: ?>
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                                <?php endif; ?>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <!-- Delete Button Form -->
                                                    <?php if (!$isCurrentUser): ?>
                                                        <form method="POST" action="<?= url('admin/staff/delete') ?>" style="display:inline;" onsubmit="return confirm('Permanently remove staff account for <?= addslashes($s['name']) ?>? This action cannot be undone.');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                            <button type="submit" class="btn-icon" title="Delete Staff Account"
                                                                    style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--status-error); cursor:pointer; transition:var(--transition);"
                                                                    onmouseover="this.style.borderColor='var(--status-error)'; this.style.background='rgba(239, 68, 68, 0.08)'"
                                                                    onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface)'">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-top:1px solid var(--border-color); background:var(--bg-surface-secondary); flex-wrap:wrap; gap:10px;">
                            <div style="font-size:0.82rem; color:var(--text-muted);">
                                Showing <?= count($staffList) ?> of <?= $pagination['total'] ?> total staff accounts
                            </div>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <?php if ($pagination['has_prev']): ?>
                                    <a href="<?= url('admin/staff?tab=staff&page=' . ($pagination['current_page'] - 1) . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (($filters['role'] ?? 'all') !== 'all' ? '&role=' . $filters['role'] : '') . (($filters['status'] ?? 'all') !== 'all' ? '&status=' . $filters['status'] : '')) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); text-decoration:none; color:var(--text-primary); background:var(--bg-surface);">
                                        &larr; Prev
                                    </a>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                    <a href="<?= url('admin/staff?tab=staff&page=' . $p . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (($filters['role'] ?? 'all') !== 'all' ? '&role=' . $filters['role'] : '') . (($filters['status'] ?? 'all') !== 'all' ? '&status=' . $filters['status'] : '')) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid <?= $p === $pagination['current_page'] ? 'var(--brand-blue)' : 'var(--border-color)' ?>; border-radius:var(--radius-sm); text-decoration:none; color:<?= $p === $pagination['current_page'] ? '#fff' : 'var(--text-primary)' ?>; background:<?= $p === $pagination['current_page'] ? 'var(--brand-blue)' : 'var(--bg-surface)' ?>;">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <a href="<?= url('admin/staff?tab=staff&page=' . ($pagination['current_page'] + 1) . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (($filters['role'] ?? 'all') !== 'all' ? '&role=' . $filters['role'] : '') . (($filters['status'] ?? 'all') !== 'all' ? '&status=' . $filters['status'] : '')) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); text-decoration:none; color:var(--text-primary); background:var(--bg-surface);">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- ============================================================ -->
            <!-- TAB 2: ROLES & PERMISSION MATRIX                             -->
            <!-- ============================================================ -->
            <?php elseif ($activeTab === 'roles'): ?>
                <!-- Roles Grid -->
                <div style="margin-bottom:28px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <div>
                            <h2 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">Configured Access Roles</h2>
                            <p style="font-size:0.85rem; color:var(--text-muted);">Built-in system roles provide baseline security tiers, while custom roles allow surgical authority delegation.</p>
                        </div>
                        <button type="button" onclick="openCreateRoleModal()"
                                style="padding:8px 14px; font-size:0.85rem; font-weight:600; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-md); color:var(--brand-blue); cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            + New Role
                        </button>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:18px;">
                        <?php foreach ($roles as $r): 
                            $assignedPerms = \App\Models\Role::getRolePermissions($r['id']);
                        ?>
                            <div class="card" style="padding:20px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); display:flex; flex-direction:column; justify-content:space-between; transition:var(--transition);" onmouseover="this.style.borderColor='var(--brand-blue)'" onmouseout="this.style.borderColor='var(--border-color)'">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <div style="width:32px; height:32px; border-radius:var(--radius-sm); background:<?= $r['name'] === 'super_admin' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(59, 130, 246, 0.1)' ?>; color:<?= $r['name'] === 'super_admin' ? '#ef4444' : 'var(--brand-blue)' ?>; display:flex; align-items:center; justify-content:center;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                            </div>
                                            <div>
                                                <h3 style="font-size:0.98rem; font-weight:700; color:var(--text-primary); margin:0;">
                                                    <?= e($r['display_name']) ?>
                                                </h3>
                                                <span style="font-size:0.75rem; color:var(--text-muted); font-family:monospace;">
                                                    <?= e($r['name']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($r['is_system']): ?>
                                            <span style="font-size:0.7rem; background:rgba(16, 185, 129, 0.12); color:var(--status-success); font-weight:700; padding:2px 8px; border-radius:999px;">
                                                System
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size:0.7rem; background:rgba(245, 158, 11, 0.12); color:#d97706; font-weight:700; padding:2px 8px; border-radius:999px;">
                                                Custom
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p style="font-size:0.83rem; color:var(--text-secondary); line-height:1.45; margin-bottom:16px;">
                                        <?= e($r['description'] ?: 'Custom role with tailored access controls.') ?>
                                    </p>
                                </div>

                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:var(--bg-surface-secondary); border-radius:var(--radius-md); font-size:0.8rem; margin-bottom:14px;">
                                        <div style="display:flex; align-items:center; gap:6px; color:var(--text-secondary);">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            <span><strong><?= $r['staff_count'] ?></strong> Assigned Staff</span>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:6px; color:var(--brand-blue); font-weight:600;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                            <span><strong><?= count($assignedPerms) ?></strong> Permissions</span>
                                        </div>
                                    </div>

                                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                                        <!-- Edit Role Button -->
                                        <button type="button" onclick='openEditRoleModal(<?= json_encode([
                                            'id'           => $r['id'],
                                            'display_name' => $r['display_name'],
                                            'description'  => $r['description'] ?? '',
                                            'is_system'    => $r['is_system'],
                                            'permissions'  => $assignedPerms
                                        ]) ?>)'
                                                style="padding:6px 12px; font-size:0.8rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-surface); color:var(--text-primary); cursor:pointer; display:inline-flex; align-items:center; gap:5px;"
                                                onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                                                onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            <?= $r['is_system'] ? 'View & Edit' : 'Edit Role' ?>
                                        </button>

                                        <!-- Delete Role Button (only if custom & no staff) -->
                                        <?php if (!$r['is_system']): ?>
                                            <form method="POST" action="<?= url('admin/staff/role/delete') ?>" style="display:inline;" onsubmit="return confirm('Permanently delete custom role \'<?= addslashes($r['display_name']) ?>\'?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <button type="submit" <?= $r['staff_count'] > 0 ? 'disabled title="Reassign active staff first"' : '' ?>
                                                        style="padding:6px 10px; font-size:0.8rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-surface); color:var(--status-error); cursor:<?= $r['staff_count'] > 0 ? 'not-allowed' : 'pointer' ?>; opacity:<?= $r['staff_count'] > 0 ? '0.5' : '1' ?>; display:inline-flex; align-items:center;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Full Permissions Taxonomy Visualizer -->
                <div class="card" style="padding:24px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                    <div style="margin-bottom:20px;">
                        <h2 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">Granular Permission Taxonomy (30+ Controls)</h2>
                        <p style="font-size:0.85rem; color:var(--text-muted);">Review all available security privileges across 14 functional sub-systems in the Jiyaji LX e-commerce infrastructure.</p>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:18px;">
                        <?php foreach ($allPermissions as $modKey => $group): ?>
                            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; background:var(--bg-surface-secondary);">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--border-color);">
                                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--brand-blue); background:rgba(59, 130, 246, 0.1); padding:2px 8px; border-radius:999px;">
                                        <?= e($modKey) ?>
                                    </span>
                                    <h4 style="font-size:0.92rem; font-weight:700; color:var(--text-primary); margin:0;">
                                        <?= e($group['title']) ?>
                                    </h4>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:8px;">
                                    <?php foreach ($group['permissions'] as $p): ?>
                                        <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-surface); padding:7px 10px; border-radius:var(--radius-sm); border:1px solid var(--border-color); font-size:0.8rem;">
                                            <div>
                                                <div style="font-weight:600; color:var(--text-primary);"><?= e($p['label']) ?></div>
                                                <div style="font-size:0.72rem; color:var(--text-muted); font-family:monospace;"><?= e($modKey) ?>.<?= e($p['action']) ?></div>
                                            </div>
                                            <span style="font-size:0.7rem; color:var(--text-muted); text-align:right; max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                <?= e($p['description']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <!-- ============================================================ -->
            <!-- TAB 3: SECURITY & ACCESS POLICY                              -->
            <!-- ============================================================ -->
            <?php elseif ($activeTab === 'policy'): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px;">
                    <!-- Policy Card 1: Root Safeguards -->
                    <div class="card" style="padding:24px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                        <div style="width:40px; height:40px; border-radius:50%; background:rgba(239, 68, 68, 0.1); color:#ef4444; display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:8px;">Root Account Protections</h3>
                        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom:16px;">
                            Administrative safety controls prevent administrative lockouts and ensure continuity of access:
                        </p>
                        <ul style="font-size:0.82rem; color:var(--text-secondary); line-height:1.6; padding-left:18px; margin:0;">
                            <li><strong>Self-Destruction Guard:</strong> Authenticated administrators cannot delete or deactivate their own active accounts.</li>
                            <li><strong>Last Super Admin Protection:</strong> The last remaining active Super Administrator account cannot be deleted, suspended, or demoted to another role.</li>
                            <li><strong>System Role Immutability:</strong> Baseline roles (<code style="color:var(--brand-blue);">super_admin</code>, <code style="color:var(--brand-blue);">order_manager</code>, etc.) cannot be deleted.</li>
                        </ul>
                    </div>

                    <!-- Policy Card 2: Permission Resolution -->
                    <div class="card" style="padding:24px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                        <div style="width:40px; height:40px; border-radius:50%; background:rgba(59, 130, 246, 0.1); color:var(--brand-blue); display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:8px;">Permission Resolution Engine</h3>
                        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom:16px;">
                            How authorization decisions are calculated throughout the admin portal:
                        </p>
                        <ul style="font-size:0.82rem; color:var(--text-secondary); line-height:1.6; padding-left:18px; margin:0;">
                            <li><strong>Super Admin Privilege:</strong> Staff assigned to the <code style="color:var(--brand-blue);">super_admin</code> role bypass all permission checks with immediate unrestricted access.</li>
                            <li><strong>Suspended Account Enforcement:</strong> Suspended or inactive staff members are instantly denied all mutating actions regardless of assigned role.</li>
                            <li><strong>Granular Module Checks:</strong> Each controller action resolves via <code style="color:var(--brand-blue);">Staff::hasPermission($adminId, $module, $action)</code>.</li>
                        </ul>
                    </div>

                    <!-- Policy Card 3: Credential & Session Security -->
                    <div class="card" style="padding:24px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                        <div style="width:40px; height:40px; border-radius:50%; background:rgba(16, 185, 129, 0.1); color:var(--status-success); display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:8px;">Credential &amp; CSRF Defense</h3>
                        <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom:16px;">
                            Enterprise-grade cryptography and session validation:
                        </p>
                        <ul style="font-size:0.82rem; color:var(--text-secondary); line-height:1.6; padding-left:18px; margin:0;">
                            <li><strong>Bcrypt Key Stretching:</strong> Passwords hashed with <code style="color:var(--brand-blue);">PASSWORD_DEFAULT</code> (bcrypt), salted automatically with cost factor 10+.</li>
                            <li><strong>Strict CSRF Tokens:</strong> Every POST action requires valid cryptographic session tokens verified through <code style="color:var(--brand-blue);">csrf_verify()</code>.</li>
                            <li><strong>Activity Audit Trail:</strong> Staff logins update <code style="color:var(--brand-blue);">last_login_at</code> in real-time, providing transparency into administrative activity.</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: ADD / EDIT STAFF MEMBER                                          -->
<!-- ========================================================================= -->
<div id="staffModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-xl); width:100%; max-width:540px; box-shadow:var(--shadow-xl); overflow:hidden; animation:modalSlideUp 0.2s ease-out;">
        <!-- Modal Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 24px; border-bottom:1px solid var(--border-color); background:var(--bg-surface-secondary);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; border-radius:var(--radius-sm); background:rgba(59, 130, 246, 0.1); color:var(--brand-blue); display:flex; align-items:center; justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>
                </div>
                <h3 id="staffModalTitle" style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:0;">Add Staff Member</h3>
            </div>
            <button type="button" onclick="closeStaffModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- Modal Form -->
        <form method="POST" action="<?= url('admin/staff/save') ?>" id="staffForm" style="padding:24px;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="staffId" value="0">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <!-- Full Name -->
                <div style="grid-column:1 / -1;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Full Name *</label>
                    <input type="text" name="name" id="staffName" required placeholder="e.g. Rahul Sharma"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                </div>

                <!-- Email -->
                <div>
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Email Address *</label>
                    <input type="email" name="email" id="staffEmail" required placeholder="name@jiyaji.com"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                </div>

                <!-- Phone -->
                <div>
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Contact Phone</label>
                    <input type="tel" name="phone" id="staffPhone" placeholder="+91 98765 43210"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                </div>

                <!-- Role Selection -->
                <div>
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Assigned Role *</label>
                    <select name="role_id" id="staffRoleId" required
                            style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Account Status -->
                <div>
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Account Status</label>
                    <select name="is_active" id="staffIsActive"
                            style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                        <option value="1">Active</option>
                        <option value="0">Suspended</option>
                    </select>
                </div>

                <!-- Password -->
                <div style="grid-column:1 / -1;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">
                        <span id="staffPasswordLabel">Password *</span>
                    </label>
                    <input type="password" name="password" id="staffPassword" placeholder="Minimum 6 characters"
                           style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                    <span id="staffPasswordHint" style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Must be at least 6 characters.</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color);">
                <button type="button" onclick="closeStaffModal()"
                        style="padding:10px 16px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface); color:var(--text-primary); font-size:0.88rem; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" id="staffSubmitBtn"
                        style="padding:10px 20px; border:none; border-radius:var(--radius-md); background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-glow-blue);">
                    Save Staff Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: CREATE / EDIT ROLE & PERMISSION MATRIX                           -->
<!-- ========================================================================= -->
<div id="roleModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-xl); width:100%; max-width:760px; max-height:90vh; display:flex; flex-direction:column; box-shadow:var(--shadow-xl); overflow:hidden; animation:modalSlideUp 0.2s ease-out;">
        <!-- Modal Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 24px; border-bottom:1px solid var(--border-color); background:var(--bg-surface-secondary); flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; border-radius:var(--radius-sm); background:rgba(139, 92, 246, 0.1); color:var(--brand-purple); display:flex; align-items:center; justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="11" r="3"/></svg>
                </div>
                <h3 id="roleModalTitle" style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:0;">Create Custom Role</h3>
            </div>
            <button type="button" onclick="closeRoleModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- Modal Form with scrollable permissions list -->
        <form method="POST" action="<?= url('admin/staff/role/save') ?>" id="roleForm" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="roleId" value="0">

            <div style="padding:20px 24px; overflow-y:auto; flex:1;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Role Display Name *</label>
                        <input type="text" name="display_name" id="roleDisplayName" required placeholder="e.g. VIP Concierge Manager"
                               style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Description</label>
                        <input type="text" name="description" id="roleDescription" placeholder="Brief summary of authority"
                               style="width:100%; padding:10px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                    </div>
                </div>

                <!-- Permissions Matrix Selector -->
                <div style="border-top:1px solid var(--border-color); padding-top:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <h4 style="font-size:0.92rem; font-weight:700; color:var(--text-primary); margin:0;">Assigned Permissions Matrix</h4>
                        <div style="display:flex; gap:8px;">
                            <button type="button" onclick="selectAllRolePerms(true)" style="padding:4px 10px; font-size:0.75rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--brand-blue); cursor:pointer;">
                                Select All
                            </button>
                            <button type="button" onclick="selectAllRolePerms(false)" style="padding:4px 10px; font-size:0.75rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-muted); cursor:pointer;">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <?php foreach ($allPermissions as $modKey => $group): ?>
                            <div style="background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:12px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; border-bottom:1px solid var(--border-color); padding-bottom:6px;">
                                    <span style="font-size:0.8rem; font-weight:700; color:var(--text-primary);">
                                        <?= e($group['title']) ?>
                                    </span>
                                    <button type="button" onclick="toggleModulePerms('<?= $modKey ?>')" style="font-size:0.7rem; color:var(--brand-blue); background:none; border:none; cursor:pointer; font-weight:600;">
                                        Toggle
                                    </button>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:6px;">
                                    <?php foreach ($group['permissions'] as $p): ?>
                                        <label style="display:flex; align-items:flex-start; gap:8px; font-size:0.8rem; color:var(--text-secondary); cursor:pointer;">
                                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" class="role-perm-check mod-check-<?= $modKey ?>" style="margin-top:2px;">
                                            <div>
                                                <span style="font-weight:600; color:var(--text-primary);"><?= e($p['label']) ?></span>
                                                <span style="display:block; font-size:0.72rem; color:var(--text-muted);"><?= e($p['description']) ?></span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid var(--border-color); background:var(--bg-surface-secondary); flex-shrink:0;">
                <button type="button" onclick="closeRoleModal()"
                        style="padding:10px 16px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface); color:var(--text-primary); font-size:0.88rem; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" id="roleSubmitBtn"
                        style="padding:10px 20px; border:none; border-radius:var(--radius-md); background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-glow-blue);">
                    Save Role &amp; Permissions
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
// ============================================================================
// STAFF MODAL CONTROLS
// ============================================================================
function openCreateStaffModal() {
    document.getElementById('staffId').value = '0';
    document.getElementById('staffModalTitle').innerText = 'Add Staff Member';
    document.getElementById('staffSubmitBtn').innerText = 'Save Staff Member';
    document.getElementById('staffName').value = '';
    document.getElementById('staffEmail').value = '';
    document.getElementById('staffPhone').value = '';
    document.getElementById('staffRoleId').selectedIndex = 0;
    document.getElementById('staffIsActive').value = '1';
    document.getElementById('staffPassword').value = '';
    document.getElementById('staffPassword').required = true;
    document.getElementById('staffPasswordLabel').innerText = 'Password *';
    document.getElementById('staffPasswordHint').innerText = 'Minimum 6 characters.';

    const modal = document.getElementById('staffModal');
    modal.style.display = 'flex';
}

function openEditStaffModal(staff) {
    document.getElementById('staffId').value = staff.id;
    document.getElementById('staffModalTitle').innerText = 'Edit Staff Member: ' + staff.name;
    document.getElementById('staffSubmitBtn').innerText = 'Update Staff Account';
    document.getElementById('staffName').value = staff.name || '';
    document.getElementById('staffEmail').value = staff.email || '';
    document.getElementById('staffPhone').value = staff.phone || '';
    document.getElementById('staffRoleId').value = staff.role_id;
    document.getElementById('staffIsActive').value = staff.is_active ? '1' : '0';
    document.getElementById('staffPassword').value = '';
    document.getElementById('staffPassword').required = false;
    document.getElementById('staffPasswordLabel').innerText = 'New Password (Optional)';
    document.getElementById('staffPasswordHint').innerText = 'Leave blank to keep existing password.';

    const modal = document.getElementById('staffModal');
    modal.style.display = 'flex';
}

function closeStaffModal() {
    document.getElementById('staffModal').style.display = 'none';
}

// ============================================================================
// ROLE MODAL CONTROLS
// ============================================================================
function openCreateRoleModal() {
    document.getElementById('roleId').value = '0';
    document.getElementById('roleModalTitle').innerText = 'Create Custom Role';
    document.getElementById('roleSubmitBtn').innerText = 'Create Role & Permissions';
    document.getElementById('roleDisplayName').value = '';
    document.getElementById('roleDescription').value = '';
    selectAllRolePerms(false);

    const modal = document.getElementById('roleModal');
    modal.style.display = 'flex';
}

function openEditRoleModal(role) {
    document.getElementById('roleId').value = role.id;
    document.getElementById('roleModalTitle').innerText = (role.is_system ? 'View & Edit System Role: ' : 'Edit Role: ') + role.display_name;
    document.getElementById('roleSubmitBtn').innerText = 'Update Role & Permissions';
    document.getElementById('roleDisplayName').value = role.display_name || '';
    document.getElementById('roleDescription').value = role.description || '';

    // Uncheck all first
    selectAllRolePerms(false);

    // Check assigned
    const assigned = role.permissions || [];
    const checks = document.querySelectorAll('.role-perm-check');
    checks.forEach(chk => {
        if (assigned.includes(parseInt(chk.value, 10))) {
            chk.checked = true;
        }
    });

    const modal = document.getElementById('roleModal');
    modal.style.display = 'flex';
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

function selectAllRolePerms(checked) {
    const checks = document.querySelectorAll('.role-perm-check');
    checks.forEach(chk => { chk.checked = checked; });
}

function toggleModulePerms(modKey) {
    const checks = document.querySelectorAll('.mod-check-' + modKey);
    const anyUnchecked = Array.from(checks).some(c => !c.checked);
    checks.forEach(c => { c.checked = anyUnchecked; });
}

// Close modals when clicking backdrop
window.addEventListener('click', function(e) {
    const staffM = document.getElementById('staffModal');
    const roleM = document.getElementById('roleModal');
    if (e.target === staffM) closeStaffModal();
    if (e.target === roleM) closeRoleModal();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
