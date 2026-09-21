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
                    <h1 class="welcome-title">Orders Management</h1>
                    <p class="welcome-subtitle">Track, fulfill, and monitor customer orders and shipments in real-time.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/returns') ?>" class="btn-export" style="background: rgba(140, 48, 245, 0.08); border-color: rgba(140, 48, 245, 0.25); color: var(--brand-purple);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        <span>Returns & Exchanges</span>
                    </a>
                    <a href="<?= url('admin/orders/export?' . http_build_query($filters)) ?>" class="btn-export" id="btnExportOrders">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- Mini Order KPIs Bar -->
            <div class="kpi-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Orders</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total_orders']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Paid Revenue</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-blue); margin-top: 4px;">₹<?= number_format((int)round((float)$kpis['total_revenue'])) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Pending Dispatch</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($kpis['pending_dispatch']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">In Transit (Shipped)</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;"><?= number_format($kpis['in_transit']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Delivered Orders</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;"><?= number_format($kpis['delivered_orders']) ?></div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
                <?php
                $tabList = [
                    'all'              => ['label' => 'All Orders', 'count' => $counts['all']],
                    'pending'          => ['label' => 'Pending', 'count' => $counts['pending']],
                    'confirmed'        => ['label' => 'Confirmed', 'count' => $counts['confirmed']],
                    'packed'           => ['label' => 'Packed', 'count' => $counts['packed'] ?? 0],
                    'shipped'          => ['label' => 'Shipped', 'count' => $counts['shipped']],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'count' => $counts['out_for_delivery'] ?? 0],
                    'delivered'        => ['label' => 'Delivered', 'count' => $counts['delivered']],
                    'cancelled'        => ['label' => 'Cancelled', 'count' => $counts['cancelled']]
                ];
                $activeTab = $filters['status'] ?? 'all';
                ?>

                <?php foreach ($tabList as $key => $tab): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $key;
                    $tabParams['page'] = 1;
                    $tabUrl = url('admin/orders?' . http_build_query($tabParams));
                    $isActive = ($activeTab === $key);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($tab['label']) ?> (<?= $tab['count'] ?>)
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filters Card -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 18px 22px;">
                <form action="<?= url('admin/orders') ?>" method="GET" style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 220px; position: relative;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            placeholder="Search order #, customer, email, phone..." 
                            class="form-input" 
                            style="height: 42px; padding-left: 14px; font-size: 0.88rem;"
                        >
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="payment_status" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem;">
                            <option value="all">Payment: All</option>
                            <option value="paid" <?= $filters['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= $filters['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= $filters['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="refunded" <?= $filters['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="payment_method" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem;">
                            <option value="all">Method: All</option>
                            <option value="Razorpay" <?= $filters['payment_method'] === 'Razorpay' ? 'selected' : '' ?>>Razorpay</option>
                            <option value="COD" <?= $filters['payment_method'] === 'COD' ? 'selected' : '' ?>>COD</option>
                            <option value="UPI" <?= $filters['payment_method'] === 'UPI' ? 'selected' : '' ?>>UPI</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="sort" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem;">
                            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest Placed</option>
                            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest Placed</option>
                            <option value="amount_high" <?= $filters['sort'] === 'amount_high' ? 'selected' : '' ?>>Amount: High &rarr; Low</option>
                            <option value="amount_low" <?= $filters['sort'] === 'amount_low' ? 'selected' : '' ?>>Amount: Low &rarr; High</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary-gradient" style="height: 42px; width: auto; padding: 0 20px; font-size: 0.85rem;">
                        Filter Orders
                    </button>

                    <?php if (!empty($filters['search']) || $filters['payment_status'] !== 'all' || $filters['payment_method'] !== 'all' || $filters['status'] !== 'all' || $filters['sort'] !== 'newest'): ?>
                        <a href="<?= url('admin/orders') ?>" style="font-size: 0.82rem; color: var(--brand-orange); font-weight: 600;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Orders Table Card -->
            <div class="card-panel">
                <?php if (!empty($orders)): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Date Placed</th>
                                    <th>Grand Total</th>
                                    <th>Payment</th>
                                    <th>Fulfillment</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $payStatus = strtolower($order['payment_status'] ?? 'pending');
                                    $payBadgeClass = match($payStatus) {
                                        'paid', 'completed' => 'badge-paid',
                                        'failed' => 'badge-failed',
                                        'refunded' => 'badge-shipped',
                                        default => 'badge-pending'
                                    };

                                    $orderStatus = strtolower($order['status'] ?? 'pending');
                                    $ordBadgeClass = match($orderStatus) {
                                        'delivered' => 'badge-delivered',
                                        'shipped', 'out_for_delivery' => 'badge-shipped',
                                        'cancelled' => 'badge-cancelled',
                                        'confirmed', 'packed' => 'badge-pending',
                                        default => 'badge-processing'
                                    };

                                    $custInitial = strtoupper(substr($order['customer_name'] ?? 'C', 0, 1));
                                    $orderDetailUrl = url('admin/orders/' . $order['encrypted_id']);
                                    $orderInvoiceUrl = url('admin/orders/' . $order['encrypted_id'] . '/invoice');
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?= $orderDetailUrl ?>" class="order-code" style="text-decoration: underline; text-underline-offset: 3px;">
                                                <?= htmlspecialchars($order['order_number']) ?>
                                            </a>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= htmlspecialchars($order['payment_method']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--brand-blue-light); color: var(--brand-blue); font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <?= $custInitial ?>
                                                </div>
                                                <div>
                                                    <div class="customer-cell"><?= htmlspecialchars($order['customer_name']) ?></div>
                                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($order['shipping_city'] ?? 'India') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: var(--text-primary);"><?= $order['item_count'] ?> item(s)</span>
                                        </td>
                                        <td>
                                            <div style="font-size: 0.84rem; color: var(--text-primary); font-weight: 600;">
                                                <?= date('d M Y', strtotime($order['placed_at'])) ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                <?= date('h:i A', strtotime($order['placed_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="amount-cell">₹<?= number_format((int)round((float)$order['grand_total'])) ?></div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted);">Incl. 12% GST</div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $payBadgeClass ?>"><?= ucfirst($payStatus) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $ordBadgeClass ?>"><?= ucfirst(str_replace('_', ' ', $orderStatus)) ?></span>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                                <a href="<?= $orderDetailUrl ?>" class="tab-btn" style="padding: 6px 12px; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 5px;" title="View Order Details">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    <span>View</span>
                                                </a>
                                                <a href="<?= $orderInvoiceUrl ?>" target="_blank" class="tab-btn" style="padding: 6px 10px; font-size: 0.78rem;" title="Print Tax Invoice">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                        <rect x="6" y="14" width="12" height="8"></rect>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color-light);">
                            <div style="font-size: 0.82rem; color: var(--text-muted);">
                                Showing page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong> (<?= $pagination['total_records'] ?> orders)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = $pagination['current_page'] - 1;
                                    ?>
                                    <a href="<?= url('admin/orders?' . http_build_query($prevParams)) ?>" class="tab-btn">
                                        &larr; Previous
                                    </a>
                                <?php endif; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = $pagination['current_page'] + 1;
                                    ?>
                                    <a href="<?= url('admin/orders?' . http_build_query($nextParams)) ?>" class="tab-btn">
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
                        <div class="empty-state-title">No Orders Found</div>
                        <div class="empty-state-desc">
                            No orders match the selected filters or search query. Try clearing filters to see all orders.
                        </div>
                        <div style="margin-top: 16px;">
                            <a href="<?= url('admin/orders') ?>" class="btn-export">Reset All Filters</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
