<?php

namespace App\Controllers\Portal;

use App\Models\Category;
use App\Middleware\PortalAuthMiddleware;
use Exception;

class PortalCategoryController {

    /**
     * Display categories and departmental hierarchy ledger.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Categories Taxonomy.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'    => $_GET['status'] ?? 'all',
            'parent_id' => $_GET['parent_id'] ?? 'all',
            'search'    => trim($_GET['search'] ?? ''),
            'sort'      => $_GET['sort'] ?? 'sort_order'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $catData    = Category::getAll($filters, $page, 15);
        $categories = $catData['categories'];
        $pagination = $catData['pagination'];

        // Ensure safe pagination flags to prevent PHP undefined key notices
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis    = Category::getKPIs();
        $parents = Category::getCandidateParents();

        $canManage = staff_can('categories', 'manage') || staff_can('categories', 'create') || staff_can('categories', 'edit');

        $title = 'Categories & Taxonomy | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/categories/index.php';
    }

    /**
     * Display detailed specification and linked garments for a single category.
     */
    public function show(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to inspect category specifications.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Category', 'The category link is invalid.');
            redirect('portal/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'No category record matches this identifier.');
            redirect('portal/categories');
        }

        // Fetch sub-categories under this category
        $subCategories = [];
        try {
            $db = \App\Config\Database::connect();
            $stmt = $db->prepare("
                SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count 
                FROM categories c 
                WHERE c.parent_id = ? 
                ORDER BY c.sort_order ASC, c.name ASC
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $subCategories[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching subcategories: " . $e->getMessage());
        }

        $canManage = staff_can('categories', 'manage') || staff_can('categories', 'edit');

        $title = htmlspecialchars($category['name']) . ' | Collection Details | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/categories/show.php';
    }

    /**
     * Show form to create a new category.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'manage') && !staff_can('categories', 'create')) {
            set_flash('error', 'Access Denied: You do not have clearance to add categories.');
            redirect('portal/categories');
        }

        $parents = Category::getCandidateParents();
        $title = 'Add New Luxury Category | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/categories/create.php';
    }

    /**
     * Store newly created category.
     */
    public function store(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'manage') && !staff_can('categories', 'create')) {
            set_flash('error', 'Access Denied: You do not have clearance to add categories.');
            redirect('portal/categories');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired or invalid request.');
            redirect('portal/categories');
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Category name is required.');
            redirect('portal/categories/create');
        }

        $slug        = trim($_POST['slug'] ?? '') ?: Category::generateSlug($name);
        $parentId    = !empty($_POST['parent_id']) && is_numeric($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = isset($_POST['sort_order']) && is_numeric($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        // Handle category image file upload
        $imageUrl = null;
        if (!empty($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
            $imageUrl = $this->handleUploadedImage($_FILES['image']);
        }

        try {
            $newId = Category::create([
                'name'        => $name,
                'slug'        => $slug,
                'parent_id'   => $parentId,
                'description' => $description,
                'image_url'   => $imageUrl,
                'sort_order'  => $sortOrder,
                'is_active'   => $isActive
            ]);

            set_toast('success', 'Category Created', "Collection '{$name}' has been successfully created.");
            redirect('portal/categories');
        } catch (Exception $e) {
            set_toast('error', 'Creation Failed', 'Could not save category: ' . $e->getMessage());
            redirect('portal/categories/create');
        }
    }

    /**
     * Show form to edit an existing category.
     */
    public function edit(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'manage') && !staff_can('categories', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to edit categories.');
            redirect('portal/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Category', 'The category identifier is invalid.');
            redirect('portal/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'The requested category does not exist.');
            redirect('portal/categories');
        }

        // Exclude current category from parent candidate list to prevent hierarchy loops
        $parents = Category::getCandidateParents($id);
        $title = "Edit Category: {$category['name']} | Jiyaji LX Staff Portal";
        include __DIR__ . '/../../Views/portal/categories/edit.php';
    }

    /**
     * Update an existing category.
     */
    public function update(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'manage') && !staff_can('categories', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to edit categories.');
            redirect('portal/categories');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Category', 'The category identifier is invalid.');
            redirect('portal/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'The requested category does not exist.');
            redirect('portal/categories');
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Category name is required.');
            redirect("portal/categories/{$encryptedId}/edit");
        }

        $slug        = trim($_POST['slug'] ?? '') ?: Category::generateSlug($name, $id);
        $parentId    = !empty($_POST['parent_id']) && is_numeric($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = isset($_POST['sort_order']) && is_numeric($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        // Prevent setting parent to itself
        if ($parentId === $id) {
            $parentId = null;
        }

        $data = [
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => $parentId,
            'description' => $description,
            'sort_order'  => $sortOrder,
            'is_active'   => $isActive
        ];

        // Handle image removal or new image upload
        if (!empty($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            $data['image_url'] = null;
        } elseif (!empty($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
            $uploaded = $this->handleUploadedImage($_FILES['image']);
            if ($uploaded) {
                $data['image_url'] = $uploaded;
            }
        }

        try {
            Category::update($id, $data);
            set_toast('success', 'Category Updated', "Category '{$name}' has been updated.");
            redirect('portal/categories');
        } catch (Exception $e) {
            set_toast('error', 'Update Failed', 'Could not update category: ' . $e->getMessage());
            redirect("portal/categories/{$encryptedId}/edit");
        }
    }

    /**
     * Fast toggle active/inactive status of a category.
     */
    public function toggleStatus(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'manage') && !staff_can('categories', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to modify category status.');
            redirect('portal/categories');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('portal/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Category', 'Category identifier is invalid.');
            redirect('portal/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'Category does not exist.');
            redirect('portal/categories');
        }

        $toggled = Category::toggleStatus($id);
        if ($toggled) {
            $newStatus = ($category['is_active'] == 1) ? 'Deactivated' : 'Activated';
            set_toast('success', 'Status Updated', "Category '{$category['name']}' is now {$newStatus}.");
        } else {
            set_toast('error', 'Action Failed', 'Could not update category status.');
        }

        redirect('portal/categories');
    }

    /**
     * Safely delete a category.
     */
    public function destroy(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'manage') && !staff_can('categories', 'delete')) {
            set_flash('error', 'Access Denied: You do not have clearance to delete categories.');
            redirect('portal/categories');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('portal/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Category', 'Category identifier is invalid.');
            redirect('portal/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'Category does not exist.');
            redirect('portal/categories');
        }

        $result = Category::delete($id);
        if ($result['success']) {
            set_toast('success', 'Category Deleted', $result['message']);
        } else {
            set_toast('error', 'Cannot Delete', $result['message']);
        }

        redirect('portal/categories');
    }

    /**
     * Export categories taxonomy ledger to CSV.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('categories', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to export taxonomy data.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'    => $_GET['status'] ?? 'all',
            'parent_id' => $_GET['parent_id'] ?? 'all',
            'search'    => trim($_GET['search'] ?? ''),
            'sort'      => $_GET['sort'] ?? 'sort_order'
        ];

        $catData = Category::getAll($filters, 1, 1000);
        $categories = $catData['categories'];

        $filename = 'jiyaji_categories_taxonomy_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Category ID',
            'Name',
            'Slug',
            'Hierarchy Level',
            'Parent Category',
            'Assigned Products Count',
            'Display Order',
            'Status',
            'Description',
            'Created Date'
        ]);

        foreach ($categories as $cat) {
            $isRoot = empty($cat['parent_id']);
            fputcsv($out, [
                $cat['id'],
                $cat['name'],
                $cat['slug'],
                $isRoot ? 'Root Collection' : 'Sub-Category',
                $cat['parent_name'] ?? 'None (Root)',
                (int)($cat['product_count'] ?? 0),
                $cat['sort_order'] ?? 0,
                ($cat['is_active'] == 1) ? 'Active' : 'Inactive',
                $cat['description'] ?? '',
                $cat['created_at'] ?? ''
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Handle category image file upload securely.
     */
    private function handleUploadedImage(array $file): ?string {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $maxSize = 8 * 1024 * 1024; // 8MB
        if ($file['size'] > $maxSize) {
            return null;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/avif'
            ];

            if (!in_array($mime, $allowedMimes, true)) {
                return null;
            }
        }

        $uploadDir = dirname(__DIR__, 3) . '/public/uploads/categories/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $uniqueName = 'cat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/categories/' . $uniqueName;
        }

        return null;
    }
}
