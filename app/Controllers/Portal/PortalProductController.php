<?php

namespace App\Controllers\Portal;

use App\Models\Product;
use App\Middleware\PortalAuthMiddleware;

class PortalProductController {

    /**
     * Display filtered catalog listing, executive KPIs, and stock control ledger.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view the Products & SKUs catalog.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'      => $_GET['status'] ?? 'all',
            'category_id' => $_GET['category_id'] ?? 'all',
            'search'      => trim($_GET['search'] ?? ''),
            'sort'        => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $prodData   = Product::getAll($filters, $page, 15);
        $products   = $prodData['products'];
        $pagination = $prodData['pagination'];

        // Ensure safe pagination flags to prevent PHP undefined key notices
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis       = Product::getKPIs();
        $categories = Product::getAllCategories();

        $canCreate = staff_can('products', 'create');
        $canEdit   = staff_can('products', 'edit');
        $canDelete = staff_can('products', 'delete');

        $title = 'Products & SKUs Catalog | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/products/index.php';
    }

    /**
     * Display detailed inspection specification and SKU matrix for a single garment.
     */
    public function show(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to inspect catalog items.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Identifier', 'The product link is invalid.');
            redirect('portal/products');
        }

        $product = Product::getById($id);
        if (!$product) {
            set_toast('error', 'Product Not Found', 'No catalog record matches this identifier.');
            redirect('portal/products');
        }

        $canEdit   = staff_can('products', 'edit');
        $canDelete = staff_can('products', 'delete');

        $title = htmlspecialchars($product['name']) . ' | Product Specification | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/products/show.php';
    }

    /**
     * Show form to create a new luxury garment with variants and media.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'create')) {
            set_flash('error', 'Access Denied: You do not have clearance to add products to the catalog.');
            redirect('portal/products');
        }

        $categories = Product::getAllCategories();
        $title = 'Add New Luxury Garment | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/products/create.php';
    }

    /**
     * Store newly created product with variants and gallery images.
     */
    public function store(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'create')) {
            set_flash('error', 'Access Denied: You do not have clearance to add products.');
            redirect('portal/products');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired or invalid request.');
            redirect('portal/products');
        }

        $name = trim($_POST['name'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);

        if (empty($name) || $basePrice <= 0) {
            set_toast('error', 'Validation Error', 'Product title and base price are mandatory.');
            redirect('portal/products/create');
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
            'status'             => in_array($_POST['status'] ?? '', ['active', 'draft', 'archived'], true) ? $_POST['status'] : 'active',
            'meta_title'         => trim($_POST['meta_title'] ?? $name),
            'meta_description'   => trim($_POST['meta_description'] ?? ''),
            'variants'           => $variants,
            'images'             => $images
        ];

        $productId = Product::create($data);

        if ($productId) {
            set_toast('success', 'Product Created', "Garment \"$name\" was added to catalog successfully.");
            redirect('portal/products');
        } else {
            set_toast('error', 'Creation Failed', 'Failed to save product in database.');
            redirect('portal/products/create');
        }
    }

    /**
     * Show form to edit an existing product.
     */
    public function edit(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to modify products.');
            redirect('portal/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'The product identifier is invalid.');
            redirect('portal/products');
        }

        $product = Product::getById($id);
        if (!$product) {
            set_toast('error', 'Product Not Found', 'No product found matching this identifier.');
            redirect('portal/products');
        }

        $categories = Product::getAllCategories();
        $title = 'Edit ' . htmlspecialchars($product['name']) . ' | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/products/edit.php';
    }

    /**
     * Update an existing product.
     */
    public function update(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to modify products.');
            redirect('portal/products');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'The product identifier is invalid.');
            redirect('portal/products');
        }

        $name = trim($_POST['name'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);

        if (empty($name) || $basePrice <= 0) {
            set_toast('error', 'Validation Error', 'Product title and base price are required.');
            redirect('portal/products/' . $encryptedId . '/edit');
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
            'status'             => in_array($_POST['status'] ?? '', ['active', 'draft', 'archived'], true) ? $_POST['status'] : 'active',
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

        redirect('portal/products');
    }

    /**
     * Fast in-line stock adjustment from modal.
     */
    public function adjustStock(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to adjust stock.');
            redirect('portal/products');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'Product identifier is invalid.');
            redirect('portal/products');
        }

        $variantId = (int)($_POST['variant_id'] ?? 0);
        $newQty    = max(0, (int)($_POST['new_qty'] ?? 0));

        $updated = Product::adjustVariantStock($variantId, $newQty);

        if ($updated) {
            set_toast('success', 'Stock Adjusted', "Variant inventory updated to $newQty units.");
        } else {
            set_toast('error', 'Adjustment Failed', 'Could not update variant stock quantity.');
        }

        redirect('portal/products');
    }

    /**
     * Fast toggle status (active, draft, archived).
     */
    public function toggleStatus(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to change product status.');
            redirect('portal/products');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'Product identifier is invalid.');
            redirect('portal/products');
        }

        $status = $_POST['status'] ?? 'active';
        $updated = Product::updateStatus($id, $status);

        if ($updated) {
            set_toast('success', 'Status Updated', 'Product status changed to ' . ucfirst($status) . '.');
        } else {
            set_toast('error', 'Update Failed', 'Unable to toggle product status.');
        }

        redirect('portal/products');
    }

    /**
     * Soft-archive / deactivate a product.
     */
    public function destroy(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'delete')) {
            set_flash('error', 'Access Denied: You do not have clearance to archive products.');
            redirect('portal/products');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/products');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Product', 'Product identifier is invalid.');
            redirect('portal/products');
        }

        $deleted = Product::delete($id);

        if ($deleted) {
            set_toast('success', 'Product Archived', 'Product was archived from the active catalog.');
        } else {
            set_toast('error', 'Action Failed', 'Failed to archive product.');
        }

        redirect('portal/products');
    }

    /**
     * Export catalog ledger to CSV.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to export catalog data.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'      => $_GET['status'] ?? 'all',
            'category_id' => $_GET['category_id'] ?? 'all',
            'search'      => trim($_GET['search'] ?? ''),
            'sort'        => $_GET['sort'] ?? 'newest'
        ];

        // Retrieve up to 2000 records for export
        $prodData = Product::getAll($filters, 1, 2000);
        $products = $prodData['products'];

        $filename = 'jiyaji_products_catalog_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Product ID',
            'Product Name',
            'Category',
            'Primary SKU',
            'Base Price (INR)',
            'Sale Price (INR)',
            'Total Stock',
            'Variant Count',
            'Stock Health',
            'Status',
            'Variants Detail',
            'Created Date'
        ]);

        foreach ($products as $p) {
            $totalStock = (int)($p['total_stock'] ?? 0);
            $lowThresh  = (int)($p['low_stock_threshold'] ?? 10);

            if ($totalStock <= 0) {
                $stockHealth = 'Out of Stock';
            } elseif ($totalStock <= $lowThresh) {
                $stockHealth = 'Low Stock';
            } else {
                $stockHealth = 'In Stock';
            }

            // Build variants detail string
            $varDetails = [];
            if (!empty($p['variants'])) {
                foreach ($p['variants'] as $v) {
                    $varDetails[] = ($v['variant_name'] ?? 'Standard') . ' [SKU: ' . ($v['sku'] ?? '') . ', Qty: ' . ($v['stock_qty'] ?? 0) . ']';
                }
            }

            fputcsv($out, [
                $p['id'],
                $p['name'],
                $p['category_name'] ?? 'General',
                $p['primary_sku'] ?? '',
                round((float)$p['base_price']),
                round((float)$p['sale_price']),
                $totalStock,
                count($p['variants'] ?? []),
                $stockHealth,
                ucfirst($p['status'] ?? 'active'),
                implode(' | ', $varDetails),
                $p['created_at'] ?? ''
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Securely process and store uploaded media files.
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
