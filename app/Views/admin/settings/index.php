<?php
include __DIR__ . '/../layouts/header.php';

$activeTab = $activeTab ?? 'brand';
$flat = $flatValues ?? [];
$catalog = \App\Models\StoreSetting::getCatalog();
$currentGroup = $allSettings[$activeTab] ?? [];
$groupMeta = $catalog[$activeTab] ?? [];
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">

            <!-- ============================================================ -->
            <!-- 1. PAGE HEADER & BREADCRUMBS                                 -->
            <!-- ============================================================ -->
            <div class="welcome-banner" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color:var(--brand-blue); text-decoration:none; font-weight:500;">Dashboard</a>
                        <span style="opacity:.4;">/</span>
                        <span>Settings</span>
                        <span style="opacity:.4;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Store Configuration</span>
                    </div>
                    <h1 class="welcome-title" style="display:flex; align-items:center; gap:12px;">
                        Store Settings &amp; Brand Control
                        <span style="font-size:0.75rem; font-weight:700; background:rgba(45, 130, 255, 0.1); color:var(--brand-blue); padding:4px 10px; border-radius:999px; letter-spacing:0.5px; text-transform:uppercase;">
                            Feature 30 &amp; Meta Pixel
                        </span>
                    </h1>
                    <p class="welcome-subtitle">
                        Orchestrate brand identity, official contact channels, WhatsApp floating concierge, regional currencies, Meta ad pixels, and atelier operational modes.
                    </p>
                </div>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Visit Storefront Button -->
                    <a href="<?= url('home') ?>" target="_blank"
                       style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); text-decoration:none; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:var(--transition);"
                       onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                       onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'"
                       title="Open Public Storefront in New Tab">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <span>View Storefront</span>
                    </a>

                    <!-- Test WhatsApp Direct Link -->
                    <button type="button" onclick="testWhatsAppConcierge()"
                            style="display:inline-flex; align-items:center; gap:8px; background:#25D366; color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); border:none; cursor:pointer; box-shadow:0 4px 12px rgba(37, 211, 102, 0.25); transition:var(--transition);"
                            onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.opacity='1'; this.style.transform=''"
                            title="Test WhatsApp concierge link with pre-filled message">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Test WhatsApp Concierge</span>
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 2. EXECUTIVE KPIS SUMMARY CARDS                             -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px;">
                <!-- KPI 1: Brand Completeness -->
                <div class="kpi-card" style="position:relative; overflow:hidden;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div class="kpi-label">Brand Profile Completeness</div>
                            <div class="kpi-val" style="color:var(--brand-purple); font-size:1.75rem; font-weight:800;">
                                <?= $kpis['profile_completeness'] ?>%
                            </div>
                        </div>
                        <div class="kpi-icon" style="background:rgba(140, 48, 245, 0.1); color:var(--brand-purple);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                    </div>
                    <div style="margin-top:12px; background:var(--border-color); height:6px; border-radius:999px; overflow:hidden;">
                        <div style="background:var(--gradient-purple); height:100%; width:<?= $kpis['profile_completeness'] ?>%; border-radius:999px; transition:width 0.6s ease;"></div>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:8px;">
                        <?= !empty($flat['store_gstin']) ? 'Verified GSTIN: ' . htmlspecialchars($flat['store_gstin']) : 'GSTIN Pending' ?>
                    </div>
                </div>

                <!-- KPI 2: WhatsApp Floating Widget -->
                <div class="kpi-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div class="kpi-label">WhatsApp Concierge</div>
                            <div class="kpi-val" style="display:flex; align-items:center; gap:8px; font-size:1.4rem; font-weight:800; color:<?= $kpis['whatsapp_enabled'] ? '#059669' : '#94A3B8' ?>;">
                                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= $kpis['whatsapp_enabled'] ? '#10B981' : '#94A3B8' ?>; <?= $kpis['whatsapp_enabled'] ? 'box-shadow:0 0 0 3px rgba(16,185,129,0.25);' : '' ?>"></span>
                                <?= $kpis['whatsapp_enabled'] ? 'Active &amp; Floating' : 'Disabled' ?>
                            </div>
                        </div>
                        <div class="kpi-icon" style="background:rgba(37, 211, 102, 0.12); color:#25D366;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                        </div>
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:12px; font-weight:600;">
                        Target: <?= htmlspecialchars($kpis['whatsapp_number'] ?: '+91 98765 43210') ?>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Position: <?= htmlspecialchars($flat['whatsapp_position'] ?? 'bottom-right') ?> &bull; Greeting Bubble: <?= !empty($flat['whatsapp_popup_enabled']) ? 'ON' : 'OFF' ?>
                    </div>
                </div>

                <!-- KPI 3: Tracking & Analytics Pixels -->
                <div class="kpi-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div class="kpi-label">Tracking Pixels &amp; Analytics</div>
                            <div class="kpi-val" style="color:var(--brand-blue); font-size:1.75rem; font-weight:800;">
                                <?= $kpis['active_pixels_count'] ?> / 3 Active
                            </div>
                        </div>
                        <div class="kpi-icon" style="background:rgba(45, 130, 255, 0.1); color:var(--brand-blue);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                    </div>
                    <div style="display:flex; gap:6px; margin-top:12px; flex-wrap:wrap;">
                        <span style="font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:4px; background:<?= $kpis['meta_pixel_active'] ? 'rgba(16,185,129,0.1)' : 'rgba(148,163,184,0.15)' ?>; color:<?= $kpis['meta_pixel_active'] ? '#059669' : '#64748B' ?>;">
                            Meta Pixel <?= $kpis['meta_pixel_active'] ? '✓' : '—' ?>
                        </span>
                        <span style="font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:4px; background:<?= $kpis['ga4_active'] ? 'rgba(16,185,129,0.1)' : 'rgba(148,163,184,0.15)' ?>; color:<?= $kpis['ga4_active'] ? '#059669' : '#64748B' ?>;">
                            GA4 <?= $kpis['ga4_active'] ? '✓' : '—' ?>
                        </span>
                        <span style="font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:4px; background:<?= $kpis['gtm_active'] ? 'rgba(16,185,129,0.1)' : 'rgba(148,163,184,0.15)' ?>; color:<?= $kpis['gtm_active'] ? '#059669' : '#64748B' ?>;">
                            GTM <?= $kpis['gtm_active'] ? '✓' : '—' ?>
                        </span>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:8px;">
                        Retargeting and conversion telemetry active
                    </div>
                </div>

                <!-- KPI 4: Store Operational State -->
                <div class="kpi-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div class="kpi-label">Store Operational State</div>
                            <div class="kpi-val" style="font-size:1.4rem; font-weight:800; color:<?= $kpis['is_maintenance'] ? '#d97706' : ($kpis['accepting_orders'] ? '#059669' : '#dc2626') ?>;">
                                <?= $kpis['is_maintenance'] ? 'Maintenance Mode' : ($kpis['accepting_orders'] ? 'Online &amp; Taking Orders' : 'Orders Paused') ?>
                            </div>
                        </div>
                        <div class="kpi-icon" style="background:<?= $kpis['is_maintenance'] ? 'rgba(245, 158, 11, 0.1)' : ($kpis['accepting_orders'] ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)') ?>; color:<?= $kpis['is_maintenance'] ? '#d97706' : ($kpis['accepting_orders'] ? '#059669' : '#dc2626') ?>;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:12px;">
                        Currency: <strong><?= htmlspecialchars($flat['currency_code'] ?? 'INR') ?> (<?= htmlspecialchars($flat['currency_symbol'] ?? '₹') ?>)</strong> &bull; TZ: <?= htmlspecialchars($flat['timezone'] ?? 'Asia/Kolkata') ?>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Last updated: <?= htmlspecialchars($kpis['latest_update']) ?>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 3. CONFIGURATION TABS NAVIGATION                             -->
            <!-- ============================================================ -->
            <div class="admin-card" style="margin-bottom:24px; padding:6px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div style="display:flex; gap:6px; overflow-x:auto; padding-bottom:2px;" class="custom-scrollbar">
                    <?php
                    $tabsCatalog = [
                        'brand'        => ['label' => 'Brand & Identity',        'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
                        'contact'      => ['label' => 'Contact & Concierge',     'icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>'],
                        'whatsapp'     => ['label' => 'WhatsApp Concierge',      'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>'],
                        'localization' => ['label' => 'Localization & Currency', 'icon' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
                        'marketing'    => ['label' => 'Marketing & Pixels',      'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
                        'social'       => ['label' => 'Social Channels',         'icon' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
                        'operations'   => ['label' => 'Store Operations',        'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
                    ];

                    foreach ($tabsCatalog as $tabKey => $tabInfo):
                        $isCurrent = ($activeTab === $tabKey);
                    ?>
                        <a href="<?= url('admin/settings?tab=' . $tabKey) ?>"
                           class="settings-tab-btn <?= $isCurrent ? 'active' : '' ?>"
                           style="display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:var(--radius-md); font-size:0.88rem; font-weight:600; text-decoration:none; white-space:nowrap; transition:var(--transition); color:<?= $isCurrent ? '#fff' : 'var(--text-secondary)' ?>; background:<?= $isCurrent ? 'var(--gradient-primary)' : 'transparent' ?>; box-shadow:<?= $isCurrent ? 'var(--shadow-glow-blue)' : 'none' ?>;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <?= $tabInfo['icon'] ?>
                            </svg>
                            <span><?= $tabInfo['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 4. TAB PANELS & FORMS                                       -->
            <!-- ============================================================ -->

            <!-- TAB 1: BRAND & IDENTITY -->
            <?php if ($activeTab === 'brand'): ?>
                <div class="settings-grid-layout" style="display:grid; grid-template-columns:2fr 1fr; gap:24px; align-items:start;">
                    <!-- Left: Core Brand Settings Form -->
                    <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                        <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                            <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                                <span style="color:var(--brand-purple);">👑</span> Brand Identity &amp; Corporate Profile
                            </h2>
                            <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                                Define your luxury atelier's name, brand slogan, official tax registrations, and primary corporate identity.
                            </p>
                        </div>

                        <form method="POST" action="<?= url('admin/settings/update') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_tab" value="brand">

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                                <div class="form-group" style="grid-column:1 / -1;">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Store / Brand Name <span style="color:var(--status-danger);">*</span>
                                    </label>
                                    <input type="text" name="store_name" class="form-input" required
                                           value="<?= htmlspecialchars($flat['store_name'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; font-weight:600;"
                                           placeholder="e.g. Jiyaji Collection">
                                    <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Appears in browser title tags, automated customer emails, order invoices, and packaging receipts.</small>
                                </div>

                                <div class="form-group" style="grid-column:1 / -1;">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Brand Tagline / Slogan
                                    </label>
                                    <input type="text" name="store_tagline" class="form-input"
                                           value="<?= htmlspecialchars($flat['store_tagline'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem;"
                                           placeholder="e.g. Timeless Indian Heritage & Bespoke Bridal Couture">
                                </div>

                                <div class="form-group" style="grid-column:1 / -1;">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Brand Bio &amp; Default SEO Meta Description
                                    </label>
                                    <textarea name="store_description" rows="3" class="form-input"
                                              style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.9rem; resize:vertical;"
                                              placeholder="Describe your couture collection..."><?= htmlspecialchars($flat['store_description'] ?? '') ?></textarea>
                                </div>

                                <div class="form-group" style="grid-column:1 / -1;">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Registered Legal Entity Name
                                    </label>
                                    <input type="text" name="store_legal_name" class="form-input"
                                           value="<?= htmlspecialchars($flat['store_legal_name'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                           placeholder="e.g. Jiyaji Luxury Apparels Pvt. Ltd.">
                                    <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Formal legal entity printed on GST compliance documents and terms of sale.</small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        GSTIN (Goods &amp; Services Tax ID)
                                    </label>
                                    <input type="text" name="store_gstin" class="form-input" maxlength="15"
                                           value="<?= htmlspecialchars($flat['store_gstin'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; font-family:monospace; text-transform:uppercase; letter-spacing:1px;"
                                           placeholder="e.g. 08AAAAA0000A1Z5">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Corporate PAN (Permanent Account Number)
                                    </label>
                                    <input type="text" name="store_pan" class="form-input" maxlength="10"
                                           value="<?= htmlspecialchars($flat['store_pan'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; font-family:monospace; text-transform:uppercase; letter-spacing:1px;"
                                           placeholder="e.g. AAACJ1234K">
                                </div>

                                <div class="form-group" style="grid-column:1 / -1;">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Corporate CIN (Company Identification Number)
                                    </label>
                                    <input type="text" name="store_cin" class="form-input" maxlength="21"
                                           value="<?= htmlspecialchars($flat['store_cin'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; font-family:monospace; text-transform:uppercase; letter-spacing:1px;"
                                           placeholder="e.g. U17100RJ2026PTC089123">
                                </div>
                            </div>

                            <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px; margin-top:24px;">
                                <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    Save Brand Identity
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Brand Logos & Favicon Asset Uploader -->
                    <div style="display:flex; flex-direction:column; gap:24px;">
                        <!-- Card: Primary Light Logo -->
                        <div class="admin-card" style="padding:24px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                            <h3 style="font-size:1rem; font-weight:800; color:var(--text-primary); margin-bottom:6px;">
                                Store Logo (Light Theme)
                            </h3>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:16px;">
                                Displayed on light headers, white invoices, and packing slips.
                            </p>

                            <div style="background:#F8FAFC; border:2px dashed var(--border-color); border-radius:var(--radius-md); padding:20px; text-align:center; margin-bottom:14px; min-height:90px; display:flex; align-items:center; justify-content:center;">
                                <?php $logoLightUrl = !empty($flat['store_logo_url']) ? image_url($flat['store_logo_url']) : asset('images/store/logo.svg'); ?>
                                <img src="<?= $logoLightUrl ?>" alt="Store Logo Light" id="previewLogoLight" style="max-height:60px; max-width:100%; object-fit:contain;">
                            </div>

                            <form method="POST" action="<?= url('admin/settings/upload-asset') ?>" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="asset_type" value="logo">
                                <div style="display:flex; gap:8px;">
                                    <input type="file" name="asset_file" accept=".png,.jpg,.jpeg,.svg,.webp" required
                                           style="font-size:0.8rem; width:100%; padding:6px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                                    <button type="submit" style="background:var(--brand-blue); color:#fff; border:none; padding:8px 14px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:700; cursor:pointer; white-space:nowrap;">
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Card: Dark Mode Logo -->
                        <div class="admin-card" style="padding:24px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                            <h3 style="font-size:1rem; font-weight:800; color:var(--text-primary); margin-bottom:6px;">
                                Dark Mode Logo (Inverted)
                            </h3>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:16px;">
                                Displayed on dark navigation bars and deep midnight banners.
                            </p>

                            <div style="background:#0F172A; border:2px dashed #334155; border-radius:var(--radius-md); padding:20px; text-align:center; margin-bottom:14px; min-height:90px; display:flex; align-items:center; justify-content:center;">
                                <?php $logoDarkUrl = !empty($flat['store_logo_dark_url']) ? image_url($flat['store_logo_dark_url']) : asset('images/store/logo-dark.svg'); ?>
                                <img src="<?= $logoDarkUrl ?>" alt="Store Logo Dark" id="previewLogoDark" style="max-height:60px; max-width:100%; object-fit:contain;">
                            </div>

                            <form method="POST" action="<?= url('admin/settings/upload-asset') ?>" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="asset_type" value="logo_dark">
                                <div style="display:flex; gap:8px;">
                                    <input type="file" name="asset_file" accept=".png,.jpg,.jpeg,.svg,.webp" required
                                           style="font-size:0.8rem; width:100%; padding:6px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                                    <button type="submit" style="background:var(--brand-purple); color:#fff; border:none; padding:8px 14px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:700; cursor:pointer; white-space:nowrap;">
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Card: Browser Favicon -->
                        <div class="admin-card" style="padding:24px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                            <h3 style="font-size:1rem; font-weight:800; color:var(--text-primary); margin-bottom:6px;">
                                Browser Favicon (.ico / .svg / .png)
                            </h3>
                            <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:16px;">
                                Browser tab icon and mobile bookmark badge.
                            </p>

                            <div style="display:flex; align-items:center; gap:16px; background:#F8FAFC; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px; margin-bottom:14px;">
                                <?php $favUrl = !empty($flat['store_favicon_url']) ? image_url($flat['store_favicon_url']) : asset('images/store/favicon.svg'); ?>
                                <img src="<?= $favUrl ?>" alt="Favicon Preview" id="previewFavicon" style="width:40px; height:40px; border-radius:8px; object-fit:contain; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                                <div style="font-size:0.75rem; color:var(--text-secondary);">
                                    32x32px or 64x64px square icon
                                </div>
                            </div>

                            <form method="POST" action="<?= url('admin/settings/upload-asset') ?>" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="asset_type" value="favicon">
                                <div style="display:flex; gap:8px;">
                                    <input type="file" name="asset_file" accept=".ico,.png,.svg" required
                                           style="font-size:0.8rem; width:100%; padding:6px; border:1px solid var(--border-color); border-radius:var(--radius-sm);">
                                    <button type="submit" style="background:#0F172A; color:#fff; border:none; padding:8px 14px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:700; cursor:pointer; white-space:nowrap;">
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB 2: CONTACT & CONCIERGE -->
            <?php if ($activeTab === 'contact'): ?>
                <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04); max-width:960px;">
                    <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                            <span style="color:var(--brand-blue);">📞</span> Customer Care &amp; Concierge Coordinates
                        </h2>
                        <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                            Configure support communication mailboxes, phone lines, dispatch headquarters address, and operating hours.
                        </p>
                    </div>

                    <form method="POST" action="<?= url('admin/settings/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_tab" value="contact">

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Primary Concierge Email <span style="color:var(--status-danger);">*</span>
                                </label>
                                <input type="email" name="contact_email" class="form-input" required
                                       value="<?= htmlspecialchars($flat['contact_email'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="concierge@jiyajicollection.com">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Orders &amp; Dispatch Desk Email
                                </label>
                                <input type="email" name="contact_orders_email" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_orders_email'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="orders@jiyajicollection.com">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Concierge Telephone / Mobile <span style="color:var(--status-danger);">*</span>
                                </label>
                                <input type="text" name="contact_phone" class="form-input" required
                                       value="<?= htmlspecialchars($flat['contact_phone'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="+91 98765 43210">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Toll-Free Helpline
                                </label>
                                <input type="text" name="contact_toll_free" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_toll_free'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="1800 123 4567">
                            </div>

                            <div class="form-group" style="grid-column:1 / -1;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Atelier Headquarters Address Line 1
                                </label>
                                <input type="text" name="contact_address_line1" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_address_line1'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="Building, Plot, Street">
                            </div>

                            <div class="form-group" style="grid-column:1 / -1;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Address Line 2 / Landmark
                                </label>
                                <input type="text" name="contact_address_line2" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_address_line2'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="Area or Landmark">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    City
                                </label>
                                <input type="text" name="contact_city" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_city'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="Jaipur">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    State / Province
                                </label>
                                <input type="text" name="contact_state" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_state'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="Rajasthan">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Postal / PIN Code
                                </label>
                                <input type="text" name="contact_pincode" class="form-input" maxlength="6"
                                       value="<?= htmlspecialchars($flat['contact_pincode'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; font-family:monospace;"
                                       placeholder="302001">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Country
                                </label>
                                <input type="text" name="contact_country" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_country'] ?? 'India') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="India">
                            </div>

                            <div class="form-group" style="grid-column:1 / -1;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Stylist &amp; Concierge Operating Hours
                                </label>
                                <input type="text" name="contact_hours" class="form-input"
                                       value="<?= htmlspecialchars($flat['contact_hours'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="Mon – Sat: 10:00 AM – 8:00 PM IST">
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px; margin-top:24px;">
                            <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save Contact Coordinates
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- TAB 3: WHATSAPP CONCIERGE WIDGET (Feature 30 & Live Simulator) -->
            <?php if ($activeTab === 'whatsapp'): ?>
                <div class="settings-grid-layout" style="display:grid; grid-template-columns:1.2fr 1fr; gap:24px; align-items:start;">
                    <!-- Left: Configuration Form -->
                    <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                        <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                                        <span style="color:#25D366;">💬</span> WhatsApp Floating Concierge
                                    </h2>
                                    <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                                        Engage prospective high-value bridal and sherwani clients in real-time on WhatsApp.
                                    </p>
                                </div>

                                <!-- Quick Toggle Form -->
                                <form method="POST" action="<?= url('admin/settings/toggle') ?>" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="key" value="whatsapp_enabled">
                                    <input type="hidden" name="_tab" value="whatsapp">
                                    <button type="submit"
                                            style="display:inline-flex; align-items:center; gap:6px; border:none; padding:6px 14px; border-radius:999px; font-size:0.8rem; font-weight:700; cursor:pointer; transition:var(--transition); background:<?= !empty($flat['whatsapp_enabled']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(148, 163, 184, 0.2)' ?>; color:<?= !empty($flat['whatsapp_enabled']) ? '#059669' : '#64748B' ?>;">
                                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor;"></span>
                                        <?= !empty($flat['whatsapp_enabled']) ? 'Active &bull; Click to Disable' : 'Inactive &bull; Click to Enable' ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="<?= url('admin/settings/update') ?>" id="whatsappForm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_tab" value="whatsapp">

                            <!-- Master Switch Checkbox -->
                            <div style="background:rgba(37, 211, 102, 0.06); border:1px solid rgba(37, 211, 102, 0.25); border-radius:var(--radius-md); padding:16px 20px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
                                <div>
                                    <div style="font-weight:700; font-size:0.95rem; color:#065f46;">Enable WhatsApp Floating Concierge Widget</div>
                                    <div style="font-size:0.8rem; color:#047857; margin-top:2px;">Renders floating WhatsApp action button on all customer-facing store pages.</div>
                                </div>
                                <label class="switch" style="position:relative; display:inline-block; width:52px; height:28px;">
                                    <input type="checkbox" name="whatsapp_enabled" value="1" <?= !empty($flat['whatsapp_enabled']) ? 'checked' : '' ?> onchange="updateSimulator();" style="opacity:0; width:0; height:0;">
                                    <span class="slider round" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:<?= !empty($flat['whatsapp_enabled']) ? '#10B981' : '#cbd5e1' ?>; transition:.3s; border-radius:34px;">
                                        <span style="position:absolute; content:''; height:22px; width:22px; left:<?= !empty($flat['whatsapp_enabled']) ? '27px' : '3px' ?>; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>
                                    </span>
                                </label>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group" style="margin-bottom:20px;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    WhatsApp Phone Number <span style="color:var(--status-danger);">*</span>
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#25D366;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </span>
                                    <input type="text" name="whatsapp_number" id="inputWhatsappNumber" class="form-input" required
                                           value="<?= htmlspecialchars($flat['whatsapp_number'] ?? '+919876543210') ?>"
                                           oninput="updateSimulator();"
                                           style="width:100%; padding:11px 14px 11px 42px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; font-family:monospace; font-weight:700;"
                                           placeholder="e.g. +919876543210">
                                </div>
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Include international country dial code (e.g. +91 for India) without dashes or spaces.</small>
                            </div>

                            <!-- Pre-filled Greeting Message -->
                            <div class="form-group" style="margin-bottom:20px;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Default Pre-filled Inbound Message
                                </label>
                                <textarea name="whatsapp_default_message" id="inputWhatsappMessage" rows="3" class="form-input"
                                          oninput="updateSimulator();"
                                          style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.9rem;"
                                          placeholder="Type default message..."><?= htmlspecialchars($flat['whatsapp_default_message'] ?? '') ?></textarea>
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">This message automatically populates the customer's WhatsApp chat when they tap the button.</small>
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                                <!-- Position -->
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Widget Screen Position
                                    </label>
                                    <select name="whatsapp_position" id="inputWhatsappPosition" class="form-input"
                                            onchange="updateSimulator();"
                                            style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; background:var(--bg-surface);">
                                        <option value="bottom-right" <?= ($flat['whatsapp_position'] ?? '') === 'bottom-right' ? 'selected' : '' ?>>Bottom Right (Standard)</option>
                                        <option value="bottom-left" <?= ($flat['whatsapp_position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>>Bottom Left</option>
                                    </select>
                                </div>

                                <!-- Hover Label -->
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                        Button Hover Tooltip
                                    </label>
                                    <input type="text" name="whatsapp_button_title" id="inputWhatsappTitle" class="form-input"
                                           value="<?= htmlspecialchars($flat['whatsapp_button_title'] ?? 'Chat with Stylist') ?>"
                                           oninput="updateSimulator();"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;">
                                </div>
                            </div>

                            <!-- Greeting Bubble Settings -->
                            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:20px; background:var(--bg-page);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                    <div>
                                        <div style="font-weight:700; font-size:0.9rem; color:var(--text-primary);">Floating Callout Greeting Bubble</div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">Appears automatically beside the button after a short delay.</div>
                                    </div>
                                    <input type="checkbox" name="whatsapp_popup_enabled" id="inputWhatsappBubbleEnabled" value="1"
                                           <?= !empty($flat['whatsapp_popup_enabled']) ? 'checked' : '' ?>
                                           onchange="updateSimulator();"
                                           style="width:18px; height:18px; cursor:pointer;">
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                    <div class="form-group">
                                        <label class="form-label" style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">
                                            Bubble Heading
                                        </label>
                                        <input type="text" name="whatsapp_popup_heading" id="inputWhatsappHeading" class="form-input"
                                               value="<?= htmlspecialchars($flat['whatsapp_popup_heading'] ?? 'Need Styling Advice?') ?>"
                                               oninput="updateSimulator();"
                                               style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.85rem;">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">
                                            Trigger Delay (Seconds)
                                        </label>
                                        <input type="number" name="whatsapp_delay_seconds" min="0" max="60" class="form-input"
                                               value="<?= htmlspecialchars($flat['whatsapp_delay_seconds'] ?? '3') ?>"
                                               style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.85rem;">
                                    </div>

                                    <div class="form-group" style="grid-column:1 / -1;">
                                        <label class="form-label" style="font-weight:600; font-size:0.8rem; display:block; margin-bottom:4px;">
                                            Bubble Invitation Message
                                        </label>
                                        <textarea name="whatsapp_popup_text" id="inputWhatsappSubtext" rows="2" class="form-input"
                                                  oninput="updateSimulator();"
                                                  style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.85rem;"><?= htmlspecialchars($flat['whatsapp_popup_text'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px;">
                                <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    Save WhatsApp Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Live Visual Mobile Simulator -->
                    <div class="admin-card" style="padding:24px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04); position:sticky; top:90px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <h3 style="font-size:1rem; font-weight:800; color:var(--text-primary); margin:0; display:flex; align-items:center; gap:8px;">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10B981; animation:pulse 2s infinite;"></span>
                                Live Storefront Simulator
                            </h3>
                            <span style="font-size:0.75rem; color:var(--text-muted); background:var(--bg-page); padding:3px 8px; border-radius:6px; font-weight:600;">
                                Real-time Visual
                            </span>
                        </div>

                        <!-- Simulated Mobile Screen -->
                        <div style="background:#0F172A; border-radius:24px; padding:12px; box-shadow:0 12px 30px rgba(0,0,0,0.18); border:4px solid #334155;">
                            <!-- Phone Notch & Speaker -->
                            <div style="display:flex; justify-content:center; margin-bottom:10px;">
                                <div style="width:70px; height:5px; background:#475569; border-radius:999px;"></div>
                            </div>

                            <!-- Phone Viewport -->
                            <div id="simViewport" style="background:#F8FAFC; border-radius:16px; height:420px; position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
                                <!-- Simulated Store Header -->
                                <div style="background:#FFFFFF; padding:10px 14px; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-weight:800; font-size:0.85rem; color:#0F172A; letter-spacing:1px;">JIYAJI <span style="color:var(--brand-blue); font-size:0.65rem;">LX</span></div>
                                    <div style="display:flex; gap:8px; color:#64748B;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                                    </div>
                                </div>

                                <!-- Simulated Store Product Hero Card -->
                                <div style="padding:16px; text-align:center;">
                                    <div style="background:linear-gradient(135deg, #FAF5FF 0%, #F0F4FD 100%); border-radius:12px; padding:20px 12px; border:1px solid #E2E8F0; margin-bottom:12px;">
                                        <div style="font-size:0.65rem; color:var(--brand-purple); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Royal Bridal Couture</div>
                                        <div style="font-weight:800; font-size:0.95rem; color:#0F172A; margin-bottom:6px;">The Mehrangarh Silk Sherwani</div>
                                        <div style="font-weight:700; font-size:0.9rem; color:var(--brand-blue);">₹ 48,999</div>
                                    </div>
                                    <div style="font-size:0.75rem; color:#64748B;">
                                        Handcrafted zardozi embroidery with gold bullion wiring and raw Banarasi silk.
                                    </div>
                                </div>

                                <!-- Floating WhatsApp Component inside Simulated Viewport -->
                                <div id="simFloatingContainer" style="position:absolute; bottom:16px; right:16px; display:flex; flex-direction:column; align-items:flex-end; z-index:10; pointer-events:auto;">
                                    <!-- Greeting Speech Bubble -->
                                    <div id="simBubble" style="background:#FFFFFF; border-radius:12px; padding:10px 14px; margin-bottom:10px; box-shadow:0 8px 24px rgba(0,0,0,0.14); border:1px solid #E2E8F0; max-width:210px; position:relative; transform-origin:bottom right; transition:all 0.3s ease;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                            <div id="simBubbleHeading" style="font-weight:800; font-size:0.78rem; color:#0F172A;">
                                                <?= htmlspecialchars($flat['whatsapp_popup_heading'] ?? 'Need Styling Advice?') ?>
                                            </div>
                                            <span style="font-size:0.65rem; background:#DCFCE7; color:#166534; padding:1px 5px; border-radius:4px; font-weight:700;">Online</span>
                                        </div>
                                        <div id="simBubbleText" style="font-size:0.7rem; color:#475569; line-height:1.35;">
                                            <?= htmlspecialchars($flat['whatsapp_popup_text'] ?? 'Connect directly with our royal wedding couture specialist on WhatsApp.') ?>
                                        </div>
                                        <div style="position:absolute; bottom:-6px; right:20px; width:12px; height:12px; background:#FFFFFF; border-right:1px solid #E2E8F0; border-bottom:1px solid #E2E8F0; transform:rotate(45deg);"></div>
                                    </div>

                                    <!-- Floating WhatsApp Button -->
                                    <div id="simButton" onclick="testWhatsAppConcierge()"
                                         style="background:#25D366; color:#FFFFFF; width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 16px rgba(37,211,102,0.4); cursor:pointer; position:relative; transition:all 0.3s ease;">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:14px; text-align:center;">
                            <button type="button" onclick="testWhatsAppConcierge()"
                                    style="background:transparent; border:1px dashed #25D366; color:#059669; font-weight:700; font-size:0.82rem; padding:8px 16px; border-radius:var(--radius-md); cursor:pointer; width:100%; transition:var(--transition);">
                                📲 Click to Launch Test Chat in New Tab
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB 4: LOCALIZATION & CURRENCY -->
            <?php if ($activeTab === 'localization'): ?>
                <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04); max-width:880px;">
                    <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                            <span style="color:var(--brand-teal);">🌐</span> Currency, Regional Formatting &amp; Timezone
                        </h2>
                        <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                            Configure your pricing currency symbols, formatting rules, and business operating timezone.
                        </p>
                    </div>

                    <form method="POST" action="<?= url('admin/settings/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_tab" value="localization">

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Base Currency Code (ISO 4217)
                                </label>
                                <input type="text" name="currency_code" class="form-input" required maxlength="3"
                                       value="<?= htmlspecialchars($flat['currency_code'] ?? 'INR') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; font-family:monospace; text-transform:uppercase; font-weight:700;"
                                       placeholder="INR">
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Passed to payment gateways (Razorpay, Bank Wire) during checkout.</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Currency Symbol
                                </label>
                                <input type="text" name="currency_symbol" class="form-input" required
                                       value="<?= htmlspecialchars($flat['currency_symbol'] ?? '₹') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:1.1rem; font-weight:800;"
                                       placeholder="₹">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Symbol Placement
                                </label>
                                <select name="currency_position" class="form-input"
                                        style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; background:var(--bg-surface);">
                                    <option value="before" <?= ($flat['currency_position'] ?? '') === 'before' ? 'selected' : '' ?>>Before amount (e.g. ₹ 24,999)</option>
                                    <option value="after" <?= ($flat['currency_position'] ?? '') === 'after' ? 'selected' : '' ?>>After amount (e.g. 24,999 ₹)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Decimal Precision
                                </label>
                                <select name="currency_decimals" class="form-input"
                                        style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; background:var(--bg-surface);">
                                    <option value="0" <?= ($flat['currency_decimals'] ?? '') === '0' ? 'selected' : '' ?>>No Decimals (e.g. ₹ 24,999 — Standard Luxury Fashion)</option>
                                    <option value="2" <?= ($flat['currency_decimals'] ?? '') === '2' ? 'selected' : '' ?>>2 Decimal Places (e.g. ₹ 24,999.00)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    System Timezone
                                </label>
                                <select name="timezone" class="form-input"
                                        style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; background:var(--bg-surface);">
                                    <option value="Asia/Kolkata" <?= ($flat['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata (IST - UTC+05:30)</option>
                                    <option value="UTC" <?= ($flat['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC (Universal Coordinated Time)</option>
                                    <option value="Asia/Dubai" <?= ($flat['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' ?>>Asia/Dubai (GST - UTC+04:00)</option>
                                    <option value="Europe/London" <?= ($flat['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>Europe/London (GMT/BST - UTC+00:00)</option>
                                    <option value="America/New_York" <?= ($flat['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New_York (EST - UTC-05:00)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Calendar Date Format
                                </label>
                                <select name="date_format" class="form-input"
                                        style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; background:var(--bg-surface);">
                                    <option value="d M Y" <?= ($flat['date_format'] ?? '') === 'd M Y' ? 'selected' : '' ?>>21 Sep 2026 (d M Y)</option>
                                    <option value="d/m/Y" <?= ($flat['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>21/09/2026 (d/m/Y)</option>
                                    <option value="Y-m-d" <?= ($flat['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>2026-09-21 (ISO Y-m-d)</option>
                                    <option value="M j, Y" <?= ($flat['date_format'] ?? '') === 'M j, Y' ? 'selected' : '' ?>>Sep 21, 2026 (M j, Y)</option>
                                </select>
                            </div>

                            <div class="form-group" style="grid-column:1 / -1;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Time Clock Display Format
                                </label>
                                <select name="time_format" class="form-input"
                                        style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; background:var(--bg-surface);">
                                    <option value="12h" <?= ($flat['time_format'] ?? '') === '12h' ? 'selected' : '' ?>>12-Hour Clock with AM/PM (e.g. 05:45 PM)</option>
                                    <option value="24h" <?= ($flat['time_format'] ?? '') === '24h' ? 'selected' : '' ?>>24-Hour Military Clock (e.g. 17:45)</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px; margin-top:24px;">
                            <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save Regional Formatting
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- TAB 5: MARKETING & TRACKING PIXELS (Feature 30 & Meta Pixel) -->
            <?php if ($activeTab === 'marketing'): ?>
                <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04); max-width:960px;">
                    <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                            <span style="color:var(--brand-blue);">📊</span> Tracking Pixels, Meta Ads &amp; Custom Scripts
                        </h2>
                        <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                            Activate your Meta (Facebook) Pixel ID, Google Analytics 4 Measurement ID, and custom conversion tracking header tags.
                        </p>
                    </div>

                    <form method="POST" action="<?= url('admin/settings/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_tab" value="marketing">

                        <!-- Meta Pixel Section -->
                        <div style="background:rgba(45, 130, 255, 0.04); border:1px solid rgba(45, 130, 255, 0.2); border-radius:var(--radius-md); padding:20px; margin-bottom:24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="background:#1877F2; color:#fff; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.2rem;">
                                        f
                                    </div>
                                    <div>
                                        <div style="font-weight:800; font-size:0.95rem; color:var(--text-primary);">Meta (Facebook) Pixel Retargeting</div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">Enables PageView, ViewContent, AddToCart, and Purchase telemetry for Instagram &amp; Facebook ads.</div>
                                    </div>
                                </div>

                                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.85rem; color:var(--text-primary);">
                                    <input type="checkbox" name="meta_pixel_enabled" value="1" <?= !empty($flat['meta_pixel_enabled']) ? 'checked' : '' ?> style="width:18px; height:18px;">
                                    Enable Meta Pixel
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.85rem; display:block; margin-bottom:6px;">
                                    Meta Pixel ID (15-16 Digits)
                                </label>
                                <input type="text" name="meta_pixel_id" class="form-input"
                                       value="<?= htmlspecialchars($flat['meta_pixel_id'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; font-family:monospace; letter-spacing:1px; font-weight:700;"
                                       placeholder="e.g. 987654321098765">
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Obtained from Meta Events Manager &bull; Datasets &bull; Pixel ID.</small>
                            </div>
                        </div>

                        <!-- Google Analytics 4 Section -->
                        <div style="background:rgba(245, 158, 11, 0.04); border:1px solid rgba(245, 158, 11, 0.2); border-radius:var(--radius-md); padding:20px; margin-bottom:24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="background:#EA4335; color:#fff; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem;">
                                        G
                                    </div>
                                    <div>
                                        <div style="font-weight:800; font-size:0.95rem; color:var(--text-primary);">Google Analytics 4 (GA4) &amp; Tag Manager</div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">Real-time audience tracking, traffic acquisition sources, and conversion funnels.</div>
                                    </div>
                                </div>

                                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.85rem; color:var(--text-primary);">
                                    <input type="checkbox" name="ga4_enabled" value="1" <?= !empty($flat['ga4_enabled']) ? 'checked' : '' ?> style="width:18px; height:18px;">
                                    Enable GA4
                                </label>
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                <div class="form-group">
                                    <label class="form-label" style="font-weight:700; font-size:0.85rem; display:block; margin-bottom:6px;">
                                        GA4 Measurement ID
                                    </label>
                                    <input type="text" name="ga4_measurement_id" class="form-input"
                                           value="<?= htmlspecialchars($flat['ga4_measurement_id'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; font-family:monospace; letter-spacing:1px;"
                                           placeholder="e.g. G-JYJ9988776">
                                </div>

                                <div class="form-group">
                                    <label class="form-label" style="font-weight:700; font-size:0.85rem; display:block; margin-bottom:6px;">
                                        Google Tag Manager (GTM) Container ID
                                    </label>
                                    <input type="text" name="gtm_container_id" class="form-input"
                                           value="<?= htmlspecialchars($flat['gtm_container_id'] ?? '') ?>"
                                           style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem; font-family:monospace; letter-spacing:1px;"
                                           placeholder="e.g. GTM-JYJLX01">
                                </div>
                            </div>
                        </div>

                        <!-- Custom Header & Footer Script Injections -->
                        <div style="margin-bottom:20px;">
                            <div class="form-group" style="margin-bottom:18px;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Custom Header Scripts (Injected inside &lt;head&gt;)
                                </label>
                                <textarea name="header_custom_scripts" rows="3" class="form-input"
                                          style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-family:monospace; font-size:0.82rem; background:#0F172A; color:#38BDF8;"
                                          placeholder="<!-- e.g. <meta name='pinterest' content='...'> or verification tags -->"><?= htmlspecialchars($flat['header_custom_scripts'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Custom Body Scripts (Injected before &lt;/body&gt;)
                                </label>
                                <textarea name="footer_custom_scripts" rows="3" class="form-input"
                                          style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-family:monospace; font-size:0.82rem; background:#0F172A; color:#A78BFA;"
                                          placeholder="<!-- e.g. third-party concierge scripts -->"><?= htmlspecialchars($flat['footer_custom_scripts'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px;">
                            <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save Pixel &amp; Marketing Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- TAB 6: SOCIAL MEDIA & CHANNELS -->
            <?php if ($activeTab === 'social'): ?>
                <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04); max-width:880px;">
                    <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                            <span style="color:#db2777;">💖</span> Social Media &amp; Community Channels
                        </h2>
                        <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                            Connect your official fashion house social media channels to activate storefront follow links and footer icons.
                        </p>
                    </div>

                    <form method="POST" action="<?= url('admin/settings/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_tab" value="social">

                        <div style="display:flex; flex-direction:column; gap:18px;">
                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Instagram Profile URL
                                </label>
                                <input type="url" name="social_instagram" class="form-input"
                                       value="<?= htmlspecialchars($flat['social_instagram'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="https://instagram.com/jiyajicollection">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Facebook Brand Page URL
                                </label>
                                <input type="url" name="social_facebook" class="form-input"
                                       value="<?= htmlspecialchars($flat['social_facebook'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="https://facebook.com/jiyajicollection">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    YouTube Runway &amp; Artisan Channel URL
                                </label>
                                <input type="url" name="social_youtube" class="form-input"
                                       value="<?= htmlspecialchars($flat['social_youtube'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="https://youtube.com/@jiyajicollection">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Pinterest Bridal Lookbook URL
                                </label>
                                <input type="url" name="social_pinterest" class="form-input"
                                       value="<?= htmlspecialchars($flat['social_pinterest'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="https://pinterest.com/jiyajicollection">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    X / Twitter Profile URL
                                </label>
                                <input type="url" name="social_twitter" class="form-input"
                                       value="<?= htmlspecialchars($flat['social_twitter'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="https://twitter.com/jiyajicollection">
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px; margin-top:24px;">
                            <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save Social Channels
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- TAB 7: STORE OPERATIONS & MAINTENANCE -->
            <?php if ($activeTab === 'operations'): ?>
                <div class="admin-card" style="padding:28px; background:var(--bg-surface); border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:0 2px 8px rgba(0,0,0,0.04); max-width:960px;">
                    <div style="margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:16px;">
                        <h2 style="font-size:1.25rem; font-weight:800; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                            <span style="color:var(--status-warning);">🛡️</span> Store Operations &amp; Atelier Controls
                        </h2>
                        <p style="font-size:0.85rem; color:var(--text-secondary); margin:0;">
                            Emergency atelier maintenance toggles, order intake states, inventory warning thresholds, and order alert routing.
                        </p>
                    </div>

                    <form method="POST" action="<?= url('admin/settings/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_tab" value="operations">

                        <!-- Maintenance Mode Switch -->
                        <div style="background:<?= !empty($flat['maintenance_mode']) ? 'rgba(245, 158, 11, 0.12)' : 'rgba(248, 250, 252, 0.8)' ?>; border:1px solid <?= !empty($flat['maintenance_mode']) ? '#F59E0B' : 'var(--border-color)' ?>; border-radius:var(--radius-md); padding:20px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; transition:var(--transition);">
                            <div>
                                <div style="font-weight:800; font-size:1rem; color:<?= !empty($flat['maintenance_mode']) ? '#B45309' : 'var(--text-primary)' ?>; display:flex; align-items:center; gap:8px;">
                                    <span>Maintenance Mode (Atelier Refresh)</span>
                                    <?php if (!empty($flat['maintenance_mode'])): ?>
                                        <span style="background:#F59E0B; color:#fff; font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:999px;">ACTIVE</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:4px;">
                                    When enabled, public storefront visitors will see a dignified maintenance announcement. Admin portal remains accessible.
                                </div>
                            </div>

                            <label class="switch" style="position:relative; display:inline-block; width:52px; height:28px;">
                                <input type="checkbox" name="maintenance_mode" value="1" <?= !empty($flat['maintenance_mode']) ? 'checked' : '' ?> style="opacity:0; width:0; height:0;">
                                <span class="slider round" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:<?= !empty($flat['maintenance_mode']) ? '#F59E0B' : '#cbd5e1' ?>; transition:.3s; border-radius:34px;">
                                    <span style="position:absolute; content:''; height:22px; width:22px; left:<?= !empty($flat['maintenance_mode']) ? '27px' : '3px' ?>; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>
                                </span>
                            </label>
                        </div>

                        <!-- Maintenance Notice Text -->
                        <div class="form-group" style="margin-bottom:24px;">
                            <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                Public Maintenance Screen Announcement Message
                            </label>
                            <textarea name="maintenance_message" rows="3" class="form-input"
                                      style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.9rem;"
                                      placeholder="Notice displayed during maintenance..."><?= htmlspecialchars($flat['maintenance_message'] ?? '') ?></textarea>
                        </div>

                        <!-- Order Acceptance Switch -->
                        <div style="background:var(--bg-page); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
                            <div>
                                <div style="font-weight:700; font-size:0.95rem; color:var(--text-primary);">Accept Online Orders &amp; Checkout</div>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">Turn off if you need to temporarily freeze checkout intake during peak royal festival season.</div>
                            </div>
                            <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.88rem;">
                                <input type="checkbox" name="order_acceptance" value="1" <?= !empty($flat['order_acceptance']) ? 'checked' : '' ?> style="width:20px; height:20px;">
                                Accepting Orders
                            </label>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Global Low Stock Warning Threshold
                                </label>
                                <input type="number" name="low_stock_threshold" min="1" max="100" class="form-input"
                                       value="<?= htmlspecialchars($flat['low_stock_threshold'] ?? '5') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem; font-weight:700;"
                                       placeholder="5">
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Products with inventory at or below this amount trigger critical warnings in admin stock alerts.</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Minimum Order Checkout Value (₹)
                                </label>
                                <input type="number" name="min_order_value" min="0" class="form-input"
                                       value="<?= htmlspecialchars($flat['min_order_value'] ?? '0') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.95rem;"
                                       placeholder="0 for no minimum">
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Minimum cart subtotal required to proceed to payment.</small>
                            </div>

                            <div class="form-group" style="grid-column:1 / -1;">
                                <label class="form-label" style="font-weight:700; font-size:0.88rem; display:block; margin-bottom:6px;">
                                    Admin Order Dispatch Alert Email
                                </label>
                                <input type="email" name="admin_order_notification_email" class="form-input"
                                       value="<?= htmlspecialchars($flat['admin_order_notification_email'] ?? '') ?>"
                                       style="width:100%; padding:11px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.92rem;"
                                       placeholder="alerts@jiyajicollection.com">
                                <small style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Sends immediate notification emails whenever a customer places an order.</small>
                            </div>

                            <div class="form-group" style="grid-column:1 / -1;">
                                <label style="display:inline-flex; align-items:center; gap:10px; cursor:pointer;">
                                    <input type="checkbox" name="allow_guest_checkout" value="1" <?= !empty($flat['allow_guest_checkout']) ? 'checked' : '' ?> style="width:18px; height:18px;">
                                    <span style="font-weight:700; font-size:0.88rem; color:var(--text-primary);">Allow Guest Checkout Without Compulsory Registration</span>
                                </label>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border-color); padding-top:20px; margin-top:24px;">
                            <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:12px 28px; font-weight:700; border-radius:var(--radius-md);">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save Operational Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- ============================================================ -->
<!-- 5. JAVASCRIPT LOGIC & DYNAMIC SIMULATOR                     -->
<!-- ============================================================ -->
<script>
/**
 * Real-time Interactive WhatsApp Simulator Updater
 */
function updateSimulator() {
    const container = document.getElementById('simFloatingContainer');
    const bubble = document.getElementById('simBubble');
    const bubbleHeading = document.getElementById('simBubbleHeading');
    const bubbleText = document.getElementById('simBubbleText');
    const button = document.getElementById('simButton');

    if (!container) return;

    // 1. WhatsApp Enabled
    const enabledInput = document.querySelector('input[name="whatsapp_enabled"]');
    const isEnabled = enabledInput ? enabledInput.checked : true;
    container.style.display = isEnabled ? 'flex' : 'none';

    // 2. Position
    const posSelect = document.getElementById('inputWhatsappPosition');
    const pos = posSelect ? posSelect.value : 'bottom-right';
    if (pos === 'bottom-left') {
        container.style.left = '16px';
        container.style.right = 'auto';
        container.style.alignItems = 'flex-start';
    } else {
        container.style.right = '16px';
        container.style.left = 'auto';
        container.style.alignItems = 'flex-end';
    }

    // 3. Bubble Enabled
    const bubbleCheck = document.getElementById('inputWhatsappBubbleEnabled');
    const isBubbleOn = bubbleCheck ? bubbleCheck.checked : true;
    if (bubble) {
        bubble.style.display = isBubbleOn ? 'block' : 'none';
    }

    // 4. Texts
    const headingInput = document.getElementById('inputWhatsappHeading');
    if (bubbleHeading && headingInput) {
        bubbleHeading.textContent = headingInput.value.trim() || 'Need Styling Advice?';
    }

    const subtextInput = document.getElementById('inputWhatsappSubtext');
    if (bubbleText && subtextInput) {
        bubbleText.textContent = subtextInput.value.trim() || 'Connect with our wedding couture specialist.';
    }

    const titleInput = document.getElementById('inputWhatsappTitle');
    if (button && titleInput) {
        button.title = titleInput.value.trim() || 'Chat with Stylist';
    }
}

/**
 * Launch WhatsApp direct link in new tab with pre-filled greeting message.
 */
function testWhatsAppConcierge() {
    const numInput = document.getElementById('inputWhatsappNumber');
    let phone = numInput ? numInput.value.trim() : '<?= htmlspecialchars($flat['whatsapp_number'] ?? '+919876543210') ?>';
    phone = phone.replace(/[^0-9]/g, '');

    const msgInput = document.getElementById('inputWhatsappMessage');
    const msg = msgInput ? msgInput.value.trim() : '<?= addslashes($flat['whatsapp_default_message'] ?? "Namaste Jiyaji Concierge!") ?>';

    if (!phone) {
        alert('Please specify a valid WhatsApp business phone number first.');
        return;
    }

    const waUrl = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(msg);
    window.open(waUrl, '_blank');
}
</script>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
.settings-tab-btn:hover {
    background: rgba(45, 130, 255, 0.08) !important;
    color: var(--brand-blue) !important;
}
.settings-tab-btn.active:hover {
    background: var(--gradient-primary) !important;
    color: #fff !important;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
