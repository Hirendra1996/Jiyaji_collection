<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Ticket {
    /**
     * Retrieve paginated and filtered list of support tickets.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $whereClauses[] = "t.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }

            // Priority filter
            if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
                $whereClauses[] = "t.priority = ?";
                $params[] = $filters['priority'];
                $types .= "s";
            }

            // Assigned Agent filter
            if (!empty($filters['assigned_to'])) {
                if ($filters['assigned_to'] === 'unassigned') {
                    $whereClauses[] = "t.assigned_to IS NULL";
                } elseif (is_numeric($filters['assigned_to'])) {
                    $whereClauses[] = "t.assigned_to = ?";
                    $params[] = (int)$filters['assigned_to'];
                    $types .= "i";
                }
            }

            // Order Association filter
            if (!empty($filters['order_linked'])) {
                if ($filters['order_linked'] === 'order_only') {
                    $whereClauses[] = "t.order_id IS NOT NULL";
                } elseif ($filters['order_linked'] === 'general_only') {
                    $whereClauses[] = "t.order_id IS NULL";
                }
            }

            // Customer specific filter (for customer profile dossier integration)
            if (!empty($filters['customer_id']) && is_numeric($filters['customer_id'])) {
                $whereClauses[] = "t.customer_id = ?";
                $params[] = (int)$filters['customer_id'];
                $types .= "i";
            }

            // Search query (Ticket ID, Subject, Customer Name, Email, Phone, Order Number)
            if (!empty($filters['search'])) {
                $search = trim($filters['search']);
                $cleanNumeric = preg_replace('/[^0-9]/', '', $search);

                if (!empty($cleanNumeric) && (str_starts_with(strtoupper($search), 'TKT-') || is_numeric($search))) {
                    $whereClauses[] = "(t.id = ? OR t.subject LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR o.order_number LIKE ?)";
                    $params[] = (int)$cleanNumeric;
                    $types .= "i";
                    $wild = "%" . $search . "%";
                    for ($i = 0; $i < 4; $i++) {
                        $params[] = $wild;
                        $types .= "s";
                    }
                } else {
                    $searchWildcard = "%" . $search . "%";
                    $whereClauses[] = "(t.subject LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR o.order_number LIKE ?)";
                    for ($i = 0; $i < 5; $i++) {
                        $params[] = $searchWildcard;
                        $types .= "s";
                    }
                }
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting
            $sortSql = "ORDER BY t.updated_at DESC, t.id DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'newest'        => "ORDER BY t.created_at DESC, t.id DESC",
                    'oldest'        => "ORDER BY t.created_at ASC, t.id ASC",
                    'priority_desc' => "ORDER BY FIELD(t.priority, 'critical', 'high', 'medium', 'low'), t.updated_at DESC",
                    'last_updated'  => "ORDER BY t.updated_at DESC, t.id DESC",
                    default         => "ORDER BY t.updated_at DESC, t.id DESC"
                };
            }

            // Count total matching
            $countSql = "
                SELECT COUNT(*) as total
                FROM support_tickets t
                LEFT JOIN customers c ON t.customer_id = c.id
                LEFT JOIN orders o ON t.order_id = o.id
                LEFT JOIN admins adm ON t.assigned_to = adm.id
                WHERE $whereSql
            ";

            $stmtCount = $db->prepare($countSql);
            if (!empty($params)) {
                $stmtCount->bind_param($types, ...$params);
            }
            $stmtCount->execute();
            $totalTickets = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);

            // Pagination calculation
            $totalPages = max(1, (int)ceil($totalTickets / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Main Query
            $selectSql = "
                SELECT 
                    t.*,
                    c.name AS customer_name,
                    c.email AS customer_email,
                    c.phone AS customer_phone,
                    c.profile_photo_url AS customer_photo,
                    o.order_number AS linked_order_number,
                    o.grand_total AS linked_order_total,
                    o.status AS linked_order_status,
                    adm.name AS assigned_agent_name,
                    adm.email AS assigned_agent_email,
                    (
                        SELECT COUNT(*) 
                        FROM support_ticket_messages m 
                        WHERE m.ticket_id = t.id
                    ) AS message_count,
                    (
                        SELECT m.message 
                        FROM support_ticket_messages m 
                        WHERE m.ticket_id = t.id 
                        ORDER BY m.id DESC 
                        LIMIT 1
                    ) AS latest_message_snippet,
                    (
                        SELECT m.created_at 
                        FROM support_ticket_messages m 
                        WHERE m.ticket_id = t.id 
                        ORDER BY m.id DESC 
                        LIMIT 1
                    ) AS latest_message_time
                FROM support_tickets t
                LEFT JOIN customers c ON t.customer_id = c.id
                LEFT JOIN orders o ON t.order_id = o.id
                LEFT JOIN admins adm ON t.assigned_to = adm.id
                WHERE $whereSql
                $sortSql
                LIMIT ? OFFSET ?
            ";

            $stmtSelect = $db->prepare($selectSql);
            $queryParams = $params;
            $queryParams[] = $perPage;
            $queryParams[] = $offset;
            $queryTypes = $types . "ii";
            $stmtSelect->bind_param($queryTypes, ...$queryParams);
            $stmtSelect->execute();
            $result = $stmtSelect->get_result();

            $tickets = [];
            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $row['customer_encrypted_id'] = !empty($row['customer_id']) ? encrypt_id($row['customer_id']) : null;
                $row['order_encrypted_id'] = !empty($row['order_id']) ? encrypt_id($row['order_id']) : null;
                $row['ticket_code'] = 'TKT-' . str_pad((string)$row['id'], 5, '0', STR_PAD_LEFT);
                $tickets[] = $row;
            }

            return [
                'tickets'    => $tickets,
                'pagination' => [
                    'total_items'  => $totalTickets,
                    'total_pages'  => $totalPages,
                    'current_page' => $page,
                    'per_page'     => $perPage,
                    'offset'       => $offset,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Ticket::getAll error: " . $e->getMessage());
            return [
                'tickets'    => [],
                'pagination' => [
                    'total_items'  => 0,
                    'total_pages'  => 1,
                    'current_page' => 1,
                    'per_page'     => $perPage,
                    'offset'       => 0,
                    'has_prev'     => false,
                    'has_next'     => false
                ]
            ];
        }
    }

    /**
     * Retrieve high-level summary KPIs for the support tickets system.
     */
    public static function getKPIs(): array {
        $kpis = [
            'total_tickets'   => 0,
            'open_tickets'    => 0,
            'in_progress'     => 0,
            'critical_cases'  => 0,
            'resolved_cases'  => 0,
            'resolution_rate' => 0.0
        ];

        try {
            $db = Database::connect();
            $sql = "
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status IN ('open', 'acknowledged') THEN 1 ELSE 0 END) AS open_cnt,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_prog_cnt,
                    SUM(CASE WHEN priority = 'critical' AND status != 'closed' THEN 1 ELSE 0 END) AS critical_cnt,
                    SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) AS resolved_cnt
                FROM support_tickets
            ";

            $res = $db->query($sql);
            if ($res && $row = $res->fetch_assoc()) {
                $total = (int)($row['total'] ?? 0);
                $resolved = (int)($row['resolved_cnt'] ?? 0);

                $kpis['total_tickets']   = $total;
                $kpis['open_tickets']    = (int)($row['open_cnt'] ?? 0);
                $kpis['in_progress']     = (int)($row['in_prog_cnt'] ?? 0);
                $kpis['critical_cases']  = (int)($row['critical_cnt'] ?? 0);
                $kpis['resolved_cases']  = $resolved;
                $kpis['resolution_rate'] = $total > 0 ? round(($resolved / $total) * 100, 1) : 100.0;
            }
        } catch (Exception $e) {
            error_log("Ticket::getKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Find single support ticket by ID with comprehensive relationships.
     */
    public static function find(int $id): ?array {
        try {
            $db = Database::connect();

            $stmt = $db->prepare("
                SELECT 
                    t.*,
                    c.name AS customer_name,
                    c.email AS customer_email,
                    c.phone AS customer_phone,
                    c.profile_photo_url AS customer_photo,
                    (
                        SELECT COALESCE(ROUND(SUM(o2.grand_total)), 0)
                        FROM orders o2 
                        WHERE o2.customer_id = c.id AND o2.status != 'cancelled'
                    ) AS customer_lifetime_spend,
                    (
                        SELECT COUNT(*)
                        FROM orders o3 
                        WHERE o3.customer_id = c.id
                    ) AS customer_orders_count,
                    o.order_number AS linked_order_number,
                    o.grand_total AS linked_order_total,
                    o.status AS linked_order_status,
                    o.placed_at AS linked_order_date,
                    adm.name AS assigned_agent_name,
                    adm.email AS assigned_agent_email
                FROM support_tickets t
                LEFT JOIN customers c ON t.customer_id = c.id
                LEFT JOIN orders o ON t.order_id = o.id
                LEFT JOIN admins adm ON t.assigned_to = adm.id
                WHERE t.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $ticket = $stmt->get_result()->fetch_assoc();

            if (!$ticket) {
                return null;
            }

            $ticket['encrypted_id'] = encrypt_id($ticket['id']);
            $ticket['customer_encrypted_id'] = !empty($ticket['customer_id']) ? encrypt_id($ticket['customer_id']) : null;
            $ticket['order_encrypted_id'] = !empty($ticket['order_id']) ? encrypt_id($ticket['order_id']) : null;
            $ticket['ticket_code'] = 'TKT-' . str_pad((string)$ticket['id'], 5, '0', STR_PAD_LEFT);

            return $ticket;
        } catch (Exception $e) {
            error_log("Ticket::find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve all conversation thread messages for a ticket.
     */
    public static function getMessages(int $ticketId): array {
        try {
            $db = Database::connect();

            $stmt = $db->prepare("
                SELECT 
                    m.*,
                    CASE 
                        WHEN m.sender_type = 'customer' THEN c.name
                        WHEN m.sender_type = 'admin' THEN adm.name
                        ELSE 'System'
                    END AS sender_name,
                    CASE 
                        WHEN m.sender_type = 'customer' THEN c.email
                        WHEN m.sender_type = 'admin' THEN adm.email
                        ELSE ''
                    END AS sender_email,
                    CASE 
                        WHEN m.sender_type = 'customer' THEN c.profile_photo_url
                        ELSE NULL
                    END AS sender_photo
                FROM support_ticket_messages m
                JOIN support_tickets t ON m.ticket_id = t.id
                LEFT JOIN customers c ON m.sender_type = 'customer' AND m.sender_id = c.id
                LEFT JOIN admins adm ON m.sender_type = 'admin' AND m.sender_id = adm.id
                WHERE m.ticket_id = ?
                ORDER BY m.created_at ASC, m.id ASC
            ");
            $stmt->bind_param("i", $ticketId);
            $stmt->execute();
            $res = $stmt->get_result();

            $messages = [];
            while ($row = $res->fetch_assoc()) {
                $messages[] = $row;
            }

            return $messages;
        } catch (Exception $e) {
            error_log("Ticket::getMessages error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new support ticket and record its initial inquiry message.
     */
    public static function create(array $data): int {
        try {
            $db = Database::connect();

            $customerId = !empty($data['customer_id']) ? (int)$data['customer_id'] : null;
            $orderId = !empty($data['order_id']) ? (int)$data['order_id'] : null;
            $subject = trim($data['subject'] ?? 'General Inquiry');
            $priority = in_array($data['priority'] ?? '', ['low', 'medium', 'high', 'critical'], true) ? $data['priority'] : 'medium';
            $status = in_array($data['status'] ?? '', ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'], true) ? $data['status'] : 'open';
            $assignedTo = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null;
            $initialMessage = trim($data['message'] ?? '');

            $stmt = $db->prepare("
                INSERT INTO support_tickets (customer_id, order_id, subject, priority, status, assigned_to, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("iisssi", $customerId, $orderId, $subject, $priority, $status, $assignedTo);
            $stmt->execute();

            $newTicketId = (int)$db->insert_id;

            if ($newTicketId > 0 && !empty($initialMessage)) {
                $senderType = $data['sender_type'] ?? 'customer';
                $senderId = !empty($data['sender_id']) ? (int)$data['sender_id'] : ($senderType === 'customer' ? $customerId : $assignedTo);

                self::addMessage($newTicketId, $senderType, (int)$senderId, $initialMessage);
            }

            return $newTicketId;
        } catch (Exception $e) {
            error_log("Ticket::create error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Append a message to a ticket conversation thread.
     */
    public static function addMessage(int $ticketId, string $senderType, int $senderId, string $message, ?string $updateStatus = null): bool {
        try {
            $db = Database::connect();

            $message = trim($message);
            if ($message === '') {
                return false;
            }

            $senderType = in_array($senderType, ['customer', 'admin'], true) ? $senderType : 'admin';

            $stmt = $db->prepare("
                INSERT INTO support_ticket_messages (ticket_id, sender_type, sender_id, message, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("isis", $ticketId, $senderType, $senderId, $message);
            $success = $stmt->execute();

            if ($success) {
                // Touch updated_at on ticket
                if (!empty($updateStatus) && in_array($updateStatus, ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'], true)) {
                    $upStmt = $db->prepare("UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?");
                    $upStmt->bind_param("si", $updateStatus, $ticketId);
                    $upStmt->execute();
                } else {
                    $upStmt = $db->prepare("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?");
                    $upStmt->bind_param("i", $ticketId);
                    $upStmt->execute();
                }
            }

            return $success;
        } catch (Exception $e) {
            error_log("Ticket::addMessage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update ticket lifecycle status.
     */
    public static function updateStatus(int $ticketId, string $status): bool {
        $allowed = ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $ticketId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Ticket::updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update ticket urgency priority.
     */
    public static function updatePriority(int $ticketId, string $priority): bool {
        $allowed = ['low', 'medium', 'high', 'critical'];
        if (!in_array($priority, $allowed, true)) {
            return false;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE support_tickets SET priority = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $priority, $ticketId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Ticket::updatePriority error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign ticket to a concierge staff admin member (or null to unassign).
     */
    public static function assignAgent(int $ticketId, ?int $adminId): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE support_tickets SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $adminId, $ticketId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Ticket::assignAgent error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Permanently delete a support ticket and its associated messages.
     */
    public static function delete(int $ticketId): bool {
        try {
            $db = Database::connect();

            // First delete associated messages
            $msgStmt = $db->prepare("DELETE FROM support_ticket_messages WHERE ticket_id = ?");
            $msgStmt->bind_param("i", $ticketId);
            $msgStmt->execute();

            // Then delete the ticket
            $stmt = $db->prepare("DELETE FROM support_tickets WHERE id = ?");
            $stmt->bind_param("i", $ticketId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Ticket::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve all active customers for dropdown selection.
     */
    public static function getCustomersList(): array {
        try {
            $db = Database::connect();
            $res = $db->query("SELECT id, name, email, phone FROM customers WHERE is_active = 1 ORDER BY name ASC");
            $list = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $list[] = $row;
            }
            return $list;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Retrieve orders for a specific customer.
     */
    public static function getOrdersForCustomer(int $customerId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT id, order_number, grand_total, status, placed_at FROM orders WHERE customer_id = ? ORDER BY placed_at DESC");
            $stmt->bind_param("i", $customerId);
            $stmt->execute();
            $res = $stmt->get_result();
            $list = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $list[] = $row;
            }
            return $list;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Retrieve all active staff admins for assignment dropdown.
     */
    public static function getAdminsList(): array {
        try {
            $db = Database::connect();
            $res = $db->query("SELECT id, name, email FROM admins WHERE is_active = 1 ORDER BY name ASC");
            $list = [];
            while ($row = $res->fetch_assoc()) {
                $list[] = $row;
            }
            return $list;
        } catch (Exception $e) {
            return [];
        }
    }
}
