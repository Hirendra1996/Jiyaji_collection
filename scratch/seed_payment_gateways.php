<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/PaymentGateway.php';

use App\Config\Database;
use App\Models\PaymentGateway;

echo "=== Seeding Payment Gateways & Sample Payments Ledger ===\n";

// 1. Ensure default configs exist
PaymentGateway::ensureDefaults();
echo "✓ Default payment_methods_config verified/created.\n";

$db = Database::connect();

// 2. Check if payments table already has records
$check = $db->query("SELECT COUNT(*) FROM payments");
$count = $check ? (int)$check->fetch_row()[0] : 0;

if ($count === 0) {
    echo "Seeding sample payment records...\n";
    // Get real order IDs
    $ordRes = $db->query("SELECT id, order_number, grand_total, payment_method, payment_status, placed_at FROM orders ORDER BY id DESC LIMIT 30");
    $orders = $ordRes ? $ordRes->fetch_all(MYSQLI_ASSOC) : [];

    $gateways = ['razorpay', 'razorpay', 'razorpay', 'cod', 'bank_transfer'];
    $methods  = ['upi', 'card', 'netbanking', 'wallet'];

    $inserted = 0;
    foreach ($orders as $idx => $ord) {
        $orderId = (int)$ord['id'];
        $amount = (float)$ord['grand_total'];
        $placedAt = $ord['placed_at'] ?? date('Y-m-d H:i:s');

        // Determine gateway and status
        $gateway = 'razorpay';
        if (stripos($ord['payment_method'], 'cod') !== false) {
            $gateway = 'cod';
        } elseif (stripos($ord['payment_method'], 'bank') !== false) {
            $gateway = 'bank_transfer';
        }

        $status = 'success';
        if ($ord['payment_status'] === 'failed' || $idx === 5 || $idx === 14) {
            $status = 'failed';
        } elseif ($ord['payment_status'] === 'refunded' || $idx === 11) {
            $status = 'refunded';
        }

        $methodRail = $methods[$idx % count($methods)];
        $payId = 'pay_' . substr(md5($orderId . 'lx_pay' . $idx), 0, 14);
        $rzpOrderId = 'order_' . substr(md5($orderId . 'lx_rzp'), 0, 14);
        $signature = hash_hmac('sha256', $rzpOrderId . '|' . $payId, 'sec_lx9182374650abc');

        $payload = [
            'entity'        => 'event',
            'account_id'    => 'acc_JiyajiLuxury',
            'event'         => $status === 'success' ? 'payment.captured' : ($status === 'refunded' ? 'refund.processed' : 'payment.failed'),
            'contains'      => ['payment'],
            'payload'       => [
                'payment' => [
                    'entity' => [
                        'id'             => $payId,
                        'order_id'       => $rzpOrderId,
                        'amount'         => (int)($amount * 100),
                        'currency'       => 'INR',
                        'status'         => $status === 'success' ? 'captured' : $status,
                        'method'         => $methodRail,
                        'bank'           => $methodRail === 'netbanking' ? 'HDFC' : null,
                        'wallet'         => $methodRail === 'wallet' ? 'paytm' : null,
                        'vpa'            => $methodRail === 'upi' ? 'customer@okhdfcbank' : null,
                        'email'          => 'vip.client' . $idx . '@example.com',
                        'contact'        => '+9198765' . sprintf("%05d", $idx),
                        'error_code'     => $status === 'failed' ? 'BAD_REQUEST_ERROR' : null,
                        'error_desc'     => $status === 'failed' ? 'Payment failed due to customer OTP timeout.' : null,
                    ]
                ]
            ],
            'created_at'    => strtotime($placedAt)
        ];

        $jsonPayload = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $stmt = $db->prepare("
            INSERT INTO payments (order_id, gateway, gateway_order_id, gateway_payment_id, gateway_signature, amount, currency, status, webhook_payload, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'INR', ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssdssss", $orderId, $gateway, $rzpOrderId, $payId, $signature, $amount, $status, $jsonPayload, $placedAt, $placedAt);
        if ($stmt->execute()) {
            $inserted++;
        }
        $stmt->close();
    }
    echo "✓ Inserted {$inserted} sample payment transactions into `payments` table.\n";
} else {
    echo "✓ Payments table already contains {$count} records.\n";
}

// Check KPIs
$kpis = PaymentGateway::getGatewayKPIs();
echo "KPI Summary:\n";
print_r($kpis);

echo "\nDone!\n";
