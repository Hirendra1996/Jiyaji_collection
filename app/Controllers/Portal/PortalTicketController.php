<?php

namespace App\Controllers\Portal;

use App\Models\Ticket;
use App\Middleware\PortalAuthMiddleware;
use Exception;

/**
 * PortalTicketController
 *
 * Dedicated Support Desk and Client Concierge system for the Staff & Operations Portal.
 * Guarded by PortalAuthMiddleware and granular RBAC permissions:
 *  - tickets:view    → Access support queue, filter tickets, view conversation threads, export CSV
 *  - tickets:reply   → Send official concierge responses to customer inquiries
 *  - tickets:resolve → Update ticket lifecycle status, adjust urgency, assign agents, and archive
 */
class PortalTicketController {

    /**
     * Display the Support Desk ledger with live KPIs, segment tabs, filter bar, and tickets list.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Support Tickets.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'       => $_GET['status'] ?? 'all',
            'priority'     => $_GET['priority'] ?? 'all',
            'assigned_to'  => $_GET['assigned_to'] ?? 'all',
            'order_linked' => $_GET['order_linked'] ?? 'all',
            'search'       => trim($_GET['search'] ?? ''),
            'sort'         => $_GET['sort'] ?? 'last_updated'
        ];

        // Customer filter from encrypted query parameter (e.g., when navigated from customer profile)
        if (!empty($_GET['customer'])) {
            $decCustId = decrypt_id($_GET['customer']);
            if ($decCustId) {
                $filters['customer_id'] = $decCustId;
            }
        }

        $page       = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $ticketData = Ticket::getAll($filters, $page, 15);
        $tickets    = $ticketData['tickets'] ?? [];
        $pagination = $ticketData['pagination'] ?? [
            'total_items'  => 0,
            'per_page'     => 15,
            'current_page' => 1,
            'total_pages'  => 1,
            'offset'       => 0,
            'has_prev'     => false,
            'has_next'     => false
        ];
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis       = Ticket::getKPIs();
        $admins     = Ticket::getAdminsList();
        $customers  = Ticket::getCustomersList();

        $canReply   = staff_can('tickets', 'reply');
        $canResolve = staff_can('tickets', 'resolve') || staff_can('tickets', 'reply');

        $title = 'Support Desk & Client Concierge | Jiyaji LX Operations Portal';
        include __DIR__ . '/../../Views/portal/tickets/index.php';
    }

    /**
     * Display a single support ticket conversation thread with client dossier and linked order context.
     */
    public function show(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Support Tickets.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_flash('error', 'Invalid ticket identifier token.');
            redirect('portal/tickets');
        }

        $ticket = Ticket::find($id);
        if (!$ticket) {
            set_flash('error', 'Support ticket not found or has been removed.');
            redirect('portal/tickets');
        }

        $messages   = Ticket::getMessages($id);
        $admins     = Ticket::getAdminsList();
        $canReply   = staff_can('tickets', 'reply');
        $canResolve = staff_can('tickets', 'resolve') || staff_can('tickets', 'reply');

        $title = "{$ticket['ticket_code']}: {$ticket['subject']} | Jiyaji LX Support Desk";
        include __DIR__ . '/../../Views/portal/tickets/show.php';
    }

    /**
     * Show standalone ticket creation form.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/dashboard');
        }

        $customers = Ticket::getCustomersList();
        $admins    = Ticket::getAdminsList();

        $preselectedCustomer = null;
        $customerOrders = [];
        if (!empty($_GET['customer'])) {
            $decCustId = decrypt_id($_GET['customer']);
            if ($decCustId) {
                $preselectedCustomer = $decCustId;
                $customerOrders = Ticket::getOrdersForCustomer($decCustId);
            }
        }

        $title = 'Open New Support Ticket | Jiyaji LX Operations Portal';
        include __DIR__ . '/../../Views/portal/tickets/create.php';
    }

    /**
     * Store newly created support ticket and initial inquiry message.
     */
    public function store(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired or security token invalid. Please try again.');
            redirect('portal/tickets');
        }

        $encryptedCustomerId = trim($_POST['customer_id'] ?? '');
        $customerId = !empty($encryptedCustomerId) ? decrypt_id($encryptedCustomerId) : null;

        $encryptedOrderId = trim($_POST['order_id'] ?? '');
        $orderId = !empty($encryptedOrderId) ? decrypt_id($encryptedOrderId) : null;

        $subject = trim($_POST['subject'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');
        $initialMessage = trim($_POST['message'] ?? '');
        $assignedTo = !empty($_POST['assigned_to']) && is_numeric($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        if (empty($subject)) {
            set_toast('error', 'Validation Error', 'Ticket subject cannot be blank.');
            redirect('portal/tickets/create');
        }

        if (empty($initialMessage)) {
            set_toast('error', 'Validation Error', 'Initial message inquiry cannot be empty.');
            redirect('portal/tickets/create');
        }

        $staff = auth_staff();
        $staffId = $staff['id'] ?? 1;

        // Sender defaults to customer context or staff logged in
        $senderType = 'customer';
        $senderId   = $customerId ?: $staffId;

        $ticketId = Ticket::create([
            'customer_id' => $customerId,
            'order_id'    => $orderId,
            'subject'     => $subject,
            'priority'    => in_array($priority, ['low', 'medium', 'high', 'critical'], true) ? $priority : 'medium',
            'status'      => 'open',
            'assigned_to' => $assignedTo,
            'message'     => $initialMessage,
            'sender_type' => $senderType,
            'sender_id'   => $senderId
        ]);

        if ($ticketId > 0) {
            set_toast('success', 'Ticket Created', "Support ticket #TKT-" . str_pad((string)$ticketId, 5, '0', STR_PAD_LEFT) . " opened successfully.");
            redirect('portal/tickets/' . encrypt_id($ticketId));
        } else {
            set_toast('error', 'Creation Failed', 'Could not open support ticket in database.');
            redirect('portal/tickets/create');
        }
    }

    /**
     * Post a concierge staff reply to a support ticket thread.
     */
    public function reply(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'reply')) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access Denied: You do not have permission to reply to tickets.']);
                exit;
            }
            set_flash('error', 'Access Denied: You do not have permission to reply.');
            redirect('portal/tickets');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Security token expired. Please reload and try again.']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session expired. Please try again.');
            redirect('portal/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid ticket identifier token.']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('portal/tickets');
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Reply message cannot be empty.']);
                exit;
            }
            set_toast('error', 'Validation Error', 'Reply message cannot be empty.');
            redirect('portal/tickets/' . $encryptedId);
        }

        $statusAction = trim($_POST['status_action'] ?? '');
        $allowedStatuses = ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'];
        $updateStatus = in_array($statusAction, $allowedStatuses, true) ? $statusAction : null;

        $staff = auth_staff();
        $staffId = $staff['id'] ?? 1;

        $success = Ticket::addMessage($id, 'admin', $staffId, $message, $updateStatus);

        if ($success) {
            // If ticket was unassigned, assign to this responding staff member automatically
            $ticket = Ticket::find($id);
            if ($ticket && empty($ticket['assigned_to'])) {
                Ticket::assignAgent($id, $staffId);
            }

            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Your reply has been posted.',
                    'sender'  => $staff['name'] ?? 'Staff Member',
                    'time'    => date('M d, Y h:i A')
                ]);
                exit;
            }

            set_toast('success', 'Reply Sent', 'Your response has been appended to the ticket conversation.');
        } else {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to record response in database.']);
                exit;
            }
            set_toast('error', 'Error', 'Failed to send reply.');
        }

        redirect('portal/tickets/' . $encryptedId);
    }

    /**
     * Update ticket lifecycle status.
     */
    public function updateStatus(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'resolve') && !staff_can('tickets', 'reply')) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access Denied: Insufficient permissions.']);
                exit;
            }
            set_flash('error', 'Access Denied: You do not have permission to update ticket status.');
            redirect('portal/tickets');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Security token expired.']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('portal/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid ticket ID.']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('portal/tickets');
        }

        $status = trim($_POST['status'] ?? '');
        $allowed = ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid ticket status.']);
                exit;
            }
            set_toast('error', 'Validation Error', 'Invalid ticket status.');
            redirect('portal/tickets/' . $encryptedId);
        }

        $success = Ticket::updateStatus($id, $status);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'status'  => $status,
                'label'   => ucwords(str_replace('_', ' ', $status)),
                'message' => $success ? 'Ticket status updated to ' . ucwords(str_replace('_', ' ', $status)) : 'Failed to update status.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Status Updated', 'Ticket status changed to ' . strtoupper(str_replace('_', ' ', $status)) . '.');
        } else {
            set_toast('error', 'Update Failed', 'Could not update ticket status.');
        }

        $returnUrl = $_POST['return_url'] ?? ('portal/tickets/' . $encryptedId);
        redirect($returnUrl);
    }

    /**
     * Update ticket urgency priority.
     */
    public function updatePriority(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'resolve') && !staff_can('tickets', 'reply')) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access Denied: Insufficient permissions.']);
                exit;
            }
            set_flash('error', 'Access Denied.');
            redirect('portal/tickets');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Security token expired.']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('portal/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid ticket ID.']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('portal/tickets');
        }

        $priority = trim($_POST['priority'] ?? '');
        $allowed = ['low', 'medium', 'high', 'critical'];
        if (!in_array($priority, $allowed, true)) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid priority level.']);
                exit;
            }
            set_toast('error', 'Validation Error', 'Invalid priority value.');
            redirect('portal/tickets/' . $encryptedId);
        }

        $success = Ticket::updatePriority($id, $priority);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => $success,
                'priority' => $priority,
                'message'  => $success ? 'Priority level changed to ' . ucfirst($priority) : 'Failed to update priority.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Priority Updated', 'Urgency priority set to ' . strtoupper($priority) . '.');
        } else {
            set_toast('error', 'Update Failed', 'Could not update ticket priority.');
        }

        $returnUrl = $_POST['return_url'] ?? ('portal/tickets/' . $encryptedId);
        redirect($returnUrl);
    }

    /**
     * Assign ticket to a concierge staff member or mark unassigned.
     */
    public function assign(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'resolve') && !staff_can('tickets', 'reply')) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access Denied.']);
                exit;
            }
            set_flash('error', 'Access Denied.');
            redirect('portal/tickets');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Security token expired.']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('portal/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid ticket ID.']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('portal/tickets');
        }

        $adminId = !empty($_POST['admin_id']) && is_numeric($_POST['admin_id']) ? (int)$_POST['admin_id'] : null;
        $success = Ticket::assignAgent($id, $adminId);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $success ? ($adminId ? 'Agent assigned successfully.' : 'Ticket marked as unassigned.') : 'Failed to assign agent.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Agent Assigned', $adminId ? 'Support agent assigned to ticket.' : 'Ticket marked as unassigned.');
        } else {
            set_toast('error', 'Assignment Failed', 'Could not update agent assignment.');
        }

        $returnUrl = $_POST['return_url'] ?? ('portal/tickets/' . $encryptedId);
        redirect($returnUrl);
    }

    /**
     * Permanently delete a support ticket and its associated conversation messages.
     */
    public function destroy(string $encryptedId): void {
        PortalAuthMiddleware::check();

        $staff = auth_staff();
        $isSuperOrSupportLead = ($staff['role_name'] ?? '') === 'super_admin' || staff_can('tickets', 'resolve');
        if (!$isSuperOrSupportLead) {
            set_flash('error', 'Access Denied: Only senior support officers can delete support tickets.');
            redirect('portal/tickets');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('portal/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('portal/tickets');
        }

        $success = Ticket::delete($id);

        if ($success) {
            set_toast('success', 'Ticket Deleted', 'The ticket and its conversation history have been deleted.');
        } else {
            set_toast('error', 'Delete Failed', 'Could not remove ticket from database.');
        }

        redirect('portal/tickets');
    }

    /**
     * JSON API: Fetch customer's orders for dynamic dropdown selection in create ticket form.
     */
    public function customerOrders(string $encryptedCustomerId): void {
        PortalAuthMiddleware::check();

        $custId = decrypt_id($encryptedCustomerId);
        if (!$custId) {
            $this->jsonResponse(['success' => false, 'orders' => [], 'message' => 'Invalid customer ID']);
        }

        $orders = Ticket::getOrdersForCustomer($custId);
        $this->jsonResponse(['success' => true, 'orders' => $orders]);
    }

    /**
     * Export support tickets ledger to CSV matching active filters.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('tickets', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/tickets');
        }

        $filters = [
            'status'       => $_GET['status'] ?? 'all',
            'priority'     => $_GET['priority'] ?? 'all',
            'assigned_to'  => $_GET['assigned_to'] ?? 'all',
            'order_linked' => $_GET['order_linked'] ?? 'all',
            'search'       => trim($_GET['search'] ?? ''),
            'sort'         => $_GET['sort'] ?? 'last_updated'
        ];

        if (!empty($_GET['customer'])) {
            $decCustId = decrypt_id($_GET['customer']);
            if ($decCustId) {
                $filters['customer_id'] = $decCustId;
            }
        }

        $ticketData = Ticket::getAll($filters, 1, 5000);
        $tickets    = $ticketData['tickets'] ?? [];

        $filename = 'jiyaji_support_tickets_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Ticket Code', 'Client Name', 'Client Email', 'Client Phone',
            'Subject', 'Priority', 'Status', 'Assigned Agent',
            'Linked Order #', 'Order Grand Total', 'Messages Count',
            'Created At', 'Last Updated'
        ]);

        foreach ($tickets as $t) {
            fputcsv($out, [
                $t['ticket_code'],
                $t['customer_name'] ?? 'Guest Visitor',
                $t['customer_email'] ?? '',
                $t['customer_phone'] ?? '',
                $t['subject'],
                strtoupper($t['priority'] ?? 'medium'),
                strtoupper($t['status'] ?? 'open'),
                $t['assigned_agent_name'] ?? 'Unassigned',
                $t['linked_order_number'] ?? 'None',
                !empty($t['linked_order_total']) ? '₹' . number_format((float)$t['linked_order_total']) : '',
                (int)($t['message_count'] ?? 0),
                !empty($t['created_at']) ? date('Y-m-d H:i:s', strtotime($t['created_at'])) : '',
                !empty($t['updated_at']) ? date('Y-m-d H:i:s', strtotime($t['updated_at'])) : '',
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Check if current HTTP request is an AJAX call.
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }

    /**
     * Helper to safely return JSON response.
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode($data);
        exit;
    }
}
