<?php
include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$firstTs = validTs($details['first_searched']);
$lastTs  = validTs($details['last_searched']);
$isZeroGap = (int)$details['catalog_total_matches'] === 0;
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
                        <a href="<?= url('admin/search-analytics') ?>" style="color:var(--brand-blue); text-decoration:none;">Search Analytics</a>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Term Intelligence</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <h1 class="welcome-title" style="margin:0;">&ldquo;<?= e($details['keyword']) ?>&rdquo;</h1>
                        <?php if ($isZeroGap): ?>
                            <span style="background:var(--brand-orange-light); color:var(--brand-orange); font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:var(--radius-full); border:1px solid rgba(255,81,0,0.2);">
                                ⚠️ Zero Catalog Results
                            </span>
                        <?php else: ?>
                            <span style="background:var(--status-success-bg); color:var(--status-success); font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:var(--radius-full);">
                                ✓ <?= $details['catalog_total_matches'] ?> Products Found
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="welcome-subtitle" style="margin-top:6px;">Keyword performance metrics, inventory correlation, shopper profile attribution, and merchandising opportunities.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <a href="<?= url('admin/search-analytics') ?>" style="background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-secondary); padding:9px 16px; border-radius:var(--radius-md); font-size:0.85rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                        &larr; Back to Analytics
                    </a>
                    <a href="<?= url('admin/products/create?name=' . urlencode($details['keyword'])) ?>" style="background:var(--gradient-primary); color:#fff; padding:10px 18px; border-radius:var(--radius-md); font-size:0.85rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:var(--shadow-glow-blue);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create Garment For Term
                    </a>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 4 METRIC HIGHLIGHT CARDS                                     -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom:24px;">
                <!-- Total Inquiries -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-blue);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon blue" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-blue);"><?= number_format($details['total_searches']) ?></div>
                    <div class="kpi-label">Total Store Searches</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        First: <?= $firstTs ? date('M j, Y', $firstTs) : '—' ?>
                    </div>
                </div>

                <!-- Zero Results Rate -->
                <div class="kpi-card" style="border-left:3px solid <?= $details['zero_results_rate'] > 0 ? 'var(--brand-orange)' : 'var(--status-success)' ?>;">
                    <div class="kpi-card-header">
                        <div class="kpi-icon <?= $details['zero_results_rate'] > 0 ? 'orange' : 'teal' ?>" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:<?= $details['zero_results_rate'] > 0 ? 'var(--brand-orange)' : 'var(--status-success)' ?>;"><?= $details['zero_results_rate'] ?>%</div>
                    <div class="kpi-label">Zero-Result Rate</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        <?= $details['zero_results_count'] ?> of <?= $details['total_searches'] ?> returned 0 items
                    </div>
                </div>

                <!-- Shoppers -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-purple);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon purple" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-purple);"><?= $details['unique_customers'] ?></div>
                    <div class="kpi-label">Registered Members</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Plus <?= $details['guest_searches'] ?> anonymous shoppers
                    </div>
                </div>

                <!-- Catalog Yield -->
                <div class="kpi-card" style="border-left:3px solid #06b6d4;">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:#06b6d4; width:40px; height:40px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:#0891b2;"><?= $details['catalog_total_matches'] ?></div>
                    <div class="kpi-label">Current Catalog Items</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Avg <?= $details['avg_results'] ?> products per query
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- ROW: CURRENT CATALOG MATCHES & RECOMMENDATIONS               -->
            <!-- ============================================================ -->
            <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:24px; margin-bottom:24px;">

                <!-- Section A: Catalog Matches -->
                <div class="card-panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                <span>Catalog Garments Matching Query</span>
                            </h2>
                            <div class="panel-subtitle">Items currently returned by store search for &ldquo;<?= e($details['keyword']) ?>&rdquo;</div>
                        </div>
                        <?php if (!empty($details['catalog_matches'])): ?>
                            <a href="<?= url('admin/products?search=' . urlencode($details['keyword'])) ?>" target="_blank" style="font-size:0.8rem; color:var(--brand-blue); text-decoration:none; font-weight:600;">
                                View All (<?= $details['catalog_total_matches'] ?>) &rarr;
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($details['catalog_matches'])): ?>
                        <div style="padding:36px 20px; text-align:center; background:rgba(255,81,0,0.03); border:1px dashed rgba(255,81,0,0.25); border-radius:var(--radius-md);">
                            <div style="font-size:2rem; margin-bottom:10px;">🏷️</div>
                            <h3 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">No Catalog Items Found for this Keyword</h3>
                            <p style="font-size:0.84rem; color:var(--text-muted); max-width:400px; margin:0 auto 16px;">
                                When shoppers search for &ldquo;<?= e($details['keyword']) ?>&rdquo;, they see an empty results screen.
                            </p>
                            <a href="<?= url('admin/products/create?name=' . urlencode($details['keyword'])) ?>" style="background:var(--brand-blue); color:#fff; font-size:0.82rem; font-weight:700; padding:8px 16px; border-radius:var(--radius-sm); text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Create Matching Garment Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ($details['catalog_matches'] as $prod):
                                $prodEnc = encrypt_id($prod['id']);
                            ?>
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--bg-surface-secondary); border-radius:var(--radius-sm); border:1px solid var(--border-color-light);">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:42px; height:42px; border-radius:var(--radius-sm); background:var(--bg-surface); border:1px solid var(--border-color); display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                                            👔
                                        </div>
                                        <div>
                                            <a href="<?= url('admin/products/' . $prodEnc . '/edit') ?>" style="font-weight:600; font-size:0.88rem; color:var(--text-primary); text-decoration:none;" onmouseover="this.style.color='var(--brand-blue)'" onmouseout="this.style.color='var(--text-primary)'">
                                                <?= e($prod['name']) ?>
                                            </a>
                                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                                                SKU: <?= e($prod['sku'] ?? 'N/A') ?> &bull; <?= currency((float)($prod['base_price'] ?? 0)) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span class="badge" style="background:var(--status-success-bg); color:var(--status-success); font-size:0.7rem; text-transform:capitalize;">
                                            <?= e($prod['status'] ?? 'published') ?>
                                        </span>
                                        <a href="<?= url('admin/products/' . $prodEnc . '/edit') ?>" style="font-size:0.78rem; color:var(--brand-blue); text-decoration:none; font-weight:600;">
                                            Edit &rarr;
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section B: Merchandising Recommendations & Devices -->
                <div style="display:flex; flex-direction:column; gap:24px;">

                    <!-- Merchandising Recommendations -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom:14px;">
                            <h2 class="panel-title" style="font-size:0.95rem;">
                                💡 Merchandising Advice
                            </h2>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <?php if ($isZeroGap): ?>
                                <div style="display:flex; gap:10px; font-size:0.84rem; color:var(--text-secondary); line-height:1.4;">
                                    <span style="color:var(--brand-orange); font-weight:700;">1.</span>
                                    <span>High unmet demand (<strong><?= $details['total_searches'] ?> searches</strong>). Add products featuring &ldquo;<?= e($details['keyword']) ?>&rdquo; in title or taxonomy.</span>
                                </div>
                                <div style="display:flex; gap:10px; font-size:0.84rem; color:var(--text-secondary); line-height:1.4;">
                                    <span style="color:var(--brand-purple); font-weight:700;">2.</span>
                                    <span>Check for spelling variants or add &ldquo;<?= e($details['keyword']) ?>&rdquo; as a keyword alias on existing luxury apparel.</span>
                                </div>
                            <?php else: ?>
                                <div style="display:flex; gap:10px; font-size:0.84rem; color:var(--text-secondary); line-height:1.4;">
                                    <span style="color:var(--status-success); font-weight:700;">✓</span>
                                    <span>Strong catalog coverage (<strong><?= $details['catalog_total_matches'] ?> matching products</strong>). Ensure featured variants are in stock.</span>
                                </div>
                                <div style="display:flex; gap:10px; font-size:0.84rem; color:var(--text-secondary); line-height:1.4;">
                                    <span style="color:var(--brand-blue); font-weight:700;">→</span>
                                    <span>Consider creating a promotional coupon or banner for &ldquo;<?= e($details['keyword']) ?>&rdquo; to increase conversion.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Device Breakdown -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom:14px;">
                            <h2 class="panel-title" style="font-size:0.95rem;">
                                📱 Device Distribution
                            </h2>
                        </div>
                        <?php
                        $totDev = max(1, array_sum($details['devices']));
                        $deskPct = round(($details['devices']['desktop'] / $totDev) * 100);
                        $mobPct  = round(($details['devices']['mobile'] / $totDev) * 100);
                        $tabPct  = round(($details['devices']['tablet'] / $totDev) * 100);
                        ?>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:4px;">
                                    <span style="color:var(--text-secondary);">Desktop</span>
                                    <span style="font-weight:700; color:var(--text-primary);"><?= $deskPct ?>% (<?= $details['devices']['desktop'] ?>)</span>
                                </div>
                                <div style="height:6px; background:var(--bg-surface-secondary); border-radius:999px; overflow:hidden;">
                                    <div style="height:100%; width:<?= $deskPct ?>%; background:var(--brand-blue); border-radius:999px;"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:4px;">
                                    <span style="color:var(--text-secondary);">Mobile</span>
                                    <span style="font-weight:700; color:var(--text-primary);"><?= $mobPct ?>% (<?= $details['devices']['mobile'] ?>)</span>
                                </div>
                                <div style="height:6px; background:var(--bg-surface-secondary); border-radius:999px; overflow:hidden;">
                                    <div style="height:100%; width:<?= $mobPct ?>%; background:var(--brand-purple); border-radius:999px;"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:4px;">
                                    <span style="color:var(--text-secondary);">Tablet</span>
                                    <span style="font-weight:700; color:var(--text-primary);"><?= $tabPct ?>% (<?= $details['devices']['tablet'] ?>)</span>
                                </div>
                                <div style="height:6px; background:var(--bg-surface-secondary); border-radius:999px; overflow:hidden;">
                                    <div style="height:100%; width:<?= $tabPct ?>%; background:#06b6d4; border-radius:999px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ============================================================ -->
            <!-- CHRONOLOGICAL SEARCH ACTIVITY LEDGER                         -->
            <!-- ============================================================ -->
            <div class="card-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Recent Shopper Inquiries for &ldquo;<?= e($details['keyword']) ?>&rdquo;</span>
                        </h2>
                        <div class="panel-subtitle">Individual search events logged by registered customers and guest shoppers</div>
                    </div>
                    <form method="POST" action="<?= url('admin/search-analytics/delete') ?>" onsubmit="return confirm('Clear ALL historical search records for this keyword?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="keyword" value="<?= e($details['keyword']) ?>">
                        <button type="submit" style="background:none; border:1px solid var(--border-color); color:var(--status-danger); padding:6px 12px; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:600; cursor:pointer;" onmouseover="this.style.background='var(--status-danger-bg)'" onmouseout="this.style.background='transparent'">
                            Clear All Records For Term
                        </button>
                    </form>
                </div>

                <div class="orders-table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Shopper Profile</th>
                                <th style="text-align:center;">Results Returned</th>
                                <th style="text-align:center;">Device</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details['recent_logs'] as $rLog):
                                $rTs = validTs($rLog['searched_at']);
                                $rZero = (int)$rLog['results_count'] === 0;
                                $rEnc = encrypt_id($rLog['id']);
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($rLog['customer_id'])): ?>
                                            <div style="font-weight:600; color:var(--text-primary); font-size:0.86rem;"><?= e($rLog['customer_name'] ?? 'Member #' . $rLog['customer_id']) ?></div>
                                            <div style="font-size:0.75rem; color:var(--text-muted);"><?= e($rLog['customer_email'] ?? '') ?></div>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:0.82rem; font-style:italic;">Guest Shopper</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if ($rZero): ?>
                                            <span style="background:var(--brand-orange-light); color:var(--brand-orange); font-size:0.72rem; font-weight:700; padding:2px 8px; border-radius:var(--radius-full);">
                                                0 products
                                            </span>
                                        <?php else: ?>
                                            <span style="background:var(--status-success-bg); color:var(--status-success); font-size:0.72rem; font-weight:700; padding:2px 8px; border-radius:var(--radius-full);">
                                                <?= $rLog['results_count'] ?> products
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <span style="background:var(--bg-surface-secondary); color:var(--text-secondary); font-size:0.72rem; padding:2px 8px; border-radius:var(--radius-full); text-transform:capitalize;">
                                            <?= e($rLog['device_type'] ?? 'desktop') ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.8rem; color:var(--text-muted); font-family:monospace;">
                                        <?= e($rLog['ip_address'] ?? '—') ?>
                                    </td>
                                    <td style="font-size:0.82rem; color:var(--text-muted);">
                                        <?= $rTs ? date('M j, Y h:i A', $rTs) : '—' ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <form method="POST" action="<?= url('admin/search-analytics/delete') ?>" onsubmit="return confirm('Delete this individual search log?');" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="log_id" value="<?= $rEnc ?>">
                                            <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;" title="Delete log entry" onmouseover="this.style.color='var(--status-danger)'" onmouseout="this.style.color='var(--text-muted)'">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
