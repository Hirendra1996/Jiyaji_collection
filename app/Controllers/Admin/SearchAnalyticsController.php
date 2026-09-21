<?php

namespace App\Controllers\Admin;

use App\Models\SearchAnalytics;
use App\Middleware\AuthMiddleware;
use App\Config\Database;
use Exception;

class SearchAnalyticsController {

    // =========================================================================
    // INDEX — Search Analytics Command Center & Dashboard
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $period = $_GET['period'] ?? '30d';
        if (!in_array($period, ['today', '7d', '30d', '90d', 'all'], true)) {
            $period = '30d';
        }

        $activeTab = $_GET['tab'] ?? 'top';
        if (!in_array($activeTab, ['top', 'zero', 'stream'], true)) {
            $activeTab = 'top';
        }

        $filters = [
            'period'         => $period,
            'search'         => trim($_GET['search'] ?? ''),
            'results_filter' => $_GET['results_filter'] ?? 'all',
            'device'         => $_GET['device'] ?? 'all',
            'user_type'      => $_GET['user_type'] ?? 'all',
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

        // Fetch Intelligence Data
        $kpis        = SearchAnalytics::getKPIs($period);
        $volumeTrend = SearchAnalytics::getVolumeTrend($period === '90d' ? 30 : 14);
        $topQueries  = SearchAnalytics::getTopQueries($filters, 20);
        $zeroQueries = SearchAnalytics::getZeroResultQueries($filters, 20);
        $recentLogs  = SearchAnalytics::getRecentLogs($filters, $page, 20);

        $logs       = $recentLogs['logs'];
        $pagination = $recentLogs['pagination'];

        $title = 'Search Analytics & Zero Results Log | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/search_analytics/index.php';
    }

    // =========================================================================
    // SHOW — Term Deep Dive Intelligence
    // =========================================================================

    public function show(string $id): void {
        AuthMiddleware::check();

        $keyword = '';
        $decryptedId = decrypt_id($id);

        if ($decryptedId && is_numeric($decryptedId)) {
            try {
                $db = Database::connect();
                $stmt = $db->prepare("SELECT keyword FROM search_logs WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $decryptedId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($row) {
                    $keyword = $row['keyword'];
                }
            } catch (Exception $e) {
                error_log("SearchAnalyticsController::show error: " . $e->getMessage());
            }
        }

        if ($keyword === '') {
            $keyword = !empty($_GET['keyword']) ? trim($_GET['keyword']) : trim(urldecode($id));
        }

        $details = SearchAnalytics::getQueryDetails($keyword);

        if (!$details) {
            set_toast('error', 'Query Not Found', 'No search analytics records exist for this keyword yet.');
            redirect('admin/search-analytics');
        }

        $title = 'Search Term: "' . e($keyword) . '" | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/search_analytics/show.php';
    }

    // =========================================================================
    // SIMULATE — Live Catalog Search Simulation (AJAX / JSON)
    // =========================================================================

    public function simulate(): void {
        AuthMiddleware::check();

        $keyword = trim($_POST['keyword'] ?? ($_GET['keyword'] ?? ''));
        $logSearch = !empty($_POST['log_simulation']);

        if ($keyword === '') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Keyword cannot be empty.', 'total' => 0, 'products' => []]);
            exit;
        }

        $result = SearchAnalytics::simulateCatalogSearch($keyword);

        if ($logSearch) {
            $admin = auth_admin();
            SearchAnalytics::log(
                $keyword,
                $result['total'],
                null,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'desktop'
            );
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status'   => 'success',
            'keyword'  => $keyword,
            'total'    => $result['total'],
            'products' => array_map(function($p) {
                return [
                    'id'         => encrypt_id((int)$p['id']),
                    'name'       => $p['name'],
                    'sku'        => $p['sku'] ?? 'N/A',
                    'price'      => currency((float)($p['base_price'] ?? 0)),
                    'status'     => $p['status'] ?? 'published',
                    'thumbnail'  => $p['thumbnail_url'] ?? asset('images/product-placeholder.png'),
                    'view_url'   => url('admin/products/' . encrypt_id((int)$p['id']) . '/edit'),
                ];
            }, $result['products']),
        ]);
        exit;
    }

    // =========================================================================
    // EXPORT — CSV Intelligence Report
    // =========================================================================

    public function export(): void {
        AuthMiddleware::check();

        $type = $_GET['export_type'] ?? 'all';
        $period = $_GET['period'] ?? 'all';
        $filters = ['period' => $period, 'search' => trim($_GET['search'] ?? '')];

        $filename = 'jiyaji_search_analytics_' . $type . '_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'zero') {
            fputcsv($out, ['Search Keyword', 'Total Unmet Searches', 'Unique Customers', 'First Searched', 'Last Searched', 'Status']);
            $zeros = SearchAnalytics::getZeroResultQueries($filters, 1000);
            foreach ($zeros as $row) {
                fputcsv($out, [
                    $row['keyword'],
                    $row['search_count'],
                    $row['unique_users'],
                    $row['first_searched'],
                    $row['last_searched'],
                    'Zero Results (Catalog Gap)'
                ]);
            }
        } elseif ($type === 'top') {
            fputcsv($out, ['Search Keyword', 'Total Searches', 'Avg Results Returned', 'Zero Results Count', 'Unique Shoppers', 'First Searched', 'Last Searched']);
            $top = SearchAnalytics::getTopQueries($filters, 1000);
            foreach ($top as $row) {
                fputcsv($out, [
                    $row['keyword'],
                    $row['search_count'],
                    $row['avg_results'],
                    $row['zero_count'],
                    $row['unique_users'],
                    $row['first_searched'],
                    $row['last_searched']
                ]);
            }
        } else {
            // Full Activity Log
            fputcsv($out, ['Log ID', 'Keyword', 'Results Count', 'Customer Name', 'Customer Email', 'Device Type', 'IP Address', 'Searched At']);
            $allLogs = SearchAnalytics::getRecentLogs($filters, 1, 5000);
            foreach ($allLogs['logs'] as $log) {
                fputcsv($out, [
                    $log['id'],
                    $log['keyword'],
                    $log['results_count'],
                    $log['customer_name'] ?? 'Guest Shopper',
                    $log['customer_email'] ?? '—',
                    ucfirst($log['device_type'] ?? 'desktop'),
                    $log['ip_address'] ?? '—',
                    $log['searched_at']
                ]);
            }
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // DESTROY — Delete Single Log or Entire Keyword
    // =========================================================================

    public function destroy(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/search-analytics');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token mismatch. Please try again.');
            redirect('admin/search-analytics');
        }

        $logId = !empty($_POST['log_id']) ? (int)decrypt_id($_POST['log_id']) : 0;
        $keyword = trim($_POST['keyword'] ?? '');

        if ($logId > 0) {
            $deleted = SearchAnalytics::deleteLog($logId);
            if ($deleted) {
                set_toast('success', 'Log Removed', 'The search entry was successfully deleted.');
            } else {
                set_toast('error', 'Delete Failed', 'Could not delete the search entry.');
            }
        } elseif ($keyword !== '') {
            $deleted = SearchAnalytics::deleteByKeyword($keyword);
            if ($deleted) {
                set_toast('success', 'Keyword Cleared', 'All search entries for "' . e($keyword) . '" were purged.');
            } else {
                set_toast('error', 'Delete Failed', 'Could not clear records for this keyword.');
            }
        }

        redirect('admin/search-analytics');
    }

    // =========================================================================
    // CLEAR OLD LOGS — Maintenance Retention Action
    // =========================================================================

    public function clearOld(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/search-analytics');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token mismatch. Please try again.');
            redirect('admin/search-analytics');
        }

        $days = isset($_POST['days']) && is_numeric($_POST['days']) ? (int)$_POST['days'] : 90;
        $days = max(30, min($days, 365));

        $affected = SearchAnalytics::clearOldLogs($days);
        set_toast('success', 'Maintenance Completed', "Successfully purged {$affected} search logs older than {$days} days.");
        redirect('admin/search-analytics');
    }
}
