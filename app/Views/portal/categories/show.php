<?php
$title = htmlspecialchars($category['name']) . ' | Collection Details | Jiyaji LX Staff Portal';
$canManage     = $canManage ?? (function_exists('staff_can') ? (staff_can('categories', 'manage') || staff_can('categories', 'edit')) : false);
$subCategories = $subCategories ?? [];
$isRoot        = empty($category['parent_id']);
$isActive      = ($category['is_active'] == 1);
$prodCount     = (int)($category['product_count'] ?? 0);

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/categories') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Categories &amp; Taxonomy</a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;"><?= htmlspecialchars($category['name']) ?></span>
            </div>

            <!-- Header Card -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                        <?php if ($isRoot): ?>
                            <span style="font-size: 0.74rem; font-weight: 700; background: #DBEAFE; color: #1E40AF; padding: 3px 10px; border-radius: 999px;">
                                Crown &bull; Root Collection
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.74rem; font-weight: 700; background: #F3E8FF; color: #7E22CE; padding: 3px 10px; border-radius: 999px;">
                                ↳ Sub-Category of <?= htmlspecialchars($category['parent_name'] ?? 'Root') ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($isActive): ?>
                            <span style="font-size: 0.74rem; font-weight: 700; background: #D1FAE5; color: #065F46; padding: 3px 10px; border-radius: 999px;">
                                ● Active on Storefront
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.74rem; font-weight: 700; background: #FEE2E2; color: #991B1B; padding: 3px 10px; border-radius: 999px;">
                                ○ Inactive / Hidden
                            </span>
                        <?php endif; ?>

                        <span style="font-size: 0.72rem; font-family: monospace; color: #94A3B8; background: #F8FAFC; padding: 3px 8px; border-radius: 4px; border: 1px solid #E2E8F0;">
                            ID: #<?= $category['id'] ?>
                        </span>
                    </div>

                    <h1 style="font-size: 1.65rem; font-weight: 800; color: #0F172A; margin: 0 0 6px 0; letter-spacing: -0.02em;">
                        <?= htmlspecialchars($category['name']) ?>
                    </h1>
                    <div style="font-size: 0.84rem; color: #64748B;">
                        Slug: <span style="font-family: monospace; color: #9333EA;">/<?= htmlspecialchars($category['slug']) ?></span> &bull; Display Sort Order #<?= $category['sort_order'] ?> &bull; Created on <?= date('d M Y', strtotime($category['created_at'])) ?>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="<?= url('portal/categories') ?>" style="height: 38px; padding: 0 16px; background: #FFFFFF; border: 1px solid #CBD5E1; color: #0F172A; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        &larr; Back to Taxonomy
                    </a>

                    <a href="<?= url('portal/products?category_id=' . $category['id']) ?>" style="height: 38px; padding: 0 16px; background: #EEF2FF; border: 1px solid #C7D2FE; color: #4F46E5; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <span>Browse Garments (<?= $prodCount ?>)</span>
                    </a>

                    <?php if ($canManage): ?>
                        <a href="<?= url('portal/categories/' . $category['encrypted_id'] . '/edit') ?>" style="height: 38px; padding: 0 18px; background: #9333EA; border: none; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(147, 51, 234, 0.25);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Edit Category</span>
                        </a>

                        <form action="<?= url('portal/categories/' . $category['encrypted_id'] . '/delete') ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Delete category <?= htmlspecialchars(addslashes($category['name'])) ?>?');">
                            <?= csrf_field() ?>
                            <button type="submit" style="height: 38px; padding: 0 14px; background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; font-weight: 700; font-size: 0.84rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2-Column Grid -->
            <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
                
                <!-- Left Column: Showcase Media & Taxonomy Architecture -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- Showcase Photo Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin: 0 0 14px 0;">Collection Showcase Photo</h3>
                        
                        <div style="width: 100%; height: 260px; border-radius: 10px; overflow: hidden; background: #F1F5F9; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($category['image_url'])): ?>
                                <img src="<?= htmlspecialchars(image_url($category['image_url'])) ?>" alt="<?= htmlspecialchars($category['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                            <?php else: ?>
                                <div style="text-align: center; padding: 20px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8">
                                        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <div style="font-size: 0.8rem; color: #64748B; margin-top: 8px;">No showcase photo attached.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Architecture & Hierarchy Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin: 0 0 14px 0;">Taxonomy Hierarchy</h3>
                        
                        <div style="font-size: 0.82rem; color: #475569; display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 8px;">
                                <span style="color: #64748B;">Hierarchy Level:</span>
                                <strong style="color: #0F172A;"><?= $isRoot ? 'Primary Root' : 'Sub-Category' ?></strong>
                            </div>

                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 8px;">
                                <span style="color: #64748B;">Parent Category:</span>
                                <?php if (!empty($category['parent_id'])): ?>
                                    <a href="<?= url('portal/categories/' . $category['parent_encrypted_id']) ?>" style="color: #9333EA; font-weight: 700; text-decoration: none;">
                                        <?= htmlspecialchars($category['parent_name'] ?? 'Root') ?> &rsaquo;
                                    </a>
                                <?php else: ?>
                                    <span style="color: #94A3B8;">None (Top-Level)</span>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 8px;">
                                <span style="color: #64748B;">Navigation Order:</span>
                                <span style="font-weight: 700; color: #0F172A;">#<?= $category['sort_order'] ?></span>
                            </div>

                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748B;">Storefront Status:</span>
                                <span style="font-weight: 700; color: <?= $isActive ? '#059669' : '#DC2626' ?>;">
                                    <?= $isActive ? 'Visible Online' : 'Hidden from Storefront' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Merchandising Metrics, Linked Subcategories & Description -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- Assigned Products Metric Tile -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">Assigned Catalog Garments</div>
                            <div style="font-size: 1.8rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                <?= number_format($prodCount) ?> <span style="font-size: 1rem; font-weight: 600; color: #64748B;">garments in this collection</span>
                            </div>
                        </div>
                        <a href="<?= url('portal/products?category_id=' . $category['id']) ?>" style="height: 40px; padding: 0 18px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.85rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <span>Inspect Garments Ledger</span>
                        </a>
                    </div>

                    <!-- Child Sub-Categories Card -->
                    <?php if ($isRoot): ?>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                            <div style="padding: 18px 22px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0;">Child Sub-Categories</h3>
                                    <div style="font-size: 0.78rem; color: #64748B; margin-top: 2px;">
                                        <?= count($subCategories) ?> nested sub-categor<?= count($subCategories) !== 1 ? 'ies' : 'y' ?> organized under this root collection
                                    </div>
                                </div>
                                <?php if ($canManage): ?>
                                    <a href="<?= url('portal/categories/create') ?>" style="font-size: 0.8rem; font-weight: 700; color: #9333EA; text-decoration: none;">
                                        + Add Sub-Category
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($subCategories)): ?>
                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                                        <thead>
                                            <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">
                                                <th style="padding: 10px 18px;">Sub-Category Name</th>
                                                <th style="padding: 10px 14px;">Slug</th>
                                                <th style="padding: 10px 14px;">Products</th>
                                                <th style="padding: 10px 14px;">Order</th>
                                                <th style="padding: 10px 18px; text-align: right;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subCategories as $sub): ?>
                                                <tr style="border-bottom: 1px solid #F1F5F9;">
                                                    <td style="padding: 12px 18px; font-weight: 700; font-size: 0.88rem; color: #0F172A;">
                                                        <a href="<?= url('portal/categories/' . $sub['encrypted_id']) ?>" style="color: #0F172A; text-decoration: none;" onmouseover="this.style.color='#9333EA';" onmouseout="this.style.color='#0F172A';">
                                                            <?= htmlspecialchars($sub['name']) ?>
                                                        </a>
                                                    </td>
                                                    <td style="padding: 12px 14px;">
                                                        <span style="font-family: monospace; font-size: 0.78rem; background: #F8FAFC; padding: 2px 6px; border-radius: 4px; border: 1px solid #E2E8F0; color: #475569;">
                                                            /<?= htmlspecialchars($sub['slug']) ?>
                                                        </span>
                                                    </td>
                                                    <td style="padding: 12px 14px;">
                                                        <a href="<?= url('portal/products?category_id=' . $sub['id']) ?>" style="color: #4F46E5; font-weight: 700; font-size: 0.82rem; text-decoration: none;">
                                                            <?= (int)($sub['product_count'] ?? 0) ?> garments
                                                        </a>
                                                    </td>
                                                    <td style="padding: 12px 14px; font-size: 0.84rem; font-weight: 600; color: #64748B;">
                                                        #<?= $sub['sort_order'] ?>
                                                    </td>
                                                    <td style="padding: 12px 18px; text-align: right;">
                                                        <a href="<?= url('portal/categories/' . $sub['encrypted_id']) ?>" style="font-size: 0.78rem; font-weight: 700; color: #9333EA; text-decoration: none;">
                                                            Inspect &rsaquo;
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div style="padding: 24px; text-align: center; color: #64748B; font-size: 0.85rem;">
                                    No child sub-categories nested under this collection.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Description Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0 0 12px 0;">Collection Story &amp; Description</h3>
                        <?php if (!empty($category['description'])): ?>
                            <div style="font-size: 0.9rem; color: #334155; line-height: 1.7;">
                                <?= nl2br(htmlspecialchars($category['description'])) ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 0.85rem; color: #94A3B8; margin: 0;">No description provided for this collection.</p>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
