<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Customer {
    /**
     * Retrieve paginated and filtered list of customers with dynamic lifetime statistics.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Account status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'active') {
                    $whereClauses[] = "c.is_active = 1";
                } elseif ($filters['status'] === 'inactive') {
                    $whereClauses[] = "c.is_active = 0";
                }
            }

            // Verification status filter
            if (!empty($filters['verification']) && $filters['verification'] !== 'all') {
                if ($filters['verification'] === 'verified') {
                    $whereClauses[] = "c.email_verified = 1";
                } elseif ($filters['verification'] === 'unverified') {
                    $whereClauses[] = "c.email_verified = 0";
                }
            }

            // Buyer frequency tier filter
            $havingClauses = [];
            if (!empty($filters['orders_filter']) && $filters['orders_filter'] !== 'all') {
                if ($filters['orders_filter'] === 'buyers') {
                    $havingClauses[] = "order_count > 0";
                } elseif ($filters['orders_filter'] === 'repeat') {
                    $havingClauses[] = "order_count > 1";
                } elseif ($filters['orders_filter'] === 'no_orders') {
                    $havingClauses[] = "order_count = 0";
                }
            }

            // Search query (Name, Email, Phone)
            if (!empty($filters['search'])) {
                $searchWildcard = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
                $params[] = $searchWildcard;
                $params[] = $searchWildcard;
                $params[] = $searchWildcard;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $whereClauses);
            $havingSql = !empty($havingClauses) ? "HAVING " . implode(" AND ", $havingClauses) : "";

            // Sorting options
            $sortSql = "ORDER BY c.id DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'newest'      => "ORDER BY c.id DESC",
                    'oldest'      => "ORDER BY c.id ASC",
                    'name_asc'    => "ORDER BY c.name ASC",
                    'name_desc'   => "ORDER BY c.name DESC",
                    'spend_high'  => "ORDER BY total_spent DESC, c.id DESC",
                    'orders_high' => "ORDER BY order_count DESC, c.id DESC",
                    default       => "ORDER BY c.id DESC"
                };
            }

            // Count total query
            $countSql = "
                SELECT COUNT(*) as total FROM (
                    SELECT c.id,
                        (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) as order_count
                    FROM customers c
                    WHERE $whereSql
                    $havingSql
                ) as customer_counts
            ";
            
            $stmtCount = $db->prepare($countSql);
            if (!empty($params)) {
                $stmtCount->bind_param($types, ...$params);
            }
            $stmtCount->execute();
            $totalCustomers = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);

            // Pagination calculations
            $totalPages = max(1, (int)ceil($totalCustomers / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Main select query
            $selectSql = "
                SELECT 
                    c.id,
                    c.name,
                    c.email,
                    c.phone,
                    c.email_verified,
                    c.is_active,
                    c.profile_photo_url,
                    c.created_at,
                    c.updated_at,
                    (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) AS order_count,
                    (SELECT COALESCE(SUM(grand_total), 0) FROM orders o WHERE o.customer_id = c.id AND o.status != 'cancelled') AS total_spent,
                    (SELECT MAX(placed_at) FROM orders o WHERE o.customer_id = c.id) AS last_order_at
                FROM customers c
                WHERE $whereSql
                $havingSql
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

            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $row['total_spent'] = (int)round((float)$row['total_spent']);
                $row['order_count'] = (int)$row['order_count'];
                $customers[] = $row;
            }

            return [
                'customers'  => $customers,
                'pagination' => [
                    'total_items'  => $totalCustomers,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'offset'       => $offset,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Customer::getAll error: " . $e->getMessage());
            return [
                'customers'  => [],
                'pagination' => [
                    'total_items'  => 0,
                    'per_page'     => $perPage,
                    'current_page' => 1,
                    'total_pages'  => 1,
                    'offset'       => 0,
                    'has_prev'     => false,
                    'has_next'     => false
                ]
            ];
        }
    }

    /**
     * Retrieve executive KPI summary for customers.
     */
    public static function getKPIs(): array {
        $kpis = [
            'total_customers'    => 0,
            'active_customers'   => 0,
            'verified_customers' => 0,
            'repeat_buyers'      => 0,
            'total_revenue'      => 0.00
        ];

        try {
            $db = Database::connect();

            // Total, active, and verified counts
            $res = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as verified
                FROM customers
            ");
            if ($res && $row = $res->fetch_assoc()) {
                $kpis['total_customers']    = (int)($row['total'] ?? 0);
                $kpis['active_customers']   = (int)($row['active'] ?? 0);
                $kpis['verified_customers'] = (int)($row['verified'] ?? 0);
            }

            // Repeat buyers (customers with >= 2 orders)
            $resRepeat = $db->query("
                SELECT COUNT(*) as repeat_count FROM (
                    SELECT customer_id FROM orders 
                    WHERE customer_id IS NOT NULL 
                    GROUP BY customer_id 
                    HAVING COUNT(*) >= 2
                ) as repeat_customers
            ");
            if ($resRepeat && $rowRepeat = $resRepeat->fetch_assoc()) {
                $kpis['repeat_buyers'] = (int)($rowRepeat['repeat_count'] ?? 0);
            }

            // Total revenue contributed by registered customers
            $resRev = $db->query("
                SELECT COALESCE(SUM(grand_total), 0) as total_rev 
                FROM orders 
                WHERE customer_id IS NOT NULL AND status != 'cancelled'
            ");
            if ($resRev && $rowRev = $resRev->fetch_assoc()) {
                $kpis['total_revenue'] = (int)round((float)($rowRev['total_rev'] ?? 0));
            }
        } catch (Exception $e) {
            error_log("Customer::getKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Find single customer by internal database ID with full lifetime metrics and orders.
     */
    public static function find(int $id): ?array {
        try {
            $db = Database::connect();

            $stmt = $db->prepare("
                SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) AS order_count,
                    (SELECT COALESCE(SUM(grand_total), 0) FROM orders o WHERE o.customer_id = c.id AND o.status != 'cancelled') AS total_spent,
                    (SELECT MAX(placed_at) FROM orders o WHERE o.customer_id = c.id) AS last_order_at,
                    (SELECT MIN(placed_at) FROM orders o WHERE o.customer_id = c.id) AS first_order_at
                FROM customers c
                WHERE c.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $customer = $res->fetch_assoc();

            if (!$customer) {
                return null;
            }

            $customer['encrypted_id'] = encrypt_id($customer['id']);
            $customer['order_count'] = (int)$customer['order_count'];
            $customer['total_spent'] = (int)round((float)$customer['total_spent']);
            $customer['avg_order_value'] = $customer['order_count'] > 0 
                ? (int)round($customer['total_spent'] / $customer['order_count']) 
                : 0;

            // Fetch customer orders history
            $customer['orders'] = self::getOrders($id);

            // Fetch customer addresses
            $customer['addresses'] = self::getAddresses($id);

            return $customer;
        } catch (Exception $e) {
            error_log("Customer::find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find customer by email (for uniqueness checks).
     */
    public static function findByEmail(string $email, ?int $excludeId = null): ?array {
        try {
            $db = Database::connect();
            $sql = "SELECT id, email FROM customers WHERE email = ?";
            if ($excludeId) {
                $sql .= " AND id != ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("si", $email, $excludeId);
            } else {
                $stmt = $db->prepare($sql);
                $stmt->bind_param("s", $email);
            }
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc() ?: null;
        } catch (Exception $e) {
            error_log("Customer::findByEmail error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new customer record.
     */
    public static function create(array $data): int {
        try {
            $db = Database::connect();

            $name = trim($data['name'] ?? '');
            $email = strtolower(trim($data['email'] ?? ''));
            $phone = trim($data['phone'] ?? '');
            $rawPassword = $data['password'] ?? 'Welcome@123';
            $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
            $emailVerified = !empty($data['email_verified']) ? 1 : 0;
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            $profilePhoto = !empty($data['profile_photo_url']) ? trim($data['profile_photo_url']) : null;

            $stmt = $db->prepare("
                INSERT INTO customers (name, email, phone, password_hash, email_verified, is_active, profile_photo_url, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssssiis", $name, $email, $phone, $passwordHash, $emailVerified, $isActive, $profilePhoto);
            $stmt->execute();

            $newId = (int)$db->insert_id;

            // If initial address provided, save it
            if ($newId > 0 && !empty($data['address_line1']) && !empty($data['city'])) {
                self::addAddress($newId, [
                    'label'         => $data['address_label'] ?? 'Home',
                    'recipient'     => !empty($data['address_recipient']) ? $data['address_recipient'] : $name,
                    'phone'         => !empty($data['address_phone']) ? $data['address_phone'] : $phone,
                    'address_line1' => $data['address_line1'],
                    'address_line2' => $data['address_line2'] ?? null,
                    'city'          => $data['city'],
                    'state'         => $data['state'] ?? '',
                    'pincode'       => $data['pincode'] ?? '',
                    'country'       => $data['country'] ?? 'India',
                    'is_default'    => 1
                ]);
            }

            return $newId;
        } catch (Exception $e) {
            error_log("Customer::create error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update customer profile details.
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::connect();

            $name = trim($data['name'] ?? '');
            $email = strtolower(trim($data['email'] ?? ''));
            $phone = trim($data['phone'] ?? '');
            $emailVerified = !empty($data['email_verified']) ? 1 : 0;
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            if (!empty($data['password'])) {
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE customers 
                    SET name = ?, email = ?, phone = ?, email_verified = ?, is_active = ?, password_hash = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("sssiisi", $name, $email, $phone, $emailVerified, $isActive, $passwordHash, $id);
            } else {
                $stmt = $db->prepare("
                    UPDATE customers 
                    SET name = ?, email = ?, phone = ?, email_verified = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("sssiii", $name, $email, $phone, $emailVerified, $isActive, $id);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Customer::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle customer active/inactive status.
     */
    public static function toggleStatus(int $id): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE customers SET is_active = 1 - is_active, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Customer::toggleStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Safeguarded customer deletion.
     * Prevents deletion if customer has linked orders to preserve audit trail.
     */
    public static function delete(int $id): array {
        try {
            $db = Database::connect();

            // Check if customer has orders
            $stmtOrder = $db->prepare("SELECT COUNT(*) as cnt FROM orders WHERE customer_id = ?");
            $stmtOrder->bind_param("i", $id);
            $stmtOrder->execute();
            $orderCount = (int)($stmtOrder->get_result()->fetch_assoc()['cnt'] ?? 0);

            if ($orderCount > 0) {
                return [
                    'success' => false,
                    'message' => "Customer has {$orderCount} linked order(s). Customer cannot be permanently removed to protect order and accounting ledgers. Please deactivate/suspend the account instead."
                ];
            }

            // Delete addresses first
            $stmtAddr = $db->prepare("DELETE FROM customer_addresses WHERE customer_id = ?");
            $stmtAddr->bind_param("i", $id);
            $stmtAddr->execute();

            // Delete customer
            $stmtCust = $db->prepare("DELETE FROM customers WHERE id = ?");
            $stmtCust->bind_param("i", $id);
            $stmtCust->execute();

            return [
                'success' => true,
                'message' => 'Customer profile successfully removed.'
            ];
        } catch (Exception $e) {
            error_log("Customer::delete error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting customer: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve order history list for customer.
     */
    public static function getOrders(int $customerId, int $limit = 50): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT 
                    o.id,
                    o.order_number,
                    o.status,
                    o.payment_status,
                    o.payment_method,
                    o.grand_total,
                    o.placed_at,
                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
                FROM orders o
                WHERE o.customer_id = ?
                ORDER BY o.placed_at DESC, o.id DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $customerId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $row['grand_total'] = (int)round((float)$row['grand_total']);
                $row['item_count'] = (int)$row['item_count'];
                $orders[] = $row;
            }
            return $orders;
        } catch (Exception $e) {
            error_log("Customer::getOrders error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve address book for a customer.
     */
    public static function getAddresses(int $customerId): array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT * FROM customer_addresses
                WHERE customer_id = ?
                ORDER BY is_default DESC, id DESC
            ");
            $stmt->bind_param("i", $customerId);
            $stmt->execute();
            $result = $stmt->get_result();

            $addresses = [];
            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $addresses[] = $row;
            }
            return $addresses;
        } catch (Exception $e) {
            error_log("Customer::getAddresses error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add address to customer address book.
     */
    public static function addAddress(int $customerId, array $data): int {
        try {
            $db = Database::connect();

            $label = !empty($data['label']) ? trim($data['label']) : 'Home';
            $recipient = trim($data['recipient'] ?? '');
            $phone = trim($data['phone'] ?? '');
            $line1 = trim($data['address_line1'] ?? '');
            $line2 = !empty($data['address_line2']) ? trim($data['address_line2']) : null;
            $city = trim($data['city'] ?? '');
            $state = trim($data['state'] ?? '');
            $pincode = trim($data['pincode'] ?? '');
            $country = !empty($data['country']) ? trim($data['country']) : 'India';
            $isDefault = !empty($data['is_default']) ? 1 : 0;

            // If this is default or the first address, clear other defaults
            if ($isDefault) {
                $db->query("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = " . (int)$customerId);
            } else {
                // If customer has no existing addresses, make this default
                $check = $db->query("SELECT COUNT(*) as cnt FROM customer_addresses WHERE customer_id = " . (int)$customerId);
                if ($check && ($check->fetch_assoc()['cnt'] ?? 0) == 0) {
                    $isDefault = 1;
                }
            }

            $stmt = $db->prepare("
                INSERT INTO customer_addresses (customer_id, label, recipient, phone, address_line1, address_line2, city, state, pincode, country, is_default, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("isssssssssi", $customerId, $label, $recipient, $phone, $line1, $line2, $city, $state, $pincode, $country, $isDefault);
            $stmt->execute();

            return (int)$db->insert_id;
        } catch (Exception $e) {
            error_log("Customer::addAddress error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete an address from customer address book.
     */
    public static function deleteAddress(int $addressId, int $customerId): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?");
            $stmt->bind_param("ii", $addressId, $customerId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Customer::deleteAddress error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set default address for customer.
     */
    public static function setDefaultAddress(int $addressId, int $customerId): bool {
        try {
            $db = Database::connect();
            $db->begin_transaction();

            // Clear defaults
            $stmtReset = $db->prepare("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?");
            $stmtReset->bind_param("i", $customerId);
            $stmtReset->execute();

            // Set new default
            $stmtSet = $db->prepare("UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND customer_id = ?");
            $stmtSet->bind_param("ii", $addressId, $customerId);
            $stmtSet->execute();

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Customer::setDefaultAddress error: " . $e->getMessage());
            return false;
        }
    }
}
