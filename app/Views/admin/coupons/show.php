<?php
include __DIR__ . '/../layouts/header.php';

/**
 * Guard against MySQL zero-dates and negative Unix timestamps.
 */
function validTs(?string $dateStr): int|false {
    if (empty($dateStr) || $dateStr === '0000-00-00 00:00:00') return false;
    $ts = strtotime($dateStr);
    return ($ts && $ts > 946684800) ? $ts : false;
}

$status = $coupon['computed_status'];
$statusConfig = [
    'active'    => ['color' => 'var(--status-success)', 'bg' => 'rgba(16,185,129,0.1)', 'label' => 'Active'],
    'expired'   => ['color' => '#94a3b8',               'bg' => 'rgba(148,163,184,0.1)', 'label' => 'Expired'],
    'disabled'  => ['color' => '#ef4444',               'bg' => 'rgba(239,68,68,0.1)', 'label' => 'Disabled'],
    'exhausted' => ['color' => 'var(--brand-orange)',   'bg' => 'rgba(255,81,0,0.1)', 'label' => 'Exhausted'],
];
$sc = $statusConfig[$status] ?? $statusConfig['disabled'];
$enc = encrypt_id($coupon['id']);
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Breadcrumb + Header -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:28px;">
                <div>
                    <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color:var(--brand-blue);">Dashboard</a>
                        <span>&nbsp;/&nbsp;</span>
                        <a href="<?= url('admin/coupons') ?>" style="color:var(--brand-blue);">Coupons &amp; Promos</a>
                        <span>&nbsp;/&nbsp;</span>
                        <span><?= e($coupon['code']) ?></span>
                    </div>
                    <h1 class="welcome-title" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <span style="font-family:monospace; letter-spacing:1px;"><?= e($coupon['code']) ?></span>
                        <span style="font-size:0.78rem; font-weight:800; padding:5px 12px; border-radius:999px; background:<?= $sc['bg'] ?>; color:<?= $sc['color'] ?>;">
                            <?= $sc['label'] ?>
                        </span>
                    </h1>
                    <?php if (!empty($coupon['description'])): ?>
                        <p class="welcome-subtitle"><?= e($coupon['description']) ?></p>
                    <?php endif; ?>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="<?= url('admin/coupons/' . $enc . '/edit') ?>" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:8px; text-decoration:none; font-size:0.85rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Coupon
                    </a>
                    <form method="POST" action="<?= url('admin/coupons/' . $enc . '/status') ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-secondary" style="padding:10px 16px; border-radius:8px; font-size:0.85rem; display:flex; align-items:center; gap:7px;"
                            onclick="return confirm('<?= $coupon['is_active'] ? 'Deactivate' : 'Activate' ?> this coupon?')">
                            <?php if ($coupon['is_active']): ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                Deactivate
                            <?php else: ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Activate
                            <?php endif; ?>
                        </button>
                    </form>
                    <form method="POST" action="<?= url('admin/coupons/' . $enc . '/delete') ?>" style="display:inline;"
                        onsubmit="return confirm('Permanently delete coupon <?= e($coupon['code']) ?>? This action cannot be undone.')">
                        <?= csrf_field() ?>
                        <button type="submit" style="padding:10px 16px; border-radius:8px; border:1px solid rgba(239,68,68,0.3); background:rgba(239,68,68,0.07); color:#ef4444; font-size:0.85rem; cursor:pointer; font-weight:700; transition:all .15s;">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start;">

                <!-- LEFT: Details + Usage History -->
                <div style="display:flex; flex-direction:column; gap:20px;">

                    <!-- Coupon Summary -->
                    <div class="kpi-card" style="padding:24px;">
                        <h3 style="font-size:0.88rem; font-weight:800; color:var(--text-primary); margin-bottom:20px;">Coupon Configuration</h3>
                        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:18px;">
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Discount Type</div>
                                <div style="font-size:0.95rem; font-weight:700; color:var(--text-primary);">
                                    <?= $coupon['type'] === 'percentage' ? 'Percentage (%)' : 'Flat Amount (₹)' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Discount Value</div>
                                <div style="font-size:1.3rem; font-weight:900; color:var(--brand-blue);">
                                    <?= $coupon['type'] === 'percentage' ? (int)$coupon['value'] . '%' : currency($coupon['value']) ?>
                                </div>
                            </div>
                            <?php if ($coupon['type'] === 'percentage' && !empty($coupon['max_discount_cap'])): ?>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Max Discount Cap</div>
                                <div style="font-size:0.95rem; font-weight:700; color:#d97706;"><?= currency($coupon['max_discount_cap']) ?></div>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Minimum Cart Value</div>
                                <div style="font-size:0.95rem; font-weight:700; color:var(--text-primary);">
                                    <?= $coupon['min_cart_value'] > 0 ? currency($coupon['min_cart_value']) : '—' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Starts At</div>
                                <div style="font-size:0.88rem; font-weight:600; color:var(--text-primary);">
                                    <?php $startsTs = validTs($coupon['starts_at'] ?? null); ?>
                                    <?= $startsTs ? date('d M Y, h:i A', $startsTs) : 'Immediately' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Expires At</div>
                                <?php $expiresTs = validTs($coupon['expires_at'] ?? null); ?>
                                <div style="font-size:0.88rem; font-weight:600; color:<?= ($expiresTs && $expiresTs < time()) ? '#ef4444' : 'var(--text-primary)' ?>;">
                                    <?= $expiresTs ? date('d M Y, h:i A', $expiresTs) : 'Never' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Global Usage Limit</div>
                                <div style="font-size:0.95rem; font-weight:700; color:var(--text-primary);">
                                    <?= $coupon['usage_limit_global'] ? number_format($coupon['usage_limit_global']) : 'Unlimited' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Per Customer Limit</div>
                                <div style="font-size:0.95rem; font-weight:700; color:var(--text-primary);"><?= number_format($coupon['usage_limit_per_user']) ?>×</div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Visibility</div>
                                <div style="font-size:0.88rem; font-weight:700; color:<?= $coupon['is_public'] ? 'var(--status-success)' : 'var(--text-muted)' ?>;">
                                    <?= $coupon['is_public'] ? '● Public' : '○ Private' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:0.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Created By</div>
                                <div style="font-size:0.88rem; font-weight:600; color:var(--text-primary);">
                                    <?= !empty($coupon['created_by_name']) ? e($coupon['created_by_name']) : 'System' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage History -->
                    <div class="kpi-card" style="padding:24px;">
                        <h3 style="font-size:0.88rem; font-weight:800; color:var(--text-primary); margin-bottom:16px;">Usage History</h3>
                        <?php if (empty($usageHistory)): ?>
                            <div style="padding:30px; text-align:center; color:var(--text-muted);">
                                <p style="font-size:0.88rem;">No usage records yet.</p>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                                    <thead>
                                        <tr style="border-bottom:1px solid var(--border-subtle);">
                                            <th style="padding:8px 12px; text-align:left; font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Customer</th>
                                            <th style="padding:8px 12px; text-align:left; font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Order</th>
                                            <th style="padding:8px 12px; text-align:right; font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Order Total</th>
                                            <th style="padding:8px 12px; text-align:right; font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Discount</th>
                                            <th style="padding:8px 12px; text-align:right; font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Used At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($usageHistory as $u): ?>
                                        <tr style="border-bottom:1px solid var(--border-subtle);">
                                            <td style="padding:10px 12px;">
                                                <div style="font-weight:700; color:var(--text-primary);"><?= e($u['customer_name']) ?></div>
                                                <div style="color:var(--text-muted); font-size:0.74rem;"><?= e($u['customer_email']) ?></div>
                                            </td>
                                            <td style="padding:10px 12px; font-family:monospace; color:var(--brand-blue);">
                                                <?= $u['order_number'] ? e($u['order_number']) : '—' ?>
                                            </td>
                                            <td style="padding:10px 12px; text-align:right; font-weight:700; color:var(--text-primary);">
                                                <?= $u['total_amount'] ? currency($u['total_amount']) : '—' ?>
                                            </td>
                                            <td style="padding:10px 12px; text-align:right; font-weight:700; color:var(--status-success);">
                                                <?= $u['discount_amount'] ? '−' . currency($u['discount_amount']) : '—' ?>
                                            </td>
                                            <td style="padding:10px 12px; text-align:right; color:var(--text-muted);">
                                                <?= date('d M Y', strtotime($u['used_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT: Stats Sidebar -->
                <div style="display:flex; flex-direction:column; gap:16px;">

                    <!-- Usage Meter -->
                    <div class="kpi-card" style="padding:20px;">
                        <div style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Redemption Progress</div>
                        <div style="font-size:2rem; font-weight:900; color:var(--brand-blue);"><?= number_format($coupon['times_used']) ?></div>
                        <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:10px;">
                            of <?= $coupon['usage_limit_global'] ? number_format($coupon['usage_limit_global']) . ' max' : 'unlimited' ?>
                        </div>
                        <?php if ($coupon['usage_limit_global']): ?>
                            <?php $pct = min(round($coupon['times_used'] / $coupon['usage_limit_global'] * 100), 100); ?>
                            <div style="height:8px; background:var(--bg-elevated); border-radius:99px; overflow:hidden;">
                                <div style="height:100%; width:<?= $pct ?>%; background:<?= $pct >= 90 ? '#ef4444' : ($pct >= 60 ? '#d97706' : 'var(--brand-blue)') ?>; border-radius:99px; transition:width .5s;"></div>
                            </div>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:6px;"><?= $pct ?>% used</div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Code Copy -->
                    <div class="kpi-card" style="padding:20px; background:linear-gradient(135deg, rgba(140,48,245,0.08), rgba(45,130,255,0.05));">
                        <div style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:10px;">Coupon Code</div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div id="copyCode" style="flex:1; font-family:monospace; font-size:1.3rem; font-weight:900; color:var(--brand-purple); letter-spacing:2px; padding:12px; border:2px dashed rgba(140,48,245,0.3); border-radius:8px; background:rgba(140,48,245,0.04); text-align:center;">
                                <?= e($coupon['code']) ?>
                            </div>
                            <button onclick="copyCode()" title="Copy code" style="width:38px; height:38px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--bg-elevated); color:var(--text-secondary); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="kpi-card" style="padding:20px;">
                        <div style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">Metadata</div>
                        <div style="display:flex; flex-direction:column; gap:10px; font-size:0.82rem;">
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--text-muted);">Created</span>
                                <span style="color:var(--text-primary); font-weight:600;"><?= date('d M Y', strtotime($coupon['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($coupon['created_by_name'])): ?>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--text-muted);">Created By</span>
                                <span style="color:var(--text-primary); font-weight:600;"><?= e($coupon['created_by_name']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--text-muted);">Coupon ID</span>
                                <span style="color:var(--text-muted); font-family:monospace;">#<?= $coupon['id'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Restrictions -->
                    <?php if (!empty($coupon['category_restrictions']) || !empty($coupon['product_restrictions'])): ?>
                    <div class="kpi-card" style="padding:20px;">
                        <div style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; margin-bottom:12px;">Restrictions</div>
                        <?php if (!empty($coupon['category_restrictions'])): ?>
                            <div style="margin-bottom:10px;">
                                <div style="font-size:0.75rem; font-weight:700; color:var(--text-secondary); margin-bottom:6px;">Categories:</div>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    <?php foreach ($coupon['category_restrictions'] as $cat): ?>
                                        <span style="font-size:0.72rem; padding:3px 9px; border-radius:999px; background:rgba(45,130,255,0.1); color:var(--brand-blue);"><?= e($cat['category_name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($coupon['product_restrictions'])): ?>
                            <div>
                                <div style="font-size:0.75rem; font-weight:700; color:var(--text-secondary); margin-bottom:6px;">Products:</div>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    <?php foreach ($coupon['product_restrictions'] as $prod): ?>
                                        <span style="font-size:0.72rem; padding:3px 9px; border-radius:999px; background:rgba(140,48,245,0.1); color:var(--brand-purple);"><?= e($prod['product_name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </main>
    </div>
</div>

<script>
function copyCode() {
    const code = document.getElementById('copyCode').textContent.trim();
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.querySelector('button[onclick="copyCode()"]');
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--status-success)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
        setTimeout(() => {
            btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
        }, 2000);
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
