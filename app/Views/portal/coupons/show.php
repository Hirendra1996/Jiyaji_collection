<?php
$title        = $title ?? 'Coupon Details | Jiyaji LX Operations Portal';
$coupon       = $coupon ?? [];
$usageHistory = $usageHistory ?? [];
$canManage    = $canManage ?? false;

include __DIR__ . '/../layouts/header.php';

$encId = encrypt_id($coupon['id']);
$cStatus = $coupon['computed_status'] ?? 'active';

$statusStyle = match($cStatus) {
    'active'    => 'background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);',
    'expired'   => 'background: rgba(239, 68, 68, 0.1); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.25);',
    'disabled'  => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);',
    'exhausted' => 'background: rgba(245, 158, 11, 0.12); color: #B45309; border: 1px solid rgba(245, 158, 11, 0.3);',
    default     => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);'
};
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <a href="<?= url('portal/dashboard') ?>" style="color: #059669; text-decoration: none; font-weight: 600;">Dashboard</a>
                    <span>&rsaquo;</span>
                    <a href="<?= url('portal/coupons') ?>" style="color: #059669; text-decoration: none; font-weight: 600;">Coupons &amp; Offers</a>
                    <span>&rsaquo;</span>
                    <span style="color: #0F172A; font-weight: 800; font-family: monospace;"><?= htmlspecialchars($coupon['code']) ?></span>
                </div>

                <a href="<?= url('portal/coupons') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.82rem; font-weight: 600; color: #334155; text-decoration: none;">
                    &larr; Back to Coupons
                </a>
            </div>

            <!-- Coupon Header Card -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span style="font-family: monospace; font-size: 1.35rem; font-weight: 900; letter-spacing: 0.05em; background: #F8FAFC; border: 1.5px solid #059669; color: #065F46; padding: 4px 14px; border-radius: 8px;">
                            <?= htmlspecialchars($coupon['code']) ?>
                        </span>
                        <button type="button" onclick="copyCode('<?= htmlspecialchars($coupon['code']) ?>', this)" 
                                style="background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 6px; padding: 5px 10px; font-size: 0.76rem; font-weight: 600; color: #475569; cursor: pointer;">
                            Copy Code
                        </button>
                        <span style="padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase; <?= $statusStyle ?>">
                            <?= ucfirst($cStatus) ?>
                        </span>
                        <span style="padding: 4px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: <?= $coupon['is_public'] ? 'rgba(16,185,129,0.1)' : 'rgba(100,116,139,0.1)' ?>; color: <?= $coupon['is_public'] ? '#059669' : '#64748B' ?>;">
                            <?= $coupon['is_public'] ? '● Storefront Public' : '○ Private / Targeted' ?>
                        </span>
                    </div>
                    <?php if (!empty($coupon['description'])): ?>
                        <p style="font-size: 0.9rem; color: #475569; margin: 0 0 4px 0;">
                            <?= htmlspecialchars($coupon['description']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canManage): ?>
                        <a href="<?= url('portal/coupons/' . $encId . '/edit') ?>" 
                           style="padding: 9px 18px; background: #0F172A; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit Coupon
                        </a>

                        <form action="<?= url('portal/coupons/' . $encId . '/status') ?>" method="POST" style="margin: 0; display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" style="padding: 9px 16px; background: <?= (int)$coupon['is_active'] ? '#FFF1F2' : '#F0FDF4' ?>; border: 1px solid <?= (int)$coupon['is_active'] ? '#FECDD3' : '#BBF7D0' ?>; color: <?= (int)$coupon['is_active'] ? '#BE123C' : '#15803D' ?>; font-weight: 700; font-size: 0.82rem; border-radius: 8px; cursor: pointer;">
                                <?= (int)$coupon['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2-Column Main Layout -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">

                <!-- LEFT COLUMN: Rules & Redemption History -->
                <div>
                    <!-- Configuration Matrix Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <h2 style="font-size: 1rem; font-weight: 800; color: #0F172A; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.05em;">
                            Discount &amp; Basket Configuration
                        </h2>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Discount Type</div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                    <?= $coupon['type'] === 'percentage' ? 'Percentage (%)' : 'Flat Amount (₹)' ?>
                                </div>
                            </div>

                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Benefit Value</div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: #059669; margin-top: 4px;">
                                    <?= $coupon['type'] === 'percentage' ? (int)$coupon['value'] . '% OFF' : '₹' . number_format((float)$coupon['value']) . ' OFF' ?>
                                </div>
                            </div>

                            <?php if ($coupon['type'] === 'percentage'): ?>
                                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                    <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Maximum Cap</div>
                                    <div style="font-size: 1.15rem; font-weight: 800; color: #D97706; margin-top: 4px;">
                                        <?= !empty($coupon['max_discount_cap']) ? '₹' . number_format((float)$coupon['max_discount_cap']) : 'No Cap' ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Minimum Order</div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                    <?= (float)$coupon['min_cart_value'] > 0 ? '₹' . number_format((float)$coupon['min_cart_value']) : 'No Minimum' ?>
                                </div>
                            </div>

                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Limit Per User</div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                    <?= (int)$coupon['usage_limit_per_user'] ?>× per shopper
                                </div>
                            </div>
                        </div>

                        <!-- Restrictions Block -->
                        <?php if (!empty($coupon['category_restrictions']) || !empty($coupon['product_restrictions'])): ?>
                            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
                                <div style="font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                    Eligibility Restrictions
                                </div>

                                <?php if (!empty($coupon['category_restrictions'])): ?>
                                    <div style="margin-bottom: 10px;">
                                        <span style="font-size: 0.76rem; color: #64748B; margin-right: 6px;">Categories:</span>
                                        <?php foreach ($coupon['category_restrictions'] as $cr): ?>
                                            <span style="display: inline-block; background: #EEF2FF; color: #4338CA; font-size: 0.74rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; margin: 2px;">
                                                <?= htmlspecialchars($cr['category_name'] ?? 'Category #' . $cr['category_id']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($coupon['product_restrictions'])): ?>
                                    <div>
                                        <span style="font-size: 0.76rem; color: #64748B; margin-right: 6px;">Specific Attire:</span>
                                        <?php foreach ($coupon['product_restrictions'] as $pr): ?>
                                            <span style="display: inline-block; background: #F0FDF4; color: #15803D; font-size: 0.74rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; margin: 2px;">
                                                <?= htmlspecialchars($pr['product_name'] ?? 'Product #' . $pr['product_id']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Redemption History Table -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="padding: 18px 22px; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between;">
                            <h2 style="font-size: 1rem; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                                <span>Recent Customer Redemptions</span>
                                <span style="background: #D1FAE5; color: #065F46; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 999px;">
                                    <?= count($usageHistory) ?> Logged
                                </span>
                            </h2>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                                <thead>
                                    <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.74rem; text-transform: uppercase; color: #64748B; letter-spacing: 0.05em;">
                                        <th style="padding: 12px 18px;">Customer</th>
                                        <th style="padding: 12px 18px;">Order #</th>
                                        <th style="padding: 12px 18px;">Discount Given</th>
                                        <th style="padding: 12px 18px;">Order Total</th>
                                        <th style="padding: 12px 18px; text-align: right;">Used At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($usageHistory)): ?>
                                        <tr>
                                            <td colspan="5" style="padding: 40px 20px; text-align: center; color: #64748B;">
                                                <div style="font-size: 1.8rem; margin-bottom: 6px;">🛍️</div>
                                                <div style="font-weight: 700; color: #0F172A; margin-bottom: 2px;">No Redemptions Yet</div>
                                                <div style="font-size: 0.8rem;">This promotional coupon has not been applied in customer orders yet.</div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($usageHistory as $u): ?>
                                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                                <td style="padding: 12px 18px;">
                                                    <div style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars($u['customer_name'] ?? 'Shopper') ?></div>
                                                    <div style="font-size: 0.74rem; color: #64748B;"><?= htmlspecialchars($u['customer_email'] ?? '') ?></div>
                                                </td>
                                                <td style="padding: 12px 18px; font-family: monospace; font-weight: 700; color: #4F46E5;">
                                                    #<?= htmlspecialchars($u['order_number'] ?? '—') ?>
                                                </td>
                                                <td style="padding: 12px 18px; font-weight: 700; color: #059669;">
                                                    -₹<?= number_format((float)($u['discount_amount'] ?? 0)) ?>
                                                </td>
                                                <td style="padding: 12px 18px; font-weight: 600; color: #0F172A;">
                                                    ₹<?= number_format((float)($u['total_amount'] ?? 0)) ?>
                                                </td>
                                                <td style="padding: 12px 18px; text-align: right; color: #64748B; font-size: 0.78rem;">
                                                    <?= !empty($u['used_at']) ? date('M d, Y h:i A', strtotime($u['used_at'])) : '—' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Stats & Validity -->
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    <!-- Performance Stats Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="font-size: 0.74rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                            Campaign Performance
                        </div>

                        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 1.8rem; font-weight: 900; color: #0F172A;">
                                <?= number_format((int)$coupon['times_used']) ?>
                            </span>
                            <span style="font-size: 0.82rem; color: #64748B;">
                                of <?= !empty($coupon['usage_limit_global']) ? number_format((int)$coupon['usage_limit_global']) . ' max' : 'unlimited' ?>
                            </span>
                        </div>

                        <?php if (!empty($coupon['usage_limit_global'])): 
                            $pct = min(100, round(($coupon['times_used'] / $coupon['usage_limit_global']) * 100));
                            $barColor = $pct >= 90 ? '#DC2626' : ($pct >= 75 ? '#D97706' : '#059669');
                        ?>
                            <div style="width: 100%; height: 7px; background: #E2E8F0; border-radius: 999px; overflow: hidden; margin-bottom: 6px;">
                                <div style="width: <?= $pct ?>%; height: 100%; background: <?= $barColor ?>;"></div>
                            </div>
                            <div style="font-size: 0.74rem; color: #64748B;"><?= $pct ?>% of capacity utilized</div>
                        <?php endif; ?>
                    </div>

                    <!-- Validity Window Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="font-size: 0.74rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                            Validity Period
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Active From</div>
                            <div style="font-size: 0.9rem; font-weight: 700; color: #0F172A; margin-top: 2px;">
                                <?= !empty($coupon['starts_at']) ? date('M d, Y &bull; h:i A', strtotime($coupon['starts_at'])) : 'Immediate (Upon Launch)' ?>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Expires On</div>
                            <div style="font-size: 0.9rem; font-weight: 700; color: #0F172A; margin-top: 2px;">
                                <?= !empty($coupon['expires_at']) ? date('M d, Y &bull; h:i A', strtotime($coupon['expires_at'])) : 'Never (Perpetual Validity)' ?>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.72rem; color: #64748B; text-transform: uppercase; font-weight: 700;">Audit Author</div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-top: 2px;">
                                <?= htmlspecialchars($coupon['created_by_name'] ?? 'Operations Staff') ?> &bull; <?= date('M d, Y', strtotime($coupon['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone (Delete) -->
                    <?php if ($canManage): ?>
                        <div style="background: #FFF1F2; border: 1px solid #FECDD3; border-radius: 14px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="font-size: 0.74rem; font-weight: 800; color: #E11D48; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                                Danger Zone
                            </div>
                            <p style="font-size: 0.78rem; color: #9F1239; margin: 0 0 12px 0;">
                                Delete this coupon. Coupons that have already been used in real customer orders cannot be deleted and must be deactivated instead.
                            </p>
                            <form action="<?= url('portal/coupons/' . $encId . '/delete') ?>" method="POST" onsubmit="return confirm('Permanently delete coupon <?= htmlspecialchars($coupon['code']) ?>? This action cannot be undone.');">
                                <?= csrf_field() ?>
                                <button type="submit" style="width: 100%; padding: 8px; background: #E11D48; color: #FFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.78rem; cursor: pointer;">
                                    Delete Coupon Campaign
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<script>
function copyCode(code, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            const orig = btn.innerText;
            btn.innerText = 'Copied!';
            btn.style.color = '#059669';
            setTimeout(() => {
                btn.innerText = orig;
                btn.style.color = '#475569';
            }, 1500);
        });
    }
}
</script>
