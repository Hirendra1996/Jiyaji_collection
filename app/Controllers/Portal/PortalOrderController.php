<?php

namespace App\Controllers\Portal;

use App\Models\Order;
use App\Middleware\PortalAuthMiddleware;

class PortalOrderController {

    /**
     * Display filtered order listing with status tabs, search, and operational KPIs.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'view')) {
            set_flash('error', 'Access Denied: You do not have permission to view store orders.');
            redirect('portal/dashboard');
        }

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

        $canEdit = staff_can('orders', 'edit');
        $canExport = staff_can('orders', 'export');

        $title = 'Orders Management | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/orders/index.php';
    }

    /**
     * Display detailed order view with items, customer details, status history, and courier tracking.
     */
    public function show(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'view')) {
            set_flash('error', 'Access Denied: You do not have permission to view order details.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid order identifier.');
            redirect('portal/orders');
        }

        $order = Order::getById($id);
        if (!$order) {
            set_flash('error', 'No order found matching this identifier.');
            redirect('portal/orders');
        }

        $canEdit = staff_can('orders', 'edit');

        $title = 'Order #' . htmlspecialchars($order['order_number']) . ' | Staff Portal';
        include __DIR__ . '/../../Views/portal/orders/show.php';
    }

    /**
     * Handle fulfillment status update with audit note.
     */
    public function updateStatus(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'edit')) {
            set_flash('error', 'Access Denied: You do not have permission to update order fulfillment states.');
            redirect('portal/orders/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token. Please try again.');
            redirect('portal/orders/' . $encryptedId);
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid order link.');
            redirect('portal/orders');
        }

        $status = $_POST['status'] ?? '';
        $note   = trim($_POST['note'] ?? '');
        $staff  = auth_staff();
        $staffId = $staff['id'] ?? null;

        $updated = Order::updateStatus($id, $status, $note, $staffId);

        if ($updated) {
            set_flash('success', 'Order fulfillment status updated to "' . ucfirst(str_replace('_', ' ', $status)) . '".');
        } else {
            set_flash('error', 'Failed to update order status. Please verify the selected status.');
        }

        $redirectTarget = $_POST['redirect_to'] ?? ('portal/orders/' . $encryptedId);
        redirect($redirectTarget);
    }

    /**
     * Handle payment status update.
     */
    public function updatePayment(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'edit')) {
            set_flash('error', 'Access Denied: You do not have permission to modify payment states.');
            redirect('portal/orders/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token.');
            redirect('portal/orders/' . $encryptedId);
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid order link.');
            redirect('portal/orders');
        }

        $paymentStatus = $_POST['payment_status'] ?? '';
        $updated = Order::updatePaymentStatus($id, $paymentStatus);

        if ($updated) {
            set_flash('success', 'Payment status updated to "' . ucfirst($paymentStatus) . '".');
        } else {
            set_flash('error', 'Failed to update payment status.');
        }

        redirect('portal/orders/' . $encryptedId);
    }

    /**
     * Handle courier partner and tracking AWB assignment.
     */
    public function updateTracking(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'edit')) {
            set_flash('error', 'Access Denied: You do not have permission to update tracking information.');
            redirect('portal/orders/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token.');
            redirect('portal/orders/' . $encryptedId);
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid order link.');
            redirect('portal/orders');
        }

        $courier = trim($_POST['courier_partner'] ?? '');
        $awb     = trim($_POST['awb_number'] ?? '');
        $est     = !empty($_POST['estimated_delivery']) ? $_POST['estimated_delivery'] : null;

        $updated = Order::updateTracking($id, $courier, $awb, $est);

        if ($updated) {
            set_flash('success', 'Shipment tracking information recorded.');
        } else {
            set_flash('error', 'Failed to save tracking details.');
        }

        redirect('portal/orders/' . $encryptedId);
    }

    /**
     * Display printable tax invoice view.
     */
    public function invoice(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'view')) {
            set_flash('error', 'Access Denied: You do not have permission to view invoices.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid invoice link.');
            redirect('portal/orders');
        }

        $order = Order::getById($id);
        if (!$order) {
            set_flash('error', 'No order found for this invoice.');
            redirect('portal/orders');
        }

        $title = 'Tax Invoice #' . htmlspecialchars($order['order_number']) . ' | Jiyaji LX';
        include __DIR__ . '/../../Views/portal/orders/invoice.php';
    }

    /**
     * Export filtered orders to CSV download stream.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('orders', 'export')) {
            set_flash('error', 'Access Denied: You do not have permission to export order records.');
            redirect('portal/orders');
        }

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

        $filename = 'Jiyaji_LX_Orders_Staff_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Header
        fputcsv($output, [
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Placed Date',
            'Subtotal (INR)',
            'Tax (INR)',
            'Grand Total (INR)',
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
}
