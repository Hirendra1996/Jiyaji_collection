<?php
$title = 'Shipments & AWBs Logistics Hub | Jiyaji LX Staff Portal';
$filters = $filters ?? [
    'status'  => $_GET['status'] ?? 'all',
    'carrier' => $_GET['carrier'] ?? 'all',
    'search'  => trim($_GET['search'] ?? '')
];
$canCreate    = $canCreate ?? (function_exists('staff_can') ? staff_can('shipments', 'create') : false);
$kpis         = $kpis ?? [];
$carrierStats = $carrierStats ?? [];
$readyOrders  = $readyOrders ?? [];
$shipments    = $shipments ?? [];
$pagination   = $pagination ?? [];
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Header Section -->
            <div class="welcome-banner" style="background: linear-gradient(135deg, #0F172A 0%, #064E3B 60%, #047857 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.3);">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 999px; color: #A7F3D0;">
                            Logistics &bull; Carrier Network
                        </span>
                        <?php if ($canCreate): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 8px; border-radius: 6px;">
                                Write &bull; Dispatch &amp; AWB Generation
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 6px;">
                                Read-Only Clearance
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Shipments &amp; AWBs Logistics Hub
                    </h1>
                    <p style="font-size: 0.88rem; color: #D1FAE5; margin: 0;">
                        Carrier integrations, automated AWB tracking, live delivery scans, and courier shipping labels.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canCreate && !empty($readyOrders)): ?>
                        <button type="button" id="btnOpenDispatchModal" onclick="openDispatchModal()" style="background: #10B981; border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 18px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10B981';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Book Shipment (<?= count($readyOrders) ?> Ready)</span>
                        </button>
                    <?php endif; ?>

                    <a href="<?= url('portal/shipments/export?' . http_build_query($filters)) ?>" class="btn-export" id="btnExportShipments" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)';" onmouseout="this.style.background='rgba(255,255,255,0.15)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export Manifest CSV</span>
                    </a>
                </div>
            </div>

            <!-- Logistics KPI Summary Bar -->
            <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 24px;">
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Total Dispatched</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($kpis['total_dispatched'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">All allocated AWBs</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">In Transit</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #2563EB; margin-top: 4px;"><?= number_format($kpis['in_transit'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #2563EB; margin-top: 2px;">With air/surface carriers</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Out for Delivery</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #7C3AED; margin-top: 4px;"><?= number_format($kpis['out_for_delivery'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #7C3AED; margin-top: 2px;">Local delivery agent out</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Delivered On-Time</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #059669; margin-top: 4px;"><?= number_format($kpis['delivered'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #059669; margin-top: 2px;">Successful handoffs</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Ready for Dispatch</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #D97706; margin-top: 4px;"><?= number_format($kpis['ready_for_dispatch'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #D97706; margin-top: 2px;">Awaiting AWB allocation</div>
                </div>
            </div>

            <!-- Carrier Volume Breakdown Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 24px;">
                <?php foreach ($carrierStats as $carrierName => $cData): ?>
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; border-left: 4px solid <?= $cData['color'] ?>; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <div style="font-size: 0.88rem; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($carrierName) ?></div>
                                <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($cData['tag']) ?></div>
                            </div>
                            <span style="font-size: 1.35rem; font-weight: 800; color: <?= $cData['color'] ?>;"><?= $cData['count'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Status Tabs -->
            <?php
            $currentStatus = $filters['status'] ?? 'all';
            $tabList = [
                'all'              => ['label' => 'All Shipments', 'count' => ($kpis['total_dispatched'] ?? 0) + ($kpis['ready_for_dispatch'] ?? 0)],
                'in_transit'       => ['label' => 'In Transit', 'count' => $kpis['in_transit'] ?? 0],
                'out_for_delivery' => ['label' => 'Out for Delivery', 'count' => $kpis['out_for_delivery'] ?? 0],
                'delivered'        => ['label' => 'Delivered', 'count' => $kpis['delivered'] ?? 0],
                'unassigned'       => ['label' => 'Ready for Dispatch', 'count' => $kpis['ready_for_dispatch'] ?? 0],
                'failed'           => ['label' => 'RTO / Returned', 'count' => 0]
            ];
            ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px;">
                <?php foreach ($tabList as $key => $tab): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $key;
                    $tabParams['page'] = 1;
                    $tabUrl = url('portal/shipments?' . http_build_query($tabParams));
                    $isActive = ($currentStatus === $key);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 7px 16px; font-size: 0.82rem; font-weight: 700; border-radius: 9999px; text-decoration: none; white-space: nowrap; border: 1px solid <?= $isActive ? '#047857' : '#E2E8F0' ?>; background: <?= $isActive ? '#047857' : '#FFFFFF' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>; transition: all 0.2s;">
                        <?= htmlspecialchars($tab['label']) ?>
                        <?php if ($tab['count'] > 0): ?>
                            <span style="opacity: 0.85; font-weight: 600;">(<?= $tab['count'] ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/shipments') ?>" method="GET" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">

                    <div style="flex: 2; min-width: 260px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search by AWB #, Order #, Customer, Pincode, City..." style="width: 100%; height: 40px; padding: 0 14px; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 0.86rem; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="flex: 1; min-width: 180px;">
                        <select name="carrier" style="width: 100%; height: 40px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 12px; font-size: 0.84rem; background: #FFFFFF;">
                            <option value="all" <?= ($filters['carrier'] === 'all') ? 'selected' : '' ?>>All Carrier Partners</option>
                            <option value="BlueDart" <?= ($filters['carrier'] === 'BlueDart') ? 'selected' : '' ?>>BlueDart Express</option>
                            <option value="Delhivery" <?= ($filters['carrier'] === 'Delhivery') ? 'selected' : '' ?>>Delhivery Air</option>
                            <option value="Shiprocket" <?= ($filters['carrier'] === 'Shiprocket') ? 'selected' : '' ?>>Shiprocket</option>
                            <option value="DTDC" <?= ($filters['carrier'] === 'DTDC') ? 'selected' : '' ?>>DTDC Express</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" style="height: 40px; padding: 0 18px; background: #0F172A; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border: none; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            Filter
                        </button>
                        <a href="<?= url('portal/shipments') ?>" style="height: 40px; padding: 0 14px; background: #F1F5F9; color: #475569; font-weight: 600; font-size: 0.84rem; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Shipments Data Table -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #64748B; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                <th style="padding: 14px 18px;">Order &amp; Customer</th>
                                <th style="padding: 14px 18px;">AWB Number</th>
                                <th style="padding: 14px 18px;">Carrier Partner</th>
                                <th style="padding: 14px 18px;">Destination</th>
                                <th style="padding: 14px 18px;">Latest Scan Activity</th>
                                <th style="padding: 14px 18px;">Status</th>
                                <th style="padding: 14px 18px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($shipments)): ?>
                                <?php foreach ($shipments as $ship): ?>
                                    <?php
                                    $orderEncId = htmlspecialchars($ship['encrypted_id'] ?? encrypt_id($ship['id']));
                                    $awb = trim($ship['awb_number'] ?? '');
                                    $status = strtolower($ship['status'] ?? 'pending');
                                    $carrier = $ship['courier_partner'] ?? 'Standard Surface';

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
                                    ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        <!-- Order & Customer -->
                                        <td style="padding: 14px 18px;">
                                            <a href="<?= url('portal/orders/' . $orderEncId) ?>" style="font-weight: 800; color: #0F172A; text-decoration: none;">
                                                #<?= htmlspecialchars($ship['order_number']) ?>
                                            </a>
                                            <div style="font-size: 0.74rem; color: #64748B; margin-top: 2px;">
                                                <?= htmlspecialchars($ship['customer_name'] ?? 'Customer') ?>
                                                <?php if (!empty($ship['shipping_phone'])): ?>
                                                    &bull; <span style="color: #94A3B8;"><?= htmlspecialchars($ship['shipping_phone']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- AWB Number -->
                                        <td style="padding: 14px 18px;">
                                            <?php if (!empty($awb)): ?>
                                                <div style="display: inline-flex; align-items: center; gap: 6px; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; border: 1px solid #E2E8F0;">
                                                    <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #0F172A;">
                                                        <?= htmlspecialchars($awb) ?>
                                                    </span>
                                                    <button type="button" onclick="navigator.clipboard.writeText('<?= addslashes($awb) ?>'); alert('AWB copied: <?= addslashes($awb) ?>');" title="Copy AWB to Clipboard" style="border: none; background: transparent; cursor: pointer; color: #64748B; padding: 0; display: flex;">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span style="display: inline-block; font-size: 0.72rem; font-weight: 700; color: #B45309; background: #FEF3C7; padding: 3px 8px; border-radius: 6px;">
                                                    Awaiting AWB
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Carrier Partner -->
                                        <td style="padding: 14px 18px;">
                                            <div style="font-weight: 700; color: #0F172A;">
                                                <?= htmlspecialchars($carrier) ?>
                                            </div>
                                            <?php if (!empty($ship['estimated_delivery'])): ?>
                                                <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;">
                                                    Est. Deliv: <?= date('d M Y', strtotime($ship['estimated_delivery'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Destination -->
                                        <td style="padding: 14px 18px;">
                                            <div style="font-weight: 600; color: #0F172A;">
                                                <?= htmlspecialchars($ship['shipping_city'] ?? '') ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;">
                                                <?= htmlspecialchars($ship['shipping_state'] ?? '') ?> - <span style="font-family: monospace;"><?= htmlspecialchars($ship['shipping_pincode'] ?? '') ?></span>
                                            </div>
                                        </td>

                                        <!-- Latest Scan Activity -->
                                        <td style="padding: 14px 18px; max-width: 240px;">
                                            <?php if (!empty($ship['latest_activity'])): ?>
                                                <div style="font-size: 0.8rem; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($ship['latest_activity']) ?>">
                                                    <?= htmlspecialchars($ship['latest_activity']) ?>
                                                </div>
                                                <div style="font-size: 0.7rem; color: #94A3B8; margin-top: 2px;">
                                                    <?= (int)($ship['milestone_count'] ?? 0) ?> milestone scan(s)
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.74rem; color: #94A3B8; font-style: italic;">No scan events yet</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Badge -->
                                        <td style="padding: 14px 18px;">
                                            <span style="display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; background: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                                                <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td style="padding: 14px 18px; text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                                <a href="<?= url('portal/shipments/' . $orderEncId) ?>" style="padding: 5px 10px; border-radius: 6px; background: #F1F5F9; color: #0F172A; font-size: 0.75rem; font-weight: 700; text-decoration: none; border: 1px solid #CBD5E1; transition: all 0.2s;" onmouseover="this.style.background='#0F172A'; this.style.color='#FFFFFF';" onmouseout="this.style.background='#F1F5F9'; this.style.color='#0F172A';">
                                                    Live Track &rarr;
                                                </a>

                                                <?php if (!empty($awb)): ?>
                                                    <a href="<?= url('portal/shipments/' . $orderEncId . '/label') ?>" target="_blank" title="Print Courier Shipping Label" style="padding: 5px 8px; border-radius: 6px; background: #FFFFFF; color: #4F46E5; font-size: 0.75rem; font-weight: 700; text-decoration: none; border: 1px solid #C7D2FE;">
                                                        Label &#128438;
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="padding: 40px 20px; text-align: center; color: #64748B;">
                                        <div style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No shipment records found</div>
                                        <p style="font-size: 0.82rem; color: #94A3B8; margin: 0;">Try modifying your search criteria or switching carrier tabs.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if (!empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
                    <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 0.82rem; color: #64748B;">
                        <div>
                            Showing Page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong> (<?= number_format($pagination['total_records']) ?> total records)
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <?php
                            $prevPage = max(1, $pagination['current_page'] - 1);
                            $nextPage = min($pagination['total_pages'], $pagination['current_page'] + 1);
                            $hasPrev = ($pagination['current_page'] > 1);
                            $hasNext = ($pagination['current_page'] < $pagination['total_pages']);
                            ?>
                            <?php if ($hasPrev): ?>
                                <?php
                                $prevParams = $filters;
                                $prevParams['page'] = $prevPage;
                                ?>
                                <a href="<?= url('portal/shipments?' . http_build_query($prevParams)) ?>" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #CBD5E1; color: #0F172A; text-decoration: none; font-weight: 600;">
                                    &larr; Previous
                                </a>
                            <?php endif; ?>

                            <?php if ($hasNext): ?>
                                <?php
                                $nextParams = $filters;
                                $nextParams['page'] = $nextPage;
                                ?>
                                <a href="<?= url('portal/shipments?' . http_build_query($nextParams)) ?>" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #CBD5E1; color: #0F172A; text-decoration: none; font-weight: 600;">
                                    Next &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<!-- Modal: Book Carrier Shipment & Allocate AWB -->
<?php if ($canCreate): ?>
<div id="dispatchModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 520px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); animation: modalFadeIn 0.2s ease-out;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; background: #FAFBFD; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #0F172A;">
                    Book Carrier Shipment
                </h3>
                <p style="margin: 2px 0 0 0; font-size: 0.78rem; color: #64748B;">Generate airway bill and schedule carrier dispatch</p>
            </div>
            <button type="button" onclick="closeDispatchModal()" style="border: none; background: transparent; font-size: 1.4rem; color: #94A3B8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form action="<?= url('portal/shipments/create') ?>" method="POST" style="padding: 24px;">
            <?= csrf_field() ?>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                    Select Ready Order
                </label>
                <select name="order_encrypted_id" id="modalOrderSelect" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600; background: #FFFFFF;" required>
                    <option value="">-- Choose Order to Dispatch --</option>
                    <?php if (!empty($readyOrders)): ?>
                        <?php foreach ($readyOrders as $ro): ?>
                            <option value="<?= htmlspecialchars($ro['encrypted_id']) ?>">
                                #<?= htmlspecialchars($ro['order_number']) ?> &bull; <?= htmlspecialchars($ro['shipping_name'] ?? 'Customer') ?> (<?= htmlspecialchars($ro['shipping_city'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Courier Partner
                    </label>
                    <select name="courier_partner" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600; background: #FFFFFF;">
                        <option value="BlueDart Express">BlueDart Express</option>
                        <option value="Delhivery Air">Delhivery Air</option>
                        <option value="Shiprocket">Shiprocket</option>
                        <option value="DTDC Express">DTDC Express</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Shipping Tier
                    </label>
                    <select name="shipping_tier" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-weight: 600; background: #FFFFFF;">
                        <option value="Express Air">Express Priority Air</option>
                        <option value="Surface Standard">Standard Surface</option>
                        <option value="Overnight Cargo">Next-Day Cargo</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Package Weight (KG)
                    </label>
                    <input type="number" step="0.05" name="package_weight" value="1.25" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.86rem; font-weight: 600; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                        Estimated Delivery
                    </label>
                    <input type="date" name="estimated_delivery" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.76rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                    Custom AWB Waybill (Leave blank to auto-generate)
                </label>
                <input type="text" name="custom_awb" placeholder="e.g. BD98472910" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.84rem; font-family: monospace; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeDispatchModal()" style="height: 40px; padding: 0 16px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; font-weight: 600; font-size: 0.84rem; color: #475569; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="height: 40px; padding: 0 22px; border-radius: 8px; border: none; background: #047857; font-weight: 700; font-size: 0.84rem; color: #FFFFFF; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#065F46';" onmouseout="this.style.background='#047857';">
                    Allocate AWB &amp; Dispatch
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDispatchModal() {
    var modal = document.getElementById('dispatchModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}
function closeDispatchModal() {
    var modal = document.getElementById('dispatchModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
window.addEventListener('click', function(e) {
    var modal = document.getElementById('dispatchModal');
    if (e.target === modal) {
        closeDispatchModal();
    }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
