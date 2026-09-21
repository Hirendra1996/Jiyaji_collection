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
                        <a href="<?= url('admin/categories') ?>" style="color: var(--brand-blue);">Categories</a>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Add New Category</span>
                    </div>
                    <h1 class="welcome-title">Add Luxury Collection Category</h1>
                    <p class="welcome-subtitle">Create a brand collection, configure hierarchical parent-child groupings, and upload lookbook cover media.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/categories') ?>" class="btn-export">
                        &larr; Back to Categories Ledger
                    </a>
                </div>
            </div>

            <form action="<?= url('admin/categories/store') ?>" method="POST" enctype="multipart/form-data" id="categoryForm">
                <?= csrf_field() ?>

                <div class="product-form-grid">
                    
                    <!-- Left Column: Primary Details & Media -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- 1. General Details Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 20px;">
                                <div>
                                    <h2 class="panel-title">1. Category Information</h2>
                                    <div class="panel-subtitle">Primary collection title, URL taxonomy, and descriptions</div>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 18px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Category / Collection Name <span style="color: #ef4444;">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        id="catName" 
                                        required 
                                        placeholder="e.g. Royal Banarasi Sarees, Handcrafted Sherwanis, Designer Accessories" 
                                        class="form-input" 
                                        style="height: 42px; font-weight: 600;"
                                    >
                                </div>

                                <div class="grid-2-cols">
                                    <div>
                                        <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                            URL Slug (Auto-Generated)
                                        </label>
                                        <input 
                                            type="text" 
                                            name="slug" 
                                            id="catSlug" 
                                            placeholder="royal-banarasi-sarees" 
                                            class="form-input" 
                                            style="height: 42px; font-family: monospace; font-size: 0.85rem;"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                            Taxonomy Parent Hierarchy
                                        </label>
                                        <select name="parent_id" class="form-input" style="height: 42px;">
                                            <option value="">★ None (Primary Root Category)</option>
                                            <?php foreach ($parents as $p): ?>
                                                <option value="<?= $p['id'] ?>">↳ Sub-category under: <?= htmlspecialchars($p['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Description & Heritage Highlights
                                    </label>
                                    <textarea 
                                        name="description" 
                                        rows="4" 
                                        class="form-input" 
                                        placeholder="Describe the fabric weaves, artisanal origin, and style details for customers browsing this luxury category."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Showcase Cover Media Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 16px;">
                                <div>
                                    <h2 class="panel-title">2. Category Showcase Cover Photo</h2>
                                    <div class="panel-subtitle">Upload a luxury lookbook banner or collection thumbnail photo</div>
                                </div>
                            </div>

                            <!-- Drag & Drop Upload Zone -->
                            <div id="catDropZone" onclick="document.getElementById('catImageInput').click()" style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 28px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                                <input type="file" id="catImageInput" name="image" accept="image/png,image/jpeg,image/webp,image/gif,image/avif" style="display: none;" onchange="previewCatImage(this)">
                                
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
                                            Drag & Drop cover photo, or <span style="color: var(--brand-blue); text-decoration: underline;">Browse File</span>
                                        </span>
                                        <span style="font-size: 0.76rem; color: var(--text-muted); display: block; margin-top: 4px;">
                                            Supports JPG, PNG, WebP (up to 8MB). Recommended aspect ratio 16:9 or 1:1.
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Preview Card -->
                            <div id="catPreviewWrapper" style="margin-top: 16px; display: none;">
                                <div style="display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; max-width: 440px;">
                                    <img id="catPreviewImg" src="" alt="Cover Preview" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                                    <div style="flex: 1; min-width: 0;">
                                        <div id="catPreviewName" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></div>
                                        <div id="catPreviewSize" style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;"></div>
                                        <button type="button" onclick="clearCatImage()" style="margin-top: 6px; background: none; border: none; color: #ef4444; font-size: 0.76rem; font-weight: 700; cursor: pointer; padding: 0;">
                                            &times; Remove Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Settings & Publishing -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Display Configuration Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                                <h3 class="panel-title" style="font-size: 1rem;">Display & Hierarchy</h3>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Display Sort Order
                                    </label>
                                    <input 
                                        type="number" 
                                        name="sort_order" 
                                        value="0" 
                                        min="0" 
                                        class="form-input" 
                                        style="height: 42px; font-weight: 700;"
                                    >
                                    <span style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 4px;">
                                        Controls category order in navigation menus. Lower numbers appear first.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Publication Status Card -->
                        <div class="card-panel">
                            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; margin-bottom: 18px;">
                                <h3 class="panel-title" style="font-size: 1rem;">Catalog Status</h3>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 6px;">
                                        Visibility in Storefront
                                    </label>
                                    <select name="is_active" class="form-input" style="height: 42px; font-weight: 600;">
                                        <option value="1" selected>🟢 Active (Visible to Customers)</option>
                                        <option value="0">⚪ Inactive (Hidden / Private)</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn-primary-gradient" style="height: 46px; font-size: 0.92rem; width: 100%;">
                                    Create Category
                                </button>

                                <a href="<?= url('admin/categories') ?>" class="btn-export" style="height: 40px; text-align: center; justify-content: center; font-size: 0.85rem;">
                                    Cancel & Return
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

            <script>
            // Live auto-slug generator
            const nameInput = document.getElementById('catName');
            const slugInput = document.getElementById('catSlug');
            let manualSlug = false;

            slugInput.addEventListener('input', function() {
                manualSlug = this.value.trim().length > 0;
            });

            nameInput.addEventListener('input', function() {
                if (!manualSlug) {
                    slugInput.value = this.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            });

            // Image file preview
            function previewCatImage(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('catPreviewImg').src = e.target.result;
                        document.getElementById('catPreviewName').textContent = file.name;
                        document.getElementById('catPreviewSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
                        document.getElementById('catPreviewWrapper').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }

            function clearCatImage() {
                document.getElementById('catImageInput').value = '';
                document.getElementById('catPreviewWrapper').style.display = 'none';
                document.getElementById('catPreviewImg').src = '';
            }

            // Drag & Drop
            const dropZone = document.getElementById('catDropZone');
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = 'var(--brand-blue)';
                    dropZone.style.background = '#eff6ff';
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = '#cbd5e1';
                    dropZone.style.background = '#f8fafc';
                });
            });
            dropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('catImageInput').files = files;
                    previewCatImage(document.getElementById('catImageInput'));
                }
            });
            </script>
        </main>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
