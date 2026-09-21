<?php

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Middleware\AuthMiddleware;

class ProductController {
    /**
     * Display product and SKU catalog listing with filters and KPIs.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'      => $_GET['status'] ?? 'all',
            'category_id' => $_GET['category_id'] ?? 'all',
            'search'      => trim($_GET['search'] ?? ''),
            'sort'        => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $prodData   = Product::getAll($filters, $page, 15);
        $products   = $prodData['products'];
        $pagination = $prodData['pagination'];

        $kpis       = Product::getKPIs();
        $categories = Product::getAllCategories();

        $title = 'Products & SKUs Catalog | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/products/index.php';
    }

    /**
     * Show form to create a new product.
     */
    public function create(): void {
        AuthMiddleware::check();

        $categories = Product::getAllCategories();
        $title = 'Add New Luxury Product | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/products/create.php';
    }

    /**
     * Store newly created product with variants and gallery images.
     */
    public function store(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/products');
        }

        $name = trim($_POST['name'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);

        if (empty($name) || $basePrice <= 0) {
            set_toast('error', 'Validation Error', 'Product title and base price are required.');
            redirect('admin/products/create');
        }

        // Process clothing variants payload
        $variants = [];
        if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
            foreach ($_POST['variants'] as $v) {
                $cName = trim($v['color_name'] ?? '');
                $cCode = trim($v['color_code'] ?? '');
                $cSize = trim($v['size'] ?? '');
                $vName = trim($v['variant_name'] ?? '');
                $sku   = trim($v['sku'] ?? '');

                if (empty($vName)) {
                    $vName = trim(($cName ?: '') . ($cSize ? ($cName ? ' / ' : '') . $cSize : '')) ?: 'Standard';
                }

                if (!empty($sku) || !empty($vName) || !empty($cName)) {
                    $variants[] = [
                        'color_name'     => $cName ?: null,
                        'color_code'     => $cCode ?: null,
                        'size'           => $cSize ?: null,
                        'variant_name'   => $vName,
                        'sku'            => $sku,
                        'price_override' => !empty($v['price_override']) ? (float)$v['price_override'] : $basePrice,
                        'stock_qty'      => isset($v['stock_qty']) ? (int)$v['stock_qty'] : 10
                    ];
                }
            }
        }

        // Process uploaded media files
        $uploadedImages = $this->handleUploadedFiles($_FILES['media_files'] ?? ($_FILES['images'] ?? []));
        $primaryChoice = $_POST['primary_image_choice'] ?? '';
        $variantTag = $_POST['media_variant_index'] ?? [];

        $images = [];
        foreach ($uploadedImages as $fIdx => $filePath) {
            $isPrimary = ($primaryChoice === 'upload_' . $fIdx || ($primaryChoice === '' && $fIdx === 0)) ? 1 : 0;
            $varIdx = isset($variantTag[$fIdx]) && is_numeric($variantTag[$fIdx]) ? (int)$variantTag[$fIdx] : null;
            $images[] = [
                'url'           => $filePath,
                'variant_index' => $varIdx,
                'is_primary'    => $isPrimary
            ];
        }

        // Fallback backward-compatible images payload (URLs)
        if (!empty($_POST['images']) && is_array($_POST['images'])) {
            foreach ($_POST['images'] as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $images[] = [
                        'url'        => $url,
                        'is_primary' => empty($images) ? 1 : 0
                    ];
                }
            }
        }

        // Guarantee at least one primary image if images exist
        if (!empty($images)) {
            $hasPrimary = false;
            foreach ($images as $img) {
                if (!empty($img['is_primary'])) { $hasPrimary = true; break; }
            }
            if (!$hasPrimary) {
                $images[0]['is_primary'] = 1;
            }
        }

        $data = [
            'category_id'        => (int)($_POST['category_id'] ?? 1),
            'name'               => $name,
            'slug'               => trim($_POST['slug'] ?? ''),
            'short_description'  => trim($_POST['short_description'] ?? ''),
            'description'        => trim($_POST['description'] ?? ''),
            'base_price'         => $basePrice,
            'sale_price'         => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : $basePrice,
            'low_stock_threshold'=> !empty($_POST['low_stock_threshold']) ? (int)$_POST['low_stock_threshold'] : 10,
            'allow_backorder'    => !empty($_POST['allow_backorder']) ? 1 : 0,
            'status'             => $_POST['status'] ?? 'active',
            'meta_title'         => trim($_POST['meta_title'] ?? $name),
            'meta_description'   => trim($_POST['meta_description'] ?? ''),
            'variants'           => $variants,
            'images'             => $images
        ];

        $productId = Product::create($data);

        if ($productId) {
            set_toast('success', 'Product Created', "Product \"$name\" was added to catalog successfully.");
            redirect('admin/products');
        } else {
            set_toast('error', 'Creation Failed', 'Failed to save product in database.');
            redirect('admin/products/create');
        }
    }

    /**
     * Show form to edit an existing product.
     */
    public function edit(?string $encryptedId = null): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product Link', 'The product identifier is invalid.');
            redirect('admin/products');
        }

        $product = Product::getById($id);
        if (!$product) {
            set_toast('error', 'Product Not Found', 'No product found matching this identifier.');
            redirect('admin/products');
        }

        $categories = Product::getAllCategories();
        $title = 'Edit ' . htmlspecialchars($product['name']) . ' | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/products/edit.php';
    }

    /**
     * Update an existing product.
     */
    public function update(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product Link', 'The product identifier is invalid.');
            redirect('admin/products');
        }

        $name = trim($_POST['name'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);

        if (empty($name) || $basePrice <= 0) {
            set_toast('error', 'Validation Error', 'Product title and base price are required.');
            redirect('admin/products/' . $encryptedId . '/edit');
        }

        // Process clothing variants payload
        $variants = [];
        if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
            foreach ($_POST['variants'] as $v) {
                $cName = trim($v['color_name'] ?? '');
                $cCode = trim($v['color_code'] ?? '');
                $cSize = trim($v['size'] ?? '');
                $vName = trim($v['variant_name'] ?? '');
                $sku   = trim($v['sku'] ?? '');

                if (empty($vName)) {
                    $vName = trim(($cName ?: '') . ($cSize ? ($cName ? ' / ' : '') . $cSize : '')) ?: 'Standard';
                }

                if (!empty($sku) || !empty($vName) || !empty($cName)) {
                    $item = [
                        'color_name'     => $cName ?: null,
                        'color_code'     => $cCode ?: null,
                        'size'           => $cSize ?: null,
                        'variant_name'   => $vName,
                        'sku'            => $sku,
                        'price_override' => !empty($v['price_override']) ? (float)$v['price_override'] : $basePrice,
                        'stock_qty'      => isset($v['stock_qty']) ? (int)$v['stock_qty'] : 10
                    ];
                    if (!empty($v['id'])) {
                        $item['id'] = (int)$v['id'];
                    }
                    $variants[] = $item;
                }
            }
        }

        // Process images: existing retained + newly uploaded
        $images = [];
        $primaryChoice = $_POST['primary_image_choice'] ?? '';

        // 1. Retained existing images
        if (!empty($_POST['existing_images']) && is_array($_POST['existing_images'])) {
            foreach ($_POST['existing_images'] as $exIdx => $ex) {
                $exUrl = trim($ex['url'] ?? '');
                if (!empty($exUrl)) {
                    $isPrimary = ($primaryChoice === 'existing_' . $exIdx) ? 1 : 0;
                    $images[] = [
                        'url'        => $exUrl,
                        'variant_id' => !empty($ex['variant_id']) ? (int)$ex['variant_id'] : null,
                        'is_primary' => $isPrimary
                    ];
                }
            }
        }

        // 2. Newly uploaded files
        $uploadedImages = $this->handleUploadedFiles($_FILES['media_files'] ?? ($_FILES['images'] ?? []));
        $variantTag = $_POST['media_variant_index'] ?? [];

        foreach ($uploadedImages as $fIdx => $filePath) {
            $isPrimary = ($primaryChoice === 'upload_' . $fIdx || ($primaryChoice === '' && empty($images) && $fIdx === 0)) ? 1 : 0;
            $varId = isset($variantTag[$fIdx]) && is_numeric($variantTag[$fIdx]) ? (int)$variantTag[$fIdx] : null;
            $images[] = [
                'url'        => $filePath,
                'variant_id' => $varId,
                'is_primary' => $isPrimary
            ];
        }

        // 3. Backward-compatible image URLs
        if (!empty($_POST['images']) && is_array($_POST['images'])) {
            foreach ($_POST['images'] as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $images[] = [
                        'url'        => $url,
                        'is_primary' => empty($images) ? 1 : 0
                    ];
                }
            }
        }

        // Ensure at least one image is primary if images exist
        if (!empty($images)) {
            $hasPrimary = false;
            foreach ($images as $im) {
                if (!empty($im['is_primary'])) { $hasPrimary = true; break; }
            }
            if (!$hasPrimary) {
                $images[0]['is_primary'] = 1;
            }
        }

        $data = [
            'category_id'        => (int)($_POST['category_id'] ?? 1),
            'name'               => $name,
            'short_description'  => trim($_POST['short_description'] ?? ''),
            'description'        => trim($_POST['description'] ?? ''),
            'base_price'         => $basePrice,
            'sale_price'         => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : $basePrice,
            'low_stock_threshold'=> !empty($_POST['low_stock_threshold']) ? (int)$_POST['low_stock_threshold'] : 10,
            'allow_backorder'    => !empty($_POST['allow_backorder']) ? 1 : 0,
            'status'             => $_POST['status'] ?? 'active',
            'meta_title'         => trim($_POST['meta_title'] ?? $name),
            'meta_description'   => trim($_POST['meta_description'] ?? ''),
            'variants'           => $variants,
            'images'             => $images
        ];

        $updated = Product::update($id, $data);

        if ($updated) {
            set_toast('success', 'Product Updated', "Changes to \"$name\" saved successfully.");
        } else {
            set_toast('error', 'Update Failed', 'Failed to update product details.');
        }

        redirect('admin/products');
    }

    /**
     * Fast toggle status (active, draft, archived).
     */
    public function toggleStatus(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'Product identifier is invalid.');
            redirect('admin/products');
        }

        $status = $_POST['status'] ?? 'active';
        $updated = Product::updateStatus($id, $status);

        if ($updated) {
            set_toast('success', 'Status Updated', 'Product status changed to ' . ucfirst($status) . '.');
        } else {
            set_toast('error', 'Update Failed', 'Unable to toggle product status.');
        }

        redirect('admin/products');
    }

    /**
     * Handle quick modal in-line stock adjustment.
     */
    public function adjustStock(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'Product identifier is invalid.');
            redirect('admin/products');
        }

        $variantId = (int)($_POST['variant_id'] ?? 0);
        $newQty    = (int)($_POST['new_qty'] ?? 0);

        $updated = Product::adjustVariantStock($variantId, $newQty);

        if ($updated) {
            set_toast('success', 'Stock Adjusted', 'Variant inventory updated to ' . $newQty . ' units.');
        } else {
            set_toast('error', 'Adjustment Failed', 'Could not update variant stock quantity.');
        }

        redirect('admin/products');
    }

    /**
     * Soft-archive / delete product.
     */
    public function destroy(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'Product identifier is invalid.');
            redirect('admin/products');
        }

        $deleted = Product::delete($id);

        if ($deleted) {
            set_toast('success', 'Product Archived', 'Product was archived from active catalog.');
        } else {
            set_toast('error', 'Action Failed', 'Failed to archive product.');
        }

        redirect('admin/products');
    }

    /**
     * Securely process and store uploaded media files.
     *
     * @param array $filesArray $_FILES entry
     * @param string $subDir Directory under public/uploads
     * @return array List of stored relative file paths (e.g. 'public/uploads/products/xyz.webp')
     */
    private function handleUploadedFiles(array $filesArray, string $subDir = 'products'): array {
        $uploadedPaths = [];
        $targetDir = dirname(__DIR__, 3) . '/public/uploads/' . trim($subDir, '/');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        if (empty($filesArray['name'])) {
            return [];
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];

        $names = is_array($filesArray['name']) ? $filesArray['name'] : [$filesArray['name']];
        $tmpNames = is_array($filesArray['tmp_name']) ? $filesArray['tmp_name'] : [$filesArray['tmp_name']];
        $errors = is_array($filesArray['error']) ? $filesArray['error'] : [$filesArray['error']];
        $sizes = is_array($filesArray['size']) ? $filesArray['size'] : [$filesArray['size']];

        foreach ($names as $i => $origName) {
            if (empty($origName) || !isset($errors[$i]) || $errors[$i] !== UPLOAD_ERR_OK || empty($tmpNames[$i])) {
                continue;
            }

            // Max 10MB per image
            if (($sizes[$i] ?? 0) > 10 * 1024 * 1024) {
                continue;
            }

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                continue;
            }

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmpNames[$i]);
                finfo_close($finfo);
                if (!in_array($mime, $allowedMimes, true)) {
                    continue;
                }
            }

            $uniqueFilename = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $targetDir . '/' . $uniqueFilename;

            if (move_uploaded_file($tmpNames[$i], $destPath)) {
                $uploadedPaths[] = 'public/uploads/' . trim($subDir, '/') . '/' . $uniqueFilename;
            }
        }

        return $uploadedPaths;
    }
}
