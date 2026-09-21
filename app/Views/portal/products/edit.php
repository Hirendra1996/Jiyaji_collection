<?php
$title = 'Edit ' . htmlspecialchars($product['name']) . ' | Jiyaji LX Staff Portal';
$categories = $categories ?? [];
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
                <a href="<?= url('portal/products/' . $product['encrypted_id']) ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($product['name']) ?></a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Edit Garment</span>
            </div>

            <div class="welcome-banner" style="background: linear-gradient(135deg, #0F172A 0%, #312E81 60%, #4338CA 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(67, 56, 202, 0.3);">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.15); padding: 3px 10px; border-radius: 999px; color: #C7D2FE;">
                        Catalog Editor &bull; Garment #<?= $product['id'] ?>
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 6px 0 4px 0;">
                        Edit: <?= htmlspecialchars($product['name']) ?>
                    </h1>
                    <p style="font-size: 0.88rem; color: #E0E7FF; margin: 0;">
                        Update pricing matrices, variant stock levels, color swatches, and high-resolution gallery photography.
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?= url('portal/products/' . $product['encrypted_id']) ?>" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        View Product
                    </a>
                    <a href="<?= url('portal/products') ?>" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        &larr; Back to Catalog
                    </a>
                </div>
            </div>

            <form action="<?= url('portal/products/' . $product['encrypted_id'] . '/update') ?>" method="POST" enctype="multipart/form-data" id="productEditForm">
                <?= csrf_field() ?>

                <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
                    
                    <!-- Left Column: Core Product Details, Variants & Media -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- 1. General Information -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 14px; margin-bottom: 20px;">
                                <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0 0 4px 0;">1. Product Information</h2>
                                <div style="font-size: 0.8rem; color: #64748B;">Primary title, collection taxonomy, highlights, and full description</div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 18px;">
                                <div>
                                    <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Product Title <span style="color: #EF4444;">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        id="productName" 
                                        value="<?= htmlspecialchars($product['name']) ?>" 
                                        required 
                                        placeholder="e.g. Royal Banarasi Silk Saree" 
                                        style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.9rem; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 8px;"
                                    >
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div>
                                        <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                            URL Slug
                                        </label>
                                        <input 
                                            type="text" 
                                            value="<?= htmlspecialchars($product['slug']) ?>" 
                                            readonly 
                                            style="width: 100%; height: 42px; padding: 0 14px; font-family: monospace; font-size: 0.85rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #F8FAFC; color: #64748B;"
                                        >
                                    </div>
                                    <div>
                                        <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                            Category Taxonomy <span style="color: #EF4444;">*</span>
                                        </label>
                                        <select name="category_id" required style="width: 100%; height: 42px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Short Summary / Highlights
                                    </label>
                                    <input 
                                        type="text" 
                                        name="short_description" 
                                        value="<?= htmlspecialchars($product['short_description'] ?? '') ?>" 
                                        placeholder="Handwoven pure silk saree with antique golden zari border..." 
                                        style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px;"
                                    >
                                </div>

                                <div>
                                    <label style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Detailed Garment Description
                                    </label>
                                    <textarea 
                                        name="description" 
                                        rows="6" 
                                        style="width: 100%; padding: 12px 14px; font-size: 0.88rem; line-height: 1.5; border: 1px solid #CBD5E1; border-radius: 8px; resize: vertical;"
                                    ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Clothing Color & Size Variants Matrix -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">2. Clothing Color &amp; Size Variants</h2>
                                        <span style="background: #EEF2FF; color: #4338CA; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">Apparel Matrix</span>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #64748B; margin-top: 2px;">Maintain fabric colors, apparel sizes, live swatch codes, SKU barcodes, and stock levels</div>
                                </div>
                                <button type="button" onclick="addVariantRow()" style="height: 34px; padding: 0 14px; font-size: 0.8rem; font-weight: 700; color: #4F46E5; background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 8px; cursor: pointer;">
                                    + Add Clothing Variant
                                </button>
                            </div>

                            <!-- Quick Color Preset Toolbar -->
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;">
                                <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Quick-Add Garment Colors:
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <button type="button" onclick="quickAddColor('Maroon', '#800020')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #800020; display: inline-block;"></span> Maroon
                                    </button>
                                    <button type="button" onclick="quickAddColor('Royal Blue', '#1E3A8A')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #1E3A8A; display: inline-block;"></span> Royal Blue
                                    </button>
                                    <button type="button" onclick="quickAddColor('Emerald Green', '#046307')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #046307; display: inline-block;"></span> Emerald Green
                                    </button>
                                    <button type="button" onclick="quickAddColor('Mustard Gold', '#D4AF37')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #D4AF37; display: inline-block;"></span> Mustard Gold
                                    </button>
                                    <button type="button" onclick="quickAddColor('Rani Pink', '#DB2777')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #DB2777; display: inline-block;"></span> Rani Pink
                                    </button>
                                    <button type="button" onclick="quickAddColor('Jet Black', '#111827')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #111827; display: inline-block;"></span> Jet Black
                                    </button>
                                </div>
                            </div>

                            <!-- Variants Table -->
                            <div style="border: 1px solid #E2E8F0; border-radius: 8px; overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; text-align: left;" id="variantsTable">
                                    <thead>
                                        <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.76rem; font-weight: 700; color: #64748B; text-transform: uppercase;">
                                            <th style="padding: 10px 14px; min-width: 180px;">Color Swatch &amp; Name *</th>
                                            <th style="padding: 10px 12px; width: 120px;">Size</th>
                                            <th style="padding: 10px 14px; min-width: 150px;">SKU Code *</th>
                                            <th style="padding: 10px 12px; width: 130px;">Price Override (₹)</th>
                                            <th style="padding: 10px 12px; width: 100px;">Stock *</th>
                                            <th style="padding: 10px 12px; text-align: center; width: 60px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantsBody">
                                        <?php if (!empty($product['variants'])): ?>
                                            <?php foreach ($product['variants'] as $idx => $v): ?>
                                                <?php
                                                $cCode = !empty($v['color_code']) ? $v['color_code'] : '#800020';
                                                $cName = !empty($v['color_name']) ? $v['color_name'] : ($v['variant_name'] ?? 'Standard');
                                                $sVal  = !empty($v['size']) ? $v['size'] : 'Free Size';
                                                ?>
                                                <tr data-row-idx="<?= $idx ?>" style="border-bottom: 1px solid #F1F5F9;">
                                                    <td style="padding: 10px 14px;">
                                                        <input type="hidden" name="variants[<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <input type="color" name="variants[<?= $idx ?>][color_code]" value="<?= htmlspecialchars($cCode) ?>" class="color-picker-input" onchange="syncColorSwatch(this, <?= $idx ?>)" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; padding: 0; background: transparent;">
                                                            <input type="text" name="variants[<?= $idx ?>][color_name]" value="<?= htmlspecialchars($cName) ?>" class="form-input color-name-input" style="height: 36px; font-size: 0.85rem; font-weight: 600; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" oninput="onVariantColorChange(<?= $idx ?>)" required>
                                                            <input type="hidden" name="variants[<?= $idx ?>][variant_name]" class="variant-name-hidden" value="<?= htmlspecialchars($v['variant_name']) ?>">
                                                        </div>
                                                    </td>
                                                    <td style="padding: 10px 12px;">
                                                        <select name="variants[<?= $idx ?>][size]" class="form-input variant-size-select" style="height: 36px; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; background: #fff;" onchange="onVariantSizeChange(<?= $idx ?>)">
                                                            <?php
                                                            $sizes = ['Free Size', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Unstitched'];
                                                            foreach ($sizes as $sz):
                                                            ?>
                                                                <option value="<?= $sz ?>" <?= ($sVal === $sz) ? 'selected' : '' ?>><?= $sz ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td style="padding: 10px 14px;">
                                                        <input type="text" name="variants[<?= $idx ?>][sku]" value="<?= htmlspecialchars($v['sku']) ?>" class="form-input variant-sku-input" style="height: 36px; font-family: monospace; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" required>
                                                    </td>
                                                    <td style="padding: 10px 12px;">
                                                        <input type="number" step="1" min="0" name="variants[<?= $idx ?>][price_override]" value="<?= $v['price_override'] !== null ? (int)round((float)$v['price_override']) : '' ?>" class="form-input variant-price-input" style="height: 36px; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" placeholder="Base">
                                                    </td>
                                                    <td style="padding: 10px 12px;">
                                                        <input type="number" name="variants[<?= $idx ?>][stock_qty]" value="<?= (int)$v['stock_qty'] ?>" min="0" class="form-input" style="height: 36px; font-size: 0.85rem; font-weight: 700; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" required>
                                                    </td>
                                                    <td style="padding: 10px 12px; text-align: center;">
                                                        <?php if (count($product['variants']) > 1): ?>
                                                            <button type="button" onclick="this.closest('tr').remove(); updateMediaVariantOptions();" style="background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; border-radius: 6px; width: 28px; height: 28px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; line-height: 1;" title="Remove Variant">&times;</button>
                                                        <?php else: ?>
                                                            <span style="color: #94A3B8; font-size: 0.75rem; font-weight: 600;">Primary</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr data-row-idx="0" style="border-bottom: 1px solid #F1F5F9;">
                                                <td style="padding: 10px 14px;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <input type="color" name="variants[0][color_code]" value="#800020" class="color-picker-input" onchange="syncColorSwatch(this, 0)" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; padding: 0; background: transparent;">
                                                        <input type="text" name="variants[0][color_name]" value="Standard" class="form-input color-name-input" style="height: 36px; font-size: 0.85rem; font-weight: 600; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" required>
                                                        <input type="hidden" name="variants[0][variant_name]" class="variant-name-hidden" value="Standard">
                                                    </div>
                                                </td>
                                                <td style="padding: 10px 12px;">
                                                    <select name="variants[0][size]" class="form-input variant-size-select" style="height: 36px; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; background: #fff;">
                                                        <option value="Free Size" selected>Free Size</option>
                                                        <option value="S">S</option>
                                                        <option value="M">M</option>
                                                        <option value="L">L</option>
                                                        <option value="XL">XL</option>
                                                    </select>
                                                </td>
                                                <td style="padding: 10px 14px;">
                                                    <input type="text" name="variants[0][sku]" value="JIY-SKU-001" class="form-input variant-sku-input" style="height: 36px; font-family: monospace; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" required>
                                                </td>
                                                <td style="padding: 10px 12px;">
                                                    <input type="number" step="1" min="0" name="variants[0][price_override]" value="<?= (int)round((float)$product['base_price']) ?>" class="form-input" style="height: 36px; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;">
                                                </td>
                                                <td style="padding: 10px 12px;">
                                                    <input type="number" name="variants[0][stock_qty]" value="10" min="0" class="form-input" style="height: 36px; font-size: 0.85rem; font-weight: 700; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" required>
                                                </td>
                                                <td style="padding: 10px 12px; text-align: center;">
                                                    <span style="color: #94A3B8; font-size: 0.75rem; font-weight: 600;">Primary</span>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; color: #64748B;">
                                    Tip: Maintain distinctive SKUs and color swatches for each garment option in your collection.
                                </span>
                                <button type="button" onclick="generateSkuSuggestions()" style="background: none; border: none; font-size: 0.76rem; color: #4F46E5; font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    Re-Generate All SKUs
                                </button>
                            </div>
                        </div>

                        <!-- 3. Direct Garment Media Gallery & File Uploads -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 14px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0;">3. High-Resolution Garment Media Gallery</h2>
                                    <span style="background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;">Direct File Upload</span>
                                </div>
                                <div style="font-size: 0.8rem; color: #64748B; margin-top: 2px;">Manage current photography, set primary cover, link images to colors, or upload new photos directly.</div>
                            </div>

                            <!-- Existing Media Gallery -->
                            <?php $existingImages = $product['images'] ?? []; ?>
                            <?php if (!empty($existingImages)): ?>
                                <div style="margin-bottom: 20px;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A; margin-bottom: 10px;">
                                        Current Catalog Photos (<?= count($existingImages) ?>)
                                    </div>
                                    <div id="existingMediaGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;">
                                        <?php foreach ($existingImages as $exIdx => $exImg): ?>
                                            <?php
                                            $isPrimary = !empty($exImg['is_primary']);
                                            $resolvedImgUrl = image_url($exImg['url']);
                                            ?>
                                            <div class="existing-media-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                <input type="hidden" name="existing_images[<?= $exIdx ?>][url]" value="<?= htmlspecialchars($exImg['url']) ?>">
                                                
                                                <div style="position: relative; width: 100%; height: 160px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                                    <img src="<?= htmlspecialchars($resolvedImgUrl) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                                    
                                                    <!-- Delete Existing Image Button -->
                                                    <button type="button" onclick="this.closest('.existing-media-card').remove(); updateExistingCount();" style="position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border-radius: 50%; background: rgba(220, 38, 38, 0.85); color: #fff; border: none; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Remove this photo from product">
                                                        &times;
                                                    </button>

                                                    <!-- Primary Badge Tag -->
                                                    <label style="position: absolute; top: 6px; left: 6px; display: flex; align-items: center; gap: 4px; background: <?= $isPrimary ? '#4F46E5' : 'rgba(0,0,0,0.6)' ?>; color: #fff; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; font-weight: 700; cursor: pointer;">
                                                        <input type="radio" name="primary_image_choice" value="existing_<?= $exIdx ?>" <?= $isPrimary ? 'checked' : '' ?> onchange="updatePrimaryBadges()" style="margin: 0; width: 12px; height: 12px; accent-color: #fff;">
                                                        <span><?= $isPrimary ? '★ Primary Cover' : 'Set Cover' ?></span>
                                                    </label>
                                                </div>

                                                <div style="padding: 10px; display: flex; flex-direction: column; gap: 6px;">
                                                    <div style="font-size: 0.72rem; color: #64748B; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?= htmlspecialchars(basename($exImg['url'])) ?>
                                                    </div>
                                                    <div style="margin-top: 4px;">
                                                        <label style="font-size: 0.68rem; font-weight: 700; color: #64748B; display: block; margin-bottom: 2px;">
                                                            Linked Color:
                                                        </label>
                                                        <select name="existing_images[<?= $exIdx ?>][variant_id]" class="form-input" style="height: 28px; font-size: 0.74rem; padding: 2px 6px; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; background: #fff;">
                                                            <option value="">All Colors (Lookbook)</option>
                                                            <?php foreach ($product['variants'] as $vOpt): ?>
                                                                <option value="<?= $vOpt['id'] ?>" <?= (!empty($exImg['variant_id']) && $exImg['variant_id'] == $vOpt['id']) ? 'selected' : '' ?>>
                                                                    Color: <?= htmlspecialchars($vOpt['variant_name'] ?: 'Variant') ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Upload Additional Photos Drop Zone -->
                            <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A; margin-bottom: 8px;">
                                Attach New High-Resolution Photos:
                            </div>
                            <div id="mediaDropZone" onclick="document.getElementById('mediaFileInput').click()" style="border: 2px dashed #CBD5E1; border-radius: 12px; padding: 26px 20px; text-align: center; background: #F8FAFC; cursor: pointer; transition: all 0.2s ease;">
                                <input type="file" id="mediaFileInput" name="media_files[]" multiple accept="image/png,image/jpeg,image/webp,image/gif,image/avif" style="display: none;" onchange="handleFileSelect(this.files)">
                                
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #EEF2FF; display: flex; align-items: center; justify-content: center; color: #4F46E5;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.88rem; font-weight: 700; color: #0F172A;">
                                            Click to add more photos or drag and drop here
                                        </div>
                                        <div style="font-size: 0.74rem; color: #64748B;">WebP, JPEG, PNG up to 10MB each</div>
                                    </div>
                                </div>
                            </div>

                            <!-- New Previews Grid -->
                            <div id="mediaPreviewContainer" style="display: none; margin-top: 16px;">
                                <div style="font-size: 0.8rem; font-weight: 700; color: #0F172A; margin-bottom: 8px;">
                                    Newly Selected Photos (<span id="mediaCountBadge">0</span>)
                                </div>
                                <div id="mediaPreviewGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Commercials, Status & Meta -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Publishing Status & Action Card -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <h3 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">Publishing &amp; Save</h3>
                            
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                    Catalog Status
                                </label>
                                <select name="status" style="width: 100%; height: 40px; padding: 0 10px; font-size: 0.85rem; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff;">
                                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>🟢 Active &amp; Published</option>
                                    <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>🟡 Draft (Hidden)</option>
                                    <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>⚪ Archived</option>
                                </select>
                            </div>

                            <button type="submit" style="width: 100%; height: 44px; background: #4F46E5; color: #FFFFFF; font-weight: 800; font-size: 0.92rem; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35); transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Update Garment Details</span>
                            </button>
                        </div>

                        <!-- Pricing & Inventory Parameters Card -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <h3 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">Pricing &amp; Inventory</h3>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Base / MRP Price (₹) <span style="color: #EF4444;">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0" 
                                        name="base_price" 
                                        value="<?= (int)round((float)$product['base_price']) ?>" 
                                        required 
                                        style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.92rem; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 8px;"
                                    >
                                </div>

                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Selling / Offer Price (₹)
                                    </label>
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0" 
                                        name="sale_price" 
                                        value="<?= (int)round((float)$product['sale_price']) ?>" 
                                        style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.92rem; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 8px;"
                                    >
                                </div>

                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Low Stock Alert Threshold
                                    </label>
                                    <input 
                                        type="number" 
                                        name="low_stock_threshold" 
                                        value="<?= (int)($product['low_stock_threshold'] ?? 10) ?>" 
                                        min="1" 
                                        style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.9rem; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 8px;"
                                    >
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                                    <input type="checkbox" name="allow_backorder" value="1" id="chkBackorder" <?= !empty($product['allow_backorder']) ? 'checked' : '' ?> style="width: 16px; height: 16px; accent-color: #4F46E5; cursor: pointer;">
                                    <label for="chkBackorder" style="font-size: 0.82rem; font-weight: 600; color: #0F172A; cursor: pointer;">
                                        Allow Backorders (Continue sales when out of stock)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Metadata Card -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <h3 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">Search Engine SEO</h3>

                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Meta Title
                                    </label>
                                    <input 
                                        type="text" 
                                        name="meta_title" 
                                        value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>" 
                                        style="width: 100%; height: 38px; padding: 0 12px; font-size: 0.85rem; border: 1px solid #CBD5E1; border-radius: 8px;"
                                    >
                                </div>

                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Meta Description
                                    </label>
                                    <textarea 
                                        name="meta_description" 
                                        rows="3" 
                                        style="width: 100%; padding: 8px 12px; font-size: 0.85rem; border: 1px solid #CBD5E1; border-radius: 8px; resize: vertical;"
                                    ><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

            <script>
            let currentVariantIndex = <?= count($product['variants'] ?? [0]) ?>;
            let currentFiles = [];

            function syncColorSwatch(picker, rowIdx) {
                const colorHex = picker.value;
                const row = document.querySelector(`tr[data-row-idx="${rowIdx}"]`);
                if (!row) return;
                const cInput = row.querySelector('.color-name-input');
                if (cInput && (!cInput.value.trim() || !cInput.dataset.modified)) {
                    cInput.value = colorHex;
                    onVariantColorChange(rowIdx);
                }
            }

            function addVariantRow(presetName = '', presetColor = '#4F46E5') {
                currentVariantIndex++;
                const idx = currentVariantIndex;
                const tbody = document.getElementById('variantsBody');

                const tr = document.createElement('tr');
                tr.setAttribute('data-row-idx', idx);
                tr.style.borderBottom = '1px solid #F1F5F9';

                const defaultName = presetName || `Variant ${idx + 1}`;

                tr.innerHTML = `
                    <td style="padding: 10px 14px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="color" name="variants[${idx}][color_code]" value="${presetColor}" class="color-picker-input" onchange="syncColorSwatch(this, ${idx})" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; padding: 0; background: transparent;">
                            <input type="text" name="variants[${idx}][color_name]" value="${defaultName}" class="form-input color-name-input" style="height: 36px; font-size: 0.85rem; font-weight: 600; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" oninput="onVariantColorChange(${idx})" required>
                            <input type="hidden" name="variants[${idx}][variant_name]" class="variant-name-hidden" value="${defaultName} / Free Size">
                        </div>
                    </td>
                    <td style="padding: 10px 12px;">
                        <select name="variants[${idx}][size]" class="form-input variant-size-select" style="height: 36px; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; background: #fff;" onchange="onVariantSizeChange(${idx})">
                            <option value="Free Size" selected>Free Size</option>
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="2XL">2XL</option>
                            <option value="3XL">3XL</option>
                            <option value="Unstitched">Unstitched</option>
                        </select>
                    </td>
                    <td style="padding: 10px 14px;">
                        <input type="text" name="variants[${idx}][sku]" class="form-input variant-sku-input" style="height: 36px; font-family: monospace; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" placeholder="e.g. JIY-VAR-FS" required>
                    </td>
                    <td style="padding: 10px 12px;">
                        <input type="number" step="1" min="0" name="variants[${idx}][price_override]" class="form-input variant-price-input" style="height: 36px; font-size: 0.85rem; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" placeholder="Base">
                    </td>
                    <td style="padding: 10px 12px;">
                        <input type="number" name="variants[${idx}][stock_qty]" value="10" min="0" class="form-input" style="height: 36px; font-size: 0.85rem; font-weight: 700; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 8px;" required>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                        <button type="button" onclick="this.closest('tr').remove(); updateMediaVariantOptions();" style="background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; border-radius: 6px; width: 28px; height: 28px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; line-height: 1;" title="Remove Variant">&times;</button>
                    </td>
                `;

                tbody.appendChild(tr);
                onVariantColorChange(idx);
                updateMediaVariantOptions();
            }

            function quickAddColor(name, hex) {
                addVariantRow(name, hex);
            }

            function onVariantColorChange(rowIdx) {
                const row = document.querySelector(`tr[data-row-idx="${rowIdx}"]`);
                if (!row) return;

                const cInput = row.querySelector('.color-name-input');
                const sInput = row.querySelector('.variant-size-select');
                const hInput = row.querySelector('.variant-name-hidden');
                const skuInput = row.querySelector('.variant-sku-input');
                
                cInput.dataset.modified = '1';
                const cVal = cInput.value.trim();
                const sVal = sInput ? sInput.value : 'Free Size';
                hInput.value = cVal ? (cVal + ' / ' + sVal) : sVal;

                if (!skuInput.value.trim()) {
                    const titleVal = document.getElementById('productName').value.trim();
                    const prefix = titleVal ? titleVal.replace(/[^a-zA-Z]/g, '').slice(0, 3).toUpperCase() : 'JIY';
                    const colCode = cVal ? cVal.replace(/[^a-zA-Z]/g, '').slice(0, 3).toUpperCase() : 'VAR';
                    const sizeCode = sVal.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 3) || 'FS';
                    skuInput.value = `JIY-${prefix}-${colCode}-${sizeCode}`;
                }

                updateMediaVariantOptions();
            }

            function onVariantSizeChange(rowIdx) {
                onVariantColorChange(rowIdx);
            }

            function generateSkuSuggestions() {
                const name = document.getElementById('productName').value.trim();
                const prefix = name ? name.replace(/[^a-zA-Z]/g, '').slice(0, 3).toUpperCase() : 'JIY';
                const rows = document.querySelectorAll('#variantsBody tr');
                rows.forEach((r, idx) => {
                    const cInput = r.querySelector('.color-name-input');
                    const sInput = r.querySelector('.variant-size-select');
                    const skuInput = r.querySelector('.variant-sku-input');
                    if (skuInput) {
                        const colCode = cInput && cInput.value.trim() ? cInput.value.trim().replace(/[^a-zA-Z]/g, '').slice(0, 3).toUpperCase() : 'COL';
                        const sizeCode = sInput && sInput.value ? sInput.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 3) : 'FS';
                        skuInput.value = `JIY-${prefix}-${colCode}-${sizeCode}-${idx + 1}`;
                    }
                });
            }

            function updateExistingCount() {
                // Keep UI aligned
            }

            // =========================================================================
            // MEDIA FILE UPLOAD SYSTEM
            // =========================================================================
            const dropZone = document.getElementById('mediaDropZone');
            const fileInput = document.getElementById('mediaFileInput');
            const previewContainer = document.getElementById('mediaPreviewContainer');
            const previewGrid = document.getElementById('mediaPreviewGrid');
            const countBadge = document.getElementById('mediaCountBadge');

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.style.borderColor = '#4F46E5';
                    dropZone.style.background = '#EEF2FF';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.style.borderColor = '#CBD5E1';
                    dropZone.style.background = '#F8FAFC';
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    handleFileSelect(dt.files);
                }
            });

            function handleFileSelect(files) {
                if (!files || files.length === 0) return;

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type.startsWith('image/')) continue;
                    currentFiles.push(file);
                }

                syncFileInput();
                renderMediaPreviews();
            }

            function syncFileInput() {
                const dataTransfer = new DataTransfer();
                currentFiles.forEach(f => dataTransfer.items.add(f));
                fileInput.files = dataTransfer.files;
            }

            function removeFile(index) {
                currentFiles.splice(index, 1);
                syncFileInput();
                renderMediaPreviews();
            }

            function renderMediaPreviews() {
                if (currentFiles.length === 0) {
                    previewContainer.style.display = 'none';
                    countBadge.textContent = '0';
                    previewGrid.innerHTML = '';
                    return;
                }

                previewContainer.style.display = 'block';
                countBadge.textContent = currentFiles.length;
                previewGrid.innerHTML = '';

                currentFiles.forEach((file, idx) => {
                    const card = document.createElement('div');
                    card.style.cssText = 'background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05);';

                    const isPrimary = (idx === 0 && document.querySelectorAll('.existing-media-card').length === 0);
                    const formattedSize = (file.size / 1024).toFixed(0) + ' KB';

                    card.innerHTML = `
                        <div style="position: relative; width: 100%; height: 160px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img id="previewImg_${idx}" src="" style="width: 100%; height: 100%; object-fit: cover;">
                            
                            <button type="button" onclick="removeFile(${idx})" style="position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border-radius: 50%; background: rgba(0,0,0,0.65); color: #fff; border: none; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Remove Photo">
                                &times;
                            </button>

                            <label style="position: absolute; top: 6px; left: 6px; display: flex; align-items: center; gap: 4px; background: ${isPrimary ? '#4F46E5' : 'rgba(0,0,0,0.6)'}; color: #fff; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; font-weight: 700; cursor: pointer;">
                                <input type="radio" name="primary_image_choice" value="upload_${idx}" ${isPrimary ? 'checked' : ''} onchange="updatePrimaryBadges()" style="margin: 0; width: 12px; height: 12px; accent-color: #fff;">
                                <span>${isPrimary ? '★ Primary Cover' : 'Set Cover'}</span>
                            </label>
                        </div>

                        <div style="padding: 10px; display: flex; flex-direction: column; gap: 6px;">
                            <div style="font-size: 0.76rem; font-weight: 600; color: #0F172A; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${file.name}">
                                ${file.name}
                            </div>
                            <div style="font-size: 0.68rem; color: #64748B; display: flex; justify-content: space-between;">
                                <span>${formattedSize}</span>
                                <span style="text-transform: uppercase;">${file.name.split('.').pop()}</span>
                            </div>

                            <div style="margin-top: 4px;">
                                <label style="font-size: 0.68rem; font-weight: 700; color: #64748B; display: block; margin-bottom: 2px;">
                                    Link to Color:
                                </label>
                                <select name="media_variant_index[${idx}]" class="media-variant-select form-input" style="height: 28px; font-size: 0.74rem; padding: 2px 6px; width: 100%; border: 1px solid #CBD5E1; border-radius: 6px; background: #fff;">
                                    <option value="">All Colors (Lookbook)</option>
                                    ${getVariantSelectOptions()}
                                </select>
                            </div>
                        </div>
                    `;

                    previewGrid.appendChild(card);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.getElementById(`previewImg_${idx}`);
                        if (img) img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            function updatePrimaryBadges() {
                const radios = document.querySelectorAll('input[name="primary_image_choice"]');
                radios.forEach(radio => {
                    const badge = radio.closest('label');
                    if (badge) {
                        if (radio.checked) {
                            badge.style.background = '#4F46E5';
                            badge.querySelector('span').textContent = '★ Primary Cover';
                        } else {
                            badge.style.background = 'rgba(0,0,0,0.6)';
                            badge.querySelector('span').textContent = 'Set Cover';
                        }
                    }
                });
            }

            function getVariantSelectOptions() {
                let optionsHtml = '';
                const rows = document.querySelectorAll('#variantsBody tr');
                rows.forEach((row, i) => {
                    const cInput = row.querySelector('.color-name-input');
                    const sInput = row.querySelector('.variant-size-select');
                    const cName = cInput && cInput.value.trim() ? cInput.value.trim() : `Variant ${i + 1}`;
                    const size = sInput ? sInput.value : '';
                    const label = size ? `${cName} (${size})` : cName;
                    optionsHtml += `<option value="${i}">Color: ${label}</option>`;
                });
                return optionsHtml;
            }

            function updateMediaVariantOptions() {
                const selects = document.querySelectorAll('.media-variant-select');
                selects.forEach(sel => {
                    const currentVal = sel.value;
                    sel.innerHTML = `<option value="">All Colors (Lookbook)</option>` + getVariantSelectOptions();
                    sel.value = currentVal;
                });
            }
            </script>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
