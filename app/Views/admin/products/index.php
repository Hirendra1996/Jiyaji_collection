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
                        <span>Catalog</span>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Products & SKUs</span>
                    </div>
                    <h1 class="welcome-title">Products & Inventory Catalog</h1>
                    <p class="welcome-subtitle">Luxury garments, multi-variant generator, individual SKUs, stock thresholds, and gallery media.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/products/create') ?>" class="btn-primary-gradient" style="height: 42px; padding: 0 18px; display: inline-flex; align-items: center; gap: 8px; width: auto;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>+ Add New Product</span>
                    </a>
                </div>
            </div>

            <!-- Products KPI Summary Bar -->
            <div class="catalog-kpi-grid">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Catalog</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Active & Published</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;"><?= number_format($kpis['active']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Draft / In Review</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;"><?= number_format($kpis['draft']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-orange);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-orange); text-transform: uppercase;">Low Stock (&lt; 10)</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($kpis['low_stock']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid #ef4444;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #ef4444; text-transform: uppercase;">Out of Stock (0)</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #ef4444; margin-top: 4px;"><?= number_format($kpis['out_of_stock']) ?></div>
                </div>
            </div>

            <!-- Status Navigation Tabs -->
            <?php $currentStatus = $filters['status'] ?? 'all'; ?>
            <div class="catalog-tabs-bar">
                <?php
                $statusTabs = [
                    'all'          => 'All Products (' . $kpis['total'] . ')',
                    'active'       => 'Active (' . $kpis['active'] . ')',
                    'draft'        => 'Drafts (' . $kpis['draft'] . ')',
                    'low_stock'    => 'Low Stock Alert (' . $kpis['low_stock'] . ')',
                    'out_of_stock' => 'Out of Stock (' . $kpis['out_of_stock'] . ')',
                    'archived'     => 'Archived'
                ];
                ?>
                <?php foreach ($statusTabs as $stKey => $stLabel): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $stKey;
                    $tabParams['page'] = 1;
                    $tabUrl = url('admin/products?' . http_build_query($tabParams));
                    $isActive = ($currentStatus === $stKey);
                    ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($stLabel) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 18px 22px;">
                <form action="<?= url('admin/products') ?>" method="GET" class="catalog-filter-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            placeholder="Search by Title, SKU code, or slug..." 
                            class="form-input" 
                            style="height: 42px; padding: 0 14px; font-size: 0.88rem;"
                        >
                    </div>

                    <div style="flex: 1; min-width: 170px;">
                        <select name="category_id" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem;">
                            <option value="all">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="sort" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem;">
                            <option value="newest" <?= ($filters['sort'] === 'newest') ? 'selected' : '' ?>>Sort: Newest</option>
                            <option value="price_asc" <?= ($filters['sort'] === 'price_asc') ? 'selected' : '' ?>>Price: Low &rarr; High</option>
                            <option value="price_desc" <?= ($filters['sort'] === 'price_desc') ? 'selected' : '' ?>>Price: High &rarr; Low</option>
                            <option value="stock_low" <?= ($filters['sort'] === 'stock_low') ? 'selected' : '' ?>>Stock: Critical First</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary-gradient" style="height: 42px; width: auto; padding: 0 20px; font-size: 0.85rem;">
                        Filter Catalog
                    </button>

                    <?php if (!empty($filters['search']) || $filters['category_id'] !== 'all' || $filters['status'] !== 'all' || $filters['sort'] !== 'newest'): ?>
                        <a href="<?= url('admin/products') ?>" style="font-size: 0.82rem; color: var(--brand-orange); font-weight: 600;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Products Catalog Ledger Table Card -->
            <div class="card-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Product Ledger & Stock Control</h2>
                        <div class="panel-subtitle">Showing <?= count($products) ?> of <?= $pagination['total_records'] ?> products</div>
                    </div>
                </div>

                <?php if (!empty($products)): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th style="width: 320px;">Product & Classification</th>
                                    <th>Primary SKU & Variants</th>
                                    <th>Pricing (INR)</th>
                                    <th>Inventory & Health</th>
                                    <th>Catalog Status</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <?php
                                    $hasVariants = !empty($p['variants']);
                                    $totalStock  = (int)($p['total_stock'] ?? 0);
                                    $lowThresh   = (int)($p['low_stock_threshold'] ?? 10);
                                    
                                    // Stock badge styling
                                    if ($totalStock <= 0) {
                                        $stockClass = 'badge-cancelled';
                                        $stockLabel = 'Out of Stock (0)';
                                    } elseif ($totalStock < $lowThresh) {
                                        $stockClass = 'badge-pending';
                                        $stockLabel = 'Low Stock (' . $totalStock . ')';
                                    } else {
                                        $stockClass = 'badge-delivered';
                                        $stockLabel = 'In Stock (' . $totalStock . ')';
                                    }

                                    // Status badge styling
                                    $statusBadge = match($p['status']) {
                                        'active'   => 'badge-delivered',
                                        'draft'    => 'badge-shipped',
                                        'archived' => 'badge-cancelled',
                                        default    => 'badge-pending'
                                    };

                                    // Safe JSON payload for stock adjust modal
                                    $variantsJson = htmlspecialchars(json_encode($p['variants'] ?? []), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <!-- Product info & thumbnail -->
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 14px;">
                                                <div style="width: 52px; height: 52px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                                                    <?php if (!empty($p['primary_image'])): ?>
                                                        <img src="<?= htmlspecialchars(image_url($p['primary_image'])) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                                    <?php else: ?>
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
                                                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                            <polyline points="21 15 16 10 5 21"></polyline>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="overflow: hidden;">
                                                    <a href="<?= url('admin/products/' . $p['encrypted_id'] . '/edit') ?>" style="font-weight: 700; color: var(--text-primary); font-size: 0.9rem; text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 230px;" title="<?= htmlspecialchars($p['name']) ?>">
                                                        <?= htmlspecialchars($p['name']) ?>
                                                    </a>
                                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                                                        <span style="font-size: 0.72rem; font-weight: 600; color: var(--brand-purple); background: rgba(140, 48, 245, 0.08); padding: 2px 8px; border-radius: 4px;">
                                                            <?= htmlspecialchars($p['category_name'] ?? 'Luxury Apparel') ?>
                                                        </span>
                                                        <span style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;">
                                                            /<?= htmlspecialchars($p['slug']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- SKU and Variants with Color Swatches -->
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-family: monospace; font-size: 0.85rem; font-weight: 700; color: var(--brand-blue); background: var(--brand-blue-light); padding: 3px 8px; border-radius: 4px;">
                                                    <?= htmlspecialchars($p['primary_sku'] ?: 'NO-SKU') ?>
                                                </span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 5px; margin-top: 5px; flex-wrap: wrap;">
                                                <?php
                                                $varList = $p['variants'] ?? [];
                                                $swatchCount = 0;
                                                foreach ($varList as $pv):
                                                    if (!empty($pv['color_code']) && $swatchCount < 4):
                                                        $swatchCount++;
                                                ?>
                                                        <span style="width: 13px; height: 13px; border-radius: 50%; background: <?= htmlspecialchars($pv['color_code']) ?>; display: inline-block; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.15);" title="<?= htmlspecialchars(($pv['color_name'] ?? '') . ($pv['size'] ? ' (' . $pv['size'] . ')' : '')) ?>"></span>
                                                <?php
                                                    endif;
                                                endforeach;
                                                ?>
                                                <span style="font-size: 0.73rem; color: var(--text-muted);">
                                                    <?= count($varList) ?> Variant<?= count($varList) !== 1 ? 's' : '' ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Pricing -->
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.92rem;">
                                                ₹<?= number_format((int)round((float)$p['sale_price'])) ?>
                                            </div>
                                            <?php if ($p['sale_price'] < $p['base_price']): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted); text-decoration: line-through;">
                                                    ₹<?= number_format((int)round((float)$p['base_price'])) ?>
                                                </div>
                                                <span style="font-size: 0.68rem; font-weight: 700; color: var(--status-success); background: rgba(16, 185, 129, 0.1); padding: 1px 5px; border-radius: 3px;">
                                                    <?= round((($p['base_price'] - $p['sale_price']) / $p['base_price']) * 100) ?>% OFF
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Inventory & Health -->
                                        <td>
                                            <span class="badge <?= $stockClass ?>">
                                                <?= $stockLabel ?>
                                            </span>
                                            <?php if (!empty($p['allow_backorder'])): ?>
                                                <div style="font-size: 0.7rem; color: var(--brand-blue); font-weight: 600; margin-top: 3px;">
                                                    &bull; Backorders Allowed
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status with quick toggle -->
                                        <td>
                                            <form action="<?= url('admin/products/' . $p['encrypted_id'] . '/status') ?>" method="POST" style="display: inline-block;">
                                                <?= csrf_field() ?>
                                                <select name="status" onchange="this.form.submit()" class="form-input" style="height: 32px; padding: 0 8px; font-size: 0.78rem; font-weight: 600; border-radius: 6px; cursor: pointer;">
                                                    <option value="active" <?= $p['status'] === 'active' ? 'selected' : '' ?>>🟢 Active</option>
                                                    <option value="draft" <?= $p['status'] === 'draft' ? 'selected' : '' ?>>🟡 Draft</option>
                                                    <option value="archived" <?= $p['status'] === 'archived' ? 'selected' : '' ?>>⚪ Archived</option>
                                                </select>
                                            </form>
                                        </td>

                                        <!-- Actions -->
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                                <!-- Quick Stock Update Trigger -->
                                                <button 
                                                    type="button" 
                                                    class="btn-export" 
                                                    style="height: 32px; padding: 0 10px; font-size: 0.78rem;" 
                                                    title="Quick Adjust Variant Stock"
                                                    onclick="openStockModal('<?= $p['encrypted_id'] ?>', '<?= htmlspecialchars(addslashes($p['name'])) ?>', <?= $variantsJson ?>)"
                                                >
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                    </svg>
                                                    <span>Stock</span>
                                                </button>

                                                <!-- Full Edit Button -->
                                                <a href="<?= url('admin/products/' . $p['encrypted_id'] . '/edit') ?>" class="btn-export" style="height: 32px; padding: 0 10px; font-size: 0.78rem;" title="Edit Product & SKUs">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                    <span>Edit</span>
                                                </a>

                                                <!-- Soft Archive/Delete -->
                                                <form action="<?= url('admin/products/' . $p['encrypted_id'] . '/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Archive product <?= htmlspecialchars(addslashes($p['name'])) ?>?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-export" style="height: 32px; padding: 0 8px; color: #ef4444;" title="Archive Product">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-top: 1px solid var(--border-color); font-size: 0.84rem; color: var(--text-muted); flex-wrap: wrap; gap: 12px;">
                            <div>
                                Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> (<?= $pagination['total_records'] ?> total products)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <?php if (!empty($pagination['has_prev']) || $pagination['current_page'] > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = $pagination['current_page'] - 1;
                                    ?>
                                    <a href="<?= url('admin/products?' . http_build_query($prevParams)) ?>" class="btn-export" style="height: 34px; padding: 0 12px;">
                                        &larr; Previous
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <?php
                                    if ($i !== 1 && $i !== $pagination['total_pages'] && abs($i - $pagination['current_page']) > 2) {
                                        continue;
                                    }
                                    $pageParams = $filters;
                                    $pageParams['page'] = $i;
                                    $isCur = ($i === $pagination['current_page']);
                                    ?>
                                    <a href="<?= url('admin/products?' . http_build_query($pageParams)) ?>" class="<?= $isCur ? 'btn-primary-gradient' : 'btn-export' ?>" style="height: 34px; width: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 6px;">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if (!empty($pagination['has_next']) || $pagination['current_page'] < $pagination['total_pages']): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = $pagination['current_page'] + 1;
                                    ?>
                                    <a href="<?= url('admin/products?' . http_build_query($nextParams)) ?>" class="btn-export" style="height: 34px; padding: 0 12px;">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom: 16px;">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">No Products Found</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); max-width: 400px; margin: 0 auto 20px;">
                            No catalog items match your search or filter criteria. Try adjusting filters or create a new luxury garment.
                        </p>
                        <a href="<?= url('admin/products/create') ?>" class="btn-primary-gradient" style="height: 40px; width: auto; padding: 0 20px; display: inline-flex; align-items: center; gap: 8px;">
                            <span>+ Create New Product</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick In-Line Stock Adjustment Modal -->
            <div id="stockAdjustModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
                <div style="background: var(--bg-card); border-radius: 14px; max-width: 540px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); overflow: hidden; animation: modalPop 0.2s ease-out;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin: 0;">Quick Inventory Adjust</h3>
                            <p id="modalProductName" style="font-size: 0.8rem; color: var(--text-muted); margin: 4px 0 0;"></p>
                        </div>
                        <button type="button" onclick="closeStockModal()" style="background: none; border: none; font-size: 1.3rem; color: var(--text-muted); cursor: pointer;">&times;</button>
                    </div>
                    <div style="padding: 24px; max-height: 60vh; overflow-y: auto;" id="modalVariantsList">
                        <!-- Populated by JavaScript -->
                    </div>
                    <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                        <button type="button" onclick="closeStockModal()" class="btn-export" style="height: 38px; padding: 0 18px;">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <script>
            function openStockModal(encryptedId, productName, variants) {
                document.getElementById('modalProductName').textContent = productName;
                const container = document.getElementById('modalVariantsList');
                container.innerHTML = '';

                if (!variants || variants.length === 0) {
                    container.innerHTML = '<p style="font-size:0.85rem; color:var(--text-muted); text-align:center;">No variants configured for this product.</p>';
                } else {
                    variants.forEach(function(v) {
                        const row = document.createElement('div');
                        row.style.cssText = 'background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; gap: 12px;';
                        
                        const actionUrl = '<?= url("admin/products/") ?>' + encryptedId + '/stock';
                        
                        row.innerHTML = `
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary);">${v.variant_name || 'Standard'}</div>
                                <div style="font-size: 0.75rem; font-family: monospace; color: var(--brand-blue); margin-top: 2px;">SKU: ${v.sku}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Current: <strong>${v.stock_qty}</strong> in stock</div>
                            </div>
                            <form action="${actionUrl}" method="POST" style="display: flex; align-items: center; gap: 8px;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="variant_id" value="${v.id}">
                                <input type="number" name="new_qty" value="${v.stock_qty}" min="0" max="9999" class="form-input" style="width: 80px; height: 36px; padding: 0 8px; font-weight: 700; text-align: center;">
                                <button type="submit" class="btn-primary-gradient" style="height: 36px; width: auto; padding: 0 12px; font-size: 0.78rem;">
                                    Update
                                </button>
                            </form>
                        `;
                        container.appendChild(row);
                    });
                }

                const modal = document.getElementById('stockAdjustModal');
                modal.style.display = 'flex';
            }

            function closeStockModal() {
                document.getElementById('stockAdjustModal').style.display = 'none';
            }

            // Close on backdrop click
            document.getElementById('stockAdjustModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeStockModal();
                }
            });
            </script>

            <style>
            @keyframes modalPop {
                from { opacity: 0; transform: scale(0.96); }
                to { opacity: 1; transform: scale(1); }
            }
            </style>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
