<?php

namespace App\Controllers\Portal;

use App\Models\Inventory;
use App\Models\Category;
use App\Middleware\PortalAuthMiddleware;
use Exception;

/**
 * PortalStockController
 *
 * Provides the Stock & Replenishment Hub for the Staff & Operations Portal.
 * All routes are guarded by PortalAuthMiddleware and granular RBAC:
 *   - products:view   → view stock ledger, movements log, CSV exports
 *   - products:edit   → perform replenishment and adjustments
 */
class PortalStockController {

    /**
     * Main inventory health ledger with KPI dashboard.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Stock & Inventory.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'      => $_GET['status'] ?? 'all',
            'category_id' => $_GET['category_id'] ?? 'all',
            'search'      => trim($_GET['search'] ?? ''),
            'sort'        => $_GET['sort'] ?? 'stock_asc',
        ];

        $page     = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $ledger   = Inventory::getVariantsLedger($filters, $page, 15);
        $variants = $ledger['variants'];

        $pagination = $ledger['pagination'];
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis       = Inventory::getKPIs();
        $categories = Category::getCandidateParents(); // for filter dropdown

        $canManage = staff_can('products', 'edit');

        $title = 'Stock & Replenishment | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/stock/index.php';
    }

    /**
     * Stock movement audit log.
     */
    public function movements(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Stock Movements.');
            redirect('portal/dashboard');
        }

        $filters = [
            'reason'     => $_GET['reason'] ?? 'all',
            'direction'  => $_GET['direction'] ?? 'all',
            'search'     => trim($_GET['search'] ?? ''),
            'date_from'  => trim($_GET['date_from'] ?? ''),
            'date_to'    => trim($_GET['date_to'] ?? ''),
        ];

        // Normalize reason 'all' to empty
        if ($filters['reason'] === 'all') {
            $filters['reason'] = '';
        }
        if ($filters['direction'] === 'all') {
            $filters['direction'] = '';
        }

        $page   = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $data   = Inventory::getMovements($filters, $page, 20);

        $movements  = $data['movements'];
        $pagination = $data['pagination'];
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $title = 'Stock Movement Audit Log | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/stock/movements.php';
    }

    /**
     * Process a single variant replenishment / adjustment via POST.
     */
    public function replenish(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to update stock levels.');
            redirect('portal/stock');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired or request invalid.');
            redirect('portal/stock');
        }

        $encryptedVariantId = trim($_POST['variant_id'] ?? '');
        $qty    = isset($_POST['qty']) && is_numeric($_POST['qty']) ? (int)$_POST['qty'] : 0;
        $reason = trim($_POST['reason'] ?? 'manual_restock');
        $note   = trim($_POST['note'] ?? '');

        $validReasons = ['manual_restock', 'adjustment', 'return', 'cancellation'];
        if (!in_array($reason, $validReasons, true)) {
            $reason = 'manual_restock';
        }

        $variantId = decrypt_id($encryptedVariantId);
        if (!$variantId) {
            set_toast('error', 'Invalid SKU', 'The selected variant identifier is invalid.');
            redirect('portal/stock');
        }

        if ($qty === 0) {
            set_toast('error', 'No Change', 'Quantity must be non-zero to record a movement.');
            redirect('portal/stock');
        }

        // Determine staff author
        $staff     = auth_staff();
        $staffId   = (int)($staff['id'] ?? 0) ?: null;
        $noteText  = !empty($note) ? $note : null;

        $success = Inventory::recordMovement($variantId, $qty, $reason, $noteText, null, $staffId);

        if ($success) {
            $action = $qty > 0 ? "Restocked +{$qty} units" : "Adjusted {$qty} units";
            set_toast('success', 'Stock Updated', "{$action} recorded successfully in audit log.");
        } else {
            set_toast('error', 'Update Failed', 'Could not update stock level. Please try again.');
        }

        redirect('portal/stock');
    }

    /**
     * Batch replenish multiple variants in a single transaction.
     */
    public function batchReplenish(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to perform batch replenishment.');
            redirect('portal/stock');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired or request invalid.');
            redirect('portal/stock');
        }

        $variantIds = $_POST['variant_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $reason     = trim($_POST['reason'] ?? 'manual_restock');
        $note       = trim($_POST['note'] ?? '');

        $validReasons = ['manual_restock', 'adjustment', 'return', 'cancellation'];
        if (!in_array($reason, $validReasons, true)) {
            $reason = 'manual_restock';
        }

        if (empty($variantIds) || !is_array($variantIds)) {
            set_toast('error', 'No Items', 'Please select at least one variant to replenish.');
            redirect('portal/stock');
        }

        $replenishments = [];
        foreach ($variantIds as $encId) {
            $variantId = decrypt_id((string)$encId);
            if (!$variantId) {
                continue;
            }
            $qty = (int)($quantities[$encId] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $replenishments[] = ['variant_id' => $variantId, 'qty' => $qty];
        }

        if (empty($replenishments)) {
            set_toast('error', 'No Valid Items', 'Please enter quantities greater than zero for at least one variant.');
            redirect('portal/stock');
        }

        $staff   = auth_staff();
        $staffId = (int)($staff['id'] ?? 0) ?: null;
        $result  = Inventory::batchReplenish($replenishments, $reason, !empty($note) ? $note : null, $staffId);

        if ($result['success'] > 0) {
            $msg = "{$result['success']} SKU(s) restocked successfully.";
            if ($result['failed'] > 0) {
                $msg .= " {$result['failed']} SKU(s) failed.";
            }
            set_toast('success', 'Batch Replenishment Complete', $msg);
        } else {
            set_toast('error', 'Replenishment Failed', 'Could not complete batch replenishment. Please review and retry.');
        }

        redirect('portal/stock');
    }

    /**
     * Stream CSV export of current stock inventory positions.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/stock');
        }

        $filters = [
            'status'      => $_GET['status'] ?? 'all',
            'category_id' => $_GET['category_id'] ?? 'all',
            'search'      => trim($_GET['search'] ?? ''),
            'sort'        => $_GET['sort'] ?? 'stock_asc',
        ];

        $all = Inventory::getVariantsLedger($filters, 1, 2000);

        $filename = 'jiyaji_stock_inventory_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Variant ID', 'SKU', 'Product Name', 'Category', 'Variant Name',
            'Color', 'Color Hex', 'Size', 'Stock Qty', 'Low Stock Threshold',
            'Health Status', 'Backorder Allowed', 'Effective Price (INR)',
            'Alert Active', 'Alert Resolved'
        ]);

        foreach ($all['variants'] as $v) {
            $stockQty  = (int)$v['stock_qty'];
            $threshold = (int)$v['low_stock_threshold'];
            $health    = $stockQty === 0 ? 'Critical' : ($stockQty <= $threshold ? 'Low Stock' : 'Healthy');
            $price     = $v['price_override'] ?? $v['sale_price'] ?? $v['base_price'] ?? 0;
            $alertActive   = (!empty($v['alert_id']) && !(int)$v['alert_resolved']) ? 'Yes' : 'No';
            $alertResolved = !empty($v['alert_id']) ? ((int)$v['alert_resolved'] ? 'Yes' : 'No') : 'N/A';

            fputcsv($out, [
                $v['variant_id'],
                $v['sku'],
                $v['product_name'],
                $v['category_name'] ?? '',
                $v['variant_name'] ?? '',
                $v['color_name'] ?? '',
                $v['color_code'] ?? '',
                $v['size'] ?? '',
                $stockQty,
                $threshold,
                $health,
                !empty($v['allow_backorder']) ? 'Yes' : 'No',
                number_format((float)$price, 2),
                $alertActive,
                $alertResolved,
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Stream CSV export of stock movement audit history.
     */
    public function exportMovements(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('products', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/stock');
        }

        $filters = [
            'reason'    => ($_GET['reason'] ?? 'all') === 'all' ? '' : ($_GET['reason'] ?? ''),
            'direction' => ($_GET['direction'] ?? 'all') === 'all' ? '' : ($_GET['direction'] ?? ''),
            'search'    => trim($_GET['search'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to'] ?? ''),
        ];

        $all = Inventory::getMovements($filters, 1, 5000);

        $filename = 'jiyaji_stock_movements_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Movement ID', 'Date & Time', 'Product Name', 'SKU', 'Color', 'Size',
            'Delta (Units)', 'Direction', 'Reason', 'Reference ID', 'Note / Memo',
            'Stock After', 'Staff Member'
        ]);

        foreach ($all['movements'] as $m) {
            $delta     = (int)$m['movement'];
            $direction = $delta >= 0 ? 'IN (+)' : 'OUT (-)';

            fputcsv($out, [
                $m['movement_id'],
                date('M d, Y h:i A', strtotime($m['created_at'])),
                $m['product_name'],
                $m['sku'],
                $m['color_name'] ?? '',
                $m['size'] ?? '',
                $delta >= 0 ? "+{$delta}" : (string)$delta,
                $direction,
                ucfirst(str_replace('_', ' ', $m['reason'])),
                $m['reference_id'] ?? '',
                $m['note'] ?? '',
                $m['current_stock'],
                $m['staff_name'] ?? 'System',
            ]);
        }

        fclose($out);
        exit;
    }
}
