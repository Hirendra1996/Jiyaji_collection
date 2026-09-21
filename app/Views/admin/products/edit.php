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
                        <a href="<?= url('admin/products') ?>" style="color: var(--brand-blue);">Products</a>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Edit Product</span>
                    </div>
                    <h1 class="welcome-title">Edit: <?= htmlspecialchars($product['name']) ?></h1>
                    <p class="welcome-subtitle">Modify garment specifications, variant SKU matrix, pricing tiers, and gallery photography.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/products') ?>" class="btn-export">
                        &larr; Back to Products Ledger
                    </a>
                </div>
            </div>

            <form action="<?= url('admin/products/' . $product['encrypted_id'] . '/update') ?>" method="POST" enctype="multipart/form-data" id="productEditForm">
                <?= csrf_field() ?>

                <div class="product-form-grid">
                    
                    <!-- Left Column: Core Product Details & Variants -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- 1. General Information -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 20px;">
                                <div>
                                    <h2 class="panel-title">1. Product Information</h2>
                                    <div class="panel-subtitle">Primary title, luxury collection taxonomy, and descriptions</div>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 18px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Product Title <span style="color: #ef4444;">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        id="productName" 
                                        required 
                                        value="<?= htmlspecialchars($product['name']) ?>" 
                                        class="form-input" 
                                        style="height: 42px; font-weight: 600;"
                                    >
                                </div>

                                <div class="grid-2-cols">
                                    <div>
                                        <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                            URL Slug
                                        </label>
                                        <input 
                                            type="text" 
                                            value="<?= htmlspecialchars($product['slug']) ?>" 
                                            readonly 
                                            class="form-input" 
                                            style="height: 42px; font-family: monospace; font-size: 0.85rem; background: #f8fafc; color: var(--text-muted);"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                            Category Taxonomy <span style="color: #ef4444;">*</span>
                                        </label>
                                        <select name="category_id" required class="form-input" style="height: 42px;">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Short Summary / Highlights
                                    </label>
                                    <input 
                                        type="text" 
                                        name="short_description" 
                                        value="<?= htmlspecialchars($product['short_description'] ?? '') ?>" 
                                        placeholder="Handwoven pure silk saree with antique golden zari border..." 
                                        class="form-input" 
                                        style="height: 42px;"
                                    >
                                </div>

                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Detailed Product Description & Care Instructions
                                    </label>
                                    <textarea 
                                        name="description" 
                                        rows="6" 
                                        class="form-input" 
                                        style="padding: 12px; font-size: 0.88rem; line-height: 1.5;"
                                    ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Clothing Color & Size Variants Matrix -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 16px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h2 class="panel-title">2. Clothing Color & Size Variants</h2>
                                        <span style="background: rgba(30, 58, 138, 0.1); color: var(--brand-blue); padding: 2px 8px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700;">Apparel Matrix</span>
                                    </div>
                                    <div class="panel-subtitle">Maintain fabric color palettes, apparel sizes, live swatch codes, SKU barcodes, and stock levels</div>
                                </div>
                                <button type="button" class="btn-export" style="height: 34px; padding: 0 12px; font-size: 0.8rem; font-weight: 700; color: var(--brand-blue);" onclick="addVariantRow()">
                                    + Add Clothing Variant
                                </button>
                            </div>

                            <!-- Quick Color Preset Toolbar -->
                            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;">
                                <div style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Quick-Add Garment Colors:
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Maroon', '#800020')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #800020; display: inline-block;"></span> Maroon
                                    </button>
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Royal Blue', '#1E3A8A')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #1E3A8A; display: inline-block;"></span> Royal Blue
                                    </button>
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Emerald Green', '#046307')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #046307; display: inline-block;"></span> Emerald Green
                                    </button>
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Mustard Gold', '#D4AF37')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #D4AF37; display: inline-block;"></span> Mustard Gold
                                    </button>
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Rani Pink', '#DB2777')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #DB2777; display: inline-block;"></span> Rani Pink
                                    </button>
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Jet Black', '#111827')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #111827; display: inline-block;"></span> Jet Black
                                    </button>
                                    <button type="button" class="color-preset-pill" onclick="addVariantRow('Wine', '#581845')" style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 10px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                        <span style="width: 12px; height: 12px; border-radius: 50%; background: #581845; display: inline-block;"></span> Wine
                                    </button>
                                </div>
                            </div>

                            <div class="orders-table-wrapper" style="border: 1px solid var(--border-color); border-radius: 8px; overflow-x: auto;">
                                <table class="orders-table" id="variantsTable">
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <th style="min-width: 180px;">Color Swatch & Name *</th>
                                            <th style="width: 120px;">Size</th>
                                            <th style="min-width: 150px;">SKU Code *</th>
                                            <th style="width: 130px;">Price Override (₹)</th>
                                            <th style="width: 100px;">Stock *</th>
                                            <th style="text-align: center; width: 60px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantsBody">
                                        <?php if (!empty($product['variants'])): ?>
                                            <?php foreach ($product['variants'] as $idx => $v): ?>
                                                <?php
                                                $cName = $v['color_name'] ?? '';
                                                $cCode = $v['color_code'] ?? '#800020';
                                                $sVal  = $v['size'] ?? 'Free Size';
                                                if (empty($cName) && !empty($v['variant_name'])) {
                                                    $parts = explode('/', $v['variant_name']);
                                                    $cName = trim($parts[0]);
                                                    if (isset($parts[1]) && empty($v['size'])) {
                                                        $sVal = trim($parts[1]);
                                                    }
                                                }
                                                ?>
                                                <tr data-row-idx="<?= $idx ?>" data-variant-id="<?= $v['id'] ?>">
                                                    <input type="hidden" name="variants[<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                                                    <td>
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <input type="color" name="variants[<?= $idx ?>][color_code]" value="<?= htmlspecialchars($cCode) ?>" class="color-picker-input" onchange="syncColorSwatch(this, <?= $idx ?>)" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; padding: 0; background: transparent;" title="Pick Color Swatch">
                                                            <input type="text" name="variants[<?= $idx ?>][color_name]" value="<?= htmlspecialchars($cName ?: $v['variant_name']) ?>" class="form-input color-name-input" style="height: 36px; font-size: 0.85rem; font-weight: 600;" placeholder="Color Name" oninput="onVariantColorChange(<?= $idx ?>)" required>
                                                            <input type="hidden" name="variants[<?= $idx ?>][variant_name]" class="variant-name-hidden" value="<?= htmlspecialchars($v['variant_name'] ?: 'Standard') ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <select name="variants[<?= $idx ?>][size]" class="form-input variant-size-select" style="height: 36px; font-size: 0.85rem;" onchange="onVariantSizeChange(<?= $idx ?>)">
                                                            <?php
                                                            $sizes = ['Free Size', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Unstitched'];
                                                            foreach ($sizes as $sz):
                                                            ?>
                                                                <option value="<?= $sz ?>" <?= ($sVal === $sz) ? 'selected' : '' ?>><?= $sz ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="variants[<?= $idx ?>][sku]" value="<?= htmlspecialchars($v['sku']) ?>" class="form-input variant-sku-input" style="height: 36px; font-family: monospace; font-size: 0.85rem;" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="1" min="0" name="variants[<?= $idx ?>][price_override]" value="<?= $v['price_override'] !== null ? (int)round((float)$v['price_override']) : '' ?>" class="form-input variant-price-input" style="height: 36px; font-size: 0.85rem;">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="variants[<?= $idx ?>][stock_qty]" value="<?= (int)$v['stock_qty'] ?>" min="0" class="form-input" style="height: 36px; font-size: 0.85rem; font-weight: 700;" required>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php if (count($product['variants']) > 1): ?>
                                                            <button type="button" onclick="removeVariantRow(this)" style="background: none; border: none; color: #ef4444; font-size: 1.1rem; cursor: pointer; padding: 4px;" title="Remove Variant">
                                                                &times;
                                                            </button>
                                                        <?php else: ?>
                                                            <span style="color: var(--text-muted); font-size: 0.75rem;">Primary</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr data-row-idx="0">
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <input type="color" name="variants[0][color_code]" value="#800020" class="color-picker-input" onchange="syncColorSwatch(this, 0)" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; padding: 0; background: transparent;">
                                                        <input type="text" name="variants[0][color_name]" value="Standard" class="form-input color-name-input" style="height: 36px; font-size: 0.85rem; font-weight: 600;" required>
                                                        <input type="hidden" name="variants[0][variant_name]" class="variant-name-hidden" value="Standard">
                                                    </div>
                                                </td>
                                                <td>
                                                    <select name="variants[0][size]" class="form-input variant-size-select" style="height: 36px; font-size: 0.85rem;">
                                                        <option value="Free Size" selected>Free Size</option>
                                                        <option value="S">S</option>
                                                        <option value="M">M</option>
                                                        <option value="L">L</option>
                                                        <option value="XL">XL</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="variants[0][sku]" value="JIY-SKU-001" class="form-input variant-sku-input" style="height: 36px; font-family: monospace; font-size: 0.85rem;" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="1" min="0" name="variants[0][price_override]" value="<?= (int)round((float)$product['base_price']) ?>" class="form-input" style="height: 36px; font-size: 0.85rem;">
                                                </td>
                                                <td>
                                                    <input type="number" name="variants[0][stock_qty]" value="10" min="0" class="form-input" style="height: 36px; font-size: 0.85rem; font-weight: 700;" required>
                                                </td>
                                                <td style="text-align: center;">
                                                    <span style="color: var(--text-muted); font-size: 0.75rem;">Primary</span>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; color: var(--text-muted);">
                                    Tip: Maintain distinctive SKUs and color swatches for each garment option in your collection.
                                </span>
                                <button type="button" onclick="generateSkuSuggestions()" style="background: none; border: none; font-size: 0.76rem; color: var(--brand-purple); font-weight: 700; cursor: pointer; text-decoration: underline;">
                                    Re-Generate All SKUs
                                </button>
                            </div>
                        </div>

                        <!-- 3. Direct Garment Media Gallery & File Uploads -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 16px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h2 class="panel-title">3. High-Resolution Garment Media Gallery</h2>
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 2px 8px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700;">Direct File Upload</span>
                                    </div>
                                    <div class="panel-subtitle">Manage existing product photography, set the primary cover photo, link images to color variants, and upload new photos directly.</div>
                                </div>
                            </div>

                            <!-- Existing Media Gallery -->
                            <?php $existingImages = $product['images'] ?? []; ?>
                            <?php if (!empty($existingImages)): ?>
                                <div style="margin-bottom: 20px;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">
                                        Current Catalog Photos (<?= count($existingImages) ?>)
                                    </div>
                                    <div id="existingMediaGrid" class="media-preview-grid">
                                        <?php foreach ($existingImages as $exIdx => $exImg): ?>
                                            <?php
                                            $isPrimary = !empty($exImg['is_primary']);
                                            $resolvedImgUrl = image_url($exImg['url']);
                                            ?>
                                            <div class="existing-media-card" style="background: #fff; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                <input type="hidden" name="existing_images[<?= $exIdx ?>][url]" value="<?= htmlspecialchars($exImg['url']) ?>">
                                                
                                                <div style="position: relative; width: 100%; height: 160px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                                    <img src="<?= htmlspecialchars($resolvedImgUrl) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                                    
                                                    <!-- Delete Existing Image Button -->
                                                    <button type="button" onclick="this.closest('.existing-media-card').remove(); updateExistingCount();" style="position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border-radius: 50%; background: rgba(239, 68, 68, 0.85); color: #fff; border: none; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s;" title="Remove this photo from product">
                                                        &times;
                                                    </button>

                                                    <!-- Primary Badge Tag -->
                                                    <label style="position: absolute; top: 6px; left: 6px; display: flex; align-items: center; gap: 4px; background: <?= $isPrimary ? '#2563eb' : 'rgba(0,0,0,0.6)' ?>; color: #fff; padding: 2px 8px; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; cursor: pointer;">
                                                        <input type="radio" name="primary_image_choice" value="existing_<?= $exIdx ?>" <?= $isPrimary ? 'checked' : '' ?> onchange="updatePrimaryBadges()" style="margin: 0; width: 12px; height: 12px; accent-color: #fff;">
                                                        <span><?= $isPrimary ? '★ Primary Cover' : 'Set Cover' ?></span>
                                                    </label>
                                                </div>

                                                <div style="padding: 10px; display: flex; flex-direction: column; gap: 6px;">
                                                    <div style="font-size: 0.72rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?= basename($exImg['url']) ?>
                                                    </div>
                                                    <div>
                                                        <label style="font-size: 0.68rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 2px;">
                                                            Linked Color:
                                                        </label>
                                                        <select name="existing_images[<?= $exIdx ?>][variant_id]" class="existing-variant-select form-input" style="height: 28px; font-size: 0.74rem; padding: 2px 6px;">
                                                            <option value="">All Colors (Lookbook)</option>
                                                            <?php foreach ($product['variants'] as $v): ?>
                                                                <option value="<?= $v['id'] ?>" <?= (!empty($exImg['variant_id']) && $exImg['variant_id'] == $v['id']) ? 'selected' : '' ?>>
                                                                    Color: <?= htmlspecialchars($v['variant_name']) ?>
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

                            <!-- Upload New Photos Dropzone -->
                            <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                                Upload Additional Photos
                            </div>
                            <div id="mediaDropZone" onclick="document.getElementById('mediaFileInput').click()" style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 28px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                                <input type="file" id="mediaFileInput" name="media_files[]" multiple accept="image/png,image/jpeg,image/webp,image/gif,image/avif" style="display: none;" onchange="handleFileSelect(this.files)">
                                
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: var(--brand-blue);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                    </div>
                                    <div>
                                        <span style="font-size: 0.92rem; font-weight: 700; color: var(--text-primary); display: block;">
                                            Drag & Drop new garment photos, or <span style="color: var(--brand-blue); text-decoration: underline;">Browse Files</span>
                                        </span>
                                        <span style="font-size: 0.76rem; color: var(--text-muted); display: block; margin-top: 4px;">
                                            JPG, PNG, WebP (up to 10MB each). Multiple files supported.
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Newly Selected Media Preview Grid -->
                            <div id="mediaPreviewContainer" style="margin-top: 20px; display: none;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-primary);">
                                        Newly Selected Photos (<span id="mediaCountBadge">0</span> images)
                                    </div>
                                    <span style="font-size: 0.74rem; color: var(--text-muted);">
                                        Choose primary cover or link photo to a color variant before saving.
                                    </span>
                                </div>

                                <div id="mediaPreviewGrid" class="media-preview-grid">
                                    <!-- Dynamic Image Preview Cards will render here -->
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Pricing, Inventory Thresholds & Publishing -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Pricing Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                                <h3 class="panel-title" style="font-size: 1rem;">Pricing & Valuation</h3>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Base / MRP Price (₹) <span style="color: #ef4444;">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0"
                                        name="base_price" 
                                        id="basePriceInput" 
                                        required 
                                        value="<?= (int)round((float)$product['base_price']) ?>" 
                                        class="form-input" 
                                        style="height: 42px; font-size: 1rem; font-weight: 700;"
                                    >
                                </div>

                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Offer / Sale Price (₹)
                                    </label>
                                    <input 
                                        type="number" 
                                        step="1" 
                                        min="0"
                                        name="sale_price" 
                                        value="<?= (int)round((float)$product['sale_price']) ?>" 
                                        class="form-input" 
                                        style="height: 42px; font-size: 1rem; font-weight: 700; color: var(--brand-blue);"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Thresholds Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                                <h3 class="panel-title" style="font-size: 1rem;">Stock & Alerts Policy</h3>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Low Stock Threshold (Units)
                                    </label>
                                    <input 
                                        type="number" 
                                        name="low_stock_threshold" 
                                        value="<?= (int)($product['low_stock_threshold'] ?? 10) ?>" 
                                        min="1" 
                                        class="form-input" 
                                        style="height: 42px; font-weight: 700;"
                                    >
                                </div>

                                <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                                    <input 
                                        type="checkbox" 
                                        name="allow_backorder" 
                                        id="allowBackorder" 
                                        value="1" 
                                        <?= !empty($product['allow_backorder']) ? 'checked' : '' ?> 
                                        style="width: 18px; height: 18px; accent-color: var(--brand-blue); cursor: pointer;"
                                    >
                                    <label for="allowBackorder" style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); cursor: pointer;">
                                        Allow Customer Backorders
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Publishing Status & Actions Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                                <h3 class="panel-title" style="font-size: 1rem;">Publication Status</h3>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Catalog Visibility
                                    </label>
                                    <select name="status" class="form-input" style="height: 42px; font-weight: 600;">
                                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>🟢 Active (Published in Storefront)</option>
                                        <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>🟡 Draft (Hidden from Customers)</option>
                                        <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>⚪ Archived</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn-primary-gradient" style="height: 46px; font-size: 0.92rem; width: 100%;">
                                    Save Changes
                                </button>

                                <a href="<?= url('admin/products') ?>" class="btn-export" style="height: 40px; text-align: center; justify-content: center; font-size: 0.85rem;">
                                    Cancel & Return
                                </a>
                            </div>
                        </div>

                        <!-- SEO Metadata Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                                <h3 class="panel-title" style="font-size: 1rem;">SEO & Metadata</h3>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">
                                        Meta Title
                                    </label>
                                    <input 
                                        type="text" 
                                        name="meta_title" 
                                        value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>" 
                                        class="form-input" 
                                        style="height: 38px; font-size: 0.82rem;"
                                    >
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 4px;">
                                        Meta Description
                                    </label>
                                    <textarea 
                                        name="meta_description" 
                                        rows="3" 
                                        class="form-input" 
                                        style="padding: 8px; font-size: 0.82rem;"
                                    ><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <script>
            let variantIndex = <?= count($product['variants'] ?? []) ?>;
            let currentFiles = [];

            const nameInput = document.getElementById('productName');
            const primarySku = document.querySelector('.variant-sku-input');

            // Add Variant Row
            function addVariantRow(colorName = '', colorHex = '#1E3A8A', size = 'Free Size') {
                const tbody = document.getElementById('variantsBody');
                const row = document.createElement('tr');
                const rowIdx = variantIndex;
                row.setAttribute('data-row-idx', rowIdx);

                const basePrice = document.getElementById('basePriceInput').value || '';
                const titleVal = nameInput ? nameInput.value.trim() : 'JIY';
                const prefix = titleVal ? titleVal.replace(/[^a-zA-Z]/g, '').slice(0, 3).toUpperCase() : 'JIY';
                const colCode = colorName ? colorName.replace(/[^a-zA-Z]/g, '').slice(0, 3).toUpperCase() : 'VAR';
                const sizeCode = size.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 3) || 'FS';
                const suggestedSku = `JIY-${prefix}-${colCode}-${sizeCode}-${rowIdx + 1}`;

                row.innerHTML = `
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="color" name="variants[${rowIdx}][color_code]" value="${colorHex}" class="color-picker-input" onchange="syncColorSwatch(this, ${rowIdx})" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; padding: 0; background: transparent;" title="Pick Color Swatch">
                            <input type="text" name="variants[${rowIdx}][color_name]" value="${colorName}" class="form-input color-name-input" style="height: 36px; font-size: 0.85rem; font-weight: 600;" placeholder="Color Name" oninput="onVariantColorChange(${rowIdx})" required>
                            <input type="hidden" name="variants[${rowIdx}][variant_name]" class="variant-name-hidden" value="${colorName ? colorName + ' / ' + size : 'Variant ' + (rowIdx + 1)}">
                        </div>
                    </td>
                    <td>
                        <select name="variants[${rowIdx}][size]" class="form-input variant-size-select" style="height: 36px; font-size: 0.85rem;" onchange="onVariantSizeChange(${rowIdx})">
                            <option value="Free Size" ${size === 'Free Size' ? 'selected' : ''}>Free Size</option>
                            <option value="XS" ${size === 'XS' ? 'selected' : ''}>XS</option>
                            <option value="S" ${size === 'S' ? 'selected' : ''}>S</option>
                            <option value="M" ${size === 'M' ? 'selected' : ''}>M</option>
                            <option value="L" ${size === 'L' ? 'selected' : ''}>L</option>
                            <option value="XL" ${size === 'XL' ? 'selected' : ''}>XL</option>
                            <option value="2XL" ${size === '2XL' ? 'selected' : ''}>2XL</option>
                            <option value="3XL" ${size === '3XL' ? 'selected' : ''}>3XL</option>
                            <option value="Unstitched" ${size === 'Unstitched' ? 'selected' : ''}>Unstitched</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="variants[${rowIdx}][sku]" class="form-input variant-sku-input" style="height: 36px; font-family: monospace; font-size: 0.85rem;" value="${suggestedSku}" required>
                    </td>
                    <td>
                        <input type="number" step="1" min="0" name="variants[${rowIdx}][price_override]" class="form-input variant-price-input" style="height: 36px; font-size: 0.85rem;" value="${basePrice}">
                    </td>
                    <td>
                        <input type="number" name="variants[${rowIdx}][stock_qty]" value="10" min="0" class="form-input" style="height: 36px; font-size: 0.85rem; font-weight: 700;" required>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" onclick="removeVariantRow(this)" style="background: none; border: none; color: #ef4444; font-size: 1.1rem; cursor: pointer; padding: 4px;" title="Remove Variant">
                            &times;
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
                variantIndex++;
                updateMediaVariantOptions();
            }

            function removeVariantRow(btn) {
                btn.closest('tr').remove();
                updateMediaVariantOptions();
            }

            function syncColorSwatch(pickerEl, rowIdx) {
                onVariantColorChange(rowIdx);
            }

            function onVariantColorChange(rowIdx) {
                const row = document.querySelector(`#variantsBody tr[data-row-idx="${rowIdx}"]`);
                if (!row) return;
                const cInput = row.querySelector('.color-name-input');
                const sInput = row.querySelector('.variant-size-select');
                const hInput = row.querySelector('.variant-name-hidden');
                
                const cVal = cInput.value.trim();
                const sVal = sInput.value;
                hInput.value = cVal ? (cVal + ' / ' + sVal) : sVal;

                updateMediaVariantOptions();
            }

            function onVariantSizeChange(rowIdx) {
                onVariantColorChange(rowIdx);
            }

            function generateSkuSuggestions() {
                const name = nameInput ? nameInput.value.trim() : 'JIY';
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
                const cards = document.querySelectorAll('.existing-media-card');
                if (cards.length === 0) {
                    const grid = document.getElementById('existingMediaGrid');
                    if (grid) grid.parentElement.style.display = 'none';
                }
            }

            // =========================================================================
            // DIRECT MEDIA FILE UPLOAD SYSTEM (EDIT MODE)
            // =========================================================================
            const dropZone = document.getElementById('mediaDropZone');
            const fileInput = document.getElementById('mediaFileInput');
            const previewContainer = document.getElementById('mediaPreviewContainer');
            const previewGrid = document.getElementById('mediaPreviewGrid');
            const countBadge = document.getElementById('mediaCountBadge');

            if (dropZone) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.style.borderColor = 'var(--brand-blue)';
                        dropZone.style.background = '#eff6ff';
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.style.borderColor = '#cbd5e1';
                        dropZone.style.background = '#f8fafc';
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    if (dt && dt.files && dt.files.length > 0) {
                        handleFileSelect(dt.files);
                    }
                });
            }

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
                    card.className = 'media-preview-card';
                    card.style.cssText = 'background: #fff; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05);';

                    const hasExistingCover = document.querySelector('input[name="primary_image_choice"]:checked') !== null;
                    const isPrimary = !hasExistingCover && (idx === 0);
                    const formattedSize = (file.size / 1024).toFixed(0) + ' KB';

                    card.innerHTML = `
                        <div style="position: relative; width: 100%; height: 160px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img id="previewImg_${idx}" src="" style="width: 100%; height: 100%; object-fit: cover;">
                            
                            <!-- Remove File Button -->
                            <button type="button" onclick="removeFile(${idx})" style="position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; border-radius: 50%; background: rgba(0,0,0,0.65); color: #fff; border: none; font-size: 1rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s;" title="Remove Photo">
                                &times;
                            </button>

                            <!-- Primary Badge Tag -->
                            <label style="position: absolute; top: 6px; left: 6px; display: flex; align-items: center; gap: 4px; background: ${isPrimary ? '#2563eb' : 'rgba(0,0,0,0.6)'}; color: #fff; padding: 2px 8px; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; cursor: pointer;">
                                <input type="radio" name="primary_image_choice" value="upload_${idx}" ${isPrimary ? 'checked' : ''} onchange="updatePrimaryBadges()" style="margin: 0; width: 12px; height: 12px; accent-color: #fff;">
                                <span>${isPrimary ? '★ Primary Cover' : 'Set Cover'}</span>
                            </label>
                        </div>

                        <div style="padding: 10px; display: flex; flex-direction: column; gap: 6px;">
                            <div style="font-size: 0.76rem; font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${file.name}">
                                ${file.name}
                            </div>
                            <div style="font-size: 0.68rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                                <span>${formattedSize}</span>
                                <span style="text-transform: uppercase;">${file.name.split('.').pop()}</span>
                            </div>

                            <!-- Link to Color Variant -->
                            <div style="margin-top: 4px;">
                                <label style="font-size: 0.68rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 2px;">
                                    Link to Color:
                                </label>
                                <select name="media_variant_index[${idx}]" class="media-variant-select form-input" style="height: 28px; font-size: 0.74rem; padding: 2px 6px;">
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
                            badge.style.background = '#2563eb';
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
                    const varId = row.getAttribute('data-variant-id') || i;
                    const cInput = row.querySelector('.color-name-input');
                    const sInput = row.querySelector('.variant-size-select');
                    const cName = cInput && cInput.value.trim() ? cInput.value.trim() : `Variant ${i + 1}`;
                    const size = sInput ? sInput.value : '';
                    const label = size ? `${cName} (${size})` : cName;
                    optionsHtml += `<option value="${varId}">Color: ${label}</option>`;
                });
                return optionsHtml;
            }

            function updateMediaVariantOptions() {
                const newSelects = document.querySelectorAll('.media-variant-select');
                newSelects.forEach(sel => {
                    const currentVal = sel.value;
                    sel.innerHTML = `<option value="">All Colors (Lookbook)</option>` + getVariantSelectOptions();
                    sel.value = currentVal;
                });

                const existingSelects = document.querySelectorAll('.existing-variant-select');
                existingSelects.forEach(sel => {
                    const currentVal = sel.value;
                    sel.innerHTML = `<option value="">All Colors (Lookbook)</option>` + getVariantSelectOptions();
                    sel.value = currentVal;
                });
            }
            </script>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
