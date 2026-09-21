<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Coupon {

    // =========================================================================
    // LISTING & PAGINATION
    // =========================================================================

    /**
     * Retrieve paginated, filtered list of coupons with usage stats.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types  = "";

            // Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'active') {
                    $whereClauses[] = "c.is_active = 1 AND (c.expires_at IS NULL OR c.expires_at > NOW()) AND (c.usage_limit_global IS NULL OR c.times_used < c.usage_limit_global)";
                } elseif ($filters['status'] === 'expired') {
                    $whereClauses[] = "c.expires_at IS NOT NULL AND c.expires_at <= NOW()";
                } elseif ($filters['status'] === 'disabled') {
                    $whereClauses[] = "c.is_active = 0";
                } elseif ($filters['status'] === 'exhausted') {
                    $whereClauses[] = "c.usage_limit_global IS NOT NULL AND c.times_used >= c.usage_limit_global";
                }
            }

            // Type filter
            if (!empty($filters['type']) && $filters['type'] !== 'all') {
                $whereClauses[] = "c.type = ?";
                $params[] = $filters['type'];
                $types   .= "s";
            }

            // Search (code or description)
            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(c.code LIKE ? OR c.description LIKE ?)";
                $params[] = $wild;
                $params[] = $wild;
                $types   .= "ss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            $sortSql = "ORDER BY c.created_at DESC, c.id DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'newest'      => "ORDER BY c.created_at DESC, c.id DESC",
                    'oldest'      => "ORDER BY c.created_at ASC, c.id ASC",
                    'most_used'   => "ORDER BY c.times_used DESC, c.created_at DESC",
                    'expiring'    => "ORDER BY c.expires_at ASC, c.id DESC",
                    default       => "ORDER BY c.created_at DESC, c.id DESC"
                };
            }

            // Count
            $countSql = "SELECT COUNT(*) as total FROM coupons c WHERE $whereSql";
            $stmtCount = $db->prepare($countSql);
            if (!empty($params)) {
                $stmtCount->bind_param($types, ...$params);
            }
            $stmtCount->execute();
            $total = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page       = max(1, min($page, $totalPages));
            $offset     = ($page - 1) * $perPage;

            $selectSql = "
                SELECT
                    c.*,
                    a.name AS created_by_name,
                    CASE
                        WHEN c.is_active = 0 THEN 'disabled'
                        WHEN c.expires_at IS NOT NULL AND c.expires_at <= NOW() THEN 'expired'
                        WHEN c.usage_limit_global IS NOT NULL AND c.times_used >= c.usage_limit_global THEN 'exhausted'
                        ELSE 'active'
                    END AS computed_status
                FROM coupons c
                LEFT JOIN admins a ON c.created_by = a.id
                WHERE $whereSql
                $sortSql
                LIMIT ? OFFSET ?
            ";

            $pagParams   = array_merge($params, [$perPage, $offset]);
            $pagTypes    = $types . "ii";

            $stmt = $db->prepare($selectSql);
            $stmt->bind_param($pagTypes, ...$pagParams);
            $stmt->execute();
            $coupons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return [
                'coupons'    => $coupons,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages,
                ],
            ];
        } catch (Exception $e) {
            error_log("Coupon::getAll error: " . $e->getMessage());
            return ['coupons' => [], 'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false]];
        }
    }

    // =========================================================================
    // KPIS / SUMMARY STATS
    // =========================================================================

    public static function getKPIs(): array {
        try {
            $db = Database::connect();
            $row = $db->query("
                SELECT
                    COUNT(*) AS total_coupons,
                    SUM(CASE WHEN is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (usage_limit_global IS NULL OR times_used < usage_limit_global) THEN 1 ELSE 0 END) AS active_count,
                    SUM(CASE WHEN expires_at IS NOT NULL AND expires_at <= NOW() THEN 1 ELSE 0 END) AS expired_count,
                    SUM(CASE WHEN expires_at IS NOT NULL AND expires_at > NOW() AND expires_at <= DATE_ADD(NOW(), INTERVAL 7 DAY) AND is_active = 1 THEN 1 ELSE 0 END) AS expiring_soon,
                    SUM(times_used) AS total_redemptions
                FROM coupons
            ")->fetch_assoc();

            $savings = $db->query("
                SELECT COALESCE(SUM(discount_amount), 0) AS total_savings
                FROM orders
                WHERE coupon_id IS NOT NULL AND payment_status IN ('paid','cod_pending')
            ")->fetch_assoc();

            return [
                'total_coupons'    => (int)($row['total_coupons']    ?? 0),
                'active_count'     => (int)($row['active_count']     ?? 0),
                'expired_count'    => (int)($row['expired_count']    ?? 0),
                'expiring_soon'    => (int)($row['expiring_soon']    ?? 0),
                'total_redemptions'=> (int)($row['total_redemptions']?? 0),
                'total_savings'    => (float)($savings['total_savings'] ?? 0),
            ];
        } catch (Exception $e) {
            error_log("Coupon::getKPIs error: " . $e->getMessage());
            return ['total_coupons' => 0, 'active_count' => 0, 'expired_count' => 0, 'expiring_soon' => 0, 'total_redemptions' => 0, 'total_savings' => 0];
        }
    }

    // =========================================================================
    // FIND / SHOW
    // =========================================================================

    public static function find(int $id): ?array {
        try {
            $db   = Database::connect();
            $stmt = $db->prepare("
                SELECT
                    c.*,
                    a.name AS created_by_name,
                    CASE
                        WHEN c.is_active = 0 THEN 'disabled'
                        WHEN c.expires_at IS NOT NULL AND c.expires_at <= NOW() THEN 'expired'
                        WHEN c.usage_limit_global IS NOT NULL AND c.times_used >= c.usage_limit_global THEN 'exhausted'
                        ELSE 'active'
                    END AS computed_status
                FROM coupons c
                LEFT JOIN admins a ON c.created_by = a.id
                WHERE c.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $coupon = $stmt->get_result()->fetch_assoc();
            if (!$coupon) return null;

            // Fetch category restrictions
            $stmtCat = $db->prepare("
                SELECT ccr.category_id, cat.name AS category_name
                FROM coupon_category_restrictions ccr
                JOIN categories cat ON ccr.category_id = cat.id
                WHERE ccr.coupon_id = ?
            ");
            $stmtCat->bind_param("i", $id);
            $stmtCat->execute();
            $coupon['category_restrictions'] = $stmtCat->get_result()->fetch_all(MYSQLI_ASSOC);

            // Fetch product restrictions
            $stmtProd = $db->prepare("
                SELECT cpr.product_id, p.name AS product_name
                FROM coupon_product_restrictions cpr
                JOIN products p ON cpr.product_id = p.id
                WHERE cpr.coupon_id = ?
            ");
            $stmtProd->bind_param("i", $id);
            $stmtProd->execute();
            $coupon['product_restrictions'] = $stmtProd->get_result()->fetch_all(MYSQLI_ASSOC);

            return $coupon;
        } catch (Exception $e) {
            error_log("Coupon::find error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // USAGE HISTORY
    // =========================================================================

    public static function getUsageHistory(int $couponId, int $limit = 20): array {
        try {
            $db   = Database::connect();
            $stmt = $db->prepare("
                SELECT
                    cu.id, cu.used_at,
                    cust.name AS customer_name, cust.email AS customer_email,
                    o.order_number, o.grand_total AS total_amount, o.discount_amount
                FROM coupon_usages cu
                JOIN customers cust ON cu.customer_id = cust.id
                LEFT JOIN orders o ON cu.order_id = o.id
                WHERE cu.coupon_id = ?
                ORDER BY cu.used_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $couponId, $limit);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Coupon::getUsageHistory error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public static function create(array $data): int|false {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO coupons
                    (code, description, type, value, max_discount_cap, min_cart_value,
                     usage_limit_global, usage_limit_per_user, is_public, starts_at, expires_at, is_active, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $code              = strtoupper(trim($data['code']));
            $description       = $data['description'] ?? null;
            $type              = $data['type'];
            $value             = (float)$data['value'];
            $maxCap            = !empty($data['max_discount_cap'])   ? (float)$data['max_discount_cap']   : null;
            $minCart           = !empty($data['min_cart_value'])     ? (float)$data['min_cart_value']     : 0.0;
            $usageLimitGlobal  = !empty($data['usage_limit_global']) ? (int)$data['usage_limit_global']   : null;
            $usageLimitPerUser = !empty($data['usage_limit_per_user'])? (int)$data['usage_limit_per_user']: 1;
            $isPublic          = isset($data['is_public']) ? 1 : 0;
            $startsAt          = !empty($data['starts_at'])  ? $data['starts_at']  : null;
            $expiresAt         = !empty($data['expires_at']) ? $data['expires_at'] : null;
            $isActive          = 1;
            $createdBy         = (int)($data['created_by'] ?? 0) ?: null;

            $stmt->bind_param(
                "sssdddiiiissi",
                $code, $description, $type, $value, $maxCap, $minCart,
                $usageLimitGlobal, $usageLimitPerUser, $isPublic, $startsAt, $expiresAt, $isActive, $createdBy
            );
            $stmt->execute();
            return $db->insert_id ?: false;
        } catch (Exception $e) {
            error_log("Coupon::create error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public static function update(int $id, array $data): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                UPDATE coupons SET
                    code = ?, description = ?, type = ?, value = ?,
                    max_discount_cap = ?, min_cart_value = ?,
                    usage_limit_global = ?, usage_limit_per_user = ?,
                    is_public = ?, starts_at = ?, expires_at = ?
                WHERE id = ?
            ");

            $code              = strtoupper(trim($data['code']));
            $description       = $data['description'] ?? null;
            $type              = $data['type'];
            $value             = (float)$data['value'];
            $maxCap            = !empty($data['max_discount_cap'])   ? (float)$data['max_discount_cap']   : null;
            $minCart           = !empty($data['min_cart_value'])     ? (float)$data['min_cart_value']     : 0.0;
            $usageLimitGlobal  = !empty($data['usage_limit_global']) ? (int)$data['usage_limit_global']   : null;
            $usageLimitPerUser = !empty($data['usage_limit_per_user'])? (int)$data['usage_limit_per_user']: 1;
            $isPublic          = isset($data['is_public']) ? 1 : 0;
            $startsAt          = !empty($data['starts_at'])  ? $data['starts_at']  : null;
            $expiresAt         = !empty($data['expires_at']) ? $data['expires_at'] : null;

            $stmt->bind_param(
                "sssdddiiissi",
                $code, $description, $type, $value, $maxCap, $minCart,
                $usageLimitGlobal, $usageLimitPerUser, $isPublic, $startsAt, $expiresAt, $id
            );
            $stmt->execute();
            return $stmt->affected_rows >= 0;
        } catch (Exception $e) {
            error_log("Coupon::update error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // TOGGLE STATUS
    // =========================================================================

    public static function toggleStatus(int $id): ?bool {
        try {
            $db   = Database::connect();
            $stmt = $db->prepare("UPDATE coupons SET is_active = NOT is_active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            if ($stmt->affected_rows === 0) return null;
            $newStatus = $db->query("SELECT is_active FROM coupons WHERE id = $id")->fetch_assoc()['is_active'];
            return (bool)$newStatus;
        } catch (Exception $e) {
            error_log("Coupon::toggleStatus error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public static function delete(int $id): bool {
        try {
            $db   = Database::connect();
            // Check usage – block deletion if coupon was used in real orders
            $usageCheck = $db->prepare("SELECT COUNT(*) as cnt FROM coupon_usages WHERE coupon_id = ? AND order_id IS NOT NULL");
            $usageCheck->bind_param("i", $id);
            $usageCheck->execute();
            $usageCount = (int)($usageCheck->get_result()->fetch_assoc()['cnt'] ?? 0);
            if ($usageCount > 0) {
                return false; // Cannot delete – has real order history
            }

            // Delete restrictions first (FK)
            $db->query("DELETE FROM coupon_category_restrictions WHERE coupon_id = $id");
            $db->query("DELETE FROM coupon_product_restrictions WHERE coupon_id = $id");
            $db->query("DELETE FROM coupon_usages WHERE coupon_id = $id");

            $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Coupon::delete error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // CODE UNIQUENESS CHECK
    // =========================================================================

    public static function codeExists(string $code, ?int $excludeId = null): bool {
        try {
            $db   = Database::connect();
            $code = strtoupper(trim($code));
            if ($excludeId) {
                $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
                $stmt->bind_param("si", $code, $excludeId);
            } else {
                $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ?");
                $stmt->bind_param("s", $code);
            }
            $stmt->execute();
            return (bool)$stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Coupon::codeExists error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public static function generateCode(string $prefix = 'JIYAJI'): string {
        return strtoupper($prefix) . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    public static function getCategories(): array {
        try {
            $db = Database::connect();
            return $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function getProducts(): array {
        try {
            $db = Database::connect();
            return $db->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
