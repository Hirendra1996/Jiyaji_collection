<?php

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Middleware\AuthMiddleware;

class OrderController {
    /**
     * Display filtered order listing with status tabs, search, and KPIs.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'         => $_GET['status'] ?? 'all',
            'payment_status' => $_GET['payment_status'] ?? 'all',
            'payment_method' => $_GET['payment_method'] ?? 'all',
            'search'         => trim($_GET['search'] ?? ''),
            'date_from'      => $_GET['date_from'] ?? '',
            'date_to'        => $_GET['date_to'] ?? '',
            'sort'           => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $orderData = Order::getAll($filters, $page, 15);
        $orders = $orderData['orders'];
        $pagination = $orderData['pagination'];

        $counts = Order::getCountsByStatus();
        $kpis = Order::getOrderKPIs();

        $title = 'Orders Management | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/orders/index.php';
    }

    /**
     * Display detailed order view with items, status timeline, customer, and shipment info.
     * Receives URL-safe encrypted ID.
     */
    public function show(?string $encryptedId = null): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Order Link', 'The order identifier in the link is invalid, corrupted, or expired.');
            redirect('admin/orders');
        }

        $order = Order::getById($id);
        if (!$order) {
            set_toast('error', 'Order Not Found', 'No order was found matching this identifier.');
            redirect('admin/orders');
        }

        $title = 'Order ' . htmlspecialchars($order['order_number']) . ' | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/orders/show.php';
    }

    /**
     * Handle fulfillment status update with audit note.
     */
    public function updateStatus(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/orders');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Order Link', 'The order identifier is invalid.');
            redirect('admin/orders');
        }

        $status = $_POST['status'] ?? '';
        $note   = trim($_POST['note'] ?? '');
        $admin  = auth_admin();
        $adminId = $admin['id'] ?? null;

        $updated = Order::updateStatus($id, $status, $note, $adminId);

        if ($updated) {
            set_toast('success', 'Status Updated', 'Order fulfillment status has been updated to "' . ucfirst(str_replace('_', ' ', $status)) . '".');
        } else {
            set_toast('error', 'Update Failed', 'Unable to update order status. Please check selected status.');
        }

        redirect('admin/orders/' . $encryptedId);
    }

    /**
     * Handle payment status update.
     */
    public function updatePayment(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/orders');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Order Link', 'The order identifier is invalid.');
            redirect('admin/orders');
        }

        $paymentStatus = $_POST['payment_status'] ?? '';
        $updated = Order::updatePaymentStatus($id, $paymentStatus);

        if ($updated) {
            set_toast('success', 'Payment Status Updated', 'Order payment status updated to "' . ucfirst($paymentStatus) . '".');
        } else {
            set_toast('error', 'Update Failed', 'Failed to update payment status.');
        }

        redirect('admin/orders/' . $encryptedId);
    }

    /**
     * Handle courier and tracking number updates.
     */
    public function updateTracking(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/orders');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Order Link', 'The order identifier is invalid.');
            redirect('admin/orders');
        }

        $courier = trim($_POST['courier_partner'] ?? '');
        $awb     = trim($_POST['awb_number'] ?? '');
        $est     = !empty($_POST['estimated_delivery']) ? $_POST['estimated_delivery'] : null;

        $updated = Order::updateTracking($id, $courier, $awb, $est);

        if ($updated) {
            set_toast('success', 'Shipment Tracking Saved', 'Courier details and AWB tracking have been recorded.');
        } else {
            set_toast('error', 'Update Failed', 'Failed to save tracking information.');
        }

        redirect('admin/orders/' . $encryptedId);
    }

    /**
     * Display printable luxury invoice view.
     */
    public function invoice(?string $encryptedId = null): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Invoice Link', 'The invoice identifier is invalid.');
            redirect('admin/orders');
        }

        $order = Order::getById($id);
        if (!$order) {
            set_toast('error', 'Order Not Found', 'No order exists for this invoice.');
            redirect('admin/orders');
        }

        $title = 'Tax Invoice #' . htmlspecialchars($order['order_number']) . ' | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/orders/invoice.php';
    }

    /**
     * Export filtered orders to CSV download stream.
     */
    public function export(): void {
        AuthMiddleware::check();

        $filters = [
            'status'         => $_GET['status'] ?? 'all',
            'payment_status' => $_GET['payment_status'] ?? 'all',
            'payment_method' => $_GET['payment_method'] ?? 'all',
            'search'         => trim($_GET['search'] ?? ''),
            'date_from'      => $_GET['date_from'] ?? '',
            'date_to'        => $_GET['date_to'] ?? '',
            'sort'           => $_GET['sort'] ?? 'newest'
        ];

        $orders = Order::getExportData($filters);

        $filename = 'Jiyaji_LX_Orders_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Header
        fputcsv($output, [
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Placed Date',
            'Subtotal (₹)',
            'Tax (₹)',
            'Grand Total (₹)',
            'Payment Status',
            'Payment Method',
            'Fulfillment Status',
            'Courier Partner',
            'AWB Tracking',
            'Shipping City',
            'Shipping State'
        ]);

        foreach ($orders as $o) {
            fputcsv($output, [
                $o['order_number'],
                $o['customer_name'] ?? $o['shipping_name'],
                $o['customer_email'] ?? '',
                $o['placed_at'],
                number_format((float)$o['subtotal'], 2, '.', ''),
                number_format((float)$o['tax_amount'], 2, '.', ''),
                number_format((float)$o['grand_total'], 2, '.', ''),
                ucfirst($o['payment_status']),
                $o['payment_method'],
                ucfirst($o['status']),
                $o['courier_partner'] ?? '',
                $o['awb_number'] ?? '',
                $o['shipping_city'],
                $o['shipping_state']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Display print-ready shipping label for 4x6 / A4 thermal/laser printing.
     */
    public function shippingLabel(?string $encryptedId = null): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Shipping Label Link', 'The identifier is invalid.');
            redirect('admin/orders');
        }

        $order = Order::getById($id);
        if (!$order) {
            set_toast('error', 'Order Not Found', 'No order exists for this shipping label.');
            redirect('admin/orders');
        }

        $title = 'Shipping Label #' . htmlspecialchars($order['order_number']) . ' | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/orders/shipping_label.php';
    }

    /**
     * Display returns and exchanges management queue.
     */
    public function returns(): void {
        AuthMiddleware::check();

        $status = $_GET['status'] ?? 'all';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

        $returnData = Order::getReturns($status, $page, 15);
        $returns = $returnData['returns'];
        $pagination = $returnData['pagination'];
        $counts = Order::getReturnCountsByStatus();

        $title = 'Returns & Refund Queue | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/orders/returns.php';
    }

    /**
     * Update return status, reverse tracking, or trigger refund processing.
     */
    public function updateReturn(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/returns');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid Return ID', 'The return identifier is invalid.');
            redirect('admin/returns');
        }

        $status          = $_POST['status'] ?? '';
        $adminNote       = trim($_POST['admin_note'] ?? '');
        $reverseAwb      = !empty($_POST['reverse_awb']) ? trim($_POST['reverse_awb']) : null;
        $refundAmount    = !empty($_POST['refund_amount']) ? (float)$_POST['refund_amount'] : null;
        $refundMethod    = !empty($_POST['refund_method']) ? trim($_POST['refund_method']) : null;
        $gatewayRefundId = !empty($_POST['gateway_refund_id']) ? trim($_POST['gateway_refund_id']) : null;

        $admin   = auth_admin();
        $adminId = $admin['id'] ?? null;

        $updated = Order::updateReturnStatus(
            $id,
            $status,
            $adminNote,
            $reverseAwb,
            $refundAmount,
            $refundMethod,
            $gatewayRefundId,
            $adminId
        );

        if ($updated) {
            set_toast('success', 'Return Updated', 'Return request status changed to "' . strtoupper(str_replace('_', ' ', $status)) . '".');
        } else {
            set_toast('error', 'Update Failed', 'Failed to update return request status.');
        }

        redirect('admin/returns');
    }
}
