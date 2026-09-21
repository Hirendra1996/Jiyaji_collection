<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Review {
    /**
     * Retrieve paginated and filtered list of customer reviews.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Moderation Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $whereClauses[] = "r.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }

            // Star Rating filter (1 to 5)
            if (!empty($filters['rating']) && $filters['rating'] !== 'all' && is_numeric($filters['rating'])) {
                $whereClauses[] = "r.rating = ?";
                $params[] = (int)$filters['rating'];
                $types .= "i";
            }

            // Verified Buyer filter
            if (!empty($filters['verified']) && $filters['verified'] === 'verified_only') {
                $whereClauses[] = "r.order_item_id IS NOT NULL";
            }

            // Photos filter
            if (!empty($filters['photos']) && $filters['photos'] === 'with_photos') {
                $whereClauses[] = "r.photo_urls IS NOT NULL AND r.photo_urls != '' AND r.photo_urls != '[]'";
            }

            // Search query (Customer Name, Email, Product Title, Review Title, Body)
            if (!empty($filters['search'])) {
                $searchWildcard = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(c.name LIKE ? OR c.email LIKE ? OR p.name LIKE ? OR r.title LIKE ? OR r.body LIKE ?)";
                for ($i = 0; $i < 5; $i++) {
                    $params[] = $searchWildcard;
                    $types .= "s";
                }
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting
            $sortSql = "ORDER BY r.created_at DESC, r.id DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'newest'      => "ORDER BY r.created_at DESC, r.id DESC",
                    'oldest'      => "ORDER BY r.created_at ASC, r.id ASC",
                    'rating_high' => "ORDER BY r.rating DESC, r.created_at DESC",
                    'rating_low'  => "ORDER BY r.rating ASC, r.created_at DESC",
                    default       => "ORDER BY r.created_at DESC, r.id DESC"
                };
            }

            // Count total query
            $countSql = "
                SELECT COUNT(*) as total
                FROM reviews r
                JOIN products p ON r.product_id = p.id
                JOIN customers c ON r.customer_id = c.id
                WHERE $whereSql
            ";

            $stmtCount = $db->prepare($countSql);
            if (!empty($params)) {
                $stmtCount->bind_param($types, ...$params);
            }
            $stmtCount->execute();
            $totalReviews = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);

            // Pagination calculations
            $totalPages = max(1, (int)ceil($totalReviews / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Main Select Query
            $selectSql = "
                SELECT 
                    r.*,
                    p.name AS product_name,
                    p.slug AS product_slug,
                    (
                        SELECT pi.url 
                        FROM product_images pi 
                        WHERE pi.product_id = p.id 
                        ORDER BY pi.is_primary DESC, pi.id ASC 
                        LIMIT 1
                    ) AS product_image,
                    c.name AS customer_name,
                    c.email AS customer_email,
                    c.profile_photo_url AS customer_photo,
                    adm.name AS moderator_name
                FROM reviews r
                JOIN products p ON r.product_id = p.id
                JOIN customers c ON r.customer_id = c.id
                LEFT JOIN admins adm ON r.moderated_by = adm.id
                WHERE $whereSql
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

            $reviews = [];
            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $row['product_encrypted_id'] = encrypt_id($row['product_id']);
                $row['customer_encrypted_id'] = encrypt_id($row['customer_id']);
                $row['is_verified_buyer'] = !empty($row['order_item_id']);

                // Parse photo URLs if JSON array or comma separated
                $photos = [];
                if (!empty($row['photo_urls'])) {
                    $decoded = json_decode($row['photo_urls'], true);
                    if (is_array($decoded)) {
                        $photos = $decoded;
                    } else {
                        $photos = array_filter(array_map('trim', explode(',', $row['photo_urls'])));
                    }
                }
                $row['photos'] = $photos;

                $reviews[] = $row;
            }

            return [
                'reviews'    => $reviews,
                'pagination' => [
                    'total_items'  => $totalReviews,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'offset'       => $offset,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Review::getAll error: " . $e->getMessage());
            return [
                'reviews'    => [],
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
     * Retrieve executive summary KPIs for reviews moderation queue.
     */
    public static function getKPIs(): array {
        $kpis = [
            'total_reviews'        => 0,
            'pending_reviews'      => 0,
            'approved_reviews'     => 0,
            'flagged_reviews'      => 0,
            'rejected_reviews'     => 0,
            'verified_reviews'     => 0,
            'photo_reviews'        => 0,
            'average_store_rating' => 5.0
        ];

        try {
            $db = Database::connect();

            $res = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'flagged' THEN 1 ELSE 0 END) as flagged,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN order_item_id IS NOT NULL THEN 1 ELSE 0 END) as verified_count,
                    SUM(CASE WHEN photo_urls IS NOT NULL AND photo_urls != '' AND photo_urls != '[]' THEN 1 ELSE 0 END) as photo_count,
                    AVG(CASE WHEN status = 'approved' THEN rating ELSE NULL END) as avg_rating
                FROM reviews
            ");

            if ($res && $row = $res->fetch_assoc()) {
                $kpis['total_reviews']        = (int)($row['total'] ?? 0);
                $kpis['pending_reviews']      = (int)($row['pending'] ?? 0);
                $kpis['approved_reviews']     = (int)($row['approved'] ?? 0);
                $kpis['flagged_reviews']      = (int)($row['flagged'] ?? 0);
                $kpis['rejected_reviews']     = (int)($row['rejected'] ?? 0);
                $kpis['verified_reviews']     = (int)($row['verified_count'] ?? 0);
                $kpis['photo_reviews']        = (int)($row['photo_count'] ?? 0);
                $kpis['average_store_rating'] = $row['avg_rating'] !== null ? round((float)$row['avg_rating'], 1) : 5.0;
            }
        } catch (Exception $e) {
            error_log("Review::getKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Retrieve all reviews matching filter criteria for CSV export.
     */
    public static function getAllForExport(array $filters = []): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Moderation Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $whereClauses[] = "r.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }

            // Star Rating filter (1 to 5)
            if (!empty($filters['rating']) && $filters['rating'] !== 'all' && is_numeric($filters['rating'])) {
                $whereClauses[] = "r.rating = ?";
                $params[] = (int)$filters['rating'];
                $types .= "i";
            }

            // Verified Buyer filter
            if (!empty($filters['verified']) && $filters['verified'] === 'verified_only') {
                $whereClauses[] = "r.order_item_id IS NOT NULL";
            }

            // Photos filter
            if (!empty($filters['photos']) && $filters['photos'] === 'with_photos') {
                $whereClauses[] = "r.photo_urls IS NOT NULL AND r.photo_urls != '' AND r.photo_urls != '[]'";
            }

            // Search query
            if (!empty($filters['search'])) {
                $searchWildcard = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(c.name LIKE ? OR c.email LIKE ? OR p.name LIKE ? OR r.title LIKE ? OR r.body LIKE ?)";
                for ($i = 0; $i < 5; $i++) {
                    $params[] = $searchWildcard;
                    $types .= "s";
                }
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting
            $sortSql = "ORDER BY r.created_at DESC, r.id DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'newest'      => "ORDER BY r.created_at DESC, r.id DESC",
                    'oldest'      => "ORDER BY r.created_at ASC, r.id ASC",
                    'rating_high' => "ORDER BY r.rating DESC, r.created_at DESC",
                    'rating_low'  => "ORDER BY r.rating ASC, r.created_at DESC",
                    default       => "ORDER BY r.created_at DESC, r.id DESC"
                };
            }

            $selectSql = "
                SELECT 
                    r.*,
                    p.name AS product_name,
                    p.slug AS product_slug,
                    c.name AS customer_name,
                    c.email AS customer_email,
                    adm.name AS moderator_name
                FROM reviews r
                JOIN products p ON r.product_id = p.id
                JOIN customers c ON r.customer_id = c.id
                LEFT JOIN admins adm ON r.moderated_by = adm.id
                WHERE $whereSql
                $sortSql
            ";

            $stmt = $db->prepare($selectSql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                $row['is_verified_buyer'] = !empty($row['order_item_id']);

                $photos = [];
                if (!empty($row['photo_urls'])) {
                    $decoded = json_decode($row['photo_urls'], true);
                    if (is_array($decoded)) {
                        $photos = $decoded;
                    } else {
                        $photos = array_filter(array_map('trim', explode(',', $row['photo_urls'])));
                    }
                }
                $row['photos'] = $photos;
                $row['photo_count'] = count($photos);

                $rows[] = $row;
            }

            return $rows;
        } catch (Exception $e) {
            error_log("Review::getAllForExport error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find single review by ID with complete relationships.
     */
    public static function find(int $id): ?array {
        try {
            $db = Database::connect();

            $stmt = $db->prepare("
                SELECT 
                    r.*,
                    p.name AS product_name,
                    p.slug AS product_slug,
                    (
                        SELECT pi.url 
                        FROM product_images pi 
                        WHERE pi.product_id = p.id 
                        ORDER BY pi.is_primary DESC, pi.id ASC 
                        LIMIT 1
                    ) AS product_image,
                    c.name AS customer_name,
                    c.email AS customer_email,
                    c.phone AS customer_phone,
                    c.profile_photo_url AS customer_photo,
                    adm.name AS moderator_name
                FROM reviews r
                JOIN products p ON r.product_id = p.id
                JOIN customers c ON r.customer_id = c.id
                LEFT JOIN admins adm ON r.moderated_by = adm.id
                WHERE r.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $review = $stmt->get_result()->fetch_assoc();

            if (!$review) {
                return null;
            }

            $review['encrypted_id'] = encrypt_id($review['id']);
            $review['product_encrypted_id'] = encrypt_id($review['product_id']);
            $review['customer_encrypted_id'] = encrypt_id($review['customer_id']);
            $review['is_verified_buyer'] = !empty($review['order_item_id']);

            $photos = [];
            if (!empty($review['photo_urls'])) {
                $decoded = json_decode($review['photo_urls'], true);
                if (is_array($decoded)) {
                    $photos = $decoded;
                } else {
                    $photos = array_filter(array_map('trim', explode(',', $review['photo_urls'])));
                }
            }
            $review['photos'] = $photos;

            return $review;
        } catch (Exception $e) {
            error_log("Review::find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Moderate a review status (Approve, Reject, Flag, Pending) and synchronize product rating.
     */
    public static function moderate(int $id, string $status, ?int $moderatorAdminId = null): bool {
        $allowedStatuses = ['pending', 'approved', 'rejected', 'flagged'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        try {
            $db = Database::connect();

            // Fetch product_id first for sync
            $pStmt = $db->prepare("SELECT product_id FROM reviews WHERE id = ?");
            $pStmt->bind_param("i", $id);
            $pStmt->execute();
            $reviewRow = $pStmt->get_result()->fetch_assoc();
            if (!$reviewRow) {
                return false;
            }
            $productId = (int)$reviewRow['product_id'];

            // Update review status
            $stmt = $db->prepare("
                UPDATE reviews 
                SET status = ?, moderated_by = ?, moderated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("sii", $status, $moderatorAdminId, $id);
            $success = $stmt->execute();

            if ($success) {
                self::syncProductRating($productId);
            }

            return $success;
        } catch (Exception $e) {
            error_log("Review::moderate error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch moderate multiple reviews.
     */
    public static function bulkModerate(array $ids, string $status, ?int $moderatorAdminId = null): int {
        $allowedStatuses = ['pending', 'approved', 'rejected', 'flagged'];
        if (!in_array($status, $allowedStatuses, true) || empty($ids)) {
            return 0;
        }

        try {
            $db = Database::connect();
            $intIds = array_map('intval', array_filter($ids, 'is_numeric'));
            if (empty($intIds)) {
                return 0;
            }

            // Find all affected product IDs
            $inPlaceholders = implode(',', array_fill(0, count($intIds), '?'));
            $pStmt = $db->prepare("SELECT DISTINCT product_id FROM reviews WHERE id IN ($inPlaceholders)");
            $types = str_repeat('i', count($intIds));
            $pStmt->bind_param($types, ...$intIds);
            $pStmt->execute();
            $pRes = $pStmt->get_result();
            $productIds = [];
            while ($pRow = $pRes->fetch_assoc()) {
                $productIds[] = (int)$pRow['product_id'];
            }

            // Execute bulk update
            $uStmt = $db->prepare("
                UPDATE reviews 
                SET status = ?, moderated_by = ?, moderated_at = NOW() 
                WHERE id IN ($inPlaceholders)
            ");
            $uTypes = "si" . str_repeat('i', count($intIds));
            $uParams = array_merge([$status, $moderatorAdminId], $intIds);
            $uStmt->bind_param($uTypes, ...$uParams);
            $uStmt->execute();
            $affected = $uStmt->affected_rows;

            // Synchronize all affected products
            foreach ($productIds as $prodId) {
                self::syncProductRating($prodId);
            }

            return $affected;
        } catch (Exception $e) {
            error_log("Review::bulkModerate error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete a review permanently and synchronize product rating.
     */
    public static function delete(int $id): bool {
        try {
            $db = Database::connect();

            // Fetch product_id for rating sync
            $pStmt = $db->prepare("SELECT product_id FROM reviews WHERE id = ?");
            $pStmt->bind_param("i", $id);
            $pStmt->execute();
            $reviewRow = $pStmt->get_result()->fetch_assoc();
            if (!$reviewRow) {
                return false;
            }
            $productId = (int)$reviewRow['product_id'];

            $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();

            if ($success) {
                self::syncProductRating($productId);
            }

            return $success;
        } catch (Exception $e) {
            error_log("Review::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new review record.
     */
    public static function create(array $data): int {
        try {
            $db = Database::connect();

            $productId = (int)($data['product_id'] ?? 0);
            $customerId = (int)($data['customer_id'] ?? 0);
            $orderItemId = !empty($data['order_item_id']) ? (int)$data['order_item_id'] : null;
            $rating = max(1, min(5, (int)($data['rating'] ?? 5)));
            $title = trim($data['title'] ?? '');
            $body = trim($data['body'] ?? '');
            $photoUrls = !empty($data['photo_urls']) ? (is_array($data['photo_urls']) ? json_encode($data['photo_urls']) : trim($data['photo_urls'])) : null;
            $status = in_array($data['status'] ?? '', ['pending', 'approved', 'rejected', 'flagged'], true) ? $data['status'] : 'pending';
            $moderatedBy = !empty($data['moderated_by']) ? (int)$data['moderated_by'] : null;

            $stmt = $db->prepare("
                INSERT INTO reviews (product_id, customer_id, order_item_id, rating, title, body, photo_urls, status, moderated_by, moderated_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, " . ($status === 'approved' ? 'NOW()' : 'NULL') . ", NOW())
            ");
            $stmt->bind_param("iiiissssi", $productId, $customerId, $orderItemId, $rating, $title, $body, $photoUrls, $status, $moderatedBy);
            $stmt->execute();

            $newId = (int)$db->insert_id;

            if ($newId > 0 && $status === 'approved') {
                self::syncProductRating($productId);
            }

            return $newId;
        } catch (Exception $e) {
            error_log("Review::create error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Automatically synchronize product average rating and review count.
     */
    public static function syncProductRating(int $productId): void {
        try {
            $db = Database::connect();

            $stmt = $db->prepare("
                UPDATE products p
                SET 
                    review_count = (
                        SELECT COUNT(*) 
                        FROM reviews r 
                        WHERE r.product_id = p.id AND r.status = 'approved'
                    ),
                    average_rating = COALESCE((
                        SELECT ROUND(AVG(r.rating), 2) 
                        FROM reviews r 
                        WHERE r.product_id = p.id AND r.status = 'approved'
                    ), 0.00)
                WHERE p.id = ?
            ");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Review::syncProductRating error: " . $e->getMessage());
        }
    }
}
