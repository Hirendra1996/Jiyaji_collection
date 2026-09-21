<?php
include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$activeTab = $activeTab ?? 'overview';

$rzp     = $gateways['razorpay'] ?? [];
$rzpCfg  = $rzp['config'] ?? [];
$cod     = $gateways['cod'] ?? [];
$codCfg  = $cod['config'] ?? [];
$bank    = $gateways['bank_transfer'] ?? [];
$bankCfg = $bank['config'] ?? [];

$webhookUrl = url('api/payment/webhook');
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
                        <span style="color:var(--text-primary); font-weight:600;">Payment Gateways</span>
                    </div>
                    <h1 class="welcome-title">Payment Gateways &amp; Settlement System</h1>
                    <p class="welcome-subtitle">Configure Razorpay API credentials, anti-fraud Cash on Delivery thresholds, serviceable postal codes whitelist, and VIP Concierge bank wire transfers.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Copy Webhook URL Button -->
                    <button type="button" onclick="copyWebhookUrl()"
                            id="copyWebhookBtn"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:var(--transition);"
                            onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                            onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'"
                            title="Copy Razorpay Webhook URL">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span id="webhookBtnText">Webhook Endpoint</span>
                    </button>

                    <!-- Test Razorpay API Connection -->
                    <button type="button" onclick="triggerTestConnection()"
                            id="testConnBtn"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); border:none; cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                            onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.opacity='1'; this.style.transform=''">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        Test Gateway API
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- EXECUTIVE KPIS SUMMARY CARDS                                 -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px;">
                <!-- KPI 1: Active Gateways -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--status-success);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    </div>
                    <div class="kpi-label">Active Gateways</div>
                    <div class="kpi-val"><?= $kpis['active_gateways'] ?> / <?= $kpis['total_gateways'] ?> Online</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Razorpay: <?= !empty($rzp['is_enabled']) ? '<span style="color:var(--status-success); font-weight:700;">Active</span>' : '<span style="color:var(--status-danger);">Disabled</span>' ?> &bull; COD: <?= !empty($cod['is_enabled']) ? '<span style="color:var(--status-success); font-weight:700;">Active</span>' : '<span style="color:var(--status-danger);">Disabled</span>' ?>
                    </div>
                </div>

                <!-- KPI 2: Razorpay Environment -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:var(--brand-blue);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </div>
                    <div class="kpi-label">Razorpay Checkout</div>
                    <div class="kpi-val" style="display:flex; align-items:center; gap:8px;">
                        <span><?= strtoupper($kpis['razorpay_mode']) ?></span>
                        <span style="font-size:0.65rem; padding:2px 8px; border-radius:12px; font-weight:700; text-transform:uppercase; <?= $kpis['razorpay_mode'] === 'live' ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(245,158,11,0.15); color:#d97706;' ?>">
                            <?= $kpis['razorpay_mode'] === 'live' ? 'Production' : 'Sandbox' ?>
                        </span>
                    </div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Key: <?= htmlspecialchars(substr($rzpCfg['key_id'] ?? 'rzp_test_...', 0, 16)) ?>...
                    </div>
                </div>

                <!-- KPI 3: Success Rate & Captured Volume -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(139, 92, 246, 0.1); color:var(--brand-purple);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                    <div class="kpi-label">Settlement Success Rate</div>
                    <div class="kpi-val"><?= $kpis['success_rate'] ?>%</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        <?= $kpis['success_tx'] ?> of <?= $kpis['total_tx'] ?> tx &bull; ₹<?= number_format($kpis['success_amount']) ?> captured
                    </div>
                </div>

                <!-- KPI 4: Orders Payment Ratio -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(236, 72, 153, 0.1); color:#ec4899;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    </div>
                    <div class="kpi-label">Payment Channel Split</div>
                    <div class="kpi-val" style="font-size:1.3rem;">
                        <span style="color:var(--brand-blue); font-weight:700;"><?= $kpis['online_orders_pct'] ?>%</span> Online / 
                        <span style="color:#f59e0b; font-weight:700;"><?= $kpis['cod_orders_pct'] ?>%</span> COD
                    </div>
                    <div style="margin-top:8px; height:6px; width:100%; background:rgba(245,158,11,0.25); border-radius:3px; overflow:hidden; display:flex;">
                        <div style="width:<?= $kpis['online_orders_pct'] ?>%; background:var(--brand-blue); height:100%;"></div>
                        <div style="width:<?= $kpis['cod_orders_pct'] ?>%; background:#f59e0b; height:100%;"></div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TAB NAVIGATION CONTROLS                                      -->
            <!-- ============================================================ -->
            <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:24px; gap:8px; overflow-x:auto;">
                <a href="<?= url('admin/payment-gateways?tab=overview') ?>"
                   class="tab-btn <?= $activeTab === 'overview' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'overview' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'overview' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Gateway Overview
                </a>

                <a href="<?= url('admin/payment-gateways?tab=razorpay') ?>"
                   class="tab-btn <?= $activeTab === 'razorpay' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'razorpay' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'razorpay' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    Razorpay Checkout
                </a>

                <a href="<?= url('admin/payment-gateways?tab=cod') ?>"
                   class="tab-btn <?= $activeTab === 'cod' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'cod' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'cod' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    COD Anti-Fraud Rules
                </a>

                <a href="<?= url('admin/payment-gateways?tab=bank_transfer') ?>"
                   class="tab-btn <?= $activeTab === 'bank_transfer' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'bank_transfer' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'bank_transfer' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="21" x2="21" y2="21"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="5 6 12 3 19 6"/><line x1="4" y1="10" x2="4" y2="21"/><line x1="20" y1="10" x2="20" y2="21"/><line x1="8" y1="14" x2="8" y2="17"/><line x1="12" y1="14" x2="12" y2="17"/><line x1="16" y1="14" x2="16" y2="17"/></svg>
                    VIP Bank Wire
                </a>

                <a href="<?= url('admin/payment-gateways?tab=transactions') ?>"
                   class="tab-btn <?= $activeTab === 'transactions' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'transactions' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'transactions' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Transactions Ledger (<?= $pagination['total'] ?>)
                </a>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: OVERVIEW TAB                                          -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'overview'): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:20px; margin-bottom:24px;">

                    <!-- Card 1: Razorpay -->
                    <div class="card-panel" style="display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; border:1px solid <?= !empty($rzp['is_enabled']) ? 'rgba(59,130,246,0.3)' : 'var(--border-color)' ?>;">
                        <div style="position:absolute; top:0; left:0; right:0; height:4px; background:<?= !empty($rzp['is_enabled']) ? 'var(--gradient-primary)' : 'var(--border-color)' ?>;"></div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:44px; height:44px; border-radius:10px; background:rgba(59, 130, 246, 0.12); display:flex; align-items:center; justify-content:center; color:var(--brand-blue);">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    </div>
                                    <div>
                                        <h3 style="font-size:1.05rem; font-weight:700; margin:0; color:var(--text-primary);">Razorpay Checkout</h3>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">Primary Online Payment Rails</span>
                                    </div>
                                </div>
                                <span class="badge" style="<?= !empty($rzp['is_enabled']) ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(239,68,68,0.15); color:var(--status-danger);' ?> font-weight:700;">
                                    <?= !empty($rzp['is_enabled']) ? '● ACTIVE' : '○ DISABLED' ?>
                                </span>
                            </div>

                            <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom:16px;">
                                <?= htmlspecialchars($rzp['description'] ?? 'Instant online payments via UPI, Credit/Debit Cards, NetBanking, and EMI.') ?>
                            </p>

                            <div style="background:var(--bg-surface-secondary); padding:12px; border-radius:var(--radius-md); font-size:0.82rem; margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="color:var(--text-muted);">Mode:</span>
                                    <span style="font-weight:700; color:<?= ($rzpCfg['mode'] ?? 'test') === 'live' ? 'var(--status-success)' : '#d97706' ?>; text-transform:uppercase;">
                                        <?= strtoupper($rzpCfg['mode'] ?? 'test') ?>
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="color:var(--text-muted);">Key ID:</span>
                                    <span style="font-family:monospace; font-weight:600; color:var(--text-primary);">
                                        <?= htmlspecialchars(substr($rzpCfg['key_id'] ?? 'Not Configured', 0, 16)) ?>...
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span style="color:var(--text-muted);">Auto Capture:</span>
                                    <span style="font-weight:600; color:var(--text-primary);">
                                        <?= !empty($rzpCfg['auto_capture']) ? 'Yes (Instant)' : 'Manual Capture' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; pt-3; border-top:1px solid var(--border-color); padding-top:14px;">
                            <!-- Toggle Button Form -->
                            <form method="POST" action="<?= url('admin/payment-gateways/toggle') ?>" style="margin:0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="method" value="razorpay">
                                <input type="hidden" name="is_enabled" value="<?= !empty($rzp['is_enabled']) ? '0' : '1' ?>">
                                <input type="hidden" name="tab" value="overview">
                                <button type="submit"
                                        style="background:none; border:1px solid var(--border-color); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:600; cursor:pointer; color:<?= !empty($rzp['is_enabled']) ? 'var(--status-danger)' : 'var(--status-success)' ?>;">
                                    <?= !empty($rzp['is_enabled']) ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>

                            <a href="<?= url('admin/payment-gateways?tab=razorpay') ?>"
                               style="display:inline-flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:700; color:var(--brand-blue); text-decoration:none;">
                                Configure Credentials
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Cash on Delivery -->
                    <div class="card-panel" style="display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; border:1px solid <?= !empty($cod['is_enabled']) ? 'rgba(245,158,11,0.3)' : 'var(--border-color)' ?>;">
                        <div style="position:absolute; top:0; left:0; right:0; height:4px; background:<?= !empty($cod['is_enabled']) ? '#f59e0b' : 'var(--border-color)' ?>;"></div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:44px; height:44px; border-radius:10px; background:rgba(245, 158, 11, 0.12); display:flex; align-items:center; justify-content:center; color:#f59e0b;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    </div>
                                    <div>
                                        <h3 style="font-size:1.05rem; font-weight:700; margin:0; color:var(--text-primary);">Cash on Delivery</h3>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">Doorstep Cash Settlement</span>
                                    </div>
                                </div>
                                <span class="badge" style="<?= !empty($cod['is_enabled']) ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(239,68,68,0.15); color:var(--status-danger);' ?> font-weight:700;">
                                    <?= !empty($cod['is_enabled']) ? '● ACTIVE' : '○ DISABLED' ?>
                                </span>
                            </div>

                            <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom:16px;">
                                <?= htmlspecialchars($cod['description'] ?? 'Doorstep cash payment with anti-fraud limits and PIN code restrictions.') ?>
                            </p>

                            <div style="background:var(--bg-surface-secondary); padding:12px; border-radius:var(--radius-md); font-size:0.82rem; margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="color:var(--text-muted);">Order Range:</span>
                                    <span style="font-weight:700; color:var(--text-primary);">
                                        ₹<?= number_format((float)($cod['cod_min_order_value'] ?? 999)) ?> – ₹<?= number_format((float)($codCfg['max_order_value'] ?? 50000)) ?>
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="color:var(--text-muted);">Convenience Fee:</span>
                                    <span style="font-weight:600; color:var(--text-primary);">
                                        <?= ((float)($codCfg['cod_fee'] ?? 0) > 0) ? '₹' . number_format((float)$codCfg['cod_fee']) : 'Free (₹0)' ?>
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span style="color:var(--text-muted);">Pincode Rule:</span>
                                    <span style="font-weight:600; color:var(--text-primary);">
                                        <?= ($codCfg['pincode_mode'] ?? 'all') === 'whitelist' ? 'Whitelist Only' : 'All India' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:14px;">
                            <!-- Toggle Button Form -->
                            <form method="POST" action="<?= url('admin/payment-gateways/toggle') ?>" style="margin:0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="method" value="cod">
                                <input type="hidden" name="is_enabled" value="<?= !empty($cod['is_enabled']) ? '0' : '1' ?>">
                                <input type="hidden" name="tab" value="overview">
                                <button type="submit"
                                        style="background:none; border:1px solid var(--border-color); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:600; cursor:pointer; color:<?= !empty($cod['is_enabled']) ? 'var(--status-danger)' : 'var(--status-success)' ?>;">
                                    <?= !empty($cod['is_enabled']) ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>

                            <a href="<?= url('admin/payment-gateways?tab=cod') ?>"
                               style="display:inline-flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:700; color:#f59e0b; text-decoration:none;">
                                Configure COD Rules
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: VIP Bank Wire Transfer -->
                    <div class="card-panel" style="display:flex; flex-direction:column; justify-content:space-between; position:relative; overflow:hidden; border:1px solid <?= !empty($bank['is_enabled']) ? 'rgba(139,92,246,0.3)' : 'var(--border-color)' ?>;">
                        <div style="position:absolute; top:0; left:0; right:0; height:4px; background:<?= !empty($bank['is_enabled']) ? 'var(--brand-purple)' : 'var(--border-color)' ?>;"></div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:44px; height:44px; border-radius:10px; background:rgba(139, 92, 246, 0.12); display:flex; align-items:center; justify-content:center; color:var(--brand-purple);">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="21" x2="21" y2="21"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="5 6 12 3 19 6"/><line x1="4" y1="10" x2="4" y2="21"/><line x1="20" y1="10" x2="20" y2="21"/><line x1="8" y1="14" x2="8" y2="17"/><line x1="12" y1="14" x2="12" y2="17"/><line x1="16" y1="14" x2="16" y2="17"/></svg>
                                    </div>
                                    <div>
                                        <h3 style="font-size:1.05rem; font-weight:700; margin:0; color:var(--text-primary);">VIP Bank Transfer</h3>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">NEFT / RTGS / Bespoke Wire</span>
                                    </div>
                                </div>
                                <span class="badge" style="<?= !empty($bank['is_enabled']) ? 'background:rgba(16,185,129,0.15); color:var(--status-success);' : 'background:rgba(239,68,68,0.15); color:var(--status-danger);' ?> font-weight:700;">
                                    <?= !empty($bank['is_enabled']) ? '● ACTIVE' : '○ DISABLED' ?>
                                </span>
                            </div>

                            <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5; margin-bottom:16px;">
                                <?= htmlspecialchars($bank['description'] ?? 'Direct high-value bespoke wire transfer for exclusive bridal couture orders.') ?>
                            </p>

                            <div style="background:var(--bg-surface-secondary); padding:12px; border-radius:var(--radius-md); font-size:0.82rem; margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="color:var(--text-muted);">Bank:</span>
                                    <span style="font-weight:700; color:var(--text-primary);">
                                        <?= htmlspecialchars($bankCfg['bank_name'] ?? 'HDFC Bank') ?>
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                    <span style="color:var(--text-muted);">IFSC:</span>
                                    <span style="font-family:monospace; font-weight:600; color:var(--text-primary);">
                                        <?= htmlspecialchars($bankCfg['ifsc_code'] ?? 'HDFC0000123') ?>
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span style="color:var(--text-muted);">UPI VPA:</span>
                                    <span style="font-weight:600; color:var(--brand-purple);">
                                        <?= htmlspecialchars($bankCfg['upi_id'] ?? 'jiyajilx@hdfcbank') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:14px;">
                            <!-- Toggle Button Form -->
                            <form method="POST" action="<?= url('admin/payment-gateways/toggle') ?>" style="margin:0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="method" value="bank_transfer">
                                <input type="hidden" name="is_enabled" value="<?= !empty($bank['is_enabled']) ? '0' : '1' ?>">
                                <input type="hidden" name="tab" value="overview">
                                <button type="submit"
                                        style="background:none; border:1px solid var(--border-color); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:600; cursor:pointer; color:<?= !empty($bank['is_enabled']) ? 'var(--status-danger)' : 'var(--status-success)' ?>;">
                                    <?= !empty($bank['is_enabled']) ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>

                            <a href="<?= url('admin/payment-gateways?tab=bank_transfer') ?>"
                               style="display:inline-flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:700; color:var(--brand-purple); text-decoration:none;">
                                Configure Bank Details
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Webhook Guidance & Security Panel -->
                <div class="card-panel" style="background:linear-gradient(135deg, var(--bg-surface) 0%, var(--bg-surface-secondary) 100%); border:1px solid var(--border-color);">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        <div style="width:36px; height:36px; border-radius:8px; background:rgba(16,185,129,0.12); display:flex; align-items:center; justify-content:center; color:var(--status-success);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:1rem; font-weight:700; color:var(--text-primary);">Webhook Real-Time Payment Synchronization</h4>
                            <p style="margin:0; font-size:0.8rem; color:var(--text-muted);">Ensure uninterrupted automated payment confirmation by adding the following endpoint in your Razorpay Merchant Dashboard:</p>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; background:var(--bg-surface); padding:10px 16px; border-radius:var(--radius-md); border:1px dashed var(--border-color); margin-top:12px; flex-wrap:wrap;">
                        <span style="font-family:monospace; font-size:0.88rem; color:var(--brand-blue); font-weight:600; word-break:break-all; flex:1;">
                            <?= htmlspecialchars($webhookUrl) ?>
                        </span>
                        <button type="button" onclick="copyWebhookUrl()"
                                style="padding:6px 14px; font-size:0.8rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">
                            Copy URL
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 2: RAZORPAY CONFIGURATION                                -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'razorpay'): ?>
                <div class="card-panel" style="max-width:900px; margin:0 auto;">
                    <div class="panel-header" style="margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
                        <div>
                            <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                Razorpay Gateway Configuration
                            </h2>
                            <p style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">Manage API keys, environment sandbox vs live mode, auto-capture policies, and supported payment rails.</p>
                        </div>
                    </div>

                    <form method="POST" action="<?= url('admin/payment-gateways/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="gateway" value="razorpay">

                        <!-- Master Active Toggle -->
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; background:var(--bg-surface-secondary); border-radius:var(--radius-md); margin-bottom:20px; border:1px solid var(--border-color);">
                            <div>
                                <strong style="color:var(--text-primary); font-size:0.95rem; display:block;">Enable Razorpay Checkout</strong>
                                <span style="font-size:0.8rem; color:var(--text-muted);">Activate or deactivate Razorpay across the customer checkout portal.</span>
                            </div>
                            <label class="switch" style="position:relative; display:inline-block; width:48px; height:26px; cursor:pointer;">
                                <input type="checkbox" name="is_enabled" value="1" <?= !empty($rzp['is_enabled']) ? 'checked' : '' ?> style="opacity:0; width:0; height:0;" id="rzpMasterSwitch" onchange="updateSwitchVisual(this)">
                                <span class="slider" style="position:absolute; inset:0; background-color:<?= !empty($rzp['is_enabled']) ? 'var(--status-success)' : '#ccc' ?>; border-radius:34px; transition:.3s;">
                                    <span style="position:absolute; content:''; height:20px; width:20px; left:<?= !empty($rzp['is_enabled']) ? '24px' : '3px' ?>; bottom:3px; background-color:white; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,0.3);"></span>
                                </span>
                            </label>
                        </div>

                        <!-- Environment Mode (Test vs Live) -->
                        <div class="form-group" style="margin-bottom:20px;">
                            <label class="form-label" style="font-weight:700;">Gateway Environment Mode</label>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:6px;">
                                <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid <?= ($rzpCfg['mode'] ?? 'test') === 'test' ? 'var(--brand-blue)' : 'var(--border-color)' ?>; border-radius:var(--radius-md); cursor:pointer; background:<?= ($rzpCfg['mode'] ?? 'test') === 'test' ? 'rgba(59,130,246,0.06)' : 'var(--bg-surface)' ?>;">
                                    <input type="radio" name="mode" value="test" <?= ($rzpCfg['mode'] ?? 'test') === 'test' ? 'checked' : '' ?> style="margin-top:3px;">
                                    <div>
                                        <div style="font-weight:700; color:var(--text-primary); font-size:0.9rem;">Test Mode (Sandbox)</div>
                                        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">Safe testing with dummy cards and simulated UPI payments. No real bank charges.</div>
                                    </div>
                                </label>

                                <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid <?= ($rzpCfg['mode'] ?? 'test') === 'live' ? 'var(--status-success)' : 'var(--border-color)' ?>; border-radius:var(--radius-md); cursor:pointer; background:<?= ($rzpCfg['mode'] ?? 'test') === 'live' ? 'rgba(16,185,129,0.06)' : 'var(--bg-surface)' ?>;">
                                    <input type="radio" name="mode" value="live" <?= ($rzpCfg['mode'] ?? 'test') === 'live' ? 'checked' : '' ?> style="margin-top:3px;">
                                    <div>
                                        <div style="font-weight:700; color:var(--status-success); font-size:0.9rem;">Live Production Mode</div>
                                        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">Real-time settlement for genuine customer transactions deposited directly to company bank.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Credentials Grid -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label" for="key_id" style="font-weight:700;">Razorpay Key ID <span style="color:var(--status-danger);">*</span></label>
                                <input type="text" id="key_id" name="key_id" class="form-input"
                                       value="<?= htmlspecialchars($rzpCfg['key_id'] ?? '') ?>"
                                       placeholder="e.g. rzp_test_9JiyajiLuxury or rzp_live_..." required>
                                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Begins with <code>rzp_test_</code> or <code>rzp_live_</code></span>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="key_secret" style="font-weight:700;">Razorpay Key Secret <span style="color:var(--status-danger);">*</span></label>
                                <div style="position:relative;">
                                    <input type="password" id="key_secret" name="key_secret" class="form-input"
                                           value="<?= !empty($rzpCfg['key_secret']) ? '••••••••' : '' ?>"
                                           placeholder="Enter Razorpay Secret">
                                    <button type="button" onclick="toggleSecretVisibility('key_secret')"
                                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                                        <svg id="eyeIcon_key_secret" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Leave as <code>••••••••</code> to keep existing secret untouched.</span>
                            </div>
                        </div>

                        <!-- Webhook Secret & Auto-Capture -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label" for="webhook_secret" style="font-weight:700;">Webhook Secret</label>
                                <input type="text" id="webhook_secret" name="webhook_secret" class="form-input"
                                       value="<?= htmlspecialchars($rzpCfg['webhook_secret'] ?? '') ?>"
                                       placeholder="e.g. whsec_jiyaji99281">
                                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Used to verify signature of incoming webhook payloads.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700;">Payment Capture Policy</label>
                                <label style="display:flex; align-items:center; gap:10px; padding:10px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; background:var(--bg-surface); margin-top:6px;">
                                    <input type="checkbox" name="auto_capture" value="1" <?= !empty($rzpCfg['auto_capture']) ? 'checked' : '' ?>>
                                    <div>
                                        <span style="font-weight:600; font-size:0.85rem; color:var(--text-primary); display:block;">Instant Auto-Capture</span>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">Automatically settle funds upon authorization without requiring manual capture.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Enabled Payment Rails / Instruments -->
                        <div class="form-group" style="margin-bottom:24px;">
                            <label class="form-label" style="font-weight:700;">Supported Payment Rails &amp; Checkout Instruments</label>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin-top:-2px; margin-bottom:10px;">Select the payment methods enabled during checkout:</p>
                            
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px;">
                                <?php
                                $rails = [
                                    'rail_upi'        => ['label' => 'UPI (GPay / PhonePe / Paytm)', 'checked' => !empty($rzpCfg['rails']['upi'] ?? true)],
                                    'rail_cards'      => ['label' => 'Credit / Debit Cards', 'checked' => !empty($rzpCfg['rails']['cards'] ?? true)],
                                    'rail_netbanking' => ['label' => 'NetBanking (50+ Banks)', 'checked' => !empty($rzpCfg['rails']['netbanking'] ?? true)],
                                    'rail_wallets'    => ['label' => 'Digital Wallets', 'checked' => !empty($rzpCfg['rails']['wallets'] ?? true)],
                                    'rail_emi'        => ['label' => 'Cardless & Credit EMI', 'checked' => !empty($rzpCfg['rails']['emi'] ?? true)],
                                ];
                                foreach ($rails as $railKey => $railInfo):
                                ?>
                                    <label style="display:flex; align-items:center; gap:8px; padding:10px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; background:var(--bg-surface);">
                                        <input type="checkbox" name="<?= $railKey ?>" value="1" <?= $railInfo['checked'] ? 'checked' : '' ?>>
                                        <span style="font-size:0.82rem; font-weight:600; color:var(--text-primary);"><?= $railInfo['label'] ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Action Bar -->
                        <div style="display:flex; justify-content:space-between; align-items:center; pt-3; border-top:1px solid var(--border-color); padding-top:16px;">
                            <button type="button" onclick="triggerTestConnection()"
                                    style="padding:10px 18px; font-size:0.85rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer;">
                                Test Current Credentials
                            </button>

                            <button type="submit"
                                    style="padding:10px 24px; font-size:0.9rem; font-weight:700; background:var(--gradient-primary); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; box-shadow:var(--shadow-glow-blue);">
                                Save Razorpay Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 3: CASH ON DELIVERY (COD) RULES & LIVE CHECKER           -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'cod'): ?>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; align-items:start;">

                    <!-- COD Rules Form -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
                            <div>
                                <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    Cash on Delivery Anti-Fraud Rules
                                </h2>
                                <p style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">Safeguard high-value couture orders with minimum/maximum basket thresholds and strict postal code whitelisting.</p>
                            </div>
                        </div>

                        <form method="POST" action="<?= url('admin/payment-gateways/update') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="gateway" value="cod">

                            <!-- Master Switch -->
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; background:var(--bg-surface-secondary); border-radius:var(--radius-md); margin-bottom:20px; border:1px solid var(--border-color);">
                                <div>
                                    <strong style="color:var(--text-primary); font-size:0.95rem; display:block;">Enable Cash on Delivery (COD)</strong>
                                    <span style="font-size:0.8rem; color:var(--text-muted);">Toggle COD availability across the checkout workflow.</span>
                                </div>
                                <label class="switch" style="position:relative; display:inline-block; width:48px; height:26px; cursor:pointer;">
                                    <input type="checkbox" name="is_enabled" value="1" <?= !empty($cod['is_enabled']) ? 'checked' : '' ?> style="opacity:0; width:0; height:0;" id="codMasterSwitch" onchange="updateSwitchVisual(this)">
                                    <span class="slider" style="position:absolute; inset:0; background-color:<?= !empty($cod['is_enabled']) ? '#f59e0b' : '#ccc' ?>; border-radius:34px; transition:.3s;">
                                        <span style="position:absolute; content:''; height:20px; width:20px; left:<?= !empty($cod['is_enabled']) ? '24px' : '3px' ?>; bottom:3px; background-color:white; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,0.3);"></span>
                                    </span>
                                </label>
                            </div>

                            <!-- Value Thresholds & Fees -->
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:20px;">
                                <div class="form-group">
                                    <label class="form-label" for="min_order_value" style="font-weight:700;">Min Order Value (₹)</label>
                                    <input type="number" step="0.01" id="min_order_value" name="min_order_value" class="form-input"
                                           value="<?= htmlspecialchars((string)($cod['cod_min_order_value'] ?? 999.00)) ?>" required>
                                    <span style="font-size:0.74rem; color:var(--text-muted); display:block; margin-top:4px;">Orders under this require prepaid.</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="max_order_value" style="font-weight:700;">Max Order Value Cap (₹)</label>
                                    <input type="number" step="0.01" id="max_order_value" name="max_order_value" class="form-input"
                                           value="<?= htmlspecialchars((string)($codCfg['max_order_value'] ?? 50000.00)) ?>" required>
                                    <span style="font-size:0.74rem; color:var(--text-muted); display:block; margin-top:4px;">Cap to prevent transit risk.</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="cod_fee" style="font-weight:700;">COD Handling Fee (₹)</label>
                                    <input type="number" step="0.01" id="cod_fee" name="cod_fee" class="form-input"
                                           value="<?= htmlspecialchars((string)($codCfg['cod_fee'] ?? 0.00)) ?>">
                                    <span style="font-size:0.74rem; color:var(--text-muted); display:block; margin-top:4px;">Extra fee added to COD orders.</span>
                                </div>
                            </div>

                            <!-- Pincode Mode Selection -->
                            <div class="form-group" style="margin-bottom:20px;">
                                <label class="form-label" style="font-weight:700;">Serviceable Delivery Territory Rule</label>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:6px;">
                                    <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid <?= ($codCfg['pincode_mode'] ?? 'all') === 'all' ? 'var(--brand-blue)' : 'var(--border-color)' ?>; border-radius:var(--radius-md); cursor:pointer; background:<?= ($codCfg['pincode_mode'] ?? 'all') === 'all' ? 'rgba(59,130,246,0.06)' : 'var(--bg-surface)' ?>;">
                                        <input type="radio" name="pincode_mode" value="all" <?= ($codCfg['pincode_mode'] ?? 'all') === 'all' ? 'checked' : '' ?> style="margin-top:3px;">
                                        <div>
                                            <div style="font-weight:700; color:var(--text-primary); font-size:0.9rem;">All India Coverage</div>
                                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">Offer COD across any Indian pincode subject to order value thresholds.</div>
                                        </div>
                                    </label>

                                    <label style="display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid <?= ($codCfg['pincode_mode'] ?? 'all') === 'whitelist' ? '#f59e0b' : 'var(--border-color)' ?>; border-radius:var(--radius-md); cursor:pointer; background:<?= ($codCfg['pincode_mode'] ?? 'all') === 'whitelist' ? 'rgba(245,158,11,0.06)' : 'var(--bg-surface)' ?>;">
                                        <input type="radio" name="pincode_mode" value="whitelist" <?= ($codCfg['pincode_mode'] ?? 'all') === 'whitelist' ? 'checked' : '' ?> style="margin-top:3px;">
                                        <div>
                                            <div style="font-weight:700; color:#d97706; font-size:0.9rem;">Whitelist Only (Restricted)</div>
                                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">Strictly restrict COD to vetted luxury courier postal codes specified below.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Whitelist Pincodes Textarea -->
                            <div class="form-group" style="margin-bottom:20px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <label class="form-label" for="pincode_whitelist" style="font-weight:700; margin:0;">
                                        Serviceable Postal Codes Whitelist
                                    </label>
                                    <div style="display:flex; gap:6px;">
                                        <button type="button" onclick="formatPincodesList()"
                                                style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:4px; cursor:pointer;">
                                            Clean &amp; Deduplicate
                                        </button>
                                        <button type="button" onclick="addMetroPincodes()"
                                                style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:4px; cursor:pointer;">
                                            + Add Top Metros
                                        </button>
                                    </div>
                                </div>
                                <textarea id="pincode_whitelist" name="pincode_whitelist" class="form-input" rows="5"
                                          placeholder="Enter 6-digit postal codes separated by comma, space or newline (e.g. 400001, 110001, 560001, 302001)"
                                          style="font-family:monospace; font-size:0.85rem; line-height:1.6;"><?= htmlspecialchars($cod['cod_pincode_whitelist'] ?? '') ?></textarea>
                                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;" id="pincodeCountText">
                                    Postal codes entered above are checked upon checkout address selection.
                                </span>
                            </div>

                            <!-- OTP Verification Option -->
                            <div class="form-group" style="margin-bottom:24px;">
                                <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; background:var(--bg-surface);">
                                    <input type="checkbox" name="otp_verify" value="1" <?= !empty($codCfg['otp_verify']) ? 'checked' : '' ?>>
                                    <div>
                                        <span style="font-weight:600; font-size:0.85rem; color:var(--text-primary); display:block;">Require OTP Verification on Delivery</span>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">Courier partners must collect 4-digit customer SMS OTP before handing over high-value apparel.</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Save Button -->
                            <div style="display:flex; justify-content:flex-end; pt-3; border-top:1px solid var(--border-color); padding-top:16px;">
                                <button type="submit"
                                        style="padding:10px 24px; font-size:0.9rem; font-weight:700; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; box-shadow:0 4px 14px rgba(245,158,11,0.3);">
                                    Save COD Anti-Fraud Rules
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Live Pincode Serviceability Checker -->
                    <div class="card-panel" style="border:1px solid var(--border-color); background:var(--bg-surface); position:sticky; top:80px;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
                            <div style="width:34px; height:34px; border-radius:8px; background:rgba(59,130,246,0.12); display:flex; align-items:center; justify-content:center; color:var(--brand-blue);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <h3 style="margin:0; font-size:0.98rem; font-weight:700; color:var(--text-primary);">Live Serviceability Test</h3>
                                <span style="font-size:0.75rem; color:var(--text-muted);">Real-time check against active COD rules</span>
                            </div>
                        </div>

                        <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:14px; line-height:1.4;">
                            Test whether a customer postal code and order basket value qualifies for Cash on Delivery:
                        </p>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label class="form-label" for="test_pincode" style="font-weight:600; font-size:0.82rem;">Indian Postal Code (6 Digits)</label>
                            <input type="text" id="test_pincode" class="form-input" placeholder="e.g. 400001 or 110001" maxlength="6" value="400001" style="font-family:monospace; font-size:1rem; letter-spacing:1px;">
                        </div>

                        <div class="form-group" style="margin-bottom:16px;">
                            <label class="form-label" for="test_amount" style="font-weight:600; font-size:0.82rem;">Simulated Order Amount (₹)</label>
                            <input type="number" id="test_amount" class="form-input" placeholder="e.g. 4500" value="4500">
                        </div>

                        <button type="button" onclick="checkPincodeServiceability()" id="btnCheckPincode"
                                style="width:100%; padding:10px; font-size:0.88rem; font-weight:700; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; color:var(--brand-blue); transition:var(--transition);"
                                onmouseover="this.style.background='var(--brand-blue)'; this.style.color='#fff'"
                                onmouseout="this.style.background='var(--bg-surface-secondary)'; this.style.color='var(--brand-blue)'">
                            Verify COD Eligibility
                        </button>

                        <!-- Result Display Box -->
                        <div id="pincodeResultBox" style="display:none; margin-top:16px; padding:14px; border-radius:var(--radius-md); font-size:0.82rem;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 4: VIP BANK WIRE TRANSFER                                -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'bank_transfer'): ?>
                <div class="card-panel" style="max-width:850px; margin:0 auto;">
                    <div class="panel-header" style="margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:14px;">
                        <div>
                            <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2.2"><line x1="3" y1="21" x2="21" y2="21"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="5 6 12 3 19 6"/><line x1="4" y1="10" x2="4" y2="21"/><line x1="20" y1="10" x2="20" y2="21"/><line x1="8" y1="14" x2="8" y2="17"/><line x1="12" y1="14" x2="12" y2="17"/><line x1="16" y1="14" x2="16" y2="17"/></svg>
                                VIP Concierge Bank Wire (NEFT / RTGS)
                            </h2>
                            <p style="font-size:0.82rem; color:var(--text-muted); margin-top:4px;">Manage company settlement banking coordinates displayed on invoices for bespoke high-value bridal couture orders.</p>
                        </div>
                    </div>

                    <form method="POST" action="<?= url('admin/payment-gateways/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="gateway" value="bank_transfer">

                        <!-- Master Switch -->
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; background:var(--bg-surface-secondary); border-radius:var(--radius-md); margin-bottom:20px; border:1px solid var(--border-color);">
                            <div>
                                <strong style="color:var(--text-primary); font-size:0.95rem; display:block;">Enable VIP Concierge Bank Wire</strong>
                                <span style="font-size:0.8rem; color:var(--text-muted);">Offer direct bank wire transfers for luxury couture clients.</span>
                            </div>
                            <label class="switch" style="position:relative; display:inline-block; width:48px; height:26px; cursor:pointer;">
                                <input type="checkbox" name="is_enabled" value="1" <?= !empty($bank['is_enabled']) ? 'checked' : '' ?> style="opacity:0; width:0; height:0;" id="bankMasterSwitch" onchange="updateSwitchVisual(this)">
                                <span class="slider" style="position:absolute; inset:0; background-color:<?= !empty($bank['is_enabled']) ? 'var(--brand-purple)' : '#ccc' ?>; border-radius:34px; transition:.3s;">
                                    <span style="position:absolute; content:''; height:20px; width:20px; left:<?= !empty($bank['is_enabled']) ? '24px' : '3px' ?>; bottom:3px; background-color:white; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,0.3);"></span>
                                </span>
                            </label>
                        </div>

                        <!-- Bank Details Fields -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                            <div class="form-group">
                                <label class="form-label" for="bank_name" style="font-weight:700;">Bank Name <span style="color:var(--status-danger);">*</span></label>
                                <input type="text" id="bank_name" name="bank_name" class="form-input"
                                       value="<?= htmlspecialchars($bankCfg['bank_name'] ?? 'HDFC Bank') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="account_name" style="font-weight:700;">Beneficiary Account Name <span style="color:var(--status-danger);">*</span></label>
                                <input type="text" id="account_name" name="account_name" class="form-input"
                                       value="<?= htmlspecialchars($bankCfg['account_name'] ?? 'Jiyaji Luxury Collections Pvt Ltd') ?>" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                            <div class="form-group">
                                <label class="form-label" for="account_number" style="font-weight:700;">Account Number <span style="color:var(--status-danger);">*</span></label>
                                <input type="text" id="account_number" name="account_number" class="form-input"
                                       value="<?= htmlspecialchars($bankCfg['account_number'] ?? '50200084920192') ?>" required style="font-family:monospace; font-weight:600;">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="ifsc_code" style="font-weight:700;">IFSC Code <span style="color:var(--status-danger);">*</span></label>
                                <input type="text" id="ifsc_code" name="ifsc_code" class="form-input"
                                       value="<?= htmlspecialchars($bankCfg['ifsc_code'] ?? 'HDFC0000123') ?>" required style="font-family:monospace; font-weight:600; text-transform:uppercase;">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                            <div class="form-group">
                                <label class="form-label" for="branch" style="font-weight:700;">Bank Branch</label>
                                <input type="text" id="branch" name="branch" class="form-input"
                                       value="<?= htmlspecialchars($bankCfg['branch'] ?? 'Fort Branch, Mumbai') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="upi_id" style="font-weight:700;">Direct Corporate UPI VPA</label>
                                <input type="text" id="upi_id" name="upi_id" class="form-input"
                                       value="<?= htmlspecialchars($bankCfg['upi_id'] ?? 'jiyajilx@hdfcbank') ?>" placeholder="e.g. jiyajilx@hdfcbank">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:24px;">
                            <label class="form-label" for="instructions" style="font-weight:700;">Client Instructions &amp; Receipt Verification Note</label>
                            <textarea id="instructions" name="instructions" class="form-input" rows="3"
                                      placeholder="Note displayed to customer on invoice/checkout"><?= htmlspecialchars($bankCfg['instructions'] ?? 'Please transfer the exact order amount and notify our concierge team with the UTR number.') ?></textarea>
                            <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Displayed to clients when placing bespoke couture wire transfer orders.</span>
                        </div>

                        <!-- Action Bar -->
                        <div style="display:flex; justify-content:flex-end; pt-3; border-top:1px solid var(--border-color); padding-top:16px;">
                            <button type="submit"
                                    style="padding:10px 24px; font-size:0.9rem; font-weight:700; background:linear-gradient(135deg, var(--brand-purple) 0%, #7c3aed 100%); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; box-shadow:0 4px 14px rgba(139,92,246,0.3);">
                                Save Bank Wire Details
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- ============================================================ -->
            <!-- TAB 5: TRANSACTIONS LEDGER & WEBHOOK INSPECTOR               -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'transactions'): ?>
                <div class="card-panel">
                    <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                        <div>
                            <h2 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                Payment Transactions &amp; Webhook Audit Ledger
                            </h2>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Real-time settlement records, gateway payment identifiers, and inspectable webhook payloads.</p>
                        </div>

                        <!-- Filter Controls -->
                        <form method="GET" action="<?= url('admin/payment-gateways') ?>" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                            <input type="hidden" name="tab" value="transactions">

                            <!-- Gateway Filter -->
                            <select name="gateway" class="form-input" style="padding:6px 10px; font-size:0.82rem; width:auto;">
                                <option value="all" <?= ($filters['gateway'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Gateways</option>
                                <option value="razorpay" <?= ($filters['gateway'] ?? '') === 'razorpay' ? 'selected' : '' ?>>Razorpay</option>
                                <option value="cod" <?= ($filters['gateway'] ?? '') === 'cod' ? 'selected' : '' ?>>Cash on Delivery</option>
                                <option value="bank_transfer" <?= ($filters['gateway'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Wire</option>
                            </select>

                            <!-- Status Filter -->
                            <select name="status" class="form-input" style="padding:6px 10px; font-size:0.82rem; width:auto;">
                                <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Statuses</option>
                                <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
                                <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="refunded" <?= ($filters['status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                <option value="initiated" <?= ($filters['status'] ?? '') === 'initiated' ? 'selected' : '' ?>>Initiated</option>
                            </select>

                            <!-- Search Box -->
                            <input type="text" name="search" class="form-input" placeholder="Search order / pay ID..."
                                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                                   style="padding:6px 10px; font-size:0.82rem; width:180px;">

                            <button type="submit" style="padding:6px 14px; font-size:0.82rem; font-weight:600; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer;">
                                Filter
                            </button>

                            <?php if (!empty($filters['gateway']) && $filters['gateway'] !== 'all' || !empty($filters['status']) && $filters['status'] !== 'all' || !empty($filters['search'])): ?>
                                <a href="<?= url('admin/payment-gateways?tab=transactions') ?>" style="font-size:0.8rem; color:var(--text-muted); text-decoration:none; padding:4px 6px;">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Ledger Table -->
                    <div style="overflow-x:auto;">
                        <table class="orders-table" style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                            <thead>
                                <tr style="border-bottom:1px solid var(--border-color); text-align:left; color:var(--text-muted);">
                                    <th style="padding:10px 14px;">Order #</th>
                                    <th style="padding:10px 14px;">Customer</th>
                                    <th style="padding:10px 14px;">Gateway</th>
                                    <th style="padding:10px 14px;">Payment ID</th>
                                    <th style="padding:10px 14px;">Amount</th>
                                    <th style="padding:10px 14px;">Status</th>
                                    <th style="padding:10px 14px;">Date / Time</th>
                                    <th style="padding:10px 14px; text-align:right;">Payload</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding:36px; color:var(--text-muted);">
                                            No payment transactions match the selected criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr style="border-bottom:1px solid var(--border-color-light);">
                                            <!-- Order # -->
                                            <td style="padding:12px 14px; font-weight:700;">
                                                <?php if (!empty($tx['order_number'])): ?>
                                                    <a href="<?= url('admin/orders?search=' . urlencode($tx['order_number'])) ?>" style="color:var(--brand-blue); text-decoration:none;">
                                                        <?= htmlspecialchars($tx['order_number']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted);">Order #<?= (int)$tx['order_id'] ?></span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Customer -->
                                            <td style="padding:12px 14px; color:var(--text-primary);">
                                                <?= htmlspecialchars($tx['shipping_name'] ?? 'VIP Guest') ?>
                                            </td>

                                            <!-- Gateway -->
                                            <td style="padding:12px 14px;">
                                                <?php if ($tx['gateway'] === 'razorpay'): ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; font-weight:600; color:var(--brand-blue);">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                                        Razorpay
                                                    </span>
                                                <?php elseif ($tx['gateway'] === 'cod'): ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; font-weight:600; color:#d97706;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                                        COD
                                                    </span>
                                                <?php else: ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; font-weight:600; color:var(--brand-purple);">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="21" x2="21" y2="21"/><polyline points="5 6 12 3 19 6"/></svg>
                                                        Bank Wire
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Payment ID -->
                                            <td style="padding:12px 14px; font-family:monospace; font-size:0.8rem; color:var(--text-secondary);">
                                                <?php if (!empty($tx['gateway_payment_id'])): ?>
                                                    <span title="<?= htmlspecialchars($tx['gateway_payment_id']) ?>">
                                                        <?= htmlspecialchars(substr($tx['gateway_payment_id'], 0, 18)) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted);">&mdash;</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Amount -->
                                            <td style="padding:12px 14px; font-weight:700; color:var(--text-primary);">
                                                ₹<?= number_format((float)$tx['amount']) ?>
                                            </td>

                                            <!-- Status -->
                                            <td style="padding:12px 14px;">
                                                <?php
                                                $status = $tx['status'];
                                                $bg = 'rgba(107,114,128,0.15)';
                                                $fg = 'var(--text-muted)';
                                                if ($status === 'success') {
                                                    $bg = 'rgba(16,185,129,0.15)';
                                                    $fg = 'var(--status-success)';
                                                } elseif ($status === 'failed') {
                                                    $bg = 'rgba(239,68,68,0.15)';
                                                    $fg = 'var(--status-danger)';
                                                } elseif ($status === 'refunded') {
                                                    $bg = 'rgba(139,92,246,0.15)';
                                                    $fg = 'var(--brand-purple)';
                                                } elseif ($status === 'initiated') {
                                                    $bg = 'rgba(245,158,11,0.15)';
                                                    $fg = '#d97706';
                                                }
                                                ?>
                                                <span class="badge" style="background:<?= $bg ?>; color:<?= $fg ?>; font-weight:700; text-transform:uppercase; font-size:0.72rem; padding:3px 8px;">
                                                    <?= htmlspecialchars($status) ?>
                                                </span>
                                            </td>

                                            <!-- Date / Time -->
                                            <td style="padding:12px 14px; color:var(--text-muted); font-size:0.78rem;">
                                                <?php $ts = validTs($tx['created_at']); ?>
                                                <?= $ts ? date('d M Y, h:i A', $ts) : '&mdash;' ?>
                                            </td>

                                            <!-- Webhook Payload Inspector Action -->
                                            <td style="padding:12px 14px; text-align:right;">
                                                <?php if (!empty($tx['webhook_payload'])): ?>
                                                    <button type="button"
                                                            onclick='inspectWebhookPayload(<?= json_encode($tx['gateway_payment_id'] ?? ('TX-' . $tx['id'])) ?>, <?= json_encode($tx['webhook_payload']) ?>)'
                                                            style="padding:4px 10px; font-size:0.75rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer; color:var(--brand-blue);"
                                                            title="Inspect Webhook JSON">
                                                        Inspect JSON
                                                    </button>
                                                <?php else: ?>
                                                    <span style="font-size:0.75rem; color:var(--text-muted);">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:14px; border-top:1px solid var(--border-color);">
                            <div style="font-size:0.8rem; color:var(--text-muted);">
                                Showing page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong> (<?= $pagination['total'] ?> transactions)
                            </div>
                            <div style="display:flex; gap:6px;">
                                <?php if ($pagination['has_prev']): ?>
                                    <a href="<?= url('admin/payment-gateways?tab=transactions&page=' . ($pagination['current_page'] - 1) . '&gateway=' . urlencode($filters['gateway']) . '&status=' . urlencode($filters['status']) . '&search=' . urlencode($filters['search'])) ?>"
                                       style="padding:6px 12px; font-size:0.8rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); text-decoration:none;">
                                        &larr; Previous
                                    </a>
                                <?php endif; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <a href="<?= url('admin/payment-gateways?tab=transactions&page=' . ($pagination['current_page'] + 1) . '&gateway=' . urlencode($filters['gateway']) . '&status=' . urlencode($filters['status']) . '&search=' . urlencode($filters['search'])) ?>"
                                       style="padding:6px 12px; font-size:0.8rem; font-weight:600; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); text-decoration:none;">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: WEBHOOK PAYLOAD INSPECTOR                             -->
<!-- ============================================================ -->
<div id="webhookModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); width:90%; max-width:680px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); overflow:hidden;">
        <!-- Header -->
        <div style="padding:16px 20px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:var(--text-primary);" id="modalTxId">Webhook Audit Payload</h3>
                <span style="font-size:0.75rem; color:var(--text-muted);">Raw verified JSON delivery from Razorpay webhook listener</span>
            </div>
            <button type="button" onclick="closeWebhookModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding:20px; overflow-y:auto; flex:1;">
            <pre id="modalJsonContent" style="background:var(--bg-surface-secondary); padding:16px; border-radius:var(--radius-md); font-family:monospace; font-size:0.8rem; line-height:1.5; color:var(--text-primary); overflow-x:auto; margin:0; border:1px solid var(--border-color);"></pre>
        </div>

        <!-- Footer -->
        <div style="padding:14px 20px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; background:var(--bg-surface-secondary);">
            <button type="button" onclick="copyWebhookModalContent()" id="btnCopyJson"
                    style="padding:6px 14px; font-size:0.82rem; font-weight:600; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer; color:var(--brand-blue);">
                Copy Payload JSON
            </button>
            <button type="button" onclick="closeWebhookModal()"
                    style="padding:6px 16px; font-size:0.82rem; font-weight:600; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-sm); cursor:pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT LOGIC & AJAX HANDLERS                             -->
<!-- ============================================================ -->
<script>
// 1. Copy Webhook URL to Clipboard
function copyWebhookUrl() {
    const url = <?= json_encode($webhookUrl) ?>;
    navigator.clipboard.writeText(url).then(() => {
        const btnText = document.getElementById('webhookBtnText');
        const orig = btnText.innerText;
        btnText.innerText = '✓ URL Copied!';
        btnText.style.color = 'var(--status-success)';
        setTimeout(() => {
            btnText.innerText = orig;
            btnText.style.color = '';
        }, 2200);
    }).catch(() => {
        alert('Webhook URL: ' + url);
    });
}

// 2. Toggle Secret Visibility
function toggleSecretVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('eyeIcon_' + inputId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

// 3. Switch Visual Feedback
function updateSwitchVisual(checkbox) {
    const slider = checkbox.nextElementSibling;
    const knob = slider.firstElementChild;
    if (checkbox.checked) {
        slider.style.backgroundColor = 'var(--status-success)';
        knob.style.left = '24px';
    } else {
        slider.style.backgroundColor = '#ccc';
        knob.style.left = '3px';
    }
}

// 4. Trigger Test Connection AJAX
function triggerTestConnection() {
    const keyIdElem = document.getElementById('key_id');
    const keySecElem = document.getElementById('key_secret');
    
    const keyId = keyIdElem ? keyIdElem.value : '';
    const keySecret = keySecElem ? keySecElem.value : '';

    const btn = document.getElementById('testConnBtn');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Testing...';

    const formData = new FormData();
    formData.append('key_id', keyId);
    formData.append('key_secret', keySecret);

    fetch('<?= url("admin/payment-gateways/test-connection") ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        if (data.valid) {
            alert('✓ Razorpay Credentials Check Passed!\n\nEnvironment: ' + data.mode.toUpperCase() + '\nMessage: ' + data.message);
        } else {
            alert('⚠ Razorpay Validation Notice:\n\n' + data.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Network error verifying credentials: ' + err.message);
    });
}

// 5. Clean & Deduplicate Pincodes
function formatPincodesList() {
    const textarea = document.getElementById('pincode_whitelist');
    if (!textarea) return;
    const raw = textarea.value;
    const matches = raw.match(/\b\d{6}\b/g) || [];
    const unique = [...new Set(matches)].sort();
    textarea.value = unique.join(', ');
    
    const countText = document.getElementById('pincodeCountText');
    if (countText) {
        countText.innerText = '✓ Cleaned and deduplicated: ' + unique.length + ' serviceable PIN codes loaded.';
        countText.style.color = 'var(--status-success)';
    }
}

// 6. Add Top Indian Metro Pincodes
function addMetroPincodes() {
    const metros = [
        '400001', '400050', '400078', // Mumbai
        '110001', '110016', '110024', // Delhi
        '560001', '560034', '560068', // Bengaluru
        '500001', '500034', '500081', // Hyderabad
        '700001', '700019', '700091', // Kolkata
        '600001', '600028', '600086', // Chennai
        '411001', '411004', '411014', // Pune
        '302001', '302015', '302020', // Jaipur
        '380001', '380015',           // Ahmedabad
        '160001', '160017',           // Chandigarh
        '226001', '226010',           // Lucknow
    ];
    const textarea = document.getElementById('pincode_whitelist');
    if (!textarea) return;
    
    const existing = textarea.value.match(/\b\d{6}\b/g) || [];
    const combined = [...new Set([...existing, ...metros])].sort();
    textarea.value = combined.join(', ');

    const countText = document.getElementById('pincodeCountText');
    if (countText) {
        countText.innerText = '✓ Added top metro hubs: ' + combined.length + ' total PIN codes loaded.';
        countText.style.color = 'var(--status-success)';
    }
}

// 7. Live Pincode Serviceability AJAX
function checkPincodeServiceability() {
    const pincode = document.getElementById('test_pincode').value.trim();
    const amount = parseFloat(document.getElementById('test_amount').value) || 1000;
    const resBox = document.getElementById('pincodeResultBox');
    const btn = document.getElementById('btnCheckPincode');

    if (pincode.length !== 6 || !/^\d{6}$/.test(pincode)) {
        resBox.style.display = 'block';
        resBox.style.background = 'rgba(239, 68, 68, 0.1)';
        resBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        resBox.innerHTML = '<strong style="color:var(--status-danger);">Invalid PIN Code</strong><p style="margin:4px 0 0; color:var(--text-secondary);">Please enter a valid 6-digit Indian postal code.</p>';
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Checking...';

    const formData = new FormData();
    formData.append('pincode', pincode);
    formData.append('amount', amount);

    fetch('<?= url("admin/payment-gateways/check-pincode") ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = 'Verify COD Eligibility';
        resBox.style.display = 'block';

        if (data.eligible) {
            resBox.style.background = 'rgba(16, 185, 129, 0.1)';
            resBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            resBox.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--status-success);"></span>
                    <strong style="color:var(--status-success); font-size:0.9rem;">Serviceable for COD</strong>
                </div>
                <div style="color:var(--text-secondary); line-height:1.4;">${data.reason}</div>
                <div style="margin-top:8px; font-weight:600; color:var(--text-primary);">
                    Handling Fee: ${data.fee > 0 ? '₹' + data.fee : 'Free (₹0)'}
                </div>
            `;
        } else {
            resBox.style.background = 'rgba(239, 68, 68, 0.1)';
            resBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            resBox.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--status-danger);"></span>
                    <strong style="color:var(--status-danger); font-size:0.9rem;">Not Eligible for COD</strong>
                </div>
                <div style="color:var(--text-secondary); line-height:1.4;">${data.reason}</div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = 'Verify COD Eligibility';
        resBox.style.display = 'block';
        resBox.style.background = 'rgba(239, 68, 68, 0.1)';
        resBox.innerHTML = '<strong style="color:var(--status-danger);">Error checking pincode:</strong> ' + err.message;
    });
}

// 8. Webhook Modal Inspection
let activeModalJson = '';
function inspectWebhookPayload(txId, rawPayload) {
    const modal = document.getElementById('webhookModal');
    const txIdElem = document.getElementById('modalTxId');
    const contentElem = document.getElementById('modalJsonContent');

    txIdElem.innerText = 'Webhook Payload — ' + txId;

    try {
        const parsed = (typeof rawPayload === 'string') ? JSON.parse(rawPayload) : rawPayload;
        activeModalJson = JSON.stringify(parsed, null, 2);
    } catch (e) {
        activeModalJson = rawPayload;
    }

    contentElem.innerText = activeModalJson;
    modal.style.display = 'flex';
}

function closeWebhookModal() {
    document.getElementById('webhookModal').style.display = 'none';
}

function copyWebhookModalContent() {
    navigator.clipboard.writeText(activeModalJson).then(() => {
        const btn = document.getElementById('btnCopyJson');
        const orig = btn.innerText;
        btn.innerText = '✓ Copied!';
        btn.style.color = 'var(--status-success)';
        setTimeout(() => {
            btn.innerText = orig;
            btn.style.color = '';
        }, 2000);
    });
}

// Close modal on escape or background click
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeWebhookModal();
});
document.getElementById('webhookModal').addEventListener('click', function(e) {
    if (e.target === this) closeWebhookModal();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
