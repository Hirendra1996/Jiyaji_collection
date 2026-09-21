<?php

namespace App\Controllers\Admin;

use App\Models\Customer;
use App\Middleware\AuthMiddleware;
use Exception;

class CustomerController {
    /**
     * Display customers directory with KPIs and search/filters.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'        => $_GET['status'] ?? 'all',
            'verification'  => $_GET['verification'] ?? 'all',
            'orders_filter' => $_GET['orders_filter'] ?? 'all',
            'search'        => trim($_GET['search'] ?? ''),
            'sort'          => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $custData   = Customer::getAll($filters, $page, 15);
        $customers  = $custData['customers'];
        $pagination = $custData['pagination'];

        $kpis  = Customer::getKPIs();
        $title = 'Customers & Profiles Ledger | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/customers/index.php';
    }

    /**
     * Show executive luxury customer profile dossier with orders & address book.
     */
    public function show(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid or tampered customer identifier.');
            redirect('admin/customers');
        }

        $customer = Customer::find($id);
        if (!$customer) {
            set_toast('error', 'Customer Not Found', 'The requested customer profile does not exist or was deleted.');
            redirect('admin/customers');
        }

        $title = "Customer Profile: {$customer['name']} | Jiyaji LX";
        include __DIR__ . '/../../Views/admin/customers/show.php';
    }

    /**
     * Show form to create a new luxury customer account.
     */
    public function create(): void {
        AuthMiddleware::check();

        $title = 'Add New Customer | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/customers/create.php';
    }

    /**
     * Store newly created customer account.
     */
    public function store(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/customers');
        }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Customer full name is required.');
            redirect('admin/customers/create');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_toast('error', 'Validation Error', 'A valid email address is required.');
            redirect('admin/customers/create');
        }

        // Check if email already registered
        if (Customer::findByEmail($email)) {
            set_toast('error', 'Email In Use', "A customer with email '{$email}' already exists.");
            redirect('admin/customers/create');
        }

        $password = trim($_POST['password'] ?? '');
        if (empty($password)) {
            $password = 'Welcome@123';
        }

        $emailVerified = !empty($_POST['email_verified']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

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
                'country'           => $_POST['country'] ?? 'India'
            ]);

            if ($newId > 0) {
                $encId = encrypt_id($newId);
                set_toast('success', 'Customer Created', "Customer profile for '{$name}' has been created successfully.");
                redirect("admin/customers/{$encId}");
            } else {
                set_toast('error', 'Creation Error', 'Unable to create customer profile.');
                redirect('admin/customers/create');
            }
        } catch (Exception $e) {
            set_toast('error', 'Creation Failed', 'Could not save customer: ' . $e->getMessage());
            redirect('admin/customers/create');
        }
    }

    /**
     * Show form to edit an existing customer profile.
     */
    public function edit(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid or tampered customer identifier.');
            redirect('admin/customers');
        }

        $customer = Customer::find($id);
        if (!$customer) {
            set_toast('error', 'Customer Not Found', 'The requested customer profile does not exist.');
            redirect('admin/customers');
        }

        $title = "Edit Customer: {$customer['name']} | Jiyaji LX";
        include __DIR__ . '/../../Views/admin/customers/edit.php';
    }

    /**
     * Update customer profile details.
     */
    public function update(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired. Please try again.');
            redirect('admin/customers');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid customer identifier.');
            redirect('admin/customers');
        }

        $customer = Customer::find($id);
        if (!$customer) {
            set_toast('error', 'Customer Not Found', 'The requested customer does not exist.');
            redirect('admin/customers');
        }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            set_toast('error', 'Validation Error', 'Customer full name is required.');
            redirect("admin/customers/{$encryptedId}/edit");
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_toast('error', 'Validation Error', 'A valid email address is required.');
            redirect("admin/customers/{$encryptedId}/edit");
        }

        // Email uniqueness check (excluding self)
        if (Customer::findByEmail($email, $id)) {
            set_toast('error', 'Email In Use', "Email '{$email}' is already in use by another customer.");
            redirect("admin/customers/{$encryptedId}/edit");
        }

        $data = [
            'name'           => $name,
            'email'          => $email,
            'phone'          => $phone,
            'email_verified' => !empty($_POST['email_verified']) ? 1 : 0,
            'is_active'      => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1
        ];

        // Optional password reset
        if (!empty($_POST['password'])) {
            $data['password'] = trim($_POST['password']);
        }

        $updated = Customer::update($id, $data);
        if ($updated) {
            set_toast('success', 'Profile Updated', "Customer details for '{$name}' have been updated.");
            redirect("admin/customers/{$encryptedId}");
        } else {
            set_toast('error', 'Update Failed', 'Failed to update customer details.');
            redirect("admin/customers/{$encryptedId}/edit");
        }
    }

    /**
     * Toggle customer active/suspended status.
     */
    public function toggleStatus(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid session token']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/customers');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid customer identifier']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid customer identifier.');
            redirect('admin/customers');
        }

        $success = Customer::toggleStatus($id);
        $customer = Customer::find($id);
        $statusText = ($customer && $customer['is_active']) ? 'Activated' : 'Suspended';

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => $success,
                'is_active' => $customer['is_active'] ?? 0,
                'message'   => "Customer account has been {$statusText}."
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Status Updated', "Customer account has been {$statusText}.");
        } else {
            set_toast('error', 'Update Failed', 'Could not toggle account status.');
        }

        // Return back to referrer or list
        $returnUrl = $_POST['return_url'] ?? "admin/customers";
        redirect($returnUrl);
    }

    /**
     * Safeguarded customer deletion.
     */
    public function destroy(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/customers');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid customer identifier.');
            redirect('admin/customers');
        }

        $res = Customer::delete($id);

        if ($res['success']) {
            set_toast('success', 'Customer Deleted', $res['message']);
            redirect('admin/customers');
        } else {
            set_toast('warning', 'Action Prevented', $res['message']);
            redirect("admin/customers/{$encryptedId}");
        }
    }

    /**
     * Add address to customer address book from profile view.
     */
    public function storeAddress(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect("admin/customers/{$encryptedId}");
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid customer identifier.');
            redirect('admin/customers');
        }

        $line1 = trim($_POST['address_line1'] ?? '');
        $city  = trim($_POST['city'] ?? '');
        if (empty($line1) || empty($city)) {
            set_toast('error', 'Validation Error', 'Address Line 1 and City are required.');
            redirect("admin/customers/{$encryptedId}");
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
            'is_default'    => !empty($_POST['is_default']) ? 1 : 0
        ]);

        if ($newAddrId > 0) {
            set_toast('success', 'Address Saved', 'New delivery address added to address book.');
        } else {
            set_toast('error', 'Error', 'Failed to save address.');
        }

        redirect("admin/customers/{$encryptedId}");
    }

    /**
     * Delete an address from customer address book.
     */
    public function deleteAddress(string $encryptedId, string $addressEncryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect("admin/customers/{$encryptedId}");
        }

        $id = decrypt_id($encryptedId);
        $addrId = decrypt_id($addressEncryptedId);

        if (!$id || !$addrId) {
            set_toast('error', 'Access Denied', 'Invalid identifiers.');
            redirect('admin/customers');
        }

        $deleted = Customer::deleteAddress($addrId, $id);
        if ($deleted) {
            set_toast('success', 'Address Removed', 'Delivery address was deleted from address book.');
        } else {
            set_toast('error', 'Error', 'Failed to remove address.');
        }

        redirect("admin/customers/{$encryptedId}");
    }

    /**
     * Set address as default.
     */
    public function setDefaultAddress(string $encryptedId, string $addressEncryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect("admin/customers/{$encryptedId}");
        }

        $id = decrypt_id($encryptedId);
        $addrId = decrypt_id($addressEncryptedId);

        if (!$id || !$addrId) {
            set_toast('error', 'Access Denied', 'Invalid identifiers.');
            redirect('admin/customers');
        }

        $updated = Customer::setDefaultAddress($addrId, $id);
        if ($updated) {
            set_toast('success', 'Default Address Updated', 'Selected address is now the primary delivery destination.');
        } else {
            set_toast('error', 'Error', 'Failed to set default address.');
        }

        redirect("admin/customers/{$encryptedId}");
    }

    /**
     * Check if request is AJAX.
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
