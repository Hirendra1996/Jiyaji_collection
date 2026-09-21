<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class PaymentGateway {

    // =========================================================================
    // 1. GET ALL GATEWAYS & CONFIGURATION
    // =========================================================================

    /**
     * Retrieve all configured payment gateways with decoded configuration.
     */
    public static function getGateways(): array {
        self::ensureDefaults();

        try {
            $db = Database::connect();
            $res = $db->query("SELECT * FROM payment_methods_config ORDER BY id ASC");
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

            $gateways = [];
            foreach ($rows as $r) {
                $cfg = !empty($r['config_data']) ? json_decode($r['config_data'], true) : [];
                $gateways[$r['method']] = [
                    'id'                    => (int)$r['id'],
                    'method'                => $r['method'],
                    'title'                 => $r['title'] ?? ucfirst($r['method']),
                    'description'           => $r['description'] ?? '',
                    'is_enabled'            => (bool)$r['is_enabled'],
                    'cod_min_order_value'   => (float)($r['cod_min_order_value'] ?? 0),
                    'cod_pincode_whitelist' => $r['cod_pincode_whitelist'] ?? '',
                    'config'                => is_array($cfg) ? $cfg : [],
                    'updated_at'            => $r['updated_at'],
                ];
            }

            return $gateways;
        } catch (Exception $e) {
            error_log("PaymentGateway::getGateways error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve configuration for a specific gateway.
     */
    public static function getGateway(string $method): ?array {
        $gateways = self::getGateways();
        return $gateways[$method] ?? null;
    }

    // =========================================================================
    // 2. UPDATE RAZORPAY CONFIGURATION
    // =========================================================================

    /**
     * Save Razorpay online gateway credentials, mode, and enabled instruments.
     */
    public static function updateRazorpay(array $data): bool {
        try {
            $db = Database::connect();
            $isEnabled = !empty($data['is_enabled']) ? 1 : 0;
            $mode = ($data['mode'] ?? 'test') === 'live' ? 'live' : 'test';
            $keyId = trim($data['key_id'] ?? '');
            $keySecret = trim($data['key_secret'] ?? '');
            $webhookSecret = trim($data['webhook_secret'] ?? '');
            $autoCapture = !empty($data['auto_capture']) ? 1 : 0;

            $rails = [
                'upi'        => !empty($data['rail_upi']),
                'cards'      => !empty($data['rail_cards']),
                'netbanking' => !empty($data['rail_netbanking']),
                'wallets'    => !empty($data['rail_wallets']),
                'emi'        => !empty($data['rail_emi']),
            ];

            // If secret was not entered or left as mask '••••••••', keep existing secret
            if ($keySecret === '' || $keySecret === '••••••••' || str_starts_with($keySecret, '••••')) {
                $existing = self::getGateway('razorpay');
                $keySecret = $existing['config']['key_secret'] ?? '';
            }

            $config = [
                'mode'           => $mode,
                'key_id'         => $keyId,
                'key_secret'     => $keySecret,
                'webhook_secret' => $webhookSecret,
                'auto_capture'   => $autoCapture,
                'rails'          => $rails,
            ];
            $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES);

            $title = 'Razorpay Secure Checkout';
            $desc = 'Instant online payments via UPI, Credit/Debit Cards, NetBanking, and EMI.';

            $stmt = $db->prepare("
                UPDATE payment_methods_config
                SET title = ?, description = ?, is_enabled = ?, config_data = ?, updated_at = NOW()
                WHERE method = 'razorpay'
            ");
            $stmt->bind_param("ssis", $title, $desc, $isEnabled, $jsonConfig);
            $success = $stmt->execute();
            $stmt->close();

            // Synchronize with site_settings table
            self::setSiteSetting('razorpay_key_id', $keyId, 'Razorpay Key ID');
            self::setSiteSetting('razorpay_key_secret', $keySecret, 'Razorpay Key Secret');
            self::setSiteSetting('razorpay_mode', $mode, 'Razorpay Mode');
            self::setSiteSetting('razorpay_enabled', (string)$isEnabled, 'Razorpay Enabled');

            return $success;
        } catch (Exception $e) {
            error_log("PaymentGateway::updateRazorpay error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 3. UPDATE CASH ON DELIVERY (COD) CONFIGURATION
    // =========================================================================

    /**
     * Save COD anti-fraud limits, convenience fees, and serviceable pincode whitelist.
     */
    public static function updateCOD(array $data): bool {
        try {
            $db = Database::connect();
            $isEnabled = !empty($data['is_enabled']) ? 1 : 0;
            $minVal = isset($data['min_order_value']) ? max(0, (float)$data['min_order_value']) : 999.00;
            $maxVal = isset($data['max_order_value']) ? max(0, (float)$data['max_order_value']) : 50000.00;
            $fee = isset($data['cod_fee']) ? max(0, (float)$data['cod_fee']) : 0.00;
            $pincodeMode = ($data['pincode_mode'] ?? 'all') === 'whitelist' ? 'whitelist' : 'all';
            $whitelist = trim($data['pincode_whitelist'] ?? '');

            $config = [
                'max_order_value' => $maxVal,
                'cod_fee'         => $fee,
                'pincode_mode'    => $pincodeMode,
                'otp_verify'      => !empty($data['otp_verify']),
            ];
            $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES);

            $title = 'Cash on Delivery (COD)';
            $desc = 'Pay in cash upon doorstep delivery of your luxury garment.';

            $stmt = $db->prepare("
                UPDATE payment_methods_config
                SET title = ?, description = ?, is_enabled = ?, cod_min_order_value = ?, cod_pincode_whitelist = ?, config_data = ?, updated_at = NOW()
                WHERE method = 'cod'
            ");
            $stmt->bind_param("ssidss", $title, $desc, $isEnabled, $minVal, $whitelist, $jsonConfig);
            $success = $stmt->execute();
            $stmt->close();

            // Synchronize with site_settings table
            self::setSiteSetting('cod_enabled', (string)$isEnabled, 'COD Enabled');
            self::setSiteSetting('cod_min_order_value', (string)$minVal, 'COD Min Order Value');
            self::setSiteSetting('cod_max_order_value', (string)$maxVal, 'COD Max Order Value');

            return $success;
        } catch (Exception $e) {
            error_log("PaymentGateway::updateCOD error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 4. UPDATE VIP BANK WIRE TRANSFER (NEFT / RTGS)
    // =========================================================================

    /**
     * Save VIP Concierge Direct Bank Transfer details.
     */
    public static function updateBankTransfer(array $data): bool {
        try {
            $db = Database::connect();
            $isEnabled = !empty($data['is_enabled']) ? 1 : 0;

            $config = [
                'bank_name'      => trim($data['bank_name'] ?? 'HDFC Bank'),
                'account_name'   => trim($data['account_name'] ?? 'Jiyaji Luxury Collections Pvt Ltd'),
                'account_number' => trim($data['account_number'] ?? ''),
                'ifsc_code'      => strtoupper(trim($data['ifsc_code'] ?? '')),
                'branch'         => trim($data['branch'] ?? ''),
                'upi_id'         => trim($data['upi_id'] ?? ''),
                'instructions'   => trim($data['instructions'] ?? 'Please share the UTR / transaction receipt via WhatsApp or email to confirm dispatch.'),
            ];
            $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES);

            $title = 'VIP Concierge Bank Transfer (NEFT / RTGS)';
            $desc = 'Direct high-value bespoke wire transfer for exclusive bespoke couture orders.';

            $stmt = $db->prepare("
                UPDATE payment_methods_config
                SET title = ?, description = ?, is_enabled = ?, config_data = ?, updated_at = NOW()
                WHERE method = 'bank_transfer'
            ");
            $stmt->bind_param("ssis", $title, $desc, $isEnabled, $jsonConfig);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            error_log("PaymentGateway::updateBankTransfer error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 5. TOGGLE GATEWAY ON / OFF
    // =========================================================================

    /**
     * Enable or disable any payment method.
     */
    public static function toggleGateway(string $method, bool $enabled): bool {
        try {
            $db = Database::connect();
            $val = $enabled ? 1 : 0;
            $stmt = $db->prepare("UPDATE payment_methods_config SET is_enabled = ?, updated_at = NOW() WHERE method = ?");
            $stmt->bind_param("is", $val, $method);
            $success = $stmt->execute();
            $stmt->close();

            if ($method === 'cod') {
                self::setSiteSetting('cod_enabled', (string)$val, 'COD Enabled');
            } elseif ($method === 'razorpay') {
                self::setSiteSetting('razorpay_enabled', (string)$val, 'Razorpay Enabled');
            }

            return $success;
        } catch (Exception $e) {
            error_log("PaymentGateway::toggleGateway error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 6. COD PINCODE SERVICEABILITY CHECKER
    // =========================================================================

    /**
     * Verify whether a 6-digit Indian pincode is eligible for Cash on Delivery.
     */
    public static function isPincodeEligibleForCOD(string $pincode, float $orderAmount = 1000.0): array {
        $cleanPincode = preg_replace('/[^0-9]/', '', trim($pincode));

        if (strlen($cleanPincode) !== 6) {
            return [
                'eligible' => false,
                'pincode'  => $pincode,
                'reason'   => 'Invalid postal code format. Must be a 6-digit Indian PIN code.',
                'fee'      => 0.0,
            ];
        }

        $cod = self::getGateway('cod');
        if (!$cod || empty($cod['is_enabled'])) {
            return [
                'eligible' => false,
                'pincode'  => $cleanPincode,
                'reason'   => 'Cash on Delivery is currently disabled by store administration.',
                'fee'      => 0.0,
            ];
        }

        $minVal = (float)($cod['cod_min_order_value'] ?? 999.00);
        $maxVal = (float)($cod['config']['max_order_value'] ?? 50000.00);
        $fee    = (float)($cod['config']['cod_fee'] ?? 0.00);
        $mode   = $cod['config']['pincode_mode'] ?? 'all';

        if ($orderAmount > 0 && $orderAmount < $minVal) {
            return [
                'eligible' => false,
                'pincode'  => $cleanPincode,
                'reason'   => "Order amount (₹" . number_format($orderAmount) . ") is below minimum COD threshold of ₹" . number_format($minVal) . ".",
                'fee'      => $fee,
            ];
        }

        if ($maxVal > 0 && $orderAmount > $maxVal) {
            return [
                'eligible' => false,
                'pincode'  => $cleanPincode,
                'reason'   => "Orders exceeding ₹" . number_format($maxVal) . " require prepaid payment due to luxury transit insurance.",
                'fee'      => $fee,
            ];
        }

        // Whitelist validation
        if ($mode === 'whitelist') {
            $rawList = $cod['cod_pincode_whitelist'] ?? '';
            // Match comma, space, or newline separated pincodes
            preg_match_all('/\b\d{6}\b/', $rawList, $matches);
            $whitelist = !empty($matches[0]) ? $matches[0] : [];

            if (!in_array($cleanPincode, $whitelist, true)) {
                return [
                    'eligible' => false,
                    'pincode'  => $cleanPincode,
                    'reason'   => 'PIN code ' . $cleanPincode . ' is outside the serviceable COD delivery territory. Prepaid checkout is required.',
                    'fee'      => $fee,
                ];
            }
        }

        return [
            'eligible' => true,
            'pincode'  => $cleanPincode,
            'reason'   => 'Eligible for doorstep Cash on Delivery delivery.',
            'fee'      => $fee,
        ];
    }

    // =========================================================================
    // 7. PAYMENT TRANSACTIONS & AUDIT LOGS
    // =========================================================================

    /**
     * Retrieve paginated payment transactions joined with orders.
     */
    public static function getRecentTransactions(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            if (!empty($filters['gateway']) && $filters['gateway'] !== 'all') {
                $whereClauses[] = "p.gateway = ?";
                $params[] = $filters['gateway'];
                $types .= "s";
            }

            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $whereClauses[] = "p.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }

            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(o.order_number LIKE ? OR p.gateway_payment_id LIKE ? OR o.shipping_name LIKE ?)";
                $params[] = $wild;
                $params[] = $wild;
                $params[] = $wild;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Count
            $countSql = "SELECT COUNT(*) FROM payments p LEFT JOIN orders o ON p.order_id = o.id WHERE $whereSql";
            $stmtC = $db->prepare($countSql);
            if ($types !== "") {
                $stmtC->bind_param($types, ...$params);
            }
            $stmtC->execute();
            $total = (int)$stmtC->get_result()->fetch_row()[0];
            $stmtC->close();

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Fetch rows
            $selectSql = "
                SELECT
                    p.*,
                    o.order_number,
                    o.shipping_name,
                    o.payment_method AS order_payment_method,
                    o.grand_total AS order_grand_total
                FROM payments p
                LEFT JOIN orders o ON p.order_id = o.id
                WHERE $whereSql
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT ? OFFSET ?
            ";

            $pagParams = array_merge($params, [$perPage, $offset]);
            $pagTypes = $types . "ii";

            $stmt = $db->prepare($selectSql);
            $stmt->bind_param($pagTypes, ...$pagParams);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return [
                'transactions' => $rows,
                'pagination'   => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages,
                ]
            ];
        } catch (Exception $e) {
            error_log("PaymentGateway::getRecentTransactions error: " . $e->getMessage());
            return [
                'transactions' => [],
                'pagination'   => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false]
            ];
        }
    }

    /**
     * Record a payment transaction attempt or webhook log.
     */
    public static function recordTransaction(array $data): bool {
        try {
            $db = Database::connect();
            $orderId = (int)$data['order_id'];
            $gateway = $data['gateway'] ?? 'razorpay';
            $gatewayOrderId = $data['gateway_order_id'] ?? null;
            $gatewayPaymentId = $data['gateway_payment_id'] ?? null;
            $gatewaySig = $data['gateway_signature'] ?? null;
            $amount = (float)$data['amount'];
            $currency = $data['currency'] ?? 'INR';
            $status = $data['status'] ?? 'initiated';
            $payload = !empty($data['webhook_payload']) ? (is_string($data['webhook_payload']) ? $data['webhook_payload'] : json_encode($data['webhook_payload'])) : null;

            $stmt = $db->prepare("
                INSERT INTO payments (order_id, gateway, gateway_order_id, gateway_payment_id, gateway_signature, amount, currency, status, webhook_payload, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("issssdsss", $orderId, $gateway, $gatewayOrderId, $gatewayPaymentId, $gatewaySig, $amount, $currency, $status, $payload);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("PaymentGateway::recordTransaction error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 8. GATEWAY EXECUTIVE KPIS
    // =========================================================================

    /**
     * Retrieve executive summary KPIs for payment performance.
     */
    public static function getGatewayKPIs(): array {
        try {
            $db = Database::connect();
            $gateways = self::getGateways();

            $activeCount = 0;
            foreach ($gateways as $g) {
                if (!empty($g['is_enabled'])) {
                    $activeCount++;
                }
            }

            // Transaction statistics
            $txRow = $db->query("
                SELECT
                    COUNT(*) AS total_tx,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_tx,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_tx,
                    SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) AS refunded_tx,
                    COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) AS success_amount
                FROM payments
            ")->fetch_assoc();

            $totalTx = (int)($txRow['total_tx'] ?? 0);
            $successTx = (int)($txRow['success_tx'] ?? 0);
            $successRate = $totalTx > 0 ? round(($successTx / $totalTx) * 100, 1) : 100.0;

            // Orders volume breakdown: Razorpay vs COD
            $ordRow = $db->query("
                SELECT
                    SUM(CASE WHEN payment_method LIKE '%Razorpay%' OR payment_method LIKE '%UPI%' OR payment_method LIKE '%Card%' THEN 1 ELSE 0 END) AS online_orders,
                    SUM(CASE WHEN payment_method = 'COD' THEN 1 ELSE 0 END) AS cod_orders
                FROM orders
            ")->fetch_assoc();

            $onlineOrders = (int)($ordRow['online_orders'] ?? 0);
            $codOrders    = (int)($ordRow['cod_orders'] ?? 0);
            $totalOrders  = $onlineOrders + $codOrders;
            $onlinePct    = $totalOrders > 0 ? round(($onlineOrders / $totalOrders) * 100) : 85;

            $razorpay = $gateways['razorpay'] ?? [];
            $mode = $razorpay['config']['mode'] ?? 'test';

            return [
                'active_gateways'   => $activeCount,
                'total_gateways'    => count($gateways),
                'razorpay_mode'     => $mode,
                'razorpay_enabled'  => !empty($razorpay['is_enabled']),
                'total_tx'          => $totalTx,
                'success_tx'        => $successTx,
                'success_rate'      => $successRate,
                'success_amount'    => (int)round((float)($txRow['success_amount'] ?? 0)),
                'online_orders_pct' => $onlinePct,
                'cod_orders_pct'    => 100 - $onlinePct,
            ];
        } catch (Exception $e) {
            error_log("PaymentGateway::getGatewayKPIs error: " . $e->getMessage());
            return [
                'active_gateways'   => 2,
                'total_gateways'    => 3,
                'razorpay_mode'     => 'test',
                'razorpay_enabled'  => true,
                'total_tx'          => 0,
                'success_tx'        => 0,
                'success_rate'      => 100.0,
                'success_amount'    => 0,
                'online_orders_pct' => 85,
                'cod_orders_pct'    => 15,
            ];
        }
    }

    // =========================================================================
    // 9. VALIDATE KEYS
    // =========================================================================

    /**
     * Validate format of Razorpay API credentials.
     */
    public static function validateKeys(string $keyId, string $keySecret): array {
        $cleanKey = trim($keyId);
        $cleanSec = trim($keySecret);

        if ($cleanKey === '') {
            return ['valid' => false, 'mode' => 'invalid', 'message' => 'Key ID cannot be empty.'];
        }

        if (str_starts_with($cleanKey, 'rzp_live_')) {
            $mode = 'live';
        } elseif (str_starts_with($cleanKey, 'rzp_test_')) {
            $mode = 'test';
        } else {
            return [
                'valid'   => false,
                'mode'    => 'invalid',
                'message' => 'Invalid Key ID prefix. Must begin with rzp_test_ or rzp_live_.',
            ];
        }

        if (strlen($cleanKey) < 16) {
            return ['valid' => false, 'mode' => $mode, 'message' => 'Key ID appears too short for a valid Razorpay key.'];
        }

        if ($cleanSec !== '' && !str_starts_with($cleanSec, '••••') && strlen($cleanSec) < 8) {
            return ['valid' => false, 'mode' => $mode, 'message' => 'Key Secret appears too short.'];
        }

        return [
            'valid'   => true,
            'mode'    => $mode,
            'message' => 'Valid ' . ucfirst($mode) . ' credentials format detected.',
        ];
    }

    // =========================================================================
    // 10. ENSURE DEFAULT GATEWAY ROWS IN DB
    // =========================================================================

    /**
     * Guarantee that default gateway configuration records exist in the database.
     */
    public static function ensureDefaults(): void {
        try {
            $db = Database::connect();

            // Default Razorpay
            $checkR = $db->query("SELECT id FROM payment_methods_config WHERE method = 'razorpay'");
            if ($checkR && $checkR->num_rows === 0) {
                $cfg = json_encode([
                    'mode'           => 'test',
                    'key_id'         => 'rzp_test_9JiyajiLuxury2026',
                    'key_secret'     => 'sec_lx9182374650abc',
                    'webhook_secret' => 'whsec_jiyaji99281',
                    'auto_capture'   => 1,
                    'rails'          => ['upi' => true, 'cards' => true, 'netbanking' => true, 'wallets' => true, 'emi' => true],
                ]);
                $db->query("
                    INSERT INTO payment_methods_config (method, title, description, is_enabled, cod_min_order_value, cod_pincode_whitelist, config_data)
                    VALUES ('razorpay', 'Razorpay Secure Online Checkout', 'Instant UPI, Credit/Debit Cards, NetBanking, and EMI payment gateway.', 1, NULL, NULL, '$cfg')
                ");
            }

            // Default COD
            $checkC = $db->query("SELECT id FROM payment_methods_config WHERE method = 'cod'");
            if ($checkC && $checkC->num_rows === 0) {
                $pincodes = "400001, 110001, 560001, 302001, 500001, 700001, 600001, 411001, 380001, 160001, 226001, 452001, 395001, 141001";
                $cfg = json_encode([
                    'max_order_value' => 50000.00,
                    'cod_fee'         => 0.00,
                    'pincode_mode'    => 'all',
                    'otp_verify'      => true,
                ]);
                $db->query("
                    INSERT INTO payment_methods_config (method, title, description, is_enabled, cod_min_order_value, cod_pincode_whitelist, config_data)
                    VALUES ('cod', 'Cash on Delivery (COD)', 'Pay cash at your doorstep upon luxury garment delivery.', 1, 999.00, '$pincodes', '$cfg')
                ");
            }

            // Default Bank Transfer
            $checkB = $db->query("SELECT id FROM payment_methods_config WHERE method = 'bank_transfer'");
            if ($checkB && $checkB->num_rows === 0) {
                $cfg = json_encode([
                    'bank_name'      => 'HDFC Bank',
                    'account_name'   => 'Jiyaji Luxury Collections Pvt Ltd',
                    'account_number' => '50200084920192',
                    'ifsc_code'      => 'HDFC0000123',
                    'branch'         => 'Fort Branch, Mumbai',
                    'upi_id'         => 'jiyajilx@hdfcbank',
                    'instructions'   => 'Please transfer the exact order amount and notify our concierge team with the UTR number.',
                ]);
                $db->query("
                    INSERT INTO payment_methods_config (method, title, description, is_enabled, cod_min_order_value, cod_pincode_whitelist, config_data)
                    VALUES ('bank_transfer', 'VIP Concierge Bank Wire (NEFT / RTGS)', 'Direct bank wire transfer for custom bridal bespoke garments.', 0, NULL, NULL, '$cfg')
                ");
            }
        } catch (Exception $e) {
            error_log("PaymentGateway::ensureDefaults error: " . $e->getMessage());
        }
    }

    /**
     * Upsert a key-value pair in the site_settings table.
     */
    private static function setSiteSetting(string $key, string $value, string $label): void {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, label, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    label = VALUES(label),
                    updated_at = NOW()
            ");
            $stmt->bind_param("sss", $key, $value, $label);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("PaymentGateway::setSiteSetting error: " . $e->getMessage());
        }
    }
}
