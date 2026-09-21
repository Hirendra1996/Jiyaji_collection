<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">

            <!-- Header -->
            <div class="welcome-banner" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color:var(--brand-blue);">Dashboard</a>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <a href="<?= url('admin/coupons') ?>" style="color:var(--brand-blue);">Coupons &amp; Promos</a>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Create New</span>
                    </div>
                    <h1 class="welcome-title">Create New Coupon</h1>
                    <p class="welcome-subtitle">Configure a discount code with type, value, validity window, and usage limits.</p>
                </div>
                <a href="<?= url('admin/coupons') ?>"
                   style="display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.85rem; font-weight:600; color:var(--text-secondary); text-decoration:none; background:var(--bg-surface); transition:var(--transition);"
                   onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                   onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    Back to Coupons
                </a>
            </div>

            <form method="POST" action="<?= url('admin/coupons/store') ?>">
                <?= csrf_field() ?>

                <div class="product-form-grid">

                    <!-- ============================================================ -->
                    <!-- LEFT: Main Configuration                                       -->
                    <!-- ============================================================ -->
                    <div style="display:flex; flex-direction:column; gap:20px;">

                        <!-- Identity -->
                        <div class="card-panel" style="gap:0;">
                            <div class="panel-header" style="border-bottom:1px solid var(--border-color-light); padding-bottom:16px; margin-bottom:20px;">
                                <div class="panel-title">
                                    <div style="width:32px; height:32px; border-radius:var(--radius-md); background:var(--brand-purple-light); display:flex; align-items:center; justify-content:center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                    </div>
                                    Coupon Identity
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Coupon Code <span style="color:var(--status-danger);">*</span></label>
                                <div style="display:flex; gap:8px;">
                                    <div style="flex:1; position:relative;">
                                        <input type="text" name="code" id="couponCode" class="form-input" required
                                               value="<?= e($suggested ?? '') ?>"
                                               placeholder="e.g. SAVE200"
                                               maxlength="50"
                                               style="font-family:monospace; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; font-size:1rem;"
                                               oninput="this.value=this.value.toUpperCase().replace(/\s/g,''); updatePreview()">
                                    </div>
                                    <button type="button" onclick="generateCode()"
                                            style="height:44px; padding:0 16px; border:1.5px solid var(--border-color); border-radius:var(--radius-md); font-size:0.82rem; font-weight:700; color:var(--text-secondary); background:var(--bg-surface-secondary); transition:var(--transition); white-space:nowrap;"
                                            onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                                            onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)'">
                                        ↻ Generate
                                    </button>
                                </div>
                                <p style="font-size:0.77rem; color:var(--text-muted); margin-top:6px;">Unique uppercase code customers enter at checkout. No spaces.</p>
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Description <span style="font-size:0.78rem; font-weight:400; color:var(--text-muted);">(internal note)</span></label>
                                <input type="text" name="description" class="form-input"
                                       placeholder="e.g. New Customer Welcome Offer — Sep 2026"
                                       maxlength="255">
                            </div>
                        </div>

                        <!-- Discount Configuration -->
                        <div class="card-panel" style="gap:0;">
                            <div class="panel-header" style="border-bottom:1px solid var(--border-color-light); padding-bottom:16px; margin-bottom:20px;">
                                <div class="panel-title">
                                    <div style="width:32px; height:32px; border-radius:var(--radius-md); background:var(--brand-blue-light); display:flex; align-items:center; justify-content:center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    </div>
                                    Discount Configuration
                                </div>
                            </div>

                            <!-- Type Selector -->
                            <div class="form-group">
                                <label class="form-label">Discount Type</label>
                                <div class="grid-2-cols">
                                    <label id="labelFlat" style="display:flex; align-items:center; gap:12px; padding:14px 16px; border:2px solid var(--brand-blue); border-radius:var(--radius-md); cursor:pointer; background:var(--brand-blue-light); transition:var(--transition);">
                                        <input type="radio" name="type" value="flat" checked onchange="toggleDiscountType()" style="width:16px; height:16px; accent-color:var(--brand-blue); flex-shrink:0;">
                                        <div>
                                            <div style="font-weight:700; font-size:0.88rem; color:var(--text-primary);">₹ Flat Amount</div>
                                            <div style="font-size:0.74rem; color:var(--text-muted); margin-top:1px;">Fixed rupee off order total</div>
                                        </div>
                                    </label>
                                    <label id="labelPct" style="display:flex; align-items:center; gap:12px; padding:14px 16px; border:2px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; transition:var(--transition);">
                                        <input type="radio" name="type" value="percentage" onchange="toggleDiscountType()" style="width:16px; height:16px; accent-color:var(--brand-blue); flex-shrink:0;">
                                        <div>
                                            <div style="font-weight:700; font-size:0.88rem; color:var(--text-primary);">% Percentage</div>
                                            <div style="font-size:0.74rem; color:var(--text-muted); margin-top:1px;">Percent off order subtotal</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Value + Cap -->
                            <div class="grid-2-cols" style="margin-bottom:16px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" id="valueLabel">Discount Value (₹) <span style="color:var(--status-danger);">*</span></label>
                                    <input type="number" name="value" id="valueInput" class="form-input" required min="1" step="1"
                                           placeholder="200" oninput="updatePreview()"
                                           style="font-size:1rem; font-weight:700;">
                                </div>
                                <div class="form-group" id="maxCapField" style="margin-bottom:0; display:none;">
                                    <label class="form-label">Max Discount Cap (₹)</label>
                                    <input type="number" name="max_discount_cap" class="form-input" min="1" step="1" placeholder="Unlimited">
                                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Maximum saving for % coupons.</p>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Minimum Cart Value (₹)</label>
                                <input type="number" name="min_cart_value" class="form-input" min="0" step="1" placeholder="0 (no minimum)">
                                <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Coupon is applied only when cart total meets this amount.</p>
                            </div>
                        </div>

                        <!-- Validity Window -->
                        <div class="card-panel" style="gap:0;">
                            <div class="panel-header" style="border-bottom:1px solid var(--border-color-light); padding-bottom:16px; margin-bottom:20px;">
                                <div class="panel-title">
                                    <div style="width:32px; height:32px; border-radius:var(--radius-md); background:var(--brand-orange-light); display:flex; align-items:center; justify-content:center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-orange)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    Validity Window
                                </div>
                            </div>

                            <div class="grid-2-cols">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Start Date &amp; Time</label>
                                    <input type="datetime-local" name="starts_at" class="form-input">
                                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Leave empty — coupon is active immediately.</p>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Expiry Date &amp; Time</label>
                                    <input type="datetime-local" name="expires_at" class="form-input">
                                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Leave empty — coupon never expires.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- RIGHT: Settings + Preview + Submit                             -->
                    <!-- ============================================================ -->
                    <div style="display:flex; flex-direction:column; gap:20px;">

                        <!-- Live Preview -->
                        <div class="card-panel" style="background:linear-gradient(135deg, var(--brand-purple-light) 0%, var(--brand-blue-light) 100%); border-color:var(--brand-purple-border); gap:0;">
                            <div style="font-size:0.74rem; font-weight:700; color:var(--brand-purple); text-transform:uppercase; letter-spacing:.6px; margin-bottom:14px;">Live Preview</div>
                            <div id="previewBadge"
                                 style="font-family:monospace; font-size:1.4rem; font-weight:900; color:var(--brand-purple); letter-spacing:2.5px; padding:16px; border:2px dashed var(--brand-purple-border); border-radius:var(--radius-md); background:rgba(140,48,245,0.04); text-align:center; word-break:break-all;">
                                <?= e($suggested ?? 'YOUR CODE') ?>
                            </div>
                            <div id="previewValue" style="font-size:0.84rem; color:var(--text-secondary); margin-top:10px; text-align:center; min-height:20px; font-weight:500;"></div>
                        </div>

                        <!-- Usage Limits -->
                        <div class="card-panel" style="gap:0;">
                            <div class="panel-header" style="margin-bottom:16px;">
                                <div class="panel-title" style="font-size:0.9rem;">Usage Limits</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total Uses (Global)</label>
                                <input type="number" name="usage_limit_global" class="form-input" min="1" step="1" placeholder="Unlimited">
                                <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Max times this coupon can be redeemed across all customers.</p>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Uses Per Customer</label>
                                <input type="number" name="usage_limit_per_user" class="form-input" min="1" step="1" value="1">
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div class="card-panel" style="gap:0;">
                            <div class="panel-header" style="margin-bottom:16px;">
                                <div class="panel-title" style="font-size:0.9rem;">Visibility</div>
                            </div>
                            <label style="display:flex; align-items:center; gap:12px; cursor:pointer; padding:12px 14px; border:1.5px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-surface-secondary); transition:var(--transition);"
                                   onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.background='var(--brand-blue-light)'"
                                   onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-surface-secondary)'">
                                <input type="checkbox" name="is_public" value="1" checked style="width:17px; height:17px; accent-color:var(--brand-blue); flex-shrink:0; border-radius:4px;">
                                <div>
                                    <div style="font-weight:700; font-size:0.88rem; color:var(--text-primary);">Publicly Visible</div>
                                    <div style="font-size:0.74rem; color:var(--text-muted); margin-top:2px;">Show in promo sections &amp; customer-facing campaigns.</div>
                                </div>
                            </label>
                        </div>

                        <!-- Actions -->
                        <button type="submit"
                                style="width:100%; height:48px; background:var(--gradient-primary); color:#fff; border-radius:var(--radius-md); font-size:0.95rem; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 28px -4px rgba(45,130,255,.45)'"
                                onmouseout="this.style.transform=''; this.style.boxShadow='var(--shadow-glow-blue)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Create Coupon
                        </button>
                        <a href="<?= url('admin/coupons') ?>"
                           style="display:flex; align-items:center; justify-content:center; height:44px; border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:0.88rem; font-weight:600; color:var(--text-secondary); text-decoration:none; transition:var(--transition);"
                           onmouseover="this.style.borderColor='var(--border-color-light)'; this.style.background='var(--bg-surface-secondary)'"
                           onmouseout="this.style.borderColor='var(--border-color)'; this.style.background=''">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
function toggleDiscountType() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const lFlat = document.getElementById('labelFlat');
    const lPct  = document.getElementById('labelPct');
    const vLabel = document.getElementById('valueLabel');
    const vInput = document.getElementById('valueInput');
    const capField = document.getElementById('maxCapField');

    const activeStyle = 'border-color:var(--brand-blue); background:var(--brand-blue-light);';
    const inactiveStyle = 'border-color:var(--border-color); background:transparent;';

    if (type === 'percentage') {
        lFlat.style.cssText += inactiveStyle;
        lPct.style.cssText  += activeStyle;
        lFlat.style.borderColor = 'var(--border-color)';
        lFlat.style.background  = 'transparent';
        lPct.style.borderColor  = 'var(--brand-blue)';
        lPct.style.background   = 'var(--brand-blue-light)';
        vLabel.innerHTML = 'Discount Value (%) <span style="color:var(--status-danger);">*</span>';
        vInput.setAttribute('max', '100');
        vInput.placeholder = '10';
        capField.style.display = 'block';
    } else {
        lFlat.style.borderColor = 'var(--brand-blue)';
        lFlat.style.background  = 'var(--brand-blue-light)';
        lPct.style.borderColor  = 'var(--border-color)';
        lPct.style.background   = 'transparent';
        vLabel.innerHTML = 'Discount Value (₹) <span style="color:var(--status-danger);">*</span>';
        vInput.removeAttribute('max');
        vInput.placeholder = '200';
        capField.style.display = 'none';
    }
    updatePreview();
}

function updatePreview() {
    const code  = (document.getElementById('couponCode')?.value || 'YOUR CODE').toUpperCase();
    const type  = document.querySelector('input[name="type"]:checked')?.value || 'flat';
    const value = parseInt(document.getElementById('valueInput')?.value) || 0;

    document.getElementById('previewBadge').textContent = code;

    let desc = '';
    if (value > 0) {
        desc = type === 'percentage'
            ? `Save ${value}% on your order`
            : `Get ₹${value.toLocaleString('en-IN')} off your order`;
    }
    document.getElementById('previewValue').textContent = desc;
}

function generateCode() {
    fetch('<?= url('admin/coupons/generate-code') ?>')
        .then(r => r.json())
        .then(d => {
            document.getElementById('couponCode').value = d.code;
            updatePreview();
        })
        .catch(console.error);
}

document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
