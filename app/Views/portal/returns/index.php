<?php
$title = 'Returns & Exchanges | Jiyaji LX Staff Portal';
$filters = $filters ?? [
    'status'       => $_GET['status'] ?? 'all',
    'request_type' => $_GET['request_type'] ?? 'all',
    'search'       => trim($_GET['search'] ?? ''),
    'date_from'    => $_GET['date_from'] ?? '',
    'date_to'      => $_GET['date_to'] ?? '',
    'sort'         => $_GET['sort'] ?? 'newest'
];
$canProcess = $canProcess ?? (function_exists('staff_can') ? staff_can('returns', 'process') : false);
$counts     = $counts ?? [];
$kpis       = $kpis ?? [];
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
                            RMA &bull; Reverse Logistics
                        </span>
                        <?php if ($canProcess): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 8px; border-radius: 6px;">
                                Write &bull; Full RMA Processing
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 6px;">
                                Read-Only Clearance
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Returns &amp; Exchanges Console
                    </h1>
                    <p style="font-size: 0.88rem; color: #CBD5E1; margin: 0;">
                        Manage customer return requests, quality inspections, reverse courier AWBs, and replacement dispatches.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canProcess): ?>
                        <a href="<?= url('portal/returns/create') ?>" id="btnCreateRma" style="background: #4F46E5; border: 1px solid rgba(255,255,255,0.2); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Initiate Return / Exchange</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('portal/returns/export?' . http_build_query($filters)) ?>" class="btn-export" id="btnExportReturns" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- Operational RMA KPIs Bar -->
            <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 24px;">
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Total RMA Volume</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($kpis['total_requests'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Cumulative return requests</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Pending Review / QA</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #D97706; margin-top: 4px;"><?= number_format($kpis['pending_review'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #D97706; margin-top: 2px;">Awaiting staff approval</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">In Reverse Transit</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #7C3AED; margin-top: 4px;"><?= number_format($kpis['in_reverse_transit'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #7C3AED; margin-top: 2px;">Pickup booked / in courier</div>
                </div>

                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Value Refunded</div>
                    <div style="font-size: 1.55rem; font-weight: 800; color: #059669; margin-top: 4px;">₹<?= number_format((float)($kpis['total_refunded'] ?? 0), 2) ?></div>
                    <div style="font-size: 0.72rem; color: #059669; margin-top: 2px;">Settled returns &amp; credits</div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px;">
                <?php
                $tabList = [
                    'all'              => ['label' => 'All Requests', 'count' => $counts['all'] ?? 0],
                    'requested'        => ['label' => 'Requested', 'count' => $counts['requested'] ?? 0],
                    'approved'         => ['label' => 'Approved', 'count' => $counts['approved'] ?? 0],
                    'pickup_scheduled' => ['label' => 'Pickup Scheduled', 'count' => $counts['pickup_scheduled'] ?? 0],
                    'item_received'    => ['label' => 'Item Received (QA)', 'count' => $counts['item_received'] ?? 0],
                    'refund_initiated' => ['label' => 'Refund Initiated', 'count' => $counts['refund_initiated'] ?? 0],
                    'completed'        => ['label' => 'Completed', 'count' => $counts['completed'] ?? 0],
                    'rejected'         => ['label' => 'Rejected', 'count' => $counts['rejected'] ?? 0]
                ];
                $activeTab = $filters['status'] ?? 'all';
                ?>

                <?php foreach ($tabList as $key => $tab): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $key;
                    $tabParams['page'] = 1;
                    $tabUrl = url('portal/returns?' . http_build_query($tabParams));
                    $isActive = ($activeTab === $key);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 7px 16px; font-size: 0.82rem; font-weight: 700; border-radius: 9999px; text-decoration: none; white-space: nowrap; border: 1px solid <?= $isActive ? '#4F46E5' : '#E2E8F0' ?>; background: <?= $isActive ? '#4F46E5' : '#FFFFFF' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>; transition: all 0.2s;">
                        <?= htmlspecialchars($tab['label']) ?> <span style="opacity: 0.85; font-weight: 600;">(<?= $tab['count'] ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filters Toolbar -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/returns') ?>" method="GET" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px; position: relative;">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search by RMA #, Order #, Customer, AWB, Reason..." style="width: 100%; height: 40px; padding: 0 14px; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 0.86rem; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <select name="request_type" style="width: 100%; height: 40px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 12px; font-size: 0.84rem; background: #FFFFFF;">
                            <option value="all" <?= ($filters['request_type'] === 'all') ? 'selected' : '' ?>>All Types</option>
                            <option value="return" <?= ($filters['request_type'] === 'return') ? 'selected' : '' ?>>Return (Refund)</option>
                            <option value="exchange" <?= ($filters['request_type'] === 'exchange') ? 'selected' : '' ?>>Exchange (Size/Style)</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="sort" style="width: 100%; height: 40px; border: 1px solid #CBD5E1; border-radius: 10px; padding: 0 12px; font-size: 0.84rem; background: #FFFFFF;">
                            <option value="newest" <?= ($filters['sort'] === 'newest') ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= ($filters['sort'] === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                            <option value="amount_high" <?= ($filters['sort'] === 'amount_high') ? 'selected' : '' ?>>Highest Value</option>
                            <option value="amount_low" <?= ($filters['sort'] === 'amount_low') ? 'selected' : '' ?>>Lowest Value</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" style="height: 40px; padding: 0 18px; background: #0F172A; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border: none; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            Filter
                        </button>
                        <a href="<?= url('portal/returns') ?>" style="height: 40px; padding: 0 14px; background: #F1F5F9; color: #475569; font-weight: 600; font-size: 0.84rem; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Returns Data Table -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #64748B; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                <th style="padding: 14px 18px;">RMA Identifier</th>
                                <th style="padding: 14px 18px;">Type</th>
                                <th style="padding: 14px 18px;">Order &amp; Customer</th>
                                <th style="padding: 14px 18px;">Reason &amp; Scope</th>
                                <th style="padding: 14px 18px;">Reverse AWB</th>
                                <th style="padding: 14px 18px;">Status</th>
                                <th style="padding: 14px 18px; text-align: right;">Value / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($returns)): ?>
                                <?php foreach ($returns as $ret): ?>
                                    <?php
                                    $retId = (int)$ret['id'];
                                    $rmaCode = 'RMA-' . str_pad($retId, 5, '0', STR_PAD_LEFT);
                                    $retEncId = htmlspecialchars($ret['encrypted_id'] ?? encrypt_id($retId));
                                    $orderEncId = htmlspecialchars($ret['order_encrypted_id'] ?? encrypt_id($ret['order_id']));
                                    $reqType = $ret['request_type'] ?? 'return';
                                    $status = strtolower($ret['status'] ?? 'requested');

                                    // Status Badge Styling
                                    $statusBg = match($status) {
                                        'completed' => '#ECFDF5',
                                        'refund_initiated' => '#EFF6FF',
                                        'item_received' => '#F5F3FF',
                                        'pickup_scheduled' => '#FEF3C7',
                                        'approved' => '#EEF2FF',
                                        'rejected' => '#FEF2F2',
                                        default => '#FFFBEB'
                                    };
                                    $statusColor = match($status) {
                                        'completed' => '#065F46',
                                        'refund_initiated' => '#1E40AF',
                                        'item_received' => '#6D28D9',
                                        'pickup_scheduled' => '#92400E',
                                        'approved' => '#3730A3',
                                        'rejected' => '#991B1B',
                                        default => '#B45309'
                                    };
                                    ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        <!-- RMA ID -->
                                        <td style="padding: 14px 18px;">
                                            <a href="<?= url('portal/returns/' . $retEncId) ?>" style="font-weight: 800; color: #4F46E5; text-decoration: none;">
                                                #<?= $rmaCode ?>
                                            </a>
                                            <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">
                                                <?= date('d M Y, h:i A', strtotime($ret['created_at'])) ?>
                                            </div>
                                        </td>

                                        <!-- Type Badge -->
                                        <td style="padding: 14px 18px;">
                                            <?php if ($reqType === 'exchange'): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: #F5F3FF; color: #6D28D9; border: 1px solid rgba(109, 40, 217, 0.2);">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                                                    Exchange
                                                </span>
                                            <?php else: ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: #FFF1F2; color: #BE123C; border: 1px solid rgba(190, 18, 60, 0.2);">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                    Return
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Order & Customer -->
                                        <td style="padding: 14px 18px;">
                                            <a href="<?= url('portal/orders/' . $orderEncId) ?>" style="font-weight: 700; color: #0F172A; text-decoration: none;">
                                                #<?= htmlspecialchars($ret['order_number'] ?? 'Order') ?>
                                            </a>
                                            <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px;">
                                                <?= htmlspecialchars($ret['customer_name'] ?? 'Guest Customer') ?>
                                                <?php if (!empty($ret['customer_email'])): ?>
                                                    &bull; <span style="color: #94A3B8;"><?= htmlspecialchars($ret['customer_email']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- Reason & Scope -->
                                        <td style="padding: 14px 18px; max-width: 240px;">
                                            <div style="font-weight: 600; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($ret['reason']) ?>">
                                                <?= htmlspecialchars($ret['reason']) ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;">
                                                <?= (int)($ret['item_count'] ?? 1) ?> item(s) &bull; <?= (int)($ret['total_units'] ?? 1) ?> unit(s)
                                            </div>
                                        </td>

                                        <!-- Reverse AWB -->
                                        <td style="padding: 14px 18px;">
                                            <?php if (!empty($ret['reverse_awb'])): ?>
                                                <span style="font-family: monospace; font-size: 0.78rem; font-weight: 700; color: #1E293B; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; border: 1px solid #E2E8F0;">
                                                    <?= htmlspecialchars($ret['reverse_awb']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="font-size: 0.75rem; color: #94A3B8; font-style: italic;">
                                                    Pending dispatch
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Badge -->
                                        <td style="padding: 14px 18px;">
                                            <span style="display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; background: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                                                <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                            </span>
                                        </td>

                                        <!-- Value & Action Button -->
                                        <td style="padding: 14px 18px; text-align: right;">
                                            <?php if ((float)($ret['refund_amount'] ?? 0) > 0): ?>
                                                <div style="font-weight: 800; color: #0F172A; font-size: 0.88rem;">
                                                    ₹<?= number_format((float)$ret['refund_amount'], 2) ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="font-size: 0.76rem; color: #64748B; font-weight: 600;">
                                                    Size Exchange
                                                </div>
                                            <?php endif; ?>

                                            <div style="margin-top: 6px; display: inline-flex; gap: 6px;">
                                                <a href="<?= url('portal/returns/' . $retEncId) ?>" style="padding: 4px 10px; border-radius: 6px; background: #F1F5F9; color: #0F172A; font-size: 0.75rem; font-weight: 700; text-decoration: none; border: 1px solid #E2E8F0; transition: all 0.2s;" onmouseover="this.style.background='#4F46E5'; this.style.color='#FFFFFF'; this.style.borderColor='#4F46E5';" onmouseout="this.style.background='#F1F5F9'; this.style.color='#0F172A'; this.style.borderColor='#E2E8F0';">
                                                    Inspect RMA &rarr;
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="padding: 40px 20px; text-align: center; color: #64748B;">
                                        <div style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No RMA requests found</div>
                                        <p style="font-size: 0.82rem; color: #94A3B8; margin: 0;">Try adjusting your search criteria, request type filter, or status tab.</p>
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
                                <a href="<?= url('portal/returns?' . http_build_query($prevParams)) ?>" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #CBD5E1; color: #0F172A; text-decoration: none; font-weight: 600;">
                                    &larr; Previous
                                </a>
                            <?php endif; ?>

                            <?php if ($hasNext): ?>
                                <?php
                                $nextParams = $filters;
                                $nextParams['page'] = $nextPage;
                                ?>
                                <a href="<?= url('portal/returns?' . http_build_query($nextParams)) ?>" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #CBD5E1; color: #0F172A; text-decoration: none; font-weight: 600;">
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
