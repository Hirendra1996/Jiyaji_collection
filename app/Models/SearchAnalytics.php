<?php

namespace App\Models;

use App\Config\Database;
use App\Models\Product;
use Exception;

class SearchAnalytics {

    // =========================================================================
    // LOG SEARCH EVENT
    // =========================================================================

    /**
     * Log a search query event from store or admin simulation.
     */
    public static function log(
        string $keyword,
        int $resultsCount = 0,
        ?int $customerId = null,
        ?string $ip = null,
        string $device = 'desktop',
        ?int $clickedProductId = null
    ): bool {
        $cleanKeyword = trim(strip_tags($keyword));
        if ($cleanKeyword === '') {
            return false;
        }

        // Limit keyword length
        if (mb_strlen($cleanKeyword) > 255) {
            $cleanKeyword = mb_substr($cleanKeyword, 0, 255);
        }

        $validDevices = ['desktop', 'mobile', 'tablet'];
        if (!in_array($device, $validDevices, true)) {
            $device = 'desktop';
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO search_logs (customer_id, keyword, results_count, ip_address, device_type, clicked_product_id, searched_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("isisis", $customerId, $cleanKeyword, $resultsCount, $ip, $device, $clickedProductId);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("SearchAnalytics::log error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // KPIS & SUMMARY INTELLIGENCE
    // =========================================================================

    /**
     * Retrieve executive summary KPIs for search performance.
     */
    public static function getKPIs(string $period = 'all'): array {
        try {
            $db = Database::connect();
            $dateClause = self::buildDateClause($period, 'searched_at');

            // Overall totals in the period
            $sql = "
                SELECT
                    COUNT(*) AS total_searches,
                    COUNT(DISTINCT LOWER(TRIM(keyword))) AS unique_keywords,
                    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) AS zero_results_count,
                    COALESCE(AVG(results_count), 0) AS avg_results_count,
                    SUM(CASE WHEN customer_id IS NOT NULL THEN 1 ELSE 0 END) AS registered_searches,
                    SUM(CASE WHEN customer_id IS NULL THEN 1 ELSE 0 END) AS guest_searches
                FROM search_logs
                WHERE $dateClause
            ";
            $totals = $db->query($sql)->fetch_assoc();

            $totalSearches = (int)($totals['total_searches'] ?? 0);
            $uniqueKeywords = (int)($totals['unique_keywords'] ?? 0);
            $zeroResults = (int)($totals['zero_results_count'] ?? 0);
            $zeroRate = $totalSearches > 0 ? round(($zeroResults / $totalSearches) * 100, 1) : 0.0;
            $avgResults = round((float)($totals['avg_results_count'] ?? 0), 1);
            $regSearches = (int)($totals['registered_searches'] ?? 0);
            $guestSearches = (int)($totals['guest_searches'] ?? 0);

            // Today searches
            $todayRow = $db->query("
                SELECT COUNT(*) AS cnt FROM search_logs WHERE DATE(searched_at) = CURDATE()
            ")->fetch_assoc();
            $todayCount = (int)($todayRow['cnt'] ?? 0);

            // Yesterday searches (for daily momentum comparison)
            $yestRow = $db->query("
                SELECT COUNT(*) AS cnt FROM search_logs WHERE DATE(searched_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
            ")->fetch_assoc();
            $yestCount = (int)($yestRow['cnt'] ?? 0);

            // Top search query overall in period
            $topQueryRow = $db->query("
                SELECT keyword, COUNT(*) AS cnt
                FROM search_logs
                WHERE $dateClause
                GROUP BY LOWER(TRIM(keyword))
                ORDER BY cnt DESC
                LIMIT 1
            ")->fetch_assoc();

            // Top zero-results query in period (unmet demand)
            $topZeroRow = $db->query("
                SELECT keyword, COUNT(*) AS cnt
                FROM search_logs
                WHERE $dateClause AND results_count = 0
                GROUP BY LOWER(TRIM(keyword))
                ORDER BY cnt DESC
                LIMIT 1
            ")->fetch_assoc();

            return [
                'total_searches'      => $totalSearches,
                'unique_keywords'     => $uniqueKeywords,
                'zero_results_count'  => $zeroResults,
                'zero_results_rate'   => $zeroRate,
                'avg_results_count'   => $avgResults,
                'registered_searches' => $regSearches,
                'guest_searches'      => $guestSearches,
                'searches_today'      => $todayCount,
                'searches_yesterday'  => $yestCount,
                'today_change_pct'    => $yestCount > 0 ? round((($todayCount - $yestCount) / $yestCount) * 100, 1) : 0,
                'top_keyword'         => $topQueryRow['keyword'] ?? 'N/A',
                'top_keyword_count'   => (int)($topQueryRow['cnt'] ?? 0),
                'top_zero_keyword'    => $topZeroRow['keyword'] ?? 'None',
                'top_zero_count'      => (int)($topZeroRow['cnt'] ?? 0),
            ];
        } catch (Exception $e) {
            error_log("SearchAnalytics::getKPIs error: " . $e->getMessage());
            return [
                'total_searches'      => 0,
                'unique_keywords'     => 0,
                'zero_results_count'  => 0,
                'zero_results_rate'   => 0.0,
                'avg_results_count'   => 0.0,
                'registered_searches' => 0,
                'guest_searches'      => 0,
                'searches_today'      => 0,
                'searches_yesterday'  => 0,
                'today_change_pct'    => 0,
                'top_keyword'         => 'N/A',
                'top_keyword_count'   => 0,
                'top_zero_keyword'    => 'None',
                'top_zero_count'      => 0,
            ];
        }
    }

    // =========================================================================
    // TOP QUERIES (High Volume Demand)
    // =========================================================================

    /**
     * Retrieve top search queries grouped by keyword.
     */
    public static function getTopQueries(array $filters = [], int $limit = 10): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? 'all';
            $dateClause = self::buildDateClause($period, 'searched_at');

            $whereClauses = [$dateClause];
            $params = [];
            $types = "";

            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "keyword LIKE ?";
                $params[] = $wild;
                $types .= "s";
            }

            if (!empty($filters['type']) && $filters['type'] === 'zero') {
                $whereClauses[] = "results_count = 0";
            } elseif (!empty($filters['type']) && $filters['type'] === 'results') {
                $whereClauses[] = "results_count > 0";
            }

            $whereSql = implode(" AND ", $whereClauses);

            $sql = "
                SELECT
                    MIN(id) AS sample_id,
                    MIN(keyword) AS keyword,
                    LOWER(TRIM(keyword)) AS normalized_keyword,
                    COUNT(*) AS search_count,
                    ROUND(AVG(results_count), 1) AS avg_results,
                    MIN(results_count) AS min_results,
                    MAX(results_count) AS max_results,
                    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) AS zero_count,
                    COUNT(DISTINCT customer_id) AS unique_users,
                    MAX(searched_at) AS last_searched,
                    MIN(searched_at) AS first_searched
                FROM search_logs
                WHERE $whereSql
                GROUP BY LOWER(TRIM(keyword))
                ORDER BY search_count DESC, last_searched DESC
                LIMIT ?
            ";

            $params[] = $limit;
            $types .= "i";

            $stmt = $db->prepare($sql);
            if ($types !== "") {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("SearchAnalytics::getTopQueries error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // ZERO RESULTS QUERIES (Unmet Demand / Merchandising Gaps)
    // =========================================================================

    /**
     * Retrieve queries that produced 0 results.
     */
    public static function getZeroResultQueries(array $filters = [], int $limit = 10): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? 'all';
            $dateClause = self::buildDateClause($period, 'searched_at');

            $whereClauses = [$dateClause, "results_count = 0"];
            $params = [];
            $types = "";

            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "keyword LIKE ?";
                $params[] = $wild;
                $types .= "s";
            }

            $whereSql = implode(" AND ", $whereClauses);

            $sql = "
                SELECT
                    MIN(id) AS sample_id,
                    MIN(keyword) AS keyword,
                    LOWER(TRIM(keyword)) AS normalized_keyword,
                    COUNT(*) AS search_count,
                    COUNT(DISTINCT customer_id) AS unique_users,
                    MAX(searched_at) AS last_searched,
                    MIN(searched_at) AS first_searched
                FROM search_logs
                WHERE $whereSql
                GROUP BY LOWER(TRIM(keyword))
                ORDER BY search_count DESC, last_searched DESC
                LIMIT ?
            ";

            $params[] = $limit;
            $types .= "i";

            $stmt = $db->prepare($sql);
            if ($types !== "") {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("SearchAnalytics::getZeroResultQueries error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // VOLUME TREND (SVG Chart Data)
    // =========================================================================

    /**
     * Daily search volume for SVG trend chart.
     */
    public static function getVolumeTrend(int $days = 14): array {
        try {
            $db = Database::connect();
            $days = max(7, min($days, 90));

            $sql = "
                SELECT
                    DATE(searched_at) AS log_date,
                    COUNT(*) AS total_count,
                    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) AS zero_count,
                    SUM(CASE WHEN results_count > 0 THEN 1 ELSE 0 END) AS success_count
                FROM search_logs
                WHERE searched_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(searched_at)
                ORDER BY log_date ASC
            ";

            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Index by date
            $mapped = [];
            foreach ($rows as $r) {
                $mapped[$r['log_date']] = [
                    'total'   => (int)$r['total_count'],
                    'zero'    => (int)$r['zero_count'],
                    'success' => (int)$r['success_count'],
                ];
            }

            // Fill all days continuously up to today
            $timeline = [];
            for ($i = $days; $i >= 0; $i--) {
                $dt = date('Y-m-d', strtotime("-$i days"));
                $ts = strtotime($dt);
                $entry = $mapped[$dt] ?? ['total' => 0, 'zero' => 0, 'success' => 0];

                $timeline[] = [
                    'date'            => $dt,
                    'formatted_date'  => date('M j', $ts),
                    'day_name'        => date('D', $ts),
                    'total_count'     => $entry['total'],
                    'zero_count'      => $entry['zero'],
                    'success_count'   => $entry['success'],
                ];
            }

            return $timeline;
        } catch (Exception $e) {
            error_log("SearchAnalytics::getVolumeTrend error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // RAW ACTIVITY STREAM (Paginated Logs)
    // =========================================================================

    /**
     * Retrieve paginated search activity ledger with customer & device info.
     */
    public static function getRecentLogs(array $filters = [], int $page = 1, int $perPage = 20): array {
        try {
            $db = Database::connect();
            $period = $filters['period'] ?? 'all';
            $dateClause = self::buildDateClause($period, 'sl.searched_at');

            $whereClauses = [$dateClause];
            $params = [];
            $types = "";

            // Keyword filter
            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "sl.keyword LIKE ?";
                $params[] = $wild;
                $types .= "s";
            }

            // Results filter
            if (isset($filters['results_filter']) && $filters['results_filter'] !== 'all') {
                if ($filters['results_filter'] === 'zero') {
                    $whereClauses[] = "sl.results_count = 0";
                } elseif ($filters['results_filter'] === 'success') {
                    $whereClauses[] = "sl.results_count > 0";
                }
            }

            // Device filter
            if (!empty($filters['device']) && in_array($filters['device'], ['desktop', 'mobile', 'tablet'], true)) {
                $whereClauses[] = "sl.device_type = ?";
                $params[] = $filters['device'];
                $types .= "s";
            }

            // User type filter
            if (!empty($filters['user_type']) && $filters['user_type'] !== 'all') {
                if ($filters['user_type'] === 'registered') {
                    $whereClauses[] = "sl.customer_id IS NOT NULL";
                } elseif ($filters['user_type'] === 'guest') {
                    $whereClauses[] = "sl.customer_id IS NULL";
                }
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Total count for pagination
            $countSql = "SELECT COUNT(*) FROM search_logs sl WHERE $whereSql";
            $countStmt = $db->prepare($countSql);
            if ($types !== "") {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $total = (int)$countStmt->get_result()->fetch_row()[0];
            $countStmt->close();

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Fetch records
            $selectSql = "
                SELECT
                    sl.*,
                    c.name AS customer_name,
                    c.email AS customer_email
                FROM search_logs sl
                LEFT JOIN customers c ON sl.customer_id = c.id
                WHERE $whereSql
                ORDER BY sl.searched_at DESC, sl.id DESC
                LIMIT ? OFFSET ?
            ";

            $pagParams = array_merge($params, [$perPage, $offset]);
            $pagTypes = $types . "ii";

            $stmt = $db->prepare($selectSql);
            $stmt->bind_param($pagTypes, ...$pagParams);
            $stmt->execute();
            $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return [
                'logs' => $logs,
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
            error_log("SearchAnalytics::getRecentLogs error: " . $e->getMessage());
            return [
                'logs' => [],
                'pagination' => [
                    'total' => 0, 'per_page' => $perPage, 'current_page' => 1,
                    'total_pages' => 1, 'has_prev' => false, 'has_next' => false
                ]
            ];
        }
    }

    // =========================================================================
    // KEYWORD DEEP-DIVE DETAILS
    // =========================================================================

    /**
     * Retrieve 360-degree analytics for a single search keyword.
     */
    public static function getQueryDetails(string $keyword): ?array {
        $cleanKeyword = trim($keyword);
        if ($cleanKeyword === '') {
            return null;
        }

        try {
            $db = Database::connect();

            // Overall stats for this term
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) AS total_searches,
                    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) AS zero_results_count,
                    COALESCE(AVG(results_count), 0) AS avg_results,
                    MIN(results_count) AS min_results,
                    MAX(results_count) AS max_results,
                    COUNT(DISTINCT customer_id) AS unique_customers,
                    SUM(CASE WHEN customer_id IS NULL THEN 1 ELSE 0 END) AS guest_searches,
                    MIN(searched_at) AS first_searched,
                    MAX(searched_at) AS last_searched
                FROM search_logs
                WHERE LOWER(TRIM(keyword)) = LOWER(?)
            ");
            $stmt->bind_param("s", $cleanKeyword);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$stats || (int)$stats['total_searches'] === 0) {
                return null;
            }

            // Device breakdown
            $devStmt = $db->prepare("
                SELECT device_type, COUNT(*) AS cnt
                FROM search_logs
                WHERE LOWER(TRIM(keyword)) = LOWER(?)
                GROUP BY device_type
            ");
            $devStmt->bind_param("s", $cleanKeyword);
            $devStmt->execute();
            $devRows = $devStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $devStmt->close();

            $devices = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
            foreach ($devRows as $dr) {
                $type = $dr['device_type'] ?? 'desktop';
                if (isset($devices[$type])) {
                    $devices[$type] = (int)$dr['cnt'];
                }
            }

            // Daily trend for this term over last 30 days
            $trendStmt = $db->prepare("
                SELECT DATE(searched_at) AS log_date, COUNT(*) AS cnt
                FROM search_logs
                WHERE LOWER(TRIM(keyword)) = LOWER(?)
                  AND searched_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(searched_at)
                ORDER BY log_date ASC
            ");
            $trendStmt->bind_param("s", $cleanKeyword);
            $trendStmt->execute();
            $trendRows = $trendStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $trendStmt->close();

            $trendMap = [];
            foreach ($trendRows as $tr) {
                $trendMap[$tr['log_date']] = (int)$tr['cnt'];
            }
            $termTimeline = [];
            for ($i = 14; $i >= 0; $i--) {
                $dt = date('Y-m-d', strtotime("-$i days"));
                $termTimeline[] = [
                    'date'  => $dt,
                    'label' => date('M j', strtotime($dt)),
                    'count' => $trendMap[$dt] ?? 0,
                ];
            }

            // Recent individual searches for this term
            $recentStmt = $db->prepare("
                SELECT sl.*, c.name AS customer_name, c.email AS customer_email
                FROM search_logs sl
                LEFT JOIN customers c ON sl.customer_id = c.id
                WHERE LOWER(TRIM(sl.keyword)) = LOWER(?)
                ORDER BY sl.searched_at DESC
                LIMIT 25
            ");
            $recentStmt->bind_param("s", $cleanKeyword);
            $recentStmt->execute();
            $recentLogs = $recentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $recentStmt->close();

            // Catalog matches currently in database
            $catalogData = Product::getAll(['search' => $cleanKeyword], 1, 8);
            $catalogMatches = $catalogData['products'] ?? [];
            $catalogTotalMatches = $catalogData['pagination']['total'] ?? count($catalogMatches);

            $totalSearches = (int)$stats['total_searches'];
            $zeroCount = (int)$stats['zero_results_count'];
            $zeroRate = $totalSearches > 0 ? round(($zeroCount / $totalSearches) * 100, 1) : 0.0;

            return [
                'keyword'               => $cleanKeyword,
                'total_searches'        => $totalSearches,
                'zero_results_count'    => $zeroCount,
                'zero_results_rate'     => $zeroRate,
                'avg_results'           => round((float)$stats['avg_results'], 1),
                'min_results'           => (int)$stats['min_results'],
                'max_results'           => (int)$stats['max_results'],
                'unique_customers'      => (int)$stats['unique_customers'],
                'guest_searches'        => (int)$stats['guest_searches'],
                'first_searched'        => $stats['first_searched'],
                'last_searched'         => $stats['last_searched'],
                'devices'               => $devices,
                'timeline'              => $termTimeline,
                'recent_logs'           => $recentLogs,
                'catalog_matches'       => $catalogMatches,
                'catalog_total_matches' => $catalogTotalMatches,
            ];
        } catch (Exception $e) {
            error_log("SearchAnalytics::getQueryDetails error: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // CATALOG SEARCH SIMULATOR
    // =========================================================================

    /**
     * Simulate live store search to evaluate catalog yield for a keyword.
     */
    public static function simulateCatalogSearch(string $keyword): array {
        $clean = trim($keyword);
        if ($clean === '') {
            return ['total' => 0, 'products' => []];
        }

        try {
            $data = Product::getAll(['search' => $clean], 1, 10);
            return [
                'total'    => $data['pagination']['total'] ?? 0,
                'products' => $data['products'] ?? [],
            ];
        } catch (Exception $e) {
            error_log("SearchAnalytics::simulateCatalogSearch error: " . $e->getMessage());
            return ['total' => 0, 'products' => []];
        }
    }

    // =========================================================================
    // DELETE & LOG MANAGEMENT
    // =========================================================================

    /**
     * Delete a single search log entry.
     */
    public static function deleteLog(int $id): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM search_logs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("SearchAnalytics::deleteLog error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all logs matching a specific keyword.
     */
    public static function deleteByKeyword(string $keyword): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM search_logs WHERE LOWER(TRIM(keyword)) = LOWER(?)");
            $stmt->bind_param("s", $keyword);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("SearchAnalytics::deleteByKeyword error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Purge search records older than specified days.
     */
    public static function clearOldLogs(int $days = 90): int {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM search_logs WHERE searched_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            return $affected;
        } catch (Exception $e) {
            error_log("SearchAnalytics::clearOldLogs error: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // HELPER: DATE CLAUSE BUILDER
    // =========================================================================

    /**
     * Generate SQL date range clause based on period identifier.
     */
    private static function buildDateClause(string $period, string $column = 'searched_at'): string {
        return match ($period) {
            'today' => "DATE($column) = CURDATE()",
            '7d'    => "$column >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            '30d'   => "$column >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            '90d'   => "$column >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            default => "1=1",
        };
    }
}
