<?php
$title     = $title ?? 'Customer Profile | Jiyaji LX Staff Portal';
$customer  = $customer ?? [];
$canManage = $canManage ?? false;

$orders    = $customer['orders'] ?? [];
$addresses = $customer['addresses'] ?? [];
$encId     = $customer['encrypted_id'] ?? '';

$orderCount = (int)($customer['order_count'] ?? 0);
$totalSpent = (int)($customer['total_spent'] ?? 0);
$avgOrder   = (int)($customer['avg_order_value'] ?? 0);
$isActive   = (bool)(int)($customer['is_active'] ?? 0);
$isVerified = (bool)(int)($customer['email_verified'] ?? 0);

$initials = strtoupper(mb_substr($customer['name'] ?? 'C', 0, 1));
$avatarColors = ['#0284C7', '#7C3AED', '#059669', '#DC2626', '#D97706', '#0891B2', '#9333EA'];
$avatarColor  = $avatarColors[($customer['id'] ?? 0) % count($avatarColors)];

$orderStatusColors = [
    'pending'    => ['#D97706', '#FEF3C7'],
    'confirmed'  => ['#2563EB', '#EFF6FF'],
    'processing' => ['#7C3AED', '#F5F3FF'],
    'shipped'    => ['#0891B2', '#ECFEFF'],
    'delivered'  => ['#059669', '#ECFDF5'],
    'cancelled'  => ['#DC2626', '#FEF2F2'],
    'returned'   => ['#6366F1', '#EEF2FF'],
];

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #0284C7; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/customers') ?>" style="color: #0284C7; text-decoration: none; font-weight: 600;">Customer Directory</a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;"><?= htmlspecialchars($customer['name'] ?? 'Profile') ?></span>
            </div>

            <!-- Profile Hero Card -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #075985 60%, #0284C7 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.25);">
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                        <!-- Large Avatar -->
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: <?= $avatarColor ?>33; border: 3px solid <?= $avatarColor ?>88; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.8rem; color: #FFFFFF; flex-shrink: 0; box-shadow: 0 0 0 4px rgba(255,255,255,0.12);">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                                <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;"><?= htmlspecialchars($customer['name'] ?? '') ?></h1>
                                <?php if ($isActive): ?>
                                    <span style="background: rgba(16,185,129,0.25); color: #A7F3D0; font-size: 0.73rem; font-weight: 700; padding: 2px 9px; border-radius: 999px; border: 1px solid rgba(167,243,208,0.4);">Active</span>
                                <?php else: ?>
                                    <span style="background: rgba(239,68,68,0.25); color: #FCA5A5; font-size: 0.73rem; font-weight: 700; padding: 2px 9px; border-radius: 999px; border: 1px solid rgba(252,165,165,0.4);">Suspended</span>
                                <?php endif; ?>
                                <?php if ($isVerified): ?>
                                    <span style="background: rgba(99,102,241,0.25); color: #C7D2FE; font-size: 0.73rem; font-weight: 700; padding: 2px 9px; border-radius: 999px;">✓ Email Verified</span>
                                <?php endif; ?>
                            </div>
                            <div style="color: #BAE6FD; font-size: 0.9rem; margin-bottom: 4px;"><?= htmlspecialchars($customer['email'] ?? '') ?></div>
                            <div style="color: #7DD3FC; font-size: 0.83rem; display: flex; gap: 16px; flex-wrap: wrap;">
                                <?php if (!empty($customer['phone'])): ?>
                                    <span><?= htmlspecialchars($customer['phone']) ?></span>
                                <?php endif; ?>
                                <span>Joined <?= !empty($customer['created_at']) ? date('M d, Y', strtotime($customer['created_at'])) : '—' ?></span>
                            </div>
                        </div>
                    </div>
                    <!-- Action buttons -->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if ($canManage): ?>
                            <a href="<?= url('portal/customers/' . $encId . '/edit') ?>" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.28); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.24)';" onmouseout="this.style.background='rgba(255,255,255,0.15)';">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Profile
                            </a>
                            <!-- Toggle Status -->
                            <form action="<?= url('portal/customers/' . $encId . '/status') ?>" method="POST" style="display: inline;" id="toggleStatusForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="return_url" value="portal/customers/<?= $encId ?>">
                                <button type="submit" style="background: <?= $isActive ? 'rgba(239,68,68,0.25)' : 'rgba(16,185,129,0.25)' ?>; border: 1px solid <?= $isActive ? 'rgba(252,165,165,0.4)' : 'rgba(167,243,208,0.4)' ?>; color: <?= $isActive ? '#FCA5A5' : '#A7F3D0' ?>; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px;"
                                    onclick="return confirm('<?= $isActive ? 'Suspend this customer account?' : 'Re-activate this customer account?' ?>');">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><?= $isActive ? '<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>' : '<polyline points="20 6 9 17 4 12"/>' ?></svg>
                                    <?= $isActive ? 'Suspend' : 'Activate' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Metric Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.12);">
                    <?php
                    $profileMetrics = [
                        ['label' => 'Total Orders', 'value' => $orderCount, 'sub' => $orderCount > 1 ? 'Repeat Buyer' : ($orderCount === 1 ? 'First-time Buyer' : 'No purchases yet')],
                        ['label' => 'Lifetime Spend', 'value' => '₹' . number_format($totalSpent), 'sub' => 'All completed orders'],
                        ['label' => 'Avg. Order Value', 'value' => '₹' . number_format($avgOrder), 'sub' => 'Per completed order'],
                        ['label' => 'Saved Addresses', 'value' => count($addresses), 'sub' => 'In address book'],
                    ];
                    ?>
                    <?php foreach ($profileMetrics as $m): ?>
                        <div style="text-align: center; padding: 12px; background: rgba(255,255,255,0.08); border-radius: 10px;">
                            <div style="font-size: 1.35rem; font-weight: 900; color: #FFFFFF; letter-spacing: -0.02em;"><?= $m['value'] ?></div>
                            <div style="font-size: 0.74rem; font-weight: 700; color: #BAE6FD; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 2px;"><?= $m['label'] ?></div>
                            <div style="font-size: 0.68rem; color: #7DD3FC; margin-top: 1px;"><?= $m['sub'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">

                <!-- Left: Order History -->
                <div>
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 20px;">
                        <div style="padding: 16px 20px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 1rem; font-weight: 800; color: #0F172A;">Order History</div>
                            <span style="font-size: 0.78rem; color: #64748B;"><?= $orderCount ?> order<?= $orderCount !== 1 ? 's' : '' ?></span>
                        </div>
                        <?php if (empty($orders)): ?>
                            <div style="padding: 40px 24px; text-align: center; color: #94A3B8;">
                                <div style="font-size: 1.5rem; margin-bottom: 8px;">🛍️</div>
                                <div style="font-weight: 600; color: #64748B;">No orders placed yet</div>
                                <div style="font-size: 0.8rem; margin-top: 4px;">This customer hasn't made a purchase.</div>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                                            <th style="text-align: left; padding: 10px 18px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Order #</th>
                                            <th style="text-align: center; padding: 10px 12px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Status</th>
                                            <th style="text-align: center; padding: 10px 12px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Items</th>
                                            <th style="text-align: right; padding: 10px 12px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Total</th>
                                            <th style="text-align: left; padding: 10px 12px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $o):
                                            $statusKey  = $o['status'] ?? 'pending';
                                            $oColors    = $orderStatusColors[$statusKey] ?? ['#64748B', '#F1F5F9'];
                                            $encOrderId = $o['encrypted_id'] ?? '';
                                        ?>
                                            <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.12s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">
                                                <td style="padding: 11px 18px;">
                                                    <a href="<?= url('portal/orders/' . $encOrderId) ?>" style="font-weight: 700; font-size: 0.84rem; color: #0284C7; text-decoration: none; font-family: monospace;" onmouseover="this.style.color='#1D4ED8';" onmouseout="this.style.color='#0284C7';">
                                                        <?= htmlspecialchars($o['order_number'] ?? "#ORD-{$o['id']}") ?>
                                                    </a>
                                                </td>
                                                <td style="padding: 11px 12px; text-align: center;">
                                                    <span style="background: <?= $oColors[1] ?>; color: <?= $oColors[0] ?>; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; white-space: nowrap;">
                                                        <?= ucfirst($statusKey) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 11px 12px; text-align: center; font-size: 0.83rem; color: #374151; font-weight: 600;"><?= (int)$o['item_count'] ?></td>
                                                <td style="padding: 11px 12px; text-align: right; font-weight: 800; font-size: 0.9rem; color: #0F172A;">₹<?= number_format((int)$o['grand_total']) ?></td>
                                                <td style="padding: 11px 12px; font-size: 0.8rem; color: #64748B; white-space: nowrap;">
                                                    <?= !empty($o['placed_at']) ? date('M d, Y', strtotime($o['placed_at'])) : '—' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Address Book + Add Address -->
                <div>
                    <!-- Address Book -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 20px;">
                        <div style="padding: 14px 18px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A;">Address Book</div>
                            <span style="font-size: 0.75rem; color: #64748B;"><?= count($addresses) ?> saved</span>
                        </div>
                        <?php if (empty($addresses)): ?>
                            <div style="padding: 28px 20px; text-align: center; color: #94A3B8; font-size: 0.82rem;">
                                No delivery addresses saved yet.
                            </div>
                        <?php else: ?>
                            <div style="padding: 12px 18px; display: flex; flex-direction: column; gap: 12px;">
                                <?php foreach ($addresses as $addr):
                                    $isDefault = (bool)(int)($addr['is_default'] ?? 0);
                                    $encAddrId = $addr['encrypted_id'] ?? '';
                                ?>
                                    <div style="border: 1px solid <?= $isDefault ? '#BAE6FD' : '#E2E8F0' ?>; border-radius: 10px; padding: 12px 14px; background: <?= $isDefault ? '#F0F9FF' : '#FAFAFA' ?>; position: relative;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-size: 0.78rem; font-weight: 700; color: #374151;"><?= htmlspecialchars($addr['label'] ?? 'Address') ?></span>
                                                <?php if ($isDefault): ?>
                                                    <span style="font-size: 0.66rem; font-weight: 700; color: #0284C7; background: #BAE6FD; padding: 1px 6px; border-radius: 999px;">Default</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($canManage): ?>
                                                <div style="display: flex; gap: 5px;">
                                                    <?php if (!$isDefault): ?>
                                                        <form action="<?= url("portal/customers/{$encId}/address/{$encAddrId}/default") ?>" method="POST" style="display: inline;">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" title="Set as default" style="background: none; border: none; cursor: pointer; color: #0284C7; font-size: 0.72rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #EFF6FF; white-space: nowrap;">⭐ Default</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form action="<?= url("portal/customers/{$encId}/address/{$encAddrId}/delete") ?>" method="POST" style="display: inline;" onsubmit="return confirm('Remove this address?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" title="Delete address" style="background: #FEF2F2; border: none; cursor: pointer; color: #DC2626; font-size: 0.72rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;">✕</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #374151; line-height: 1.5;">
                                            <?php if (!empty($addr['recipient'])): ?><div style="font-weight: 600;"><?= htmlspecialchars($addr['recipient']) ?></div><?php endif; ?>
                                            <?php if (!empty($addr['phone'])): ?><div style="color: #64748B; font-size: 0.75rem;"><?= htmlspecialchars($addr['phone']) ?></div><?php endif; ?>
                                            <div><?= htmlspecialchars($addr['address_line1'] ?? '') ?><?= !empty($addr['address_line2']) ? ', ' . htmlspecialchars($addr['address_line2']) : '' ?></div>
                                            <div><?= htmlspecialchars($addr['city'] ?? '') ?><?= !empty($addr['state']) ? ', ' . htmlspecialchars($addr['state']) : '' ?><?= !empty($addr['pincode']) ? ' - ' . htmlspecialchars($addr['pincode']) : '' ?></div>
                                            <?php if (!empty($addr['country'])): ?><div style="color: #64748B; font-size: 0.73rem;"><?= htmlspecialchars($addr['country']) ?></div><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Add Address Form (canManage only) -->
                    <?php if ($canManage): ?>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                            <div style="padding: 14px 18px; border-bottom: 1px solid #F1F5F9;">
                                <div style="font-size: 0.95rem; font-weight: 800; color: #0F172A;">Add Delivery Address</div>
                            </div>
                            <form action="<?= url("portal/customers/{$encId}/address") ?>" method="POST" style="padding: 16px 18px;">
                                <?= csrf_field() ?>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Label</label>
                                        <select name="label" style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; background: #fff;">
                                            <option value="Home">Home</option>
                                            <option value="Work">Work</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Phone</label>
                                        <input type="text" name="phone" placeholder="Contact number" style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                    </div>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Recipient Name</label>
                                    <input type="text" name="recipient" placeholder="Full name at this address" style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Address Line 1 <span style="color: #EF4444;">*</span></label>
                                    <input type="text" name="address_line1" placeholder="Street, building, apartment..." required style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Address Line 2</label>
                                    <input type="text" name="address_line2" placeholder="Landmark, floor..." style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">City <span style="color: #EF4444;">*</span></label>
                                        <input type="text" name="city" placeholder="City" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">State</label>
                                        <input type="text" name="state" placeholder="State" style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Pincode</label>
                                        <input type="text" name="pincode" placeholder="PIN / ZIP" style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                    </div>
                                    <div>
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #374151; display: block; margin-bottom: 4px;">Country</label>
                                        <input type="text" name="country" value="India" style="width: 100%; height: 36px; padding: 0 10px; font-size: 0.84rem; border: 1px solid #CBD5E1; border-radius: 7px; outline: none;">
                                    </div>
                                </div>
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #374151; font-weight: 600; margin-bottom: 14px; cursor: pointer;">
                                    <input type="checkbox" name="is_default" value="1" style="width: 16px; height: 16px; accent-color: #0284C7;"> Set as default address
                                </label>
                                <button type="submit" style="width: 100%; height: 40px; background: linear-gradient(135deg, #075985 0%, #0284C7 100%); color: #FFFFFF; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(2,132,199,0.3);">
                                    Save Address
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
