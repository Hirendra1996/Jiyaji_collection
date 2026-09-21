<?php
$title    = $title ?? 'Stock & Replenishment | Jiyaji LX Staff Portal';
$kpis     = $kpis ?? [];
$variants = $variants ?? [];
$filters  = $filters ?? [];
$pagination = $pagination ?? ['has_prev' => false, 'has_next' => false, 'current_page' => 1, 'total_pages' => 1, 'total_records' => 0];
$categories = $categories ?? [];
$canManage  = $canManage ?? false;

include __DIR__ . '/../layouts/header.php';
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
                <span style="color: #0F172A; font-weight: 600;">Stock &amp; Replenishment</span>
            </div>

            <!-- Executive Hero Banner -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #064E3B 55%, #10B981 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.25);">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #A7F3D0;">
                            Inventory Control &bull; Live Stock Monitoring
                        </span>
                        <?php if ($canManage): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: rgba(16, 185, 129, 0.25); color: #A7F3D0; padding: 2px 8px; border-radius: 6px;">Replenishment Authority</span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: rgba(255,255,255,0.12); color: #D1FAE5; padding: 2px 8px; border-radius: 6px;">Read-Only View</span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Stock &amp; Replenishment Hub
                    </h1>
                    <p style="font-size: 0.88rem; color: #A7F3D0; margin: 0;">
                        Real-time inventory health monitoring, low-stock alerts, instant variant restocking, and immutable audit trail.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="<?= url('portal/stock/movements') ?>" style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Audit Log
                    </a>
                    <?php if ($canManage): ?>
                        <button type="button" onclick="openBulkModal()" style="background: rgba(16, 185, 129, 0.3); border: 1px solid rgba(16, 185, 129, 0.5); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(16,185,129,0.45)';" onmouseout="this.style.background='rgba(16,185,129,0.3)';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                            Bulk Restock
                        </button>
                    <?php endif; ?>
                    <a href="<?= url('portal/stock/export?' . http_build_query(array_filter($filters))) ?>" style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- 5 KPI Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">Total Units On Hand</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px;"><?= number_format($kpis['total_units'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Active variant stock combined</div>
                </div>
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #10B981;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Inventory Valuation</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #10B981; margin-top: 6px;">₹<?= number_format($kpis['valuation'] ?? 0, 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Based on effective selling price</div>
                </div>
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #DC2626;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #DC2626; text-transform: uppercase; letter-spacing: 0.05em;">Critical / Out of Stock</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #DC2626; margin-top: 6px;"><?= number_format($kpis['critical_count'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Variants with zero units</div>
                </div>
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #F59E0B;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Low Stock Warnings</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #F59E0B; margin-top: 6px;"><?= number_format($kpis['low_stock_count'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Approaching reorder threshold</div>
                </div>
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #6366F1;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #4338CA; text-transform: uppercase; letter-spacing: 0.05em;">Restocked (30d)</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #6366F1; margin-top: 6px;">+<?= number_format($kpis['restocked_30d'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Units added this month</div>
                </div>
            </div>

            <!-- Status Tabs -->
            <?php
            $currentStatus = $filters['status'] ?? 'all';
            $tabs = [
                'all'          => ['label' => 'All SKUs', 'count' => $pagination['total_records'] ?? 0],
                'out_of_stock' => ['label' => 'Critical', 'count' => $kpis['critical_count'] ?? 0],
                'low_stock'    => ['label' => 'Low Stock Alert', 'count' => $kpis['low_stock_count'] ?? 0],
                'healthy'      => ['label' => 'Healthy Stock', 'count' => ''],
            ];
            $tabColors = ['all' => '#10B981', 'out_of_stock' => '#DC2626', 'low_stock' => '#F59E0B', 'healthy' => '#3B82F6'];
            ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
                <?php foreach ($tabs as $tKey => $tData): ?>
                    <?php
                    $isActiveTab = ($currentStatus === $tKey);
                    $tColor = $tabColors[$tKey] ?? '#64748B';
                    $tabFilters = $filters;
                    $tabFilters['status'] = $tKey;
                    $tabFilters['page'] = 1;
                    $tabUrl = url('portal/stock?' . http_build_query(array_filter($tabFilters, fn($v) => $v !== '' && $v !== 'all' || $tKey === 'all')));
                    $tabUrl = url('portal/stock?' . http_build_query($tabFilters));
                    ?>
                    <a href="<?= $tabUrl ?>" style="padding: 8px 16px; font-size: 0.84rem; font-weight: <?= $isActiveTab ? '700' : '600' ?>; border-radius: 8px; text-decoration: none; transition: all 0.15s; background: <?= $isActiveTab ? $tColor : '#F8FAFC' ?>; color: <?= $isActiveTab ? '#FFFFFF' : '#475569' ?>; border: 1px solid <?= $isActiveTab ? $tColor : '#E2E8F0' ?>;">
                        <?= htmlspecialchars($tData['label']) ?><?= $tData['count'] !== '' ? ' (' . number_format((int)$tData['count']) . ')' : '' ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/stock') ?>" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? 'all') ?>">
                    <div style="flex: 2; min-width: 240px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                            placeholder="Search by SKU, Product, Color, Size..."
                            style="width: 100%; height: 40px; padding: 0 14px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                            onfocus="this.style.borderColor='#10B981';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>
                    <div style="flex: 1; min-width: 180px;">
                        <select name="category_id" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['category_id'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (($filters['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 180px;">
                        <select name="sort" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="stock_asc"  <?= (($filters['sort'] ?? '') === 'stock_asc') ? 'selected' : '' ?>>Stock: Lowest First</option>
                            <option value="stock_desc" <?= (($filters['sort'] ?? '') === 'stock_desc') ? 'selected' : '' ?>>Stock: Highest First</option>
                            <option value="name_asc"   <?= (($filters['sort'] ?? '') === 'name_asc') ? 'selected' : '' ?>>Product: A → Z</option>
                            <option value="sku_asc"    <?= (($filters['sort'] ?? '') === 'sku_asc') ? 'selected' : '' ?>>SKU: A → Z</option>
                            <option value="newest"     <?= (($filters['sort'] ?? '') === 'newest') ? 'selected' : '' ?>>Newest Variants</option>
                        </select>
                    </div>
                    <button type="submit" style="height: 40px; padding: 0 20px; background: #10B981; color: #FFFFFF; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Apply</button>
                    <a href="<?= url('portal/stock') ?>" style="height: 40px; padding: 0 16px; background: #F1F5F9; color: #475569; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center;">Clear</a>
                </form>
            </div>

            <!-- Inventory Ledger Table -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 24px;">
                <div style="padding: 16px 22px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 1rem; font-weight: 800; color: #0F172A;">Inventory Ledger</div>
                    <div style="font-size: 0.8rem; color: #64748B;">
                        <?= number_format($pagination['total_records'] ?? 0) ?> total variant SKUs
                        &mdash; Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?>
                    </div>
                </div>

                <?php if (empty($variants)): ?>
                    <div style="padding: 48px 24px; text-align: center;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #F8FAFC; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <div style="font-weight: 700; color: #475569; margin-bottom: 4px;">No inventory records found</div>
                        <div style="font-size: 0.8rem; color: #94A3B8;">Adjust your filters or add product variants to the catalog.</div>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                                    <th style="text-align: left; padding: 12px 20px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Garment &amp; SKU</th>
                                    <th style="text-align: left; padding: 12px 16px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Variant</th>
                                    <th style="text-align: left; padding: 12px 16px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Stock Health</th>
                                    <th style="text-align: center; padding: 12px 16px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Units</th>
                                    <th style="text-align: center; padding: 12px 16px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Threshold</th>
                                    <th style="text-align: center; padding: 12px 16px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Restock Sugg.</th>
                                    <?php if ($canManage): ?>
                                        <th style="text-align: center; padding: 12px 16px; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variants as $v):
                                    $stockQty  = (int)($v['stock_qty'] ?? 0);
                                    $threshold = (int)($v['low_stock_threshold'] ?? 10);
                                    $maxScale  = max($threshold * 3, 1);

                                    $isCritical = $stockQty === 0;
                                    $isLow      = !$isCritical && $stockQty <= $threshold;
                                    $isHealthy  = $stockQty > $threshold;

                                    $barWidth  = min(100, (int)round($stockQty / $maxScale * 100));
                                    $barColor  = $isCritical ? '#EF4444' : ($isLow ? '#F59E0B' : '#10B981');
                                    $badgeBg   = $isCritical ? '#FEE2E2' : ($isLow ? '#FEF3C7' : '#DCFCE7');
                                    $badgeText = $isCritical ? '#DC2626' : ($isLow ? '#D97706' : '#16A34A');
                                    $badgeLabel = $isCritical ? 'Critical' : ($isLow ? 'Low Stock' : 'Healthy');
                                    $suggested  = max(0, $threshold * 3 - $stockQty);
                                    $encVariant = $v['encrypted_variant_id'] ?? '';
                                    $encProduct = $v['encrypted_product_id'] ?? '';
                                    $effectivePrice = $v['price_override'] ?? $v['sale_price'] ?? $v['base_price'] ?? 0;
                                    $alertActive = !empty($v['alert_id']) && !(int)($v['alert_resolved'] ?? 1);
                                ?>
                                <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">

                                    <!-- Garment & SKU -->
                                    <td style="padding: 14px 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <!-- Thumbnail -->
                                            <div style="width: 42px; height: 42px; border-radius: 8px; overflow: hidden; background: #F1F5F9; flex-shrink: 0; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center;">
                                                <?php if (!empty($v['cover_image'])): ?>
                                                    <img src="<?= htmlspecialchars(image_url($v['cover_image'])) ?>" alt="<?= htmlspecialchars($v['product_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <a href="<?= url('portal/products/' . $encProduct) ?>" style="font-weight: 700; font-size: 0.88rem; color: #0F172A; text-decoration: none; max-width: 220px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" onmouseover="this.style.color='#10B981';" onmouseout="this.style.color='#0F172A';">
                                                    <?= htmlspecialchars($v['product_name'] ?? '') ?>
                                                </a>
                                                <div style="font-family: monospace; font-size: 0.76rem; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($v['sku'] ?? '') ?></div>
                                                <?php if (!empty($v['category_name'])): ?>
                                                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 1px;"><?= htmlspecialchars($v['category_name']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Variant details -->
                                    <td style="padding: 14px 16px;">
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <?php if (!empty($v['color_name'])): ?>
                                                <div style="display: flex; align-items: center; gap: 5px;">
                                                    <?php if (!empty($v['color_code'])): ?>
                                                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: <?= htmlspecialchars($v['color_code']) ?>; border: 1px solid rgba(0,0,0,0.1);"></span>
                                                    <?php endif; ?>
                                                    <span style="font-size: 0.8rem; color: #374151; font-weight: 600;"><?= htmlspecialchars($v['color_name']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($v['size'])): ?>
                                                <span style="background: #EEF2FF; color: #4338CA; padding: 1px 7px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; display: inline-block; width: fit-content;">Size: <?= htmlspecialchars($v['size']) ?></span>
                                            <?php endif; ?>
                                            <div style="font-size: 0.78rem; color: #0F172A; font-weight: 700;">₹<?= number_format((float)$effectivePrice, 0) ?></div>
                                        </div>
                                    </td>

                                    <!-- Stock Health -->
                                    <td style="padding: 14px 16px; min-width: 160px;">
                                        <div style="display: flex; flex-direction: column; gap: 6px;">
                                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                                <span style="background: <?= $badgeBg ?>; color: <?= $badgeText ?>; padding: 2px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">
                                                    <?php if ($alertActive): ?><span style="margin-right: 3px;">⚠</span><?php endif; ?>
                                                    <?= $badgeLabel ?>
                                                </span>
                                                <?php if (!empty($v['allow_backorder'])): ?>
                                                    <span style="font-size: 0.68rem; color: #6366F1; background: #EEF2FF; padding: 1px 5px; border-radius: 4px; font-weight: 700;">Backorder</span>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Stock bar -->
                                            <div style="width: 100%; height: 6px; background: #F1F5F9; border-radius: 999px; overflow: hidden;">
                                                <div style="width: <?= $barWidth ?>%; height: 100%; background: <?= $barColor ?>; border-radius: 999px; transition: width 0.4s;"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Current Stock Units -->
                                    <td style="padding: 14px 16px; text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 800; color: <?= $isCritical ? '#DC2626' : ($isLow ? '#D97706' : '#0F172A') ?>;">
                                            <?= number_format($stockQty) ?>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #94A3B8;">units</div>
                                    </td>

                                    <!-- Reorder Threshold -->
                                    <td style="padding: 14px 16px; text-align: center;">
                                        <div style="font-size: 0.9rem; font-weight: 700; color: #374151;"><?= number_format($threshold) ?></div>
                                        <div style="font-size: 0.7rem; color: #94A3B8;">min. level</div>
                                    </td>

                                    <!-- Suggested Restock -->
                                    <td style="padding: 14px 16px; text-align: center;">
                                        <?php if ($suggested > 0): ?>
                                            <div style="font-size: 0.9rem; font-weight: 800; color: #6366F1;">+<?= number_format($suggested) ?></div>
                                            <div style="font-size: 0.7rem; color: #94A3B8;">suggested</div>
                                        <?php else: ?>
                                            <div style="font-size: 0.8rem; color: #94A3B8;">&mdash;</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions -->
                                    <?php if ($canManage): ?>
                                        <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                                            <button type="button"
                                                onclick="openRestockModal('<?= htmlspecialchars($encVariant) ?>', '<?= htmlspecialchars($v['sku'] ?? '') ?>', '<?= htmlspecialchars($v['product_name'] ?? '') ?>', <?= $stockQty ?>, <?= $threshold ?>, <?= $suggested ?>)"
                                                style="background: #ECFDF5; color: #059669; border: 1px solid #6EE7B7; border-radius: 7px; padding: 5px 12px; font-size: 0.78rem; font-weight: 700; cursor: pointer; white-space: nowrap;"
                                                onmouseover="this.style.background='#D1FAE5';" onmouseout="this.style.background='#ECFDF5';">
                                                + Restock
                                            </button>
                                        </td>
                                    <?php endif; ?>

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
                        &mdash; <?= number_format($pagination['total_records']) ?> total SKUs
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <?php if ($pagination['has_prev']): ?>
                            <?php $prevParams = $filters; $prevParams['page'] = ($pagination['current_page'] - 1); ?>
                            <a href="<?= url('portal/stock?' . http_build_query($prevParams)) ?>" style="padding: 7px 16px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 0.84rem; font-weight: 600; color: #374151; text-decoration: none;">&larr; Previous</a>
                        <?php endif; ?>
                        <?php if ($pagination['has_next']): ?>
                            <?php $nextParams = $filters; $nextParams['page'] = ($pagination['current_page'] + 1); ?>
                            <a href="<?= url('portal/stock?' . http_build_query($nextParams)) ?>" style="padding: 7px 16px; background: #10B981; border: 1px solid #10B981; border-radius: 8px; font-size: 0.84rem; font-weight: 700; color: #FFFFFF; text-decoration: none;">Next &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<!-- Quick Restock Modal -->
<div id="restockModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.55); z-index: 9000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 460px; box-shadow: 0 25px 60px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #064E3B 0%, #10B981 100%); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #A7F3D0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">Stock Movement</div>
                <div style="font-size: 1.1rem; font-weight: 800; color: #FFFFFF;" id="modalTitle">Restock Variant</div>
                <div style="font-size: 0.8rem; color: #D1FAE5;" id="modalSku"></div>
            </div>
            <button onclick="closeRestockModal()" style="background: rgba(255,255,255,0.2); border: none; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <form action="<?= url('portal/stock/replenish') ?>" method="POST" style="padding: 24px;">
            <?= csrf_field() ?>
            <input type="hidden" name="variant_id" id="modalVariantId">

            <!-- Stock status summary -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.84rem; margin-bottom: 8px;">
                    <span style="color: #64748B; font-weight: 600;">Current Stock</span>
                    <span style="font-weight: 800; color: #0F172A;" id="modalCurrent">—</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.84rem; margin-bottom: 8px;">
                    <span style="color: #64748B; font-weight: 600;">Reorder Threshold</span>
                    <span style="font-weight: 700; color: #D97706;" id="modalThreshold">—</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.84rem;">
                    <span style="color: #64748B; font-weight: 600;">Suggested Qty</span>
                    <span style="font-weight: 800; color: #6366F1;" id="modalSuggested">—</span>
                </div>
            </div>

            <!-- Delta quantity input -->
            <div style="margin-bottom: 16px;">
                <label for="modalQty" style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                    Quantity Delta <span style="color: #EF4444;">*</span>
                    <span style="font-weight: 400; color: #64748B;">(positive = add stock, negative = deduct)</span>
                </label>
                <input type="number" name="qty" id="modalQty" required step="1"
                    style="width: 100%; height: 44px; padding: 0 14px; font-size: 1rem; font-weight: 700; border: 2px solid #CBD5E1; border-radius: 8px; outline: none; text-align: center;"
                    onfocus="this.style.borderColor='#10B981';" onblur="this.style.borderColor='#CBD5E1';">
            </div>

            <!-- Reason -->
            <div style="margin-bottom: 16px;">
                <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">Movement Reason</label>
                <select name="reason" style="width: 100%; height: 42px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                    <option value="manual_restock">Manual Restock (PO Received)</option>
                    <option value="adjustment">Inventory Adjustment (Cycle Count)</option>
                    <option value="return">Return / RMA Item Restored</option>
                    <option value="cancellation">Order Cancellation Reversal</option>
                </select>
            </div>

            <!-- Notes -->
            <div style="margin-bottom: 22px;">
                <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">Note / PO Reference <span style="font-weight: 400; color: #94A3B8;">(optional)</span></label>
                <input type="text" name="note" placeholder="e.g. PO-2024-101, Vendor Shipment #SR-5912..."
                    style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                    onfocus="this.style.borderColor='#10B981';" onblur="this.style.borderColor='#CBD5E1';">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 1; height: 44px; background: linear-gradient(135deg, #059669 0%, #10B981 100%); color: #FFFFFF; border: none; border-radius: 10px; font-size: 0.92rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(16,185,129,0.35);">
                    Record Movement
                </button>
                <button type="button" onclick="closeRestockModal()" style="height: 44px; padding: 0 20px; background: #F1F5F9; color: #475569; border: none; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Replenish Modal -->
<div id="bulkModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.55); z-index: 9000; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 640px; box-shadow: 0 25px 60px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #1E3A5F 0%, #3B82F6 100%); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #BFDBFE; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">Inventory Management</div>
                <div style="font-size: 1.1rem; font-weight: 800; color: #FFFFFF;">Bulk Replenishment Order</div>
                <div style="font-size: 0.8rem; color: #BFDBFE;">Restock multiple SKUs in a single transaction</div>
            </div>
            <button onclick="closeBulkModal()" style="background: rgba(255,255,255,0.2); border: none; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        <form action="<?= url('portal/stock/batch-replenish') ?>" method="POST" style="padding: 24px;">
            <?= csrf_field() ?>

            <!-- Low-stock candidates table -->
            <?php
            $candidates = App\Models\Inventory::getLowStockCandidates();
            ?>
            <?php if (!empty($candidates)): ?>
                <div style="font-size: 0.82rem; font-weight: 700; color: #374151; margin-bottom: 12px;">
                    <?= count($candidates) ?> SKUs at or below low-stock threshold — enter restock quantities:
                </div>
                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 18px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.84rem;">
                        <thead style="position: sticky; top: 0; background: #F8FAFC; z-index: 1;">
                            <tr>
                                <th style="text-align: left; padding: 10px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">SKU / Product</th>
                                <th style="text-align: center; padding: 10px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Current</th>
                                <th style="text-align: center; padding: 10px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Restock Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $c): ?>
                                <tr style="border-top: 1px solid #F1F5F9;">
                                    <td style="padding: 10px 14px;">
                                        <input type="hidden" name="variant_ids[]" value="<?= htmlspecialchars($c['encrypted_variant_id']) ?>">
                                        <div style="font-weight: 700; color: #0F172A; font-size: 0.82rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($c['product_name']) ?></div>
                                        <div style="font-family: monospace; font-size: 0.73rem; color: #64748B;"><?= htmlspecialchars($c['sku']) ?></div>
                                        <?php if (!empty($c['size'])): ?>
                                            <span style="font-size: 0.68rem; background: #EEF2FF; color: #4338CA; padding: 1px 5px; border-radius: 3px; font-weight: 700;">Size <?= htmlspecialchars($c['size']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px 14px; text-align: center;">
                                        <span style="font-weight: 800; color: <?= (int)$c['stock_qty'] === 0 ? '#DC2626' : '#D97706' ?>;"><?= (int)$c['stock_qty'] ?></span>
                                    </td>
                                    <td style="padding: 10px 14px; text-align: center;">
                                        <input type="number"
                                            name="quantities[<?= htmlspecialchars($c['encrypted_variant_id']) ?>]"
                                            value="<?= max(0, (int)$c['suggested_restock']) ?>"
                                            min="0" step="1"
                                            style="width: 80px; height: 34px; text-align: center; font-size: 0.88rem; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 6px; outline: none;"
                                            onfocus="this.style.borderColor='#3B82F6';" onblur="this.style.borderColor='#CBD5E1';">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 24px; color: #64748B;">
                    <div style="font-size: 1.5rem; margin-bottom: 8px;">✅</div>
                    <div style="font-weight: 700;">All SKUs are fully stocked!</div>
                    <div style="font-size: 0.8rem; margin-top: 4px;">No variants are below their low-stock threshold.</div>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">Reason</label>
                    <select name="reason" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.86rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                        <option value="manual_restock">Manual Restock</option>
                        <option value="adjustment">Inventory Adjustment</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">PO / Batch Reference <span style="font-weight: 400; color: #94A3B8;">(optional)</span></label>
                    <input type="text" name="note" placeholder="e.g. PO-2024-101"
                        style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.86rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                        onfocus="this.style.borderColor='#3B82F6';" onblur="this.style.borderColor='#CBD5E1';">
                </div>
            </div>

            <?php if (!empty($candidates)): ?>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="flex: 1; height: 44px; background: linear-gradient(135deg, #1D4ED8 0%, #3B82F6 100%); color: #FFFFFF; border: none; border-radius: 10px; font-size: 0.92rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(59,130,246,0.35);">
                        Execute Batch Replenishment
                    </button>
                    <button type="button" onclick="closeBulkModal()" style="height: 44px; padding: 0 20px; background: #F1F5F9; color: #475569; border: none; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Cancel</button>
                </div>
            <?php else: ?>
                <div style="text-align: center;">
                    <button type="button" onclick="closeBulkModal()" style="height: 44px; padding: 0 32px; background: #F1F5F9; color: #475569; border: none; border-radius: 10px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Close</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function openRestockModal(encVariantId, sku, productName, currentStock, threshold, suggested) {
    document.getElementById('modalVariantId').value = encVariantId;
    document.getElementById('modalTitle').textContent = 'Restock: ' + productName;
    document.getElementById('modalSku').textContent = 'SKU: ' + sku;
    document.getElementById('modalCurrent').textContent = currentStock + ' units';
    document.getElementById('modalThreshold').textContent = threshold + ' units';
    document.getElementById('modalSuggested').textContent = '+' + suggested + ' units';
    document.getElementById('modalQty').value = suggested > 0 ? suggested : '';
    const modal = document.getElementById('restockModal');
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('modalQty').focus(), 100);
}

function closeRestockModal() {
    document.getElementById('restockModal').style.display = 'none';
}

function openBulkModal() {
    document.getElementById('bulkModal').style.display = 'flex';
}

function closeBulkModal() {
    document.getElementById('bulkModal').style.display = 'none';
}

// Close modals on outside click
document.getElementById('restockModal').addEventListener('click', function(e) {
    if (e.target === this) closeRestockModal();
});
document.getElementById('bulkModal').addEventListener('click', function(e) {
    if (e.target === this) closeBulkModal();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRestockModal();
        closeBulkModal();
    }
});
</script>
