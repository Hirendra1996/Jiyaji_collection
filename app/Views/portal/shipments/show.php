<?php
$awbNumber = htmlspecialchars($order['awb_number'] ?? 'UNASSIGNED');
$orderNumber = htmlspecialchars($order['order_number']);
$title = 'Shipment Tracking #' . $awbNumber . ' | Jiyaji LX Staff Portal';
$canCreate = $canCreate ?? (function_exists('staff_can') ? staff_can('shipments', 'create') : false);
$encryptedId = htmlspecialchars($order['encrypted_id'] ?? encrypt_id($order['id']));
$status = strtolower($order['status'] ?? 'pending');
$carrier = $order['courier_partner'] ?? 'Standard Surface';

$statusBg = match($status) {
    'delivered' => '#ECFDF5',
    'shipped', 'in_transit' => '#EFF6FF',
    'out_for_delivery' => '#F5F3FF',
    'cancelled' => '#FEF2F2',
    default => '#FFFBEB'
};
$statusColor = match($status) {
    'delivered' => '#065F46',
    'shipped', 'in_transit' => '#1E40AF',
    'out_for_delivery' => '#6D28D9',
    'cancelled' => '#991B1B',
    default => '#B45309'
};

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumb Navigation -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.84rem;">
                    <a href="<?= url('portal/shipments') ?>" style="color: #047857; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        &larr; Shipments &amp; AWBs
                    </a>
                    <span style="color: #CBD5E1;">/</span>
                    <span style="color: #64748B; font-weight: 600;">AWB #<?= $awbNumber ?></span>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="<?= url('portal/orders/' . $encryptedId) ?>" style="font-size: 0.82rem; font-weight: 700; color: #0F172A; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 7px 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <span>View Order Details #<?= $orderNumber ?></span>
                        &rarr;
                    </a>
                </div>
            </div>

            <!-- Shipment Header Banner -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span style="font-size: 1.45rem; font-weight: 800; color: #0F172A; letter-spacing: -0.02em;">
                            AWB: <?= $awbNumber ?>
                        </span>

                        <span style="display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; background: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                        </span>

                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: #F1F5F9; color: #334155;">
                            Carrier: <?= htmlspecialchars($carrier) ?>
                        </span>
                    </div>

                    <div style="font-size: 0.8rem; color: #64748B;">
                        Order Reference: <a href="<?= url('portal/orders/' . $encryptedId) ?>" style="color: #047857; font-weight: 700; text-decoration: none;">#<?= $orderNumber ?></a> &bull; Recipient: <strong><?= htmlspecialchars($order['shipping_name'] ?? $order['customer_name'] ?? 'Customer') ?></strong> (<?= htmlspecialchars($order['shipping_city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_state'] ?? '') ?>)
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Expected Delivery</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin-top: 2px;">
                        <?= !empty($order['estimated_delivery']) ? date('l, d M Y', strtotime($order['estimated_delivery'])) : 'Standard Logistics ETA' ?>
                    </div>
                </div>
            </div>

            <!-- Two-Column Layout -->
            <div style="display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start;">

                <!-- LEFT COLUMN: Milestones Timeline & Details -->
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- Chronological Milestones Timeline Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #0F172A;">
                                Chronological Carrier Scan Milestones
                            </h3>
                            <span style="font-size: 0.75rem; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                                <?= count($milestones ?? []) ?> Scan Event(s)
                            </span>
                        </div>

                        <?php if (!empty($milestones)): ?>
                            <div style="position: relative; padding-left: 28px; border-left: 2px solid #E2E8F0; margin-left: 10px;">
                                <?php foreach ($milestones as $idx => $m): ?>
                                    <?php
                                    $isLatest = ($idx === count($milestones) - 1);
                                    ?>
                                    <div style="margin-bottom: 24px; position: relative;">
                                        <!-- Timeline Dot -->
                                        <div style="position: absolute; left: -35px; top: 2px; width: 14px; height: 14px; border-radius: 50%; background: <?= $isLatest ? '#047857' : '#94A3B8' ?>; border: 3px solid #FFFFFF; box-shadow: 0 0 0 2px <?= $isLatest ? '#047857' : '#E2E8F0' ?>;"></div>

                                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                            <div style="font-size: 0.88rem; font-weight: 700; color: #0F172A;">
                                                <?= htmlspecialchars($m['activity']) ?>
                                            </div>
                                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 7px; border-radius: 4px; text-transform: uppercase;">
                                                <?= htmlspecialchars(str_replace('_', ' ', $m['status'])) ?>
                                            </span>
                                        </div>

                                        <div style="font-size: 0.75rem; color: #64748B; margin-top: 4px;">
                                            Location: <strong><?= htmlspecialchars($m['location'] ?? 'In Transit') ?></strong> &bull; <?= date('d M Y, h:i A', strtotime($m['event_time'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="padding: 30px; text-align: center; color: #94A3B8; background: #F8FAFC; border-radius: 10px;">
                                <p style="margin: 0; font-size: 0.85rem;">No milestone scans recorded yet. Electronic booking confirmed.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Destination & Consignment Specs Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 16px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Delivery Destination &amp; Consignment Details
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 0.84rem;">
                            <div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Recipient Info</div>
                                <div style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars($order['shipping_name'] ?? $order['customer_name'] ?? 'Customer') ?></div>
                                <div style="color: #475569; margin-top: 2px;">Phone: <?= htmlspecialchars($order['shipping_phone'] ?? $order['customer_phone'] ?? 'N/A') ?></div>
                                <div style="color: #475569; margin-top: 2px;">Email: <?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?></div>
                            </div>

                            <div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">Shipping Address</div>
                                <div style="color: #0F172A; line-height: 1.5;">
                                    <?= htmlspecialchars($order['shipping_address1'] ?? '') ?>
                                    <?php if (!empty($order['shipping_address2'])): ?>
                                        , <?= htmlspecialchars($order['shipping_address2']) ?>
                                    <?php endif; ?><br>
                                    <?= htmlspecialchars($order['shipping_city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_state'] ?? '') ?> - <span style="font-family: monospace; font-weight: 700;"><?= htmlspecialchars($order['shipping_pincode'] ?? '') ?></span><br>
                                    <?= htmlspecialchars($order['shipping_country'] ?? 'India') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Log Scans & Quick Actions -->
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    <!-- Action: Log New Carrier Milestone Scan -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                Log Carrier Scan Event
                            </h3>
                            <?php if ($canCreate): ?>
                                <span style="font-size: 0.68rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 7px; border-radius: 4px;">
                                    Permitted
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.68rem; font-weight: 700; background: #FEF2F2; color: #991B1B; padding: 2px 7px; border-radius: 4px;">
                                    View-Only
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($canCreate): ?>
                            <form action="<?= url('portal/shipments/' . $encryptedId . '/milestone') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="awb_number" value="<?= htmlspecialchars($order['awb_number'] ?? '') ?>">

                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Scan Status Milestone
                                    </label>
                                    <select name="status" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600;">
                                        <option value="in_transit">In Transit (Package en route)</option>
                                        <option value="reached_hub">Reached Regional Hub / Sorting Facility</option>
                                        <option value="out_for_delivery">Out for Delivery (Assigned to delivery agent)</option>
                                        <option value="delivered">Delivered (Handed over to client)</option>
                                        <option value="rto">RTO / Return to Origin</option>
                                        <option value="failed">Delivery Attempt Failed</option>
                                    </select>
                                </div>

                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Hub / Location
                                    </label>
                                    <input type="text" name="location" placeholder="e.g. Mumbai Gateway Hub, MH" style="width: 100%; height: 38px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; box-sizing: border-box;" required>
                                </div>

                                <div style="margin-bottom: 16px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                                        Activity Memo
                                    </label>
                                    <textarea name="activity" rows="2" placeholder="e.g. Consignment processed and sorted for final delivery run" style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 6px 10px; font-size: 0.8rem; box-sizing: border-box;" required></textarea>
                                </div>

                                <button type="submit" style="width: 100%; height: 40px; border-radius: 8px; background: #047857; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#065F46';" onmouseout="this.style.background='#047857';">
                                    Record Scan Update
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="font-size: 0.8rem; color: #64748B; background: #F8FAFC; padding: 12px; border-radius: 8px;">
                                Current Delivery State: <strong style="color: #0F172A;"><?= ucfirst(str_replace('_', ' ', $status)) ?></strong>
                                <p style="margin: 6px 0 0 0; font-size: 0.74rem; color: #94A3B8;">
                                    Your role clearance is read-only. Recording courier scans requires Logistics Dispatch clearance.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action: Shipping Label & Documents -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                            Logistics Documentation
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="<?= url('portal/shipments/' . $encryptedId . '/label') ?>" target="_blank" style="padding: 10px 14px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                <span>Print Thermal Courier Label</span>
                            </a>

                            <a href="<?= url('portal/orders/' . $encryptedId . '/invoice') ?>" target="_blank" style="padding: 9px 14px; background: #F1F5F9; color: #0F172A; font-weight: 600; font-size: 0.82rem; border-radius: 8px; text-decoration: none; text-align: center; border: 1px solid #CBD5E1;">
                                Print Tax Invoice
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
