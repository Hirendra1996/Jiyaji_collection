<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Shipment {
    /**
     * Retrieve paginated and filtered list of shipments.
     */
    public static function getShipments(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'unassigned') {
                    $whereClauses[] = "(o.awb_number IS NULL OR o.awb_number = '') AND o.status IN ('pending', 'confirmed', 'packed')";
                } elseif ($filters['status'] === 'in_transit') {
                    $whereClauses[] = "o.status = 'shipped'";
                } elseif ($filters['status'] === 'out_for_delivery') {
                    $whereClauses[] = "o.status = 'out_for_delivery'";
                } elseif ($filters['status'] === 'delivered') {
                    $whereClauses[] = "o.status = 'delivered'";
                } elseif ($filters['status'] === 'failed') {
                    $whereClauses[] = "o.status = 'cancelled'";
                }
            }

            // Carrier partner filter
            if (!empty($filters['carrier']) && $filters['carrier'] !== 'all') {
                $whereClauses[] = "o.courier_partner LIKE ?";
                $params[] = "%" . $filters['carrier'] . "%";
                $types .= "s";
            }

            // Search query (AWB, Order number, customer name, pincode, city)
            if (!empty($filters['search'])) {
                $wildcard = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(o.awb_number LIKE ? OR o.order_number LIKE ? OR o.shipping_name LIKE ? OR c.name LIKE ? OR o.shipping_pincode LIKE ? OR o.shipping_city LIKE ?)";
                for ($i = 0; $i < 6; $i++) {
                    $params[] = $wildcard;
                    $types .= "s";
                }
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Total count
            $countSql = "
                SELECT COUNT(*) as total 
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE $whereSql
            ";
            $countStmt = $db->prepare($countSql);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $totalCount = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

            $page = max(1, $page);
            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $offset = ($page - 1) * $perPage;

            $dataSql = "
                SELECT o.*,
                       COALESCE(c.name, o.shipping_name, 'Customer') as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone,
                       (SELECT COUNT(*) FROM shipment_tracking_events WHERE order_id = o.id) as milestone_count,
                       (SELECT activity FROM shipment_tracking_events WHERE order_id = o.id ORDER BY event_time DESC LIMIT 1) as latest_activity
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE $whereSql
                ORDER BY CASE WHEN o.awb_number IS NOT NULL AND o.awb_number != '' THEN 0 ELSE 1 END, o.placed_at DESC
                LIMIT ?, ?
            ";

            $stmt = $db->prepare($dataSql);
            $bindParams = $params;
            $bindParams[] = $offset;
            $bindParams[] = $perPage;
            $bindTypes = $types . "ii";
            $stmt->bind_param($bindTypes, ...$bindParams);
            $stmt->execute();
            $res = $stmt->get_result();

            $shipments = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $shipments[] = $row;
            }

            return [
                'shipments'  => $shipments,
                'pagination' => [
                    'current_page'  => $page,
                    'per_page'      => $perPage,
                    'total_records' => $totalCount,
                    'total_pages'   => $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Shipment::getShipments error: " . $e->getMessage());
            return [
                'shipments'  => [],
                'pagination' => ['current_page' => 1, 'per_page' => $perPage, 'total_records' => 0, 'total_pages' => 1]
            ];
        }
    }

    /**
     * Retrieve executive shipment KPI counters.
     */
    public static function getShipmentKPIs(): array {
        $kpis = [
            'total_dispatched'   => 0,
            'in_transit'         => 0,
            'out_for_delivery'   => 0,
            'delivered'          => 0,
            'ready_for_dispatch' => 0
        ];

        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT 
                    COALESCE(SUM(CASE WHEN awb_number IS NOT NULL AND awb_number != '' THEN 1 ELSE 0 END), 0) as total_dispatched,
                    COALESCE(SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END), 0) as in_transit,
                    COALESCE(SUM(CASE WHEN status = 'out_for_delivery' THEN 1 ELSE 0 END), 0) as out_for_delivery,
                    COALESCE(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END), 0) as delivered,
                    COALESCE(SUM(CASE WHEN (awb_number IS NULL OR awb_number = '') AND status IN ('pending', 'confirmed', 'packed') THEN 1 ELSE 0 END), 0) as ready_for_dispatch
                FROM orders
            ");
            if ($res) {
                $row = $res->fetch_assoc();
                $kpis['total_dispatched']   = (int)$row['total_dispatched'];
                $kpis['in_transit']         = (int)$row['in_transit'];
                $kpis['out_for_delivery']   = (int)$row['out_for_delivery'];
                $kpis['delivered']          = (int)$row['delivered'];
                $kpis['ready_for_dispatch'] = (int)$row['ready_for_dispatch'];
            }
        } catch (Exception $e) {
            error_log("Shipment::getShipmentKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Get shipment volume breakdown by carrier partner.
     */
    public static function getCarrierStats(): array {
        $carriers = [
            'BlueDart Express' => ['count' => 0, 'tag' => 'Primary Air Carrier', 'color' => '#2D82FF'],
            'Delhivery Air'    => ['count' => 0, 'tag' => 'Express Priority', 'color' => '#8C30F5'],
            'Shiprocket'       => ['count' => 0, 'tag' => 'Multi-courier Aggregator', 'color' => '#FF5100'],
            'DTDC Express'     => ['count' => 0, 'tag' => 'Standard Surface', 'color' => '#10B981']
        ];

        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT courier_partner, COUNT(*) as cnt 
                FROM orders 
                WHERE courier_partner IS NOT NULL AND courier_partner != ''
                GROUP BY courier_partner
            ");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $cp = $row['courier_partner'];
                    $cnt = (int)$row['cnt'];
                    foreach ($carriers as $key => &$data) {
                        if (stripos($cp, explode(' ', $key)[0]) !== false) {
                            $data['count'] += $cnt;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Shipment::getCarrierStats error: " . $e->getMessage());
        }

        return $carriers;
    }

    /**
     * Retrieve orders ready for AWB allocation and dispatch.
     */
    public static function getReadyToShipOrders(): array {
        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT o.id, o.order_number, o.shipping_name, o.shipping_city, o.shipping_pincode, o.status, o.grand_total, o.payment_method, o.placed_at
                FROM orders o
                WHERE (o.awb_number IS NULL OR o.awb_number = '') AND o.status IN ('pending', 'confirmed', 'packed')
                ORDER BY o.placed_at ASC
            ");
            $orders = [];
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $row['encrypted_id'] = encrypt_id($row['id']);
                    $orders[] = $row;
                }
            }
            return $orders;
        } catch (Exception $e) {
            error_log("Shipment::getReadyToShipOrders error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a shipment: assign carrier, allocate AWB, update status to shipped, and log tracking scans.
     */
    public static function createShipment(
        int $orderId, 
        string $courier, 
        string $tier = 'Express Air', 
        float $weight = 1.25, 
        ?string $awb = null, 
        ?string $estDelivery = null,
        ?int $adminId = null
    ): bool {
        try {
            $db = Database::connect();
            $db->begin_transaction();

            // Auto-generate AWB if not supplied
            if (empty($awb)) {
                $prefix = match(true) {
                    stripos($courier, 'delhivery') !== false => 'DLHV',
                    stripos($courier, 'bluedart') !== false  => 'BD',
                    stripos($courier, 'shiprocket') !== false => 'SRKT',
                    default                                  => 'JLX'
                };
                $awb = $prefix . mt_rand(10000000, 99999999);
            }

            if (empty($estDelivery)) {
                $estDelivery = date('Y-m-d', strtotime('+3 days'));
            }

            // 1. Update orders table
            $stmt = $db->prepare("
                UPDATE orders 
                SET courier_partner = ?, awb_number = ?, estimated_delivery = ?, status = 'shipped', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssi", $courier, $awb, $estDelivery, $orderId);
            $stmt->execute();

            // 2. Insert into shipment_tracking_events
            $eStmt = $db->prepare("
                INSERT INTO shipment_tracking_events (order_id, awb_number, status, location, activity, event_time)
                VALUES (?, ?, 'manifest_created', 'Central Logistics Hub (Delhi)', ?, NOW())
            ");
            $act = "Electronic shipping label generated and booking confirmed with {$courier} ({$tier}). Weight: {$weight} KG.";
            $eStmt->bind_param("iss", $orderId, $awb, $act);
            $eStmt->execute();

            // 3. Log into order_status_history
            $hStmt = $db->prepare("
                INSERT INTO order_status_history (order_id, status, note, changed_by, changed_at)
                VALUES (?, 'shipped', ?, ?, NOW())
            ");
            $note = "Shipment dispatched with {$courier} ({$tier}). Allocated AWB: {$awb}.";
            $hStmt->bind_param("issi", $orderId, $note, $adminId);
            $hStmt->execute();

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Shipment::createShipment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve chronological tracking milestones for an order.
     */
    public static function getTrackingMilestones(int $orderId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT * FROM shipment_tracking_events 
                WHERE order_id = ? 
                ORDER BY event_time ASC, id ASC
            ");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $res = $stmt->get_result();

            $events = [];
            while ($row = $res->fetch_assoc()) {
                $events[] = $row;
            }
            return $events;
        } catch (Exception $e) {
            error_log("Shipment::getTrackingMilestones error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add a real-time carrier tracking scan milestone.
     */
    public static function addTrackingMilestone(
        int $orderId, 
        string $awb, 
        string $status, 
        string $location, 
        string $activity, 
        ?int $adminId = null
    ): bool {
        try {
            $db = Database::connect();
            $db->begin_transaction();

            // 1. Insert tracking event
            $stmt = $db->prepare("
                INSERT INTO shipment_tracking_events (order_id, awb_number, status, location, activity, event_time)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("issss", $orderId, $awb, $status, $location, $activity);
            $stmt->execute();

            // 2. Sync order status if milestone matches lifecycle transition
            $orderStatusToSet = null;
            if ($status === 'out_for_delivery') {
                $orderStatusToSet = 'out_for_delivery';
            } elseif ($status === 'delivered') {
                $orderStatusToSet = 'delivered';
            } elseif ($status === 'cancelled' || $status === 'rto') {
                $orderStatusToSet = 'cancelled';
            }

            if ($orderStatusToSet !== null) {
                $uStmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $uStmt->bind_param("si", $orderStatusToSet, $orderId);
                $uStmt->execute();

                $hStmt = $db->prepare("
                    INSERT INTO order_status_history (order_id, status, note, changed_by, changed_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $note = "Carrier scan update: {$activity} ({$location}).";
                $hStmt->bind_param("issi", $orderId, $orderStatusToSet, $note, $adminId);
                $hStmt->execute();
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Shipment::addTrackingMilestone error: " . $e->getMessage());
            return false;
        }
    }
}
