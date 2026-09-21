<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Breadcrumbs & Header -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 10px;">
                    <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                    <span>&nbsp;/&nbsp;</span>
                    <a href="<?= url('admin/customers') ?>" style="color: var(--brand-blue);">Customers & Support</a>
                    <span>&nbsp;/&nbsp;</span>
                    <span><?= htmlspecialchars($customer['name']) ?></span>
                </div>
            </div>

            <!-- Luxury Executive Profile Banner -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 24px 28px; background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <!-- Avatar -->
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, rgba(219, 39, 119, 0.2), rgba(140, 48, 245, 0.2)); color: #db2777; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; border: 2px solid rgba(219, 39, 119, 0.3); box-shadow: 0 4px 12px rgba(219, 39, 119, 0.1);">
                            <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                        </div>

                        <!-- Name & Key Attributes -->
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                                    <?= htmlspecialchars($customer['name']) ?>
                                </h1>

                                <!-- Status Badge -->
                                <?php if ($customer['is_active']): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(16, 185, 129, 0.1); color: #059669;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span>
                                        Suspended
                                    </span>
                                <?php endif; ?>

                                <!-- Email Verification Badge -->
                                <?php if ($customer['email_verified']): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(16, 185, 129, 0.1); color: #059669;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Verified Email
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(245, 158, 11, 0.1); color: #d97706;">
                                        Unverified
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="font-size: 0.88rem; color: var(--text-muted); margin-top: 6px; display: flex; gap: 16px; flex-wrap: wrap;">
                                <span>📧 <?= htmlspecialchars($customer['email']) ?></span>
                                <?php if (!empty($customer['phone'])): ?>
                                    <span>📞 <?= htmlspecialchars($customer['phone']) ?></span>
                                <?php endif; ?>
                                <span>🗓️ Member since <?= date('M d, Y', strtotime($customer['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Controls -->
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <a href="<?= url('admin/customers/' . $customer['encrypted_id'] . '/edit') ?>" class="btn-primary" style="height: 40px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;" id="btnEditCustomer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Edit Profile</span>
                        </a>

                        <!-- Open Support Ticket -->
                        <a href="<?= url('admin/tickets/create?customer=' . $customer['encrypted_id']) ?>" class="btn-secondary" style="height: 40px; padding: 0 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; color: var(--brand-blue);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                            <span>Support Ticket</span>
                        </a>

                        <!-- Toggle Status -->
                        <form action="<?= url('admin/customers/' . $customer['encrypted_id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_url" value="admin/customers/<?= $customer['encrypted_id'] ?>">
                            <button type="submit" class="btn-secondary" style="height: 40px; padding: 0 14px; font-weight: 600; color: <?= $customer['is_active'] ? '#dc2626' : '#059669' ?>;">
                                <?= $customer['is_active'] ? 'Suspend Account' : 'Activate Account' ?>
                            </button>
                        </form>

                        <!-- Safe Delete (only if 0 orders) -->
                        <?php if ($customer['order_count'] == 0): ?>
                            <form action="<?= url('admin/customers/' . $customer['encrypted_id'] . '/delete') ?>" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Permanently delete this customer account?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-secondary" style="height: 40px; padding: 0 14px; color: #dc2626;" title="Delete Profile">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Customer Lifetime Analytics KPI Grid -->
            <div class="catalog-kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 18px; border-left: 4px solid #db2777;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #db2777; text-transform: uppercase;">Total Lifetime Spend</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #db2777; margin-top: 4px;">₹<?= number_format((int)round((float)$customer['total_spent'])) ?></div>
                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;">Exclusive of cancelled orders</div>
                </div>

                <div class="kpi-card" style="padding: 18px; border-left: 4px solid var(--brand-purple);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-purple); text-transform: uppercase;">Total Orders Placed</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;"><?= $customer['order_count'] ?></div>
                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;"><?= $customer['order_count'] >= 2 ? 'VIP Repeat Client' : ($customer['order_count'] === 1 ? 'First-time Buyer' : 'Prospect (0 Orders)') ?></div>
                </div>

                <div class="kpi-card" style="padding: 18px; border-left: 4px solid var(--brand-blue);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-blue); text-transform: uppercase;">Average Order Value (AOV)</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--brand-blue); margin-top: 4px;">₹<?= number_format((int)round((float)$customer['avg_order_value'])) ?></div>
                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;">Spend per completed order</div>
                </div>

                <div class="kpi-card" style="padding: 18px; border-left: 4px solid var(--brand-orange);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-orange); text-transform: uppercase;">Last Purchase</div>
                    <div style="font-size: 1.15rem; font-weight: 800; color: var(--brand-orange); margin-top: 6px;">
                        <?= !empty($customer['last_order_at']) ? date('M d, Y', strtotime($customer['last_order_at'])) : 'No purchases yet' ?>
                    </div>
                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;">
                        <?= !empty($customer['last_order_at']) ? date('h:i A', strtotime($customer['last_order_at'])) : 'Awaiting first order' ?>
                    </div>
                </div>
            </div>

            <!-- 2-Column Responsive Layout: Orders History & Address Book -->
            <div class="grid-2-cols" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
                
                <!-- Left Column: Customer Order History -->
                <div class="card-panel" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-alt, #fafafc);">
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0;">Order History Ledger</h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 2px 0 0 0;">All transactions and fulfillment requests logged for this account.</p>
                        </div>
                        <span style="font-size: 0.78rem; font-weight: 700; color: var(--brand-purple); background: rgba(140, 48, 245, 0.1); padding: 4px 10px; border-radius: 999px;">
                            <?= count($customer['orders']) ?> <?= count($customer['orders']) === 1 ? 'Order' : 'Orders' ?>
                        </span>
                    </div>

                    <?php if (empty($customer['orders'])): ?>
                        <div style="padding: 48px 24px; text-align: center; color: var(--text-muted);">
                            <div style="font-size: 2.2rem; margin-bottom: 8px;">🛍️</div>
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">No Orders Placed Yet</div>
                            <div style="font-size: 0.82rem; margin-top: 4px;">When this client completes a checkout, their order timeline and invoices will appear here.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="font-size: 0.76rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-light); background: #ffffff;">
                                        <th style="padding: 12px 18px; text-align: left;">Order #</th>
                                        <th style="padding: 12px 18px; text-align: left;">Placed Date</th>
                                        <th style="padding: 12px 18px; text-align: center;">Items</th>
                                        <th style="padding: 12px 18px; text-align: right;">Amount</th>
                                        <th style="padding: 12px 18px; text-align: center;">Payment</th>
                                        <th style="padding: 12px 18px; text-align: center;">Status</th>
                                        <th style="padding: 12px 18px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customer['orders'] as $ord): ?>
                                        <tr style="border-bottom: 1px solid var(--border-light); font-size: 0.85rem;">
                                            <td style="padding: 12px 18px; font-weight: 700;">
                                                <a href="<?= url('admin/orders/' . $ord['encrypted_id']) ?>" style="color: var(--brand-blue); text-decoration: none;">
                                                    #<?= htmlspecialchars($ord['order_number']) ?>
                                                </a>
                                            </td>
                                            <td style="padding: 12px 18px; color: var(--text-muted); font-size: 0.82rem;">
                                                <?= date('M d, Y', strtotime($ord['placed_at'])) ?>
                                            </td>
                                            <td style="padding: 12px 18px; text-align: center;">
                                                <span style="font-weight: 600;"><?= $ord['item_count'] ?></span>
                                            </td>
                                            <td style="padding: 12px 18px; text-align: right; font-weight: 700; color: var(--text-primary);">
                                                ₹<?= number_format((int)round((float)$ord['grand_total'])) ?>
                                            </td>
                                            <td style="padding: 12px 18px; text-align: center;">
                                                <?php
                                                $payStatus = strtolower($ord['payment_status'] ?? 'pending');
                                                $payBg = match($payStatus) {
                                                    'paid'      => 'rgba(16, 185, 129, 0.1)',
                                                    'refunded'  => 'rgba(140, 48, 245, 0.1)',
                                                    'failed'    => 'rgba(239, 68, 68, 0.1)',
                                                    default     => 'rgba(245, 158, 11, 0.1)'
                                                };
                                                $payColor = match($payStatus) {
                                                    'paid'      => '#059669',
                                                    'refunded'  => '#7c3aed',
                                                    'failed'    => '#dc2626',
                                                    default     => '#d97706'
                                                };
                                                ?>
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: <?= $payBg ?>; color: <?= $payColor ?>;">
                                                    <?= ucfirst($payStatus) ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px 18px; text-align: center;">
                                                <?php
                                                $ordStatus = strtolower($ord['status'] ?? 'pending');
                                                $ordBg = match($ordStatus) {
                                                    'delivered' => 'rgba(16, 185, 129, 0.1)',
                                                    'shipped', 'out_for_delivery' => 'rgba(45, 130, 255, 0.1)',
                                                    'cancelled' => 'rgba(239, 68, 68, 0.1)',
                                                    default     => 'rgba(245, 158, 11, 0.1)'
                                                };
                                                $ordColor = match($ordStatus) {
                                                    'delivered' => '#059669',
                                                    'shipped', 'out_for_delivery' => '#2563eb',
                                                    'cancelled' => '#dc2626',
                                                    default     => '#d97706'
                                                };
                                                ?>
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: <?= $ordBg ?>; color: <?= $ordColor ?>;">
                                                    <?= ucfirst($ordStatus) ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px 18px; text-align: right;">
                                                <a href="<?= url('admin/orders/' . $ord['encrypted_id']) ?>" class="btn-secondary" style="padding: 4px 10px; font-size: 0.76rem; text-decoration: none;">
                                                    View &raquo;
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Profile Overview & Delivery Address Book -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- Profile Information Card -->
                    <div class="card-panel" style="padding: 22px;">
                        <h2 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0 0 16px 0; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                            Account Information
                        </h2>

                        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.86rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Full Legal Name</div>
                                <div style="font-weight: 600; color: var(--text-primary); margin-top: 2px;"><?= htmlspecialchars($customer['name']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Email Address</div>
                                <div style="color: var(--text-primary); margin-top: 2px;"><?= htmlspecialchars($customer['email']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Phone Number</div>
                                <div style="color: var(--text-primary); margin-top: 2px;">
                                    <?= !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : '<span style="color: var(--text-muted);">None registered</span>' ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Registration Date</div>
                                <div style="color: var(--text-primary); margin-top: 2px;"><?= date('F j, Y, g:i a', strtotime($customer['created_at'])) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Last Profile Update</div>
                                <div style="color: var(--text-primary); margin-top: 2px;"><?= date('F j, Y, g:i a', strtotime($customer['updated_at'])) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address Book Card -->
                    <div class="card-panel" style="padding: 22px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                            <h2 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                                Address Book
                            </h2>
                            <button type="button" class="btn-secondary" onclick="toggleAddressForm()" style="padding: 4px 10px; font-size: 0.76rem; font-weight: 600;" id="btnToggleAddAddress">
                                + Add Address
                            </button>
                        </div>

                        <!-- Add Address Collapsible Form -->
                        <div id="newAddressForm" style="display: none; background: var(--bg-surface-alt, #fafafc); border: 1px dashed var(--border-light); border-radius: 8px; padding: 16px; margin-bottom: 18px;">
                            <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0 0 12px 0;">Add Delivery Destination</h3>
                            <form action="<?= url('admin/customers/' . $customer['encrypted_id'] . '/address') ?>" method="POST">
                                <?= csrf_field() ?>
                                
                                <div style="margin-bottom: 10px;">
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Label (Home, Office, etc.)</label>
                                    <input type="text" name="label" value="Home" class="form-input" style="width: 100%;">
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Recipient Name</label>
                                        <input type="text" name="recipient" value="<?= htmlspecialchars($customer['name']) ?>" class="form-input" style="width: 100%;" required>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Phone</label>
                                        <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" class="form-input" style="width: 100%;">
                                    </div>
                                </div>

                                <div style="margin-bottom: 10px;">
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Address Line 1</label>
                                    <input type="text" name="address_line1" class="form-input" placeholder="House/Flat #, Building, Street" style="width: 100%;" required>
                                </div>

                                <div style="margin-bottom: 10px;">
                                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Address Line 2 (Optional)</label>
                                    <input type="text" name="address_line2" class="form-input" placeholder="Landmark, Area" style="width: 100%;">
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">City</label>
                                        <input type="text" name="city" class="form-input" style="width: 100%;" required>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">State</label>
                                        <input type="text" name="state" class="form-input" style="width: 100%;">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px;">
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">PIN Code</label>
                                        <input type="text" name="pincode" class="form-input" style="width: 100%;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Country</label>
                                        <input type="text" name="country" value="India" class="form-input" style="width: 100%;">
                                    </div>
                                </div>

                                <div style="margin-bottom: 14px;">
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; cursor: pointer;">
                                        <input type="checkbox" name="is_default" value="1">
                                        <span>Set as default delivery address</span>
                                    </label>
                                </div>

                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button type="button" class="btn-secondary" onclick="toggleAddressForm()" style="padding: 6px 12px; font-size: 0.8rem;">Cancel</button>
                                    <button type="submit" class="btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">Save Address</button>
                                </div>
                            </form>
                        </div>

                        <!-- Addresses List -->
                        <?php if (empty($customer['addresses'])): ?>
                            <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.84rem;">
                                📍 No saved delivery addresses found. Click "+ Add Address" to record a delivery destination.
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                <?php foreach ($customer['addresses'] as $addr): ?>
                                    <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 14px; position: relative; background: <?= $addr['is_default'] ? 'rgba(16, 185, 129, 0.03)' : '#ffffff' ?>; border-color: <?= $addr['is_default'] ? 'rgba(16, 185, 129, 0.3)' : 'var(--border-light)' ?>;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">
                                                    <?= htmlspecialchars($addr['label'] ?: 'Address') ?>
                                                </span>
                                                <?php if ($addr['is_default']): ?>
                                                    <span style="font-size: 0.7rem; font-weight: 700; background: rgba(16, 185, 129, 0.15); color: #059669; padding: 2px 7px; border-radius: 999px;">
                                                        Default
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Address Action Controls -->
                                            <div style="display: flex; gap: 4px; align-items: center;">
                                                <?php if (!$addr['is_default']): ?>
                                                    <form action="<?= url('admin/customers/' . $customer['encrypted_id'] . '/address/' . $addr['encrypted_id'] . '/default') ?>" method="POST" style="margin: 0;">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn-secondary" style="padding: 2px 8px; font-size: 0.72rem;" title="Make Primary Destination">
                                                            Set Default
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form action="<?= url('admin/customers/' . $customer['encrypted_id'] . '/address/' . $addr['encrypted_id'] . '/delete') ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Remove this address?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-secondary" style="padding: 2px 6px; font-size: 0.72rem; color: #dc2626;" title="Delete Address">
                                                        ✕
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div style="font-size: 0.83rem; color: var(--text-primary); font-weight: 600;">
                                            <?= htmlspecialchars($addr['recipient']) ?>
                                            <?php if (!empty($addr['phone'])): ?>
                                                <span style="font-weight: 400; color: var(--text-muted); font-size: 0.78rem;">(<?= htmlspecialchars($addr['phone']) ?>)</span>
                                            <?php endif; ?>
                                        </div>

                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">
                                            <?= htmlspecialchars($addr['address_line1']) ?><br>
                                            <?php if (!empty($addr['address_line2'])): ?>
                                                <?= htmlspecialchars($addr['address_line2']) ?><br>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> <?= htmlspecialchars($addr['pincode']) ?><br>
                                            <?= htmlspecialchars($addr['country']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleAddressForm() {
    const f = document.getElementById('newAddressForm');
    f.style.display = (f.style.display === 'none' || f.style.display === '') ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
