<?php

namespace App\Controllers\Admin;

use App\Models\Ticket;
use App\Middleware\AuthMiddleware;
use Exception;

class TicketController {
    /**
     * Display the support tickets ledger with KPIs, filters, and quick modal.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'       => $_GET['status'] ?? 'all',
            'priority'     => $_GET['priority'] ?? 'all',
            'assigned_to'  => $_GET['assigned_to'] ?? 'all',
            'order_linked' => $_GET['order_linked'] ?? 'all',
            'search'       => trim($_GET['search'] ?? ''),
            'sort'         => $_GET['sort'] ?? 'last_updated'
        ];

        // Customer filter from encrypted query parameter (if accessed from customer profile)
        if (!empty($_GET['customer'])) {
            $decCustId = decrypt_id($_GET['customer']);
            if ($decCustId) {
                $filters['customer_id'] = $decCustId;
            }
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $ticketData = Ticket::getAll($filters, $page, 15);
        $tickets    = $ticketData['tickets'];
        $pagination = $ticketData['pagination'];

        $kpis       = Ticket::getKPIs();
        $admins     = Ticket::getAdminsList();
        $customers  = Ticket::getCustomersList();

        $title = 'Support Tickets & Priority Resolution | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/tickets/index.php';
    }

    /**
     * Display a single support ticket conversation thread, customer dossier & order context.
     */
    public function show(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier token.');
            redirect('admin/tickets');
        }

        $ticket = Ticket::find($id);
        if (!$ticket) {
            set_toast('error', 'Not Found', 'Support ticket not found or has been removed.');
            redirect('admin/tickets');
        }

        $messages = Ticket::getMessages($id);
        $admins   = Ticket::getAdminsList();

        $title = "{$ticket['ticket_code']}: {$ticket['subject']} | Jiyaji LX Support";
        include __DIR__ . '/../../Views/admin/tickets/show.php';
    }

    /**
     * Show standalone ticket creation form.
     */
    public function create(): void {
        AuthMiddleware::check();

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

        $title = 'Open New Support Ticket | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/tickets/create.php';
    }

    /**
     * Store newly created support ticket and initial message.
     */
    public function store(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired. Please try again.');
            redirect('admin/tickets');
        }

        $encryptedCustomerId = trim($_POST['customer_id'] ?? '');
        $customerId = decrypt_id($encryptedCustomerId);

        $encryptedOrderId = trim($_POST['order_id'] ?? '');
        $orderId = !empty($encryptedOrderId) ? decrypt_id($encryptedOrderId) : null;

        $subject = trim($_POST['subject'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');
        $initialMessage = trim($_POST['message'] ?? '');
        $assignedTo = !empty($_POST['assigned_to']) && is_numeric($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        if (empty($subject)) {
            set_toast('error', 'Validation Error', 'Ticket subject cannot be blank.');
            redirect('admin/tickets/create');
        }

        if (empty($initialMessage)) {
            set_toast('error', 'Validation Error', 'Initial message inquiry cannot be empty.');
            redirect('admin/tickets/create');
        }

        $admin = auth_admin();
        $adminId = $admin['id'] ?? 1;

        // If ticket is logged by staff on customer's behalf
        $senderType = 'customer';
        $senderId = $customerId ?: $adminId;

        $ticketId = Ticket::create([
            'customer_id' => $customerId,
            'order_id'    => $orderId,
            'subject'     => $subject,
            'priority'    => $priority,
            'status'      => 'open',
            'assigned_to' => $assignedTo,
            'message'     => $initialMessage,
            'sender_type' => $senderType,
            'sender_id'   => $senderId
        ]);

        if ($ticketId > 0) {
            set_toast('success', 'Ticket Created', "Support ticket #TKT-" . str_pad((string)$ticketId, 5, '0', STR_PAD_LEFT) . " opened successfully.");
            redirect('admin/tickets/' . encrypt_id($ticketId));
        } else {
            set_toast('error', 'Creation Failed', 'Could not open support ticket.');
            redirect('admin/tickets/create');
        }
    }

    /**
     * Post an admin concierge reply to a ticket thread.
     */
    public function reply(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired. Please try again.');
            redirect('admin/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('admin/tickets');
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            set_toast('error', 'Validation Error', 'Reply message cannot be empty.');
            redirect('admin/tickets/' . $encryptedId);
        }

        $statusAction = trim($_POST['status_action'] ?? '');
        $allowedStatuses = ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'];
        $updateStatus = in_array($statusAction, $allowedStatuses, true) ? $statusAction : null;

        $admin = auth_admin();
        $adminId = $admin['id'] ?? 1;

        $success = Ticket::addMessage($id, 'admin', $adminId, $message, $updateStatus);

        if ($success) {
            // If ticket was unassigned, automatically assign to this replying admin
            $ticket = Ticket::find($id);
            if ($ticket && empty($ticket['assigned_to'])) {
                Ticket::assignAgent($id, $adminId);
            }

            set_toast('success', 'Reply Sent', 'Your response has been added to the ticket conversation.');
        } else {
            set_toast('error', 'Error', 'Failed to send reply.');
        }

        redirect('admin/tickets/' . $encryptedId);
    }

    /**
     * Update ticket lifecycle status.
     */
    public function updateStatus(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('admin/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('admin/tickets');
        }

        $status = trim($_POST['status'] ?? '');
        $allowed = ['open', 'acknowledged', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowed, true)) {
            set_toast('error', 'Validation Error', 'Invalid ticket status.');
            redirect('admin/tickets/' . $encryptedId);
        }

        $success = Ticket::updateStatus($id, $status);

        if ($success) {
            set_toast('success', 'Status Updated', 'Ticket status changed to ' . strtoupper(str_replace('_', ' ', $status)) . '.');
        } else {
            set_toast('error', 'Update Failed', 'Could not update ticket status.');
        }

        $returnUrl = $_POST['return_url'] ?? ('admin/tickets/' . $encryptedId);
        redirect($returnUrl);
    }

    /**
     * Update ticket urgency priority.
     */
    public function updatePriority(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('admin/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('admin/tickets');
        }

        $priority = trim($_POST['priority'] ?? '');
        $allowed = ['low', 'medium', 'high', 'critical'];
        if (!in_array($priority, $allowed, true)) {
            set_toast('error', 'Validation Error', 'Invalid priority.');
            redirect('admin/tickets/' . $encryptedId);
        }

        $success = Ticket::updatePriority($id, $priority);

        if ($success) {
            set_toast('success', 'Priority Updated', 'Ticket priority set to ' . strtoupper($priority) . '.');
        } else {
            set_toast('error', 'Update Failed', 'Could not update priority.');
        }

        $returnUrl = $_POST['return_url'] ?? ('admin/tickets/' . $encryptedId);
        redirect($returnUrl);
    }

    /**
     * Assign ticket to admin staff member.
     */
    public function assign(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('admin/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('admin/tickets');
        }

        $adminId = !empty($_POST['assigned_to']) && is_numeric($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        $success = Ticket::assignAgent($id, $adminId);

        if ($success) {
            set_toast('success', 'Ticket Assigned', $adminId ? 'Concierge staff assigned to ticket.' : 'Ticket marked as unassigned.');
        } else {
            set_toast('error', 'Assignment Failed', 'Could not assign ticket.');
        }

        $returnUrl = $_POST['return_url'] ?? ('admin/tickets/' . $encryptedId);
        redirect($returnUrl);
    }

    /**
     * Delete a support ticket and its conversation history.
     */
    public function destroy(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session expired.');
            redirect('admin/tickets');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid ticket identifier.');
            redirect('admin/tickets');
        }

        $success = Ticket::delete($id);

        if ($success) {
            set_toast('success', 'Ticket Deleted', 'The support ticket and thread have been permanently removed.');
        } else {
            set_toast('error', 'Delete Failed', 'Could not delete support ticket.');
        }

        redirect('admin/tickets');
    }

    /**
     * AJAX endpoint to retrieve orders for a selected customer.
     */
    public function customerOrders(string $encryptedCustomerId): void {
        AuthMiddleware::check();

        $customerId = decrypt_id($encryptedCustomerId);
        if (!$customerId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'orders' => []]);
            exit;
        }

        $orders = Ticket::getOrdersForCustomer($customerId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'orders' => $orders]);
        exit;
    }
}
