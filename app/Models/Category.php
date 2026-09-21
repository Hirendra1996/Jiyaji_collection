<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Category {
    /**
     * Retrieve paginated and filtered categories list.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'active') {
                    $whereClauses[] = "c.is_active = 1";
                } elseif ($filters['status'] === 'inactive') {
                    $whereClauses[] = "c.is_active = 0";
                }
            }

            // Parent filter
            if (!empty($filters['parent_id']) && $filters['parent_id'] !== 'all') {
                if ($filters['parent_id'] === 'root') {
                    $whereClauses[] = "c.parent_id IS NULL";
                } elseif ($filters['parent_id'] === 'sub') {
                    $whereClauses[] = "c.parent_id IS NOT NULL";
                } elseif (is_numeric($filters['parent_id'])) {
                    $whereClauses[] = "c.parent_id = ?";
                    $params[] = (int)$filters['parent_id'];
                    $types .= "i";
                }
            }

            // Search filter
            if (!empty($filters['search'])) {
                $term = "%" . $filters['search'] . "%";
                $whereClauses[] = "(c.name LIKE ? OR c.slug LIKE ? OR c.description LIKE ?)";
                $params[] = $term;
                $params[] = $term;
                $params[] = $term;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting
            $sortSql = "ORDER BY c.sort_order ASC, c.name ASC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'newest'      => "ORDER BY c.id DESC",
                    'oldest'      => "ORDER BY c.id ASC",
                    'name_desc'   => "ORDER BY c.name DESC",
                    'name_asc'    => "ORDER BY c.name ASC",
                    'products_desc'=> "ORDER BY product_count DESC",
                    'sort_order'  => "ORDER BY c.sort_order ASC, c.name ASC",
                    default       => "ORDER BY c.sort_order ASC, c.name ASC"
                };
            }

            // Total count query
            $countSql = "SELECT COUNT(*) as total FROM categories c WHERE $whereSql";
            $countStmt = $db->prepare($countSql);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $totalCount = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

            $page = max(1, $page);
            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $offset = ($page - 1) * $perPage;

            // Fetch categories with parent name and linked products count
            $dataSql = "
                SELECT c.*,
                       parent.name as parent_name,
                       parent.slug as parent_slug,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
                FROM categories c
                LEFT JOIN categories parent ON c.parent_id = parent.id
                WHERE $whereSql
                $sortSql
                LIMIT ?, ?
            ";

            $stmt = $db->prepare($dataSql);
            $bindParams = $params;
            $bindParams[] = $offset;
            $bindParams[] = $perPage;
            $bindTypes = $types . "ii";
            $stmt->bind_param($bindTypes, ...$bindParams);
            $stmt->execute();
            $res = $stmt->get_result();

            $categories = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);
                if (!empty($row['parent_id'])) {
                    $row['parent_encrypted_id'] = encrypt_id($row['parent_id']);
                }
                $categories[] = $row;
            }

            return [
                'categories' => $categories,
                'pagination' => [
                    'current_page'  => $page,
                    'per_page'      => $perPage,
                    'total_records' => $totalCount,
                    'total_pages'   => $totalPages,
                    'has_prev'      => ($page > 1),
                    'has_next'      => ($page < $totalPages)
                ]
            ];
        } catch (Exception $e) {
            error_log("Category::getAll error: " . $e->getMessage());
            return [
                'categories' => [],
                'pagination' => [
                    'current_page'  => 1,
                    'per_page'      => $perPage,
                    'total_records' => 0,
                    'total_pages'   => 1,
                    'has_prev'      => false,
                    'has_next'      => false
                ]
            ];
        }
    }

    /**
     * Get aggregate KPI numbers for the categories dashboard.
     */
    public static function getKPIs(): array {
        try {
            $db = Database::connect();
            $q = "
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END) as root_categories,
                    SUM(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END) as sub_categories,
                    (SELECT COUNT(*) FROM products) as total_products
                FROM categories
            ";
            $res = $db->query($q);
            $kpis = $res ? $res->fetch_assoc() : [];

            return [
                'total'          => (int)($kpis['total'] ?? 0),
                'active'         => (int)($kpis['active'] ?? 0),
                'inactive'       => (int)($kpis['inactive'] ?? 0),
                'root_categories'=> (int)($kpis['root_categories'] ?? 0),
                'sub_categories' => (int)($kpis['sub_categories'] ?? 0),
                'total_products' => (int)($kpis['total_products'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("Category::getKPIs error: " . $e->getMessage());
            return [
                'total' => 0, 'active' => 0, 'inactive' => 0,
                'root_categories' => 0, 'sub_categories' => 0, 'total_products' => 0
            ];
        }
    }

    /**
     * Find a category by numeric ID.
     */
    public static function find(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT c.*,
                       parent.name as parent_name,
                       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
                FROM categories c
                LEFT JOIN categories parent ON c.parent_id = parent.id
                WHERE c.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $category = $stmt->get_result()->fetch_assoc();

            if ($category) {
                $category['encrypted_id'] = encrypt_id($category['id']);
                if (!empty($category['parent_id'])) {
                    $category['parent_encrypted_id'] = encrypt_id($category['parent_id']);
                }
            }

            return $category ?: null;
        } catch (Exception $e) {
            error_log("Category::find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch candidate parent categories (root categories excluding self to avoid loops).
     */
    public static function getCandidateParents(?int $excludeId = null): array {
        try {
            $db = Database::connect();
            $sql = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL";
            if ($excludeId !== null) {
                $sql .= " AND id != " . (int)$excludeId;
            }
            $sql .= " ORDER BY name ASC";
            $res = $db->query($sql);
            $parents = [];
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $parents[] = $row;
                }
            }
            return $parents;
        } catch (Exception $e) {
            error_log("Category::getCandidateParents error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new category.
     */
    public static function create(array $data): int {
        try {
            $db = Database::connect();

            $name        = trim($data['name'] ?? '');
            $slug        = self::generateSlug($data['slug'] ?? $name);
            $parentId    = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
            $description = !empty($data['description']) ? trim($data['description']) : null;
            $imageUrl    = !empty($data['image_url']) ? trim($data['image_url']) : null;
            $sortOrder   = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
            $isActive    = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            $stmt = $db->prepare("
                INSERT INTO categories (parent_id, name, slug, description, image_url, sort_order, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("issssii", $parentId, $name, $slug, $description, $imageUrl, $sortOrder, $isActive);
            $stmt->execute();

            return (int)$stmt->insert_id;
        } catch (Exception $e) {
            error_log("Category::create error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing category.
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::connect();

            $name        = trim($data['name'] ?? '');
            $slug        = self::generateSlug($data['slug'] ?? $name, $id);
            $parentId    = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
            $description = !empty($data['description']) ? trim($data['description']) : null;
            $sortOrder   = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
            $isActive    = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            // Check if image is being updated
            if (array_key_exists('image_url', $data)) {
                $imageUrl = !empty($data['image_url']) ? trim($data['image_url']) : null;
                $stmt = $db->prepare("
                    UPDATE categories 
                    SET parent_id = ?, name = ?, slug = ?, description = ?, image_url = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("issssiii", $parentId, $name, $slug, $description, $imageUrl, $sortOrder, $isActive, $id);
            } else {
                $stmt = $db->prepare("
                    UPDATE categories 
                    SET parent_id = ?, name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("isssiii", $parentId, $name, $slug, $description, $sortOrder, $isActive, $id);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Category::update error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Toggle is_active status of a category.
     */
    public static function toggleStatus(int $id): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE categories SET is_active = IF(is_active = 1, 0, 1), updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Category::toggleStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Safely delete a category.
     * Prevents deletion if linked products exist.
     */
    public static function delete(int $id): array {
        try {
            $db = Database::connect();

            // 1. Check linked products
            $pStmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
            $pStmt->bind_param("i", $id);
            $pStmt->execute();
            $pCount = (int)($pStmt->get_result()->fetch_assoc()['count'] ?? 0);

            if ($pCount > 0) {
                return [
                    'success' => false,
                    'message' => "Cannot delete category: {$pCount} product(s) are currently assigned to it. Reassign or remove these products first."
                ];
            }

            // 2. Check child sub-categories
            $cStmt = $db->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
            $cStmt->bind_param("i", $id);
            $cStmt->execute();
            $subCount = (int)($cStmt->get_result()->fetch_assoc()['count'] ?? 0);

            if ($subCount > 0) {
                // Reassign child categories to root
                $db->query("UPDATE categories SET parent_id = NULL WHERE parent_id = $id");
            }

            // 3. Delete category
            $dStmt = $db->prepare("DELETE FROM categories WHERE id = ?");
            $dStmt->bind_param("i", $id);
            $dStmt->execute();

            return [
                'success' => true,
                'message' => "Category successfully deleted."
            ];
        } catch (Exception $e) {
            error_log("Category::delete error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "An unexpected database error occurred while deleting the category."
            ];
        }
    }

    /**
     * Generate unique URL slug.
     */
    public static function generateSlug(string $text, ?int $excludeId = null): string {
        $db = Database::connect();
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $baseSlug = strtolower($text) ?: 'category';

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM categories WHERE slug = ?";
            if ($excludeId !== null) {
                $sql .= " AND id != " . (int)$excludeId;
            }
            $stmt = $db->prepare($sql);
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
