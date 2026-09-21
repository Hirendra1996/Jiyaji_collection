<?php

namespace App\Models;

use App\Config\Database;
use Exception;

/**
 * Inventory Model
 *
 * Provides data access methods for the Stock & Replenishment hub,
 * covering variant stock ledgers, movement audit logs, KPI aggregates,
 * and atomic stock update/replenishment operations.
 */
class Inventory {

    /**
     * Return aggregate KPI numbers for the stock dashboard header.
     */
    public static function getKPIs(): array {
        try {
            $db = Database::connect();

            // Total units on hand (sum of all active variant quantities)
            $totalUnitsRes = $db->query("
                SELECT COALESCE(SUM(pv.stock_qty), 0) as total_units
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.is_active = 1 AND p.status = 'active'
            ");
            $totalUnits = (int)($totalUnitsRes ? $totalUnitsRes->fetch_row()[0] : 0);

            // Inventory valuation (stock_qty * effective selling price)
            $valuationRes = $db->query("
                SELECT COALESCE(SUM(pv.stock_qty * COALESCE(pv.price_override, p.sale_price, p.base_price)), 0) as valuation
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.is_active = 1 AND p.status = 'active'
            ");
            $valuation = (float)($valuationRes ? $valuationRes->fetch_row()[0] : 0);

            // Critical: out of stock variants (qty = 0)
            $criticalRes = $db->query("
                SELECT COUNT(*) FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.is_active = 1 AND pv.stock_qty = 0
            ");
            $criticalCount = (int)($criticalRes ? $criticalRes->fetch_row()[0] : 0);

            // Low stock: 0 < qty <= threshold
            $lowRes = $db->query("
                SELECT COUNT(*) FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.is_active = 1 AND pv.stock_qty > 0 AND pv.stock_qty <= p.low_stock_threshold
            ");
            $lowCount = (int)($lowRes ? $lowRes->fetch_row()[0] : 0);

            // Active unresolved stock alerts count
            $alertsRes = $db->query("SELECT COUNT(*) FROM stock_alerts WHERE resolved = 0");
            $activeAlerts = (int)($alertsRes ? $alertsRes->fetch_row()[0] : 0);

            // Units restocked in last 30 days (positive manual_restock movements)
            $restockedRes = $db->query("
                SELECT COALESCE(SUM(movement), 0) FROM stock_movements
                WHERE reason IN ('manual_restock', 'adjustment') AND movement > 0
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $restockedLast30 = (int)($restockedRes ? $restockedRes->fetch_row()[0] : 0);

            return [
                'total_units'       => $totalUnits,
                'valuation'         => $valuation,
                'critical_count'    => $criticalCount,
                'low_stock_count'   => $lowCount,
                'active_alerts'     => $activeAlerts,
                'restocked_30d'     => $restockedLast30,
            ];
        } catch (Exception $e) {
            error_log('Inventory::getKPIs error: ' . $e->getMessage());
            return [
                'total_units'       => 0,
                'valuation'         => 0.0,
                'critical_count'    => 0,
                'low_stock_count'   => 0,
                'active_alerts'     => 0,
                'restocked_30d'     => 0,
            ];
        }
    }

    /**
     * Fetch paginated, filtered list of product variants for the stock ledger.
     *
     * Filters:
     *   - status: all | out_of_stock | low_stock | healthy
     *   - category_id: all | numeric ID
     *   - search: SKU, product name, color, size
     *   - sort: stock_asc | stock_desc | name_asc | sku_asc | newest
     */
    public static function getVariantsLedger(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ['pv.is_active = 1'];
            $params = [];
            $types  = '';

            // Health filter
            $status = $filters['status'] ?? 'all';
            if ($status === 'out_of_stock') {
                $whereClauses[] = 'pv.stock_qty = 0';
            } elseif ($status === 'low_stock') {
                $whereClauses[] = 'pv.stock_qty > 0 AND pv.stock_qty <= p.low_stock_threshold';
            } elseif ($status === 'healthy') {
                $whereClauses[] = 'pv.stock_qty > p.low_stock_threshold';
            }

            // Category filter
            if (!empty($filters['category_id']) && $filters['category_id'] !== 'all' && is_numeric($filters['category_id'])) {
                $whereClauses[] = 'p.category_id = ?';
                $params[]       = (int)$filters['category_id'];
                $types         .= 'i';
            }

            // Search filter
            if (!empty($filters['search'])) {
                $wildcard       = '%' . trim($filters['search']) . '%';
                $whereClauses[] = '(pv.sku LIKE ? OR p.name LIKE ? OR pv.color_name LIKE ? OR pv.size LIKE ?)';
                $params[]       = $wildcard;
                $params[]       = $wildcard;
                $params[]       = $wildcard;
                $params[]       = $wildcard;
                $types         .= 'ssss';
            }

            $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

            // Sort
            $sortSql = match ($filters['sort'] ?? 'stock_asc') {
                'stock_desc' => 'ORDER BY pv.stock_qty DESC',
                'name_asc'   => 'ORDER BY p.name ASC, pv.variant_name ASC',
                'sku_asc'    => 'ORDER BY pv.sku ASC',
                'newest'     => 'ORDER BY pv.id DESC',
                default      => 'ORDER BY pv.stock_qty ASC, p.name ASC',
            };

            // Count query
            $countSql = "
                SELECT COUNT(*) FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                $whereSql
            ";
            if (!empty($params)) {
                $stmt = $db->prepare($countSql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $totalCount = (int)$stmt->get_result()->fetch_row()[0];
            } else {
                $totalCount = (int)$db->query($countSql)->fetch_row()[0];
            }

            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $page       = max(1, min($page, $totalPages));
            $offset     = ($page - 1) * $perPage;

            // Data query
            $dataSql = "
                SELECT
                    pv.id           AS variant_id,
                    pv.sku,
                    pv.variant_name,
                    pv.color_name,
                    pv.color_code,
                    pv.size,
                    pv.price_override,
                    pv.stock_qty,
                    p.id            AS product_id,
                    p.name          AS product_name,
                    p.slug          AS product_slug,
                    p.base_price,
                    p.sale_price,
                    p.low_stock_threshold,
                    p.allow_backorder,
                    p.status        AS product_status,
                    c.name          AS category_name,
                    sa.id           AS alert_id,
                    sa.resolved     AS alert_resolved,
                    sa.alerted_at,
                    (SELECT pi.url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS cover_image
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN stock_alerts sa ON sa.variant_id = pv.id
                $whereSql
                $sortSql
                LIMIT ?, ?
            ";

            $bindParams = $params;
            $bindParams[] = $offset;
            $bindParams[] = $perPage;
            $bindTypes = $types . 'ii';

            $stmt = $db->prepare($dataSql);
            $stmt->bind_param($bindTypes, ...$bindParams);
            $stmt->execute();
            $res = $stmt->get_result();

            $variants = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_product_id'] = encrypt_id($row['product_id']);
                $row['encrypted_variant_id'] = encrypt_id($row['variant_id']);
                $variants[] = $row;
            }

            return [
                'variants'   => $variants,
                'pagination' => [
                    'current_page'  => $page,
                    'per_page'      => $perPage,
                    'total_records' => $totalCount,
                    'total_pages'   => $totalPages,
                    'has_prev'      => ($page > 1),
                    'has_next'      => ($page < $totalPages),
                ],
            ];
        } catch (Exception $e) {
            error_log('Inventory::getVariantsLedger error: ' . $e->getMessage());
            return [
                'variants'   => [],
                'pagination' => [
                    'current_page'  => 1,
                    'per_page'      => $perPage,
                    'total_records' => 0,
                    'total_pages'   => 1,
                    'has_prev'      => false,
                    'has_next'      => false,
                ],
            ];
        }
    }

    /**
     * Fetch paginated stock movement audit log.
     *
     * Filters:
     *   - reason: all | manual_restock | sale | return | adjustment | cancellation
     *   - search: SKU, product name
     *   - date_from / date_to: YYYY-MM-DD strings
     */
    public static function getMovements(array $filters = [], int $page = 1, int $perPage = 20): array {
        try {
            $db = Database::connect();
            $whereClauses = ['1 = 1'];
            $params = [];
            $types  = '';

            // Reason filter
            $validReasons = ['manual_restock', 'sale', 'return', 'adjustment', 'cancellation'];
            if (!empty($filters['reason']) && in_array($filters['reason'], $validReasons, true)) {
                $whereClauses[] = 'sm.reason = ?';
                $params[]       = $filters['reason'];
                $types         .= 's';
            }

            // Direction filter (positive / negative)
            if (!empty($filters['direction'])) {
                if ($filters['direction'] === 'in') {
                    $whereClauses[] = 'sm.movement > 0';
                } elseif ($filters['direction'] === 'out') {
                    $whereClauses[] = 'sm.movement < 0';
                }
            }

            // Keyword search
            if (!empty($filters['search'])) {
                $wildcard       = '%' . trim($filters['search']) . '%';
                $whereClauses[] = '(pv.sku LIKE ? OR p.name LIKE ?)';
                $params[]       = $wildcard;
                $params[]       = $wildcard;
                $types         .= 'ss';
            }

            // Date range
            if (!empty($filters['date_from'])) {
                $whereClauses[] = 'DATE(sm.created_at) >= ?';
                $params[]       = $filters['date_from'];
                $types         .= 's';
            }
            if (!empty($filters['date_to'])) {
                $whereClauses[] = 'DATE(sm.created_at) <= ?';
                $params[]       = $filters['date_to'];
                $types         .= 's';
            }

            $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

            // Count
            $countSql = "
                SELECT COUNT(*) FROM stock_movements sm
                JOIN product_variants pv ON sm.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                $whereSql
            ";
            if (!empty($params)) {
                $stmt = $db->prepare($countSql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $totalCount = (int)$stmt->get_result()->fetch_row()[0];
            } else {
                $totalCount = (int)$db->query($countSql)->fetch_row()[0];
            }

            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $page       = max(1, min($page, $totalPages));
            $offset     = ($page - 1) * $perPage;

            $dataSql = "
                SELECT
                    sm.id           AS movement_id,
                    sm.movement,
                    sm.reason,
                    sm.reference_id,
                    sm.note,
                    sm.created_at,
                    pv.id           AS variant_id,
                    pv.sku,
                    pv.variant_name,
                    pv.color_name,
                    pv.color_code,
                    pv.size,
                    pv.stock_qty    AS current_stock,
                    p.id            AS product_id,
                    p.name          AS product_name,
                    p.low_stock_threshold,
                    a.id            AS staff_id,
                    a.name          AS staff_name
                FROM stock_movements sm
                JOIN product_variants pv ON sm.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                LEFT JOIN admins a ON sm.created_by = a.id
                $whereSql
                ORDER BY sm.created_at DESC, sm.id DESC
                LIMIT ?, ?
            ";

            $bindParams = $params;
            $bindParams[] = $offset;
            $bindParams[] = $perPage;
            $bindTypes = $types . 'ii';

            $stmt = $db->prepare($dataSql);
            $stmt->bind_param($bindTypes, ...$bindParams);
            $stmt->execute();
            $res = $stmt->get_result();

            $movements = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_product_id'] = encrypt_id($row['product_id']);
                $movements[] = $row;
            }

            return [
                'movements'  => $movements,
                'pagination' => [
                    'current_page'  => $page,
                    'per_page'      => $perPage,
                    'total_records' => $totalCount,
                    'total_pages'   => $totalPages,
                    'has_prev'      => ($page > 1),
                    'has_next'      => ($page < $totalPages),
                ],
            ];
        } catch (Exception $e) {
            error_log('Inventory::getMovements error: ' . $e->getMessage());
            return [
                'movements'  => [],
                'pagination' => [
                    'current_page'  => 1,
                    'per_page'      => $perPage,
                    'total_records' => 0,
                    'total_pages'   => 1,
                    'has_prev'      => false,
                    'has_next'      => false,
                ],
            ];
        }
    }

    /**
     * Atomically record a stock movement:
     * - Updates product_variants.stock_qty
     * - Inserts a stock_movements audit row
     * - Syncs stock_alerts (raises or resolves)
     *
     * @param  int         $variantId   Product variant primary key
     * @param  int         $delta       Units to add (positive) or remove (negative)
     * @param  string      $reason      Movement reason enum
     * @param  string|null $note        Optional staff note / memo
     * @param  int|null    $referenceId Optional reference (order ID, PO number, etc.)
     * @param  int|null    $createdBy   Staff admin ID performing the movement
     * @return bool
     */
    public static function recordMovement(
        int $variantId,
        int $delta,
        string $reason = 'manual_restock',
        ?string $note = null,
        ?int $referenceId = null,
        ?int $createdBy = null
    ): bool {
        if ($delta === 0) {
            return true;
        }

        $validReasons = ['sale', 'return', 'manual_restock', 'cancellation', 'adjustment'];
        if (!in_array($reason, $validReasons, true)) {
            $reason = 'adjustment';
        }

        try {
            $db = Database::connect();

            // Fetch current stock and threshold
            $stmt = $db->prepare("
                SELECT pv.stock_qty, p.low_stock_threshold
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.id = ?
            ");
            $stmt->bind_param('i', $variantId);
            $stmt->execute();
            $current = $stmt->get_result()->fetch_assoc();

            if (!$current) {
                return false;
            }

            // Compute new quantity, floor at 0
            $newQty = max(0, (int)$current['stock_qty'] + $delta);
            $threshold = (int)$current['low_stock_threshold'];

            // Update stock_qty on variant
            $upStmt = $db->prepare("UPDATE product_variants SET stock_qty = ? WHERE id = ?");
            $upStmt->bind_param('ii', $newQty, $variantId);
            $upStmt->execute();

            // Record movement in audit log
            $movStmt = $db->prepare("
                INSERT INTO stock_movements (variant_id, movement, reason, reference_id, note, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $movStmt->bind_param('iisiis', $variantId, $delta, $reason, $referenceId, $note, $createdBy);
            $movStmt->execute();

            // Sync stock_alerts
            if ($newQty <= $threshold) {
                $db->query("
                    INSERT INTO stock_alerts (variant_id, alerted_at, resolved)
                    VALUES ($variantId, NOW(), 0)
                    ON DUPLICATE KEY UPDATE alerted_at = NOW(), resolved = 0
                ");
            } else {
                $db->query("UPDATE stock_alerts SET resolved = 1 WHERE variant_id = $variantId");
            }

            return true;
        } catch (Exception $e) {
            error_log('Inventory::recordMovement error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch process multiple replenishment movements in a single DB transaction.
     *
     * @param array $replenishments  Array of ['variant_id' => int, 'qty' => int] pairs
     * @param string $reason         Movement reason (defaults to manual_restock)
     * @param string|null $note      Batch note / PO reference
     * @param int|null $createdBy    Staff admin ID
     * @return array ['success' => count, 'failed' => count]
     */
    public static function batchReplenish(
        array $replenishments,
        string $reason = 'manual_restock',
        ?string $note = null,
        ?int $createdBy = null
    ): array {
        $success = 0;
        $failed  = 0;

        try {
            $db = Database::connect();
            $db->begin_transaction();

            foreach ($replenishments as $item) {
                $variantId = (int)($item['variant_id'] ?? 0);
                $qty       = (int)($item['qty'] ?? 0);

                if ($variantId <= 0 || $qty <= 0) {
                    $failed++;
                    continue;
                }

                if (self::recordMovement($variantId, $qty, $reason, $note, null, $createdBy)) {
                    $success++;
                } else {
                    $failed++;
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            error_log('Inventory::batchReplenish error: ' . $e->getMessage());
            $failed += count($replenishments) - $success;
        }

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * Return all variants at or below low_stock_threshold for bulk reorder suggestions.
     */
    public static function getLowStockCandidates(): array {
        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT
                    pv.id AS variant_id,
                    pv.sku,
                    pv.variant_name,
                    pv.color_name,
                    pv.size,
                    pv.stock_qty,
                    p.id AS product_id,
                    p.name AS product_name,
                    p.low_stock_threshold
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.is_active = 1 AND pv.stock_qty <= p.low_stock_threshold
                ORDER BY pv.stock_qty ASC, p.name ASC
                LIMIT 200
            ");

            $candidates = [];
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $row['encrypted_variant_id'] = encrypt_id($row['variant_id']);
                    $row['encrypted_product_id'] = encrypt_id($row['product_id']);
                    $row['suggested_restock']    = max(10, $row['low_stock_threshold'] * 3 - $row['stock_qty']);
                    $candidates[] = $row;
                }
            }
            return $candidates;
        } catch (Exception $e) {
            error_log('Inventory::getLowStockCandidates error: ' . $e->getMessage());
            return [];
        }
    }
}
