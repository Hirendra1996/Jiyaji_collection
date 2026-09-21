<?php
$title = 'Categories & Taxonomy | Jiyaji LX Staff Portal';
$filters = $filters ?? [
    'status'    => $_GET['status'] ?? 'all',
    'parent_id' => $_GET['parent_id'] ?? 'all',
    'search'    => trim($_GET['search'] ?? ''),
    'sort'      => $_GET['sort'] ?? 'sort_order'
];
$canManage  = $canManage ?? (function_exists('staff_can') ? (staff_can('categories', 'manage') || staff_can('categories', 'create') || staff_can('categories', 'edit')) : false);
$categories = $categories ?? [];
$pagination = $pagination ?? [];
$kpis       = $kpis ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'root_categories' => 0, 'sub_categories' => 0, 'total_products' => 0];
$parents    = $parents ?? [];

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Header Banner -->
            <div class="welcome-banner" style="background: linear-gradient(135deg, #0F172A 0%, #581C87 60%, #7E22CE 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(126, 34, 206, 0.3);">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 999px; color: #F3E8FF;">
                            Catalog &bull; Taxonomy Structure
                        </span>
                        <?php if ($canManage): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #FAF5FF; color: #6B21A8; padding: 2px 8px; border-radius: 6px;">
                                Write &bull; Taxonomy Manager &amp; Merchandiser
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 6px;">
                                Read-Only Clearance
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Categories &amp; Taxonomy
                    </h1>
                    <p style="font-size: 0.88rem; color: #F3E8FF; margin: 0;">
                        Curate luxury apparel collections, multi-tier parent hierarchies, showcase covers, and linked garment lines.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- CSV Export Button -->
                    <?php
                    $exportParams = $filters;
                    $exportUrl = url('portal/categories/export?' . http_build_query($exportParams));
                    ?>
                    <a href="<?= $exportUrl ?>" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 15px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export CSV</span>
                    </a>

                    <!-- Add New Category Button -->
                    <?php if ($canManage): ?>
                        <a href="<?= url('portal/categories/create') ?>" style="background: #9333EA; border: 1px solid rgba(255,255,255,0.3); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(147, 51, 234, 0.35);" onmouseover="this.style.background='#7E22CE';" onmouseout="this.style.background='#9333EA';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>+ Add New Category</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categories KPI Summary Bar -->
            <div class="catalog-kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">Total Collections</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px;"><?= number_format($kpis['total'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Entire departmental taxonomy</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #10B981;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Active in Storefront</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #10B981; margin-top: 6px;"><?= number_format($kpis['active'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Visible in shopper menus</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #3B82F6;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #1D4ED8; text-transform: uppercase; letter-spacing: 0.05em;">Root Collections</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #3B82F6; margin-top: 6px;"><?= number_format($kpis['root_categories'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Top-level brand categories</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #9333EA;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.05em;">Sub-Categories</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #9333EA; margin-top: 6px;"><?= number_format($kpis['sub_categories'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Nested specialty lines</div>
                </div>
                <div class="kpi-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); border-left: 4px solid #F59E0B;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Assigned Garments</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #F59E0B; margin-top: 6px;"><?= number_format($kpis['total_products'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Catalog items in taxonomy</div>
                </div>
            </div>

            <!-- Quick Status & Level Tabs -->
            <?php
            $currentStatus = $filters['status'] ?? 'all';
            $currentParent = $filters['parent_id'] ?? 'all';
            ?>
            <div class="catalog-tabs-bar" style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
                <?php
                $tabs = [
                    'all'      => ['label' => 'All Categories (' . ($kpis['total'] ?? 0) . ')', 'status' => 'all', 'parent' => 'all'],
                    'active'   => ['label' => 'Active (' . ($kpis['active'] ?? 0) . ')', 'status' => 'active', 'parent' => 'all'],
                    'inactive' => ['label' => 'Hidden / Inactive (' . ($kpis['inactive'] ?? 0) . ')', 'status' => 'inactive', 'parent' => 'all'],
                    'root'     => ['label' => 'Primary Root (' . ($kpis['root_categories'] ?? 0) . ')', 'status' => 'all', 'parent' => 'root'],
                    'sub'      => ['label' => 'Sub-Categories (' . ($kpis['sub_categories'] ?? 0) . ')', 'status' => 'all', 'parent' => 'sub'],
                ];
                foreach ($tabs as $tKey => $tData):
                    $tabParams = $filters;
                    $tabParams['status'] = $tData['status'];
                    $tabParams['parent_id'] = $tData['parent'];
                    $tabParams['page'] = 1;
                    $tabUrl = url('portal/categories?' . http_build_query($tabParams));
                    $isActive = ($tKey === 'all' && $currentStatus === 'all' && $currentParent === 'all')
                             || ($tKey === 'active' && $currentStatus === 'active')
                             || ($tKey === 'inactive' && $currentStatus === 'inactive')
                             || ($tKey === 'root' && $currentParent === 'root')
                             || ($tKey === 'sub' && $currentParent === 'sub');
                ?>
                    <a href="<?= $tabUrl ?>" style="padding: 8px 16px; font-size: 0.84rem; font-weight: <?= $isActive ? '700' : '600' ?>; border-radius: 8px; text-decoration: none; transition: all 0.15s; background: <?= $isActive ? '#9333EA' : '#F8FAFC' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>; border: 1px solid <?= $isActive ? '#9333EA' : '#E2E8F0' ?>;">
                        <?= htmlspecialchars($tData['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/categories') ?>" method="GET" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? 'all') ?>">

                    <!-- Search Input -->
                    <div style="flex: 2; min-width: 240px;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                            placeholder="Search by Category Name or URL Slug..." 
                            class="form-input" 
                            style="height: 42px; padding: 0 14px; font-size: 0.88rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 8px;"
                        >
                    </div>

                    <!-- Hierarchy Level Filter -->
                    <div style="flex: 1; min-width: 180px;">
                        <select name="parent_id" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                            <option value="all" <?= (($filters['parent_id'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Hierarchy Levels</option>
                            <option value="root" <?= (($filters['parent_id'] ?? '') === 'root') ? 'selected' : '' ?>>Primary Roots Only</option>
                            <option value="sub" <?= (($filters['parent_id'] ?? '') === 'sub') ? 'selected' : '' ?>>All Sub-Categories</option>
                            <optgroup label="Specific Parent:">
                                <?php foreach ($parents as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= (($filters['parent_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                                        Under: <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Sort Selector -->
                    <div style="flex: 1; min-width: 160px;">
                        <select name="sort" class="form-input" style="height: 42px; padding: 0 12px; font-size: 0.88rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                            <option value="sort_order" <?= (($filters['sort'] ?? '') === 'sort_order') ? 'selected' : '' ?>>Sort: Display Order</option>
                            <option value="name_asc" <?= (($filters['sort'] ?? '') === 'name_asc') ? 'selected' : '' ?>>Name: A &rarr; Z</option>
                            <option value="name_desc" <?= (($filters['sort'] ?? '') === 'name_desc') ? 'selected' : '' ?>>Name: Z &rarr; A</option>
                            <option value="products_desc" <?= (($filters['sort'] ?? '') === 'products_desc') ? 'selected' : '' ?>>Most Products First</option>
                            <option value="newest" <?= (($filters['sort'] ?? '') === 'newest') ? 'selected' : '' ?>>Recently Created</option>
                        </select>
                    </div>

                    <!-- Filter Button -->
                    <button type="submit" style="height: 42px; padding: 0 20px; font-size: 0.85rem; font-weight: 700; background: #9333EA; color: #FFFFFF; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#7E22CE';" onmouseout="this.style.background='#9333EA';">
                        Filter Categories
                    </button>

                    <!-- Reset Filters Link -->
                    <?php if (!empty($filters['search']) || ($filters['parent_id'] ?? 'all') !== 'all' || ($filters['status'] ?? 'all') !== 'all' || ($filters['sort'] ?? 'sort_order') !== 'sort_order'): ?>
                        <a href="<?= url('portal/categories') ?>" style="font-size: 0.82rem; color: #D97706; font-weight: 700; text-decoration: none;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Categories Ledger Table Card -->
            <div class="card-panel" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">Departmental Taxonomy Ledger</h2>
                        <div style="font-size: 0.8rem; color: #64748B; margin-top: 3px;">
                            Showing <?= count($categories) ?> of <?= $pagination['total_records'] ?? 0 ?> categories
                        </div>
                    </div>
                </div>

                <?php if (!empty($categories)): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.76rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <th style="padding: 14px 20px; width: 340px;">Category &amp; Media</th>
                                    <th style="padding: 14px 16px;">Taxonomy Level</th>
                                    <th style="padding: 14px 16px;">Slug / Route</th>
                                    <th style="padding: 14px 16px;">Assigned Garments</th>
                                    <th style="padding: 14px 16px;">Display Order</th>
                                    <th style="padding: 14px 16px;">Visibility</th>
                                    <th style="padding: 14px 20px; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <?php
                                    $isRoot = empty($cat['parent_id']);
                                    $isActive = ($cat['is_active'] == 1);
                                    $prodCount = (int)($cat['product_count'] ?? 0);
                                    ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        
                                        <!-- Category thumbnail and name -->
                                        <td style="padding: 16px 20px;">
                                            <div style="display: flex; align-items: center; gap: 14px;">
                                                <div style="width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #F1F5F9; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0;">
                                                    <?php if (!empty($cat['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars(image_url($cat['image_url'])) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                                    <?php else: ?>
                                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8">
                                                            <line x1="8" y1="6" x2="21" y2="6"></line>
                                                            <line x1="8" y1="12" x2="21" y2="12"></line>
                                                            <line x1="8" y1="18" x2="21" y2="18"></line>
                                                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <a href="<?= url('portal/categories/' . $cat['encrypted_id']) ?>" style="font-weight: 700; font-size: 0.92rem; color: #0F172A; text-decoration: none;" onmouseover="this.style.color='#9333EA';" onmouseout="this.style.color='#0F172A';">
                                                        <?= htmlspecialchars($cat['name']) ?>
                                                    </a>
                                                    <?php if (!empty($cat['description'])): ?>
                                                        <div style="font-size: 0.74rem; color: #64748B; margin-top: 2px; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            <?= htmlspecialchars($cat['description']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Hierarchy Badge -->
                                        <td style="padding: 16px;">
                                            <?php if ($isRoot): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #DBEAFE; color: #1E40AF; padding: 3px 9px; border-radius: 999px; font-size: 0.74rem; font-weight: 700;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                    Root Collection
                                                </span>
                                            <?php else: ?>
                                                <div style="display: flex; flex-direction: column;">
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #F3E8FF; color: #7E22CE; padding: 3px 9px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; width: fit-content;">
                                                        ↳ Sub-Category
                                                    </span>
                                                    <span style="font-size: 0.72rem; color: #64748B; margin-top: 3px;">
                                                        under <strong><?= htmlspecialchars($cat['parent_name'] ?? 'Root') ?></strong>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Slug / Route -->
                                        <td style="padding: 16px;">
                                            <span style="font-family: monospace; font-size: 0.8rem; background: #F8FAFC; padding: 3px 8px; border-radius: 4px; border: 1px solid #E2E8F0; color: #475569;">
                                                /<?= htmlspecialchars($cat['slug']) ?>
                                            </span>
                                        </td>

                                        <!-- Products assigned -->
                                        <td style="padding: 16px;">
                                            <a href="<?= url('portal/products?category_id=' . $cat['id']) ?>" style="display: inline-flex; align-items: center; gap: 6px; color: #4F46E5; font-weight: 700; font-size: 0.84rem; text-decoration: none;" title="View all garments in this category">
                                                <span><?= number_format($prodCount) ?> garments</span>
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            </a>
                                        </td>

                                        <!-- Sort Order -->
                                        <td style="padding: 16px;">
                                            <span style="font-weight: 700; font-size: 0.85rem; color: #0F172A; background: #F1F5F9; padding: 2px 8px; border-radius: 4px;">
                                                #<?= $cat['sort_order'] ?>
                                            </span>
                                        </td>

                                        <!-- Visibility & In-Line Toggle -->
                                        <td style="padding: 16px;">
                                            <?php if ($canManage): ?>
                                                <form action="<?= url('portal/categories/' . $cat['encrypted_id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;" title="Click to toggle active status">
                                                        <?php if ($isActive): ?>
                                                            <span style="font-size: 0.74rem; font-weight: 700; background: #D1FAE5; color: #065F46; padding: 3px 9px; border-radius: 999px; cursor: pointer;">
                                                                ● Active
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="font-size: 0.74rem; font-weight: 700; background: #FEE2E2; color: #991B1B; padding: 3px 9px; border-radius: 999px; cursor: pointer;">
                                                                ○ Inactive
                                                            </span>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <?php if ($isActive): ?>
                                                    <span style="font-size: 0.74rem; font-weight: 700; background: #D1FAE5; color: #065F46; padding: 3px 9px; border-radius: 999px;">
                                                        ● Active
                                                    </span>
                                                <?php else: ?>
                                                    <span style="font-size: 0.74rem; font-weight: 700; background: #FEE2E2; color: #991B1B; padding: 3px 9px; border-radius: 999px;">
                                                        ○ Inactive
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td style="padding: 16px 20px; text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px; justify-content: flex-end;">
                                                <a href="<?= url('portal/categories/' . $cat['encrypted_id']) ?>" style="height: 32px; padding: 0 9px; font-size: 0.78rem; font-weight: 700; background: #F8FAFC; border: 1px solid #CBD5E1; color: #64748B; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="View Category Specification">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                                    </svg>
                                                    <span>View</span>
                                                </a>

                                                <?php if ($canManage): ?>
                                                    <a href="<?= url('portal/categories/' . $cat['encrypted_id'] . '/edit') ?>" style="height: 32px; padding: 0 10px; font-size: 0.78rem; font-weight: 700; background: #F8FAFC; border: 1px solid #CBD5E1; color: #0F172A; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;" title="Edit Category Specifications">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                        <span>Edit</span>
                                                    </a>

                                                    <form action="<?= url('portal/categories/' . $cat['encrypted_id'] . '/delete') ?>" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete category \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" style="height: 32px; padding: 0 8px; background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center;" title="Delete Category">
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
                                Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?> (<?= $pagination['total_records'] ?? 0 ?> total categories)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <?php if (!empty($pagination['has_prev']) || ($pagination['current_page'] ?? 1) > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = ($pagination['current_page'] ?? 2) - 1;
                                    ?>
                                    <a href="<?= url('portal/categories?' . http_build_query($prevParams)) ?>" style="height: 34px; padding: 0 12px; display: inline-flex; align-items: center; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700; border-radius: 6px; text-decoration: none;">
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
                                    <a href="<?= url('portal/categories?' . http_build_query($pageParams)) ?>" style="height: 34px; width: 34px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 6px; text-decoration: none; background: <?= $isCur ? '#9333EA' : '#FFFFFF' ?>; color: <?= $isCur ? '#FFFFFF' : '#0F172A' ?>; border: 1px solid <?= $isCur ? '#9333EA' : '#CBD5E1' ?>;">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if (!empty($pagination['has_next']) || ($pagination['current_page'] ?? 1) < ($pagination['total_pages'] ?? 1)): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = ($pagination['current_page'] ?? 1) + 1;
                                    ?>
                                    <a href="<?= url('portal/categories?' . http_build_query($nextParams)) ?>" style="height: 34px; padding: 0 12px; display: inline-flex; align-items: center; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 700; border-radius: 6px; text-decoration: none;">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" style="margin-bottom: 16px;">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No Categories Found</h3>
                        <p style="font-size: 0.85rem; color: #64748B; max-width: 400px; margin: 0 auto 20px;">
                            No collections match your current filter criteria. Create a new collection or clear your filters.
                        </p>
                        <?php if ($canManage): ?>
                            <a href="<?= url('portal/categories/create') ?>" style="height: 40px; padding: 0 20px; display: inline-flex; align-items: center; gap: 8px; background: #9333EA; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none;">
                                <span>+ Create New Category</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
