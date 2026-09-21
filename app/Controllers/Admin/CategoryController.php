<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Middleware\AuthMiddleware;
use Exception;

class CategoryController {
    /**
     * Display categories ledger with KPIs and search/filters.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'    => $_GET['status'] ?? 'all',
            'parent_id' => $_GET['parent_id'] ?? 'all',
            'search'    => trim($_GET['search'] ?? ''),
            'sort'      => $_GET['sort'] ?? 'sort_order'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $catData    = Category::getAll($filters, $page, 15);
        $categories = $catData['categories'];
        $pagination = $catData['pagination'];

        $kpis    = Category::getKPIs();
        $parents = Category::getCandidateParents();

        $title = 'Categories & Taxonomy Catalog | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/categories/index.php';
    }

    /**
     * Show form to create a new luxury category.
     */
    public function create(): void {
        AuthMiddleware::check();

        $parents = Category::getCandidateParents();
        $title = 'Add New Luxury Category | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/categories/create.php';
    }

    /**
     * Store newly created category.
     */
    public function store(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/categories');
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Category name is required.');
            redirect('admin/categories/create');
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
            redirect('admin/categories');
        } catch (Exception $e) {
            set_toast('error', 'Creation Failed', 'Could not save category: ' . $e->getMessage());
            redirect('admin/categories/create');
        }
    }

    /**
     * Show form to edit an existing category.
     */
    public function edit(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid or tampered category identifier.');
            redirect('admin/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'The requested category does not exist or was deleted.');
            redirect('admin/categories');
        }

        // Exclude current category from parent candidate list to prevent hierarchy cycles
        $parents = Category::getCandidateParents($id);
        $title = "Edit Category: {$category['name']} | Jiyaji LX";
        include __DIR__ . '/../../Views/admin/categories/edit.php';
    }

    /**
     * Update an existing category.
     */
    public function update(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid category identifier.');
            redirect('admin/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'The requested category does not exist.');
            redirect('admin/categories');
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Category name is required.');
            redirect("admin/categories/{$encryptedId}/edit");
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
            redirect('admin/categories');
        } catch (Exception $e) {
            set_toast('error', 'Update Failed', 'Could not update category: ' . $e->getMessage());
            redirect("admin/categories/{$encryptedId}/edit");
        }
    }

    /**
     * Toggle active/inactive status of a category.
     */
    public function toggleStatus(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('admin/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid category identifier.');
            redirect('admin/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'Category does not exist.');
            redirect('admin/categories');
        }

        $toggled = Category::toggleStatus($id);
        if ($toggled) {
            $newStatus = ($category['is_active'] == 1) ? 'Deactivated' : 'Activated';
            set_toast('success', 'Status Updated', "Category '{$category['name']}' is now {$newStatus}.");
        } else {
            set_toast('error', 'Action Failed', 'Could not update category status.');
        }

        redirect('admin/categories');
    }

    /**
     * Safely delete a category.
     */
    public function destroy(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('admin/categories');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid category identifier.');
            redirect('admin/categories');
        }

        $category = Category::find($id);
        if (!$category) {
            set_toast('error', 'Category Not Found', 'Category does not exist.');
            redirect('admin/categories');
        }

        $result = Category::delete($id);
        if ($result['success']) {
            set_toast('success', 'Category Deleted', $result['message']);
        } else {
            set_toast('error', 'Cannot Delete', $result['message']);
        }

        redirect('admin/categories');
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

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            'image/avif' => 'avif'
        ];

        if (!isset($allowedMimes[$mime])) {
            return null;
        }

        $ext = $allowedMimes[$mime];
        $uploadDir = __DIR__ . '/../../../public/uploads/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueName = 'cat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/categories/' . $uniqueName;
        }

        return null;
    }
}
