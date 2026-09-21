<?php
$title = 'Order #' . htmlspecialchars($order['order_number']) . ' | Staff Portal';
include __DIR__ . '/../layouts/header.php';

$orderNumber = htmlspecialchars($order['order_number']);
$encryptedId = htmlspecialchars($order['encrypted_id'] ?? encrypt_id($order['id']));
$payStatus   = strtolower($order['payment_status'] ?? 'pending');
$orderStatus = strtolower($order['status'] ?? 'pending');
$canEdit     = $canEdit ?? (function_exists('staff_can') ? staff_can('orders', 'edit') : false);

$payBg = match($payStatus) {
    'paid', 'completed' => '#ECFDF5',
    'failed' => '#FEF2F2',
    'refunded' => '#EEF2FF',
    default => '#FFFBEB'
};
$payColor = match($payStatus) {
    'paid', 'completed' => '#065F46',
    'failed' => '#991B1B',
    'refunded' => '#3730A3',
    default => '#92400E'
};

$ordBg = match($orderStatus) {
    'delivered' => '#ECFDF5',
    'shipped', 'out_for_delivery' => '#EFF6FF',
    'cancelled' => '#FEF2F2',
    'confirmed', 'packed' => '#F5F3FF',
    default => '#FFFBEB'
};
$ordColor = match($orderStatus) {
    'delivered' => '#065F46',
    'shipped', 'out_for_delivery' => '#1E40AF',
    'cancelled' => '#991B1B',
    'confirmed', 'packed' => '#5B21B6',
    default => '#92400E'
};

// Stepper Stages
$stepperStages = [
    'pending'          => 'Placed',
    'confirmed'        => 'Confirmed',
    'packed'           => 'Packed',
    'shipped'          => 'Shipped',
    'out_for_delivery' => 'Out for Delivery',
    'delivered'        => 'Delivered'
];
$stageKeys = array_keys($stepperStages);
$currentStageIndex = array_search($orderStatus, $stageKeys, true);
if ($currentStageIndex === false && $orderStatus !== 'cancelled') {
    $currentStageIndex = 0;
}
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #64748B; margin-bottom: 16px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #4F46E5; text-decoration: none;">Dashboard</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/orders') ?>" style="color: #4F46E5; text-decoration: none;">Orders</a>
                <span>&rsaquo;</span>
                <span style="font-weight: 700; color: #0F172A;">#<?= $orderNumber ?></span>
            </div>

            <!-- Detail Header Banner -->
            <div class="welcome-banner" style="background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 60%, #312E81 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0;">
                            Order #<?= $orderNumber ?>
                        </h1>
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: <?= $ordBg ?>; color: <?= $ordColor ?>;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $ordColor ?>;"></span>
                            <?= ucfirst(str_replace('_', ' ', $orderStatus)) ?>
                        </span>
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; background: <?= $payBg ?>; color: <?= $payColor ?>;">
                            Payment: <?= ucfirst($payStatus) ?>
                        </span>
                    </div>
                    <p style="font-size: 0.86rem; color: #CBD5E1; margin: 0;">
                        Placed on <strong><?= date('l, d F Y \a\t h:i A', strtotime($order['placed_at'])) ?></strong> &bull; Method: <?= htmlspecialchars($order['payment_method']) ?>
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="<?= url('portal/orders/' . $encryptedId . '/invoice') ?>" target="_blank" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <span>Print Tax Invoice</span>
                    </a>

                    <?php if (function_exists('staff_can') && staff_can('returns', 'process')): ?>
                        <a href="<?= url('portal/returns/create?order_id=' . $encryptedId) ?>" style="background: rgba(238, 242, 255, 0.15); border: 1px solid rgba(238, 242, 255, 0.3); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(238,242,255,0.25)';" onmouseout="this.style.background='rgba(238,242,255,0.15)';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                            <span>Initiate RMA</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Fulfillment Stepper -->
            <?php if ($orderStatus !== 'cancelled'): ?>
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.76rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 16px;">
                        Fulfillment Progress
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                        <?php foreach ($stepperStages as $idx => $stgKey): ?>
                            <?php
                            $stepIndex = array_search($idx, $stageKeys, true);
                            $isDone = ($stepIndex <= $currentStageIndex);
                            $isCurrent = ($stepIndex === $currentStageIndex);
                            ?>
                            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1; position: relative; z-index: 2;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; background: <?= $isDone ? '#4F46E5' : '#F1F5F9' ?>; color: <?= $isDone ? '#FFFFFF' : '#94A3B8' ?>; border: 2px solid <?= $isCurrent ? '#4F46E5' : ($isDone ? '#4F46E5' : '#E2E8F0') ?>; box-shadow: <?= $isCurrent ? '0 0 0 4px rgba(79, 70, 229, 0.2)' : 'none' ?>;">
                                    <?php if ($isDone): ?>
                                        &#10003;
                                    <?php else: ?>
                                        <?= $stepIndex + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 0.72rem; font-weight: <?= $isCurrent ? '800' : '600' ?>; color: <?= $isCurrent ? '#4F46E5' : ($isDone ? '#0F172A' : '#94A3B8') ?>; margin-top: 6px;">
                                    <?= htmlspecialchars($stgKey) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Order Details Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">

                <!-- LEFT COLUMN: Items, Financials, History -->
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- Order Items Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between;">
                            <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                Ordered Line Items (<?= count($order['items'] ?? []) ?>)
                            </h3>
                            <span style="font-size: 0.76rem; color: #64748B;">SKU &amp; Pricing Ledger</span>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.84rem;">
                                <thead>
                                    <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #64748B; font-size: 0.72rem; text-transform: uppercase;">
                                        <th style="padding: 12px 18px;">Product</th>
                                        <th style="padding: 12px 18px;">SKU</th>
                                        <th style="padding: 12px 18px; text-align: center;">Qty</th>
                                        <th style="padding: 12px 18px; text-align: right;">Unit Price</th>
                                        <th style="padding: 12px 18px; text-align: right;">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($order['items'])): ?>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <?php
                                            $unitPrice = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                                            $qty = (int)($item['quantity'] ?? 1);
                                            $lineTotal = (float)($item['line_total'] ?? $item['total'] ?? ($unitPrice * $qty));
                                            $variantDesc = $item['variant_info'] ?? $item['variant_name'] ?? '';
                                            ?>
                                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                                <td style="padding: 14px 18px;">
                                                    <div style="font-weight: 700; color: #0F172A;">
                                                        <?= htmlspecialchars($item['product_name'] ?? 'Bespoke Garment') ?>
                                                    </div>
                                                    <?php if (!empty($variantDesc) || !empty($item['size'])): ?>
                                                        <div style="font-size: 0.72rem; color: #64748B;">
                                                            <?= htmlspecialchars($variantDesc) ?>
                                                            <?= !empty($item['size']) && strpos($variantDesc, $item['size']) === false ? ' &bull; Size: ' . htmlspecialchars($item['size']) : '' ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 14px 18px; color: #64748B; font-family: monospace;">
                                                    <?= htmlspecialchars($item['sku'] ?? 'N/A') ?>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: center; font-weight: 700; color: #0F172A;">
                                                    <?= $qty ?>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: right; color: #475569;">
                                                    ₹<?= number_format($unitPrice, 2) ?>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: right; font-weight: 800; color: #0F172A;">
                                                    ₹<?= number_format($lineTotal, 2) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="padding: 20px; text-align: center; color: #94A3B8;">
                                                No line items recorded for this order.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Summary -->
                        <div style="padding: 18px 20px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end;">
                            <div style="width: 100%; max-width: 320px; font-size: 0.84rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #64748B;">
                                    <span>Subtotal:</span>
                                    <strong style="color: #0F172A;">₹<?= number_format((float)($order['subtotal'] ?? 0), 2) ?></strong>
                                </div>
                                <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #059669;">
                                        <span>Promotional Discount:</span>
                                        <strong>-₹<?= number_format((float)$order['discount_amount'], 2) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #64748B;">
                                    <span>Estimated Shipping:</span>
                                    <strong style="color: #0F172A;">₹<?= number_format((float)($order['shipping_amount'] ?? 0), 2) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #64748B;">
                                    <span>GST / Tax:</span>
                                    <strong style="color: #0F172A;">₹<?= number_format((float)($order['tax_amount'] ?? 0), 2) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid #E2E8F0; font-size: 1.05rem; font-weight: 800; color: #0F172A;">
                                    <span>Grand Total:</span>
                                    <span style="color: #4F46E5;">₹<?= number_format((float)($order['grand_total'] ?? 0), 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status History Audit Timeline -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 16px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Fulfillment Audit Timeline
                        </h3>
                        <?php if (!empty($order['history'])): ?>
                            <div style="position: relative; padding-left: 20px; border-left: 2px solid #EEF2FF;">
                                <?php foreach ($order['history'] as $hist): ?>
                                    <div style="margin-bottom: 16px; position: relative;">
                                        <div style="position: absolute; left: -26px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: #4F46E5;"></div>
                                        <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A;">
                                            Status set to "<?= ucfirst(str_replace('_', ' ', $hist['status'] ?? '')) ?>"
                                        </div>
                                        <?php
                                        $histTime = $hist['changed_at'] ?? $hist['created_at'] ?? null;
                                        ?>
                                        <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;">
                                            <?= !empty($histTime) ? date('d M Y, h:i A', strtotime($histTime)) : 'Recorded' ?>
                                            <?= !empty($hist['admin_name']) ? ' &bull; by ' . htmlspecialchars($hist['admin_name']) : '' ?>
                                        </div>
                                        <?php if (!empty($hist['notes'])): ?>
                                            <div style="font-size: 0.76rem; color: #475569; background: #F8FAFC; padding: 6px 10px; border-radius: 6px; margin-top: 6px;">
                                                <?= htmlspecialchars($hist['notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 0.8rem; color: #94A3B8; margin: 0;">Initial order placement recorded.</p>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Customer, Shipping, Actions -->
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- Fulfillment Stage Changer Card (Guarded by orders:edit) -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                Fulfillment Stage
                            </h3>
                            <?php if ($canEdit): ?>
                                <span style="font-size: 0.68rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 7px; border-radius: 4px;">
                                    Permitted
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.68rem; font-weight: 700; background: #FEF2F2; color: #991B1B; padding: 2px 7px; border-radius: 4px;">
                                    View-Only
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($canEdit): ?>
                            <form action="<?= url('portal/orders/' . $encryptedId . '/status') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Advance Status
                                    </label>
                                    <select name="status" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600;">
                                        <option value="pending" <?= $orderStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $orderStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="packed" <?= $orderStatus === 'packed' ? 'selected' : '' ?>>Packed</option>
                                        <option value="shipped" <?= $orderStatus === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="out_for_delivery" <?= $orderStatus === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                        <option value="delivered" <?= $orderStatus === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $orderStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div style="margin-bottom: 14px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Audit Note
                                    </label>
                                    <textarea name="note" rows="2" placeholder="Optional fulfillment memo" style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 6px 10px; font-size: 0.8rem; box-sizing: border-box;"></textarea>
                                </div>
                                <button type="submit" style="width: 100%; height: 38px; border-radius: 8px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                                    Update Stage
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="font-size: 0.8rem; color: #64748B; background: #F8FAFC; padding: 12px; border-radius: 8px;">
                                Current Status: <strong style="color: #0F172A;"><?= ucfirst(str_replace('_', ' ', $orderStatus)) ?></strong>
                                <p style="margin: 6px 0 0 0; font-size: 0.74rem; color: #94A3B8;">
                                    Your role clearance is set to read-only. Status transitions can only be authorized by an Orders &amp; Logistics Manager.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Courier Tracking & Logistics Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Courier Logistics &amp; AWB
                        </h3>

                        <?php if ($canEdit): ?>
                            <form action="<?= url('portal/orders/' . $encryptedId . '/tracking') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Courier Partner
                                    </label>
                                    <input type="text" name="courier_partner" value="<?= htmlspecialchars($order['courier_partner'] ?? '') ?>" placeholder="e.g. Blue Dart, Delhivery, DTDC" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; box-sizing: border-box;">
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        AWB Waybill Tracking Number
                                    </label>
                                    <input type="text" name="awb_number" value="<?= htmlspecialchars($order['awb_number'] ?? '') ?>" placeholder="e.g. 78291039841" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; box-sizing: border-box;">
                                </div>
                                <div style="margin-bottom: 14px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Estimated Delivery Date
                                    </label>
                                    <input type="date" name="estimated_delivery" value="<?= htmlspecialchars($order['estimated_delivery'] ?? '') ?>" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; box-sizing: border-box;">
                                </div>
                                <button type="submit" style="width: 100%; height: 38px; border-radius: 8px; background: #0D9488; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#0F766E';" onmouseout="this.style.background='#0D9488';">
                                    Save Tracking Info
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="font-size: 0.82rem; line-height: 1.6;">
                                <div><span style="color: #64748B;">Partner:</span> <strong><?= htmlspecialchars($order['courier_partner'] ?? 'Not assigned') ?></strong></div>
                                <div><span style="color: #64748B;">AWB #:</span> <strong><?= htmlspecialchars($order['awb_number'] ?? 'Not generated') ?></strong></div>
                                <div><span style="color: #64748B;">Est. Delivery:</span> <strong><?= htmlspecialchars($order['estimated_delivery'] ?? 'Pending dispatch') ?></strong></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($order['awb_number'])): ?>
                            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #E2E8F0; display: flex; flex-direction: column; gap: 8px;">
                                <a href="<?= url('portal/shipments/' . $encryptedId) ?>" style="font-size: 0.78rem; font-weight: 700; color: #047857; text-decoration: none; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Live Tracking Milestones</span>
                                    <span>&rarr;</span>
                                </a>
                                <a href="<?= url('portal/shipments/' . $encryptedId . '/label') ?>" target="_blank" style="font-size: 0.78rem; font-weight: 700; color: #4F46E5; text-decoration: none; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Print Thermal Shipping Label</span>
                                    <span>&#128438;</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Customer Profile & Delivery Address Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Customer &amp; Delivery Destination
                        </h3>
                        <div style="font-size: 0.84rem; line-height: 1.5; color: #334155;">
                            <div style="font-weight: 800; color: #0F172A; font-size: 0.95rem; margin-bottom: 2px;">
                                <?= htmlspecialchars($order['shipping_name'] ?? $order['customer_name'] ?? 'Recipient') ?>
                            </div>
                            <div style="color: #64748B; margin-bottom: 2px;">
                                <?= htmlspecialchars($order['shipping_phone'] ?? $order['customer_phone'] ?? 'No phone recorded') ?>
                            </div>
                            <div style="color: #64748B; margin-bottom: 12px;">
                                <?= htmlspecialchars($order['customer_email'] ?? 'No email provided') ?>
                            </div>

                            <div style="padding-top: 10px; border-top: 1px solid #F1F5F9; font-size: 0.8rem; color: #475569;">
                                <div style="font-weight: 700; color: #0F172A; margin-bottom: 4px;">Shipping Address</div>
                                <div><?= htmlspecialchars($order['shipping_address'] ?? '') ?></div>
                                <div>
                                    <?= htmlspecialchars($order['shipping_city'] ?? '') ?>, 
                                    <?= htmlspecialchars($order['shipping_state'] ?? '') ?> - 
                                    <?= htmlspecialchars($order['shipping_pincode'] ?? '') ?>
                                </div>
                                <div><?= htmlspecialchars($order['shipping_country'] ?? 'India') ?></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
