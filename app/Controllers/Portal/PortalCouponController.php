<?php

namespace App\Controllers\Portal;

use App\Models\Coupon;
use App\Middleware\PortalAuthMiddleware;
use Exception;

/**
 * PortalCouponController
 *
 * Dedicated Promotional Engine and Offers Management for the Staff & Operations Portal.
 * Guarded by PortalAuthMiddleware and granular RBAC:
 *   - coupons:view   → Browse promotional campaign ledgers, inspect rules & limits, view redemption history, export CSV
 *   - coupons:manage → Create promo codes, configure basket minimums & caps, toggle active status, edit rules, delete promos
 */
class PortalCouponController {

    /**
     * Display the promotional campaigns ledger with live KPIs, status segment tabs, and filter tools.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Coupons & Promotions.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'type'   => $_GET['type']   ?? 'all',
            'search' => trim($_GET['search'] ?? ''),
            'sort'   => $_GET['sort']   ?? 'newest',
        ];

        $page       = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $couponData = Coupon::getAll($filters, $page, 15);
        $coupons    = $couponData['coupons'] ?? [];
        $pagination = $couponData['pagination'] ?? [
            'total'        => 0,
            'per_page'     => 15,
            'current_page' => 1,
            'total_pages'  => 1,
            'has_prev'     => false,
            'has_next'     => false,
        ];
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis      = Coupon::getKPIs();
        $canManage = staff_can('coupons', 'manage');

        $title = 'Coupons & Promotional Campaigns | Jiyaji LX Operations Portal';
        include __DIR__ . '/../../Views/portal/coupons/index.php';
    }

    /**
     * Display a single coupon configuration matrix and customer redemption ledger.
     */
    public function show(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Coupons & Promotions.');
            redirect('portal/dashboard');
        }

        $id = is_numeric($encryptedId) ? (int)$encryptedId : decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid coupon identifier.');
            redirect('portal/coupons');
        }

        $coupon = Coupon::find($id);
        if (!$coupon) {
            set_flash('error', 'Coupon not found or has been removed.');
            redirect('portal/coupons');
        }

        $usageHistory = Coupon::getUsageHistory($id, 30);
        $canManage    = staff_can('coupons', 'manage');

        $title = "Coupon {$coupon['code']} | Jiyaji LX Operations Portal";
        include __DIR__ . '/../../Views/portal/coupons/show.php';
    }

    /**
     * Render the new coupon creation form.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'manage')) {
            set_flash('error', 'Access Denied: You do not have permission to create promotional campaigns.');
            redirect('portal/coupons');
        }

        $categories = Coupon::getCategories();
        $products   = Coupon::getProducts();
        $suggested  = Coupon::generateCode();

        $title = 'Create Promotional Coupon | Jiyaji LX Operations Portal';
        include __DIR__ . '/../../Views/portal/coupons/create.php';
    }

    /**
     * Validate and save new promotional coupon.
     */
    public function store(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'manage')) {
            set_flash('error', 'Access Denied: Insufficient permissions to create coupons.');
            redirect('portal/coupons');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Security Error', 'Session expired or security token invalid. Please try again.');
            redirect('portal/coupons/create');
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        if (empty($code)) {
            set_toast('error', 'Validation Error', 'Coupon code is required.');
            redirect('portal/coupons/create');
        }

        if (Coupon::codeExists($code)) {
            set_toast('error', 'Duplicate Code', "The coupon code \"{$code}\" already exists.");
            redirect('portal/coupons/create');
        }

        $type  = in_array($_POST['type'] ?? '', ['flat', 'percentage'], true) ? $_POST['type'] : 'flat';
        $value = (float)($_POST['value'] ?? 0);

        if ($type === 'percentage' && ($value <= 0 || $value > 100)) {
            set_toast('error', 'Validation Error', 'Percentage discount value must be between 1 and 100%.');
            redirect('portal/coupons/create');
        }

        if ($type === 'flat' && $value <= 0) {
            set_toast('error', 'Validation Error', 'Flat discount amount must be greater than zero.');
            redirect('portal/coupons/create');
        }

        $staff = auth_staff();
        $data  = [
            'code'                => $code,
            'description'         => trim($_POST['description'] ?? ''),
            'type'                => $type,
            'value'               => $value,
            'max_discount_cap'    => !empty($_POST['max_discount_cap']) ? (float)$_POST['max_discount_cap'] : '',
            'min_cart_value'      => !empty($_POST['min_cart_value']) ? (float)$_POST['min_cart_value'] : 0,
            'usage_limit_global'  => !empty($_POST['usage_limit_global']) ? (int)$_POST['usage_limit_global'] : '',
            'usage_limit_per_user'=> !empty($_POST['usage_limit_per_user']) ? (int)$_POST['usage_limit_per_user'] : 1,
            'is_public'           => isset($_POST['is_public']) ? 1 : 0,
            'starts_at'           => !empty($_POST['starts_at']) ? $_POST['starts_at'] : '',
            'expires_at'          => !empty($_POST['expires_at']) ? $_POST['expires_at'] : '',
            'created_by'          => $staff['id'] ?? null,
        ];

        $newId = Coupon::create($data);
        if ($newId) {
            // Synchronize restrictions if provided
            $categoryRestrictions = $_POST['category_restrictions'] ?? [];
            $productRestrictions  = $_POST['product_restrictions'] ?? [];
            Coupon::syncRestrictions($newId, (array)$categoryRestrictions, (array)$productRestrictions);

            set_toast('success', 'Coupon Created', "Promotional campaign \"{$code}\" has been launched.");
            redirect('portal/coupons/' . encrypt_id($newId));
        } else {
            set_toast('error', 'Save Failed', 'Failed to store coupon in database. Please check values.');
            redirect('portal/coupons/create');
        }
    }

    /**
     * Render the coupon edit form.
     */
    public function edit(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'manage')) {
            set_flash('error', 'Access Denied: You do not have permission to modify promotional coupons.');
            redirect('portal/coupons');
        }

        $id = is_numeric($encryptedId) ? (int)$encryptedId : decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid coupon identifier.');
            redirect('portal/coupons');
        }

        $coupon = Coupon::find($id);
        if (!$coupon) {
            set_flash('error', 'Coupon not found or has been removed.');
            redirect('portal/coupons');
        }

        $categories = Coupon::getCategories();
        $products   = Coupon::getProducts();

        $title = "Edit Coupon {$coupon['code']} | Jiyaji LX Operations Portal";
        include __DIR__ . '/../../Views/portal/coupons/edit.php';
    }

    /**
     * Save modifications for an existing coupon.
     */
    public function update(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'manage')) {
            set_flash('error', 'Access Denied: Insufficient permissions.');
            redirect('portal/coupons');
        }

        $id = is_numeric($encryptedId) ? (int)$encryptedId : decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('portal/coupons');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Security Error', 'Session expired or token mismatch.');
            redirect('portal/coupons/' . $encryptedId . '/edit');
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        if (empty($code)) {
            set_toast('error', 'Validation Error', 'Coupon code is required.');
            redirect('portal/coupons/' . $encryptedId . '/edit');
        }

        if (Coupon::codeExists($code, $id)) {
            set_toast('error', 'Duplicate Code', "The coupon code \"{$code}\" is already assigned to another campaign.");
            redirect('portal/coupons/' . $encryptedId . '/edit');
        }

        $type  = in_array($_POST['type'] ?? '', ['flat', 'percentage'], true) ? $_POST['type'] : 'flat';
        $value = (float)($_POST['value'] ?? 0);

        if ($type === 'percentage' && ($value <= 0 || $value > 100)) {
            set_toast('error', 'Validation Error', 'Percentage discount value must be between 1 and 100%.');
            redirect('portal/coupons/' . $encryptedId . '/edit');
        }

        if ($type === 'flat' && $value <= 0) {
            set_toast('error', 'Validation Error', 'Flat discount value must be greater than zero.');
            redirect('portal/coupons/' . $encryptedId . '/edit');
        }

        $data = [
            'code'                => $code,
            'description'         => trim($_POST['description'] ?? ''),
            'type'                => $type,
            'value'               => $value,
            'max_discount_cap'    => !empty($_POST['max_discount_cap']) ? (float)$_POST['max_discount_cap'] : '',
            'min_cart_value'      => !empty($_POST['min_cart_value']) ? (float)$_POST['min_cart_value'] : 0,
            'usage_limit_global'  => !empty($_POST['usage_limit_global']) ? (int)$_POST['usage_limit_global'] : '',
            'usage_limit_per_user'=> !empty($_POST['usage_limit_per_user']) ? (int)$_POST['usage_limit_per_user'] : 1,
            'is_public'           => isset($_POST['is_public']) ? 1 : 0,
            'starts_at'           => !empty($_POST['starts_at']) ? $_POST['starts_at'] : '',
            'expires_at'          => !empty($_POST['expires_at']) ? $_POST['expires_at'] : '',
        ];

        if (Coupon::update($id, $data)) {
            // Update restrictions
            $categoryRestrictions = $_POST['category_restrictions'] ?? [];
            $productRestrictions  = $_POST['product_restrictions'] ?? [];
            Coupon::syncRestrictions($id, (array)$categoryRestrictions, (array)$productRestrictions);

            set_toast('success', 'Coupon Updated', "Promotional campaign \"{$code}\" has been updated.");
            redirect('portal/coupons/' . $encryptedId);
        } else {
            set_toast('error', 'Update Failed', 'Could not save coupon modifications.');
            redirect('portal/coupons/' . $encryptedId . '/edit');
        }
    }

    /**
     * Toggle coupon active/disabled status. Supports both AJAX and standard redirects.
     */
    public function toggleStatus(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'manage')) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Access Denied: You do not have clearance to modify coupon status.'], 403);
            }
            set_flash('error', 'Access Denied: Insufficient permissions.');
            redirect('portal/coupons');
        }

        $id = is_numeric($encryptedId) ? (int)$encryptedId : decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid coupon identifier.'], 400);
            }
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('portal/coupons');
        }

        $newStatus = Coupon::toggleStatus($id);

        if ($this->isAjax()) {
            if ($newStatus === null) {
                $this->jsonResponse(['success' => false, 'message' => 'Could not change coupon status.']);
            }
            $this->jsonResponse([
                'success'    => true,
                'is_active'  => $newStatus,
                'status_str' => $newStatus ? 'active' : 'disabled',
                'message'    => 'Coupon status changed to ' . ($newStatus ? 'Active' : 'Disabled') . '.'
            ]);
        }

        if ($newStatus === null) {
            set_toast('error', 'Action Failed', 'Could not change coupon status.');
        } else {
            $label = $newStatus ? 'activated' : 'deactivated';
            set_toast('success', 'Status Changed', "Coupon has been {$label}.");
        }

        $returnUrl = $_POST['return_url'] ?? ($_SERVER['HTTP_REFERER'] ?? url('portal/coupons'));
        header('Location: ' . $returnUrl);
        exit;
    }

    /**
     * Permanently delete a promotional coupon (safeguarded against deletion if used in orders).
     */
    public function destroy(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'manage')) {
            set_flash('error', 'Access Denied: Insufficient permissions to delete promotional campaigns.');
            redirect('portal/coupons');
        }

        $id = is_numeric($encryptedId) ? (int)$encryptedId : decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid coupon identifier.');
            redirect('portal/coupons');
        }

        $result = Coupon::delete($id);
        if ($result === false) {
            set_toast('error', 'Cannot Delete', 'This coupon has been used in client orders and cannot be deleted. Deactivate it instead.');
        } elseif ($result) {
            set_toast('success', 'Coupon Deleted', 'Promotional campaign has been removed.');
        } else {
            set_toast('error', 'Delete Failed', 'Coupon not found or could not be removed.');
        }

        redirect('portal/coupons');
    }

    /**
     * AJAX endpoint to generate a fresh unique promotional code.
     */
    public function generateCode(): void {
        PortalAuthMiddleware::check();

        $prefix = strtoupper(trim($_GET['prefix'] ?? 'JIYAJI'));
        $code = Coupon::generateCode($prefix);
        $this->jsonResponse(['success' => true, 'code' => $code]);
    }

    /**
     * Export promotional campaigns ledger to CSV.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('coupons', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/coupons');
        }

        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'type'   => $_GET['type']   ?? 'all',
            'search' => trim($_GET['search'] ?? ''),
            'sort'   => $_GET['sort']   ?? 'newest',
        ];

        $couponData = Coupon::getAll($filters, 1, 5000);
        $coupons    = $couponData['coupons'] ?? [];

        $filename = 'jiyaji_coupons_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Coupon Code', 'Description', 'Discount Type', 'Value', 'Max Cap (INR)',
            'Min Basket (INR)', 'Global Limit', 'Limit Per User', 'Times Used',
            'Storefront Public', 'Status', 'Valid From', 'Valid Until', 'Created At'
        ]);

        foreach ($coupons as $c) {
            fputcsv($out, [
                $c['code'],
                $c['description'] ?? '',
                strtoupper($c['type']),
                $c['type'] === 'percentage' ? $c['value'] . '%' : '₹' . number_format((float)$c['value']),
                !empty($c['max_discount_cap']) ? '₹' . number_format((float)$c['max_discount_cap']) : 'None',
                !empty($c['min_cart_value']) ? '₹' . number_format((float)$c['min_cart_value']) : '0',
                $c['usage_limit_global'] ?: 'Unlimited',
                $c['usage_limit_per_user'] ?: '1',
                (int)$c['times_used'],
                $c['is_public'] ? 'YES' : 'NO',
                strtoupper($c['computed_status']),
                !empty($c['starts_at']) ? date('Y-m-d H:i:s', strtotime($c['starts_at'])) : 'Immediate',
                !empty($c['expires_at']) ? date('Y-m-d H:i:s', strtotime($c['expires_at'])) : 'Never',
                !empty($c['created_at']) ? date('Y-m-d H:i:s', strtotime($c['created_at'])) : '',
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Check if current HTTP request is an AJAX call.
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }

    /**
     * Helper to safely return JSON response.
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode($data);
        exit;
    }
}
