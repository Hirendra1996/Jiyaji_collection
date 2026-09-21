<?php
$title = htmlspecialchars($product['name']) . ' | Product Specification | Jiyaji LX Staff Portal';
$canEdit   = $canEdit ?? (function_exists('staff_can') ? staff_can('products', 'edit') : false);
$canDelete = $canDelete ?? (function_exists('staff_can') ? staff_can('products', 'delete') : false);
$variants  = $product['variants'] ?? [];
$images    = $product['images'] ?? [];

$totalStock = (int)($product['total_stock'] ?? 0);
$lowThresh  = (int)($product['low_stock_threshold'] ?? 10);

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

$statusPillBg = match($product['status'] ?? 'active') {
    'active'   => '#D1FAE5',
    'draft'    => '#E0E7FF',
    'archived' => '#F1F5F9',
    default    => '#FEF3C7'
};
$statusPillColor = match($product['status'] ?? 'active') {
    'active'   => '#065F46',
    'draft'    => '#3730A3',
    'archived' => '#475569',
    default    => '#92400E'
};

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
                <a href="<?= url('portal/products') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Products &amp; SKUs</a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;"><?= htmlspecialchars($product['name']) ?></span>
            </div>

            <!-- Header Card -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span style="font-size: 0.72rem; font-weight: 700; background: #EEF2FF; color: #4338CA; padding: 3px 10px; border-radius: 6px;">
                            <?= htmlspecialchars($product['category_name'] ?? 'Luxury Apparel') ?>
                        </span>
                        <span style="font-size: 0.72rem; font-weight: 700; background: <?= $statusPillBg ?>; color: <?= $statusPillColor ?>; padding: 3px 10px; border-radius: 6px;">
                            Status: <?= ucfirst($product['status'] ?? 'active') ?>
                        </span>
                        <span style="font-size: 0.72rem; font-family: monospace; color: #94A3B8; background: #F8FAFC; padding: 3px 8px; border-radius: 4px; border: 1px solid #E2E8F0;">
                            ID: #<?= $product['id'] ?>
                        </span>
                    </div>
                    <h1 style="font-size: 1.65rem; font-weight: 800; color: #0F172A; margin: 0 0 6px 0; letter-spacing: -0.02em;">
                        <?= htmlspecialchars($product['name']) ?>
                    </h1>
                    <div style="font-size: 0.84rem; color: #64748B;">
                        Slug: <span style="font-family: monospace; color: #4F46E5;">/<?= htmlspecialchars($product['slug']) ?></span> &bull; Created on <?= date('d M Y, h:i A', strtotime($product['created_at'])) ?>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="<?= url('portal/products') ?>" style="height: 38px; padding: 0 16px; background: #FFFFFF; border: 1px solid #CBD5E1; color: #0F172A; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        &larr; Back to Catalog
                    </a>

                    <?php if ($canEdit): ?>
                        <a href="<?= url('portal/products/' . $product['encrypted_id'] . '/edit') ?>" style="height: 38px; padding: 0 18px; background: #4F46E5; border: none; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Edit Garment</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($canDelete): ?>
                        <form action="<?= url('portal/products/' . $product['encrypted_id'] . '/delete') ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Archive product <?= htmlspecialchars(addslashes($product['name'])) ?>?');">
                            <?= csrf_field() ?>
                            <button type="submit" style="height: 38px; padding: 0 14px; background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; font-weight: 700; font-size: 0.84rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                <span>Archive</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Specs 2-Column Grid -->
            <div style="display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start;">
                
                <!-- Left Column: Gallery & Media -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Gallery Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin: 0 0 14px 0;">Gallery Media (<?= count($images) ?>)</h3>
                        
                        <?php if (!empty($images)): ?>
                            <!-- Primary Preview -->
                            <div style="width: 100%; height: 340px; border-radius: 10px; overflow: hidden; background: #F1F5F9; border: 1px solid #E2E8F0; margin-bottom: 12px; display: flex; align-items: center; justify-content: center;">
                                <img id="activeMainImg" src="<?= htmlspecialchars(image_url($images[0]['url'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>

                            <!-- Thumbnail Row -->
                            <?php if (count($images) > 1): ?>
                                <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px;">
                                    <?php foreach ($images as $img): ?>
                                        <div onclick="document.getElementById('activeMainImg').src='<?= htmlspecialchars(image_url($img['url'])) ?>'" style="width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 2px solid <?= !empty($img['is_primary']) ? '#4F46E5' : '#E2E8F0' ?>; cursor: pointer; flex-shrink: 0;">
                                            <img src="<?= htmlspecialchars(image_url($img['url'])) ?>" alt="Thumb" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px 10px; background: #F8FAFC; border-radius: 8px; border: 1px dashed #CBD5E1;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8">
                                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p style="font-size: 0.8rem; color: #64748B; margin: 8px 0 0;">No gallery images uploaded.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- SEO Metadata Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0F172A; margin: 0 0 12px 0;">Search &amp; Meta Information</h3>
                        <div style="font-size: 0.8rem; color: #64748B; line-height: 1.5;">
                            <div style="margin-bottom: 8px;">
                                <strong style="color: #0F172A;">Meta Title:</strong><br>
                                <?= htmlspecialchars($product['meta_title'] ?? $product['name']) ?>
                            </div>
                            <div>
                                <strong style="color: #0F172A;">Meta Description:</strong><br>
                                <?= htmlspecialchars($product['meta_description'] ?? ($product['short_description'] ?? 'No description defined.')) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Specs, Pricing, & SKU Matrix -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- Pricing & Inventory KPI Banner -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px;">
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Selling Price</div>
                            <div style="font-size: 1.45rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                ₹<?= number_format((int)round((float)$product['sale_price'])) ?>
                            </div>
                            <?php if ($product['sale_price'] < $product['base_price']): ?>
                                <div style="font-size: 0.74rem; color: #94A3B8; text-decoration: line-through;">
                                    Base: ₹<?= number_format((int)round((float)$product['base_price'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Units in Stock</div>
                            <div style="font-size: 1.45rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                <?= number_format($totalStock) ?>
                            </div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: <?= $stockColor ?>;">
                                <?= $stockLabel ?>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Low Stock Warning</div>
                            <div style="font-size: 1.45rem; font-weight: 800; color: #0F172A; margin-top: 4px;">
                                &le; <?= (int)$lowThresh ?>
                            </div>
                            <div style="font-size: 0.72rem; color: #64748B;">Alert threshold</div>
                        </div>

                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Backorder Rule</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: <?= !empty($product['allow_backorder']) ? '#059669' : '#64748B' ?>; margin-top: 6px;">
                                <?= !empty($product['allow_backorder']) ? 'Enabled' : 'Disabled' ?>
                            </div>
                            <div style="font-size: 0.72rem; color: #94A3B8;">Sales when stock is 0</div>
                        </div>
                    </div>

                    <!-- Multi-Variant & SKU Matrix Table -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                        <div style="padding: 18px 22px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0F172A; margin: 0;">Multi-Variant &amp; SKU Matrix</h3>
                                <div style="font-size: 0.78rem; color: #64748B; margin-top: 2px;">
                                    <?= count($variants) ?> configured variation<?= count($variants) !== 1 ? 's' : '' ?> with dedicated inventory tracking
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($variants)): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                                    <thead>
                                        <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.76rem; font-weight: 700; color: #64748B; text-transform: uppercase;">
                                            <th style="padding: 12px 20px;">Variant Name</th>
                                            <th style="padding: 12px 16px;">Color</th>
                                            <th style="padding: 12px 16px;">Size</th>
                                            <th style="padding: 12px 16px;">SKU Code</th>
                                            <th style="padding: 12px 16px;">Price Override</th>
                                            <th style="padding: 12px 16px;">Stock Qty</th>
                                            <?php if ($canEdit): ?>
                                                <th style="padding: 12px 20px; text-align: right;">Quick Adjust</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($variants as $v): ?>
                                            <?php
                                            $vQty = (int)($v['stock_qty'] ?? 0);
                                            $vAlert = ($vQty <= $lowThresh);
                                            ?>
                                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                                <td style="padding: 14px 20px; font-weight: 700; font-size: 0.88rem; color: #0F172A;">
                                                    <?= htmlspecialchars($v['variant_name'] ?: 'Standard') ?>
                                                </td>
                                                <td style="padding: 14px 16px;">
                                                    <?php if (!empty($v['color_name']) || !empty($v['color_code'])): ?>
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <?php if (!empty($v['color_code'])): ?>
                                                                <span style="width: 16px; height: 16px; border-radius: 50%; background: <?= htmlspecialchars($v['color_code']) ?>; display: inline-block; border: 1px solid rgba(0,0,0,0.15);"></span>
                                                            <?php endif; ?>
                                                            <span style="font-size: 0.84rem; color: #334155;">
                                                                <?= htmlspecialchars($v['color_name'] ?: 'N/A') ?>
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span style="font-size: 0.8rem; color: #94A3B8;">&mdash;</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 14px 16px;">
                                                    <?php if (!empty($v['size'])): ?>
                                                        <span style="font-size: 0.78rem; font-weight: 700; background: #F1F5F9; color: #334155; padding: 2px 8px; border-radius: 4px;">
                                                            <?= htmlspecialchars($v['size']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="font-size: 0.8rem; color: #94A3B8;">Standard</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 14px 16px;">
                                                    <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; background: #DBEAFE; color: #1E40AF; padding: 3px 8px; border-radius: 4px;">
                                                        <?= htmlspecialchars($v['sku']) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 14px 16px; font-weight: 700; font-size: 0.88rem; color: #0F172A;">
                                                    ₹<?= number_format((int)round((float)($v['price_override'] ?? $product['sale_price']))) ?>
                                                </td>
                                                <td style="padding: 14px 16px;">
                                                    <span style="font-size: 0.82rem; font-weight: 800; color: <?= $vQty <= 0 ? '#DC2626' : ($vAlert ? '#D97706' : '#059669') ?>;">
                                                        <?= $vQty ?> units
                                                    </span>
                                                    <?php if ($vAlert): ?>
                                                        <span style="font-size: 0.68rem; background: #FEF3C7; color: #92400E; font-weight: 700; padding: 2px 6px; border-radius: 3px; margin-left: 4px;">
                                                            Low
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($canEdit): ?>
                                                    <td style="padding: 14px 20px; text-align: right;">
                                                        <form action="<?= url('portal/products/' . $product['encrypted_id'] . '/stock') ?>" method="POST" style="display: inline-flex; align-items: center; gap: 6px; margin: 0;">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                                                            <input type="number" name="new_qty" value="<?= $vQty ?>" min="0" max="99999" style="width: 70px; height: 32px; padding: 0 6px; font-weight: 700; text-align: center; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 0.82rem;">
                                                            <button type="submit" style="height: 32px; padding: 0 10px; font-size: 0.78rem; font-weight: 700; background: #4F46E5; color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer;">
                                                                Save
                                                            </button>
                                                        </form>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px 20px; color: #64748B; font-size: 0.85rem;">
                                No variants configured for this garment.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Descriptions & Details Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0 0 12px 0;">Garment Description &amp; Highlights</h3>
                        
                        <?php if (!empty($product['short_description'])): ?>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #334155; margin-bottom: 12px; line-height: 1.6; border-left: 3px solid #4F46E5; padding-left: 12px;">
                                <?= nl2br(htmlspecialchars($product['short_description'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['description'])): ?>
                            <div style="font-size: 0.88rem; color: #64748B; line-height: 1.7;">
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 0.85rem; color: #94A3B8; margin: 0;">No extended description provided.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
