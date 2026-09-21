<?php

namespace App\Controllers\Admin;

use App\Models\Shipment;
use App\Models\Order;
use App\Middleware\AuthMiddleware;

class ShipmentController {
    /**
     * Display shipments and logistics tracking management center.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'  => $_GET['status'] ?? 'all',
            'carrier' => $_GET['carrier'] ?? 'all',
            'search'  => trim($_GET['search'] ?? '')
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $shipmentData = Shipment::getShipments($filters, $page, 15);
        $shipments    = $shipmentData['shipments'];
        $pagination   = $shipmentData['pagination'];

        $kpis         = Shipment::getShipmentKPIs();
        $carrierStats = Shipment::getCarrierStats();
        $readyOrders  = Shipment::getReadyToShipOrders();

        $title = 'Shipments & AWBs Logistics Hub | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/shipments/index.php';
    }

    /**
     * Create a new shipment booking and allocate an AWB for an order.
     */
    public function create(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/shipments');
        }

        $orderEncId = $_POST['order_encrypted_id'] ?? '';
        $orderId    = decrypt_id($orderEncId);

        if (!$orderId) {
            set_toast('error', 'Invalid Order', 'Could not identify the selected order.');
            redirect('admin/shipments');
        }

        $courier     = trim($_POST['courier_partner'] ?? 'BlueDart Express');
        $tier        = trim($_POST['shipping_tier'] ?? 'Express Air');
        $weight      = !empty($_POST['package_weight']) ? (float)$_POST['package_weight'] : 1.25;
        $customAwb   = !empty($_POST['custom_awb']) ? trim($_POST['custom_awb']) : null;
        $estDelivery = !empty($_POST['estimated_delivery']) ? $_POST['estimated_delivery'] : null;

        $admin   = auth_admin();
        $adminId = $admin['id'] ?? null;

        $created = Shipment::createShipment($orderId, $courier, $tier, $weight, $customAwb, $estDelivery, $adminId);

        if ($created) {
            set_toast('success', 'Shipment Booked', 'AWB allocated successfully and order status advanced to "Shipped".');
        } else {
            set_toast('error', 'Booking Failed', 'Unable to create shipment for this order.');
        }

        redirect('admin/shipments');
    }

    /**
     * Return JSON milestones for asynchronous modal tracking timeline.
     */
    public function track(?string $encryptedId = null): void {
        AuthMiddleware::check();

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
            'customer_name'      => $order['customer_name'],
            'courier_partner'    => $order['courier_partner'] ?: 'Unassigned',
            'awb_number'         => $order['awb_number'] ?: 'N/A',
            'destination_city'   => $order['shipping_city'],
            'destination_pincode'=> $order['shipping_pincode'],
            'current_status'     => $order['status'],
            'estimated_delivery' => $order['estimated_delivery'] ? date('d M Y', strtotime($order['estimated_delivery'])) : '3-4 Business Days',
            'milestones'         => $milestones
        ]);
        exit;
    }

    /**
     * Record a new tracking scan milestone and sync status.
     */
    public function addMilestone(?string $encryptedId = null): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/shipments');
        }

        $orderId = decrypt_id($encryptedId);
        if (!$orderId) {
            set_toast('error', 'Invalid Shipment', 'The shipment identifier is invalid.');
            redirect('admin/shipments');
        }

        $order = Order::getById($orderId);
        if (!$order) {
            set_toast('error', 'Order Not Found', 'Order does not exist.');
            redirect('admin/shipments');
        }

        $status   = $_POST['status'] ?? 'in_transit';
        $location = trim($_POST['location'] ?? 'Destination Delivery Facility');
        $activity = trim($_POST['activity'] ?? 'In transit to customer delivery address');

        $admin   = auth_admin();
        $adminId = $admin['id'] ?? null;

        $updated = Shipment::addTrackingMilestone(
            $orderId, 
            $order['awb_number'] ?: 'AWB-PENDING', 
            $status, 
            $location, 
            $activity, 
            $adminId
        );

        if ($updated) {
            set_toast('success', 'Tracking Scan Logged', 'Milestone event recorded and order status updated.');
        } else {
            set_toast('error', 'Update Failed', 'Unable to record tracking event.');
        }

        redirect('admin/shipments');
    }
}
