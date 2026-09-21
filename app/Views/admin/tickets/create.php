<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 8px;">
                    <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                    <span>&nbsp;/&nbsp;</span>
                    <a href="<?= url('admin/tickets') ?>" style="color: var(--brand-blue);">Support Tickets</a>
                    <span>&nbsp;/&nbsp;</span>
                    <span>Open New Ticket</span>
                </div>
                <h1 class="welcome-title">Open New Support Ticket</h1>
                <p class="welcome-subtitle">Log a client service case, bespoke styling inquiry, delivery alteration, or VIP escalation.</p>
            </div>

            <div style="max-width: 780px;">
                <div class="card-panel" style="padding: 28px;">
                    <form action="<?= url('admin/tickets/store') ?>" method="POST">
                        <?= csrf_field() ?>

                        <!-- Client Selection -->
                        <div style="margin-bottom: 20px;">
                            <label class="form-label" style="display: block; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
                                Client Profile <span style="color: #dc2626;">*</span>
                            </label>
                            <select name="customer_id" id="pageCustomerSelect" class="form-input" required onchange="onPageCustomerChange(this.value)">
                                <option value="">-- Choose Registered Client Profile --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['encrypted_id'] ?>" <?= (!empty($preselectedCustomer) && $c['id'] == $preselectedCustomer) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Linked Order (Optional) -->
                        <div style="margin-bottom: 20px;">
                            <label class="form-label" style="display: block; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
                                Associated Order (Optional)
                            </label>
                            <select name="order_id" id="pageOrderSelect" class="form-input">
                                <option value="">-- None / General Pre-sale Inquiry --</option>
                                <?php if (!empty($customerOrders)): ?>
                                    <?php foreach ($customerOrders as $ord): ?>
                                        <option value="<?= $ord['encrypted_id'] ?>">
                                            <?= htmlspecialchars($ord['order_number']) ?> (<?= currency($ord['grand_total']) ?> - <?= ucfirst($ord['status']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 5px;">
                                Orders list dynamically loads when a client profile is chosen.
                            </div>
                        </div>

                        <!-- Ticket Subject -->
                        <div style="margin-bottom: 20px;">
                            <label class="form-label" style="display: block; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
                                Subject <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text" name="subject" class="form-input" placeholder="e.g. Alteration inquiry for Imperial Raw Silk Sherwani" required>
                        </div>

                        <!-- Priority & Assigned Agent -->
                        <div class="grid-2-cols" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                            <div>
                                <label class="form-label" style="display: block; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
                                    Priority Urgency
                                </label>
                                <select name="priority" class="form-input">
                                    <option value="medium" selected>🔵 Medium (Standard)</option>
                                    <option value="high">🟠 High (Urgent)</option>
                                    <option value="critical">🔴 Critical (VIP Escalation)</option>
                                    <option value="low">⚪ Low (Inquiry / Question)</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label" style="display: block; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
                                    Assign Concierge Staff
                                </label>
                                <select name="assigned_to" class="form-input">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($admins as $adm): ?>
                                        <option value="<?= $adm['id'] ?>"><?= htmlspecialchars($adm['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Initial Message -->
                        <div style="margin-bottom: 24px;">
                            <label class="form-label" style="display: block; font-size: 0.84rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">
                                Initial Inquiry / Case Notes <span style="color: #dc2626;">*</span>
                            </label>
                            <textarea name="message" class="form-input" rows="6" style="height: auto; padding: 12px 14px; font-size: 0.88rem; line-height: 1.5;" placeholder="Provide the customer's request details, measurements, or support inquiry..." required></textarea>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-light); padding-top: 20px;">
                            <a href="<?= url('admin/tickets') ?>" class="btn-secondary" style="padding: 10px 18px;">Cancel</a>
                            <button type="submit" class="btn-primary" style="padding: 10px 24px; font-weight: 700;">Open Support Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function onPageCustomerChange(encCustId) {
    const orderSelect = document.getElementById('pageOrderSelect');
    orderSelect.innerHTML = '<option value="">Loading client orders...</option>';

    if (!encCustId) {
        orderSelect.innerHTML = '<option value="">-- None / General Pre-sale Inquiry --</option>';
        return;
    }

    fetch('<?= url("admin/tickets/customer-orders") ?>/' + encCustId)
        .then(res => res.json())
        .then(data => {
            orderSelect.innerHTML = '<option value="">-- None / General Pre-sale Inquiry --</option>';
            if (data.success && data.orders.length > 0) {
                data.orders.forEach(ord => {
                    const opt = document.createElement('option');
                    opt.value = ord.encrypted_id;
                    opt.textContent = ord.order_number + ' (₹' + Math.round(ord.grand_total) + ' - ' + ord.status + ')';
                    orderSelect.appendChild(opt);
                });
            }
        })
        .catch(() => {
            orderSelect.innerHTML = '<option value="">-- None / General Pre-sale Inquiry --</option>';
        });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
