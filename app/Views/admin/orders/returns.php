<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Header Section -->
            <div class="welcome-banner" style="margin-bottom: 24px;">
                <div>
                    <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                        <span>&nbsp;/&nbsp;</span>
                        <a href="<?= url('admin/orders') ?>" style="color: var(--brand-blue);">Orders</a>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Returns & Refunds</span>
                    </div>
                    <h1 class="welcome-title">Returns & Exchange Hub</h1>
                    <p class="welcome-subtitle">Review customer return requests, schedule reverse pickups, and execute refunds.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/orders') ?>" class="btn-export">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Back to All Orders</span>
                    </a>
                </div>
            </div>

            <!-- Mini Return KPIs Bar -->
            <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Requests</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($counts['all'] ?? 0) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Pending Review</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($counts['requested'] ?? 0) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">In Reverse Transit</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;">
                        <?= number_format(($counts['approved'] ?? 0) + ($counts['pickup_scheduled'] ?? 0) + ($counts['item_received'] ?? 0)) ?>
                    </div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Refunded / Closed</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;">
                        <?= number_format(($counts['refund_initiated'] ?? 0) + ($counts['completed'] ?? 0)) ?>
                    </div>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <?php $currentStatus = $_GET['status'] ?? 'all'; ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
                <?php
                $tabList = [
                    'all'              => ['label' => 'All Requests', 'count' => $counts['all'] ?? 0],
                    'requested'        => ['label' => 'Pending Review', 'count' => $counts['requested'] ?? 0],
                    'approved'         => ['label' => 'Approved', 'count' => $counts['approved'] ?? 0],
                    'pickup_scheduled' => ['label' => 'Pickup Scheduled', 'count' => $counts['pickup_scheduled'] ?? 0],
                    'item_received'    => ['label' => 'Item Received', 'count' => $counts['item_received'] ?? 0],
                    'refund_initiated' => ['label' => 'Refund Initiated', 'count' => $counts['refund_initiated'] ?? 0],
                    'completed'        => ['label' => 'Completed', 'count' => $counts['completed'] ?? 0],
                    'rejected'         => ['label' => 'Rejected', 'count' => $counts['rejected'] ?? 0]
                ];
                ?>
                <?php foreach ($tabList as $key => $tab): ?>
                    <?php
                    $tabUrl = url('admin/returns' . ($key !== 'all' ? '?status=' . $key : ''));
                    $isActive = ($currentStatus === $key);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($tab['label']) ?> (<?= $tab['count'] ?>)
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Returns Table Card -->
            <div class="card-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Return Requests Ledger</h2>
                        <div class="panel-subtitle">Showing <?= count($returns) ?> of <?= $pagination['total_records'] ?> return/exchange requests</div>
                    </div>
                </div>

                <?php if (!empty($returns)): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Return Ref</th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Reason / Details</th>
                                    <th>Status</th>
                                    <th>Reverse AWB</th>
                                    <th>Refund</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returns as $ret): ?>
                                    <?php
                                    $retStatus = strtolower($ret['status'] ?? 'requested');
                                    $retBadgeClass = match($retStatus) {
                                        'approved'         => 'badge-paid',
                                        'completed'        => 'badge-delivered',
                                        'pickup_scheduled' => 'badge-shipped',
                                        'item_received', 'refund_initiated' => 'badge-processing',
                                        'rejected'         => 'badge-cancelled',
                                        default            => 'badge-pending'
                                    };
                                    ?>
                                    <tr>
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.88rem; font-weight: 700; color: var(--brand-purple);">
                                                #RET-<?= str_pad($ret['id'], 4, '0', STR_PAD_LEFT) ?>
                                            </span>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= date('d M Y, h:i A', strtotime($ret['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?= url('admin/orders/' . $ret['order_encrypted_id']) ?>" class="order-code" style="text-decoration: underline; text-underline-offset: 3px;" title="View Order Details">
                                                <?= htmlspecialchars($ret['order_number']) ?>
                                            </a>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                Total: ₹<?= number_format((int)round((float)$ret['order_total'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($ret['customer_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($ret['customer_email']) ?></div>
                                            <?php if (!empty($ret['customer_phone'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($ret['customer_phone']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 260px;">
                                            <div style="font-weight: 600; color: var(--text-primary); font-size: 0.86rem;">
                                                <?= htmlspecialchars($ret['reason']) ?>
                                            </div>
                                            <?php if (!empty($ret['description'])): ?>
                                                <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    "<?= htmlspecialchars($ret['description']) ?>"
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $retBadgeClass ?>">
                                                <?= ucfirst(str_replace('_', ' ', $ret['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($ret['reverse_awb'])): ?>
                                                <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; background: var(--bg-surface-secondary); padding: 3px 8px; border-radius: 4px; border: 1px solid var(--border-color);">
                                                    <?= htmlspecialchars($ret['reverse_awb']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.8rem;">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ret['refund_amount'] && $ret['refund_amount'] > 0): ?>
                                                <div style="font-weight: 800; color: #059669; font-size: 0.9rem;">
                                                    ₹<?= number_format((int)round((float)$ret['refund_amount'])) ?>
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--text-muted);">
                                                    <?= htmlspecialchars($ret['refund_method'] ?: 'Online') ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.8rem;">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <button type="button" 
                                                    class="btn-primary-gradient" 
                                                    style="height: 34px; padding: 0 16px; font-size: 0.82rem; width: auto; display: inline-flex; align-items: center; gap: 6px;"
                                                    onclick='openReturnModal(<?= json_encode($ret) ?>)'>
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                                <span>Manage</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 18px; margin-top: 18px; border-top: 1px solid var(--border-color);">
                            <div style="font-size: 0.84rem; color: var(--text-muted);">
                                Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> (<?= $pagination['total_records'] ?> requests)
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <a href="<?= url('admin/returns?page=' . ($pagination['current_page'] - 1) . ($currentStatus !== 'all' ? '&status=' . $currentStatus : '')) ?>" class="tab-btn">
                                        &larr; Previous
                                    </a>
                                <?php endif; ?>
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <a href="<?= url('admin/returns?page=' . ($pagination['current_page'] + 1) . ($currentStatus !== 'all' ? '&status=' . $currentStatus : '')) ?>" class="tab-btn">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <div class="empty-state-title">No Return Requests Found</div>
                        <div class="empty-state-desc">
                            There are currently no return or exchange requests matching the selected status filter.
                        </div>
                        <div style="margin-top: 16px;">
                            <a href="<?= url('admin/returns') ?>" class="btn-export">View All Requests</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- Manage Return Modal -->
<div class="modal-overlay" id="returnModalOverlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalReturnTitle">Manage Return Request</h3>
                <p class="modal-subtitle">Update fulfillment state, reverse tracking, or trigger refund payout.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeReturnModal()">&times;</button>
        </div>

        <form id="returnActionForm" method="POST" action="">
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
            <div class="modal-body">
                <!-- Customer Details Card -->
                <div style="background: var(--bg-surface-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Customer:</span>
                        <strong id="modalCustName" style="font-size: 0.85rem; color: var(--text-primary);"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Order Number:</span>
                        <strong id="modalOrderNum" style="font-size: 0.85rem; color: var(--brand-blue);"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Reason:</span>
                        <span id="modalReason" style="font-size: 0.85rem; color: var(--brand-orange); font-weight: 700;"></span>
                    </div>
                </div>

                <!-- Update Status -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="returnStatusSelect">
                        Update Return Status <span style="color: red;">*</span>
                    </label>
                    <select name="status" id="returnStatusSelect" style="width: 100%; height: 44px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;" required onchange="toggleRefundFields(this.value)">
                        <option value="requested">Requested (Pending Review)</option>
                        <option value="approved">Approved (Accept Return)</option>
                        <option value="pickup_scheduled">Pickup Scheduled (Reverse Courier Booked)</option>
                        <option value="item_received">Item Received (Inspected in Warehouse)</option>
                        <option value="refund_initiated">Refund Initiated (Process Payout)</option>
                        <option value="completed">Completed (Closed)</option>
                        <option value="rejected">Rejected (Decline Return)</option>
                    </select>
                </div>

                <!-- Reverse AWB Input -->
                <div style="margin-bottom: 16px;" id="reverseAwbGroup">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="reverseAwbInput">
                        Reverse Courier AWB Number
                    </label>
                    <input type="text" name="reverse_awb" id="reverseAwbInput" style="width: 100%; height: 44px; padding: 0 14px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;" placeholder="e.g. DELH-REV-98765432">
                    <span style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px; display: block;">Tracking code from Delhivery / Shiprocket reverse pickup booking.</span>
                </div>

                <!-- Refund Fields Group (Conditional) -->
                <div id="refundFieldsBox" style="background: var(--brand-purple-light); border: 1px solid var(--brand-purple-border); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 16px; display: none;">
                    <div style="font-weight: 700; color: var(--brand-purple); font-size: 0.88rem; margin-bottom: 10px;">
                        Refund Execution Details
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;" for="refundAmountInput">Refund Amount (₹)</label>
                        <input type="number" step="1" min="0" name="refund_amount" id="refundAmountInput" style="width: 100%; height: 40px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;" placeholder="0">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;" for="refundMethodSelect">Refund Method</label>
                        <select name="refund_method" id="refundMethodSelect" style="width: 100%; height: 40px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;">
                            <option value="Original Payment Method (Razorpay)">Original Payment Method (Razorpay)</option>
                            <option value="Bank NEFT/IMPS Transfer">Bank NEFT/IMPS Transfer</option>
                            <option value="Store Credit Voucher">Store Credit Voucher</option>
                            <option value="UPI Direct Pay">UPI Direct Pay</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;" for="gatewayRefundIdInput">Payment Gateway / Transaction Reference</label>
                        <input type="text" name="gateway_refund_id" id="gatewayRefundIdInput" style="width: 100%; height: 40px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;" placeholder="e.g. rfnd_Nw98Fh3829">
                    </div>
                </div>

                <!-- Admin Audit Note -->
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="returnAdminNote">
                        Internal Admin Note / Audit Log
                    </label>
                    <textarea name="admin_note" id="returnAdminNote" style="width: 100%; height: 80px; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.88rem; font-family: inherit; resize: vertical;" placeholder="Provide reason or operational details..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-export" onclick="closeReturnModal()">Cancel</button>
                <button type="submit" class="btn-primary-gradient" style="height: 40px; padding: 0 20px; width: auto;">Save Return Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(ret) {
    document.getElementById('modalReturnTitle').textContent = 'Manage Return #RET-' + String(ret.id).padStart(4, '0');
    document.getElementById('modalCustName').textContent = ret.customer_name;
    document.getElementById('modalOrderNum').textContent = ret.order_number;
    document.getElementById('modalReason').textContent = ret.reason;

    document.getElementById('returnStatusSelect').value = ret.status;
    document.getElementById('reverseAwbInput').value = ret.reverse_awb || '';
    document.getElementById('refundAmountInput').value = ret.refund_amount ? ret.refund_amount : (ret.order_total || '');
    document.getElementById('refundMethodSelect').value = ret.refund_method || 'Original Payment Method (Razorpay)';
    document.getElementById('gatewayRefundIdInput').value = ret.gateway_refund_id || '';
    document.getElementById('returnAdminNote').value = ret.admin_note || '';

    // Set action URL with encrypted ID
    document.getElementById('returnActionForm').action = '<?= url("admin/returns/") ?>' + ret.encrypted_id + '/status';

    toggleRefundFields(ret.status);

    document.getElementById('returnModalOverlay').style.display = 'flex';
}

function closeReturnModal() {
    document.getElementById('returnModalOverlay').style.display = 'none';
}

function toggleRefundFields(status) {
    const box = document.getElementById('refundFieldsBox');
    if (status === 'refund_initiated' || status === 'completed') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}
</script>
