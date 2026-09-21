<?php
include __DIR__ . '/../layouts/header.php';

/**
 * Guard against MySQL zero-dates and negative Unix timestamps.
 */
function validTs(?string $d): int|false {
    if (empty($d) || $d === '0000-00-00 00:00:00') return false;
    $ts = strtotime($d);
    return ($ts && $ts > 946684800) ? $ts : false;
}

$statusTabs = [
    'all'       => 'All Coupons',
    'active'    => 'Active',
    'expired'   => 'Expired',
    'disabled'  => 'Disabled',
    'exhausted' => 'Exhausted',
];

$curStatus = $filters['status'] ?? 'all';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">

            <!-- ============================================================ -->
            <!-- PAGE HEADER                                                   -->
            <!-- ============================================================ -->
            <div class="welcome-banner" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color:var(--brand-blue);">Dashboard</a>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span>Marketing &amp; Growth</span>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Coupons &amp; Promos</span>
                    </div>
                    <h1 class="welcome-title">Coupons &amp; Promotions</h1>
                    <p class="welcome-subtitle">Create, manage and monitor discount codes — flat amounts, percentage offers, and time-limited flash campaigns.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="<?= url('admin/coupons/create') ?>"
                       style="display:inline-flex; align-items:center; gap:8px; background:var(--brand-blue); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 20px; border-radius:var(--radius-md); text-decoration:none; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                       onmouseover="this.style.background='var(--brand-blue-hover)'; this.style.transform='translateY(-1px)'"
                       onmouseout="this.style.background='var(--brand-blue)'; this.style.transform=''">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        New Coupon
                    </a>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- KPI CARDS                                                     -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px;">
                <!-- Total -->
                <div class="kpi-card">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg,#64748b 0%,#94a3b8 100%); width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value"><?= number_format($kpis['total_coupons']) ?></div>
                    <div class="kpi-label">Total Coupons</div>
                </div>

                <!-- Active -->
                <div class="kpi-card" style="border-left:3px solid var(--status-success);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon teal" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <?php if ($kpis['active_count'] > 0): ?>
                            <span style="font-size:0.68rem; font-weight:700; background:var(--status-success-bg); color:var(--status-success); padding:2px 8px; border-radius:var(--radius-full);">Live</span>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-value" style="color:var(--status-success);"><?= number_format($kpis['active_count']) ?></div>
                    <div class="kpi-label">Active</div>
                </div>

                <!-- Expiring Soon -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-orange);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon orange" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <?php if ($kpis['expiring_soon'] > 0): ?>
                            <span style="font-size:0.68rem; font-weight:700; background:var(--brand-orange-light); color:var(--brand-orange); padding:2px 8px; border-radius:var(--radius-full);">⚠ Act Now</span>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-orange);"><?= number_format($kpis['expiring_soon']) ?></div>
                    <div class="kpi-label">Expiring in 7 Days</div>
                </div>

                <!-- Redemptions -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-blue);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon blue" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-blue);"><?= number_format($kpis['total_redemptions']) ?></div>
                    <div class="kpi-label">Total Redemptions</div>
                </div>

                <!-- Savings Issued -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-purple);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon purple" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-purple); font-size:1.5rem;"><?= currency($kpis['total_savings']) ?></div>
                    <div class="kpi-label">Total Savings Issued</div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- STATUS TABS                                                   -->
            <!-- ============================================================ -->
            <div style="display:flex; gap:4px; margin-bottom:18px; flex-wrap:wrap; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:5px; box-shadow:var(--shadow-subtle);">
                <?php foreach ($statusTabs as $key => $label):
                    $isActive = $curStatus === $key;
                    $href = url('admin/coupons?' . http_build_query(array_merge($_GET, ['status' => $key, 'page' => 1])));
                    $count = match($key) {
                        'all'    => $kpis['total_coupons'],
                        'active' => $kpis['active_count'],
                        'expired'=> $kpis['expired_count'],
                        default  => null,
                    };
                ?>
                <a href="<?= $href ?>"
                   style="padding:7px 16px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:700; text-decoration:none; transition:var(--transition); white-space:nowrap;
                          background:<?= $isActive ? 'var(--brand-blue)' : 'transparent' ?>;
                          color:<?= $isActive ? '#fff' : 'var(--text-secondary)' ?>;
                          box-shadow:<?= $isActive ? '0 2px 8px rgba(45,130,255,0.3)' : 'none' ?>;"
                   onmouseover="if(!this.style.background.includes('brand-blue') && !this.style.background.includes('2D82FF')) this.style.background='var(--bg-surface-secondary)'"
                   onmouseout="if(!<?= $isActive ? 'true' : 'false' ?>) this.style.background='transparent'">
                    <?= $label ?>
                    <?php if ($count !== null): ?>
                        <span style="font-size:0.7rem; font-weight:600; opacity:0.8; margin-left:3px;">(<?= $count ?>)</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- ============================================================ -->
            <!-- FILTER TOOLBAR                                                -->
            <!-- ============================================================ -->
            <div class="card-panel" style="margin-bottom:20px; padding:14px 18px;">
                <form method="GET" action="<?= url('admin/coupons') ?>" class="catalog-filter-form">
                    <input type="hidden" name="status" value="<?= e($filters['status']) ?>">

                    <div style="position:relative; flex:2; min-width:220px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; z-index:1;">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" name="search" class="form-input" value="<?= e($filters['search']) ?>"
                               placeholder="Search code or description…" style="padding-left:36px;">
                    </div>

                    <select name="type" class="form-input" style="width:150px; flex-shrink:0;">
                        <option value="all"        <?= $filters['type'] === 'all'        ? 'selected' : '' ?>>All Types</option>
                        <option value="flat"       <?= $filters['type'] === 'flat'       ? 'selected' : '' ?>>₹ Flat Discount</option>
                        <option value="percentage" <?= $filters['type'] === 'percentage' ? 'selected' : '' ?>>% Percentage</option>
                    </select>

                    <select name="sort" class="form-input" style="width:160px; flex-shrink:0;">
                        <option value="newest"    <?= $filters['sort'] === 'newest'    ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest"    <?= $filters['sort'] === 'oldest'    ? 'selected' : '' ?>>Oldest First</option>
                        <option value="most_used" <?= $filters['sort'] === 'most_used' ? 'selected' : '' ?>>Most Used</option>
                        <option value="expiring"  <?= $filters['sort'] === 'expiring'  ? 'selected' : '' ?>>Expiring Soon</option>
                    </select>

                    <button type="submit"
                            style="padding:0 20px; height:44px; background:var(--brand-blue); color:#fff; border-radius:var(--radius-md); font-size:0.88rem; font-weight:700; transition:var(--transition); white-space:nowrap; flex-shrink:0;"
                            onmouseover="this.style.background='var(--brand-blue-hover)'"
                            onmouseout="this.style.background='var(--brand-blue)'">
                        Apply
                    </button>

                    <?php if (!empty($filters['search']) || $filters['type'] !== 'all' || $filters['sort'] !== 'newest'): ?>
                        <a href="<?= url('admin/coupons?status=' . $filters['status']) ?>"
                           style="padding:0 16px; height:44px; display:flex; align-items:center; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.85rem; color:var(--text-secondary); text-decoration:none; font-weight:600; flex-shrink:0; transition:var(--transition);"
                           onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                           onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)'">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- ============================================================ -->
            <!-- COUPONS TABLE                                                 -->
            <!-- ============================================================ -->
            <div class="card-panel" style="padding:0; overflow:hidden;">
                <?php if (empty($coupons)): ?>
                    <div class="empty-state" style="padding:60px 40px;">
                        <div class="empty-state-icon" style="width:64px; height:64px; border-radius:var(--radius-lg); background:var(--brand-purple-light);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                        <p class="empty-state-title">No coupons found</p>
                        <p class="empty-state-desc">
                            <?php if (!empty($filters['search'])): ?>
                                No results for "<?= e($filters['search']) ?>".
                                <a href="<?= url('admin/coupons') ?>" style="color:var(--brand-blue);">Clear filters</a>
                            <?php else: ?>
                                <a href="<?= url('admin/coupons/create') ?>" style="color:var(--brand-blue);">Create your first coupon →</a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Code &amp; Description</th>
                                    <th>Type / Value</th>
                                    <th>Min Cart</th>
                                    <th>Usage</th>
                                    <th>Validity</th>
                                    <th>Status</th>
                                    <th style="text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($coupons as $coupon):
                                $enc = encrypt_id($coupon['id']);
                                $status = $coupon['computed_status'];

                                $badgeStyle = match($status) {
                                    'active'    => 'background:var(--status-success-bg); color:var(--status-success); border:1px solid rgba(16,185,129,0.25);',
                                    'expired'   => 'background:var(--bg-surface-secondary); color:var(--text-secondary); border:1px solid var(--border-color);',
                                    'disabled'  => 'background:var(--status-danger-bg); color:var(--status-danger); border:1px solid rgba(239,68,68,0.2);',
                                    'exhausted' => 'background:var(--brand-orange-light); color:var(--brand-orange); border:1px solid var(--brand-orange-border);',
                                    default     => 'background:var(--bg-surface-secondary); color:var(--text-muted);',
                                };
                                $badgeLabel = match($status) {
                                    'active'    => '● Active',
                                    'expired'   => '○ Expired',
                                    'disabled'  => '○ Disabled',
                                    'exhausted' => '● Exhausted',
                                    default     => $status,
                                };

                                $usagePct  = $coupon['usage_limit_global'] ? round($coupon['times_used'] / $coupon['usage_limit_global'] * 100) : null;
                                $startsTs  = validTs($coupon['starts_at'] ?? null);
                                $expiresTs = validTs($coupon['expires_at'] ?? null);
                                $daysLeft  = $expiresTs ? (int)ceil(($expiresTs - time()) / 86400) : null;
                            ?>
                                <tr style="cursor:pointer;" onclick="window.location='<?= url('admin/coupons/' . $enc) ?>'">

                                    <!-- Code -->
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="width:38px; height:38px; border-radius:var(--radius-md); background:var(--brand-purple-light); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                            </div>
                                            <div style="min-width:0;">
                                                <div class="order-code" style="font-family:monospace; font-size:0.9rem; letter-spacing:0.8px;"><?= e($coupon['code']) ?></div>
                                                <?php if (!empty($coupon['description'])): ?>
                                                    <div style="font-size:0.76rem; color:var(--text-muted); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px;"><?= e($coupon['description']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type / Value -->
                                    <td>
                                        <?php if ($coupon['type'] === 'percentage'): ?>
                                            <div style="font-size:1.05rem; font-weight:800; color:var(--brand-blue); line-height:1.1;"><?= (int)$coupon['value'] ?>%</div>
                                            <div style="font-size:0.72rem; color:var(--text-muted);">Percentage Off</div>
                                            <?php if (!empty($coupon['max_discount_cap'])): ?>
                                                <div style="font-size:0.72rem; color:var(--status-warning); font-weight:600; margin-top:1px;">Cap: <?= currency($coupon['max_discount_cap']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div style="font-size:1.05rem; font-weight:800; color:var(--status-success); line-height:1.1;"><?= currency($coupon['value']) ?></div>
                                            <div style="font-size:0.72rem; color:var(--text-muted);">Flat Discount</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Min Cart -->
                                    <td class="amount-cell" style="font-size:0.88rem;">
                                        <?= ($coupon['min_cart_value'] > 0) ? currency($coupon['min_cart_value']) : '<span style="color:var(--text-muted); font-weight:400;">—</span>' ?>
                                    </td>

                                    <!-- Usage -->
                                    <td>
                                        <div style="font-size:0.88rem; font-weight:700; color:var(--text-primary);">
                                            <?= number_format($coupon['times_used']) ?>
                                            <?php if ($coupon['usage_limit_global']): ?>
                                                <span style="font-weight:400; color:var(--text-muted); font-size:0.82rem;"> / <?= number_format($coupon['usage_limit_global']) ?></span>
                                            <?php else: ?>
                                                <span style="font-size:0.7rem; color:var(--text-muted); font-weight:400;"> uses</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($usagePct !== null): ?>
                                            <div style="height:4px; background:var(--bg-surface-secondary); border-radius:var(--radius-full); margin-top:5px; overflow:hidden; width:72px;">
                                                <div style="height:100%; width:<?= min($usagePct,100) ?>%; border-radius:var(--radius-full);
                                                    background:<?= $usagePct >= 90 ? 'var(--status-danger)' : ($usagePct >= 60 ? 'var(--status-warning)' : 'var(--brand-blue)') ?>;
                                                    transition:width .5s ease;"></div>
                                            </div>
                                        <?php else: ?>
                                            <div style="font-size:0.68rem; color:var(--text-muted); margin-top:2px;">unlimited</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Validity — safe date rendering -->
                                    <td style="font-size:0.8rem; color:var(--text-secondary);">
                                        <?php if ($startsTs && $startsTs > time()): ?>
                                            <div style="color:var(--text-muted); margin-bottom:2px;">
                                                Starts <strong><?= date('d M Y', $startsTs) ?></strong>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($expiresTs): ?>
                                            <?php
                                            $expColor = 'var(--text-secondary)';
                                            $bold = false;
                                            if ($expiresTs < time()) { $expColor = 'var(--text-muted)'; }
                                            elseif ($daysLeft <= 3) { $expColor = 'var(--status-danger)'; $bold = true; }
                                            elseif ($daysLeft <= 7) { $expColor = 'var(--status-warning)'; $bold = true; }
                                            ?>
                                            <div style="color:<?= $expColor ?>; font-weight:<?= $bold ? '700' : '400' ?>;">
                                                <?= $expiresTs < time() ? 'Expired ' : 'Expires ' ?><?= date('d M Y', $expiresTs) ?>
                                                <?php if ($daysLeft !== null && $daysLeft > 0 && $daysLeft <= 30): ?>
                                                    <span style="font-size:0.7rem;">(<?= $daysLeft ?>d)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <?php if (!$startsTs || $startsTs <= time()): ?>
                                                <span style="color:var(--text-muted);">No expiry</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Status Badge -->
                                    <td>
                                        <span class="badge" style="<?= $badgeStyle ?>"><?= $badgeLabel ?></span>
                                        <?php if (!$coupon['is_public']): ?>
                                            <div style="font-size:0.68rem; color:var(--text-muted); font-weight:600; margin-top:4px;">Private</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions -->
                                    <td onclick="event.stopPropagation()">
                                        <div style="display:flex; gap:5px; justify-content:center; align-items:center;">
                                            <a href="<?= url('admin/coupons/' . $enc) ?>" title="View Details"
                                               style="width:32px; height:32px; border-radius:var(--radius-sm); background:var(--brand-blue-light); color:var(--brand-blue); display:flex; align-items:center; justify-content:center; text-decoration:none; transition:var(--transition);"
                                               onmouseover="this.style.background='var(--brand-blue)'; this.style.color='#fff'"
                                               onmouseout="this.style.background='var(--brand-blue-light)'; this.style.color='var(--brand-blue)'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <a href="<?= url('admin/coupons/' . $enc . '/edit') ?>" title="Edit Coupon"
                                               style="width:32px; height:32px; border-radius:var(--radius-sm); background:var(--brand-purple-light); color:var(--brand-purple); display:flex; align-items:center; justify-content:center; text-decoration:none; transition:var(--transition);"
                                               onmouseover="this.style.background='var(--brand-purple)'; this.style.color='#fff'"
                                               onmouseout="this.style.background='var(--brand-purple-light)'; this.style.color='var(--brand-purple)'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <form method="POST" action="<?= url('admin/coupons/' . $enc . '/status') ?>" style="display:contents;">
                                                <?= csrf_field() ?>
                                                <button type="submit" title="<?= $coupon['is_active'] ? 'Deactivate' : 'Activate' ?>"
                                                        onclick="return confirm('<?= $coupon['is_active'] ? 'Deactivate' : 'Activate' ?> coupon <?= addslashes(e($coupon['code'])) ?>?')"
                                                        style="width:32px; height:32px; border-radius:var(--radius-sm); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:var(--transition);
                                                               background:<?= $coupon['is_active'] ? 'var(--status-danger-bg)' : 'var(--status-success-bg)' ?>;
                                                               color:<?= $coupon['is_active'] ? 'var(--status-danger)' : 'var(--status-success)' ?>;"
                                                        onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                                                    <?php if ($coupon['is_active']): ?>
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                    <?php else: ?>
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============================================================ -->
            <!-- PAGINATION                                                    -->
            <!-- ============================================================ -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:12px;">
                    <div style="font-size:0.8rem; color:var(--text-muted);">
                        Showing <strong style="color:var(--text-primary);"><?= number_format(($pagination['current_page'] - 1) * $pagination['per_page'] + 1) ?></strong>
                        –<strong style="color:var(--text-primary);"><?= number_format(min($pagination['current_page'] * $pagination['per_page'], $pagination['total'])) ?></strong>
                        of <strong style="color:var(--text-primary);"><?= number_format($pagination['total']) ?></strong> coupons
                    </div>
                    <div style="display:flex; gap:4px; align-items:center;">
                        <?php if ($pagination['has_prev']): ?>
                            <a href="<?= url('admin/coupons?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1]))) ?>"
                               style="padding:6px 14px; border-radius:var(--radius-sm); background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-primary); text-decoration:none; font-size:0.82rem; font-weight:600; transition:var(--transition);"
                               onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                               onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">← Prev</a>
                        <?php endif; ?>
                        <?php for ($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['total_pages'], $pagination['current_page'] + 2); $p++): ?>
                            <a href="<?= url('admin/coupons?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>"
                               style="padding:6px 12px; border-radius:var(--radius-sm); font-size:0.82rem; font-weight:700; text-decoration:none; transition:var(--transition);
                                      background:<?= $p === $pagination['current_page'] ? 'var(--brand-blue)' : 'var(--bg-surface)' ?>;
                                      color:<?= $p === $pagination['current_page'] ? '#fff' : 'var(--text-primary)' ?>;
                                      border:1px solid <?= $p === $pagination['current_page'] ? 'var(--brand-blue)' : 'var(--border-color)' ?>;"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($pagination['has_next']): ?>
                            <a href="<?= url('admin/coupons?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1]))) ?>"
                               style="padding:6px 14px; border-radius:var(--radius-sm); background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-primary); text-decoration:none; font-size:0.82rem; font-weight:600; transition:var(--transition);"
                               onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                               onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">Next →</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
