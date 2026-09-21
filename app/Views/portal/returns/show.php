<?php
$rmaCode = 'RMA-' . str_pad($return['id'], 5, '0', STR_PAD_LEFT);
$title = '#' . $rmaCode . ' | Returns & Exchanges Portal';
$canProcess = $canProcess ?? (function_exists('staff_can') ? staff_can('returns', 'process') : false);
include __DIR__ . '/../layouts/header.php';

$reqType = $return['request_type'] ?? 'return';
$status = strtolower($return['status'] ?? 'requested');
$encryptedId = htmlspecialchars($return['encrypted_id'] ?? encrypt_id($return['id']));
$orderEncryptedId = htmlspecialchars($return['order_encrypted_id'] ?? encrypt_id($return['order_id']));

// Stepper Stages
$stepperStages = [
    'requested'        => '1. Request Lodged',
    'approved'         => '2. Approved',
    'pickup_scheduled' => '3. Reverse Pickup',
    'item_received'    => '4. Quality QA',
    'refund_initiated' => ($reqType === 'exchange' ? '5. Replacement' : '5. Refund Dispatched'),
    'completed'        => '6. Settled'
];
$stageKeys = array_keys($stepperStages);
$currentStageIndex = array_search($status, $stageKeys, true);
if ($currentStageIndex === false && $status !== 'rejected') {
    $currentStageIndex = 0;
}

$statusBg = match($status) {
    'completed' => '#ECFDF5',
    'refund_initiated' => '#EFF6FF',
    'item_received' => '#F5F3FF',
    'pickup_scheduled' => '#FEF3C7',
    'approved' => '#EEF2FF',
    'rejected' => '#FEF2F2',
    default => '#FFFBEB'
};
$statusColor = match($status) {
    'completed' => '#065F46',
    'refund_initiated' => '#1E40AF',
    'item_received' => '#6D28D9',
    'pickup_scheduled' => '#92400E',
    'approved' => '#3730A3',
    'rejected' => '#991B1B',
    default => '#B45309'
};
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumb Navigation -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.84rem;">
                    <a href="<?= url('portal/returns') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        &larr; Returns &amp; Exchanges
                    </a>
                    <span style="color: #CBD5E1;">/</span>
                    <span style="color: #64748B; font-weight: 600;">Case #<?= $rmaCode ?></span>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="<?= url('portal/orders/' . $orderEncryptedId) ?>" style="font-size: 0.82rem; font-weight: 700; color: #0F172A; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <span>View Parent Order #<?= htmlspecialchars($return['order_number']) ?></span>
                        &rarr;
                    </a>
                </div>
            </div>

            <!-- Header Card with RMA Title & Status Badge -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 1.45rem; font-weight: 800; color: #0F172A; letter-spacing: -0.02em;">
                            #<?= $rmaCode ?>
                        </span>

                        <?php if ($reqType === 'exchange'): ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; background: #F5F3FF; color: #6D28D9; border: 1px solid rgba(109, 40, 217, 0.2);">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                                Size / Style Exchange
                            </span>
                        <?php else: ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; background: #FFF1F2; color: #BE123C; border: 1px solid rgba(190, 18, 60, 0.2);">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Return for Refund
                            </span>
                        <?php endif; ?>

                        <span style="padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; background: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                        </span>
                    </div>

                    <div style="font-size: 0.8rem; color: #64748B;">
                        Registered on <?= date('d M Y, h:i A', strtotime($return['created_at'])) ?> &bull; Linked Order: <a href="<?= url('portal/orders/' . $orderEncryptedId) ?>" style="color: #4F46E5; font-weight: 700; text-decoration: none;">#<?= htmlspecialchars($return['order_number']) ?></a>
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Estimated Settlement</div>
                    <div style="font-size: 1.45rem; font-weight: 800; color: #0F172A; margin-top: 2px;">
                        <?php if ((float)$return['refund_amount'] > 0): ?>
                            ₹<?= number_format((float)$return['refund_amount'], 2) ?>
                        <?php else: ?>
                            Garment Exchange
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RMA Lifecycle Stepper -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 0.76rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 18px;">
                    RMA Fulfillment Progression
                </div>

                <?php if ($status === 'rejected'): ?>
                    <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 10px; padding: 14px 18px; color: #991B1B; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <span>This return request was formally rejected following policy inspection. See administrative remarks below.</span>
                    </div>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                        <div style="position: absolute; top: 14px; left: 20px; right: 20px; height: 3px; background: #E2E8F0; z-index: 1;"></div>
                        <div style="position: absolute; top: 14px; left: 20px; width: <?= $currentStageIndex !== false ? (($currentStageIndex / (count($stepperStages) - 1)) * 100) : 0 ?>%; height: 3px; background: #4F46E5; z-index: 2; transition: width 0.3s;"></div>

                        <?php foreach ($stepperStages as $stKey => $stLabel): ?>
                            <?php
                            $stepIndex = array_search($stKey, $stageKeys, true);
                            $isPassed = ($currentStageIndex !== false && $stepIndex <= $currentStageIndex);
                            $isCurrent = ($status === $stKey);
                            ?>
                            <div style="position: relative; z-index: 3; text-align: center; width: 110px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; background: <?= $isPassed ? '#4F46E5' : '#FFFFFF' ?>; color: <?= $isPassed ? '#FFFFFF' : '#94A3B8' ?>; border: 2px solid <?= $isPassed ? '#4F46E5' : '#CBD5E1' ?>; box-shadow: <?= $isCurrent ? '0 0 0 4px rgba(79, 70, 229, 0.2)' : 'none' ?>;">
                                    <?php if ($isPassed && !$isCurrent): ?>
                                        &#10003;
                                    <?php else: ?>
                                        <?= $stepIndex + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.72rem; font-weight: <?= $isCurrent ? '800' : '600' ?>; color: <?= $isCurrent ? '#0F172A' : ($isPassed ? '#4F46E5' : '#94A3B8') ?>; line-height: 1.25;">
                                    <?= htmlspecialchars($stLabel) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Two-Column Grid: Details Left, Actions Right -->
            <div style="display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start;">

                <!-- LEFT COLUMN -->
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- Items Under Return Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="padding: 16px 20px; border-bottom: 1px solid #E2E8F0; background: #F8FAFC; display: flex; align-items: center; justify-content: space-between;">
                            <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                Line Items Requested For <?= $reqType === 'exchange' ? 'Exchange' : 'Return' ?>
                            </h3>
                            <span style="font-size: 0.74rem; font-weight: 700; color: #64748B;">
                                <?= count($return['items'] ?? []) ?> Item(s)
                            </span>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.84rem;">
                                <thead>
                                    <tr style="background: #FAFBFD; border-bottom: 1px solid #E2E8F0; color: #64748B; font-size: 0.72rem; text-transform: uppercase;">
                                        <th style="padding: 12px 18px;">Product</th>
                                        <th style="padding: 12px 18px;">SKU</th>
                                        <th style="padding: 12px 18px; text-align: center;">Return Qty</th>
                                        <th style="padding: 12px 18px; text-align: right;">Rate</th>
                                        <th style="padding: 12px 18px; text-align: right;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($return['items'])): ?>
                                        <?php foreach ($return['items'] as $item): ?>
                                            <?php
                                            $unitPrice = (float)($item['unit_price'] ?? 0);
                                            $returnQty = (int)($item['quantity'] ?? 1);
                                            $itemTotal = $unitPrice * $returnQty;
                                            ?>
                                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                                <td style="padding: 14px 18px;">
                                                    <div style="font-weight: 700; color: #0F172A;">
                                                        <?= htmlspecialchars($item['product_name'] ?? 'Garment') ?>
                                                    </div>
                                                    <?php if (!empty($item['variant_info'])): ?>
                                                        <div style="font-size: 0.72rem; color: #64748B;">
                                                            <?= htmlspecialchars($item['variant_info']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 14px 18px; color: #64748B; font-family: monospace;">
                                                    <?= htmlspecialchars($item['sku'] ?? 'N/A') ?>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: center; font-weight: 800; color: #0F172A;">
                                                    <?= $returnQty ?>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: right; color: #475569;">
                                                    ₹<?= number_format($unitPrice, 2) ?>
                                                </td>
                                                <td style="padding: 14px 18px; text-align: right; font-weight: 800; color: #0F172A;">
                                                    ₹<?= number_format($itemTotal, 2) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="padding: 24px; text-align: center; color: #94A3B8;">
                                                No specific line items attached to this request.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Defect / Reason & Exchange Details Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Customer Reason &amp; Replacement Instructions
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <span style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Primary Reason</span>
                                <div style="font-size: 0.9rem; font-weight: 700; color: #0F172A; margin-top: 2px;">
                                    <?= htmlspecialchars($return['reason']) ?>
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Request Category</span>
                                <div style="font-size: 0.9rem; font-weight: 700; color: #0F172A; margin-top: 2px;">
                                    <?= ucfirst($reqType) ?> &bull; <?= $reqType === 'exchange' ? 'Replacement Size/Color' : 'Full Monetary Refund' ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($return['description'])): ?>
                            <div style="margin-bottom: 16px;">
                                <span style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Customer Description</span>
                                <div style="font-size: 0.84rem; color: #334155; background: #F8FAFC; padding: 10px 14px; border-radius: 8px; margin-top: 4px; border-left: 3px solid #CBD5E1;">
                                    <?= nl2br(htmlspecialchars($return['description'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($return['exchange_notes'])): ?>
                            <div style="margin-bottom: 16px;">
                                <span style="font-size: 0.72rem; font-weight: 700; color: #6D28D9; text-transform: uppercase;">Exchange Preference</span>
                                <div style="font-size: 0.84rem; color: #4C1D95; background: #F5F3FF; padding: 10px 14px; border-radius: 8px; margin-top: 4px; border-left: 3px solid #7C3AED; font-weight: 600;">
                                    <?= nl2br(htmlspecialchars($return['exchange_notes'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Client Profile & Destination Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Customer &amp; Reverse Pickup Address
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 0.84rem;">
                            <div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Client Details</div>
                                <div style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars($return['customer_name'] ?? 'Customer') ?></div>
                                <div style="color: #475569; margin-top: 2px;">Email: <?= htmlspecialchars($return['customer_email'] ?? 'N/A') ?></div>
                                <div style="color: #475569; margin-top: 2px;">Phone: <?= htmlspecialchars($return['customer_phone'] ?? $return['shipping_phone'] ?? 'N/A') ?></div>
                            </div>

                            <div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Pickup Address</div>
                                <div style="color: #0F172A; line-height: 1.5;">
                                    <?= htmlspecialchars($return['shipping_address'] ?? 'Atelier Address on File') ?><br>
                                    <?= htmlspecialchars($return['shipping_city'] ?? '') ?>, <?= htmlspecialchars($return['shipping_state'] ?? '') ?> - <?= htmlspecialchars($return['shipping_pincode'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Trail & Administrative Notes -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Internal Audit Remarks &amp; History
                        </h3>

                        <?php if (!empty($return['admin_note'])): ?>
                            <pre style="white-space: pre-wrap; font-family: inherit; font-size: 0.8rem; color: #334155; background: #F8FAFC; padding: 14px 16px; border-radius: 8px; border: 1px solid #E2E8F0; line-height: 1.6; margin: 0;"><?= htmlspecialchars($return['admin_note']) ?></pre>
                        <?php else: ?>
                            <p style="font-size: 0.82rem; color: #94A3B8; margin: 0;">No administrative remarks have been logged yet.</p>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- RIGHT COLUMN: ACTIONS -->
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    <!-- Action 1: Status Progression -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                Fulfillment Stage
                            </h3>
                            <?php if ($canProcess): ?>
                                <span style="font-size: 0.68rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 7px; border-radius: 4px;">
                                    Permitted
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.68rem; font-weight: 700; background: #FEF2F2; color: #991B1B; padding: 2px 7px; border-radius: 4px;">
                                    View-Only
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($canProcess): ?>
                            <form action="<?= url('portal/returns/' . $encryptedId . '/status') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Advance Stage
                                    </label>
                                    <select name="status" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600;">
                                        <option value="requested" <?= $status === 'requested' ? 'selected' : '' ?>>1. Requested</option>
                                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>2. Approved (Authorize)</option>
                                        <option value="pickup_scheduled" <?= $status === 'pickup_scheduled' ? 'selected' : '' ?>>3. Pickup Scheduled</option>
                                        <option value="item_received" <?= $status === 'item_received' ? 'selected' : '' ?>>4. Item Received (QA)</option>
                                        <option value="refund_initiated" <?= $status === 'refund_initiated' ? 'selected' : '' ?>>5. Refund / Replacement Dispatched</option>
                                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>6. Completed (Settled)</option>
                                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected (Decline)</option>
                                    </select>
                                </div>

                                <div style="margin-bottom: 14px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Audit Memo Note
                                    </label>
                                    <textarea name="note" rows="2" placeholder="e.g. Quality inspection passed with zero defects." style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 6px 10px; font-size: 0.8rem; box-sizing: border-box;"></textarea>
                                </div>

                                <button type="submit" style="width: 100%; height: 38px; border-radius: 8px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                                    Update RMA Stage
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="font-size: 0.8rem; color: #64748B; background: #F8FAFC; padding: 12px; border-radius: 8px;">
                                Current Stage: <strong style="color: #0F172A;"><?= ucfirst(str_replace('_', ' ', $status)) ?></strong>
                                <p style="margin: 6px 0 0 0; font-size: 0.74rem; color: #94A3B8;">
                                    Your role clearance is read-only. Status advancement is restricted to Returns &amp; Logistics Managers.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action 2: Reverse Logistics & Courier AWB -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Reverse Courier &amp; AWB Tracking
                        </h3>

                        <?php if ($canProcess): ?>
                            <form action="<?= url('portal/returns/' . $encryptedId . '/tracking') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Reverse Pickup AWB Number
                                    </label>
                                    <input type="text" name="reverse_awb" value="<?= htmlspecialchars($return['reverse_awb'] ?? '') ?>" placeholder="e.g. DELH-REV-992211" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-family: monospace; box-sizing: border-box;" required>
                                </div>

                                <div style="margin-bottom: 14px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Courier Note
                                    </label>
                                    <input type="text" name="note" placeholder="e.g. Delhivery reverse pickup scheduled for tomorrow" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; box-sizing: border-box;">
                                </div>

                                <button type="submit" style="width: 100%; height: 38px; border-radius: 8px; background: #0F172A; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#1E293B';" onmouseout="this.style.background='#0F172A';">
                                    Save Reverse AWB
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="font-size: 0.82rem; color: #475569;">
                                Reverse AWB: <strong><?= htmlspecialchars($return['reverse_awb'] ?? 'Not yet assigned') ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action 3: Settlement Card (Refund or Exchange Resolution) -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            <?= $reqType === 'exchange' ? 'Exchange Resolution' : 'Refund Settlement' ?>
                        </h3>

                        <?php if ($reqType === 'exchange'): ?>
                            <div style="font-size: 0.82rem; color: #475569; line-height: 1.5; background: #F8FAFC; padding: 12px; border-radius: 8px;">
                                <div><strong>Exchange Policy:</strong> No direct monetary payout.</div>
                                <div style="margin-top: 6px; color: #64748B;">Once the returned piece passes QA, issue the replacement garment from inventory and advance stage to <strong>Replacement Dispatched</strong>.</div>
                            </div>
                        <?php else: ?>
                            <?php if ($canProcess): ?>
                                <form action="<?= url('portal/returns/' . $encryptedId . '/refund') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <div style="margin-bottom: 12px;">
                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                            Refund Amount (₹)
                                        </label>
                                        <input type="number" step="0.01" name="refund_amount" value="<?= htmlspecialchars((string)($return['refund_amount'] ?? 0)) ?>" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.86rem; font-weight: 700; box-sizing: border-box;" required>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                            Refund Method
                                        </label>
                                        <select name="refund_method" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600;">
                                            <option value="store_credit" <?= ($return['refund_method'] ?? '') === 'store_credit' ? 'selected' : '' ?>>Store Credit / Atelier Voucher</option>
                                            <option value="original_payment" <?= ($return['refund_method'] ?? '') === 'original_payment' ? 'selected' : '' ?>>Original Payment Gateway</option>
                                            <option value="bank_transfer" <?= ($return['refund_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>NEFT / Direct Bank Transfer</option>
                                            <option value="upi" <?= ($return['refund_method'] ?? '') === 'upi' ? 'selected' : '' ?>>UPI Transfer</option>
                                        </select>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                            Gateway / Transaction Ref ID
                                        </label>
                                        <input type="text" name="gateway_refund_id" value="<?= htmlspecialchars($return['gateway_refund_id'] ?? '') ?>" placeholder="e.g. rfnd_9823019842" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.82rem; font-family: monospace; box-sizing: border-box;">
                                    </div>

                                    <div style="margin-bottom: 14px;">
                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                            Settlement Note
                                        </label>
                                        <input type="text" name="note" placeholder="e.g. Voucher code sent to client via concierge SMS" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.82rem; box-sizing: border-box;">
                                    </div>

                                    <button type="submit" style="width: 100%; height: 38px; border-radius: 8px; background: #059669; color: #FFFFFF; font-weight: 700; font-size: 0.82rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#047857';" onmouseout="this.style.background='#059669';">
                                        Record Refund Settlement
                                    </button>
                                </form>
                            <?php else: ?>
                                <div style="font-size: 0.82rem; color: #475569;">
                                    Settled Amount: <strong>₹<?= number_format((float)($return['refund_amount'] ?? 0), 2) ?></strong><br>
                                    Method: <strong><?= ucfirst(str_replace('_', ' ', $return['refund_method'] ?? 'N/A')) ?></strong>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
