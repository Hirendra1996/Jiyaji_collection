<?php
$title = 'Products & SKUs Catalog | Jiyaji LX Staff Portal';
$filters = $filters ?? [
    'status'      => $_GET['status'] ?? 'all',
    'category_id' => $_GET['category_id'] ?? 'all',
    'search'      => trim($_GET['search'] ?? ''),
    'sort'        => $_GET['sort'] ?? 'newest'
];
$canCreate  = $canCreate ?? (function_exists('staff_can') ? staff_can('products', 'create') : false);
$canEdit    = $canEdit ?? (function_exists('staff_can') ? staff_can('products', 'edit') : false);
$canDelete  = $canDelete ?? (function_exists('staff_can') ? staff_can('products', 'delete') : false);
$products   = $products ?? [];
$pagination = $pagination ?? [];
$kpis       = $kpis ?? ['total' => 0, 'active' => 0, 'draft' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
$categories = $categories ?? [];

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Header Banner -->
            <div class="welcome-banner" style="background: linear-gradient(135deg, #0F172A 0%, #312E81 60%, #4338CA 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(67, 56, 202, 0.3);">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 999px; color: #C7D2FE;">
                            Inventory &bull; Merchandising Ledger
                        </span>
                        <?php if ($canCreate): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #EEF2FF; color: #4338CA; padding: 2px 8px; border-radius: 6px;">
                                Write &bull; Full Catalog Clearance
                            </span>
                        <?php elseif ($canEdit): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #ECFDF5; color: #065F46; padding: 2px 8px; border-radius: 6px;">
                                Stock &bull; Inventory Adjust Clearance
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 6px;">
                                Read-Only Clearance
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Products &amp; SKUs Catalog
                    </h1>
                    <p style="font-size: 0.88rem; color: #E0E7FF; margin: 0;">
                        Luxury garments, multi-variant matrices, individual SKUs, stock thresholds, and gallery media.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- CSV Export Button -->
                    <?php
                    $exportParams = $filters;
                    $exportUrl = url('portal/products/export?' . http_build_query($exportParams));
                    ?>
                    <a href="<?= $exportUrl ?>" class="btn-export" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 15px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export CSV</span>
                    </a>

                    <!-- Add New Product Button -->
                    <?php if ($canCreate): ?>
                        <a href="<?= url('portal/products/create') ?>" style="background: #4F46E5; border: 1px solid rgba(255,255,255,0.3); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>+ Add New Garment</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Products KPI Summary Bar -->
            <div class="catalog-kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">Total Catalog</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px;"><?= number_format($kpis['total'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Unique garment master records</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #10B981;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Active &amp; Published</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #10B981; margin-top: 6px;"><?= number_format($kpis['active'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Live on customer storefront</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #6366F1;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #4F46E5; text-transform: uppercase; letter-spacing: 0.05em;">Draft / In Review</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #4F46E5; margin-top: 6px;"><?= number_format($kpis['draft'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Unpublished or upcoming</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #F59E0B;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Low Stock (&le; 10)</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #F59E0B; margin-top: 6px;"><?= number_format($kpis['low_stock'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Inventory replenishment needed</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #EF4444;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #DC2626; text-transform: uppercase; letter-spacing: 0.05em;">Out of Stock (0)</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #EF4444; margin-top: 6px;"><?= number_format($kpis['out_of_stock'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Unavailable for immediate purchase</div>
                </div>
            </div>

            <!-- Status Navigation Tabs -->
            <?php $currentStatus = $filters['status'] ?? 'all'; ?>
            <div class="catalog-tabs-bar" style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
                <?php
                $statusTabs = [
                    'all'          => 'All Products (' . ($kpis['total'] ?? 0) . ')',
                    'active'       => 'Active (' . ($kpis['active'] ?? 0) . ')',
                    'draft'        => 'Drafts (' . ($kpis['draft'] ?? 0) . ')',
                    'low_stock'    => 'Low Stock Alert (' . ($kpis['low_stock'] ?? 0) . ')',
                    'out_of_stock' => 'Out of Stock (' . ($kpis['out_of_stock'] ?? 0) . ')',
                    'archived'     => 'Archived'
                ];
                ?>
                <?php foreach ($statusTabs as $stKey => $stLabel): ?>
                    <?php
                    $tabParams = $filters;
                    $tabParams['status'] = $stKey;
                    $tabParams['page'] = 1;
                    $tabUrl = url('portal/products?' . http_build_query($tabParams));
                    $isActive = ($currentStatus === $stKey);
                    ?>
                    <a href="<?= $tabUrl ?>" style="padding: 8px 16px; font-size: 0.84rem; font-weight: <?= $isActive ? '700' : '600' ?>; border-radius: 8px; text-decoration: none; transition: all 0.15s; background: <?= $isActive ? '#4F46E5' : '#F8FAFC' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>; border: 1px solid <?= $isActive ? '#4F46E5' : '#E2E8F0' ?>;">
                        <?= htmlspecialchars($stLabel) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/products') ?>" method="GET" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? 'all') ?>">

                    <!-- Search Input -->
                    <div style="flex: 2; min-width: 240px; position: relative;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                            placeholder="Search by Title, SKU code, or slug..." 
                            class="form-input" 
                            style="height: 42px; padding: 0 14px; font-size: 0.88rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 8px;"
                        >
                    </div>

                    <!-- Category Select -->
                    <div style="flex: 1; min-width: 170px;">
                        <select name="category_id" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                            <option value="all">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (($filters['category_id'] ?? 'all') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort Selector -->
                    <div style="flex: 1; min-width: 160px;">
                        <select name="sort" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                            <option value="newest" <?= (($filters['sort'] ?? '') === 'newest') ? 'selected' : '' ?>>Sort: Newest</option>
                            <option value="oldest" <?= (($filters['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Sort: Oldest</option>
                            <option value="price_low" <?= (($filters['sort'] ?? '') === 'price_low') ? 'selected' : '' ?>>Price: Low &rarr; High</option>
                            <option value="price_high" <?= (($filters['sort'] ?? '') === 'price_high') ? 'selected' : '' ?>>Price: High &rarr; Low</option>
                            <option value="stock_low" <?= (($filters['sort'] ?? '') === 'stock_low') ? 'selected' : '' ?>>Stock: Critical First</option>
                            <option value="stock_high" <?= (($filters['sort'] ?? '') === 'stock_high') ? 'selected' : '' ?>>Stock: High &rarr; Low</option>
                            <option value="name_asc" <?= (($filters['sort'] ?? '') === 'name_asc') ? 'selected' : '' ?>>Name: A &rarr; Z</option>
                        </select>
                    </div>

                    <!-- Filter Button -->
                    <button type="submit" style="height: 42px; padding: 0 20px; font-size: 0.85rem; font-weight: 700; background: #4F46E5; color: #FFFFFF; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                        Filter Catalog
                    </button>

                    <!-- Reset Filters Link -->
                    <?php if (!empty($filters['search']) || ($filters['category_id'] ?? 'all') !== 'all' || ($filters['status'] ?? 'all') !== 'all' || ($filters['sort'] ?? 'newest') !== 'newest'): ?>
                        <a href="<?= url('portal/products') ?>" style="font-size: 0.82rem; color: #D97706; font-weight: 700; text-decoration: none;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Products Catalog Ledger Table Card -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">Garment Master &amp; Stock Ledger</h2>
                        <div style="font-size: 0.8rem; color: #64748B; margin-top: 3px;">
                            Showing <?= count($products) ?> of <?= $pagination['total_records'] ?? 0 ?> catalog products
                        </div>
                    </div>
                </div>

                <?php if (!empty($products)): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.76rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <th style="padding: 14px 20px; width: 340px;">Garment &amp; Classification</th>
                                    <th style="padding: 14px 16px;">Primary SKU &amp; Variants</th>
                                    <th style="padding: 14px 16px;">Pricing (INR)</th>
                                    <th style="padding: 14px 16px;">Stock Health</th>
                                    <th style="padding: 14px 16px;">Catalog Status</th>
                                    <th style="padding: 14px 20px; text-align: right;">Actions</th>
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
                                        $stockBg = '#FEE2E2';
                                        $stockColor = '#991B1B';
                                        $stockLabel = 'Out of Stock (0)';
                                    } elseif ($totalStock <= $lowThresh) {
                                        $stockBg = '#FEF3C7';
                                        $stockColor = '#92400E';
                                        $stockLabel = 'Low Stock (' . $totalStock . ')';
                                    } else {
                                        $stockBg = '#D1FAE5';
                                        $stockColor = '#065F46';
                                        $stockLabel = 'In Stock (' . $totalStock . ')';
                                    }

                                    // Status styling
                                    $statusPillBg = match($p['status']) {
                                        'active'   => '#D1FAE5',
                                        'draft'    => '#E0E7FF',
                                        'archived' => '#F1F5F9',
                                        default    => '#FEF3C7'
                                    };
                                    $statusPillColor = match($p['status']) {
                                        'active'   => '#065F46',
                                        'draft'    => '#3730A3',
                                        'archived' => '#475569',
                                        default    => '#92400E'
                                    };

                                    // Safe JSON payload for stock adjust modal
                                    $variantsJson = htmlspecialchars(json_encode($p['variants'] ?? []), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        
                                        <!-- Product info & thumbnail -->
                                        <td style="padding: 16px 20px;">
                                            <div style="display: flex; align-items: center; gap: 14px;">
                                                <div style="width: 52px; height: 52px; border-radius: 8px; overflow: hidden; background: #F1F5F9; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0;">
                                                    <?php if (!empty($p['primary_image'])): ?>
                                                        <img src="<?= htmlspecialchars(image_url($p['primary_image'])) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                                    <?php else: ?>
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8">
                                                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                            <polyline points="21 15 16 10 5 21"></polyline>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="overflow: hidden;">
                                                    <a href="<?= url('portal/products/' . $p['encrypted_id']) ?>" style="font-weight: 700; color: #0F172A; font-size: 0.9rem; text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px;" title="<?= htmlspecialchars($p['name']) ?>" onmouseover="this.style.color='#4F46E5';" onmouseout="this.style.color='#0F172A';">
                                                        <?= htmlspecialchars($p['name']) ?>
                                                    </a>
                                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                                        <span style="font-size: 0.72rem; font-weight: 700; color: #4338CA; background: #EEF2FF; padding: 2px 8px; border-radius: 4px;">
                                                            <?= htmlspecialchars($p['category_name'] ?? 'Luxury Apparel') ?>
                                                        </span>
                                                        <span style="font-size: 0.72rem; color: #94A3B8; font-family: monospace;">
                                                            /<?= htmlspecialchars($p['slug']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- SKU and Variants with Color Swatches -->
                                        <td style="padding: 16px;">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #1E40AF; background: #DBEAFE; padding: 3px 8px; border-radius: 4px;">
                                                    <?= htmlspecialchars($p['primary_sku'] ?: 'NO-SKU') ?>
                                                </span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 5px; margin-top: 6px; flex-wrap: wrap;">
                                                <?php
                                                $varList = $p['variants'] ?? [];
                                                $swatchCount = 0;
                                                foreach ($varList as $pv):
                                                    if (!empty($pv['color_code']) && $swatchCount < 5):
                                                        $swatchCount++;
                                                ?>
                                                        <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= htmlspecialchars($pv['color_code']) ?>; display: inline-block; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.2);" title="<?= htmlspecialchars(($pv['color_name'] ?? '') . ($pv['size'] ? ' (' . $pv['size'] . ')' : '')) ?>"></span>
                                                <?php
                                                    endif;
                                                endforeach;
                                                ?>
                                                <span style="font-size: 0.74rem; color: #64748B; font-weight: 600;">
                                                    <?= count($varList) ?> Variant<?= count($varList) !== 1 ? 's' : '' ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Pricing -->
                                        <td style="padding: 16px;">
                                            <div style="font-weight: 800; color: #0F172A; font-size: 0.92rem;">
                                                ₹<?= number_format((int)round((float)$p['sale_price'])) ?>
                                            </div>
                                            <?php if ($p['sale_price'] < $p['base_price']): ?>
                                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                                    <span style="font-size: 0.74rem; color: #94A3B8; text-decoration: line-through;">
                                                        ₹<?= number_format((int)round((float)$p['base_price'])) ?>
                                                    </span>
                                                    <span style="font-size: 0.68rem; font-weight: 700; color: #059669; background: #D1FAE5; padding: 1px 5px; border-radius: 3px;">
                                                        <?= round((($p['base_price'] - $p['sale_price']) / $p['base_price']) * 100) ?>% OFF
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Stock Health -->
                                        <td style="padding: 16px;">
                                            <span style="font-size: 0.75rem; font-weight: 700; background: <?= $stockBg ?>; color: <?= $stockColor ?>; padding: 4px 10px; border-radius: 999px; display: inline-block;">
                                                <?= $stockLabel ?>
                                            </span>
                                            <?php if (!empty($p['allow_backorder'])): ?>
                                                <div style="font-size: 0.7rem; color: #2563EB; font-weight: 600; margin-top: 4px;">
                                                    &bull; Backorders Allowed
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Catalog Status -->
                                        <td style="padding: 16px;">
                                            <?php if ($canEdit): ?>
                                                <form action="<?= url('portal/products/' . $p['encrypted_id'] . '/status') ?>" method="POST" style="margin: 0;">
                                                    <?= csrf_field() ?>
                                                    <select name="status" onchange="this.form.submit()" style="height: 32px; padding: 0 8px; font-size: 0.78rem; font-weight: 700; border-radius: 6px; border: 1px solid #CBD5E1; background: #fff; cursor: pointer; color: <?= $statusPillColor ?>;">
                                                        <option value="active" <?= $p['status'] === 'active' ? 'selected' : '' ?>>🟢 Active</option>
                                                        <option value="draft" <?= $p['status'] === 'draft' ? 'selected' : '' ?>>🟡 Draft</option>
                                                        <option value="archived" <?= $p['status'] === 'archived' ? 'selected' : '' ?>>⚪ Archived</option>
                                                    </select>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size: 0.75rem; font-weight: 700; background: <?= $statusPillBg ?>; color: <?= $statusPillColor ?>; padding: 4px 10px; border-radius: 6px;">
                                                    <?= ucfirst($p['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td style="padding: 16px 20px; text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                                <!-- Quick Stock Update Trigger -->
                                                <?php if ($canEdit): ?>
                                                    <button 
                                                        type="button" 
                                                        style="height: 32px; padding: 0 10px; font-size: 0.78rem; font-weight: 700; background: #F8FAFC; border: 1px solid #CBD5E1; color: #0F172A; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s;" 
                                                        title="Quick Adjust Variant Stock"
                                                        onclick="openStockModal('<?= $p['encrypted_id'] ?>', '<?= htmlspecialchars(addslashes($p['name'])) ?>', <?= $variantsJson ?>)"
                                                        onmouseover="this.style.background='#EEF2FF'; this.style.borderColor='#4F46E5'; this.style.color='#4F46E5';"
                                                        onmouseout="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'; this.style.color='#0F172A';"
                                                    >
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                        </svg>
                                                        <span>Stock</span>
                                                    </button>
                                                <?php endif; ?>

                                                <!-- Full Edit Button -->
                                                <?php if ($canEdit): ?>
                                                    <a href="<?= url('portal/products/' . $p['encrypted_id'] . '/edit') ?>" style="height: 32px; padding: 0 10px; font-size: 0.78rem; font-weight: 700; background: #F8FAFC; border: 1px solid #CBD5E1; color: #0F172A; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s;" title="Edit Product &amp; SKUs" onmouseover="this.style.background='#F1F5F9'; this.style.color='#4F46E5';" onmouseout="this.style.background='#F8FAFC'; this.style.color='#0F172A';">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                        <span>Edit</span>
                                                    </a>
                                                <?php endif; ?>

                                                <!-- Inspect Details Button -->
                                                <a href="<?= url('portal/products/' . $p['encrypted_id']) ?>" style="height: 32px; padding: 0 9px; font-size: 0.78rem; font-weight: 700; background: #F8FAFC; border: 1px solid #CBD5E1; color: #64748B; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;" title="View Specifications &amp; SKUs" onmouseover="this.style.color='#0F172A'; this.style.borderColor='#94A3B8';" onmouseout="this.style.color='#64748B'; this.style.borderColor='#CBD5E1';">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                                    </svg>
                                                    <span>View</span>
                                                </a>

                                                <!-- Soft Archive/Delete -->
                                                <?php if ($canDelete): ?>
                                                    <form action="<?= url('portal/products/' . $p['encrypted_id'] . '/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to archive <?= htmlspecialchars(addslashes($p['name'])) ?>?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" style="height: 32px; padding: 0 8px; background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; transition: all 0.15s;" title="Archive Product" onmouseover="this.style.background='#FCA5A5';" onmouseout="this.style.background='#FEE2E2';">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-top: 1px solid #E2E8F0; font-size: 0.84rem; color: #64748B; flex-wrap: wrap; gap: 12px; background: #FAF5FF;">
                            <div>
                                Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?> (<?= $pagination['total_records'] ?? 0 ?> total products)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <?php if (!empty($pagination['has_prev']) || ($pagination['current_page'] ?? 1) > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = ($pagination['current_page'] ?? 2) - 1;
                                    ?>
                                    <a href="<?= url('portal/products?' . http_build_query($prevParams)) ?>" style="height: 34px; padding: 0 12px; display: inline-flex; align-items: center; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700; border-radius: 6px; text-decoration: none;">
                                        &larr; Previous
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= ($pagination['total_pages'] ?? 1); $i++): ?>
                                    <?php
                                    if ($i !== 1 && $i !== ($pagination['total_pages'] ?? 1) && abs($i - ($pagination['current_page'] ?? 1)) > 2) {
                                        continue;
                                    }
                                    $pageParams = $filters;
                                    $pageParams['page'] = $i;
                                    $isCur = ($i === ($pagination['current_page'] ?? 1));
                                    ?>
                                    <a href="<?= url('portal/products?' . http_build_query($pageParams)) ?>" style="height: 34px; width: 34px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 6px; text-decoration: none; background: <?= $isCur ? '#4F46E5' : '#FFFFFF' ?>; color: <?= $isCur ? '#FFFFFF' : '#0F172A' ?>; border: 1px solid <?= $isCur ? '#4F46E5' : '#CBD5E1' ?>;">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if (!empty($pagination['has_next']) || ($pagination['current_page'] ?? 1) < ($pagination['total_pages'] ?? 1)): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = ($pagination['current_page'] ?? 1) + 1;
                                    ?>
                                    <a href="<?= url('portal/products?' . http_build_query($nextParams)) ?>" style="height: 34px; padding: 0 12px; display: inline-flex; align-items: center; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700; border-radius: 6px; text-decoration: none;">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" style="margin-bottom: 16px;">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No Catalog Garments Found</h3>
                        <p style="font-size: 0.85rem; color: #64748B; max-width: 400px; margin: 0 auto 20px;">
                            No catalog items match your search or filter criteria. Try adjusting filters or create a new luxury garment.
                        </p>
                        <?php if ($canCreate): ?>
                            <a href="<?= url('portal/products/create') ?>" style="height: 40px; padding: 0 20px; display: inline-flex; align-items: center; gap: 8px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none;">
                                <span>+ Create New Product</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick In-Line Stock Adjustment Modal -->
            <div id="stockAdjustModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
                <div style="background: #FFFFFF; border-radius: 14px; max-width: 560px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #E2E8F0; overflow: hidden; animation: modalPop 0.2s ease-out;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">Quick Inventory Adjust</h3>
                            <p id="modalProductName" style="font-size: 0.82rem; color: #64748B; margin: 4px 0 0;"></p>
                        </div>
                        <button type="button" onclick="closeStockModal()" style="background: none; border: none; font-size: 1.5rem; color: #94A3B8; cursor: pointer; line-height: 1;">&times;</button>
                    </div>
                    <div style="padding: 24px; max-height: 60vh; overflow-y: auto;" id="modalVariantsList">
                        <!-- Populated by JavaScript -->
                    </div>
                    <div style="padding: 16px 24px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end;">
                        <button type="button" onclick="closeStockModal()" style="height: 38px; padding: 0 18px; background: #FFFFFF; border: 1px solid #CBD5E1; color: #0F172A; font-weight: 700; font-size: 0.84rem; border-radius: 8px; cursor: pointer;">
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
                    container.innerHTML = '<p style="font-size:0.85rem; color:#64748B; text-align:center; padding: 20px 0;">No variants configured for this product.</p>';
                } else {
                    variants.forEach(function(v) {
                        const row = document.createElement('div');
                        row.style.cssText = 'background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; gap: 12px;';
                        
                        const actionUrl = '<?= url("portal/products/") ?>' + encryptedId + '/stock';
                        
                        row.innerHTML = `
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.88rem; color: #0F172A;">${v.variant_name || 'Standard'}</div>
                                <div style="font-size: 0.75rem; font-family: monospace; color: #2563EB; margin-top: 2px;">SKU: ${v.sku}</div>
                                <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px;">Current: <strong>${v.stock_qty}</strong> in stock</div>
                            </div>
                            <form action="${actionUrl}" method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="variant_id" value="${v.id}">
                                <input type="number" name="new_qty" value="${v.stock_qty}" min="0" max="99999" style="width: 80px; height: 36px; padding: 0 8px; font-weight: 700; text-align: center; border: 1px solid #CBD5E1; border-radius: 6px;">
                                <button type="submit" style="height: 36px; padding: 0 14px; font-size: 0.8rem; font-weight: 700; background: #4F46E5; color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
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
    </div>
</div>
