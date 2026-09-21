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
                        <span>Shipments & AWBs</span>
                    </div>
                    <h1 class="welcome-title">Shipments & Logistics Hub</h1>
                    <p class="welcome-subtitle">Carrier integrations, automated AWB tracking, live delivery scans, and shipping labels.</p>
                </div>
                <div class="banner-controls">
                    <?php if (!empty($readyOrders)): ?>
                        <button type="button" class="btn-primary-gradient" style="height: 42px; padding: 0 18px; display: inline-flex; align-items: center; gap: 8px; width: auto;" onclick="openDispatchModal()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Book Carrier Shipment (<?= count($readyOrders) ?> Ready)</span>
                        </button>
                    <?php endif; ?>
                    <a href="<?= url('admin/orders') ?>" class="btn-export">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span>Orders Ledger</span>
                    </a>
                </div>
            </div>

            <!-- Logistics KPI Summary Bar -->
            <div class="kpi-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Dispatched</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total_dispatched']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">In Transit</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-blue); margin-top: 4px;"><?= number_format($kpis['in_transit']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Out for Delivery</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;"><?= number_format($kpis['out_for_delivery']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Delivered On-Time</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;"><?= number_format($kpis['delivered']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Ready to Dispatch</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($kpis['ready_for_dispatch']) ?></div>
                </div>
            </div>

            <!-- Carrier Volume Breakdown Grid -->
            <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
                <?php foreach ($carrierStats as $carrierName => $cData): ?>
                    <div class="kpi-card" style="padding: 16px; border-left: 4px solid <?= $cData['color'] ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-primary);"><?= htmlspecialchars($carrierName) ?></div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;"><?= htmlspecialchars($cData['tag']) ?></div>
                            </div>
                            <span style="font-size: 1.3rem; font-weight: 800; color: <?= $cData['color'] ?>;"><?= $cData['count'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Status Tabs -->
            <?php $currentStatus = $filters['status'] ?? 'all'; ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
                <?php
                $tabList = [
                    'all'              => 'All Shipments',
                    'in_transit'       => 'In Transit (' . $kpis['in_transit'] . ')',
                    'out_for_delivery' => 'Out for Delivery (' . $kpis['out_for_delivery'] . ')',
                    'delivered'        => 'Delivered (' . $kpis['delivered'] . ')',
                    'unassigned'       => 'Ready for Dispatch (' . $kpis['ready_for_dispatch'] . ')',
                    'failed'           => 'RTO / Returned'
                ];
                ?>
                <?php foreach ($tabList as $key => $label): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $key;
                    $tabParams['page'] = 1;
                    $tabUrl = url('admin/shipments?' . http_build_query($tabParams));
                    $isActive = ($currentStatus === $key);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Carrier Filter Panel -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 18px 22px;">
                <form action="<?= url('admin/shipments') ?>" method="GET" style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            placeholder="Search by AWB #, Order #, Customer, Pincode, City..." 
                            class="form-input" 
                            style="height: 42px; padding: 0 14px; font-size: 0.88rem;"
                        >
                    </div>

                    <div style="flex: 1; min-width: 180px;">
                        <select name="carrier" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem;">
                            <option value="all">Carrier: All Partners</option>
                            <option value="BlueDart" <?= $filters['carrier'] === 'BlueDart' ? 'selected' : '' ?>>BlueDart Express</option>
                            <option value="Delhivery" <?= $filters['carrier'] === 'Delhivery' ? 'selected' : '' ?>>Delhivery Air</option>
                            <option value="Shiprocket" <?= $filters['carrier'] === 'Shiprocket' ? 'selected' : '' ?>>Shiprocket Aggregator</option>
                            <option value="DTDC" <?= $filters['carrier'] === 'DTDC' ? 'selected' : '' ?>>DTDC Express</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary-gradient" style="height: 42px; width: auto; padding: 0 20px; font-size: 0.85rem;">
                        Filter Shipments
                    </button>

                    <?php if (!empty($filters['search']) || $filters['carrier'] !== 'all' || $filters['status'] !== 'all'): ?>
                        <a href="<?= url('admin/shipments') ?>" style="font-size: 0.82rem; color: var(--brand-orange); font-weight: 600;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Shipments Ledger Table Card -->
            <div class="card-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Active Logistics & Tracking Ledger</h2>
                        <div class="panel-subtitle">Showing <?= count($shipments) ?> of <?= $pagination['total_records'] ?> shipments</div>
                    </div>
                </div>

                <?php if (!empty($shipments)): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>AWB Tracking #</th>
                                    <th>Order #</th>
                                    <th>Customer & Destination</th>
                                    <th>Carrier & Routing</th>
                                    <th>Milestone State</th>
                                    <th>Expected Delivery</th>
                                    <th style="text-align: right;">Logistics Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $s): ?>
                                    <?php
                                    $hasAwb = !empty($s['awb_number']);
                                    $st = strtolower($s['status']);
                                    $stBadge = match($st) {
                                        'delivered'        => 'badge-delivered',
                                        'shipped'          => 'badge-shipped',
                                        'out_for_delivery' => 'badge-paid',
                                        'cancelled'        => 'badge-cancelled',
                                        default            => 'badge-pending'
                                    };
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($hasAwb): ?>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <span style="font-family: monospace; font-size: 0.88rem; font-weight: 700; color: var(--brand-blue); background: var(--brand-blue-light); padding: 3px 8px; border-radius: 4px;">
                                                        <?= htmlspecialchars($s['awb_number']) ?>
                                                    </span>
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                                    <?= $s['milestone_count'] ?> scan event(s) logged
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.82rem; color: var(--brand-orange); font-weight: 700;">
                                                    &bull; AWB Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= url('admin/orders/' . $s['encrypted_id']) ?>" class="order-code" style="text-decoration: underline; text-underline-offset: 3px;">
                                                <?= htmlspecialchars($s['order_number']) ?>
                                            </a>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= date('d M Y', strtotime($s['placed_at'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($s['customer_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($s['shipping_city']) ?>, <?= htmlspecialchars($s['shipping_state']) ?> - <strong><?= htmlspecialchars($s['shipping_pincode']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary); font-size: 0.85rem;">
                                                <?= htmlspecialchars($s['courier_partner'] ?: 'Not Assigned') ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted);">
                                                Hub: <?= strtoupper(substr($s['shipping_city'], 0, 3)) ?> / <?= htmlspecialchars($s['shipping_pincode']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $stBadge ?>">
                                                <?= ucfirst(str_replace('_', ' ', $s['status'])) ?>
                                            </span>
                                            <?php if (!empty($s['latest_activity'])): ?>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?= htmlspecialchars($s['latest_activity']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($s['estimated_delivery'])): ?>
                                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">
                                                    <?= date('d M Y', strtotime($s['estimated_delivery'])) ?>
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--status-success); font-weight: 600;">
                                                    On Schedule
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">TBD</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                                <?php if ($hasAwb): ?>
                                                    <a href="<?= url('admin/orders/' . $s['encrypted_id'] . '/shipping-label') ?>" target="_blank" class="btn-export" style="padding: 6px 12px; font-size: 0.78rem; background: rgba(140, 48, 245, 0.08); border-color: rgba(140, 48, 245, 0.25); color: var(--brand-purple);" title="Print 4x6 / A4 Shipping Label">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="1" y="3" width="15" height="13"></rect>
                                                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                                        </svg>
                                                        <span>Label</span>
                                                    </a>
                                                    <button type="button" class="btn-primary-gradient" style="height: 32px; padding: 0 12px; font-size: 0.78rem; width: auto;" onclick="openTrackingModal('<?= $s['encrypted_id'] ?>')">
                                                        <span>Track & Update</span>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn-primary-gradient" style="height: 32px; padding: 0 12px; font-size: 0.78rem; width: auto;" onclick="openSingleDispatchModal('<?= $s['encrypted_id'] ?>', '<?= htmlspecialchars($s['order_number']) ?>')">
                                                        <span>Allocate AWB</span>
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
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 18px; margin-top: 18px; border-top: 1px solid var(--border-color);">
                            <div style="font-size: 0.84rem; color: var(--text-muted);">
                                Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> (<?= $pagination['total_records'] ?> shipments)
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = $pagination['current_page'] - 1;
                                    ?>
                                    <a href="<?= url('admin/shipments?' . http_build_query($prevParams)) ?>" class="tab-btn">
                                        &larr; Previous
                                    </a>
                                <?php endif; ?>
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = $pagination['current_page'] + 1;
                                    ?>
                                    <a href="<?= url('admin/shipments?' . http_build_query($nextParams)) ?>" class="tab-btn">
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
                        <div class="empty-state-title">No Shipments Found</div>
                        <div class="empty-state-desc">
                            No shipments match the selected filters or search query.
                        </div>
                        <div style="margin-top: 16px;">
                            <a href="<?= url('admin/shipments') ?>" class="btn-export">Reset Filters</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- 1. Dispatch & Assign AWB Modal -->
<div class="modal-overlay" id="dispatchModalOverlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Book Carrier & Allocate AWB</h3>
                <p class="modal-subtitle">Generate courier manifest and assign tracking number to order.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeDispatchModal()">&times;</button>
        </div>

        <form method="POST" action="<?= url('admin/shipments/create') ?>">
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
            <div class="modal-body">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="dispatchOrderSelect">
                        Select Order to Dispatch <span style="color: red;">*</span>
                    </label>
                    <select name="order_encrypted_id" id="dispatchOrderSelect" style="width: 100%; height: 44px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;" required>
                        <?php if (empty($readyOrders)): ?>
                            <option value="">No pending orders ready for dispatch</option>
                        <?php else: ?>
                            <?php foreach ($readyOrders as $ro): ?>
                                <option value="<?= $ro['encrypted_id'] ?>">
                                    Order <?= htmlspecialchars($ro['order_number']) ?> &bull; <?= htmlspecialchars($ro['shipping_name']) ?> (<?= htmlspecialchars($ro['shipping_city']) ?>) - ₹<?= number_format((int)round((float)$ro['grand_total'])) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="courierPartnerSelect">Carrier Partner <span style="color: red;">*</span></label>
                        <select name="courier_partner" id="courierPartnerSelect" style="width: 100%; height: 44px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;" required>
                            <option value="BlueDart Express">BlueDart Express</option>
                            <option value="Delhivery Air">Delhivery Air</option>
                            <option value="Shiprocket">Shiprocket Aggregator</option>
                            <option value="DTDC Express">DTDC Express</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="shippingTierSelect">Shipping Service Tier</label>
                        <select name="shipping_tier" id="shippingTierSelect" style="width: 100%; height: 44px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;">
                            <option value="Express Air Priority">Express Air Priority</option>
                            <option value="Standard Surface">Standard Surface Cargo</option>
                            <option value="Same Day Regional">Same Day Regional</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="packageWeightInput">Dead Weight (KG)</label>
                        <input type="number" step="0.05" name="package_weight" id="packageWeightInput" value="1.25" style="width: 100%; height: 44px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="estDeliveryInput">Estimated Delivery</label>
                        <input type="date" name="estimated_delivery" id="estDeliveryInput" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" style="width: 100%; height: 44px; padding: 0 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;" for="customAwbInput">
                        Custom AWB / Tracking Code <span style="font-weight: 400; color: var(--text-muted);">(Leave empty to auto-generate)</span>
                    </label>
                    <input type="text" name="custom_awb" id="customAwbInput" placeholder="Auto-generates e.g. BD77665544 / DLHV889900" style="width: 100%; height: 44px; padding: 0 14px; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); background: #FFFFFF; font-size: 0.9rem;">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-export" onclick="closeDispatchModal()">Cancel</button>
                <button type="submit" class="btn-primary-gradient" style="height: 40px; padding: 0 20px; width: auto;">Book & Allocate AWB</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Live Carrier Tracking Modal -->
<div class="modal-overlay" id="trackingModalOverlay" style="display: none;">
    <div class="modal-dialog" style="max-width: 620px;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="trackModalAwbTitle">Live Carrier Tracking</h3>
                <p class="modal-subtitle" id="trackModalOrderSubtitle">Real-time scan logs and milestone progression.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeTrackingModal()">&times;</button>
        </div>

        <div class="modal-body" id="trackingModalBody">
            <!-- Loading Indicator -->
            <div id="trackingLoading" style="text-align: center; padding: 30px;">
                <div style="font-size: 1.1rem; color: var(--brand-blue); font-weight: 700;">Loading Tracking Events...</div>
            </div>

            <!-- Content Area -->
            <div id="trackingContent" style="display: none;">
                <!-- Summary Card -->
                <div style="background: var(--bg-surface-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Courier Partner</div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-primary);" id="trackCarrier"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Destination Hub</div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-primary);" id="trackDest"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Current State</div>
                        <div style="font-size: 0.85rem; font-weight: 700;" id="trackCurrentState"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Estimated Delivery</div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--status-success);" id="trackEstDel"></div>
                    </div>
                </div>

                <!-- Visual Step Indicator -->
                <div style="margin-bottom: 24px; padding: 0 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                        <div style="position: absolute; top: 14px; left: 10px; right: 10px; height: 3px; background: var(--border-color); z-index: 1;"></div>
                        <div id="stepBooked" class="track-step-node" style="z-index: 2; text-align: center;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--brand-blue); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 800; margin: 0 auto;">1</div>
                            <div style="font-size: 0.7rem; font-weight: 700; margin-top: 4px; color: var(--text-primary);">Booked</div>
                        </div>
                        <div id="stepTransit" class="track-step-node" style="z-index: 2; text-align: center;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--border-color); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 800; margin: 0 auto;">2</div>
                            <div style="font-size: 0.7rem; font-weight: 700; margin-top: 4px; color: var(--text-muted);">In Transit</div>
                        </div>
                        <div id="stepOut" class="track-step-node" style="z-index: 2; text-align: center;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--border-color); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 800; margin: 0 auto;">3</div>
                            <div style="font-size: 0.7rem; font-weight: 700; margin-top: 4px; color: var(--text-muted);">Out for Delivery</div>
                        </div>
                        <div id="stepDelivered" class="track-step-node" style="z-index: 2; text-align: center;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--border-color); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 800; margin: 0 auto;">4</div>
                            <div style="font-size: 0.7rem; font-weight: 700; margin-top: 4px; color: var(--text-muted);">Delivered</div>
                        </div>
                    </div>
                </div>

                <!-- Event Timeline List -->
                <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">
                    Carrier Checkpoint Activity
                </div>
                <div id="trackEventsList" style="border-left: 2px solid var(--border-color); margin-left: 12px; padding-left: 16px; display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;"></div>

                <!-- Add Tracking Scan Form -->
                <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px;">
                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 10px;">
                        Log New Carrier Scan Event
                    </div>
                    <form id="addMilestoneForm" method="POST" action="">
                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Milestone State</label>
                                <select name="status" class="form-input" style="height: 38px; padding: 0 10px; font-size: 0.85rem;" required>
                                    <option value="in_transit">In Transit (Hub Arrival/Depart)</option>
                                    <option value="out_for_delivery">Out for Delivery</option>
                                    <option value="delivered">Delivered Successfully</option>
                                    <option value="failed">Delivery Failed / RTO</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Facility Location</label>
                                <input type="text" name="location" placeholder="e.g. Mumbai Airport Cargo Hub" class="form-input" style="height: 38px; padding: 0 12px; font-size: 0.85rem;" required>
                            </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Scan Activity Note</label>
                            <input type="text" name="activity" placeholder="e.g. Scanned into sorting facility. Bag sealed." class="form-input" style="height: 38px; padding: 0 12px; font-size: 0.85rem;" required>
                        </div>
                        <div style="text-align: right;">
                            <button type="submit" class="btn-primary-gradient" style="height: 34px; padding: 0 14px; font-size: 0.8rem; width: auto;">
                                Record Scan Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openDispatchModal() {
    document.getElementById('dispatchModalOverlay').style.display = 'flex';
}

function openSingleDispatchModal(encId, orderNum) {
    const sel = document.getElementById('dispatchOrderSelect');
    if (sel) {
        sel.value = encId;
    }
    document.getElementById('dispatchModalOverlay').style.display = 'flex';
}

function closeDispatchModal() {
    document.getElementById('dispatchModalOverlay').style.display = 'none';
}

function openTrackingModal(encId) {
    const overlay = document.getElementById('trackingModalOverlay');
    const loading = document.getElementById('trackingLoading');
    const content = document.getElementById('trackingContent');
    const form = document.getElementById('addMilestoneForm');

    overlay.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';

    form.action = '<?= url("admin/shipments/") ?>' + encId + '/milestone';

    fetch('<?= url("admin/shipments/") ?>' + encId + '/track')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                closeTrackingModal();
                return;
            }

            document.getElementById('trackModalAwbTitle').textContent = 'AWB: ' + data.awb_number;
            document.getElementById('trackModalOrderSubtitle').textContent = 'Order #' + data.order_number + ' • ' + data.customer_name;
            document.getElementById('trackCarrier').textContent = data.courier_partner;
            document.getElementById('trackDest').textContent = data.destination_city + ' (' + data.destination_pincode + ')';
            document.getElementById('trackCurrentState').textContent = (data.current_status || '').toUpperCase().replace('_', ' ');
            document.getElementById('trackEstDel').textContent = data.estimated_delivery;

            // Update Stepper
            const s = data.current_status;
            const stepTransit = document.getElementById('stepTransit');
            const stepOut = document.getElementById('stepOut');
            const stepDelivered = document.getElementById('stepDelivered');

            function markActive(el) {
                const circle = el.querySelector('div:first-child');
                const label = el.querySelector('div:last-child');
                circle.style.background = 'var(--brand-blue)';
                label.style.color = 'var(--text-primary)';
            }

            if (s === 'shipped' || s === 'out_for_delivery' || s === 'delivered') {
                markActive(stepTransit);
            }
            if (s === 'out_for_delivery' || s === 'delivered') {
                markActive(stepOut);
            }
            if (s === 'delivered') {
                const circle = stepDelivered.querySelector('div:first-child');
                circle.style.background = 'var(--status-success)';
                stepDelivered.querySelector('div:last-child').style.color = 'var(--status-success)';
            }

            // Populate Events List
            const list = document.getElementById('trackEventsList');
            list.innerHTML = '';

            if (data.milestones && data.milestones.length > 0) {
                data.milestones.forEach(m => {
                    const item = document.createElement('div');
                    item.style.position = 'relative';
                    item.innerHTML = `
                        <div style="position: absolute; left: -22px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: var(--brand-blue);"></div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">${m.activity}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                            <strong>${m.location}</strong> &bull; ${m.event_time}
                        </div>
                    `;
                    list.appendChild(item);
                });
            } else {
                list.innerHTML = '<div style="font-size: 0.82rem; color: var(--text-muted);">No carrier scan events logged yet. Use the form below to record the first checkpoint.</div>';
            }

            loading.style.display = 'none';
            content.style.display = 'block';
        })
        .catch(err => {
            alert('Failed to fetch live tracking details.');
            closeTrackingModal();
        });
}

function closeTrackingModal() {
    document.getElementById('trackingModalOverlay').style.display = 'none';
}
</script>
