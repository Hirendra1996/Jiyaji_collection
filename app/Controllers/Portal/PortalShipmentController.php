<?php

namespace App\Controllers\Portal;

use App\Models\Shipment;
use App\Models\Order;
use App\Middleware\PortalAuthMiddleware;

class PortalShipmentController {

    /**
     * Display shipments and logistics tracking management hub.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('shipments', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view shipments.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'  => $_GET['status'] ?? 'all',
            'carrier' => $_GET['carrier'] ?? 'all',
            'search'  => trim($_GET['search'] ?? '')
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $shipmentData = Shipment::getShipments($filters, $page, 15);
        $shipments    = $shipmentData['shipments'];
        $pagination   = $shipmentData['pagination'];

        $kpis         = Shipment::getShipmentKPIs();
        $carrierStats = Shipment::getCarrierStats();
        $readyOrders  = Shipment::getReadyToShipOrders();

        $canCreate = staff_can('shipments', 'create');

        $title = 'Shipments & AWBs Logistics Hub | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/shipments/index.php';
    }

    /**
     * Display comprehensive details and live milestone timeline for a single shipment.
     */
    public function show(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('shipments', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view shipments.');
            redirect('portal/dashboard');
        }

        $orderId = decrypt_id($encryptedId);
        if (!$orderId) {
            set_flash('error', 'Invalid shipment identifier.');
            redirect('portal/shipments');
        }

        $order = Order::getById($orderId);
        if (!$order) {
            set_flash('error', 'No order found matching this shipment identifier.');
            redirect('portal/shipments');
        }

        $milestones = Shipment::getTrackingMilestones($orderId);
        $canCreate  = staff_can('shipments', 'create');

        $title = 'Shipment #' . htmlspecialchars($order['awb_number'] ?? $order['order_number']) . ' | Staff Portal';
        include __DIR__ . '/../../Views/portal/shipments/show.php';
    }

    /**
     * Create a new carrier shipment booking and allocate an AWB for an order.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('shipments', 'create')) {
            set_flash('error', 'Access Denied: You do not have clearance to book shipments or allocate AWBs.');
            redirect('portal/shipments');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token. Please try again.');
            redirect('portal/shipments');
        }

        $orderEncId = $_POST['order_encrypted_id'] ?? '';
        $orderId    = is_numeric($orderEncId) ? (int)$orderEncId : (int)decrypt_id($orderEncId);

        if (!$orderId) {
            set_flash('error', 'Please select a valid order for shipment dispatch.');
            redirect('portal/shipments');
        }

        $courier     = trim($_POST['courier_partner'] ?? 'BlueDart Express');
        $tier        = trim($_POST['shipping_tier'] ?? 'Express Air');
        $weight      = !empty($_POST['package_weight']) ? (float)$_POST['package_weight'] : 1.25;
        $customAwb   = !empty($_POST['custom_awb']) ? trim($_POST['custom_awb']) : null;
        $estDelivery = !empty($_POST['estimated_delivery']) ? $_POST['estimated_delivery'] : null;

        $staff   = auth_staff();
        $staffId = $staff['id'] ?? null;

        $created = Shipment::createShipment($orderId, $courier, $tier, $weight, $customAwb, $estDelivery, $staffId);

        if ($created) {
            set_flash('success', 'Shipment successfully booked with ' . htmlspecialchars($courier) . '. AWB allocated and status advanced to "Shipped".');
        } else {
            set_flash('error', 'Failed to create shipment. Please verify order state.');
        }

        redirect('portal/shipments');
    }

    /**
     * Log a real-time carrier scan milestone and synchronize order fulfillment status.
     */
    public function addMilestone(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('shipments', 'create')) {
            set_flash('error', 'Access Denied: You do not have clearance to record tracking scans.');
            redirect('portal/shipments/' . $encryptedId);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_flash('error', 'Invalid or expired session token.');
            redirect('portal/shipments/' . $encryptedId);
        }

        $orderId = decrypt_id($encryptedId);
        if (!$orderId) {
            set_flash('error', 'Invalid shipment link.');
            redirect('portal/shipments');
        }

        $order = Order::getById($orderId);
        if (!$order) {
            set_flash('error', 'Order not found.');
            redirect('portal/shipments');
        }

        $awb      = trim($_POST['awb_number'] ?? $order['awb_number'] ?? '');
        $status   = trim($_POST['status'] ?? 'in_transit');
        $location = trim($_POST['location'] ?? 'Logistics Hub');
        $activity = trim($_POST['activity'] ?? 'Package scanned at hub facility');

        $staff   = auth_staff();
        $staffId = $staff['id'] ?? null;

        $added = Shipment::addTrackingMilestone($orderId, $awb, $status, $location, $activity, $staffId);

        if ($added) {
            set_flash('success', 'Carrier scan milestone recorded successfully.');
        } else {
            set_flash('error', 'Failed to log tracking milestone.');
        }

        redirect('portal/shipments/' . $encryptedId);
    }

    /**
     * Render the printable courier shipping label and thermal packing slip.
     */
    public function label(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        if (!staff_can('shipments', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to print shipping labels.');
            redirect('portal/dashboard');
        }

        $orderId = decrypt_id($encryptedId);
        if (!$orderId) {
            set_flash('error', 'Invalid order identifier.');
            redirect('portal/shipments');
        }

        $order = Order::getById($orderId);
        if (!$order) {
            set_flash('error', 'Order not found.');
            redirect('portal/shipments');
        }

        $title = 'Shipping Label #' . htmlspecialchars($order['awb_number'] ?? $order['order_number']) . ' | Jiyaji LX';
        include __DIR__ . '/../../Views/portal/shipments/label.php';
    }

    /**
     * Asynchronous JSON endpoint returning milestones for the timeline drawer.
     */
    public function trackJson(?string $encryptedId = null): void {
        PortalAuthMiddleware::check();

        header('Content-Type: application/json; charset=utf-8');

        $orderId = decrypt_id($encryptedId);
        if (!$orderId) {
            http_response_code(404);
            echo json_encode(['error' => 'Invalid order identifier']);
            exit;
        }

        $order = Order::getById($orderId);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }

        $milestones = Shipment::getTrackingMilestones($orderId);

        echo json_encode([
            'order_id'           => $encryptedId,
            'order_number'       => $order['order_number'],
            'awb_number'         => $order['awb_number'],
            'courier_partner'    => $order['courier_partner'],
            'status'             => $order['status'],
            'shipping_name'      => $order['shipping_name'],
            'shipping_city'      => $order['shipping_city'],
            'shipping_state'     => $order['shipping_state'],
            'shipping_pincode'   => $order['shipping_pincode'],
            'estimated_delivery' => $order['estimated_delivery'],
            'milestones'         => $milestones
        ]);
        exit;
    }

    /**
     * Stream CSV dispatch manifest for courier partner pickups.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('shipments', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to export shipments.');
            redirect('portal/shipments');
        }

        $filters = [
            'status'  => $_GET['status'] ?? 'all',
            'carrier' => $_GET['carrier'] ?? 'all',
            'search'  => trim($_GET['search'] ?? '')
        ];

        $shipmentData = Shipment::getShipments($filters, 1, 2000);
        $shipments    = $shipmentData['shipments'] ?? [];

        $filename = 'logistics_manifest_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        // Manifest Header Row
        fputcsv($output, [
            'AWB Number',
            'Order Number',
            'Courier Partner',
            'Delivery Status',
            'Recipient Name',
            'Recipient Phone',
            'Destination City',
            'Destination State',
            'Pincode',
            'Order Value (INR)',
            'Payment Mode',
            'Estimated Delivery',
            'Dispatch Date',
            'Latest Milestone Activity'
        ]);

        foreach ($shipments as $s) {
            fputcsv($output, [
                $s['awb_number'] ?? 'UNASSIGNED',
                $s['order_number'] ?? 'N/A',
                $s['courier_partner'] ?? 'Standard Courier',
                ucfirst(str_replace('_', ' ', $s['status'] ?? '')),
                $s['shipping_name'] ?? 'Customer',
                $s['shipping_phone'] ?? 'N/A',
                $s['shipping_city'] ?? 'N/A',
                $s['shipping_state'] ?? 'N/A',
                $s['shipping_pincode'] ?? 'N/A',
                number_format((float)($s['grand_total'] ?? 0), 2, '.', ''),
                strtoupper($s['payment_method'] ?? 'PREPAID'),
                $s['estimated_delivery'] ?? 'N/A',
                date('Y-m-d H:i', strtotime($s['placed_at'])),
                $s['latest_activity'] ?? 'Booking recorded'
            ]);
        }

        fclose($output);
        exit;
    }
}
