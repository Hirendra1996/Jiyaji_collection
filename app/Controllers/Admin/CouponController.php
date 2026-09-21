<?php

namespace App\Controllers\Admin;

use App\Models\Coupon;
use App\Middleware\AuthMiddleware;
use Exception;

class CouponController {

    // =========================================================================
    // INDEX — Coupon Ledger with KPI Dashboard
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'type'   => $_GET['type']   ?? 'all',
            'search' => trim($_GET['search'] ?? ''),
            'sort'   => $_GET['sort']   ?? 'newest',
        ];
        $page       = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $couponData = Coupon::getAll($filters, $page, 20);
        $coupons    = $couponData['coupons'];
        $pagination = $couponData['pagination'];
        $kpis       = Coupon::getKPIs();

        $title = 'Coupons & Promos | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/coupons/index.php';
    }

    // =========================================================================
    // CREATE FORM
    // =========================================================================

    public function create(): void {
        AuthMiddleware::check();

        $categories = Coupon::getCategories();
        $products   = Coupon::getProducts();
        $suggested  = Coupon::generateCode();

        $title = 'Create New Coupon | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/coupons/create.php';
    }

    // =========================================================================
    // STORE — Save New Coupon
    // =========================================================================

    public function store(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/coupons/create');
        }

        // CSRF
        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token mismatch. Please try again.');
            redirect('admin/coupons/create');
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        if (empty($code)) {
            set_toast('error', 'Validation Error', 'Coupon code is required.');
            redirect('admin/coupons/create');
        }

        if (Coupon::codeExists($code)) {
            set_toast('error', 'Duplicate Code', "The coupon code \"{$code}\" already exists.");
            redirect('admin/coupons/create');
        }

        $type  = $_POST['type']  ?? 'flat';
        $value = (float)($_POST['value'] ?? 0);

        if ($type === 'percentage' && ($value <= 0 || $value > 100)) {
            set_toast('error', 'Validation Error', 'Percentage discount must be between 1 and 100.');
            redirect('admin/coupons/create');
        }
        if ($type === 'flat' && $value <= 0) {
            set_toast('error', 'Validation Error', 'Flat discount value must be greater than zero.');
            redirect('admin/coupons/create');
        }

        $admin = auth_admin();
        $data  = [
            'code'                => $code,
            'description'         => trim($_POST['description'] ?? ''),
            'type'                => $type,
            'value'               => $value,
            'max_discount_cap'    => $_POST['max_discount_cap']    ?? '',
            'min_cart_value'      => $_POST['min_cart_value']      ?? 0,
            'usage_limit_global'  => $_POST['usage_limit_global']  ?? '',
            'usage_limit_per_user'=> $_POST['usage_limit_per_user'] ?? 1,
            'is_public'           => isset($_POST['is_public']) ? 1 : 0,
            'starts_at'           => $_POST['starts_at']  ?? '',
            'expires_at'          => $_POST['expires_at'] ?? '',
            'created_by'          => $admin['id'] ?? null,
        ];

        $newId = Coupon::create($data);
        if ($newId) {
            set_toast('success', 'Coupon Created', "Coupon \"{$code}\" has been created successfully.");
            redirect('admin/coupons/' . encrypt_id($newId));
        } else {
            set_toast('error', 'Save Failed', 'Failed to create coupon. Please try again.');
            redirect('admin/coupons/create');
        }
    }

    // =========================================================================
    // SHOW — Coupon Detail / Usage History
    // =========================================================================

    public function show(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('admin/coupons');
        }

        $coupon  = Coupon::find($id);
        if (!$coupon) {
            set_toast('error', 'Not Found', 'Coupon not found or has been removed.');
            redirect('admin/coupons');
        }

        $usageHistory = Coupon::getUsageHistory($id, 25);

        $title = "Coupon: {$coupon['code']} | Jiyaji LX";
        include __DIR__ . '/../../Views/admin/coupons/show.php';
    }

    // =========================================================================
    // EDIT FORM
    // =========================================================================

    public function edit(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('admin/coupons');
        }

        $coupon = Coupon::find($id);
        if (!$coupon) {
            set_toast('error', 'Not Found', 'Coupon not found or has been removed.');
            redirect('admin/coupons');
        }

        $categories = Coupon::getCategories();
        $products   = Coupon::getProducts();

        $title = "Edit Coupon: {$coupon['code']} | Jiyaji LX";
        include __DIR__ . '/../../Views/admin/coupons/edit.php';
    }

    // =========================================================================
    // UPDATE — Save Edits
    // =========================================================================

    public function update(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/coupons');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('admin/coupons');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token mismatch. Please try again.');
            redirect('admin/coupons/' . $encryptedId . '/edit');
        }

        $code  = strtoupper(trim($_POST['code'] ?? ''));
        if (empty($code)) {
            set_toast('error', 'Validation Error', 'Coupon code is required.');
            redirect('admin/coupons/' . $encryptedId . '/edit');
        }

        if (Coupon::codeExists($code, $id)) {
            set_toast('error', 'Duplicate Code', "The coupon code \"{$code}\" is already in use by another coupon.");
            redirect('admin/coupons/' . $encryptedId . '/edit');
        }

        $type  = $_POST['type']  ?? 'flat';
        $value = (float)($_POST['value'] ?? 0);

        if ($type === 'percentage' && ($value <= 0 || $value > 100)) {
            set_toast('error', 'Validation Error', 'Percentage discount must be between 1 and 100.');
            redirect('admin/coupons/' . $encryptedId . '/edit');
        }

        $data = [
            'code'                => $code,
            'description'         => trim($_POST['description'] ?? ''),
            'type'                => $type,
            'value'               => $value,
            'max_discount_cap'    => $_POST['max_discount_cap']    ?? '',
            'min_cart_value'      => $_POST['min_cart_value']      ?? 0,
            'usage_limit_global'  => $_POST['usage_limit_global']  ?? '',
            'usage_limit_per_user'=> $_POST['usage_limit_per_user'] ?? 1,
            'is_public'           => isset($_POST['is_public']) ? 1 : 0,
            'starts_at'           => $_POST['starts_at']  ?? '',
            'expires_at'          => $_POST['expires_at'] ?? '',
        ];

        if (Coupon::update($id, $data)) {
            set_toast('success', 'Coupon Updated', "Coupon \"{$code}\" has been updated.");
            redirect('admin/coupons/' . $encryptedId);
        } else {
            set_toast('error', 'Update Failed', 'Failed to update coupon. Please try again.');
            redirect('admin/coupons/' . $encryptedId . '/edit');
        }
    }

    // =========================================================================
    // TOGGLE STATUS (AJAX-safe POST endpoint)
    // =========================================================================

    public function toggleStatus(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('admin/coupons');
        }

        $newStatus = Coupon::toggleStatus($id);
        if ($newStatus === null) {
            set_toast('error', 'Action Failed', 'Could not change coupon status.');
        } else {
            $label = $newStatus ? 'activated' : 'deactivated';
            set_toast('success', 'Status Changed', "Coupon has been {$label}.");
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? url('admin/coupons');
        header('Location: ' . $ref);
        exit;
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('admin/coupons');
        }

        $result = Coupon::delete($id);
        if ($result === false) {
            // Could be blocked due to order history
            set_toast('error', 'Cannot Delete', 'This coupon has been used in real orders and cannot be deleted. Deactivate it instead.');
        } elseif ($result) {
            set_toast('success', 'Coupon Deleted', 'Coupon has been permanently deleted.');
        } else {
            set_toast('error', 'Delete Failed', 'Coupon not found or could not be deleted.');
        }

        redirect('admin/coupons');
    }

    // =========================================================================
    // GENERATE CODE (AJAX endpoint)
    // =========================================================================

    public function generateCode(): void {
        AuthMiddleware::check();
        header('Content-Type: application/json');
        $prefix = strtoupper(trim($_GET['prefix'] ?? 'JIYAJI'));
        echo json_encode(['code' => Coupon::generateCode($prefix)]);
        exit;
    }
}
