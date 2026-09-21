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
                        <span>Categories</span>
                    </div>
                    <h1 class="welcome-title">Categories & Taxonomy</h1>
                    <p class="welcome-subtitle">Curate luxury apparel collections, multi-tier parent hierarchies, showcase covers, and linked garment lines.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/categories/create') ?>" class="btn-primary-gradient" style="height: 42px; padding: 0 18px; display: inline-flex; align-items: center; gap: 8px; width: auto;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>+ Add New Category</span>
                    </a>
                </div>
            </div>

            <!-- Categories KPI Summary Bar -->
            <div class="catalog-kpi-grid">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Collections</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Active in Storefront</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;"><?= number_format($kpis['active']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Root Collections</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-blue); margin-top: 4px;"><?= number_format($kpis['root_categories']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-purple);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-purple); text-transform: uppercase;">Sub-Categories</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;"><?= number_format($kpis['sub_categories']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-orange);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-orange); text-transform: uppercase;">Assigned Garments</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($kpis['total_products']) ?></div>
                </div>
            </div>

            <!-- Quick Status & Level Tabs -->
            <?php
            $currentStatus = $filters['status'] ?? 'all';
            $currentParent = $filters['parent_id'] ?? 'all';
            ?>
            <div class="catalog-tabs-bar">
                <?php
                $tabs = [
                    'all'      => ['label' => 'All Categories (' . $kpis['total'] . ')', 'status' => 'all', 'parent' => 'all'],
                    'active'   => ['label' => 'Active (' . $kpis['active'] . ')', 'status' => 'active', 'parent' => 'all'],
                    'inactive' => ['label' => 'Hidden / Inactive (' . $kpis['inactive'] . ')', 'status' => 'inactive', 'parent' => 'all'],
                    'root'     => ['label' => 'Primary Root (' . $kpis['root_categories'] . ')', 'status' => 'all', 'parent' => 'root'],
                    'sub'      => ['label' => 'Sub-Categories (' . $kpis['sub_categories'] . ')', 'status' => 'all', 'parent' => 'sub'],
                ];
                foreach ($tabs as $tKey => $tData):
                    $tabParams = $filters;
                    $tabParams['status'] = $tData['status'];
                    $tabParams['parent_id'] = $tData['parent'];
                    $tabParams['page'] = 1;
                    $tabUrl = url('admin/categories?' . http_build_query($tabParams));
                    $isActive = ($tKey === 'all' && $currentStatus === 'all' && $currentParent === 'all')
                             || ($tKey === 'active' && $currentStatus === 'active')
                             || ($tKey === 'inactive' && $currentStatus === 'inactive')
                             || ($tKey === 'root' && $currentParent === 'root')
                             || ($tKey === 'sub' && $currentParent === 'sub');
                ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($tData['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 18px 22px;">
                <form action="<?= url('admin/categories') ?>" method="GET" class="catalog-filter-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            placeholder="Search by Category Name or URL Slug..." 
                            class="form-input" 
                            style="height: 42px; font-size: 0.88rem;"
                        >
                    </div>

                    <div style="flex: 1; min-width: 180px;">
                        <select name="parent_id" class="form-input" style="height: 42px; font-size: 0.88rem;">
                            <option value="all" <?= ($filters['parent_id'] === 'all') ? 'selected' : '' ?>>All Hierarchy Levels</option>
                            <option value="root" <?= ($filters['parent_id'] === 'root') ? 'selected' : '' ?>>Primary Roots Only</option>
                            <option value="sub" <?= ($filters['parent_id'] === 'sub') ? 'selected' : '' ?>>All Sub-Categories</option>
                            <optgroup label="Specific Parent:">
                                <?php foreach ($parents as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($filters['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                        Under: <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <select name="sort" class="form-input" style="height: 42px; font-size: 0.88rem;">
                            <option value="sort_order" <?= ($filters['sort'] === 'sort_order') ? 'selected' : '' ?>>Sort: Display Order</option>
                            <option value="name_asc" <?= ($filters['sort'] === 'name_asc') ? 'selected' : '' ?>>Name: A &rarr; Z</option>
                            <option value="name_desc" <?= ($filters['sort'] === 'name_desc') ? 'selected' : '' ?>>Name: Z &rarr; A</option>
                            <option value="products_desc" <?= ($filters['sort'] === 'products_desc') ? 'selected' : '' ?>>Most Products First</option>
                            <option value="newest" <?= ($filters['sort'] === 'newest') ? 'selected' : '' ?>>Recently Created</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary-gradient" style="height: 42px; width: auto; padding: 0 20px; font-size: 0.85rem;">
                        Filter Categories
                    </button>

                    <?php if (!empty($filters['search']) || $filters['parent_id'] !== 'all' || $filters['status'] !== 'all' || $filters['sort'] !== 'sort_order'): ?>
                        <a href="<?= url('admin/categories') ?>" style="font-size: 0.82rem; color: var(--brand-orange); font-weight: 600;">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Categories Ledger Table Card -->
            <div class="card-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Taxonomy Ledger</h2>
                        <div class="panel-subtitle">Showing <?= count($categories) ?> of <?= $pagination['total_records'] ?> categories</div>
                    </div>
                </div>

                <?php if (!empty($categories)): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th style="width: 320px;">Category & Media</th>
                                    <th>Taxonomy Level</th>
                                    <th>Slug / Route</th>
                                    <th>Assigned Garments</th>
                                    <th>Display Order</th>
                                    <th>Visibility</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <?php
                                    $isRoot = empty($cat['parent_id']);
                                    $isActive = ($cat['is_active'] == 1);
                                    $prodCount = (int)($cat['product_count'] ?? 0);
                                    ?>
                                    <tr>
                                        <!-- Category thumbnail and name -->
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 14px;">
                                                <div style="width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                                                    <?php if (!empty($cat['image_url'])): ?>
                                                        <img src="<?= htmlspecialchars(image_url($cat['image_url'])) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                                    <?php else: ?>
                                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
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
                                                    <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">
                                                        <?= htmlspecialchars($cat['name']) ?>
                                                    </div>
                                                    <?php if (!empty($cat['description'])): ?>
                                                        <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            <?= htmlspecialchars($cat['description']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Hierarchy Badge -->
                                        <td>
                                            <?php if ($isRoot): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; background: rgba(45, 130, 255, 0.1); color: var(--brand-blue); padding: 3px 9px; border-radius: 9999px; font-size: 0.74rem; font-weight: 700;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                    Root Collection
                                                </span>
                                            <?php else: ?>
                                                <div style="display: flex; flex-direction: column;">
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; background: rgba(140, 48, 245, 0.1); color: var(--brand-purple); padding: 3px 9px; border-radius: 9999px; font-size: 0.74rem; font-weight: 700; width: fit-content;">
                                                        ↳ Sub-Category
                                                    </span>
                                                    <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                                        under <strong><?= htmlspecialchars($cat['parent_name'] ?? 'Root') ?></strong>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Slug / Route -->
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.8rem; background: #f8fafc; padding: 3px 8px; border-radius: 4px; border: 1px solid var(--border-color); color: var(--text-secondary);">
                                                <?= htmlspecialchars($cat['slug']) ?>
                                            </span>
                                        </td>

                                        <!-- Products assigned -->
                                        <td>
                                            <a href="<?= url('admin/products?category_id=' . $cat['id']) ?>" style="display: inline-flex; align-items: center; gap: 6px; color: var(--brand-blue); font-weight: 700; font-size: 0.84rem; text-decoration: none;" title="View all garments in this category">
                                                <span><?= number_format($prodCount) ?> garments</span>
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            </a>
                                        </td>

                                        <!-- Sort Order -->
                                        <td>
                                            <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-primary);">
                                                #<?= $cat['sort_order'] ?>
                                            </span>
                                        </td>

                                        <!-- Visibility & In-Line Toggle -->
                                        <td>
                                            <form action="<?= url('admin/categories/' . $cat['encrypted_id'] . '/status') ?>" method="POST" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;" title="Click to toggle active status">
                                                    <?php if ($isActive): ?>
                                                        <span class="badge-delivered" style="cursor: pointer;">
                                                            ● Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-cancelled" style="cursor: pointer;">
                                                            ○ Inactive
                                                        </span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Actions -->
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <a href="<?= url('admin/categories/' . $cat['encrypted_id'] . '/edit') ?>" class="btn-export" style="height: 32px; padding: 0 10px; font-size: 0.78rem;" title="Edit Category Specifications">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                    <span>Edit</span>
                                                </a>

                                                <form action="<?= url('admin/categories/' . $cat['encrypted_id'] . '/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete category \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn-export" style="height: 32px; padding: 0 8px; color: #ef4444;" title="Delete Category">
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
                                Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> (<?= $pagination['total_records'] ?> total categories)
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <?php if (!empty($pagination['has_prev']) || $pagination['current_page'] > 1): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = $pagination['current_page'] - 1;
                                    ?>
                                    <a href="<?= url('admin/categories?' . http_build_query($prevParams)) ?>" class="btn-export" style="height: 34px; padding: 0 12px;">
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
                                    <a href="<?= url('admin/categories?' . http_build_query($pageParams)) ?>" class="<?= $isCur ? 'btn-primary-gradient' : 'btn-export' ?>" style="height: 34px; width: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 6px;">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if (!empty($pagination['has_next']) || $pagination['current_page'] < $pagination['total_pages']): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = $pagination['current_page'] + 1;
                                    ?>
                                    <a href="<?= url('admin/categories?' . http_build_query($nextParams)) ?>" class="btn-export" style="height: 34px; padding: 0 12px;">
                                        Next &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom: 16px;">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">No Categories Found</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); max-width: 400px; margin: 0 auto 20px;">
                            No collections match your current filter criteria. Create a new collection or clear your filters.
                        </p>
                        <a href="<?= url('admin/categories/create') ?>" class="btn-primary-gradient" style="height: 40px; width: auto; padding: 0 20px; display: inline-flex; align-items: center; gap: 8px;">
                            <span>+ Create New Category</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
