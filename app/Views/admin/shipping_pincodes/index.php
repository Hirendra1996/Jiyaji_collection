<?php
include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$activeTab = $activeTab ?? 'zones';
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
                        <span style="color:var(--text-primary); font-weight:600;">Shipping &amp; Pincodes</span>
                    </div>
                    <h1 class="welcome-title">Shipping Zones &amp; Delivery Logistics</h1>
                    <p class="welcome-subtitle">Configure regional delivery territories, weight-based rate slabs, integrated carrier partners, storewide free shipping thresholds, and test real-time customer rate calculations.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Quick Calculate Button -->
                    <a href="<?= url('admin/shipping-pincodes?tab=calculator') ?>"
                       style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); text-decoration:none; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:var(--transition);"
                       onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                       onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Rate Calculator
                    </a>

                    <!-- Add Rate Slab Trigger -->
                    <button type="button" onclick="openCreateRateModal()"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface-secondary); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); cursor:pointer; transition:var(--transition);"
                            onmouseover="this.style.background='var(--border-color)'"
                            onmouseout="this.style.background='var(--bg-surface-secondary)'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        + Add Rate Slab
                    </button>

                    <!-- Add New Zone Trigger -->
                    <button type="button" onclick="openCreateZoneModal()"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); border:none; cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                            onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.opacity='1'; this.style.transform=''">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><circle cx="12" cy="12" r="10"/></svg>
                        + Create Delivery Zone
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- EXECUTIVE KPIS SUMMARY CARDS (5 CARDS)                       -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
                <!-- KPI 1: Active Delivery Zones -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:var(--brand-blue);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div class="kpi-label">Delivery Zones</div>
                    <div class="kpi-val"><?= $kpis['active_zones'] ?> / <?= $kpis['total_zones'] ?> Active</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Metro, Tier 1/2, ROI, Remote
                    </div>
                </div>

                <!-- KPI 2: Serviceable Mapped PIN Codes -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--status-success);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="kpi-label">Mapped Pincodes</div>
                    <div class="kpi-val"><?= $kpis['total_mapped_pincodes'] ?>+ Pins</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Pan-India express coverage
                    </div>
                </div>

                <!-- KPI 3: Active Rate Slabs -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(139, 92, 246, 0.1); color:var(--brand-purple);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div class="kpi-label">Active Rate Slabs</div>
                    <div class="kpi-val"><?= $kpis['active_rates'] ?> Slabs</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Avg fee: ₹<?= $kpis['avg_shipping_fee'] ?> across tiers
                    </div>
                </div>

                <!-- KPI 4: Global Free Shipping Limit -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/></svg>
                    </div>
                    <div class="kpi-label">Free Shipping Limit</div>
                    <div class="kpi-val">₹<?= number_format($kpis['free_shipping_limit']) ?></div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Orders above unlock free delivery
                    </div>
                </div>

                <!-- KPI 5: Courier Integrations -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(236, 72, 153, 0.1); color:#ec4899;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                    </div>
                    <div class="kpi-label">Courier Partners</div>
                    <div class="kpi-val"><?= $kpis['active_couriers'] ?> / <?= $kpis['total_couriers'] ?> Live</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        BlueDart, Delhivery, Shiprocket
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SUB-TABS NAVIGATION CONTROLS                                 -->
            <!-- ============================================================ -->
            <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:24px; gap:8px; overflow-x:auto;">
                <a href="<?= url('admin/shipping-pincodes?tab=zones') ?>"
                   class="tab-btn <?= $activeTab === 'zones' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'zones' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'zones' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Delivery Zones &amp; Pincodes (<?= count($zones) ?>)
                </a>

                <a href="<?= url('admin/shipping-pincodes?tab=rates') ?>"
                   class="tab-btn <?= $activeTab === 'rates' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'rates' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'rates' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Rate Slabs Matrix (<?= count($rates) ?>)
                </a>

                <a href="<?= url('admin/shipping-pincodes?tab=settings') ?>"
                   class="tab-btn <?= $activeTab === 'settings' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'settings' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'settings' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/></svg>
                    Global Shipping Policy
                </a>

                <a href="<?= url('admin/shipping-pincodes?tab=couriers') ?>"
                   class="tab-btn <?= $activeTab === 'couriers' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'couriers' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'couriers' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Carrier Partners (<?= count($couriers) ?>)
                </a>

                <a href="<?= url('admin/shipping-pincodes?tab=calculator') ?>"
                   class="tab-btn <?= $activeTab === 'calculator' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'calculator' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'calculator' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Live Rate Calculator
                </a>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: DELIVERY ZONES & PINCODES                             -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'zones'): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:20px;">
                    <?php foreach ($zones as $zone): ?>
                        <div class="card-panel" style="display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; border:1px solid <?= !empty($zone['is_active']) ? 'var(--border-color)' : 'rgba(239,68,68,0.2)' ?>;">
                            <div style="position:absolute; top:0; left:0; right:0; height:4px; background:<?= !empty($zone['is_active']) ? 'var(--gradient-primary)' : '#ccc' ?>;"></div>
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                    <div>
                                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                            <span style="font-size:0.75rem; padding:2px 8px; border-radius:4px; font-weight:700; background:rgba(59,130,246,0.12); color:var(--brand-blue); font-family:monospace;">
                                                <?= htmlspecialchars($zone['zone_code']) ?>
                                            </span>
                                            <span class="badge" style="<?= !empty($zone['is_active']) ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(239,68,68,0.15); color:var(--status-danger);' ?> font-weight:700; font-size:0.7rem;">
                                                <?= !empty($zone['is_active']) ? '● ACTIVE' : '○ INACTIVE' ?>
                                            </span>
                                        </div>
                                        <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);"><?= htmlspecialchars($zone['name']) ?></h3>
                                    </div>
                                    <span style="font-size:0.78rem; font-weight:600; color:var(--text-muted); background:var(--bg-surface-secondary); padding:4px 8px; border-radius:4px;">
                                        <?= $zone['rates_count'] ?> Rate Slabs
                                    </span>
                                </div>

                                <p style="font-size:0.82rem; color:var(--text-secondary); line-height:1.4; margin-bottom:14px;">
                                    <?= htmlspecialchars($zone['description'] ?: 'Territory delivery zone.') ?>
                                </p>

                                <!-- Pincodes Snippet Box -->
                                <div style="background:var(--bg-surface-secondary); padding:10px 12px; border-radius:var(--radius-md); font-size:0.78rem; margin-bottom:16px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                        <span style="color:var(--text-muted); font-weight:600;">Mapped PIN Codes:</span>
                                        <span style="font-weight:700; color:var(--brand-blue);">
                                            <?= $zone['pincode_count'] > 0 ? $zone['pincode_count'] . ' Specific Pins' : 'All Other India PINs (Default)' ?>
                                        </span>
                                    </div>
                                    <div style="font-family:monospace; color:var(--text-secondary); word-break:break-all; max-height:45px; overflow-y:auto; line-height:1.4;">
                                        <?= htmlspecialchars(substr($zone['pincodes'] ?: 'Default fallback for any unmapped postal code across India', 0, 140)) ?><?= strlen($zone['pincodes']) > 140 ? '...' : '' ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Actions -->
                            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:12px;">
                                <div style="display:flex; gap:8px;">
                                    <button type="button" onclick='openEditZoneModal(<?= json_encode($zone) ?>)'
                                            style="padding:6px 12px; font-size:0.8rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer; color:var(--text-primary);">
                                        Edit Zone
                                    </button>

                                    <!-- Quick Toggle Form -->
                                    <form method="POST" action="<?= url('admin/shipping-pincodes/zone/toggle') ?>" style="margin:0; display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $zone['id'] ?>">
                                        <input type="hidden" name="is_active" value="<?= !empty($zone['is_active']) ? '0' : '1' ?>">
                                        <button type="submit"
                                                style="padding:6px 12px; font-size:0.8rem; font-weight:600; background:none; border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer; color:<?= !empty($zone['is_active']) ? 'var(--status-danger)' : 'var(--status-success)' ?>;">
                                            <?= !empty($zone['is_active']) ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </div>

                                <?php if ($zone['zone_code'] !== 'ROI'): ?>
                                    <form method="POST" action="<?= url('admin/shipping-pincodes/zone/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this delivery zone and its rate slabs?');" style="margin:0;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $zone['id'] ?>">
                                        <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;" title="Delete Zone">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 2: SHIPPING RATE MATRIX                                  -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'rates'): ?>
                <div class="card-panel">
                    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                        <div>
                            <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                Shipping Rates &amp; Weight Slabs Matrix
                            </h2>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Define pricing rules, dead weight brackets, free shipping qualification, and transit days per zone.</p>
                        </div>
                        <button type="button" onclick="openCreateRateModal()"
                                style="padding:8px 16px; font-size:0.85rem; font-weight:700; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer;">
                            + Add Rate Slab
                        </button>
                    </div>

                    <div style="overflow-x:auto;">
                        <table class="orders-table" style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                            <thead>
                                <tr style="border-bottom:1px solid var(--border-color); text-align:left; color:var(--text-muted);">
                                    <th style="padding:10px 14px;">Zone</th>
                                    <th style="padding:10px 14px;">Service Method</th>
                                    <th style="padding:10px 14px;">Weight Bracket</th>
                                    <th style="padding:10px 14px;">Flat Fee</th>
                                    <th style="padding:10px 14px;">Free Shipping Above</th>
                                    <th style="padding:10px 14px;">Estimated Days</th>
                                    <th style="padding:10px 14px;">Status</th>
                                    <th style="padding:10px 14px; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rates)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding:36px; color:var(--text-muted);">
                                            No rate slabs configured yet. Click "+ Add Rate Slab" to define delivery pricing.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rates as $rate): ?>
                                        <tr style="border-bottom:1px solid var(--border-color-light);">
                                            <!-- Zone -->
                                            <td style="padding:12px 14px; font-weight:700;">
                                                <span style="font-size:0.75rem; padding:2px 6px; border-radius:4px; font-weight:700; background:rgba(59,130,246,0.12); color:var(--brand-blue); font-family:monospace; margin-right:6px;">
                                                    <?= htmlspecialchars($rate['zone_code']) ?>
                                                </span>
                                                <?= htmlspecialchars($rate['zone_name']) ?>
                                            </td>

                                            <!-- Method -->
                                            <td style="padding:12px 14px;">
                                                <?php
                                                $mBadge = 'background:rgba(59,130,246,0.12); color:var(--brand-blue);';
                                                if ($rate['method'] === 'express') {
                                                    $mBadge = 'background:rgba(245,158,11,0.15); color:#d97706;';
                                                } elseif ($rate['method'] === 'free') {
                                                    $mBadge = 'background:rgba(16,185,129,0.15); color:var(--status-success);';
                                                }
                                                ?>
                                                <span style="font-size:0.75rem; padding:3px 8px; border-radius:12px; font-weight:700; text-transform:uppercase; <?= $mBadge ?>">
                                                    <?= htmlspecialchars($rate['method']) ?>
                                                </span>
                                                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
                                                    <?= htmlspecialchars($rate['title']) ?>
                                                </div>
                                            </td>

                                            <!-- Weight -->
                                            <td style="padding:12px 14px; font-family:monospace;">
                                                <?= $rate['weight_from_g'] ?>g – <?= $rate['weight_to_g'] > 0 ? $rate['weight_to_g'] . 'g' : 'Any' ?>
                                            </td>

                                            <!-- Flat Fee -->
                                            <td style="padding:12px 14px; font-weight:700; color:var(--text-primary);">
                                                <?= $rate['flat_rate'] > 0 ? '₹' . number_format($rate['flat_rate']) : '<span style="color:var(--status-success);">FREE (₹0)</span>' ?>
                                            </td>

                                            <!-- Free Above -->
                                            <td style="padding:12px 14px; color:var(--text-secondary);">
                                                <?= $rate['free_above_order_value'] !== null ? 'Orders ≥ ₹' . number_format($rate['free_above_order_value']) : '<span style="color:var(--text-muted);">&mdash;</span>' ?>
                                            </td>

                                            <!-- Estimated Days -->
                                            <td style="padding:12px 14px; color:var(--text-muted); font-size:0.8rem;">
                                                <?= htmlspecialchars($rate['estimated_days']) ?>
                                            </td>

                                            <!-- Status -->
                                            <td style="padding:12px 14px;">
                                                <form method="POST" action="<?= url('admin/shipping-pincodes/rate/toggle') ?>" style="margin:0;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $rate['id'] ?>">
                                                    <input type="hidden" name="is_active" value="<?= !empty($rate['is_active']) ? '0' : '1' ?>">
                                                    <button type="submit" style="background:none; border:none; cursor:pointer; padding:0;">
                                                        <span class="badge" style="<?= !empty($rate['is_active']) ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(239,68,68,0.15); color:var(--status-danger);' ?> font-weight:700; font-size:0.72rem;">
                                                            <?= !empty($rate['is_active']) ? 'Active' : 'Disabled' ?>
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>

                                            <!-- Actions -->
                                            <td style="padding:12px 14px; text-align:right;">
                                                <button type="button" onclick='openEditRateModal(<?= json_encode($rate) ?>)'
                                                        style="padding:4px 10px; font-size:0.75rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer; color:var(--text-primary); margin-right:4px;">
                                                    Edit
                                                </button>
                                                <form method="POST" action="<?= url('admin/shipping-pincodes/rate/delete') ?>" onsubmit="return confirm('Delete this rate slab?');" style="display:inline; margin:0;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $rate['id'] ?>">
                                                    <button type="submit" style="background:none; border:none; color:var(--status-danger); cursor:pointer; font-size:0.75rem; font-weight:600;">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 3: GLOBAL SHIPPING POLICY                                -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'settings'): ?>
                <div class="card-panel" style="max-width:850px; margin:0 auto;">
                    <div class="panel-header" style="margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
                        <div>
                            <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2.2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/></svg>
                                Storewide Shipping &amp; Free Delivery Policy
                            </h2>
                            <p style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">Manage universal free delivery qualifying basket thresholds, fallback flat fees, and express courier options.</p>
                        </div>
                    </div>

                    <form method="POST" action="<?= url('admin/shipping-pincodes/settings') ?>">
                        <?= csrf_field() ?>

                        <!-- Free Shipping Threshold -->
                        <div style="background:var(--bg-surface-secondary); padding:16px 20px; border-radius:var(--radius-md); border:1px solid var(--border-color); margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                                <div>
                                    <strong style="color:var(--text-primary); font-size:0.95rem; display:block;">Storewide Free Shipping Threshold (₹)</strong>
                                    <span style="font-size:0.8rem; color:var(--text-muted);">Orders meeting or exceeding this basket value automatically unlock complimentary luxury delivery.</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:1.1rem; font-weight:700; color:var(--text-primary);">₹</span>
                                    <input type="number" step="1" name="free_shipping_threshold" class="form-input"
                                           value="<?= htmlspecialchars((string)$settings['free_shipping_threshold']) ?>"
                                           style="width:140px; font-size:1.1rem; font-weight:700; text-align:right;" required>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback Flat Rates -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label" for="default_shipping_fee" style="font-weight:700;">Default Standard Delivery Fee (₹)</label>
                                <input type="number" step="0.01" id="default_shipping_fee" name="default_shipping_fee" class="form-input"
                                       value="<?= htmlspecialchars((string)$settings['default_shipping_fee']) ?>" required>
                                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Charged when an order is below free shipping and no custom rate slab applies.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="default_express_fee" style="font-weight:700;">Default Express Air Priority Fee (₹)</label>
                                <input type="number" step="0.01" id="default_express_fee" name="default_express_fee" class="form-input"
                                       value="<?= htmlspecialchars((string)$settings['default_express_fee']) ?>" required>
                                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Flat expedited air courier charge for 24-48 hour delivery.</span>
                            </div>
                        </div>

                        <!-- Service Toggles -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                            <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; background:var(--bg-surface);">
                                <input type="checkbox" name="express_shipping_enabled" value="1" <?= !empty($settings['express_shipping_enabled']) ? 'checked' : '' ?> style="margin-top:3px;">
                                <div>
                                    <span style="font-weight:700; font-size:0.9rem; color:var(--text-primary); display:block;">Enable Express Air Priority</span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">Offer fast 24-48h air delivery tier alongside standard surface cargo at checkout.</span>
                                </div>
                            </label>

                            <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; background:var(--bg-surface);">
                                <input type="checkbox" name="same_day_delivery_enabled" value="1" <?= !empty($settings['same_day_delivery_enabled']) ? 'checked' : '' ?> style="margin-top:3px;">
                                <div>
                                    <span style="font-weight:700; font-size:0.9rem; color:var(--text-primary); display:block;">Same-Day Regional Dispatch</span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">Enable dedicated local concierge courier delivery for orders placed before 12 PM.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Luxury Gift Wrap Fee -->
                        <div class="form-group" style="margin-bottom:24px;">
                            <label class="form-label" for="luxury_packaging_fee" style="font-weight:700;">Luxury Couture Gift Packaging &amp; Garment Box Fee (₹)</label>
                            <input type="number" step="0.01" id="luxury_packaging_fee" name="luxury_packaging_fee" class="form-input"
                                   value="<?= htmlspecialchars((string)$settings['luxury_packaging_fee']) ?>" style="max-width:240px;">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Set to 0 for complimentary packaging.</span>
                        </div>

                        <div style="display:flex; justify-content:flex-end; pt-3; border-top:1px solid var(--border-color); padding-top:16px;">
                            <button type="submit"
                                    style="padding:10px 24px; font-size:0.9rem; font-weight:700; background:var(--gradient-primary); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; box-shadow:var(--shadow-glow-blue);">
                                Save Shipping Policy
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 4: CARRIER PARTNERS                                      -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'couriers'): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px;">
                    <?php foreach ($couriers as $cpKey => $cp): ?>
                        <div class="card-panel" style="display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; border:1px solid <?= !empty($cp['is_active']) ? 'var(--border-color)' : 'rgba(239,68,68,0.2)' ?>;">
                            <div style="position:absolute; top:0; left:0; right:0; height:4px; background:<?= htmlspecialchars($cp['badge_color'] ?? 'var(--brand-blue)') ?>;"></div>
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:42px; height:42px; border-radius:10px; background:rgba(59,130,246,0.1); display:flex; align-items:center; justify-content:center; color:<?= htmlspecialchars($cp['badge_color'] ?? 'var(--brand-blue)') ?>;">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                        </div>
                                        <div>
                                            <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);"><?= htmlspecialchars($cp['name']) ?></h3>
                                            <span style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($cp['type']) ?></span>
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <span class="badge" style="<?= !empty($cp['is_active']) ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(239,68,68,0.15); color:var(--status-danger);' ?> font-weight:700; font-size:0.7rem;">
                                            <?= !empty($cp['is_active']) ? 'CONNECTED' : 'DISABLED' ?>
                                        </span>
                                        <?php if (!empty($cp['is_default'])): ?>
                                            <div style="font-size:0.65rem; color:var(--brand-blue); font-weight:700; margin-top:3px;">★ DEFAULT CARRIER</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div style="background:var(--bg-surface-secondary); padding:10px 12px; border-radius:var(--radius-md); font-size:0.8rem; margin-bottom:16px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                        <span style="color:var(--text-muted);">Tracking Template:</span>
                                    </div>
                                    <div style="font-family:monospace; font-size:0.74rem; color:var(--text-primary); word-break:break-all;">
                                        <?= htmlspecialchars($cp['tracking_url']) ?>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; justify-content:flex-end; pt-3; border-top:1px solid var(--border-color); padding-top:12px;">
                                <button type="button" onclick='openEditCourierModal(<?= json_encode($cpKey) ?>, <?= json_encode($cp) ?>)'
                                        style="padding:6px 14px; font-size:0.8rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">
                                    Configure Courier
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 5: LIVE PINCODE & RATE CALCULATOR                        -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'calculator'): ?>
                <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:24px; align-items:start;">
                    <!-- Calculator Inputs Panel -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom:16px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
                            <div>
                                <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    Live Rate &amp; Pincode Calculator
                                </h2>
                                <p style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Simulate order checkout charges, eligible tiers, and transit delivery days for any Indian destination.</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:14px;">
                            <label class="form-label" for="calc_pincode" style="font-weight:700;">Customer Indian Postal Code (6 Digits) <span style="color:red;">*</span></label>
                            <input type="text" id="calc_pincode" class="form-input" placeholder="e.g. 400001, 110001, 302001" maxlength="6" value="400001" style="font-family:monospace; font-size:1.1rem; letter-spacing:1.5px; font-weight:700;">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Try testing: 400001 (Metro), 302001 (Tier 1/2), 795001 (Remote), or unmapped PIN</span>
                        </div>

                        <div class="form-group" style="margin-bottom:14px;">
                            <label class="form-label" for="calc_subtotal" style="font-weight:700;">Cart Order Subtotal (₹)</label>
                            <input type="number" id="calc_subtotal" class="form-input" placeholder="e.g. 3500" value="3500" style="font-size:1.05rem; font-weight:700;">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Free shipping unlocks at ₹<?= number_format($settings['free_shipping_threshold']) ?></span>
                        </div>

                        <div class="form-group" style="margin-bottom:20px;">
                            <label class="form-label" for="calc_weight" style="font-weight:700;">Dead Weight Bracket (Grams)</label>
                            <input type="number" id="calc_weight" class="form-input" placeholder="e.g. 800" value="800">
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Typical Sherwani: 1800g | Kurta: 450g | Tuxedo: 2200g</span>
                        </div>

                        <button type="button" onclick="runLiveCalculation()" id="btnCalculate"
                                style="width:100%; padding:12px; font-size:0.92rem; font-weight:700; background:var(--gradient-primary); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                                onmouseover="this.style.opacity='0.92'"
                                onmouseout="this.style.opacity='1'">
                            Calculate Delivery Charges
                        </button>
                    </div>

                    <!-- Calculator Results Display Card -->
                    <div class="card-panel" id="calcResultContainer" style="border:1px solid var(--border-color); min-height:350px;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
                            <div style="width:36px; height:36px; border-radius:8px; background:rgba(16,185,129,0.12); display:flex; align-items:center; justify-content:center; color:var(--status-success);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <div>
                                <h3 style="margin:0; font-size:1.02rem; font-weight:700; color:var(--text-primary);">Logistics Quotation</h3>
                                <span style="font-size:0.75rem; color:var(--text-muted);" id="calcResultPincode">PIN 400001 Breakdown</span>
                            </div>
                        </div>

                        <!-- Dynamic Content Placeholder -->
                        <div id="calcLoading" style="display:none; text-align:center; padding:40px; color:var(--text-muted);">
                            Calculating shipping methods...
                        </div>

                        <div id="calcBody">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: CREATE / EDIT DELIVERY ZONE                           -->
<!-- ============================================================ -->
<div id="zoneModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); width:90%; max-width:600px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);" id="zoneModalTitle">Create Delivery Zone</h3>
            <button type="button" onclick="closeZoneModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>

        <form method="POST" action="<?= url('admin/shipping-pincodes/zone/save') ?>" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="zone_form_id" value="0">

            <div style="padding:20px; overflow-y:auto; flex:1;">
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:14px; margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label" for="zone_name" style="font-weight:700;">Zone Name <span style="color:red;">*</span></label>
                        <input type="text" id="zone_name" name="name" class="form-input" placeholder="e.g. Metro Express Hubs" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="zone_code" style="font-weight:700;">Zone Code</label>
                        <input type="text" id="zone_code" name="zone_code" class="form-input" placeholder="e.g. METRO" style="font-family:monospace; text-transform:uppercase;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" for="zone_description" style="font-weight:700;">Description</label>
                    <input type="text" id="zone_description" name="description" class="form-input" placeholder="Brief territory explanation">
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label class="form-label" for="zone_pincodes" style="font-weight:700; margin:0;">Mapped 6-Digit PIN Codes</label>
                        <div style="display:flex; gap:6px;">
                            <button type="button" onclick="cleanZonePincodes()" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:4px; cursor:pointer;">
                                Clean &amp; Deduplicate
                            </button>
                            <button type="button" onclick="addMetroPinsToZone()" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:4px; cursor:pointer;">
                                + Add Metro Hubs
                            </button>
                        </div>
                    </div>
                    <textarea id="zone_pincodes" name="pincodes" class="form-input" rows="5"
                              placeholder="Enter 6-digit postal codes separated by comma or space (Leave empty if this is a catch-all Rest of India zone)"
                              style="font-family:monospace; font-size:0.85rem; line-height:1.5;"></textarea>
                    <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;" id="zonePinCountFeedback">
                        Pincodes in this list route to this zone's rate slabs.
                    </span>
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="zone_is_active" value="1" checked>
                        <span style="font-weight:600; font-size:0.85rem; color:var(--text-primary);">Zone is Active</span>
                    </label>
                </div>
            </div>

            <div style="padding:14px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:10px; background:var(--bg-surface-secondary);">
                <button type="button" onclick="closeZoneModal()" style="padding:8px 16px; font-size:0.85rem; font-weight:600; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding:8px 20px; font-size:0.85rem; font-weight:700; background:var(--gradient-primary); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer;">
                    Save Delivery Zone
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: CREATE / EDIT RATE SLAB                               -->
<!-- ============================================================ -->
<div id="rateModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); width:90%; max-width:560px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);" id="rateModalTitle">Add Shipping Rate Slab</h3>
            <button type="button" onclick="closeRateModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>

        <form method="POST" action="<?= url('admin/shipping-pincodes/rate/save') ?>" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="rate_form_id" value="0">

            <div style="padding:20px; overflow-y:auto; flex:1;">
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" for="rate_zone_id" style="font-weight:700;">Delivery Zone <span style="color:red;">*</span></label>
                    <select name="zone_id" id="rate_zone_id" class="form-input" required>
                        <?php foreach ($zones as $z): ?>
                            <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['name']) ?> (<?= htmlspecialchars($z['zone_code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label" for="rate_method" style="font-weight:700;">Service Tier</label>
                        <select name="method" id="rate_method" class="form-input" required>
                            <option value="standard">Standard Surface</option>
                            <option value="express">Express Air Priority</option>
                            <option value="free">Free Luxury Delivery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rate_title" style="font-weight:700;">Customer Display Title</label>
                        <input type="text" id="rate_title" name="title" class="form-input" placeholder="e.g. Express Air Priority">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label" for="rate_weight_from" style="font-weight:700;">Weight From (Grams)</label>
                        <input type="number" id="rate_weight_from" name="weight_from_g" class="form-input" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rate_weight_to" style="font-weight:700;">Weight To (Grams)</label>
                        <input type="number" id="rate_weight_to" name="weight_to_g" class="form-input" value="5000" min="0">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label" for="rate_flat_rate" style="font-weight:700;">Flat Shipping Fee (₹)</label>
                        <input type="number" step="0.01" id="rate_flat_rate" name="flat_rate" class="form-input" value="149.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rate_free_above" style="font-weight:700;">Free If Order ≥ (₹)</label>
                        <input type="number" step="0.01" id="rate_free_above" name="free_above_order_value" class="form-input" placeholder="e.g. 2999">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" for="rate_estimated_days" style="font-weight:700;">Estimated Delivery Timeframe</label>
                    <input type="text" id="rate_estimated_days" name="estimated_days" class="form-input" placeholder="e.g. 2-3 Business Days">
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="rate_is_active" value="1" checked>
                        <span style="font-weight:600; font-size:0.85rem; color:var(--text-primary);">Rate Slab is Active</span>
                    </label>
                </div>
            </div>

            <div style="padding:14px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:10px; background:var(--bg-surface-secondary);">
                <button type="button" onclick="closeRateModal()" style="padding:8px 16px; font-size:0.85rem; font-weight:600; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding:8px 20px; font-size:0.85rem; font-weight:700; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer;">
                    Save Rate Slab
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: EDIT COURIER PARTNER                                  -->
<!-- ============================================================ -->
<div id="courierModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); width:90%; max-width:520px; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);" id="courierModalTitle">Configure Carrier Partner</h3>
            <button type="button" onclick="closeCourierModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>

        <form method="POST" action="<?= url('admin/shipping-pincodes/courier/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="partner_key" id="courier_partner_key" value="">

            <div style="padding:20px;">
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" for="courier_name" style="font-weight:700;">Carrier Name</label>
                    <input type="text" id="courier_name" name="name" class="form-input" required>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" for="courier_tracking_url" style="font-weight:700;">Tracking URL Template</label>
                    <input type="text" id="courier_tracking_url" name="tracking_url" class="form-input" placeholder="https://track.carrier.com?awb={AWB}" required style="font-family:monospace; font-size:0.85rem;">
                    <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Use <code>{AWB}</code> as the placeholder for tracking codes.</span>
                </div>

                <div style="display:flex; gap:16px; margin-top:16px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="courier_is_active" value="1">
                        <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">Active Integration</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_default" id="courier_is_default" value="1">
                        <span style="font-size:0.85rem; font-weight:600; color:var(--brand-blue);">Set as Default Carrier</span>
                    </label>
                </div>
            </div>

            <div style="padding:14px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:10px; background:var(--bg-surface-secondary);">
                <button type="button" onclick="closeCourierModal()" style="padding:8px 16px; font-size:0.85rem; font-weight:600; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 20px; font-size:0.85rem; font-weight:700; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer;">Save Carrier</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT INTERACTIONS & AJAX CALCULATOR                     -->
<!-- ============================================================ -->
<script>
// 1. Zone Modal Handlers
function openCreateZoneModal() {
    document.getElementById('zoneModalTitle').innerText = 'Create Delivery Zone';
    document.getElementById('zone_form_id').value = '0';
    document.getElementById('zone_name').value = '';
    document.getElementById('zone_code').value = '';
    document.getElementById('zone_description').value = '';
    document.getElementById('zone_pincodes').value = '';
    document.getElementById('zone_is_active').checked = true;
    document.getElementById('zoneModal').style.display = 'flex';
}

function openEditZoneModal(zone) {
    document.getElementById('zoneModalTitle').innerText = 'Edit Zone: ' + zone.name;
    document.getElementById('zone_form_id').value = zone.id;
    document.getElementById('zone_name').value = zone.name;
    document.getElementById('zone_code').value = zone.zone_code;
    document.getElementById('zone_description').value = zone.description;
    document.getElementById('zone_pincodes').value = zone.pincodes;
    document.getElementById('zone_is_active').checked = Boolean(zone.is_active);
    document.getElementById('zoneModal').style.display = 'flex';
}

function closeZoneModal() {
    document.getElementById('zoneModal').style.display = 'none';
}

function cleanZonePincodes() {
    const area = document.getElementById('zone_pincodes');
    const matches = area.value.match(/\b\d{6}\b/g) || [];
    const unique = [...new Set(matches)].sort();
    area.value = unique.join(', ');
    const countBox = document.getElementById('zonePinCountFeedback');
    countBox.innerText = '✓ ' + unique.length + ' unique 6-digit PIN codes loaded.';
    countBox.style.color = 'var(--status-success)';
}

function addMetroPinsToZone() {
    const metros = ['400001','400050','110001','110016','560001','500001','700001','600001','411001','380001'];
    const area = document.getElementById('zone_pincodes');
    const existing = area.value.match(/\b\d{6}\b/g) || [];
    const combined = [...new Set([...existing, ...metros])].sort();
    area.value = combined.join(', ');
    const countBox = document.getElementById('zonePinCountFeedback');
    countBox.innerText = '✓ Added top metro capitals: ' + combined.length + ' total PIN codes.';
    countBox.style.color = 'var(--status-success)';
}

// 2. Rate Modal Handlers
function openCreateRateModal() {
    document.getElementById('rateModalTitle').innerText = 'Add Shipping Rate Slab';
    document.getElementById('rate_form_id').value = '0';
    document.getElementById('rate_title').value = '';
    document.getElementById('rate_weight_from').value = '0';
    document.getElementById('rate_weight_to').value = '5000';
    document.getElementById('rate_flat_rate').value = '149.00';
    document.getElementById('rate_free_above').value = '2999';
    document.getElementById('rate_estimated_days').value = '3-5 Business Days';
    document.getElementById('rate_is_active').checked = true;
    document.getElementById('rateModal').style.display = 'flex';
}

function openEditRateModal(rate) {
    document.getElementById('rateModalTitle').innerText = 'Edit Rate Slab';
    document.getElementById('rate_form_id').value = rate.id;
    document.getElementById('rate_zone_id').value = rate.zone_id;
    document.getElementById('rate_method').value = rate.method;
    document.getElementById('rate_title').value = rate.title;
    document.getElementById('rate_weight_from').value = rate.weight_from_g;
    document.getElementById('rate_weight_to').value = rate.weight_to_g;
    document.getElementById('rate_flat_rate').value = rate.flat_rate;
    document.getElementById('rate_free_above').value = rate.free_above_order_value !== null ? rate.free_above_order_value : '';
    document.getElementById('rate_estimated_days').value = rate.estimated_days;
    document.getElementById('rate_is_active').checked = Boolean(rate.is_active);
    document.getElementById('rateModal').style.display = 'flex';
}

function closeRateModal() {
    document.getElementById('rateModal').style.display = 'none';
}

// 3. Courier Modal Handlers
function openEditCourierModal(partnerKey, cp) {
    document.getElementById('courierModalTitle').innerText = 'Configure ' + cp.name;
    document.getElementById('courier_partner_key').value = partnerKey;
    document.getElementById('courier_name').value = cp.name;
    document.getElementById('courier_tracking_url').value = cp.tracking_url;
    document.getElementById('courier_is_active').checked = Boolean(cp.is_active);
    document.getElementById('courier_is_default').checked = Boolean(cp.is_default);
    document.getElementById('courierModal').style.display = 'flex';
}

function closeCourierModal() {
    document.getElementById('courierModal').style.display = 'none';
}

// 4. Live Pincode Calculator AJAX
function runLiveCalculation() {
    const pincode = document.getElementById('calc_pincode').value.trim();
    const subtotal = parseFloat(document.getElementById('calc_subtotal').value) || 0;
    const weight = parseInt(document.getElementById('calc_weight').value) || 500;

    const btn = document.getElementById('btnCalculate');
    const bodyBox = document.getElementById('calcBody');
    const loading = document.getElementById('calcLoading');
    const headerPincode = document.getElementById('calcResultPincode');

    if (pincode.length !== 6 || !/^\d{6}$/.test(pincode)) {
        alert('Please enter a valid 6-digit Indian PIN code.');
        return;
    }

    btn.disabled = true;
    loading.style.display = 'block';
    bodyBox.style.display = 'none';

    const formData = new FormData();
    formData.append('pincode', pincode);
    formData.append('subtotal', subtotal);
    formData.append('weight_grams', weight);

    fetch('<?= url("admin/shipping-pincodes/calculate") ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        loading.style.display = 'none';
        bodyBox.style.display = 'block';

        if (!data.valid) {
            bodyBox.innerHTML = '<div style="color:var(--status-danger); padding:20px; font-weight:700;">' + data.message + '</div>';
            return;
        }

        headerPincode.innerText = 'PIN ' + data.pincode + ' Logistics Breakdown';

        let methodsHtml = '';
        if (data.methods && data.methods.length > 0) {
            data.methods.forEach(m => {
                const isFree = m.is_free;
                const feeText = isFree ? '<span style="color:var(--status-success); font-weight:800;">FREE</span>' : '<span style="font-weight:800; color:var(--text-primary);">₹' + Math.round(m.fee) + '</span>';
                methodsHtml += `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:var(--bg-surface-secondary); border-radius:var(--radius-md); border:1px solid var(--border-color); margin-bottom:10px;">
                        <div>
                            <div style="font-weight:700; color:var(--text-primary); font-size:0.92rem;">${m.title}</div>
                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">Transit Time: <strong>${m.estimated_days}</strong></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:1.1rem;">${feeText}</div>
                            ${isFree && m.base_fee > 0 ? '<div style="font-size:0.7rem; color:var(--text-muted); text-decoration:line-through;">₹' + Math.round(m.base_fee) + '</div>' : ''}
                        </div>
                    </div>
                `;
            });
        }

        const cod = data.cod;
        const codBadge = cod && cod.eligible 
            ? '<span style="color:var(--status-success); font-weight:700;">● Eligible for Doorstep COD</span>'
            : '<span style="color:var(--status-danger); font-weight:700;">○ Prepaid Only (' + (cod ? cod.reason : 'Not Available') + ')</span>';

        bodyBox.innerHTML = `
            <div style="margin-bottom:14px; background:rgba(59,130,246,0.06); padding:12px 14px; border-radius:var(--radius-md); border:1px solid rgba(59,130,246,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Matched Territory:</span>
                    <span style="font-size:0.75rem; padding:2px 8px; border-radius:4px; font-weight:700; background:rgba(59,130,246,0.15); color:var(--brand-blue); font-family:monospace;">${data.zone.zone_code}</span>
                </div>
                <div style="font-weight:800; font-size:1.05rem; color:var(--text-primary); margin-top:4px;">${data.zone.name}</div>
                <div style="font-size:0.78rem; color:var(--text-secondary); margin-top:2px;">${data.zone.description}</div>
            </div>

            ${data.threshold_message ? `
                <div style="padding:10px 14px; border-radius:var(--radius-md); background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.25); color:#d97706; font-size:0.82rem; font-weight:700; margin-bottom:14px;">
                    ⚡ ${data.threshold_message}
                </div>
            ` : ''}

            <div style="margin-bottom:12px; font-weight:700; font-size:0.85rem; color:var(--text-muted); text-transform:uppercase;">
                Available Delivery Services
            </div>
            ${methodsHtml}

            <div style="margin-top:16px; padding:12px 14px; border-radius:var(--radius-md); background:var(--bg-surface); border:1px solid var(--border-color); font-size:0.82rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:600; color:var(--text-muted);">Payment Mode Serviceability:</span>
                    ${codBadge}
                </div>
            </div>
        `;
    })
    .catch(err => {
        btn.disabled = false;
        loading.style.display = 'none';
        bodyBox.style.display = 'block';
        bodyBox.innerHTML = '<div style="color:var(--status-danger); padding:20px;">Error calculating shipping: ' + err.message + '</div>';
    });
}

// Run initial calculation on page load if calculator tab is open
window.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('calc_pincode')) {
        runLiveCalculation();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
