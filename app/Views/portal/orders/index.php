<?php
$title = 'Orders Management | Jiyaji LX Staff Portal';
$filters = $filters ?? [
    'status'         => $_GET['status'] ?? 'all',
    'payment_status' => $_GET['payment_status'] ?? 'all',
    'payment_method' => $_GET['payment_method'] ?? 'all',
    'search'         => trim($_GET['search'] ?? ''),
    'date_from'      => $_GET['date_from'] ?? '',
    'date_to'        => $_GET['date_to'] ?? '',
    'sort'           => $_GET['sort'] ?? 'newest'
];
$canEdit   = $canEdit ?? (function_exists('staff_can') ? staff_can('orders', 'edit') : false);
$canExport = $canExport ?? (function_exists('staff_can') ? staff_can('orders', 'export') : false);
$counts    = $counts ?? [];
$kpis      = $kpis ?? [];
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Header Section -->
            <div class="welcome-banner" style="background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 60%, #312E81 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 999px; color: #C7D2FE;">
                            Fulfillment Operations
                        </span>
                        <?php if ($canEdit): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 8px; border-radius: 6px;">
                                Write &bull; Status Updates Enabled
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 6px;">
                                Read-Only Clearance
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Orders &amp; Fulfillment Console
                    </h1>
                    <p style="font-size: 0.88rem; color: #CBD5E1; margin: 0;">
                        Track customer orders, verify payments, generate invoices, and advance fulfillment stages.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canExport): ?>
                        <a href="<?= url('portal/orders/export?' . http_build_query($filters)) ?>" class="btn-export" id="btnExportOrders" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <span>Export Ledger CSV</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Operational Order KPIs Bar -->
            <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 24px;">
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Total Orders</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($kpis['total_orders'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">All-time store volume</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Paid Revenue</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #2563EB; margin-top: 4px;">₹<?= number_format((int)round((float)($kpis['total_revenue'] ?? 0))) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Completed transactions</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Pending Dispatch</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #D97706; margin-top: 4px;"><?= number_format($kpis['pending_dispatch'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #D97706; margin-top: 2px;">Requires fulfillment</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">In Transit (Shipped)</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #7C3AED; margin-top: 4px;"><?= number_format($kpis['in_transit'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #7C3AED; margin-top: 2px;">With courier partners</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Delivered</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #059669; margin-top: 4px;"><?= number_format($kpis['delivered_orders'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #059669; margin-top: 2px;">Completed deliveries</div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px;">
                <?php
                $tabList = [
                    'all'              => ['label' => 'All Orders', 'count' => $counts['all'] ?? 0],
                    'pending'          => ['label' => 'Pending', 'count' => $counts['pending'] ?? 0],
                    'confirmed'        => ['label' => 'Confirmed', 'count' => $counts['confirmed'] ?? 0],
                    'packed'           => ['label' => 'Packed', 'count' => $counts['packed'] ?? 0],
                    'shipped'          => ['label' => 'Shipped', 'count' => $counts['shipped'] ?? 0],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'count' => $counts['out_for_delivery'] ?? 0],
                    'delivered'        => ['label' => 'Delivered', 'count' => $counts['delivered'] ?? 0],
                    'cancelled'        => ['label' => 'Cancelled', 'count' => $counts['cancelled'] ?? 0]
                ];
                $activeTab = $filters['status'] ?? 'all';
                ?>

                <?php foreach ($tabList as $key => $tab): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $key;
                    $tabParams['page'] = 1;
                    $tabUrl = url('portal/orders?' . http_build_query($tabParams));
                    $isActive = ($activeTab === $key);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 7px 16px; font-size: 0.82rem; font-weight: 700; border-radius: 9999px; text-decoration: none; white-space: nowrap; border: 1px solid <?= $isActive ? '#4F46E5' : '#E2E8F0' ?>; background: <?= $isActive ? '#4F46E5' : '#FFFFFF' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>; transition: all 0.2s;">
                        <?= htmlspecialchars($tab['label']) ?> <span style="opacity: 0.85; font-weight: 600;">(<?= $tab['count'] ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filters Toolbar -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/orders') ?>" method="GET" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px; position: relative;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            placeholder="Search order #, customer name, email, phone..." 
                            class="form-input" 
                            style="height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 14px; font-size: 0.86rem; width: 100%; box-sizing: border-box;"
                        >
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="payment_status" class="form-input" style="height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; width: 100%; box-sizing: border-box;">
                            <option value="all">Payment: All</option>
                            <option value="paid" <?= $filters['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= $filters['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= $filters['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="refunded" <?= $filters['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="payment_method" class="form-input" style="height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; width: 100%; box-sizing: border-box;">
                            <option value="all">Method: All</option>
                            <option value="Razorpay" <?= $filters['payment_method'] === 'Razorpay' ? 'selected' : '' ?>>Razorpay</option>
                            <option value="COD" <?= $filters['payment_method'] === 'COD' ? 'selected' : '' ?>>COD</option>
                            <option value="PhonePe" <?= $filters['payment_method'] === 'PhonePe' ? 'selected' : '' ?>>PhonePe</option>
                            <option value="UPI" <?= $filters['payment_method'] === 'UPI' ? 'selected' : '' ?>>UPI</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="sort" class="form-input" style="height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; width: 100%; box-sizing: border-box;">
                            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="amount_high" <?= $filters['sort'] === 'amount_high' ? 'selected' : '' ?>>Amount: High &rarr; Low</option>
                            <option value="amount_low" <?= $filters['sort'] === 'amount_low' ? 'selected' : '' ?>>Amount: Low &rarr; High</option>
                        </select>
                    </div>

                    <button type="submit" style="height: 40px; background: #4F46E5; color: #FFFFFF; border: none; border-radius: 8px; padding: 0 18px; font-weight: 700; font-size: 0.84rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                        Filter
                    </button>

                    <?php if (!empty($filters['search']) || $filters['payment_status'] !== 'all' || $filters['payment_method'] !== 'all' || $filters['status'] !== 'all' || $filters['sort'] !== 'newest'): ?>
                        <a href="<?= url('portal/orders') ?>" style="font-size: 0.82rem; color: #DC2626; font-weight: 700; text-decoration: none;">
                            Reset Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Orders Table Card -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <?php if (!empty($orders)): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #64748B; font-weight: 700; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <th style="padding: 14px 18px;">Order #</th>
                                    <th style="padding: 14px 18px;">Customer</th>
                                    <th style="padding: 14px 18px;">Date Placed</th>
                                    <th style="padding: 14px 18px;">Items</th>
                                    <th style="padding: 14px 18px;">Grand Total</th>
                                    <th style="padding: 14px 18px;">Payment</th>
                                    <th style="padding: 14px 18px;">Fulfillment Status</th>
                                    <th style="padding: 14px 18px; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $encryptedId = htmlspecialchars($order['encrypted_id'] ?? encrypt_id($order['id']));
                                    $payStatus = strtolower($order['payment_status'] ?? 'pending');
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

                                    $orderStatus = strtolower($order['status'] ?? 'pending');
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
                                    ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        <!-- Order Number -->
                                        <td style="padding: 14px 18px;">
                                            <a href="<?= url('portal/orders/' . $encryptedId) ?>" style="font-weight: 800; color: #4F46E5; text-decoration: none;">
                                                #<?= htmlspecialchars($order['order_number']) ?>
                                            </a>
                                            <?php if (!empty($order['courier_partner'])): ?>
                                                <div style="font-size: 0.7rem; color: #64748B; margin-top: 2px;">
                                                    via <?= htmlspecialchars($order['courier_partner']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Customer Details -->
                                        <td style="padding: 14px 18px;">
                                            <div style="font-weight: 700; color: #0F172A;">
                                                <?= htmlspecialchars($order['customer_name'] ?? $order['shipping_name'] ?? 'Guest Customer') ?>
                                            </div>
                                            <div style="font-size: 0.74rem; color: #64748B;">
                                                <?= htmlspecialchars($order['customer_email'] ?? $order['shipping_phone'] ?? '') ?>
                                            </div>
                                        </td>

                                        <!-- Date Placed -->
                                        <td style="padding: 14px 18px; color: #475569; white-space: nowrap;">
                                            <div style="font-weight: 600;"><?= date('d M Y', strtotime($order['placed_at'])) ?></div>
                                            <div style="font-size: 0.72rem; color: #94A3B8;"><?= date('h:i A', strtotime($order['placed_at'])) ?></div>
                                        </td>

                                        <!-- Items Summary -->
                                        <td style="padding: 14px 18px; color: #475569;">
                                            <span style="font-weight: 700; color: #0F172A;"><?= (int)($order['item_count'] ?? 1) ?></span> items
                                            <?php if (!empty($order['total_quantity'])): ?>
                                                <span style="font-size: 0.72rem; color: #94A3B8;">(<?= (int)$order['total_quantity'] ?> pcs)</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Grand Total -->
                                        <td style="padding: 14px 18px;">
                                            <span style="font-weight: 800; font-size: 0.95rem; color: #0F172A;">
                                                ₹<?= number_format((float)$order['grand_total'], 2) ?>
                                            </span>
                                        </td>

                                        <!-- Payment Status -->
                                        <td style="padding: 14px 18px;">
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: <?= $payBg ?>; color: <?= $payColor ?>;">
                                                <?= ucfirst($payStatus) ?>
                                            </span>
                                            <div style="font-size: 0.7rem; color: #94A3B8; margin-top: 2px;">
                                                <?= htmlspecialchars($order['payment_method']) ?>
                                            </div>
                                        </td>

                                        <!-- Fulfillment Status -->
                                        <td style="padding: 14px 18px;">
                                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: <?= $ordBg ?>; color: <?= $ordColor ?>;">
                                                <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $ordColor ?>;"></span>
                                                <?= ucfirst(str_replace('_', ' ', $orderStatus)) ?>
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td style="padding: 14px 18px; text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <!-- View Order Details -->
                                                <a href="<?= url('portal/orders/' . $encryptedId) ?>" class="btn-action" title="View Full Order Ledger" style="background: #EEF2FF; color: #4F46E5; padding: 6px 10px; border-radius: 6px; font-weight: 700; font-size: 0.76rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    <span>View</span>
                                                </a>

                                                <!-- Invoice -->
                                                <a href="<?= url('portal/orders/' . $encryptedId . '/invoice') ?>" target="_blank" class="btn-action" title="Print Tax Invoice" style="background: #F1F5F9; color: #475569; padding: 6px 9px; border-radius: 6px; font-weight: 700; font-size: 0.76rem; text-decoration: none; display: inline-flex; align-items: center;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                        <polyline points="14 2 14 8 20 8"></polyline>
                                                    </svg>
                                                </a>

                                                <!-- Quick Status Update Modal Trigger (If permitted) -->
                                                <?php if ($canEdit && $orderStatus !== 'cancelled' && $orderStatus !== 'delivered'): ?>
                                                    <button type="button" onclick="openQuickStatusModal('<?= $encryptedId ?>', '<?= htmlspecialchars($order['order_number']) ?>', '<?= $orderStatus ?>')" title="Advance Fulfillment Status" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 6px 9px; border-radius: 6px; font-weight: 700; font-size: 0.76rem; cursor: pointer; display: inline-flex; align-items: center;">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <polyline points="9 18 15 12 9 6"></polyline>
                                                        </svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php 
                    $currPage = (int)($pagination['current_page'] ?? 1);
                    $totPages = (int)($pagination['total_pages'] ?? 1);
                    $totRecords = (int)($pagination['total_records'] ?? $pagination['total'] ?? 0);
                    ?>
                    <?php if ($totPages > 1): ?>
                        <div style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #F1F5F9; flex-wrap: wrap; gap: 10px;">
                            <div style="font-size: 0.8rem; color: #64748B;">
                                Showing Page <strong><?= $currPage ?></strong> of <strong><?= $totPages ?></strong> (<?= number_format($totRecords) ?> total orders)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <?php if ($currPage > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = $currPage - 1;
                                    ?>
                                    <a href="<?= url('portal/orders?' . http_build_query($prevParams)) ?>" style="padding: 6px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; color: #0F172A; text-decoration: none; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                        &larr; Prev
                                    </a>
                                <?php endif; ?>

                                <?php if ($currPage < $totPages): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = $currPage + 1;
                                    ?>
                                    <a href="<?= url('portal/orders?' . http_build_query($nextParams)) ?>" style="padding: 6px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; color: #0F172A; text-decoration: none; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Empty State -->
                    <div style="padding: 60px 20px; text-align: center;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0 0 6px 0;">No Orders Found</h3>
                        <p style="font-size: 0.85rem; color: #64748B; margin: 0 0 16px 0; max-width: 420px; display: inline-block;">
                            There are currently no customer orders matching your active filter criteria. Try clearing search filters or changing the fulfillment status tab.
                        </p>
                        <div>
                            <a href="<?= url('portal/orders') ?>" style="padding: 8px 18px; background: #4F46E5; color: #FFFFFF; text-decoration: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700;">
                                Reset All Filters
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<!-- Quick Status Update Modal (Only accessible if staff_can('orders', 'edit')) -->
<?php if ($canEdit): ?>
<div id="quickStatusModal" style="display:none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 460px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0F172A;">
                Advance Order Fulfillment
            </h3>
            <button type="button" onclick="closeQuickStatusModal()" style="background: transparent; border: none; font-size: 1.25rem; color: #94A3B8; cursor: pointer;">&times;</button>
        </div>

        <form id="quickStatusForm" action="" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'portal/orders') ?>">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Order Number</label>
                <div id="modalOrderNum" style="font-weight: 800; font-size: 1rem; color: #4F46E5;"></div>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="modalStatusSelect" style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Update Status To</label>
                <select id="modalStatusSelect" name="status" required style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; font-size: 0.88rem; font-weight: 600;">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="packed">Packed</option>
                    <option value="shipped">Shipped</option>
                    <option value="out_for_delivery">Out for Delivery</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="modalNote" style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Internal Staff Audit Note (Optional)</label>
                <textarea id="modalNote" name="note" rows="2" placeholder="e.g. Package packed by logistics bay 4" style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; font-size: 0.84rem; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeQuickStatusModal()" style="padding: 10px 18px; border-radius: 8px; background: #F1F5F9; border: 1px solid #E2E8F0; color: #475569; font-weight: 700; font-size: 0.84rem; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; border-radius: 8px; background: #4F46E5; border: none; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; cursor: pointer;">Save Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuickStatusModal(encryptedId, orderNumber, currentStatus) {
    const modal = document.getElementById('quickStatusModal');
    const form = document.getElementById('quickStatusForm');
    const orderNumEl = document.getElementById('modalOrderNum');
    const selectEl = document.getElementById('modalStatusSelect');

    form.action = '<?= url("portal/orders") ?>/' + encryptedId + '/status';
    orderNumEl.textContent = '#' + orderNumber;
    selectEl.value = currentStatus;

    modal.style.display = 'flex';
}

function closeQuickStatusModal() {
    document.getElementById('quickStatusModal').style.display = 'none';
}
</script>
<?php endif; ?>
