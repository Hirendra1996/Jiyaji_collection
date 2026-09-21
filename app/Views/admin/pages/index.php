<?php
use App\Models\Page;
use App\Models\Staff;

include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$activeTab = $activeTab ?? 'pages';
$pagesList = $pagesData['pages'] ?? [];
$pagination = $pagesData['pagination'] ?? ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false];
$enqList = $enquiriesData['enquiries'] ?? [];
$enqPagination = $enquiriesData['pagination'] ?? ['total' => 0, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false];
$isEditing = !empty($editingPage);
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
                        <span>Content Management</span>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Static CMS Pages</span>
                    </div>
                    <h1 class="welcome-title">Static CMS Pages &amp; Brand Content</h1>
                    <p class="welcome-subtitle">Administer brand storytelling, legal compliance disclosures, customer care policies, SEO metadata, and moderate incoming customer inquiries.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Public Storefront Preview -->
                    <a href="<?= url('about-us') ?>" target="_blank"
                       style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); text-decoration:none; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:var(--transition);"
                       onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                       onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        View Storefront
                    </a>

                    <!-- Add New Page Trigger -->
                    <a href="<?= url('admin/pages?tab=editor') ?>"
                       style="display:inline-flex; align-items:center; gap:8px; background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); text-decoration:none; border:none; cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                       onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                       onmouseout="this.style.opacity='1'; this.style.transform=''">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        + Create New Page
                    </a>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- EXECUTIVE KPIS SUMMARY CARDS (5 CARDS)                       -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
                <!-- KPI 1: Total CMS Pages -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:var(--brand-blue);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <div class="kpi-label">Total CMS Pages</div>
                    <div class="kpi-val"><?= (int)($kpis['total_pages'] ?? 0) ?> Documents</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Published articles &amp; legal policies
                    </div>
                </div>

                <!-- KPI 2: Published & Live -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--status-success);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    </div>
                    <div class="kpi-label">Live on Storefront</div>
                    <div class="kpi-val"><?= (int)($kpis['published_pages'] ?? 0) ?> Active</div>
                    <div class="kpi-subtext" style="color:var(--status-success); font-weight:600;">
                        Accessible to public clients
                    </div>
                </div>

                <!-- KPI 3: Draft Pages -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </div>
                    <div class="kpi-label">Draft / Hidden</div>
                    <div class="kpi-val"><?= (int)($kpis['draft_pages'] ?? 0) ?> Drafts</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        Unpublished revisions
                    </div>
                </div>

                <!-- KPI 4: System Baselines -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(139, 92, 246, 0.1); color:var(--brand-purple);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    </div>
                    <div class="kpi-label">System Baselines</div>
                    <div class="kpi-val"><?= (int)($kpis['system_pages'] ?? 0) ?> Core</div>
                    <div class="kpi-subtext" style="color:var(--text-muted);">
                        <?= (int)($kpis['custom_pages'] ?? 0) ?> Custom Editorial Pages
                    </div>
                </div>

                <!-- KPI 5: Customer Inquiries -->
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:rgba(236, 72, 153, 0.1); color:#ec4899;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div class="kpi-label">Client Inquiries</div>
                    <div class="kpi-val"><?= (int)($kpis['total_enquiries'] ?? 0) ?> Inquiries</div>
                    <div class="kpi-subtext" style="color:<?= ($kpis['unread_enquiries'] ?? 0) > 0 ? '#ef4444' : 'var(--text-muted)' ?>; font-weight:<?= ($kpis['unread_enquiries'] ?? 0) > 0 ? '700' : 'normal' ?>;">
                        <?= (int)($kpis['unread_enquiries'] ?? 0) ?> Awaiting Response
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SUB-TABS NAVIGATION CONTROLS                                 -->
            <!-- ============================================================ -->
            <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:24px; gap:8px; overflow-x:auto;">
                <a href="<?= url('admin/pages?tab=pages') ?>"
                   class="tab-btn <?= $activeTab === 'pages' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'pages' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'pages' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Pages Directory (<?= $pagination['total'] ?>)
                </a>

                <a href="<?= url('admin/pages?tab=editor' . ($isEditing ? "&id={$editingPage['id']}" : '')) ?>"
                   class="tab-btn <?= $activeTab === 'editor' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'editor' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'editor' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <?= $isEditing ? 'Edit Page: ' . htmlspecialchars($editingPage['title']) : 'Create New Page' ?>
                </a>

                <a href="<?= url('admin/pages?tab=enquiries') ?>"
                   class="tab-btn <?= $activeTab === 'enquiries' ? 'active' : '' ?>"
                   style="display:inline-flex; align-items:center; gap:8px; padding:12px 18px; font-size:0.9rem; font-weight:600; text-decoration:none; border-bottom:2px solid <?= $activeTab === 'enquiries' ? 'var(--brand-blue)' : 'transparent' ?>; color:<?= $activeTab === 'enquiries' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; transition:var(--transition);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Client Inquiries Inbox (<?= $kpis['total_enquiries'] ?? 0 ?>)
                    <?php if (!empty($kpis['unread_enquiries'])): ?>
                        <span style="background:#ef4444; color:#fff; font-size:0.68rem; font-weight:700; padding:1px 6px; border-radius:999px;">
                            <?= $kpis['unread_enquiries'] ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: PAGES DIRECTORY                                       -->
            <!-- ============================================================ -->
            <?php if ($activeTab === 'pages'): ?>
                <!-- Search & Filters Toolbar -->
                <div class="card" style="padding:16px 20px; margin-bottom:20px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                    <form method="GET" action="<?= url('admin/pages') ?>" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <input type="hidden" name="tab" value="pages">

                        <!-- Search text -->
                        <div style="flex:1; min-width:220px; position:relative;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by title, slug, or content excerpt..."
                                   style="width:100%; padding:9px 12px 9px 36px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                        </div>

                        <!-- Status filter -->
                        <div style="min-width:150px;">
                            <select name="status" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                                <option value="all">All Statuses</option>
                                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Published / Active</option>
                                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Draft / Hidden</option>
                            </select>
                        </div>

                        <!-- Type filter -->
                        <div style="min-width:150px;">
                            <select name="type" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                                <option value="all">All Types</option>
                                <option value="system" <?= ($filters['type'] ?? '') === 'system' ? 'selected' : '' ?>>Built-in System</option>
                                <option value="custom" <?= ($filters['type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Editorial</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <button type="submit" style="padding:9px 18px; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-md); font-weight:600; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Filter
                        </button>

                        <?php if (!empty($filters['search']) || ($filters['status'] ?? 'all') !== 'all' || ($filters['type'] ?? 'all') !== 'all'): ?>
                            <a href="<?= url('admin/pages?tab=pages') ?>" style="padding:9px 14px; background:var(--bg-surface-secondary); color:var(--text-muted); border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.88rem; text-decoration:none;">
                                Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Pages Table Card -->
                <div class="card" style="padding:0; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
                            <thead>
                                <tr style="background:var(--bg-surface-secondary); border-bottom:1px solid var(--border-color); color:var(--text-secondary); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                                    <th style="padding:14px 20px; font-weight:600;">Page Title &amp; Excerpt</th>
                                    <th style="padding:14px 16px; font-weight:600;">URL Slug</th>
                                    <th style="padding:14px 16px; font-weight:600;">Word Count</th>
                                    <th style="padding:14px 16px; font-weight:600;">Status</th>
                                    <th style="padding:14px 16px; font-weight:600;">Last Modified</th>
                                    <th style="padding:14px 20px; font-weight:600; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pagesList)): ?>
                                    <tr>
                                        <td colspan="6" style="padding:48px 20px; text-align:center; color:var(--text-muted);">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px; opacity:0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            <div style="font-weight:600; color:var(--text-primary); font-size:0.95rem; margin-bottom:4px;">No CMS Pages Found</div>
                                            <div style="font-size:0.82rem;">Try modifying your search or click "+ Create New Page" to publish fresh editorial content.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pagesList as $p): ?>
                                        <tr style="border-bottom:1px solid var(--border-color); transition:background 0.15s;" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                            <!-- Page Title & Excerpt -->
                                            <td style="padding:14px 20px; max-width:320px;">
                                                <div style="display:flex; align-items:flex-start; gap:10px;">
                                                    <div style="width:34px; height:34px; border-radius:var(--radius-sm); background:<?= $p['is_system'] ? 'rgba(59, 130, 246, 0.1)' : 'rgba(245, 158, 11, 0.1)' ?>; color:<?= $p['is_system'] ? 'var(--brand-blue)' : '#d97706' ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                                                        <?php if ($p['is_system']): ?>
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                                        <?php else: ?>
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight:600; color:var(--text-primary); margin-bottom:2px; display:flex; align-items:center; gap:6px;">
                                                            <?= e($p['title']) ?>
                                                            <?php if ($p['is_system']): ?>
                                                                <span style="font-size:0.65rem; background:rgba(59, 130, 246, 0.12); color:var(--brand-blue); padding:1px 6px; border-radius:999px; font-weight:700;">System</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="font-size:0.78rem; color:var(--text-muted); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                                            <?= e($p['excerpt'] ?: strip_tags(substr($p['content'], 0, 120)) . '...') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- URL Slug -->
                                            <td style="padding:14px 16px;">
                                                <div style="display:inline-flex; align-items:center; gap:4px; font-family:monospace; font-size:0.8rem; background:var(--bg-surface-secondary); padding:3px 8px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                                                    <a href="<?= url($p['slug']) ?>" target="_blank" style="color:var(--brand-blue); text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Open live public page">
                                                        /<?= e($p['slug']) ?>
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                    </a>
                                                </div>
                                            </td>

                                            <!-- Word Count -->
                                            <td style="padding:14px 16px; color:var(--text-secondary); font-size:0.82rem;">
                                                <div style="font-weight:600; color:var(--text-primary);"><?= number_format($p['word_count']) ?> words</div>
                                                <div style="font-size:0.72rem; color:var(--text-muted);">~<?= ceil($p['word_count'] / 200) ?> min read</div>
                                            </td>

                                            <!-- Status -->
                                            <td style="padding:14px 16px;">
                                                <?php if ($p['is_active']): ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(16, 185, 129, 0.12); color:var(--status-success); font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:999px;">
                                                        <span style="width:6px; height:6px; border-radius:50%; background:var(--status-success);"></span>
                                                        Published
                                                    </span>
                                                <?php else: ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(245, 158, 11, 0.12); color:#d97706; font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:999px;">
                                                        <span style="width:6px; height:6px; border-radius:50%; background:#d97706;"></span>
                                                        Draft / Hidden
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Last Modified -->
                                            <td style="padding:14px 16px; color:var(--text-secondary); font-size:0.82rem;">
                                                <?php $updTs = validTs($p['updated_at']); ?>
                                                <div style="font-weight:500; color:var(--text-primary);"><?= $updTs ? date('M d, Y', $updTs) : '—' ?></div>
                                                <div style="font-size:0.73rem; color:var(--text-muted);">by <?= e($p['updated_by_name']) ?></div>
                                            </td>

                                            <!-- Actions -->
                                            <td style="padding:14px 20px; text-align:right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">
                                                    <!-- Live Preview Modal Button -->
                                                    <button type="button" class="btn-icon" title="Quick Preview"
                                                            onclick="openPreviewModal(<?= (int)$p['id'] ?>)"
                                                            style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-primary); cursor:pointer; transition:var(--transition);"
                                                            onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                                                            onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    </button>

                                                    <!-- Edit Button -->
                                                    <a href="<?= url('admin/pages?tab=editor&id=' . $p['id']) ?>" class="btn-icon" title="Edit Page Content"
                                                       style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-primary); text-decoration:none; transition:var(--transition);"
                                                       onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                                                       onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </a>

                                                    <!-- Toggle Status Form -->
                                                    <form method="POST" action="<?= url('admin/pages/toggle') ?>" style="display:inline;" onsubmit="return confirm('Change status of <?= addslashes($p['title']) ?> to <?= $p['is_active'] ? 'Draft' : 'Published' ?>?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="is_active" value="<?= $p['is_active'] ? '0' : '1' ?>">
                                                        <button type="submit" class="btn-icon" title="<?= $p['is_active'] ? 'Unpublish (Move to Drafts)' : 'Publish Live' ?>"
                                                                style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:<?= $p['is_active'] ? 'var(--status-warning)' : 'var(--status-success)' ?>; cursor:pointer; transition:var(--transition);"
                                                                onmouseover="this.style.borderColor='currentColor'"
                                                                onmouseout="this.style.borderColor='var(--border-color)'">
                                                            <?php if ($p['is_active']): ?>
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                            <?php else: ?>
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                            <?php endif; ?>
                                                        </button>
                                                    </form>

                                                    <!-- Delete Button Form (Only Custom Pages) -->
                                                    <?php if (!$p['is_system']): ?>
                                                        <form method="POST" action="<?= url('admin/pages/delete') ?>" style="display:inline;" onsubmit="return confirm('Permanently delete custom page <?= addslashes($p['title']) ?>?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                            <button type="submit" class="btn-icon" title="Delete Custom Page"
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
                                Showing <?= count($pagesList) ?> of <?= $pagination['total'] ?> total CMS pages
                            </div>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <?php if ($pagination['has_prev']): ?>
                                    <a href="<?= url('admin/pages?tab=pages&page=' . ($pagination['current_page'] - 1) . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (($filters['status'] ?? 'all') !== 'all' ? '&status=' . $filters['status'] : '') . (($filters['type'] ?? 'all') !== 'all' ? '&type=' . $filters['type'] : '')) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); text-decoration:none; color:var(--text-primary); background:var(--bg-surface);">
                                        &larr; Prev
                                    </a>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                    <a href="<?= url('admin/pages?tab=pages&page=' . $p . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (($filters['status'] ?? 'all') !== 'all' ? '&status=' . $filters['status'] : '') . (($filters['type'] ?? 'all') !== 'all' ? '&type=' . $filters['type'] : '')) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid <?= $p === $pagination['current_page'] ? 'var(--brand-blue)' : 'var(--border-color)' ?>; border-radius:var(--radius-sm); text-decoration:none; color:<?= $p === $pagination['current_page'] ? '#fff' : 'var(--text-primary)' ?>; background:<?= $p === $pagination['current_page'] ? 'var(--brand-blue)' : 'var(--bg-surface)' ?>;">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <a href="<?= url('admin/pages?tab=pages&page=' . ($pagination['current_page'] + 1) . (!empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '') . (($filters['status'] ?? 'all') !== 'all' ? '&status=' . $filters['status'] : '') . (($filters['type'] ?? 'all') !== 'all' ? '&type=' . $filters['type'] : '')) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); text-decoration:none; color:var(--text-primary); background:var(--bg-surface);">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- ============================================================ -->
            <!-- TAB 2: PAGE EDITOR (CREATE / EDIT)                           -->
            <!-- ============================================================ -->
            <?php elseif ($activeTab === 'editor'): ?>
                <div class="card" style="padding:24px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); margin-bottom:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border-color);">
                        <div>
                            <h2 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-bottom:4px;">
                                <?= $isEditing ? 'Editing Page: ' . e($editingPage['title']) : 'Create New Static Page' ?>
                            </h2>
                            <p style="font-size:0.85rem; color:var(--text-muted);">
                                <?= $isEditing ? ($editingPage['is_system'] ? 'Built-in system page with protected slug. Customise the editorial content and SEO metadata below.' : 'Custom page. Update editorial content, SEO metadata, and slug.') : 'Author fresh brand story, lookbook announcement, or legal document.' ?>
                            </p>
                        </div>
                        <?php if ($isEditing): ?>
                            <a href="<?= url($editingPage['slug']) ?>" target="_blank"
                               style="display:inline-flex; align-items:center; gap:6px; font-size:0.85rem; font-weight:600; color:var(--brand-blue); text-decoration:none;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Open Live Page
                            </a>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?= url('admin/pages/save') ?>" id="pageEditorForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $isEditing ? $editingPage['id'] : 0 ?>">

                        <!-- Title & Slug Row -->
                        <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:18px;">
                            <div>
                                <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Page Title *</label>
                                <input type="text" name="title" id="pageTitleInput" required value="<?= e($editingPage['title'] ?? '') ?>" placeholder="e.g. Master Artisans &amp; Handloom Guild"
                                       style="width:100%; padding:10px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.95rem; font-weight:600;">
                            </div>

                            <div>
                                <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">
                                    URL Slug *
                                    <?php if ($isEditing && $editingPage['is_system']): ?>
                                        <span style="font-size:0.7rem; color:var(--brand-blue); font-weight:normal;">(System Protected)</span>
                                    <?php endif; ?>
                                </label>
                                <div style="display:flex; align-items:center; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); overflow:hidden;">
                                    <span style="padding:10px 10px 10px 12px; font-size:0.82rem; color:var(--text-muted); background:rgba(0,0,0,0.03); border-right:1px solid var(--border-color);">/</span>
                                    <input type="text" name="slug" id="pageSlugInput" value="<?= e($editingPage['slug'] ?? '') ?>" placeholder="artisans-handloom"
                                           <?= ($isEditing && $editingPage['is_system']) ? 'readonly style="width:100%; padding:10px 12px; border:none; background:transparent; color:var(--text-muted); font-size:0.85rem; font-family:monospace; cursor:not-allowed;"' : 'style="width:100%; padding:10px 12px; border:none; background:transparent; color:var(--text-primary); font-size:0.85rem; font-family:monospace;"' ?>>
                                </div>
                            </div>
                        </div>

                        <!-- Excerpt / Header Summary -->
                        <div style="margin-bottom:18px;">
                            <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--text-primary); margin-bottom:6px;">Page Subtitle &amp; Lead Excerpt</label>
                            <input type="text" name="excerpt" value="<?= e($editingPage['excerpt'] ?? '') ?>" placeholder="Brief compelling summary displayed in page headers and social cards..."
                                   style="width:100%; padding:10px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                        </div>

                        <!-- Formatting Toolbar & Content Area -->
                        <div style="margin-bottom:24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <label style="font-size:0.82rem; font-weight:600; color:var(--text-primary);">Editorial Body Content (HTML &amp; Rich Text) *</label>
                                <div style="display:flex; gap:6px;">
                                    <button type="button" onclick="formatContent('h2')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer;">H2</button>
                                    <button type="button" onclick="formatContent('h3')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer;">H3</button>
                                    <button type="button" onclick="formatContent('b')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer; font-weight:700;">B</button>
                                    <button type="button" onclick="formatContent('i')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer; font-style:italic;">I</button>
                                    <button type="button" onclick="formatContent('blockquote')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer;">Quote</button>
                                    <button type="button" onclick="formatContent('ul')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer;">Bullet List</button>
                                    <button type="button" onclick="formatContent('hr')" style="padding:3px 8px; font-size:0.75rem; background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-sm); color:var(--text-primary); cursor:pointer;">Line</button>
                                </div>
                            </div>
                            <textarea name="content" id="pageContentArea" rows="16" required placeholder="Write your page content using clean HTML or standard text..."
                                      style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.9rem; font-family:monospace; line-height:1.5; resize:vertical;"><?= htmlspecialchars($editingPage['content'] ?? '') ?></textarea>
                        </div>

                        <!-- SEO Metadata Accordion / Card -->
                        <div style="background:var(--bg-surface-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:24px;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--brand-blue);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <h4 style="font-size:0.92rem; font-weight:700; color:var(--text-primary); margin:0;">Search Engine Optimization (SEO) Metadata</h4>
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px;">
                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--text-primary); margin-bottom:4px;">SEO Title Tag</label>
                                    <input type="text" name="meta_title" value="<?= e($editingPage['meta_title'] ?? '') ?>" placeholder="e.g. Master Artisans | Jiyaji LX Royal Couture"
                                           style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-surface); color:var(--text-primary); font-size:0.85rem;">
                                    <span style="font-size:0.72rem; color:var(--text-muted); display:block; margin-top:3px;">Recommended length: 50–60 characters.</span>
                                </div>

                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--text-primary); margin-bottom:4px;">SEO Keywords</label>
                                    <input type="text" name="meta_keywords" value="<?= e($editingPage['meta_keywords'] ?? '') ?>" placeholder="ethnic wear, royal sherwani, jaipur couture"
                                           style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-surface); color:var(--text-primary); font-size:0.85rem;">
                                    <span style="font-size:0.72rem; color:var(--text-muted); display:block; margin-top:3px;">Comma-separated relevant search terms.</span>
                                </div>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--text-primary); margin-bottom:4px;">Meta Description</label>
                                <textarea name="meta_description" rows="2" placeholder="Search engine snippet describing this page in 150-160 characters..."
                                          style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-surface); color:var(--text-primary); font-size:0.85rem; resize:vertical;"><?= e($editingPage['meta_description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Status & Submit Bar -->
                        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:16px; border-top:1px solid var(--border-color); flex-wrap:wrap; gap:12px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <label style="display:flex; align-items:center; gap:8px; font-size:0.88rem; font-weight:600; color:var(--text-primary); cursor:pointer;">
                                    <input type="checkbox" name="is_active" value="1" <?= (!$isEditing || $editingPage['is_active']) ? 'checked' : '' ?> style="width:16px; height:16px; cursor:pointer;">
                                    <span>Publish Page Live on Storefront</span>
                                </label>
                            </div>

                            <div style="display:flex; gap:10px;">
                                <a href="<?= url('admin/pages?tab=pages') ?>"
                                   style="padding:10px 18px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface); color:var(--text-primary); font-size:0.88rem; font-weight:600; text-decoration:none;">
                                    Cancel
                                </a>
                                <button type="submit"
                                        style="padding:10px 24px; border:none; border-radius:var(--radius-md); background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; cursor:pointer; box-shadow:var(--shadow-glow-blue);">
                                    <?= $isEditing ? 'Save Page Changes' : 'Publish Page' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            <!-- ============================================================ -->
            <!-- TAB 3: CUSTOMER INQUIRIES INBOX                              -->
            <!-- ============================================================ -->
            <?php elseif ($activeTab === 'enquiries'): ?>
                <!-- Search & Filters -->
                <div class="card" style="padding:16px 20px; margin-bottom:20px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg);">
                    <form method="GET" action="<?= url('admin/pages') ?>" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <input type="hidden" name="tab" value="enquiries">

                        <div style="flex:1; min-width:240px; position:relative;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" name="enq_search" value="<?= e($enqSearch) ?>" placeholder="Search by client name, email, phone, or subject..."
                                   style="width:100%; padding:9px 12px 9px 36px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                        </div>

                        <div style="min-width:160px;">
                            <select name="enq_status" style="width:100%; padding:9px 12px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); color:var(--text-primary); font-size:0.88rem;">
                                <option value="all">All Inquiries</option>
                                <option value="unread" <?= $enqStatus === 'unread' ? 'selected' : '' ?>>Unread Only</option>
                                <option value="read" <?= $enqStatus === 'read' ? 'selected' : '' ?>>Read Only</option>
                            </select>
                        </div>

                        <button type="submit" style="padding:9px 18px; background:var(--brand-blue); color:#fff; border:none; border-radius:var(--radius-md); font-weight:600; font-size:0.88rem; cursor:pointer;">
                            Filter
                        </button>

                        <?php if ($enqSearch !== '' || $enqStatus !== 'all'): ?>
                            <a href="<?= url('admin/pages?tab=enquiries') ?>" style="padding:9px 14px; background:var(--bg-surface-secondary); color:var(--text-muted); border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.88rem; text-decoration:none;">
                                Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Inquiries Table Card -->
                <div class="card" style="padding:0; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
                            <thead>
                                <tr style="background:var(--bg-surface-secondary); border-bottom:1px solid var(--border-color); color:var(--text-secondary); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                                    <th style="padding:14px 20px; font-weight:600;">Client Details</th>
                                    <th style="padding:14px 16px; font-weight:600;">Subject &amp; Message</th>
                                    <th style="padding:14px 16px; font-weight:600;">Status</th>
                                    <th style="padding:14px 16px; font-weight:600;">Received At</th>
                                    <th style="padding:14px 20px; font-weight:600; text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($enqList)): ?>
                                    <tr>
                                        <td colspan="5" style="padding:48px 20px; text-align:center; color:var(--text-muted);">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px; opacity:0.5;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            <div style="font-weight:600; color:var(--text-primary); font-size:0.95rem; margin-bottom:4px;">No Customer Inquiries</div>
                                            <div style="font-size:0.82rem;">Messages submitted via the public Contact Us page will arrive directly in this inbox.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($enqList as $enq): ?>
                                        <tr style="border-bottom:1px solid var(--border-color); background:<?= empty($enq['is_read']) ? 'rgba(59, 130, 246, 0.03)' : 'transparent' ?>; transition:background 0.15s;" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='<?= empty($enq['is_read']) ? 'rgba(59, 130, 246, 0.03)' : 'transparent' ?>'">
                                            <!-- Client Details -->
                                            <td style="padding:14px 20px;">
                                                <div style="font-weight:<?= empty($enq['is_read']) ? '700' : '600' ?>; color:var(--text-primary);">
                                                    <?= e($enq['name']) ?>
                                                </div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">
                                                    <a href="mailto:<?= e($enq['email']) ?>" style="color:var(--brand-blue); text-decoration:none;"><?= e($enq['email']) ?></a>
                                                    <?php if (!empty($enq['phone'])): ?>
                                                        <span>&bull;</span>
                                                        <span><?= e($enq['phone']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <!-- Subject & Message -->
                                            <td style="padding:14px 16px; max-width:380px;">
                                                <div style="font-weight:600; color:var(--text-primary); margin-bottom:3px;">
                                                    <?= e($enq['subject'] ?: 'General Inquiry') ?>
                                                </div>
                                                <div style="font-size:0.82rem; color:var(--text-secondary); line-height:1.45;">
                                                    <?= nl2br(e($enq['message'])) ?>
                                                </div>
                                            </td>

                                            <!-- Status -->
                                            <td style="padding:14px 16px;">
                                                <?php if (empty($enq['is_read'])): ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(239, 68, 68, 0.12); color:#ef4444; font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:999px;">
                                                        <span style="width:6px; height:6px; border-radius:50%; background:#ef4444;"></span>
                                                        Unread
                                                    </span>
                                                <?php else: ?>
                                                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(16, 185, 129, 0.12); color:var(--status-success); font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:999px;">
                                                        <span style="width:6px; height:6px; border-radius:50%; background:var(--status-success);"></span>
                                                        Read
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Received At -->
                                            <td style="padding:14px 16px; color:var(--text-secondary); font-size:0.82rem;">
                                                <?php $enqTs = validTs($enq['created_at']); ?>
                                                <div style="font-weight:500; color:var(--text-primary);"><?= $enqTs ? date('M d, Y', $enqTs) : '—' ?></div>
                                                <div style="font-size:0.73rem; color:var(--text-muted);"><?= $enqTs ? date('h:i A', $enqTs) : '' ?></div>
                                            </td>

                                            <!-- Actions -->
                                            <td style="padding:14px 20px; text-align:right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px; justify-content:flex-end;">
                                                    <!-- Direct Email Reply -->
                                                    <a href="mailto:<?= e($enq['email']) ?>?subject=Re:%20<?= urlencode($enq['subject'] ?: 'Inquiry with Jiyaji LX') ?>" class="btn-icon" title="Reply via Email"
                                                       style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--brand-blue); text-decoration:none;">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                    </a>

                                                    <!-- Toggle Read Form -->
                                                    <form method="POST" action="<?= url('admin/pages/enquiry/toggle') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                                                        <input type="hidden" name="is_read" value="<?= empty($enq['is_read']) ? '1' : '0' ?>">
                                                        <button type="submit" class="btn-icon" title="<?= empty($enq['is_read']) ? 'Mark as Read' : 'Mark as Unread' ?>"
                                                                style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-secondary); cursor:pointer;">
                                                            <?php if (empty($enq['is_read'])): ?>
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                            <?php else: ?>
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                                                            <?php endif; ?>
                                                        </button>
                                                    </form>

                                                    <!-- Delete Form -->
                                                    <form method="POST" action="<?= url('admin/pages/enquiry/delete') ?>" style="display:inline;" onsubmit="return confirm('Delete customer inquiry from <?= addslashes($enq['name']) ?>?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= $enq['id'] ?>">
                                                        <button type="submit" class="btn-icon" title="Delete Inquiry"
                                                                style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-surface); color:var(--status-error); cursor:pointer;">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Inquiries Pagination -->
                    <?php if ($enqPagination['total_pages'] > 1): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-top:1px solid var(--border-color); background:var(--bg-surface-secondary);">
                            <div style="font-size:0.82rem; color:var(--text-muted);">
                                Showing <?= count($enqList) ?> of <?= $enqPagination['total'] ?> inquiries
                            </div>
                            <div style="display:flex; gap:6px;">
                                <?php if ($enqPagination['has_prev']): ?>
                                    <a href="<?= url('admin/pages?tab=enquiries&enq_page=' . ($enqPagination['current_page'] - 1)) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); text-decoration:none; color:var(--text-primary); background:var(--bg-surface);">
                                        &larr; Prev
                                    </a>
                                <?php endif; ?>
                                <?php if ($enqPagination['has_next']): ?>
                                    <a href="<?= url('admin/pages?tab=enquiries&enq_page=' . ($enqPagination['current_page'] + 1)) ?>"
                                       style="padding:6px 12px; font-size:0.82rem; font-weight:600; border:1px solid var(--border-color); border-radius:var(--radius-sm); text-decoration:none; color:var(--text-primary); background:var(--bg-surface);">
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

<!-- ========================================================================= -->
<!-- LIVE PREVIEW MODAL                                                        -->
<!-- ========================================================================= -->
<div id="pagePreviewModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-xl); width:100%; max-width:900px; max-height:90vh; display:flex; flex-direction:column; box-shadow:var(--shadow-xl); overflow:hidden;">
        <!-- Modal Topbar -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid var(--border-color); background:var(--bg-surface-secondary); flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span id="previewModalSlug" style="font-family:monospace; font-size:0.8rem; background:rgba(59, 130, 246, 0.1); color:var(--brand-blue); padding:3px 8px; border-radius:var(--radius-sm);">/page</span>
                <h3 id="previewModalTitle" style="font-size:0.95rem; font-weight:700; color:var(--text-primary); margin:0;">Page Preview</h3>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <!-- Viewport buttons -->
                <button type="button" onclick="setPreviewWidth('100%')" title="Desktop View" style="padding:4px 8px; font-size:0.75rem; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">Desktop</button>
                <button type="button" onclick="setPreviewWidth('768px')" title="Tablet View" style="padding:4px 8px; font-size:0.75rem; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">Tablet</button>
                <button type="button" onclick="setPreviewWidth('380px')" title="Mobile View" style="padding:4px 8px; font-size:0.75rem; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">Mobile</button>

                <button type="button" onclick="closePreviewModal()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        <!-- Modal Body Container -->
        <div style="flex:1; overflow-y:auto; padding:24px; display:flex; justify-content:center; background:#0d1117;">
            <div id="previewContainer" style="background:#fff; color:#1f2937; padding:36px; border-radius:var(--radius-md); width:100%; max-width:100%; transition:max-width 0.2s ease-in-out; box-shadow:0 4px 12px rgba(0,0,0,0.15); line-height:1.6; font-size:0.95rem;">
                <h1 id="previewContentTitle" style="font-size:1.8rem; font-weight:800; color:#111827; margin-bottom:8px; line-height:1.2;"></h1>
                <p id="previewContentExcerpt" style="font-size:1.05rem; color:#4b5563; font-weight:500; margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid #e5e7eb;"></p>
                <div id="previewContentBody" style="color:#374151;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-slug generator for page editor
const titleInput = document.getElementById('pageTitleInput');
const slugInput = document.getElementById('pageSlugInput');
if (titleInput && slugInput && !slugInput.readOnly) {
    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
    slugInput.addEventListener('input', function() {
        slugInput.dataset.manual = 'true';
    });
}

// Formatting buttons helper
function formatContent(tag) {
    const area = document.getElementById('pageContentArea');
    if (!area) return;
    const start = area.selectionStart;
    const end = area.selectionEnd;
    const text = area.value;
    const selected = text.substring(start, end) || 'Sample text';

    let replacement = '';
    if (tag === 'hr') {
        replacement = '\n<hr>\n';
    } else if (tag === 'ul') {
        replacement = '\n<ul>\n    <li>' + selected + '</li>\n</ul>\n';
    } else {
        replacement = '<' + tag + '>' + selected + '</' + tag + '>';
    }

    area.value = text.substring(0, start) + replacement + text.substring(end);
    area.focus();
    area.selectionStart = start + replacement.length;
    area.selectionEnd = start + replacement.length;
}

// Live Preview Modal
<?php
$pagesCatalog = [];
if (!empty($pagesList)) {
    foreach ($pagesList as $pItem) {
        $pagesCatalog[$pItem['id']] = [
            'id'      => (int)$pItem['id'],
            'title'   => $pItem['title'],
            'excerpt' => $pItem['excerpt'] ?? '',
            'content' => $pItem['content'] ?? '',
            'slug'    => $pItem['slug'] ?? ''
        ];
    }
}
?>
window.pagesCatalog = <?= json_encode($pagesCatalog, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function openPreviewModal(pageOrId) {
    let page = pageOrId;
    if (typeof pageOrId === 'number' || typeof pageOrId === 'string') {
        page = (window.pagesCatalog && window.pagesCatalog[pageOrId]) ? window.pagesCatalog[pageOrId] : null;
    }
    if (!page) return;
    document.getElementById('previewModalTitle').innerText = page.title || 'Untitled Page';
    document.getElementById('previewModalSlug').innerText = '/' + (page.slug || '');
    document.getElementById('previewContentTitle').innerText = page.title || 'Untitled Page';
    document.getElementById('previewContentExcerpt').innerText = page.excerpt || '';
    document.getElementById('previewContentBody').innerHTML = page.content || '<p>No content written yet.</p>';
    document.getElementById('pagePreviewModal').style.display = 'flex';
}

function closePreviewModal() {
    document.getElementById('pagePreviewModal').style.display = 'none';
}

function setPreviewWidth(width) {
    document.getElementById('previewContainer').style.maxWidth = width;
}

// Close preview modal on backdrop click
window.addEventListener('click', function(e) {
    const modal = document.getElementById('pagePreviewModal');
    if (e.target === modal) closePreviewModal();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
