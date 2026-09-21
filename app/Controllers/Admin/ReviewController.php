<?php

namespace App\Controllers\Admin;

use App\Models\Review;
use App\Middleware\AuthMiddleware;
use Exception;

class ReviewController {
    /**
     * Display the reviews moderation queue with KPIs and filters.
     */
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status'   => $_GET['status'] ?? 'all',
            'rating'   => $_GET['rating'] ?? 'all',
            'verified' => $_GET['verified'] ?? 'all',
            'search'   => trim($_GET['search'] ?? ''),
            'sort'     => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $reviewData = Review::getAll($filters, $page, 15);
        $reviews    = $reviewData['reviews'];
        $pagination = $reviewData['pagination'];

        $kpis  = Review::getKPIs();
        $title = 'Reviews Moderation Queue | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/reviews/index.php';
    }

    /**
     * Moderate a single review status (Approve, Reject, Flag, Pending).
     */
    public function moderate(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired. Please try again.']);
                exit;
            }
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/reviews');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid review identifier.']);
                exit;
            }
            set_toast('error', 'Access Denied', 'Invalid review identifier.');
            redirect('admin/reviews');
        }

        $status = trim($_POST['status'] ?? '');
        $allowedStatuses = ['pending', 'approved', 'rejected', 'flagged'];
        if (!in_array($status, $allowedStatuses, true)) {
            set_toast('error', 'Validation Error', 'Invalid status transition.');
            redirect('admin/reviews');
        }

        $admin = auth_admin();
        $adminId = $admin['id'] ?? null;

        $success = Review::moderate($id, $status, $adminId);

        $statusLabels = [
            'approved' => 'Approved and published to storefront.',
            'rejected' => 'Rejected and hidden from storefront.',
            'flagged'  => 'Flagged for internal review.',
            'pending'  => 'Moved back to pending queue.'
        ];

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'status'  => $status,
                'message' => $success ? $statusLabels[$status] : 'Failed to update review status.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Review Moderated', $statusLabels[$status]);
        } else {
            set_toast('error', 'Update Failed', 'Could not update review status.');
        }

        $returnUrl = $_POST['return_url'] ?? 'admin/reviews';
        redirect($returnUrl);
    }

    /**
     * Bulk moderate multiple reviews in one action.
     */
    public function bulkModerate(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/reviews');
        }

        $status = trim($_POST['bulk_status'] ?? '');
        $allowedStatuses = ['pending', 'approved', 'rejected', 'flagged'];
        if (!in_array($status, $allowedStatuses, true)) {
            set_toast('error', 'Validation Error', 'Invalid bulk status specified.');
            redirect('admin/reviews');
        }

        $encryptedIds = $_POST['selected_reviews'] ?? [];
        if (empty($encryptedIds) || !is_array($encryptedIds)) {
            set_toast('warning', 'No Reviews Selected', 'Please select at least one review to moderate.');
            redirect('admin/reviews');
        }

        $rawIds = [];
        foreach ($encryptedIds as $encId) {
            $decId = decrypt_id($encId);
            if ($decId) {
                $rawIds[] = $decId;
            }
        }

        if (empty($rawIds)) {
            set_toast('error', 'Error', 'No valid review IDs found.');
            redirect('admin/reviews');
        }

        $admin = auth_admin();
        $adminId = $admin['id'] ?? null;

        $count = Review::bulkModerate($rawIds, $status, $adminId);

        set_toast('success', 'Bulk Action Complete', "Successfully updated {$count} review(s) to " . strtoupper($status) . ".");
        redirect('admin/reviews');
    }

    /**
     * Delete a review permanently.
     */
    public function destroy(string $encryptedId): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            set_toast('error', 'Invalid Request', 'Session token expired.');
            redirect('admin/reviews');
        }

        $id = decrypt_id($encryptedId);
        if (!$id) {
            set_toast('error', 'Access Denied', 'Invalid review identifier.');
            redirect('admin/reviews');
        }

        $success = Review::delete($id);

        if ($success) {
            set_toast('success', 'Review Deleted', 'The customer review has been permanently removed.');
        } else {
            set_toast('error', 'Delete Failed', 'Could not delete review.');
        }

        redirect('admin/reviews');
    }

    /**
     * Fetch single review details as JSON (for detail modal inspection).
     */
    public function show(string $encryptedId): void {
        AuthMiddleware::check();

        $id = decrypt_id($encryptedId);
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid review token.']);
            exit;
        }

        $review = Review::find($id);
        if (!$review) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Review not found.']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'review' => $review]);
        exit;
    }

    /**
     * Helper to detect AJAX requests.
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
