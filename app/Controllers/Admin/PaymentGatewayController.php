<?php

namespace App\Controllers\Admin;

use App\Models\PaymentGateway;
use App\Middleware\AuthMiddleware;
use Exception;

class PaymentGatewayController {

    // =========================================================================
    // INDEX — Payment Gateways & Settlement Command Center
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $activeTab = $_GET['tab'] ?? 'overview';
        $validTabs = ['overview', 'razorpay', 'cod', 'bank_transfer', 'transactions'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'overview';
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $filters = [
            'gateway' => $_GET['gateway'] ?? 'all',
            'status'  => $_GET['status']  ?? 'all',
            'search'  => trim($_GET['search'] ?? ''),
        ];

        $gateways         = PaymentGateway::getGateways();
        $kpis             = PaymentGateway::getGatewayKPIs();
        $transactionsData = PaymentGateway::getRecentTransactions($filters, $page, 15);

        $transactions     = $transactionsData['transactions'];
        $pagination       = $transactionsData['pagination'];

        $title = 'Payment Gateways & COD Rules | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/payment_gateways/index.php';
    }

    // =========================================================================
    // UPDATE — Save Gateway Configuration (Razorpay / COD / Bank Wire)
    // =========================================================================

    public function update(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/payment-gateways');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF verification token expired. Please try again.');
            redirect('admin/payment-gateways');
        }

        $gateway = $_POST['gateway'] ?? '';
        $success = false;
        $tab = 'overview';

        if ($gateway === 'razorpay') {
            $success = PaymentGateway::updateRazorpay($_POST);
            $tab = 'razorpay';
            if ($success) {
                set_toast('success', 'Razorpay Updated', 'Razorpay credentials and payment rail settings saved.');
            } else {
                set_toast('error', 'Update Error', 'Failed to update Razorpay settings.');
            }
        } elseif ($gateway === 'cod') {
            $success = PaymentGateway::updateCOD($_POST);
            $tab = 'cod';
            if ($success) {
                set_toast('success', 'COD Rules Saved', 'Cash on Delivery limits and serviceable pincodes updated.');
            } else {
                set_toast('error', 'Update Error', 'Failed to update COD settings.');
            }
        } elseif ($gateway === 'bank_transfer') {
            $success = PaymentGateway::updateBankTransfer($_POST);
            $tab = 'bank_transfer';
            if ($success) {
                set_toast('success', 'Bank Wire Saved', 'VIP Concierge bank transfer credentials updated.');
            } else {
                set_toast('error', 'Update Error', 'Failed to update Bank Transfer settings.');
            }
        }

        redirect('admin/payment-gateways?tab=' . $tab);
    }

    // =========================================================================
    // TOGGLE — Quick Master Enable / Disable
    // =========================================================================

    public function toggle(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/payment-gateways');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF verification token expired. Please try again.');
            redirect('admin/payment-gateways');
        }

        $method = $_POST['method'] ?? '';
        $enabled = !empty($_POST['is_enabled']) && $_POST['is_enabled'] === '1';

        $updated = PaymentGateway::toggleGateway($method, $enabled);
        if ($updated) {
            $statusStr = $enabled ? 'enabled' : 'disabled';
            set_toast('success', 'Status Updated', "Payment gateway '{$method}' has been {$statusStr}.");
        } else {
            set_toast('error', 'Update Error', 'Failed to change gateway status.');
        }

        $tab = $_POST['tab'] ?? 'overview';
        redirect('admin/payment-gateways?tab=' . $tab);
    }

    // =========================================================================
    // CHECK PINCODE — Live COD Serviceability Check (AJAX / JSON)
    // =========================================================================

    public function checkPincode(): void {
        AuthMiddleware::check();

        $pincode = trim($_POST['pincode'] ?? ($_GET['pincode'] ?? ''));
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : (isset($_GET['amount']) ? (float)$_GET['amount'] : 1000.0);

        $result = PaymentGateway::isPincodeEligibleForCOD($pincode, $amount);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // =========================================================================
    // TEST CONNECTION — Razorpay Credentials Verification (AJAX / JSON)
    // =========================================================================

    public function testConnection(): void {
        AuthMiddleware::check();

        $keyId = trim($_POST['key_id'] ?? '');
        $keySecret = trim($_POST['key_secret'] ?? '');

        if ($keyId === '') {
            $rzp = PaymentGateway::getGateway('razorpay');
            $keyId = $rzp['config']['key_id'] ?? '';
            $keySecret = $rzp['config']['key_secret'] ?? '';
        }

        $check = PaymentGateway::validateKeys($keyId, $keySecret);

        header('Content-Type: application/json');
        echo json_encode($check);
        exit;
    }
}
