<?php

namespace App\Controllers\Portal;

use App\Models\Review;
use App\Middleware\PortalAuthMiddleware;
use Exception;

/**
 * PortalReviewController
 *
 * Provides customer reviews and sentiment moderation for the Staff & Operations Portal.
 * Guarded by PortalAuthMiddleware and granular RBAC:
 *   - reviews:view     → inspect feedback ledger, search, filter, view photos, export CSV
 *   - reviews:moderate → approve, reject, flag, reset, delete, and bulk-moderate reviews
 */
class PortalReviewController {

    /**
     * Display the reviews moderation queue with KPIs, filter toolbar, and review ledger.
     */
    public function index(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('reviews', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to view Customer Reviews.');
            redirect('portal/dashboard');
        }

        $filters = [
            'status'   => $_GET['status'] ?? 'all',
            'rating'   => $_GET['rating'] ?? 'all',
            'verified' => $_GET['verified'] ?? 'all',
            'photos'   => $_GET['photos'] ?? 'all',
            'search'   => trim($_GET['search'] ?? ''),
            'sort'     => $_GET['sort'] ?? 'newest'
        ];

        $page       = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $reviewData = Review::getAll($filters, $page, 15);
        $reviews    = $reviewData['reviews'] ?? [];
        $pagination = $reviewData['pagination'] ?? [
            'total_items'  => 0,
            'per_page'     => 15,
            'current_page' => 1,
            'total_pages'  => 1,
            'offset'       => 0,
            'has_prev'     => false,
            'has_next'     => false
        ];
        $pagination['has_prev'] = $pagination['has_prev'] ?? ($page > 1);
        $pagination['has_next'] = $pagination['has_next'] ?? ($page < ($pagination['total_pages'] ?? 1));

        $kpis        = Review::getKPIs();
        $canModerate = staff_can('reviews', 'moderate');

        $title = 'Customer Reviews & Moderation | Jiyaji LX Staff Portal';
        include __DIR__ . '/../../Views/portal/reviews/index.php';
    }

    /**
     * Moderate a single review status (Approve, Reject, Flag, Pending).
     */
    public function moderate(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('reviews', 'moderate')) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access Denied: You do not have permission to moderate reviews.']);
                exit;
            }
            set_flash('error', 'Access Denied: Insufficient permissions to moderate reviews.');
            redirect('portal/reviews');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Security validation failed or token expired.']);
                exit;
            }
            set_toast('error', 'Security Error', 'Session token expired. Please try again.');
            redirect('portal/reviews');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid review identifier.']);
                exit;
            }
            set_toast('error', 'Invalid Token', 'Invalid review identifier provided.');
            redirect('portal/reviews');
        }

        $status = trim($_POST['status'] ?? '');
        $allowedStatuses = ['pending', 'approved', 'rejected', 'flagged'];
        if (!in_array($status, $allowedStatuses, true)) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid status transition requested.']);
                exit;
            }
            set_toast('error', 'Validation Error', 'Invalid status state.');
            redirect('portal/reviews');
        }

        $staff = auth_staff();
        $staffId = !empty($staff['id']) ? (int)$staff['id'] : null;

        $success = Review::moderate($id, $status, $staffId);

        $statusLabels = [
            'approved' => 'Approved and published to storefront.',
            'rejected' => 'Rejected and hidden from storefront.',
            'flagged'  => 'Flagged for internal compliance audit.',
            'pending'  => 'Returned to pending moderation queue.'
        ];

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'status'  => $status,
                'status_label' => ucfirst($status),
                'message' => $success ? ($statusLabels[$status] ?? 'Status updated.') : 'Database update failed.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Review Moderated', $statusLabels[$status] ?? 'Review status updated successfully.');
        } else {
            set_toast('error', 'Update Failed', 'Could not update review status.');
        }

        $returnUrl = $_POST['return_url'] ?? 'portal/reviews';
        redirect($returnUrl);
    }

    /**
     * Bulk moderate multiple reviews in one action.
     */
    public function bulkModerate(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('reviews', 'moderate')) {
            set_flash('error', 'Access Denied: You do not have permission to moderate reviews.');
            redirect('portal/reviews');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/reviews');
        }

        $bulkAction = trim($_POST['bulk_status'] ?? '');
        $encryptedIds = $_POST['selected_reviews'] ?? [];

        if (empty($encryptedIds) || !is_array($encryptedIds)) {
            set_toast('warning', 'No Reviews Selected', 'Please select at least one review checkbox.');
            redirect('portal/reviews');
        }

        $rawIds = [];
        foreach ($encryptedIds as $encId) {
            $decId = decrypt_id($encId);
            if ($decId) {
                $rawIds[] = $decId;
            }
        }

        if (empty($rawIds)) {
            set_toast('error', 'Error', 'No valid review tokens found.');
            redirect('portal/reviews');
        }

        $staff = auth_staff();
        $staffId = !empty($staff['id']) ? (int)$staff['id'] : null;

        if ($bulkAction === 'delete') {
            $deletedCount = 0;
            foreach ($rawIds as $rid) {
                if (Review::delete($rid)) {
                    $deletedCount++;
                }
            }
            set_toast('success', 'Bulk Delete Complete', "Permanently removed {$deletedCount} review(s).");
            redirect('portal/reviews');
        }

        $allowedStatuses = ['pending', 'approved', 'rejected', 'flagged'];
        if (!in_array($bulkAction, $allowedStatuses, true)) {
            set_toast('error', 'Validation Error', 'Invalid bulk action specified.');
            redirect('portal/reviews');
        }

        $count = Review::bulkModerate($rawIds, $bulkAction, $staffId);

        set_toast('success', 'Bulk Action Complete', "Successfully updated {$count} review(s) to " . strtoupper($bulkAction) . ".");
        redirect('portal/reviews');
    }

    /**
     * Delete a review permanently.
     */
    public function destroy(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('reviews', 'moderate')) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access Denied: Insufficient permissions.']);
                exit;
            }
            set_flash('error', 'Access Denied: You do not have permission to delete reviews.');
            redirect('portal/reviews');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request or token expired.']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('portal/reviews');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid review token.']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid review identifier.');
            redirect('portal/reviews');
        }

        $success = Review::delete($id);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Review permanently removed.' : 'Could not delete review.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Review Deleted', 'The customer review has been permanently removed.');
        } else {
            set_toast('error', 'Delete Failed', 'Could not delete review from database.');
        }

        redirect('portal/reviews');
    }

    /**
     * Fetch single review details as JSON (for detail inspection modal / slide-out).
     */
    public function show(string $encryptedId): void {
        PortalAuthMiddleware::check();

        if (!staff_can('reviews', 'view')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access Denied.']);
            exit;
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid review token.']);
            exit;
        }

        $review = Review::find($id);
        if (!$review) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Review record not found.']);
            exit;
        }

        // Add formatted dates and photo image URLs
        $review['formatted_date'] = date('M d, Y h:i A', strtotime($review['created_at']));
        $review['moderated_date'] = !empty($review['moderated_at']) ? date('M d, Y h:i A', strtotime($review['moderated_at'])) : null;
        
        $resolvedPhotos = [];
        if (!empty($review['photos']) && is_array($review['photos'])) {
            foreach ($review['photos'] as $p) {
                $resolvedPhotos[] = [
                    'raw' => $p,
                    'url' => image_url($p)
                ];
            }
        }
        $review['resolved_photos'] = $resolvedPhotos;
        $review['product_image_url'] = !empty($review['product_image']) ? image_url($review['product_image']) : null;
        $review['product_portal_url'] = url('portal/products/' . ($review['product_encrypted_id'] ?? '') . '/edit');
        $review['customer_portal_url'] = url('portal/customers/' . ($review['customer_encrypted_id'] ?? ''));

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'review' => $review]);
        exit;
    }

    /**
     * Stream a complete CSV export of filtered reviews.
     */
    public function export(): void {
        PortalAuthMiddleware::check();

        if (!staff_can('reviews', 'view')) {
            set_flash('error', 'Access Denied: You do not have clearance to export Customer Reviews.');
            redirect('portal/reviews');
        }

        $filters = [
            'status'   => $_GET['status'] ?? 'all',
            'rating'   => $_GET['rating'] ?? 'all',
            'verified' => $_GET['verified'] ?? 'all',
            'photos'   => $_GET['photos'] ?? 'all',
            'search'   => trim($_GET['search'] ?? ''),
            'sort'     => $_GET['sort'] ?? 'newest'
        ];

        $reviews = Review::getAllForExport($filters);

        $filename = 'jiyaji_reviews_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, [
            'Review ID',
            'Submission Date',
            'Product Name',
            'Product Slug',
            'Customer Name',
            'Customer Email',
            'Star Rating',
            'Review Title',
            'Feedback Body',
            'Verified Buyer',
            'Moderation Status',
            'Moderated By',
            'Moderated Date',
            'Photo Count',
            'Photo URLs'
        ]);

        foreach ($reviews as $rev) {
            $photosStr = !empty($rev['photos']) ? implode(' | ', $rev['photos']) : '';

            fputcsv($out, [
                $rev['id'],
                $rev['created_at'],
                $rev['product_name'] ?? '',
                $rev['product_slug'] ?? '',
                $rev['customer_name'] ?? '',
                $rev['customer_email'] ?? '',
                $rev['rating'] . ' / 5',
                $rev['title'] ?? '',
                $rev['body'] ?? '',
                !empty($rev['is_verified_buyer']) ? 'Yes' : 'No',
                strtoupper($rev['status'] ?? 'pending'),
                $rev['moderator_name'] ?? 'System / Unassigned',
                $rev['moderated_at'] ?? 'N/A',
                $rev['photo_count'] ?? 0,
                $photosStr
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Helper to detect AJAX or JSON requests.
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
