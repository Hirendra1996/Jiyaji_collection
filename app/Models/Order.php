<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Order {
    /**
     * Retrieve paginated and filtered list of orders.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();

            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Filter by fulfillment status
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $whereClauses[] = "o.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }

            // Filter by payment status
            if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
                $whereClauses[] = "o.payment_status = ?";
                $params[] = $filters['payment_status'];
                $types .= "s";
            }

            // Filter by payment method
            if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
                $whereClauses[] = "o.payment_method LIKE ?";
                $params[] = "%" . $filters['payment_method'] . "%";
                $types .= "s";
            }

            // Search query (Order number, customer name, email, phone)
            if (!empty($filters['search'])) {
                $searchWildcard = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(o.order_number LIKE ? OR o.shipping_name LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR o.shipping_phone LIKE ?)";
                for ($i = 0; $i < 5; $i++) {
                    $params[] = $searchWildcard;
                    $types .= "s";
                }
            }

            // Date range filter
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "DATE(o.placed_at) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "DATE(o.placed_at) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting
            $sortSql = "ORDER BY o.placed_at DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'oldest'      => "ORDER BY o.placed_at ASC",
                    'amount_high' => "ORDER BY o.grand_total DESC",
                    'amount_low'  => "ORDER BY o.grand_total ASC",
                    default       => "ORDER BY o.placed_at DESC"
                };
            }

            // Total count query
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

            // Pagination calculation
            $page = max(1, $page);
            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $offset = ($page - 1) * $perPage;

            // Fetch records query
            $dataSql = "
                SELECT o.*,
                       COALESCE(c.name, o.shipping_name, 'Guest Customer') AS customer_name,
                       c.email AS customer_email,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE $whereSql
                $sortSql
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

            $orders = [];
            while ($row = $res->fetch_assoc()) {
                // Attach encrypted ID
                $row['encrypted_id'] = encrypt_id($row['id']);
                $orders[] = $row;
            }

            return [
                'orders'     => $orders,
                'pagination' => [
                    'current_page'  => $page,
                    'per_page'      => $perPage,
                    'total_records' => $totalCount,
                    'total_pages'   => $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Order::getAll error: " . $e->getMessage());
            return [
                'orders'     => [],
                'pagination' => ['current_page' => 1, 'per_page' => $perPage, 'total_records' => 0, 'total_pages' => 1]
            ];
        }
    }

    /**
     * Get order counts by status for tab headers.
     */
    public static function getCountsByStatus(): array {
        $counts = [
            'all'              => 0,
            'pending'          => 0,
            'confirmed'        => 0,
            'packed'           => 0,
            'shipped'          => 0,
            'out_for_delivery' => 0,
            'delivered'        => 0,
            'cancelled'        => 0
        ];

        try {
            $db = Database::connect();
            $res = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $st = strtolower($row['status']);
                    $cnt = (int)$row['cnt'];
                    $counts['all'] += $cnt;
                    if (isset($counts[$st])) {
                        $counts[$st] = $cnt;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Order::getCountsByStatus error: " . $e->getMessage());
        }

        return $counts;
    }

    /**
     * Aggregate executive order KPIs for top overview banner.
     */
    public static function getOrderKPIs(): array {
        $kpis = [
            'total_orders'      => 0,
            'total_revenue'     => 0.00,
            'pending_dispatch'  => 0,
            'in_transit'        => 0,
            'delivered_orders'  => 0
        ];

        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN grand_total ELSE 0 END), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN status IN ('pending', 'confirmed', 'packed') THEN 1 ELSE 0 END), 0) as pending_dispatch,
                    COALESCE(SUM(CASE WHEN status IN ('shipped', 'out_for_delivery') THEN 1 ELSE 0 END), 0) as in_transit,
                    COALESCE(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END), 0) as delivered_orders
                FROM orders
            ");
            if ($res) {
                $row = $res->fetch_assoc();
                $kpis['total_orders']     = (int)$row['total_orders'];
                $kpis['total_revenue']    = (int)round((float)$row['total_revenue']);
                $kpis['pending_dispatch'] = (int)$row['pending_dispatch'];
                $kpis['in_transit']       = (int)$row['in_transit'];
                $kpis['delivered_orders'] = (int)$row['delivered_orders'];
            }
        } catch (Exception $e) {
            error_log("Order::getOrderKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Retrieve single order details by internal database ID.
     */
    public static function getById(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT o.*,
                       COALESCE(c.name, o.shipping_name, 'Guest Customer') AS customer_name,
                       c.email AS customer_email,
                       c.phone AS customer_phone,
                       c.created_at AS customer_registered_at,
                       (SELECT COUNT(*) FROM orders WHERE customer_id = o.customer_id) AS customer_total_orders
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE o.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $order = $res->fetch_assoc();

            if ($order) {
                $order['encrypted_id'] = encrypt_id($order['id']);
                $order['items'] = self::getItems($id);
                $order['history'] = self::getStatusHistory($id);
                return $order;
            }
            return null;
        } catch (Exception $e) {
            error_log("Order::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve line items for a specific order.
     */
    public static function getItems(int $orderId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT oi.*, p.slug as product_slug
                FROM order_items oi
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                LEFT JOIN products p ON pv.product_id = p.id
                WHERE oi.order_id = ?
                ORDER BY oi.id ASC
            ");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $res = $stmt->get_result();

            $items = [];
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }
            return $items;
        } catch (Exception $e) {
            error_log("Order::getItems error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve chronological status transition history log for an order.
     */
    public static function getStatusHistory(int $orderId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT osh.*, a.name AS admin_name
                FROM order_status_history osh
                LEFT JOIN admins a ON osh.changed_by = a.id
                WHERE osh.order_id = ?
                ORDER BY osh.changed_at ASC
            ");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $res = $stmt->get_result();

            $history = [];
            while ($row = $res->fetch_assoc()) {
                $history[] = $row;
            }
            return $history;
        } catch (Exception $e) {
            error_log("Order::getStatusHistory error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update order fulfillment status and log into audit trail history.
     */
    public static function updateStatus(int $orderId, string $status, string $note = '', ?int $adminId = null): bool {
        $allowed = ['pending', 'confirmed', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        try {
            $db = Database::connect();
            $db->begin_transaction();

            // 1. Update orders table
            $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $orderId);
            $stmt->execute();

            // 2. Insert into status history
            $hStmt = $db->prepare("INSERT INTO order_status_history (order_id, status, note, changed_by, changed_at) VALUES (?, ?, ?, ?, NOW())");
            $hStmt->bind_param("issi", $orderId, $status, $note, $adminId);
            $hStmt->execute();

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Order::updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order payment status.
     */
    public static function updatePaymentStatus(int $orderId, string $paymentStatus): bool {
        $allowed = ['pending', 'paid', 'failed', 'refunded', 'partial_refund'];
        if (!in_array($paymentStatus, $allowed, true)) {
            return false;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $paymentStatus, $orderId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order::updatePaymentStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update courier tracking information for an order.
     */
    public static function updateTracking(int $orderId, string $courier, string $awb, ?string $estimatedDelivery = null): bool {
        try {
            $db = Database::connect();
            $est = !empty($estimatedDelivery) ? $estimatedDelivery : null;
            $stmt = $db->prepare("
                UPDATE orders 
                SET courier_partner = ?, awb_number = ?, estimated_delivery = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssi", $courier, $awb, $est, $orderId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order::updateTracking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch orders for CSV export.
     */
    public static function getExportData(array $filters = []): array {
        $all = self::getAll($filters, 1, 10000);
        return $all['orders'];
    }

    /**
     * Retrieve paginated list of returns and exchange requests.
     */
    public static function getReturns(?string $statusFilter = null, int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($statusFilter) && $statusFilter !== 'all') {
                $whereClauses[] = "r.status = ?";
                $params[] = $statusFilter;
                $types .= "s";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Count
            $countSql = "SELECT COUNT(*) as total FROM returns r WHERE $whereSql";
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
                SELECT r.*,
                       o.order_number,
                       o.grand_total as order_total,
                       o.payment_method,
                       o.payment_status,
                       COALESCE(c.name, o.shipping_name, 'Customer') as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone
                FROM returns r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN customers c ON r.customer_id = c.id
                WHERE $whereSql
                ORDER BY r.created_at DESC
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

            $returns = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $row['order_encrypted_id'] = encrypt_id($row['order_id']);
                $returns[] = $row;
            }

            return [
                'returns'    => $returns,
                'pagination' => [
                    'current_page'  => $page,
                    'per_page'      => $perPage,
                    'total_records' => $totalCount,
                    'total_pages'   => $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Order::getReturns error: " . $e->getMessage());
            return [
                'returns'    => [],
                'pagination' => ['current_page' => 1, 'per_page' => $perPage, 'total_records' => 0, 'total_pages' => 1]
            ];
        }
    }

    /**
     * Get return request counts grouped by status.
     */
    public static function getReturnCountsByStatus(): array {
        $counts = [
            'all'              => 0,
            'requested'        => 0,
            'approved'         => 0,
            'rejected'         => 0,
            'pickup_scheduled' => 0,
            'item_received'    => 0,
            'refund_initiated' => 0,
            'completed'        => 0
        ];

        try {
            $db = Database::connect();
            $res = $db->query("SELECT status, COUNT(*) as cnt FROM returns GROUP BY status");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $st = strtolower($row['status']);
                    $cnt = (int)$row['cnt'];
                    $counts['all'] += $cnt;
                    if (isset($counts[$st])) {
                        $counts[$st] = $cnt;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Order::getReturnCountsByStatus error: " . $e->getMessage());
        }

        return $counts;
    }

    /**
     * Retrieve single return record by ID.
     */
    public static function getReturnById(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT r.*,
                       o.order_number,
                       o.grand_total as order_total,
                       o.payment_method,
                       o.payment_status,
                       COALESCE(c.name, o.shipping_name, 'Customer') as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone
                FROM returns r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN customers c ON r.customer_id = c.id
                WHERE r.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $record = $res->fetch_assoc();

            if ($record) {
                $record['encrypted_id'] = encrypt_id($record['id']);
                $record['order_encrypted_id'] = encrypt_id($record['order_id']);
                return $record;
            }
            return null;
        } catch (Exception $e) {
            error_log("Order::getReturnById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update return status, reverse tracking, or refund data.
     */
    public static function updateReturnStatus(
        int $returnId, 
        string $status, 
        string $adminNote = '', 
        ?string $reverseAwb = null, 
        ?float $refundAmount = null, 
        ?string $refundMethod = null, 
        ?string $gatewayRefundId = null,
        ?int $adminId = null
    ): bool {
        $allowed = ['requested', 'approved', 'rejected', 'pickup_scheduled', 'item_received', 'refund_initiated', 'completed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        try {
            $db = Database::connect();
            $db->begin_transaction();

            // Fetch current return record to obtain order_id
            $cur = self::getReturnById($returnId);
            if (!$cur) {
                $db->rollback();
                return false;
            }

            $orderId = (int)$cur['order_id'];

            // Update returns table
            $stmt = $db->prepare("
                UPDATE returns 
                SET status = ?,
                    admin_note = ?,
                    reverse_awb = COALESCE(?, reverse_awb),
                    refund_amount = COALESCE(?, refund_amount),
                    refund_method = COALESCE(?, refund_method),
                    gateway_refund_id = COALESCE(?, gateway_refund_id),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssi", $status, $adminNote, $reverseAwb, $refundAmount, $refundMethod, $gatewayRefundId, $returnId);
            $stmt->execute();

            // Log note to order_status_history
            $historyNote = "Return Request (ID: $returnId) updated to '" . strtoupper($status) . "'.";
            if (!empty($adminNote)) {
                $historyNote .= " Note: " . $adminNote;
            }
            if (!empty($reverseAwb)) {
                $historyNote .= " Reverse AWB: " . $reverseAwb;
            }
            if ($refundAmount !== null && $refundAmount > 0) {
                $historyNote .= " Refund Processed: ₹" . number_format((int)round((float)$refundAmount));
            }

            $hStmt = $db->prepare("INSERT INTO order_status_history (order_id, status, note, changed_by, changed_at) VALUES (?, ?, ?, ?, NOW())");
            $orderStatusTag = ($status === 'completed' || $status === 'refund_initiated') ? 'returned' : 'confirmed';
            $hStmt->bind_param("issi", $orderId, $orderStatusTag, $historyNote, $adminId);
            $hStmt->execute();

            // If refund processed or completed, update order payment status
            if ($status === 'refund_initiated' || $status === 'completed') {
                $pStmt = $db->prepare("UPDATE orders SET payment_status = 'refunded', updated_at = NOW() WHERE id = ?");
                $pStmt->bind_param("i", $orderId);
                $pStmt->execute();
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Order::updateReturnStatus error: " . $e->getMessage());
            return false;
        }
    }
}

