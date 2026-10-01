<?php
$title      = $title ?? 'Open New Support Ticket | Jiyaji LX Operations Portal';
$customers  = $customers ?? [];
$admins     = $admins ?? [];
$preselectedCustomer = $preselectedCustomer ?? null;
$customerOrders = $customerOrders ?? [];

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <a href="<?= url('portal/dashboard') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Dashboard</a>
                    <span>&rsaquo;</span>
                    <a href="<?= url('portal/tickets') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Support Desk</a>
                    <span>&rsaquo;</span>
                    <span style="color: #0F172A; font-weight: 600;">Open New Ticket</span>
                </div>

                <a href="<?= url('portal/tickets') ?>" style="color: #64748B; text-decoration: none; font-weight: 600; font-size: 0.82rem;">
                    &larr; Back to Ticket Queue
                </a>
            </div>

            <!-- Main Create Card -->
            <div style="max-width: 840px; margin: 0 auto;">
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px -4px rgba(0,0,0,0.05);">
                    
                    <!-- Header Banner -->
                    <div style="background: linear-gradient(135deg, #0F172A 0%, #312E81 100%); padding: 24px 28px; color: #FFFFFF;">
                        <h1 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 6px 0; letter-spacing: -0.01em;">Open New Support Ticket</h1>
                        <p style="font-size: 0.84rem; color: #C7D2FE; margin: 0;">
                            Create a client concierge ticket for bespoke inquiries, measurement revisions, exchange requests, or delivery escalations.
                        </p>
                    </div>

                    <!-- Form -->
                    <form action="<?= url('portal/tickets/store') ?>" method="POST" style="padding: 28px;">
                        <?= csrf_field() ?>

                        <!-- Client and Order Section -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                    Client Profile <span style="color: #EF4444;">*</span>
                                </label>
                                <select name="customer_id" id="clientSelect" onchange="fetchOrders(this.value)" required 
                                        style="width: 100%; height: 44px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 14px; font-size: 0.88rem; outline: none; background: #FFF;">
                                    <option value="">-- Choose Registered Customer --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['encrypted_id'] ?>" <?= ((int)$preselectedCustomer === (int)$c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                    Associated Order (Optional)
                                </label>
                                <select name="order_id" id="orderSelect" 
                                        style="width: 100%; height: 44px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 14px; font-size: 0.88rem; outline: none; background: #FFF;">
                                    <option value="">-- No Order Associated --</option>
                                    <?php if (!empty($customerOrders)): ?>
                                        <?php foreach ($customerOrders as $ord): ?>
                                            <option value="<?= $ord['encrypted_id'] ?>">
                                                #<?= htmlspecialchars($ord['order_number']) ?> (<?= currency($ord['grand_total']) ?> - <?= strtoupper($ord['status']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Urgency Priority & Assignee -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                    Urgency Priority <span style="color: #EF4444;">*</span>
                                </label>
                                <select name="priority" required 
                                        style="width: 100%; height: 44px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 14px; font-size: 0.88rem; outline: none; background: #FFF;">
                                    <option value="medium" selected>🔵 Medium Priority (Standard 24h SLA)</option>
                                    <option value="low">⚪ Low Priority (General Question)</option>
                                    <option value="high">🟠 High Priority (Alteration/Courier Expedite)</option>
                                    <option value="critical">🔴 Critical Priority (Urgent Complaint)</option>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                    Assign Concierge Staff
                                </label>
                                <select name="assigned_to" 
                                        style="width: 100%; height: 44px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 14px; font-size: 0.88rem; outline: none; background: #FFF;">
                                    <option value="">-- Assign Automatically / Unassigned --</option>
                                    <?php foreach ($admins as $adm): ?>
                                        <option value="<?= $adm['id'] ?>">
                                            <?= htmlspecialchars($adm['name']) ?> (<?= htmlspecialchars($adm['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                Ticket Subject <span style="color: #EF4444;">*</span>
                            </label>
                            <input type="text" name="subject" required placeholder="e.g. Bespoke Sherwani Sizing Adjustment or Delivery Tracking Delay" 
                                   style="width: 100%; height: 44px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 14px; font-size: 0.88rem; outline: none;"
                                   onfocus="this.style.borderColor='#4F46E5';" onblur="this.style.borderColor='#CBD5E1';">
                        </div>

                        <!-- Message -->
                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">
                                Inquiry Details / Initial Message <span style="color: #EF4444;">*</span>
                            </label>
                            <textarea name="message" required rows="6" placeholder="Describe client request, specific requirements, or order alteration inquiries in detail..." 
                                      style="width: 100%; border: 1px solid #CBD5E1; border-radius: 10px; padding: 14px; font-size: 0.88rem; outline: none; resize: vertical; line-height: 1.6;"
                                      onfocus="this.style.borderColor='#4F46E5';" onblur="this.style.borderColor='#CBD5E1';"></textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #E2E8F0; padding-top: 20px;">
                            <a href="<?= url('portal/tickets') ?>" style="padding: 11px 20px; border: 1px solid #CBD5E1; background: #FFF; color: #475569; border-radius: 10px; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
                                Cancel
                            </a>
                            <button type="submit" style="padding: 11px 26px; border: none; background: #4F46E5; color: #FFF; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; box-shadow: 0 2px 10px rgba(79,70,229,0.35);">
                                Open Support Ticket &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<script>
function fetchOrders(encCustId) {
    const orderSelect = document.getElementById('orderSelect');
    if (!orderSelect) return;

    orderSelect.innerHTML = '<option value="">Loading customer orders...</option>';
    
    if (!encCustId) {
        orderSelect.innerHTML = '<option value="">-- No Order Associated --</option>';
        return;
    }

    fetch('<?= url("portal/tickets/customer-orders") ?>/' + encodeURIComponent(encCustId))
        .then(res => res.json())
        .then(data => {
            orderSelect.innerHTML = '<option value="">-- No Order Associated --</option>';
            if (data.success && data.orders && data.orders.length > 0) {
                data.orders.forEach(ord => {
                    const opt = document.createElement('option');
                    opt.value = ord.encrypted_id;
                    opt.textContent = '#' + ord.order_number + ' (₹' + Number(ord.grand_total).toLocaleString() + ' - ' + ord.status.toUpperCase() + ')';
                    orderSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- No Previous Orders Found --';
                orderSelect.appendChild(opt);
            }
        })
        .catch(err => {
            console.error('Failed to load orders', err);
            orderSelect.innerHTML = '<option value="">-- No Order Associated --</option>';
        });
}
</script>
