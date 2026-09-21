<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Product {
    /**
     * Retrieve paginated and filtered catalog products list.
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 15): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            // Status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'low_stock') {
                    $whereClauses[] = "(SELECT COALESCE(SUM(stock_qty), 0) FROM product_variants WHERE product_id = p.id) > 0 AND (SELECT COALESCE(SUM(stock_qty), 0) FROM product_variants WHERE product_id = p.id) <= p.low_stock_threshold";
                } elseif ($filters['status'] === 'out_of_stock') {
                    $whereClauses[] = "(SELECT COALESCE(SUM(stock_qty), 0) FROM product_variants WHERE product_id = p.id) = 0";
                } else {
                    $whereClauses[] = "p.status = ?";
                    $params[] = $filters['status'];
                    $types .= "s";
                }
            }

            // Category filter
            if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
                $whereClauses[] = "p.category_id = ?";
                $params[] = (int)$filters['category_id'];
                $types .= "i";
            }

            // Keyword Search (Product Name, Slug, or SKU)
            if (!empty($filters['search'])) {
                $wildcard = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(p.name LIKE ? OR p.slug LIKE ? OR EXISTS (SELECT 1 FROM product_variants WHERE product_id = p.id AND sku LIKE ?))";
                $params[] = $wildcard;
                $params[] = $wildcard;
                $params[] = $wildcard;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Sorting
            $sortSql = "ORDER BY p.id DESC";
            if (!empty($filters['sort'])) {
                $sortSql = match ($filters['sort']) {
                    'oldest'     => "ORDER BY p.id ASC",
                    'price_high' => "ORDER BY p.base_price DESC",
                    'price_low'  => "ORDER BY p.base_price ASC",
                    'stock_low'  => "ORDER BY total_stock ASC",
                    'stock_high' => "ORDER BY total_stock DESC",
                    'name_asc'   => "ORDER BY p.name ASC",
                    default      => "ORDER BY p.id DESC"
                };
            }

            // Total count query
            $countSql = "SELECT COUNT(*) as total FROM products p WHERE $whereSql";
            $countStmt = $db->prepare($countSql);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $totalCount = (int)($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

            $page = max(1, $page);
            $totalPages = max(1, (int)ceil($totalCount / $perPage));
            $offset = ($page - 1) * $perPage;

            // Fetch records
            $dataSql = "
                SELECT p.*,
                       c.name as category_name,
                       c.slug as category_slug,
                       COALESCE((SELECT SUM(stock_qty) FROM product_variants WHERE product_id = p.id), 0) as total_stock,
                       (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count,
                       (SELECT sku FROM product_variants WHERE product_id = p.id ORDER BY id ASC LIMIT 1) as primary_sku,
                       (SELECT url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
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

            $products = [];
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = encrypt_id($row['id']);

                // Fetch associated variants for this product
                $vStmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
                $vStmt->bind_param("i", $row['id']);
                $vStmt->execute();
                $vRes = $vStmt->get_result();
                $variants = [];
                while ($v = $vRes->fetch_assoc()) {
                    $v['encrypted_id'] = encrypt_id($v['id']);
                    $variants[] = $v;
                }
                $row['variants'] = $variants;

                $products[] = $row;
            }

            return [
                'products'   => $products,
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
            error_log("Product::getAll error: " . $e->getMessage());
            return [
                'products'   => [],
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
     * Aggregate executive catalog KPIs.
     */
    public static function getKPIs(): array {
        $kpis = [
            'total'           => 0,
            'total_products'  => 0,
            'active'          => 0,
            'active_products' => 0,
            'draft'           => 0,
            'draft_products'  => 0,
            'low_stock'       => 0,
            'out_of_stock'    => 0
        ];

        try {
            $db = Database::connect();
            $res = $db->query("
                SELECT 
                    COUNT(*) as total_products,
                    COALESCE(SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END), 0) as active_products,
                    COALESCE(SUM(CASE WHEN p.status = 'draft' THEN 1 ELSE 0 END), 0) as draft_products,
                    COALESCE(SUM(CASE WHEN (SELECT COALESCE(SUM(stock_qty), 0) FROM product_variants WHERE product_id = p.id) > 0 AND (SELECT COALESCE(SUM(stock_qty), 0) FROM product_variants WHERE product_id = p.id) <= p.low_stock_threshold THEN 1 ELSE 0 END), 0) as low_stock,
                    COALESCE(SUM(CASE WHEN (SELECT COALESCE(SUM(stock_qty), 0) FROM product_variants WHERE product_id = p.id) = 0 THEN 1 ELSE 0 END), 0) as out_of_stock
                FROM products p
            ");
            if ($res) {
                $row = $res->fetch_assoc();
                $kpis['total'] = $kpis['total_products']  = (int)$row['total_products'];
                $kpis['active'] = $kpis['active_products'] = (int)$row['active_products'];
                $kpis['draft'] = $kpis['draft_products']  = (int)$row['draft_products'];
                $kpis['low_stock']       = (int)$row['low_stock'];
                $kpis['out_of_stock']    = (int)$row['out_of_stock'];
            }
        } catch (Exception $e) {
            error_log("Product::getKPIs error: " . $e->getMessage());
        }

        return $kpis;
    }

    /**
     * Retrieve complete product details with variants and gallery images.
     */
    public static function getById(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT p.*,
                       c.name as category_name,
                       c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?
                LIMIT 1
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $product = $res->fetch_assoc();

            if (!$product) {
                return null;
            }

            $product['encrypted_id'] = encrypt_id($product['id']);

            // Fetch variants
            $vStmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
            $vStmt->bind_param("i", $id);
            $vStmt->execute();
            $vRes = $vStmt->get_result();

            $variants = [];
            $totalStock = 0;
            while ($v = $vRes->fetch_assoc()) {
                $v['encrypted_id'] = encrypt_id($v['id']);
                $totalStock += (int)$v['stock_qty'];
                $variants[] = $v;
            }
            $product['variants'] = $variants;
            $product['total_stock'] = $totalStock;

            // Fetch images
            $imgStmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
            $imgStmt->bind_param("i", $id);
            $imgStmt->execute();
            $imgRes = $imgStmt->get_result();

            $images = [];
            while ($img = $imgRes->fetch_assoc()) {
                $images[] = $img;
            }
            $product['images'] = $images;

            return $product;
        } catch (Exception $e) {
            error_log("Product::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new luxury product with dynamic variants and gallery images.
     */
    public static function create(array $data): ?int {
        try {
            $db = Database::connect();
            $db->begin_transaction();

            $categoryId = !empty($data['category_id']) ? (int)$data['category_id'] : 1;
            $name       = trim($data['name'] ?? '');
            
            // Generate unique slug
            $slug = !empty($data['slug']) ? trim($data['slug']) : self::generateSlug($name);
            $slug = self::ensureUniqueSlug($db, $slug);

            $shortDesc  = trim($data['short_description'] ?? '');
            $desc       = trim($data['description'] ?? '');
            $basePrice  = (float)round((float)($data['base_price'] ?? 0));
            $salePrice  = !empty($data['sale_price']) ? (float)round((float)$data['sale_price']) : $basePrice;
            $lowStock   = !empty($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : 10;
            $backorder  = !empty($data['allow_backorder']) ? 1 : 0;
            $status     = in_array($data['status'] ?? '', ['active', 'draft', 'archived'], true) ? $data['status'] : 'active';
            $metaTitle  = trim($data['meta_title'] ?? $name);
            $metaDesc   = trim($data['meta_description'] ?? $shortDesc);

            // 1. Insert product
            $stmt = $db->prepare("
                INSERT INTO products 
                (category_id, name, slug, short_description, description, base_price, sale_price, low_stock_threshold, allow_backorder, status, meta_title, meta_description, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("issssddiisss", $categoryId, $name, $slug, $shortDesc, $desc, $basePrice, $salePrice, $lowStock, $backorder, $status, $metaTitle, $metaDesc);
            $stmt->execute();
            $productId = $stmt->insert_id;

            // 2. Insert Variants
            $variants = $data['variants'] ?? [];
            if (empty($variants)) {
                // Fallback default single variant
                $variants[] = [
                    'variant_name'   => 'Standard',
                    'sku'            => 'JLX-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'price_override' => $salePrice,
                    'stock_qty'      => 20
                ];
            }

            $vStmt = $db->prepare("INSERT INTO product_variants (product_id, sku, variant_name, color_name, color_code, size, price_override, stock_qty, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
            $createdVariantIds = [];

            foreach ($variants as $vIdx => $v) {
                $cName  = !empty($v['color_name']) ? trim($v['color_name']) : null;
                $cCode  = !empty($v['color_code']) ? trim($v['color_code']) : null;
                $cSize  = !empty($v['size']) ? trim($v['size']) : null;

                $vName = !empty($v['variant_name']) && $v['variant_name'] !== 'Standard'
                    ? trim($v['variant_name'])
                    : trim(($cName ?: '') . ($cSize ? ($cName ? " / " : "") . $cSize : ''));
                if (empty($vName)) {
                    $vName = 'Standard';
                }

                $vSku   = !empty($v['sku']) ? trim($v['sku']) : ('JLX-' . strtoupper(substr(md5(uniqid()), 0, 8)));
                $vPrice = !empty($v['price_override']) ? (float)round((float)$v['price_override']) : $salePrice;
                $vStock = isset($v['stock_qty']) ? (int)$v['stock_qty'] : 10;

                $vStmt->bind_param("isssssdi", $productId, $vSku, $vName, $cName, $cCode, $cSize, $vPrice, $vStock);
                $vStmt->execute();
                $vId = $vStmt->insert_id;
                $createdVariantIds[$vIdx] = $vId;

                // If stock below threshold, record in stock_alerts
                if ($vStock <= $lowStock) {
                    $saStmt = $db->prepare("INSERT INTO stock_alerts (variant_id, alerted_at, resolved) VALUES (?, NOW(), 0) ON DUPLICATE KEY UPDATE alerted_at = NOW(), resolved = 0");
                    $saStmt->bind_param("i", $vId);
                    $saStmt->execute();
                }
            }

            // 3. Insert Images
            $images = $data['images'] ?? [];
            if (!empty($images)) {
                $imgStmt = $db->prepare("INSERT INTO product_images (product_id, variant_id, url, alt_text, sort_order, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                foreach ($images as $idx => $img) {
                    $imgUrl = '';
                    $varId = null;
                    $isPrimary = ($idx === 0) ? 1 : 0;

                    if (is_array($img)) {
                        $imgUrl = trim($img['url'] ?? '');
                        if (isset($img['variant_index']) && isset($createdVariantIds[$img['variant_index']])) {
                            $varId = $createdVariantIds[$img['variant_index']];
                        } elseif (!empty($img['variant_id'])) {
                            $varId = (int)$img['variant_id'];
                        }
                        if (isset($img['is_primary'])) {
                            $isPrimary = (int)$img['is_primary'];
                        }
                    } else {
                        $imgUrl = trim((string)$img);
                    }

                    if (!empty($imgUrl)) {
                        $imgStmt->bind_param("iissii", $productId, $varId, $imgUrl, $name, $idx, $isPrimary);
                        $imgStmt->execute();
                    }
                }
            }

            $db->commit();
            return $productId;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Product::create error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing product, including variants and media.
     */
    public static function update(int $id, array $data): bool {
        try {
            $db = Database::connect();
            $db->begin_transaction();

            $categoryId = !empty($data['category_id']) ? (int)$data['category_id'] : 1;
            $name       = trim($data['name'] ?? '');
            $shortDesc  = trim($data['short_description'] ?? '');
            $desc       = trim($data['description'] ?? '');
            $basePrice  = (float)round((float)($data['base_price'] ?? 0));
            $salePrice  = !empty($data['sale_price']) ? (float)round((float)$data['sale_price']) : $basePrice;
            $lowStock   = !empty($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : 10;
            $backorder  = !empty($data['allow_backorder']) ? 1 : 0;
            $status     = in_array($data['status'] ?? '', ['active', 'draft', 'archived'], true) ? $data['status'] : 'active';
            $metaTitle  = trim($data['meta_title'] ?? $name);
            $metaDesc   = trim($data['meta_description'] ?? $shortDesc);

            // 1. Update product base fields
            $stmt = $db->prepare("
                UPDATE products 
                SET category_id = ?, name = ?, short_description = ?, description = ?, 
                    base_price = ?, sale_price = ?, low_stock_threshold = ?, allow_backorder = ?, 
                    status = ?, meta_title = ?, meta_description = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("isssddiisssi", $categoryId, $name, $shortDesc, $desc, $basePrice, $salePrice, $lowStock, $backorder, $status, $metaTitle, $metaDesc, $id);
            $stmt->execute();

            // 2. Synchronize variants
            if (!empty($data['variants']) && is_array($data['variants'])) {
                $keptVariantIds = [];

                foreach ($data['variants'] as $v) {
                    $cName  = !empty($v['color_name']) ? trim($v['color_name']) : null;
                    $cCode  = !empty($v['color_code']) ? trim($v['color_code']) : null;
                    $cSize  = !empty($v['size']) ? trim($v['size']) : null;

                    $vName = !empty($v['variant_name']) && $v['variant_name'] !== 'Standard'
                        ? trim($v['variant_name'])
                        : trim(($cName ?: '') . ($cSize ? ($cName ? " / " : "") . $cSize : ''));
                    if (empty($vName)) {
                        $vName = 'Standard';
                    }

                    $vSku   = !empty($v['sku']) ? trim($v['sku']) : ('JLX-' . strtoupper(substr(md5(uniqid()), 0, 8)));
                    $vPrice = !empty($v['price_override']) ? (float)round((float)$v['price_override']) : $salePrice;
                    $vStock = isset($v['stock_qty']) ? (int)$v['stock_qty'] : 10;

                    if (!empty($v['id'])) {
                        // Update existing variant
                        $vId = (int)$v['id'];
                        $vUp = $db->prepare("UPDATE product_variants SET sku = ?, variant_name = ?, color_name = ?, color_code = ?, size = ?, price_override = ?, stock_qty = ? WHERE id = ? AND product_id = ?");
                        $vUp->bind_param("sssssdiii", $vSku, $vName, $cName, $cCode, $cSize, $vPrice, $vStock, $vId, $id);
                        $vUp->execute();
                        $keptVariantIds[] = $vId;
                    } else {
                        // Insert new variant
                        $vIn = $db->prepare("INSERT INTO product_variants (product_id, sku, variant_name, color_name, color_code, size, price_override, stock_qty, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
                        $vIn->bind_param("isssssdi", $id, $vSku, $vName, $cName, $cCode, $cSize, $vPrice, $vStock);
                        $vIn->execute();
                        $keptVariantIds[] = $vIn->insert_id;
                    }
                }

                // Delete variants not in updated payload
                if (!empty($keptVariantIds)) {
                    $inPlaceholders = implode(',', array_fill(0, count($keptVariantIds), '?'));
                    $delStmt = $db->prepare("DELETE FROM product_variants WHERE product_id = ? AND id NOT IN ($inPlaceholders)");
                    $delStmt->bind_param("i" . str_repeat('i', count($keptVariantIds)), $id, ...$keptVariantIds);
                    $delStmt->execute();
                }
            }

            // 3. Update images if passed
            if (isset($data['images']) && is_array($data['images'])) {
                // Clear and recreate gallery
                $db->query("DELETE FROM product_images WHERE product_id = $id");
                $imgStmt = $db->prepare("INSERT INTO product_images (product_id, variant_id, url, alt_text, sort_order, is_primary, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                foreach ($data['images'] as $idx => $img) {
                    $imgUrl = '';
                    $varId = null;
                    $isPrimary = ($idx === 0) ? 1 : 0;

                    if (is_array($img)) {
                        $imgUrl = trim($img['url'] ?? '');
                        if (!empty($img['variant_id'])) {
                            $varId = (int)$img['variant_id'];
                        }
                        if (isset($img['is_primary'])) {
                            $isPrimary = (int)$img['is_primary'];
                        }
                    } else {
                        $imgUrl = trim((string)$img);
                    }

                    if (!empty($imgUrl)) {
                        $imgStmt->bind_param("iissii", $id, $varId, $imgUrl, $name, $idx, $isPrimary);
                        $imgStmt->execute();
                    }
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            error_log("Product::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fast status toggle (active, draft, archived).
     */
    public static function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['active', 'draft', 'archived'], true)) {
            return false;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Product::updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * In-line adjust stock quantity for a single variant.
     */
    public static function adjustVariantStock(int $variantId, int $newQty): bool {
        try {
            $db = Database::connect();
            $newQty = max(0, $newQty);

            $stmt = $db->prepare("UPDATE product_variants SET stock_qty = ? WHERE id = ?");
            $stmt->bind_param("ii", $newQty, $variantId);
            $success = $stmt->execute();

            if ($success) {
                // Check product low stock threshold
                $v = $db->query("
                    SELECT pv.stock_qty, p.low_stock_threshold 
                    FROM product_variants pv 
                    JOIN products p ON pv.product_id = p.id 
                    WHERE pv.id = $variantId
                ")->fetch_assoc();

                if ($v) {
                    if ($newQty <= (int)$v['low_stock_threshold']) {
                        $db->query("INSERT INTO stock_alerts (variant_id, alerted_at, resolved) VALUES ($variantId, NOW(), 0) ON DUPLICATE KEY UPDATE alerted_at = NOW(), resolved = 0");
                    } else {
                        $db->query("UPDATE stock_alerts SET resolved = 1 WHERE variant_id = $variantId");
                    }
                }
            }

            return $success;
        } catch (Exception $e) {
            error_log("Product::adjustVariantStock error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete/archive a product.
     */
    public static function delete(int $id): bool {
        return self::updateStatus($id, 'archived');
    }

    /**
     * Fetch active categories for dropdown selects.
     */
    public static function getAllCategories(): array {
        try {
            $db = Database::connect();
            $res = $db->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY name ASC");
            $categories = [];
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $categories[] = $row;
                }
            }
            return $categories;
        } catch (Exception $e) {
            error_log("Product::getAllCategories error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper to slugify a string.
     */
    public static function generateSlug(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'product-' . time() : $text;
    }

    /**
     * Helper to guarantee uniqueness of a product slug.
     */
    private static function ensureUniqueSlug($db, string $slug): string {
        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
