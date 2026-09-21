<?php

namespace App\Controllers\Portal;

use App\Models\ReturnRequest;
use App\Models\Order;
use App\Middleware\PortalAuthMiddleware;

class PortalReturnController {

    /**
     * Display filtered Returns & Exchanges list with KPI summary and status tabs.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Returns & Exchanges.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'       => $_GET['status'] ?? 'all',
            'request_type' => $_GET['request_type'] ?? 'all',
            'search'       => trim($_GET['search'] ?? ''),
            'date_from'    => $_GET['date_from'] ?? '',
            'date_to'      => $_GET['date_to'] ?? '',
            'sort'         => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $rmaData = ReturnRequest::getAll($filters, $page, 15);
        $returns = $rmaData['returns'];
        $pagination = $rmaData['pagination'];

        $counts = ReturnRequest::getStatusCounts();
        $kpis = ReturnRequest::getRmaKPIs();
        $canProcess = staff_can('returns', 'process');

        $title = 'Returns & Exchanges | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/returns/index.php';
    }

    /**
     * Display comprehensive details for a single return/exchange request.
     */
    public function show(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view return details.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid return identifier.');
            redirect('portal/returns');
        }

        $return = ReturnRequest::getById($id);
        if (!$return) {
            set_flash('error', 'No return request found matching this identifier.');
            redirect('portal/returns');
        }

        $canProcess = staff_can('returns', 'process');
        $title = 'RMA #' . str_pad($return['id'], 5, '0', STR_PAD_LEFT) . ' | Returns Portal';
        include __DIR__ . '/../../Views/portal/returns/show.php';
    }

    /**
     * Render the staff-assisted RMA registration form.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'process')) {
            set_flash('error', 'Access Denied: You do not have clearance to initiate return requests.');
            redirect('portal/returns');
        }

        $selectedOrder = null;
        if (!empty($_GET['order_id'])) {
            $orderId = decrypt_id($_GET['order_id']);
            if ($orderId) {
                $selectedOrder = Order::getById($orderId);
            }
        }

        // Recent delivered orders for selection dropdown if no order specified
        $recentOrders = [];
        if (!$selectedOrder) {
            $deliveredData = Order::getAll(['status' => 'delivered'], 1, 20);
            $recentOrders = $deliveredData['orders'] ?? [];
        }

        $title = 'Initiate Return / Exchange | Staff Operations Portal';
        include __DIR__ . '/../../Views/portal/returns/create.php';
    }

    /**
     * Handle submission of a new return / exchange request.
     */
    public function store(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'process')) {
            set_flash('error', 'Access Denied: You do not have clearance to initiate return requests.');
            redirect('portal/returns');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token. Please try again.');
            redirect('portal/returns/create');
        }

        $orderIdInput = $_POST['order_id'] ?? '';
        $orderId = is_numeric($orderIdInput) ? (int)$orderIdInput : (int)decrypt_id($orderIdInput);

        if (!$orderId) {
            set_flash('error', 'Please select a valid order for this return request.');
            redirect('portal/returns/create');
        }

        $order = Order::getById($orderId);
        if (!$order) {
            set_flash('error', 'Order not found in database.');
            redirect('portal/returns/create');
        }

        $selectedItemIds = $_POST['items'] ?? [];
        if (empty($selectedItemIds) || !is_array($selectedItemIds)) {
            set_flash('error', 'Please select at least one line item to return or exchange.');
            redirect('portal/returns/create' . (!empty($_POST['order_id']) ? '?order_id=' . urlencode($_POST['order_id']) : ''));
        }

        $items = [];
        $calculatedRefund = 0.00;
        foreach ($selectedItemIds as $orderItemId) {
            $qtyKey = 'qty_' . $orderItemId;
            $qty = isset($_POST[$qtyKey]) ? max(1, (int)$_POST[$qtyKey]) : 1;
            $items[] = [
                'order_item_id' => (int)$orderItemId,
                'quantity'      => $qty
            ];

            // Estimate item refund
            foreach ($order['items'] as $oi) {
                if ((int)$oi['id'] === (int)$orderItemId) {
                    $unitPrice = (float)($oi['unit_price'] ?? $oi['price'] ?? 0);
                    $calculatedRefund += ($unitPrice * $qty);
                    break;
                }
            }
        }

        $requestType   = in_array($_POST['request_type'] ?? '', ['return', 'exchange'], true) ? $_POST['request_type'] : 'return';
        $reason        = trim($_POST['reason'] ?? 'Customer request');
        $description   = trim($_POST['description'] ?? '');
        $exchangeNotes = trim($_POST['exchange_notes'] ?? '');
        $adminNote     = trim($_POST['admin_note'] ?? 'Initiated by staff member');

        $rmaId = ReturnRequest::createRma([
            'order_id'       => $orderId,
            'customer_id'    => $order['customer_id'] ?? null,
            'request_type'   => $requestType,
            'reason'         => $reason,
            'description'    => $description,
            'exchange_notes' => $exchangeNotes,
            'admin_note'     => $adminNote,
            'status'         => 'requested',
            'refund_amount'  => ($requestType === 'return') ? $calculatedRefund : 0.00,
            'items'          => $items
        ]);

        if ($rmaId) {
            set_flash('success', 'RMA Request #RMA-' . str_pad($rmaId, 5, '0', STR_PAD_LEFT) . ' has been registered successfully.');
            redirect('portal/returns/' . encrypt_id($rmaId));
        } else {
            set_flash('error', 'Failed to register return request. Please review input fields.');
            redirect('portal/returns/create');
        }
    }

    /**
     * Handle fulfillment stage advancement for an RMA.
     */
    public function updateStatus(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'process')) {
            set_flash('error', 'Access Denied: You do not have permission to modify RMA statuses.');
            redirect('portal/returns/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token.');
            redirect('portal/returns/' . $encryptedId);
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid return link.');
            redirect('portal/returns');
        }

        $status  = $_POST['status'] ?? '';
        $note    = trim($_POST['note'] ?? '');
        $staff   = auth_staff();
        $staffId = $staff['id'] ?? null;

        $updated = ReturnRequest::updateStatus($id, $status, $note, $staffId);

        if ($updated) {
            set_flash('success', 'RMA stage advanced to "' . ucfirst(str_replace('_', ' ', $status)) . '".');
        } else {
            set_flash('error', 'Failed to update RMA status. Invalid status transition.');
        }

        $redirectTarget = $_POST['redirect_to'] ?? ('portal/returns/' . $encryptedId);
        redirect($redirectTarget);
    }

    /**
     * Handle reverse logistics AWB waybill assignment.
     */
    public function updateTracking(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'process')) {
            set_flash('error', 'Access Denied: You do not have permission to assign reverse logistics AWBs.');
            redirect('portal/returns/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token.');
            redirect('portal/returns/' . $encryptedId);
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid return link.');
            redirect('portal/returns');
        }

        $reverseAwb = trim($_POST['reverse_awb'] ?? '');
        $note       = trim($_POST['note'] ?? '');

        if (empty($reverseAwb)) {
            set_flash('error', 'Please enter a valid reverse courier AWB tracking number.');
            redirect('portal/returns/' . $encryptedId);
        }

        $updated = ReturnRequest::updateTracking($id, $reverseAwb, $note);

        if ($updated) {
            set_flash('success', 'Reverse logistics AWB (' . htmlspecialchars($reverseAwb) . ') assigned.');
        } else {
            set_flash('error', 'Failed to save reverse AWB tracking.');
        }

        redirect('portal/returns/' . $encryptedId);
    }

    /**
     * Handle refund recording and payout reconciliation.
     */
    public function processRefund(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'process')) {
            set_flash('error', 'Access Denied: You do not have permission to issue refunds or credits.');
            redirect('portal/returns/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token.');
            redirect('portal/returns/' . $encryptedId);
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid return link.');
            redirect('portal/returns');
        }

        $amount    = (float)($_POST['refund_amount'] ?? 0);
        $method    = trim($_POST['refund_method'] ?? 'store_credit');
        $gatewayId = trim($_POST['gateway_refund_id'] ?? '');
        $note      = trim($_POST['note'] ?? '');

        if ($amount <= 0) {
            set_flash('error', 'Refund amount must be greater than zero.');
            redirect('portal/returns/' . $encryptedId);
        }

        $processed = ReturnRequest::processRefund($id, $amount, $method, $gatewayId, $note);

        if ($processed) {
            set_flash('success', 'Refund of ₹' . number_format($amount, 2) . ' recorded via ' . ucfirst(str_replace('_', ' ', $method)) . '.');
        } else {
            set_flash('error', 'Failed to process refund. Please try again.');
        }

        redirect('portal/returns/' . $encryptedId);
    }

    /**
     * Stream CSV ledger export for accounting and logistics reconciliation.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('returns', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to export returns.');
            redirect('portal/returns');
        }

        $filters = [
            'status'       => $_GET['status'] ?? 'all',
            'request_type' => $_GET['request_type'] ?? 'all',
            'search'       => trim($_GET['search'] ?? ''),
            'date_from'    => $_GET['date_from'] ?? '',
            'date_to'      => $_GET['date_to'] ?? '',
            'sort'         => $_GET['sort'] ?? 'newest'
        ];

        // Fetch up to 2000 matching records for export
        $rmaData = ReturnRequest::getAll($filters, 1, 2000);
        $returns = $rmaData['returns'] ?? [];

        $filename = 'returns_exchanges_ledger_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility

        // Header Row
        fputcsv($output, [
            'RMA ID',
            'Request Type',
            'Status',
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Reason',
            'Reverse AWB',
            'Refund Amount (INR)',
            'Refund Method',
            'Gateway Refund ID',
            'Items Count',
            'Exchange Notes',
            'Created Date'
        ]);

        foreach ($returns as $r) {
            fputcsv($output, [
                'RMA-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT),
                ucfirst($r['request_type'] ?? 'return'),
                ucfirst(str_replace('_', ' ', $r['status'] ?? '')),
                $r['order_number'] ?? 'N/A',
                $r['customer_name'] ?? 'Guest',
                $r['customer_email'] ?? 'N/A',
                $r['customer_phone'] ?? 'N/A',
                $r['reason'] ?? '',
                $r['reverse_awb'] ?? '',
                number_format((float)($r['refund_amount'] ?? 0), 2, '.', ''),
                ucfirst(str_replace('_', ' ', $r['refund_method'] ?? 'N/A')),
                $r['gateway_refund_id'] ?? '',
                (int)($r['item_count'] ?? 1),
                $r['exchange_notes'] ?? '',
                date('Y-m-d H:i', strtotime($r['created_at']))
            ]);
        }

        fclose($output);
        exit;
    }
}
