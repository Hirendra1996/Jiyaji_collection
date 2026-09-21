<?php
$title      = $title ?? 'Stock Movement Audit Log | Jiyaji LX Staff Portal';
$movements  = $movements ?? [];
$filters    = $filters ?? [];
$pagination = $pagination ?? ['has_prev' => false, 'has_next' => false, 'current_page' => 1, 'total_pages' => 1, 'total_records' => 0];

include __DIR__ . '/../layouts/header.php';

// Movement reason labels and colors
$reasonMap = [
    'manual_restock' => ['label' => 'Manual Restock',     'color' => '#059669', 'bg' => '#ECFDF5'],
    'sale'           => ['label' => 'Sale (Deducted)',     'color' => '#2563EB', 'bg' => '#EFF6FF'],
    'return'         => ['label' => 'Return / RMA',        'color' => '#7C3AED', 'bg' => '#F5F3FF'],
    'adjustment'     => ['label' => 'Inv. Adjustment',     'color' => '#D97706', 'bg' => '#FEF3C7'],
    'cancellation'   => ['label' => 'Order Cancellation',  'color' => '#0891B2', 'bg' => '#ECFEFF'],
];
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #10B981; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/stock') ?>" style="color: #10B981; text-decoration: none; font-weight: 600;">Stock &amp; Replenishment</a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Movement Audit Log</span>
            </div>

            <!-- Page Header -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 55%, #4338CA 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(67, 56, 202, 0.25);">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #C7D2FE; margin-bottom: 8px; display: inline-block;">
                        Audit Trail &bull; Immutable &bull; All Stock Events
                    </div>
                    <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Stock Movement Audit Log
                    </h1>
                    <p style="font-size: 0.88rem; color: #C7D2FE; margin: 0;">
                        Every inventory event — sales, restocks, adjustments, returns — recorded immutably with full traceability.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="<?= url('portal/stock') ?>" style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="10 17 4 11 10 5"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                        Stock Dashboard
                    </a>
                    <a href="<?= url('portal/stock/export-movements?' . http_build_query(array_filter($filters, fn($v) => $v !== ''))) ?>" style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/stock/movements') ?>" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                            placeholder="Search by SKU, Product Name..."
                            style="width: 100%; height: 40px; padding: 0 14px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                            onfocus="this.style.borderColor='#6366F1';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>
                    <div style="flex: 0 0 auto; min-width: 170px;">
                        <select name="reason" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['reason'] ?? '') === '') ? 'selected' : '' ?>>All Reasons</option>
                            <option value="manual_restock" <?= (($filters['reason'] ?? '') === 'manual_restock') ? 'selected' : '' ?>>Manual Restock</option>
                            <option value="sale"           <?= (($filters['reason'] ?? '') === 'sale')           ? 'selected' : '' ?>>Sale (Deduction)</option>
                            <option value="return"         <?= (($filters['reason'] ?? '') === 'return')         ? 'selected' : '' ?>>Return / RMA</option>
                            <option value="adjustment"     <?= (($filters['reason'] ?? '') === 'adjustment')     ? 'selected' : '' ?>>Inventory Adjustment</option>
                            <option value="cancellation"   <?= (($filters['reason'] ?? '') === 'cancellation')   ? 'selected' : '' ?>>Order Cancellation</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px;">
                        <select name="direction" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['direction'] ?? '') === '') ? 'selected' : '' ?>>All Directions</option>
                            <option value="in"  <?= (($filters['direction'] ?? '') === 'in')  ? 'selected' : '' ?>>Inbound (IN +)</option>
                            <option value="out" <?= (($filters['direction'] ?? '') === 'out') ? 'selected' : '' ?>>Outbound (OUT −)</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 140px;">
                        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                            title="Date From" style="width: 100%; height: 40px; padding: 0 10px; font-size: 0.86rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                            onfocus="this.style.borderColor='#6366F1';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>
                    <div style="flex: 0 0 auto; min-width: 140px;">
                        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                            title="Date To" style="width: 100%; height: 40px; padding: 0 10px; font-size: 0.86rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                            onfocus="this.style.borderColor='#6366F1';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>
                    <button type="submit" style="height: 40px; padding: 0 20px; background: #6366F1; color: #FFFFFF; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Apply</button>
                    <a href="<?= url('portal/stock/movements') ?>" style="height: 40px; padding: 0 16px; background: #F1F5F9; color: #475569; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center;">Clear</a>
                </form>
            </div>

            <!-- Movements Table -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 24px;">
                <div style="padding: 16px 22px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 1rem; font-weight: 800; color: #0F172A;">Movement Ledger</div>
                    <div style="font-size: 0.8rem; color: #64748B;">
                        <?= number_format($pagination['total_records'] ?? 0) ?> total movements
                        &mdash; Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?>
                    </div>
                </div>

                <?php if (empty($movements)): ?>
                    <div style="padding: 56px 24px; text-align: center;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #F8FAFC; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <div style="font-weight: 700; color: #475569; margin-bottom: 4px; font-size: 1rem;">No movements recorded yet</div>
                        <div style="font-size: 0.82rem; color: #94A3B8;">Stock events will appear here as inventory changes are made — sales, restocks, adjustments, and returns.</div>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                                    <th style="text-align: left; padding: 12px 20px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">#</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Date &amp; Time</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Product / SKU</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Reason</th>
                                    <th style="text-align: center; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Delta</th>
                                    <th style="text-align: center; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Stock After</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Note / Ref.</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movements as $m):
                                    $delta       = (int)($m['movement'] ?? 0);
                                    $isPositive  = $delta >= 0;
                                    $deltaLabel  = ($isPositive ? '+' : '') . $delta;
                                    $deltaColor  = $isPositive ? '#059669' : '#DC2626';
                                    $deltaBg     = $isPositive ? '#ECFDF5' : '#FEF2F2';
                                    $reasonKey   = $m['reason'] ?? 'adjustment';
                                    $reasonInfo  = $reasonMap[$reasonKey] ?? ['label' => ucfirst(str_replace('_', ' ', $reasonKey)), 'color' => '#64748B', 'bg' => '#F1F5F9'];
                                    $stockAfter  = (int)($m['current_stock'] ?? 0);
                                    $stockColor  = $stockAfter === 0 ? '#DC2626' : ($stockAfter <= (int)$m['low_stock_threshold'] ? '#D97706' : '#10B981');
                                    $createdAt   = !empty($m['created_at']) ? date('M d, Y', strtotime($m['created_at'])) . '<br><span style="font-size:0.72rem;color:#94A3B8;">' . date('h:i A', strtotime($m['created_at'])) . '</span>' : '—';
                                ?>
                                <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.12s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">

                                    <!-- ID -->
                                    <td style="padding: 12px 20px;">
                                        <span style="font-family: monospace; font-size: 0.75rem; color: #94A3B8; font-weight: 700;">#<?= $m['movement_id'] ?></span>
                                    </td>

                                    <!-- Date -->
                                    <td style="padding: 12px 14px; white-space: nowrap; font-size: 0.82rem; color: #374151; line-height: 1.6;"><?= $createdAt ?></td>

                                    <!-- Product / SKU -->
                                    <td style="padding: 12px 14px; max-width: 220px;">
                                        <a href="<?= url('portal/products/' . htmlspecialchars($m['encrypted_product_id'] ?? '')) ?>" style="font-weight: 700; color: #0F172A; text-decoration: none; font-size: 0.84rem; display: block; max-width: 210px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" onmouseover="this.style.color='#6366F1';" onmouseout="this.style.color='#0F172A';">
                                            <?= htmlspecialchars($m['product_name'] ?? '') ?>
                                        </a>
                                        <div style="font-family: monospace; font-size: 0.73rem; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($m['sku'] ?? '') ?></div>
                                        <?php if (!empty($m['color_name'])): ?>
                                            <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 1px;"><?= htmlspecialchars($m['color_name']) ?><?= !empty($m['size']) ? ' / Size ' . htmlspecialchars($m['size']) : '' ?></div>
                                        <?php elseif (!empty($m['size'])): ?>
                                            <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 1px;">Size <?= htmlspecialchars($m['size']) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Reason badge -->
                                    <td style="padding: 12px 14px; white-space: nowrap;">
                                        <span style="background: <?= $reasonInfo['bg'] ?>; color: <?= $reasonInfo['color'] ?>; padding: 3px 10px; border-radius: 999px; font-size: 0.73rem; font-weight: 700;">
                                            <?= htmlspecialchars($reasonInfo['label']) ?>
                                        </span>
                                    </td>

                                    <!-- Delta -->
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <span style="background: <?= $deltaBg ?>; color: <?= $deltaColor ?>; padding: 4px 12px; border-radius: 8px; font-size: 0.9rem; font-weight: 800; font-family: monospace;">
                                            <?= $deltaLabel ?>
                                        </span>
                                    </td>

                                    <!-- Stock After -->
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <span style="font-weight: 800; font-size: 0.95rem; color: <?= $stockColor ?>;"><?= number_format($stockAfter) ?></span>
                                        <div style="font-size: 0.7rem; color: #94A3B8;">units</div>
                                    </td>

                                    <!-- Note / Reference -->
                                    <td style="padding: 12px 14px; max-width: 180px;">
                                        <?php if (!empty($m['note'])): ?>
                                            <span style="font-size: 0.8rem; color: #374151; display: block; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($m['note']) ?>">
                                                <?= htmlspecialchars($m['note']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['reference_id'])): ?>
                                            <span style="font-size: 0.72rem; font-family: monospace; color: #6366F1; background: #EEF2FF; padding: 1px 6px; border-radius: 3px; font-weight: 700;">Ref#<?= htmlspecialchars((string)$m['reference_id']) ?></span>
                                        <?php endif; ?>
                                        <?php if (empty($m['note']) && empty($m['reference_id'])): ?>
                                            <span style="color: #CBD5E1; font-size: 0.82rem;">&mdash;</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Staff Author -->
                                    <td style="padding: 12px 14px; white-space: nowrap;">
                                        <span style="font-size: 0.8rem; font-weight: 600; color: #374151;">
                                            <?= htmlspecialchars($m['staff_name'] ?? 'System') ?>
                                        </span>
                                    </td>

                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
                    <div style="font-size: 0.82rem; color: #64748B;">
                        Showing page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong>
                        &mdash; <?= number_format($pagination['total_records']) ?> total events
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <?php if ($pagination['has_prev']): ?>
                            <?php
                            $prevF = $filters; $prevF['page'] = ($pagination['current_page'] - 1);
                            $prevF['reason'] = $prevF['reason'] ?: 'all';
                            $prevF['direction'] = $prevF['direction'] ?: 'all';
                            ?>
                            <a href="<?= url('portal/stock/movements?' . http_build_query($prevF)) ?>" style="padding: 7px 16px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 0.84rem; font-weight: 600; color: #374151; text-decoration: none;">&larr; Previous</a>
                        <?php endif; ?>
                        <?php if ($pagination['has_next']): ?>
                            <?php
                            $nextF = $filters; $nextF['page'] = ($pagination['current_page'] + 1);
                            $nextF['reason'] = $nextF['reason'] ?: 'all';
                            $nextF['direction'] = $nextF['direction'] ?: 'all';
                            ?>
                            <a href="<?= url('portal/stock/movements?' . http_build_query($nextF)) ?>" style="padding: 7px 16px; background: #6366F1; border: 1px solid #6366F1; border-radius: 8px; font-size: 0.84rem; font-weight: 700; color: #FFFFFF; text-decoration: none;">Next &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
