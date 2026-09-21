<?php
$category = $category ?? [];
$parents = $parents ?? [];
$encryptedId = $category['encrypted_id'] ?? encrypt_id($category['id'] ?? 0);
$title = $title ?? 'Edit Category: ' . ($category['name'] ?? '') . ' | Jiyaji LX Staff Portal';
$productCount = (int)($category['product_count'] ?? 0);
$isRoot = empty($category['parent_id']);
$canManage = staff_can('categories', 'manage') || staff_can('categories', 'edit');

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">
            
            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #9333EA; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/categories') ?>" style="color: #9333EA; text-decoration: none; font-weight: 600;">Categories Taxonomy</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/categories/' . $encryptedId) ?>" style="color: #9333EA; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($category['name'] ?? '') ?></a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Edit</span>
            </div>

            <!-- Executive Hero Banner -->
            <div class="welcome-banner" style="background: linear-gradient(135deg, #1E1B4B 0%, #581C87 50%, #9333EA 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(147, 51, 234, 0.3);">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #F3E8FF;">
                            Taxonomy Editor &bull; ID #<?= htmlspecialchars((string)($category['id'] ?? '')) ?>
                        </span>
                        <?php if ($isRoot): ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #DBEAFE; color: #1E40AF; padding: 2px 8px; border-radius: 999px;">
                                Primary Department
                            </span>
                        <?php else: ?>
                            <span style="font-size: 0.72rem; font-weight: 700; background: #E0E7FF; color: #3730A3; padding: 2px 8px; border-radius: 999px;">
                                Sub-Collection
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">
                        Edit Category: <?= htmlspecialchars($category['name'] ?? '') ?>
                    </h1>
                    <p style="font-size: 0.88rem; color: #E9D5FF; margin: 0;">
                        Modify nomenclature, parent hierarchy relations, banner photography, and customer storefront discovery.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="<?= url('portal/categories/' . $encryptedId) ?>" style="background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Public Profile
                    </a>
                    <a href="<?= url('portal/categories') ?>" style="background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Back to Ledger
                    </a>
                </div>
            </div>

            <!-- Form Container -->
            <form action="<?= url('portal/categories/' . $encryptedId . '/update') ?>" method="POST" enctype="multipart/form-data" id="editCategoryForm">
                <?= csrf_field() ?>

                <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
                    
                    <!-- Left Column: Core Identity & Media -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- 1. Category Identity & Metadata -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 14px; margin-bottom: 20px;">
                                <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #F3E8FF; color: #9333EA; font-size: 0.82rem; font-weight: 800;">1</span>
                                    Category Nomenclature &amp; Metadata
                                </h2>
                                <div style="font-size: 0.8rem; color: #64748B;">Core identification and SEO URL pathing for this department.</div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <div>
                                    <label for="categoryName" style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Category Title <span style="color: #EF4444;">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        id="categoryName" 
                                        required 
                                        value="<?= htmlspecialchars($category['name'] ?? '') ?>" 
                                        style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.92rem; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 8px; outline: none; transition: border 0.15s;"
                                        onfocus="this.style.borderColor='#9333EA';"
                                        onblur="this.style.borderColor='#CBD5E1';"
                                    >
                                    <div style="font-size: 0.74rem; color: #64748B; margin-top: 4px;">Modifying the title will not overwrite the custom URL slug unless manually regenerated.</div>
                                </div>

                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                        <label for="categorySlug" style="font-size: 0.84rem; font-weight: 700; color: #0F172A; margin: 0;">
                                            URL Slug (Permlink)
                                        </label>
                                        <button type="button" onclick="autoGenerateSlug()" style="background: none; border: none; color: #9333EA; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">
                                            Regenerate from Title
                                        </button>
                                    </div>
                                    <div style="display: flex; align-items: center; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; overflow: hidden;">
                                        <span style="padding: 0 12px; font-size: 0.82rem; color: #64748B; font-family: monospace; background: #F1F5F9; border-right: 1px solid #CBD5E1; height: 42px; display: flex; align-items: center;">
                                            /collections/
                                        </span>
                                        <input 
                                            type="text" 
                                            name="slug" 
                                            id="categorySlug" 
                                            value="<?= htmlspecialchars($category['slug'] ?? '') ?>" 
                                            style="flex: 1; height: 42px; padding: 0 12px; font-family: monospace; font-size: 0.86rem; border: none; outline: none; background: transparent;"
                                        >
                                    </div>
                                    <div style="font-size: 0.74rem; color: #64748B; margin-top: 4px;">Caution: Changing slug will change the public URL and can affect external bookmarks.</div>
                                </div>

                                <div>
                                    <label for="categoryDescription" style="font-size: 0.84rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Merchandising Summary / Description
                                    </label>
                                    <textarea 
                                        name="description" 
                                        id="categoryDescription" 
                                        rows="4" 
                                        style="width: 100%; padding: 12px 14px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none; font-family: inherit; line-height: 1.5; resize: vertical;"
                                        onfocus="this.style.borderColor='#9333EA';"
                                        onblur="this.style.borderColor='#CBD5E1';"
                                    ><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                                    <div style="font-size: 0.74rem; color: #64748B; margin-top: 4px;">Public introduction banner copy rendered at the top of category collection pages.</div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Visual Identity & Media Banner -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 14px; margin-bottom: 20px;">
                                <h2 style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #F3E8FF; color: #9333EA; font-size: 0.82rem; font-weight: 800;">2</span>
                                    Category Hero / Cover Asset
                                </h2>
                                <div style="font-size: 0.8rem; color: #64748B;">Update or replace the category banner photography.</div>
                            </div>

                            <!-- Current Image Preview if exists -->
                            <?php if (!empty($category['image_url'])): ?>
                                <div id="currentImageCard" style="display: flex; align-items: center; gap: 16px; padding: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 18px;">
                                    <div style="width: 72px; height: 72px; border-radius: 8px; overflow: hidden; background: #FFFFFF; border: 1px solid #CBD5E1; flex-shrink: 0;">
                                        <img src="<?= htmlspecialchars(image_url($category['image_url'])) ?>" alt="Current Banner" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 0.85rem; font-weight: 700; color: #0F172A;">Active Banner Asset</div>
                                        <div style="font-size: 0.74rem; color: #64748B; word-break: break-all; margin-top: 2px;">
                                            <?= htmlspecialchars($category['image_url']) ?>
                                        </div>
                                        <div style="margin-top: 8px;">
                                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; font-weight: 700; color: #DC2626; cursor: pointer;">
                                                <input type="checkbox" name="remove_image" value="1" id="removeImageCheckbox" onchange="toggleRemoveImageState(this)" style="accent-color: #DC2626; cursor: pointer;">
                                                Remove Current Image on Save
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Upload New / Replacement Area -->
                            <div 
                                id="dropZone"
                                style="border: 2px dashed #CBD5E1; border-radius: 12px; padding: 28px 20px; text-align: center; background: #F8FAFC; cursor: pointer; transition: all 0.2s;"
                                onclick="document.getElementById('categoryImageInput').click();"
                                ondragover="event.preventDefault(); this.style.borderColor='#9333EA'; this.style.background='#FAF5FF';"
                                ondragleave="event.preventDefault(); this.style.borderColor='#CBD5E1'; this.style.background='#F8FAFC';"
                                ondrop="handleFileDrop(event);"
                            >
                                <input 
                                    type="file" 
                                    name="image" 
                                    id="categoryImageInput" 
                                    accept="image/jpeg,image/png,image/webp,image/jpg" 
                                    style="display: none;" 
                                    onchange="previewCategoryPhoto(this)"
                                >

                                <div id="uploadPrompt">
                                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #F3E8FF; color: #9333EA; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    </div>
                                    <div style="font-size: 0.9rem; font-weight: 700; color: #0F172A; margin-bottom: 4px;">
                                        <?= !empty($category['image_url']) ? 'Click to replace with new banner' : 'Click to browse or drag & drop banner' ?>
                                    </div>
                                    <div style="font-size: 0.76rem; color: #64748B;">
                                        Recommended: 800x800 square or 1200x600 landscape banner. Formats: JPG, WebP, PNG (Max 5MB).
                                    </div>
                                </div>

                                <div id="previewContainer" style="display: none; flex-direction: column; align-items: center; gap: 12px;">
                                    <img id="imagePreview" src="#" alt="New Preview" style="max-height: 180px; max-width: 100%; border-radius: 8px; object-fit: contain; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #E2E8F0;">
                                    <div style="display: flex; gap: 10px;">
                                        <span id="fileNameBadge" style="font-size: 0.78rem; font-weight: 600; color: #475569; background: #E2E8F0; padding: 4px 10px; border-radius: 999px;"></span>
                                        <button type="button" onclick="clearSelectedImage(event)" style="background: #FEE2E2; color: #DC2626; border: none; border-radius: 999px; padding: 4px 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Cancel Replacement</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Taxonomy Placement & Publish Options -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Hierarchy & Placement -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <h3 style="font-size: 1rem; font-weight: 800; color: #0F172A; margin: 0 0 14px 0; border-bottom: 1px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9333EA" stroke-width="2.2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                Taxonomy Hierarchy
                            </h3>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label for="categoryParent" style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Parent Level (Hierarchy)
                                    </label>
                                    <select 
                                        name="parent_id" 
                                        id="categoryParent" 
                                        style="width: 100%; height: 42px; padding: 0 12px; font-size: 0.88rem; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 8px; background: #FFFFFF; outline: none;"
                                        onfocus="this.style.borderColor='#9333EA';"
                                        onblur="this.style.borderColor='#CBD5E1';"
                                    >
                                        <option value="" <?= empty($category['parent_id']) ? 'selected' : '' ?>>&bull; None (Primary Root Department)</option>
                                        <?php if (!empty($parents)): ?>
                                            <optgroup label="Available Parent Collections:">
                                                <?php foreach ($parents as $p): ?>
                                                    <option value="<?= $p['id'] ?>" <?= ((int)($category['parent_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>>
                                                        📁 <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['slug']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                    </select>
                                    <div style="font-size: 0.74rem; color: #64748B; margin-top: 4px;">
                                        Reassigning to a parent converts this into a sub-category under that department.
                                    </div>
                                </div>

                                <div>
                                    <label for="categorySortOrder" style="font-size: 0.82rem; font-weight: 700; color: #0F172A; display: block; margin-bottom: 6px;">
                                        Display Priority / Sort Order
                                    </label>
                                    <input 
                                        type="number" 
                                        name="sort_order" 
                                        id="categorySortOrder" 
                                        value="<?= htmlspecialchars((string)($category['sort_order'] ?? 0)) ?>" 
                                        min="0" 
                                        step="1" 
                                        style="width: 100%; height: 42px; padding: 0 12px; font-size: 0.88rem; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                                        onfocus="this.style.borderColor='#9333EA';"
                                        onblur="this.style.borderColor='#CBD5E1';"
                                    >
                                    <div style="font-size: 0.74rem; color: #64748B; margin-top: 4px;">
                                        Lower numbers appear first in storefront navigation bars.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visibility & Status -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <h3 style="font-size: 1rem; font-weight: 800; color: #0F172A; margin: 0 0 14px 0; border-bottom: 1px solid #F1F5F9; padding-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Visibility &amp; Status
                            </h3>

                            <div style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                                <input 
                                    type="checkbox" 
                                    name="is_active" 
                                    id="categoryIsActive" 
                                    value="1" 
                                    <?= (!empty($category['is_active'])) ? 'checked' : '' ?> 
                                    style="width: 18px; height: 18px; accent-color: #9333EA; margin-top: 2px; cursor: pointer;"
                                >
                                <label for="categoryIsActive" style="cursor: pointer; user-select: none;">
                                    <div style="font-size: 0.88rem; font-weight: 700; color: #0F172A;">Publicly Active</div>
                                    <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px;">
                                        Unchecking sets this category to draft/hidden. Associated products remain in the database.
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Action Submit Card -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; flex-direction: column; gap: 12px;">
                            <button 
                                type="submit" 
                                style="width: 100%; height: 44px; background: linear-gradient(135deg, #7E22CE 0%, #9333EA 100%); color: #FFFFFF; border: none; border-radius: 10px; font-size: 0.92rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(147, 51, 234, 0.35); transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.92';"
                                onmouseout="this.style.opacity='1';"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Changes
                            </button>

                            <a 
                                href="<?= url('portal/categories/' . $encryptedId) ?>" 
                                style="width: 100%; height: 40px; background: #F1F5F9; color: #475569; border-radius: 10px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: background 0.15s;"
                                onmouseover="this.style.background='#E2E8F0';"
                                onmouseout="this.style.background='#F1F5F9';"
                            >
                                View Profile
                            </a>

                            <?php if ($canManage): ?>
                                <hr style="border: 0; border-top: 1px solid #F1F5F9; margin: 4px 0;">
                                <?php if ($productCount > 0): ?>
                                    <div style="font-size: 0.74rem; color: #64748B; text-align: center; line-height: 1.4;">
                                        Deletion blocked: <strong><?= $productCount ?></strong> linked garments must be reassigned first.
                                    </div>
                                <?php else: ?>
                                    <button 
                                        type="button" 
                                        onclick="confirmCategoryDelete()" 
                                        style="width: 100%; height: 38px; background: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3; border-radius: 10px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.15s;"
                                        onmouseover="this.style.background='#FFE4E6';"
                                        onmouseout="this.style.background='#FFF1F2';"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        Delete Category
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- System Metadata -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-size: 0.78rem; font-weight: 700; color: #0F172A; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.04em;">
                                Audit Metadata
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.78rem;">
                                <div style="display: flex; justify-content: space-between; color: #64748B;">
                                    <span>Internal ID:</span>
                                    <strong style="color: #0F172A;">#<?= htmlspecialchars((string)($category['id'] ?? '')) ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #64748B;">
                                    <span>Garments Linked:</span>
                                    <a href="<?= url('portal/products?category_id=' . ($category['id'] ?? '')) ?>" style="color: #9333EA; font-weight: 700; text-decoration: none;">
                                        <?= $productCount ?> items &rarr;
                                    </a>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #64748B;">
                                    <span>Created:</span>
                                    <strong style="color: #0F172A;"><?= !empty($category['created_at']) ? date('M d, Y', strtotime($category['created_at'])) : '&mdash;' ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #64748B;">
                                    <span>Last Modified:</span>
                                    <strong style="color: #0F172A;"><?= !empty($category['updated_at']) ? date('M d, Y', strtotime($category['updated_at'])) : '&mdash;' ?></strong>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

            <!-- Hidden Delete Form -->
            <?php if ($canManage && $productCount === 0): ?>
                <form id="deleteCategoryForm" action="<?= url('portal/categories/' . $encryptedId . '/delete') ?>" method="POST" style="display: none;">
                    <?= csrf_field() ?>
                </form>
            <?php endif; ?>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<script>
// Auto-slug generator
function autoGenerateSlug() {
    const nameInput = document.getElementById('categoryName');
    const slugInput = document.getElementById('categorySlug');
    if (!nameInput || !slugInput) return;
    
    let text = nameInput.value.trim().toLowerCase();
    text = text.replace(/[^a-z0-9\s-]/g, '');
    text = text.replace(/[\s-]+/g, '-');
    slugInput.value = text;
}

// Preview Category Photo
function previewCategoryPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const prompt = document.getElementById('uploadPrompt');
            const container = document.getElementById('previewContainer');
            const badge = document.getElementById('fileNameBadge');
            
            preview.src = e.target.result;
            badge.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            prompt.style.display = 'none';
            container.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }
}

// Drag & drop handling
function handleFileDrop(e) {
    e.preventDefault();
    const dropZone = document.getElementById('dropZone');
    dropZone.style.borderColor = '#CBD5E1';
    dropZone.style.background = '#F8FAFC';
    
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const fileInput = document.getElementById('categoryImageInput');
        fileInput.files = e.dataTransfer.files;
        previewCategoryPhoto(fileInput);
    }
}

// Clear selected image
function clearSelectedImage(e) {
    e.stopPropagation();
    const fileInput = document.getElementById('categoryImageInput');
    const prompt = document.getElementById('uploadPrompt');
    const container = document.getElementById('previewContainer');
    
    fileInput.value = '';
    prompt.style.display = 'block';
    container.style.display = 'none';
}

// Toggle remove image styling
function toggleRemoveImageState(checkbox) {
    const card = document.getElementById('currentImageCard');
    if (card) {
        if (checkbox.checked) {
            card.style.opacity = '0.4';
            card.style.textDecoration = 'line-through';
        } else {
            card.style.opacity = '1';
            card.style.textDecoration = 'none';
        }
    }
}

// Delete confirmation
function confirmCategoryDelete() {
    if (confirm('Are you sure you want to permanently delete category "<?= addslashes(htmlspecialchars($category['name'] ?? '')) ?>"? Any subcategories will become root categories. This action cannot be undone.')) {
        document.getElementById('deleteCategoryForm').submit();
    }
}
</script>
