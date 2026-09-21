<?php

namespace App\Controllers\Portal;

use App\Models\Customer;
use App\Middleware\PortalAuthMiddleware;
use Exception;

/**
 * PortalCustomerController
 *
 * Provides the Customer Directory & Shopper Profiles hub for the Staff & Operations Portal.
 * All routes are guarded by PortalAuthMiddleware and granular RBAC:
 *   - customers:view  → browse ledger, view profiles, address book, export CSV
 *   - customers:edit  → create, update profiles, toggle status
 */
class PortalCustomerController {

    /**
     * Customer directory index with KPI metrics and search/filter controls.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view the Customer Directory.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'        => $_GET['status'] ?? 'all',
            'verification'  => $_GET['verification'] ?? 'all',
            'orders_filter' => $_GET['orders_filter'] ?? 'all',
            'search'        => trim($_GET['search'] ?? ''),
            'sort'          => $_GET['sort'] ?? 'newest',
        ];

        $page       = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $custData   = Customer::getAll($filters, $page, 15);
        $customers  = $custData['customers'];
        $pagination = $custData['pagination'];
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis      = Customer::getKPIs();
        $canManage = staff_can('customers', 'edit');

        $title = 'Customer Directory | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/customers/index.php';
    }

    /**
     * Show a detailed customer profile dossier: metrics, order history, address book.
     */
    public function show(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Customer Profiles.');
            redirect('portal/dashboard');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid ID', 'The customer identifier is invalid or tampered.');
            redirect('portal/customers');
        }

        $customer = Customer::find($id);
        if (!$customer) {
            set_toast('error', 'Customer Not Found', 'The requested customer profile does not exist.');
            redirect('portal/customers');
        }

        $canManage = staff_can('customers', 'edit');
        $title     = "Customer Profile: {$customer['name']} | Jiyaji LX Staff Portal";
        include __DIR__ . '/../../Views/portal/customers/show.php';
    }

    /**
     * Show form to create a new customer account.
     */
    public function create(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to create Customer Profiles.');
            redirect('portal/customers');
        }

        $title = 'Add New Customer | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/customers/create.php';
    }

    /**
     * Store a newly created customer.
     */
    public function store(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('portal/customers');
        }

        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Customer full name is required.');
            redirect('portal/customers/create');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_toast('error', 'Validation Error', 'A valid email address is required.');
            redirect('portal/customers/create');
        }

        if (Customer::findByEmail($email)) {
            set_toast('error', 'Email In Use', "A customer account with email '{$email}' already exists.");
            redirect('portal/customers/create');
        }

        $password = trim($_POST['password'] ?? '');
        if (empty($password)) {
            $password = 'Welcome@123';
        }

        $emailVerified = !empty($_POST['email_verified']) ? 1 : 0;
        $isActive      = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        try {
            $newId = Customer::create([
                'name'              => $name,
                'email'             => $email,
                'phone'             => $phone,
                'password'          => $password,
                'email_verified'    => $emailVerified,
                'is_active'         => $isActive,
                'address_label'     => $_POST['address_label'] ?? 'Home',
                'address_recipient' => $_POST['address_recipient'] ?? $name,
                'address_phone'     => $_POST['address_phone'] ?? $phone,
                'address_line1'     => $_POST['address_line1'] ?? '',
                'address_line2'     => $_POST['address_line2'] ?? '',
                'city'              => $_POST['city'] ?? '',
                'state'             => $_POST['state'] ?? '',
                'pincode'           => $_POST['pincode'] ?? '',
                'country'           => $_POST['country'] ?? 'India',
            ]);

            if ($newId > 0) {
                $encId = encrypt_id($newId);
                set_toast('success', 'Customer Created', "Profile for '{$name}' has been created successfully.");
                redirect("portal/customers/{$encId}");
            } else {
                set_toast('error', 'Creation Error', 'Unable to create customer profile. Please try again.');
                redirect('portal/customers/create');
            }
        } catch (Exception $e) {
            set_toast('error', 'Server Error', 'Could not save customer: ' . $e->getMessage());
            redirect('portal/customers/create');
        }
    }

    /**
     * Show form to edit an existing customer profile.
     */
    public function edit(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied: You do not have clearance to edit Customer Profiles.');
            redirect('portal/customers');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid ID', 'The customer identifier is invalid.');
            redirect('portal/customers');
        }

        $customer = Customer::find($id);
        if (!$customer) {
            set_toast('error', 'Customer Not Found', 'The requested customer profile does not exist.');
            redirect('portal/customers');
        }

        $title = "Edit Customer: {$customer['name']} | Jiyaji LX Staff Portal";
        include __DIR__ . '/../../Views/portal/customers/edit.php';
    }

    /**
     * Update customer profile details.
     */
    public function update(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/customers');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid ID', 'Invalid customer identifier.');
            redirect('portal/customers');
        }

        $customer = Customer::find($id);
        if (!$customer) {
            set_toast('error', 'Not Found', 'Customer does not exist.');
            redirect('portal/customers');
        }

        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Customer full name is required.');
            redirect("portal/customers/{$encryptedId}/edit");
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_toast('error', 'Validation Error', 'A valid email address is required.');
            redirect("portal/customers/{$encryptedId}/edit");
        }

        if (Customer::findByEmail($email, $id)) {
            set_toast('error', 'Email In Use', "Email '{$email}' is already registered by another customer.");
            redirect("portal/customers/{$encryptedId}/edit");
        }

        $data = [
            'name'           => $name,
            'email'          => $email,
            'phone'          => $phone,
            'email_verified' => !empty($_POST['email_verified']) ? 1 : 0,
            'is_active'      => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1,
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = trim($_POST['password']);
        }

        $updated = Customer::update($id, $data);
        if ($updated) {
            set_toast('success', 'Profile Updated', "Customer details for '{$name}' have been updated.");
            redirect("portal/customers/{$encryptedId}");
        } else {
            set_toast('error', 'Update Failed', 'Failed to update customer details. Please try again.');
            redirect("portal/customers/{$encryptedId}/edit");
        }
    }

    /**
     * Toggle customer active / suspended status.
     */
    public function toggleStatus(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/customers');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid ID', 'Invalid customer identifier.');
            redirect('portal/customers');
        }

        $success  = Customer::toggleStatus($id);
        $customer = Customer::find($id);
        $statusText = ($customer && $customer['is_active']) ? 'Activated' : 'Suspended';

        if ($success) {
            set_toast('success', 'Status Updated', "Customer account has been {$statusText}.");
        } else {
            set_toast('error', 'Update Failed', 'Could not toggle account status.');
        }

        $returnUrl = $_POST['return_url'] ?? 'portal/customers';
        redirect($returnUrl);
    }

    /**
     * Add an address to the customer's address book.
     */
    public function storeAddress(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect("portal/customers/{$encryptedId}");
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Invalid ID', 'Invalid customer identifier.');
            redirect('portal/customers');
        }

        $line1 = trim($_POST['address_line1'] ?? '');
        $city  = trim($_POST['city'] ?? '');

        if (empty($line1) || empty($city)) {
            set_toast('error', 'Validation Error', 'Address Line 1 and City are required.');
            redirect("portal/customers/{$encryptedId}");
        }

        $newAddrId = Customer::addAddress($id, [
            'label'         => $_POST['label'] ?? 'Home',
            'recipient'     => trim($_POST['recipient'] ?? ''),
            'phone'         => trim($_POST['phone'] ?? ''),
            'address_line1' => $line1,
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city'          => $city,
            'state'         => trim($_POST['state'] ?? ''),
            'pincode'       => trim($_POST['pincode'] ?? ''),
            'country'       => trim($_POST['country'] ?? 'India'),
            'is_default'    => !empty($_POST['is_default']) ? 1 : 0,
        ]);

        if ($newAddrId > 0) {
            set_toast('success', 'Address Saved', 'New delivery address added to address book.');
        } else {
            set_toast('error', 'Error', 'Failed to save address. Please try again.');
        }

        redirect("portal/customers/{$encryptedId}");
    }

    /**
     * Delete an address from customer's address book.
     */
    public function deleteAddress(string $encryptedId, string $addressEncryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect("portal/customers/{$encryptedId}");
        }

        $id     = decrypt_id($encryptedId);
        $addrId = decrypt_id($addressEncryptedId);

        if (!$id || !$addrId) {
            set_toast('error', 'Invalid ID', 'Invalid customer or address identifier.');
            redirect('portal/customers');
        }

        $deleted = Customer::deleteAddress($addrId, $id);
        if ($deleted) {
            set_toast('success', 'Address Removed', 'Delivery address was successfully deleted.');
        } else {
            set_toast('error', 'Error', 'Failed to remove address.');
        }

        redirect("portal/customers/{$encryptedId}");
    }

    /**
     * Set an address as the customer's default delivery destination.
     */
    public function setDefaultAddress(string $encryptedId, string $addressEncryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'edit')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect("portal/customers/{$encryptedId}");
        }

        $id     = decrypt_id($encryptedId);
        $addrId = decrypt_id($addressEncryptedId);

        if (!$id || !$addrId) {
            set_toast('error', 'Invalid ID', 'Invalid customer or address identifier.');
            redirect('portal/customers');
        }

        $updated = Customer::setDefaultAddress($addrId, $id);
        if ($updated) {
            set_toast('success', 'Default Updated', 'Selected address is now the primary delivery destination.');
        } else {
            set_toast('error', 'Error', 'Failed to set default address.');
        }

        redirect("portal/customers/{$encryptedId}");
    }

    /**
     * Stream CSV export of the customer directory.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('customers', 'view')) {
            set_flash('error', 'Access Denied.');
            redirect('portal/customers');
        }

        $filters = [
            'status'        => $_GET['status'] ?? 'all',
            'verification'  => $_GET['verification'] ?? 'all',
            'orders_filter' => $_GET['orders_filter'] ?? 'all',
            'search'        => trim($_GET['search'] ?? ''),
            'sort'          => $_GET['sort'] ?? 'newest',
        ];

        $all = Customer::getAll($filters, 1, 2000);

        $filename = 'jiyaji_customers_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Customer ID', 'Full Name', 'Email', 'Phone',
            'Account Status', 'Email Verified',
            'Total Orders', 'Total Spend (INR)', 'Last Order Date', 'Registered On'
        ]);

        foreach ($all['customers'] as $c) {
            fputcsv($out, [
                $c['id'],
                $c['name'],
                $c['email'],
                $c['phone'] ?? '',
                (int)$c['is_active'] ? 'Active' : 'Suspended',
                (int)$c['email_verified'] ? 'Verified' : 'Unverified',
                (int)$c['order_count'],
                '₹' . number_format((int)$c['total_spent']),
                !empty($c['last_order_at']) ? date('M d, Y', strtotime($c['last_order_at'])) : 'Never',
                !empty($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '',
            ]);
        }

        fclose($out);
        exit;
    }
}
