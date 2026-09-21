<?php
include __DIR__ . '/../layouts/header.php';

$orderNumber = htmlspecialchars($order['order_number']);
$encryptedId = htmlspecialchars($order['encrypted_id']);
$payStatus   = strtolower($order['payment_status'] ?? 'pending');
$orderStatus = strtolower($order['status'] ?? 'pending');

$payBadgeClass = match($payStatus) {
    'paid', 'completed' => 'badge-paid',
    'failed' => 'badge-failed',
    'refunded' => 'badge-shipped',
    default => 'badge-pending'
};

$ordBadgeClass = match($orderStatus) {
    'delivered' => 'badge-delivered',
    'shipped', 'out_for_delivery' => 'badge-shipped',
    'cancelled' => 'badge-cancelled',
    'confirmed', 'packed' => 'badge-pending',
    default => 'badge-processing'
};

// Fulfillment Stepper Stages
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

        <main class="dashboard-content">
            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 14px;">
                <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                <span>&nbsp;/&nbsp;</span>
                <a href="<?= url('admin/orders') ?>" style="color: var(--brand-blue);">Orders</a>
                <span>&nbsp;/&nbsp;</span>
                <span><?= $orderNumber ?></span>
            </div>

            <!-- Detail Header Banner -->
            <div class="welcome-banner" style="margin-bottom: 24px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <h1 class="welcome-title" style="margin-bottom: 0;">Order <?= $orderNumber ?></h1>
                        <span class="badge <?= $ordBadgeClass ?>" style="font-size: 0.82rem; padding: 4px 12px;">
                            <?= ucfirst(str_replace('_', ' ', $orderStatus)) ?>
                        </span>
                        <span class="badge <?= $payBadgeClass ?>" style="font-size: 0.82rem; padding: 4px 12px;">
                            Payment: <?= ucfirst($payStatus) ?>
                        </span>
                    </div>
                    <p class="welcome-subtitle" style="margin-top: 6px;">
                        Placed on <strong><?= date('l, d F Y \a\t h:i A', strtotime($order['placed_at'])) ?></strong> &bull; Method: <?= htmlspecialchars($order['payment_method']) ?>
                    </p>
                </div>

                <div class="banner-controls">
                    <a href="<?= url('admin/orders/' . $encryptedId . '/shipping-label') ?>" target="_blank" class="btn-export" style="background: rgba(140, 48, 245, 0.08); border-color: rgba(140, 48, 245, 0.25); color: var(--brand-purple);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        <span>Shipping Label</span>
                    </a>
                    <a href="<?= url('admin/orders/' . $encryptedId . '/invoice') ?>" target="_blank" class="btn-export" style="background: var(--brand-blue-light); border-color: var(--brand-blue-border); color: var(--brand-blue);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <span>Tax Invoice</span>
                    </a>
                    <a href="<?= url('admin/orders') ?>" class="btn-export">
                        &larr; Back to Orders
                    </a>
                </div>
            </div>

            <!-- Fulfillment Progress Stepper -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 22px 28px;">
                <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-primary); margin-bottom: 18px;">
                    Fulfillment Progress
                </div>

                <?php if ($orderStatus === 'cancelled'): ?>
                    <div style="padding: 14px 18px; border-radius: var(--radius-md); background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <span>This order was cancelled. No further fulfillment actions are required.</span>
                    </div>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; position: relative; margin: 10px 0;">
                        <!-- Background Connector Line -->
                        <div style="position: absolute; top: 16px; left: 30px; right: 30px; height: 3px; background: var(--border-color); z-index: 1;"></div>
                        <!-- Active Filled Connector Line -->
                        <?php
                        $fillPercentage = $currentStageIndex !== false ? ($currentStageIndex / (count($stepperStages) - 1)) * 100 : 0;
                        ?>
                        <div style="position: absolute; top: 16px; left: 30px; width: calc((100% - 60px) * <?= $fillPercentage / 100 ?>); height: 3px; background: var(--brand-blue); z-index: 2; transition: width 0.5s ease;"></div>

                        <?php $idx = 0; foreach ($stepperStages as $key => $label): ?>
                            <?php
                            $isComplete = ($currentStageIndex !== false && $idx <= $currentStageIndex);
                            $isCurrent  = ($currentStageIndex !== false && $idx === $currentStageIndex);
                            ?>
                            <div style="display: flex; flex-direction: column; align-items: center; z-index: 3; position: relative;">
                                <div style="
                                    width: 34px; height: 34px; border-radius: 50%;
                                    display: flex; align-items: center; justify-content: center;
                                    font-size: 0.85rem; font-weight: 700;
                                    background: <?= $isComplete ? 'var(--brand-blue)' : '#FFFFFF' ?>;
                                    color: <?= $isComplete ? '#FFFFFF' : 'var(--text-muted)' ?>;
                                    border: 3px solid <?= $isComplete ? 'var(--brand-blue)' : 'var(--border-color)' ?>;
                                    box-shadow: <?= $isCurrent ? '0 0 0 4px var(--brand-blue-light)' : 'none' ?>;
                                ">
                                    <?php if ($isComplete): ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    <?php else: ?>
                                        <?= $idx + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: <?= $isComplete ? '700' : '500' ?>; color: <?= $isComplete ? 'var(--text-primary)' : 'var(--text-muted)' ?>; margin-top: 8px;">
                                    <?= htmlspecialchars($label) ?>
                                </span>
                            </div>
                        <?php $idx++; endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2-Column Layout -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
                <!-- Left Column: Items, Status Updater, Tracking -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Line Items Table -->
                    <div class="card-panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                    <span>Purchased Items (<?= count($order['items']) ?>)</span>
                                </h2>
                            </div>
                        </div>

                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Unit Price</th>
                                        <th>Qty</th>
                                        <th style="text-align: right;">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: var(--text-primary);">
                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                </div>
                                                <?php if (!empty($item['variant_info'])): ?>
                                                    <div style="font-size: 0.76rem; color: var(--text-muted);">
                                                        <?= htmlspecialchars($item['variant_info']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code style="background: var(--bg-surface-secondary); padding: 2px 6px; border-radius: 4px; font-size: 0.78rem;">
                                                    <?= htmlspecialchars($item['sku'] ?? 'N/A') ?>
                                                </code>
                                            </td>
                                            <td>₹<?= number_format((int)round((float)$item['unit_price'])) ?></td>
                                            <td><strong>&times; <?= $item['quantity'] ?></strong></td>
                                            <td style="text-align: right; font-weight: 700; color: var(--text-primary);">
                                                ₹<?= number_format((int)round((float)$item['line_total'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Price Breakdown Totals -->
                        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color-light); display: flex; justify-content: flex-end;">
                            <div style="width: 280px; display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; color: var(--text-secondary);">
                                    <span>Subtotal:</span>
                                    <span style="font-weight: 600; color: var(--text-primary);">₹<?= number_format((int)round((float)$order['subtotal'])) ?></span>
                                </div>
                                <?php if ((float)$order['discount_amount'] > 0): ?>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.86rem; color: var(--status-success);">
                                        <span>Discount <?= !empty($order['coupon_code_used']) ? "('{$order['coupon_code_used']}')" : '' ?>:</span>
                                        <span style="font-weight: 600;">-₹<?= number_format((int)round((float)$order['discount_amount'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; color: var(--text-secondary);">
                                    <span>GST Tax (12%):</span>
                                    <span style="font-weight: 600; color: var(--text-primary);">₹<?= number_format((int)round((float)$order['tax_amount'])) ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; color: var(--text-secondary);">
                                    <span>Shipping Charge:</span>
                                    <span style="font-weight: 600; color: var(--text-primary);">
                                        <?= (float)$order['shipping_charge'] > 0 ? '₹' . number_format((int)round((float)$order['shipping_charge'])) : 'Free Shipping' ?>
                                    </span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 800; color: var(--text-primary); padding-top: 8px; border-top: 2px solid var(--border-color);">
                                    <span>Grand Total:</span>
                                    <span style="color: var(--brand-blue);">₹<?= number_format((int)round((float)$order['grand_total'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fulfillment & Status Workflow Manager -->
                    <div class="card-panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-orange)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                    <span>Update Order Status</span>
                                </h2>
                                <div class="panel-subtitle">Change workflow state and log audit trail note</div>
                            </div>
                        </div>

                        <form action="<?= url('admin/orders/' . $encryptedId . '/status') ?>" method="POST" style="margin-bottom: 20px;">
                            <?= csrf_field() ?>

                            <div style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 12px; align-items: center;">
                                <div>
                                    <select name="status" class="form-input" style="height: 44px; font-weight: 600;">
                                        <option value="pending" <?= $orderStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $orderStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="packed" <?= $orderStatus === 'packed' ? 'selected' : '' ?>>Packed</option>
                                        <option value="shipped" <?= $orderStatus === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="out_for_delivery" <?= $orderStatus === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                        <option value="delivered" <?= $orderStatus === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $orderStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div>
                                    <input type="text" name="note" class="form-input" style="height: 44px; font-size: 0.88rem;" placeholder="Optional reason/note for this transition...">
                                </div>
                                <div>
                                    <button type="submit" class="btn-primary-gradient" style="height: 44px; width: auto; padding: 0 20px; font-size: 0.85rem;">
                                        Update Status
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Status History Timeline -->
                        <div style="border-top: 1px solid var(--border-color-light); padding-top: 16px;">
                            <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">
                                Status Audit Trail
                            </div>

                            <?php if (!empty($order['history'])): ?>
                                <div class="activity-stream">
                                    <?php foreach ($order['history'] as $hist): ?>
                                        <div class="activity-item">
                                            <div class="activity-dot blue">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <div class="activity-content">
                                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                                    <div class="activity-title">
                                                        Status changed to: <strong><?= ucfirst(str_replace('_', ' ', $hist['status'])) ?></strong>
                                                    </div>
                                                    <span style="font-size: 0.74rem; color: var(--text-muted);">
                                                        <?= date('d M Y, h:i A', strtotime($hist['changed_at'])) ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($hist['note'])): ?>
                                                    <div class="activity-desc" style="margin-top: 2px;">
                                                        &ldquo;<?= htmlspecialchars($hist['note']) ?>&rdquo;
                                                    </div>
                                                <?php endif; ?>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                    Updated by: <?= htmlspecialchars($hist['admin_name'] ?? 'System Admin') ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 0.82rem; color: var(--text-muted);">
                                    Order placed on <?= date('d M Y, h:i A', strtotime($order['placed_at'])) ?>. No subsequent status changes logged yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Courier Tracking Editor -->
                    <div class="card-panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                    <span>Courier & Tracking Management</span>
                                </h2>
                                <div class="panel-subtitle">Assign logistics partner and AWB tracking code</div>
                            </div>
                        </div>

                        <form action="<?= url('admin/orders/' . $encryptedId . '/tracking') ?>" method="POST">
                            <?= csrf_field() ?>

                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.8rem;">Courier Partner</label>
                                    <input 
                                        type="text" 
                                        name="courier_partner" 
                                        class="form-input" 
                                        placeholder="e.g. Shiprocket, BlueDart, Delhivery" 
                                        value="<?= htmlspecialchars($order['courier_partner'] ?? '') ?>"
                                        style="height: 42px; font-size: 0.88rem;"
                                    >
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.8rem;">AWB / Tracking Number</label>
                                    <input 
                                        type="text" 
                                        name="awb_number" 
                                        class="form-input" 
                                        placeholder="e.g. BD88912903" 
                                        value="<?= htmlspecialchars($order['awb_number'] ?? '') ?>"
                                        style="height: 42px; font-size: 0.88rem;"
                                    >
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.8rem;">Estimated Delivery</label>
                                    <input 
                                        type="date" 
                                        name="estimated_delivery" 
                                        class="form-input" 
                                        value="<?= htmlspecialchars($order['estimated_delivery'] ?? '') ?>"
                                        style="height: 42px; font-size: 0.88rem;"
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn-primary-gradient" style="height: 42px; width: auto; padding: 0 20px; font-size: 0.85rem;">
                                Save Tracking Info
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Customer Snapshot, Delivery Address, Payment -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Customer Snapshot -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom: 14px;">
                            <h2 class="panel-title" style="font-size: 0.95rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>Customer Profile</span>
                            </h2>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--gradient-primary); color: #FFFFFF; font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                                <?= strtoupper(substr($order['customer_name'] ?? 'C', 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary); font-size: 0.96rem;">
                                    <?= htmlspecialchars($order['customer_name']) ?>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--brand-purple); font-weight: 600;">
                                    <?= !empty($order['customer_id']) ? 'Registered Customer' : 'Guest Checkout' ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.84rem; color: var(--text-secondary);">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <span><?= htmlspecialchars($order['customer_email'] ?? $order['guest_email'] ?? 'Not provided') ?></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <span><?= htmlspecialchars($order['shipping_phone'] ?? $order['customer_phone'] ?? 'Not provided') ?></span>
                            </div>
                            <?php if (!empty($order['customer_total_orders'])): ?>
                                <div style="margin-top: 6px; padding: 6px 10px; background: var(--bg-surface-secondary); border-radius: var(--radius-sm); font-size: 0.78rem;">
                                    Total Lifetime Orders: <strong><?= $order['customer_total_orders'] ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shipping Address Card -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom: 14px;">
                            <h2 class="panel-title" style="font-size: 0.95rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-orange)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>Shipping Address</span>
                            </h2>
                        </div>

                        <div style="font-size: 0.86rem; color: var(--text-primary); line-height: 1.5;">
                            <strong><?= htmlspecialchars($order['shipping_name']) ?></strong><br>
                            <?= htmlspecialchars($order['shipping_address1']) ?><br>
                            <?php if (!empty($order['shipping_address2'])): ?>
                                <?= htmlspecialchars($order['shipping_address2']) ?><br>
                            <?php endif; ?>
                            <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> &ndash; <?= htmlspecialchars($order['shipping_pincode']) ?><br>
                            <span style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($order['shipping_country']) ?></span>
                        </div>
                    </div>

                    <!-- Payment Status Manager -->
                    <div class="card-panel">
                        <div class="panel-header" style="margin-bottom: 14px;">
                            <h2 class="panel-title" style="font-size: 0.95rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                <span>Payment Details</span>
                            </h2>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.86rem; margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Payment Method:</span>
                                <span style="font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($order['payment_method']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Status:</span>
                                <span class="badge <?= $payBadgeClass ?>"><?= ucfirst($payStatus) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Currency:</span>
                                <span style="font-weight: 600;">INR (₹)</span>
                            </div>
                        </div>

                        <form action="<?= url('admin/orders/' . $encryptedId . '/payment') ?>" method="POST">
                            <?= csrf_field() ?>

                            <label class="form-label" style="font-size: 0.8rem;">Change Payment Status</label>
                            <div style="display: flex; gap: 8px;">
                                <select name="payment_status" class="form-input" style="height: 40px; font-size: 0.85rem;">
                                    <option value="paid" <?= $payStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="pending" <?= $payStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="failed" <?= $payStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="refunded" <?= $payStatus === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                </select>
                                <button type="submit" class="tab-btn" style="height: 40px; padding: 0 14px; background: var(--brand-purple); color: #FFFFFF; font-size: 0.82rem;">
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
