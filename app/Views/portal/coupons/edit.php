<?php
/**
 * Portal Coupons Edit View
 * Luxury Jiyaji LX Operations Theme
 * 
 * @var array $coupon
 * @var array $restrictions
 * @var array $categories
 * @var array $products
 * @var array $selectedCategoryIds
 * @var array $selectedProductIds
 */
$coupon = $coupon ?? [];
$categories = $categories ?? [];
$products = $products ?? [];
$selectedCategoryIds = $selectedCategoryIds ?? [];
$selectedProductIds = $selectedProductIds ?? [];

$id = (int)($coupon['id'] ?? 0);
$type = $coupon['type'] ?? 'percentage';
$startsAtVal = !empty($coupon['starts_at']) ? date('Y-m-d\TH:i', strtotime($coupon['starts_at'])) : '';
$expiresAtVal = !empty($coupon['expires_at']) ? date('Y-m-d\TH:i', strtotime($coupon['expires_at'])) : '';
?>

<div class="space-y-8 animate-fadeIn max-w-6xl mx-auto pb-12">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/5 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gold/70 tracking-wider uppercase mb-1">
                <a href="<?= url('portal/coupons') ?>" class="hover:text-gold transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Campaigns & Offers
                </a>
                <span>/</span>
                <a href="<?= url('portal/coupons/' . $id) ?>" class="hover:text-gold transition-colors font-mono">
                    <?= htmlspecialchars($coupon['code'] ?? '') ?>
                </a>
                <span>/</span>
                <span class="text-white/40">Modify</span>
            </div>
            <h1 class="text-3xl font-display font-light text-white tracking-wide">
                Refine Campaign <span class="text-gold font-normal font-mono"><?= htmlspecialchars($coupon['code'] ?? '') ?></span>
            </h1>
            <p class="text-sm text-white/50 font-light mt-1">
                Adjust discount margins, threshold triggers, customer quotas, or catalog limits.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= url('portal/coupons/' . $id) ?>" 
               class="px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-white/80 text-xs tracking-wider uppercase transition-all duration-200">
                Cancel
            </a>
            <button type="submit" form="couponEditForm"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-gold via-amber-400 to-gold text-rich-black font-medium text-xs tracking-wider uppercase shadow-lg shadow-gold/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Save Refinements
            </button>
        </div>
    </div>

    <!-- Main Edit Form -->
    <form id="couponEditForm" action="<?= url('portal/coupons/' . $id . '/update') ?>" method="POST" class="space-y-8">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Primary Configuration -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card 1: Core Identification & Code -->
                <div class="bg-card-bg/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute -right-12 -top-12 w-36 h-36 bg-gold/5 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex items-center gap-3 mb-6 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-gold/10 text-gold flex items-center justify-center border border-gold/20 font-bold text-xs">
                            01
                        </div>
                        <div>
                            <h2 class="text-base font-medium text-white tracking-wide">Identity & Voucher Code</h2>
                            <p class="text-xs text-white/40">Campaign voucher string and marketing narrative</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <!-- Code -->
                        <div>
                            <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                                Promo Code <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" name="code" id="couponCodeInput" required maxlength="50"
                                   value="<?= htmlspecialchars($coupon['code'] ?? '') ?>"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm font-mono text-gold uppercase tracking-wider focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all">
                            <p class="text-[11px] text-white/40 mt-1.5 font-light">
                                Changing the promo code takes effect immediately for all pending carts.
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                                Campaign Narrative / Description
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white/90 placeholder-white/20 focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all font-light"><?= htmlspecialchars($coupon['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Discount Value & Mechanics -->
                <div class="bg-card-bg/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center gap-3 mb-6 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 font-bold text-xs">
                            02
                        </div>
                        <div>
                            <h2 class="text-base font-medium text-white tracking-wide">Discount Engine & Thresholds</h2>
                            <p class="text-xs text-white/40">Percentage vs flat value deduction with margin protection ceilings</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Discount Type -->
                        <div>
                            <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                                Benefit Structure <span class="text-rose-400">*</span>
                            </label>
                            <select name="type" id="discountTypeSelect" required
                                    class="w-full bg-rich-black/60 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-gold/50 transition-all">
                                <option value="percentage" <?= $type === 'percentage' ? 'selected' : '' ?>>Percentage Deduction (%)</option>
                                <option value="flat" <?= $type === 'flat' ? 'selected' : '' ?>>Fixed / Flat Amount (₹)</option>
                            </select>
                        </div>

                        <!-- Discount Value -->
                        <div>
                            <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2" id="discountValueLabel">
                                Percentage Rate (%) <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40 text-sm font-medium" id="valuePrefixSymbol"><?= $type === 'percentage' ? '%' : '₹' ?></span>
                                <input type="number" step="0.01" min="0.01" name="value" id="discountValueInput" required
                                       value="<?= htmlspecialchars($coupon['value'] ?? '0') ?>"
                                       class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all">
                            </div>
                        </div>

                        <!-- Max Discount Amount (Only for percentage) -->
                        <div id="maxDiscountContainer" style="<?= $type === 'flat' ? 'display: none;' : '' ?>">
                            <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                                Max Discount Ceiling (₹) <span class="text-white/40 font-normal">(Optional Cap)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40 text-sm font-medium">₹</span>
                                <input type="number" step="0.01" min="0" name="max_discount_amount"
                                       value="<?= htmlspecialchars($coupon['max_discount_amount'] ?? '') ?>"
                                       placeholder="e.g. 1500 (No limit if blank)"
                                       class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all">
                            </div>
                            <p class="text-[11px] text-white/40 mt-1">Guards margins on massive luxury carts.</p>
                        </div>

                        <!-- Minimum Cart Value -->
                        <div>
                            <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                                Minimum Order Value (₹) <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40 text-sm font-medium">₹</span>
                                <input type="number" step="0.01" min="0" name="min_cart_value" required
                                       value="<?= htmlspecialchars($coupon['min_cart_value'] ?? '0.00') ?>"
                                       class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all">
                            </div>
                            <p class="text-[11px] text-white/40 mt-1">Cart subtotal required before coupon unlocks.</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Catalog Targeting & Restrictions -->
                <div class="bg-card-bg/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-6 border-b border-white/5 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center border border-sky-500/20 font-bold text-xs">
                                03
                            </div>
                            <div>
                                <h2 class="text-base font-medium text-white tracking-wide">Catalog Restrictions</h2>
                                <p class="text-xs text-white/40">Scope coupon exclusively to chosen categories or products</p>
                            </div>
                        </div>
                        <span class="text-[11px] px-2.5 py-1 rounded-full bg-white/5 text-white/60 border border-white/10">
                            Leave empty for storewide
                        </span>
                    </div>

                    <div class="space-y-6">
                        <!-- Category Restrictions -->
                        <div>
                            <label class="block text-xs font-medium text-white/80 uppercase tracking-wider mb-2">
                                Targeted Categories
                            </label>
                            <?php if (empty($categories)): ?>
                                <p class="text-xs text-white/30 italic">No categories found in store catalog.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto p-3 rounded-xl bg-rich-black/40 border border-white/5">
                                    <?php foreach ($categories as $cat): ?>
                                        <?php $catChecked = in_array((int)$cat['id'], $selectedCategoryIds, true); ?>
                                        <label class="flex items-center gap-2.5 p-2 rounded-lg bg-white/[0.02] hover:bg-white/[0.06] border border-white/5 cursor-pointer text-xs transition-colors">
                                            <input type="checkbox" name="categories[]" value="<?= (int)$cat['id'] ?>"
                                                   <?= $catChecked ? 'checked' : '' ?>
                                                   class="rounded border-white/20 bg-white/5 text-gold focus:ring-gold focus:ring-offset-0">
                                            <span class="text-white/80 truncate"><?= htmlspecialchars($cat['name'] ?? 'Category #'.$cat['id']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <p class="text-[11px] text-white/40 mt-1.5">When chosen, at least one item in cart must belong to selected categories.</p>
                        </div>

                        <!-- Product Restrictions -->
                        <div>
                            <label class="block text-xs font-medium text-white/80 uppercase tracking-wider mb-2">
                                Targeted Specific Products
                            </label>
                            <?php if (empty($products)): ?>
                                <p class="text-xs text-white/30 italic">No products found in store catalog.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-56 overflow-y-auto p-3 rounded-xl bg-rich-black/40 border border-white/5">
                                    <?php foreach ($products as $prod): ?>
                                        <?php $prodChecked = in_array((int)$prod['id'], $selectedProductIds, true); ?>
                                        <label class="flex items-center gap-2.5 p-2 rounded-lg bg-white/[0.02] hover:bg-white/[0.06] border border-white/5 cursor-pointer text-xs transition-colors">
                                            <input type="checkbox" name="products[]" value="<?= (int)$prod['id'] ?>"
                                                   <?= $prodChecked ? 'checked' : '' ?>
                                                   class="rounded border-white/20 bg-white/5 text-gold focus:ring-gold focus:ring-offset-0">
                                            <div class="truncate flex-1">
                                                <span class="text-white/80 truncate block"><?= htmlspecialchars($prod['name'] ?? 'Product #'.$prod['id']) ?></span>
                                                <span class="text-[10px] text-white/40 font-mono">₹<?= number_format((float)($prod['price'] ?? 0), 2) ?></span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <p class="text-[11px] text-white/40 mt-1.5">Restricts discount calculation to only matching items in checkout.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Limits, Lifespan & Status Controls -->
            <div class="space-y-6">

                <!-- Publishing & Visibility -->
                <div class="bg-card-bg/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20 font-bold text-xs">
                            04
                        </div>
                        <div>
                            <h2 class="text-base font-medium text-white tracking-wide">Publishing Status</h2>
                            <p class="text-xs text-white/40">Activate and broadcast settings</p>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-white">Campaign Active</p>
                            <p class="text-[11px] text-white/40">Allow redemptions now</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= !empty($coupon['is_active']) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Public Visibility Toggle -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-white">Publicly Broadcast</p>
                            <p class="text-[11px] text-white/40">Show on store header & checkout</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" <?= !empty($coupon['is_public']) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gold"></div>
                        </label>
                    </div>
                </div>

                <!-- Redemption Capacity & Limits -->
                <div class="bg-card-bg/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20 font-bold text-xs">
                            05
                        </div>
                        <div>
                            <h2 class="text-base font-medium text-white tracking-wide">Usage Governance</h2>
                            <p class="text-xs text-white/40">Total budget and customer caps</p>
                        </div>
                    </div>

                    <!-- Usage info display -->
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-between text-xs">
                        <span class="text-white/50">Current Redemptions:</span>
                        <span class="font-mono font-bold text-white"><?= (int)($coupon['times_used'] ?? 0) ?> orders</span>
                    </div>

                    <!-- Total Usage Limit -->
                    <div>
                        <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                            Total Redemptions Cap
                        </label>
                        <input type="number" min="0" name="usage_limit"
                               value="<?= htmlspecialchars($coupon['usage_limit'] ?? '') ?>"
                               placeholder="e.g. 500 (Blank for unlimited)"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all">
                        <p class="text-[11px] text-white/40 mt-1">Maximum lifetime redemptions across all users.</p>
                    </div>

                    <!-- Per User Limit -->
                    <div>
                        <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                            Redemptions Per Customer
                        </label>
                        <input type="number" min="1" name="per_user_limit"
                               value="<?= htmlspecialchars($coupon['per_user_limit'] ?? '1') ?>"
                               placeholder="1"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all">
                        <p class="text-[11px] text-white/40 mt-1">Default 1 ensures fair individual redemption.</p>
                    </div>
                </div>

                <!-- Lifespan & Schedule -->
                <div class="bg-card-bg/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20 font-bold text-xs">
                            06
                        </div>
                        <div>
                            <h2 class="text-base font-medium text-white tracking-wide">Validity Schedule</h2>
                            <p class="text-xs text-white/40">Campaign start and expiration times</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                            Launch Date & Time
                        </label>
                        <input type="datetime-local" name="starts_at"
                               value="<?= $startsAtVal ?>"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-gold/50 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-white/70 uppercase tracking-wider mb-2">
                            Expiration Date & Time
                        </label>
                        <input type="datetime-local" name="expires_at"
                               value="<?= $expiresAtVal ?>"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-gold/50 transition-all">
                        <p class="text-[11px] text-white/40 mt-1">Leave empty for evergreen promotion.</p>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('discountTypeSelect');
    const valueLabel = document.getElementById('discountValueLabel');
    const valuePrefix = document.getElementById('valuePrefixSymbol');
    const valueInput = document.getElementById('discountValueInput');
    const maxDiscountContainer = document.getElementById('maxDiscountContainer');

    function syncTypeUI() {
        if (!typeSelect) return;
        if (typeSelect.value === 'percentage') {
            valueLabel.innerHTML = 'Percentage Rate (%) <span class="text-rose-400">*</span>';
            valuePrefix.textContent = '%';
            valueInput.placeholder = '15.00';
            valueInput.max = '100';
            maxDiscountContainer.style.display = 'block';
        } else {
            valueLabel.innerHTML = 'Fixed Amount Deduction (₹) <span class="text-rose-400">*</span>';
            valuePrefix.textContent = '₹';
            valueInput.placeholder = '500.00';
            valueInput.removeAttribute('max');
            maxDiscountContainer.style.display = 'none';
        }
    }

    typeSelect?.addEventListener('change', syncTypeUI);
});
</script>
