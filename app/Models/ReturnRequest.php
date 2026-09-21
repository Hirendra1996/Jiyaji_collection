<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class ReturnRequest {

    /**
     * Retrieve paginated return & exchange records with multi-factor filtering.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();

            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Filter by RMA status
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $whereClauses[] = "r.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }

            // Filter by Request Type (return vs exchange)
            if (!empty($filters['request_type']) && $filters['request_type'] !== 'all') {
                $whereClauses[] = "r.request_type = ?";
                $params[] = $filters['request_type'];
                $types .= "s";
            }

            // Global search query
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $whereClauses[] = "(
                    r.id LIKE ? 
                    OR o.order_number LIKE ? 
                    OR c.name LIKE ? 
                    OR c.email LIKE ? 
                    OR o.shipping_name LIKE ? 
                    OR r.reverse_awb LIKE ? 
                    OR r.reason LIKE ?
                )";
                for ($i = 0; $i < 7; $i++) {
                    $params[] = $searchTerm;
                    $types .= "s";
                }
            }

            // Date Range
            if (!empty($filters['date_from'])) {
                $whereClauses[] = "DATE(r.created_at) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            if (!empty($filters['date_to'])) {
                $whereClauses[] = "DATE(r.created_at) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting logic
            $sortSql = "ORDER BY r.created_at DESC";
            if (!empty($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'oldest':
                        $sortSql = "ORDER BY r.created_at ASC";
                        break;
                    case 'amount_high':
                        $sortSql = "ORDER BY r.refund_amount DESC";
                        break;
                    case 'amount_low':
                        $sortSql = "ORDER BY r.refund_amount ASC";
                        break;
                    case 'newest':
                    default:
                        $sortSql = "ORDER BY r.created_at DESC";
                        break;
                }
            }

            // Total count query
            $countSql = "
                SELECT COUNT(*) as total
                FROM returns r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN customers c ON r.customer_id = c.id
                WHERE $whereSql
            ";
            $countStmt = $db->prepare($countSql);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $totalCount = (int)$countStmt->get_result()->fetch_assoc()['total'];

            // Pagination calculation
            $page = max(1, $page);
            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $offset = ($page - 1) * $perPage;

            // Fetch records query
            $dataSql = "
                SELECT r.*,
                       o.order_number,
                       o.status AS order_status,
                       o.placed_at AS order_placed_at,
                       o.grand_total AS order_grand_total,
                       COALESCE(c.name, o.shipping_name, 'Guest Customer') AS customer_name,
                       c.email AS customer_email,
                       c.phone AS customer_phone,
                       (SELECT COUNT(*) FROM return_items WHERE return_id = r.id) AS item_count,
                       (SELECT SUM(quantity) FROM return_items WHERE return_id = r.id) AS total_units
                FROM returns r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN customers c ON r.customer_id = c.id
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
            error_log("ReturnRequest::getAll error: " . $e->getMessage());
            return [
                'returns'    => [],
                'pagination' => ['current_page' => 1, 'per_page' => $perPage, 'total_records' => 0, 'total_pages' => 1]
            ];
        }
    }

    /**
     * Get RMA request counts by status for tab counters.
     */
    public static function getStatusCounts(): array {
        $counts = [
            'all'              => 0,
            'requested'        => 0,
            'approved'         => 0,
            'pickup_scheduled' => 0,
            'item_received'    => 0,
            'refund_initiated' => 0,
            'completed'        => 0,
            'rejected'         => 0
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
            error_log("ReturnRequest::getStatusCounts error: " . $e->getMessage());
        }

        return $counts;
    }

    /**
     * Aggregate executive RMA KPIs for top overview banner.
     */
    public static function getRmaKPIs(): array {
        $kpis = [
            'total_requests'     => 0,
            'pending_review'     => 0,
            'in_reverse_transit' => 0,
            'total_refunded'     => 0.00,
            'active_exchanges'   => 0
        ];

        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT 
                    COUNT(*) as total_requests,
                    COALESCE(SUM(CASE WHEN status = 'requested' THEN 1 ELSE 0 END), 0) as pending_review,
                    COALESCE(SUM(CASE WHEN status IN ('pickup_scheduled', 'approved') THEN 1 ELSE 0 END), 0) as in_reverse_transit,
                    COALESCE(SUM(CASE WHEN status IN ('refund_initiated', 'completed') AND request_type = 'return' THEN refund_amount ELSE 0 END), 0) as total_refunded,
                    COALESCE(SUM(CASE WHEN request_type = 'exchange' AND status NOT IN ('completed', 'rejected') THEN 1 ELSE 0 END), 0) as active_exchanges
                FROM returns
            ");
            if ($res && $row = $res->fetch_assoc()) {
                $kpis['total_requests']     = (int)$row['total_requests'];
                $kpis['pending_review']     = (int)$row['pending_review'];
                $kpis['in_reverse_transit'] = (int)$row['in_reverse_transit'];
                $kpis['total_refunded']     = (float)$row['total_refunded'];
                $kpis['active_exchanges']   = (int)$row['active_exchanges'];
            }
        } catch (Exception $e) {
            error_log("ReturnRequest::getRmaKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Retrieve single return/exchange record by ID with joined order and items.
     */
    public static function getById(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT r.*,
                       o.order_number,
                       o.status AS order_status,
                       o.placed_at AS order_placed_at,
                       o.payment_status AS order_payment_status,
                       o.payment_method AS order_payment_method,
                       o.grand_total AS order_grand_total,
                       o.subtotal AS order_subtotal,
                       o.shipping_name,
                       o.shipping_phone,
                       o.shipping_address1,
                       o.shipping_address2,
                       CONCAT_WS(', ', o.shipping_address1, o.shipping_address2) AS shipping_address,
                       o.shipping_city,
                       o.shipping_state,
                       o.shipping_pincode,
                       COALESCE(c.name, o.shipping_name, 'Customer') AS customer_name,
                       c.email AS customer_email,
                       c.phone AS customer_phone
                FROM returns r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN customers c ON r.customer_id = c.id
                WHERE r.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $return = $res->fetch_assoc();

            if ($return) {
                $return['encrypted_id'] = encrypt_id($return['id']);
                $return['order_encrypted_id'] = encrypt_id($return['order_id']);
                $return['items'] = self::getItems($id);
                return $return;
            }
            return null;
        } catch (Exception $e) {
            error_log("ReturnRequest::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve line items associated with a return request.
     */
    public static function getItems(int $returnId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT ri.*,
                       oi.product_name,
                       oi.variant_info,
                       oi.sku,
                       oi.unit_price,
                       oi.line_total,
                       oi.quantity AS order_quantity
                FROM return_items ri
                LEFT JOIN order_items oi ON ri.order_item_id = oi.id
                WHERE ri.return_id = ?
                ORDER BY ri.id ASC
            ");
            $stmt->bind_param("i", $returnId);
            $stmt->execute();
            $res = $stmt->get_result();

            $items = [];
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }
            return $items;
        } catch (Exception $e) {
            error_log("ReturnRequest::getItems error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Advance RMA fulfillment status and record internal administrative remark.
     */
    public static function updateStatus(int $id, string $status, string $note = '', ?int $adminId = null): bool {
        $allowed = ['requested', 'approved', 'rejected', 'pickup_scheduled', 'item_received', 'refund_initiated', 'completed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        try {
            $db = Database::connect();

            // Prepare appended note if provided
            $appendedNote = '';
            if (!empty(trim($note))) {
                $timestamp = date('d M Y, h:i A');
                $appendedNote = "[$timestamp - " . ucfirst(str_replace('_', ' ', $status)) . "]: " . trim($note) . "\n";
            }

            $stmt = $db->prepare("
                UPDATE returns 
                SET status = ?, 
                    admin_note = CONCAT(COALESCE(admin_note, ''), ?),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $status, $appendedNote, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ReturnRequest::updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign or update reverse courier partner and AWB waybill tracking.
     */
    public static function updateTracking(int $id, string $reverseAwb, ?string $note = null): bool {
        try {
            $db = Database::connect();
            $reverseAwb = trim($reverseAwb);

            $appendedNote = '';
            if (!empty(trim($note ?? ''))) {
                $timestamp = date('d M Y, h:i A');
                $appendedNote = "[$timestamp - Logistics AWB]: " . trim($note) . "\n";
            }

            $stmt = $db->prepare("
                UPDATE returns 
                SET reverse_awb = ?,
                    admin_note = CONCAT(COALESCE(admin_note, ''), ?),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $reverseAwb, $appendedNote, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ReturnRequest::updateTracking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record refund transaction and transition status.
     */
    public static function processRefund(int $id, float $amount, string $method, ?string $gatewayId = null, string $note = ''): bool {
        try {
            $db = Database::connect();
            $gatewayId = trim($gatewayId ?? '');

            $timestamp = date('d M Y, h:i A');
            $appendedNote = "[$timestamp - Refund Processed]: ₹" . number_format($amount, 2) . " via " . ucfirst(str_replace('_', ' ', $method)) . ($gatewayId ? " (Ref: $gatewayId)" : "");
            if (!empty(trim($note))) {
                $appendedNote .= " - " . trim($note);
            }
            $appendedNote .= "\n";

            $stmt = $db->prepare("
                UPDATE returns 
                SET refund_amount = ?,
                    refund_method = ?,
                    gateway_refund_id = ?,
                    status = 'refund_initiated',
                    admin_note = CONCAT(COALESCE(admin_note, ''), ?),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("dsssi", $amount, $method, $gatewayId, $appendedNote, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ReturnRequest::processRefund error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new return / exchange request from the staff operations portal.
     */
    public static function createRma(array $data): ?int {
        try {
            $db = Database::connect();
            $db->begin_transaction();

            $orderId      = (int)($data['order_id'] ?? 0);
            $customerId   = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
            $requestType  = in_array($data['request_type'] ?? '', ['return', 'exchange'], true) ? $data['request_type'] : 'return';
            $reason       = trim($data['reason'] ?? 'Customer request');
            $description  = trim($data['description'] ?? '');
            $exchangeNotes = trim($data['exchange_notes'] ?? '');
            $adminNote    = trim($data['admin_note'] ?? '');
            $status       = $data['status'] ?? 'requested';
            $refundAmount = (float)($data['refund_amount'] ?? 0);

            if (!empty($adminNote)) {
                $adminNote = "[" . date('d M Y, h:i A') . " - Registered]: " . $adminNote . "\n";
            }

            $stmt = $db->prepare("
                INSERT INTO returns (order_id, customer_id, request_type, reason, description, exchange_notes, status, refund_amount, admin_note, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("iisssssds", $orderId, $customerId, $requestType, $reason, $description, $exchangeNotes, $status, $refundAmount, $adminNote);
            $stmt->execute();
            $returnId = (int)$db->insert_id;

            // Insert line items
            if (!empty($data['items']) && is_array($data['items'])) {
                $itemStmt = $db->prepare("INSERT INTO return_items (return_id, order_item_id, quantity) VALUES (?, ?, ?)");
                foreach ($data['items'] as $item) {
                    $orderItemId = (int)($item['order_item_id'] ?? 0);
                    $qty         = max(1, (int)($item['quantity'] ?? 1));
                    if ($orderItemId > 0) {
                        $itemStmt->bind_param("iii", $returnId, $orderItemId, $qty);
                        $itemStmt->execute();
                    }
                }
            }

            $db->commit();
            return $returnId;
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            error_log("ReturnRequest::createRma error: " . $e->getMessage());
            return null;
        }
    }
}
