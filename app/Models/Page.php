<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Page {

    // =========================================================================
    // 1. DIRECTORY RETRIEVAL & CRUD
    // =========================================================================

    /**
     * Retrieve paginated and filtered static CMS pages.
     */
    public static function getPages(array $filters = [], int $page = 1, int $perPage = 15): array {
        self::ensureDefaults();

        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
                $whereClauses[] = "p.is_active = ?";
                $params[] = $filters['status'] === 'active' ? 1 : 0;
                $types .= "i";
            }

            if (isset($filters['type']) && $filters['type'] !== 'all' && $filters['type'] !== '') {
                $whereClauses[] = "p.is_system = ?";
                $params[] = $filters['type'] === 'system' ? 1 : 0;
                $types .= "i";
            }

            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(p.title LIKE ? OR p.slug LIKE ? OR p.excerpt LIKE ?)";
                $params[] = $wild;
                $params[] = $wild;
                $params[] = $wild;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Count total
            $stmtC = $db->prepare("SELECT COUNT(*) FROM static_pages p WHERE $whereSql");
            if ($types !== "") {
                $stmtC->bind_param($types, ...$params);
            }
            $stmtC->execute();
            $total = (int)$stmtC->get_result()->fetch_row()[0];
            $stmtC->close();

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Fetch records
            $sql = "
                SELECT 
                    p.*,
                    a.name AS updated_by_name
                FROM static_pages p
                LEFT JOIN admins a ON p.updated_by = a.id
                WHERE $whereSql
                ORDER BY p.is_system DESC, p.title ASC
                LIMIT ? OFFSET ?
            ";
            $pagParams = array_merge($params, [$perPage, $offset]);
            $pagTypes = $types . "ii";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($pagTypes, ...$pagParams);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $pages = [];
            foreach ($rows as $r) {
                $pages[] = [
                    'id'               => (int)$r['id'],
                    'slug'             => $r['slug'],
                    'title'            => $r['title'],
                    'excerpt'          => $r['excerpt'] ?? '',
                    'content'          => $r['content'] ?? '',
                    'word_count'       => str_word_count(strip_tags($r['content'] ?? '')),
                    'meta_title'       => $r['meta_title'] ?? '',
                    'meta_description' => $r['meta_description'] ?? '',
                    'meta_keywords'    => $r['meta_keywords'] ?? '',
                    'is_active'        => (bool)$r['is_active'],
                    'is_system'        => (bool)$r['is_system'],
                    'updated_by'       => $r['updated_by'] ? (int)$r['updated_by'] : null,
                    'updated_by_name'  => $r['updated_by_name'] ?? 'System',
                    'updated_at'       => $r['updated_at'],
                    'created_at'       => $r['created_at'] ?? $r['updated_at'],
                ];
            }

            return [
                'pages'      => $pages,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages,
                ]
            ];
        } catch (Exception $e) {
            error_log("Page::getPages error: " . $e->getMessage());
            return [
                'pages'      => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false]
            ];
        }
    }

    /**
     * Retrieve single page by primary ID.
     */
    public static function getPage(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT p.*, a.name AS updated_by_name
                FROM static_pages p
                LEFT JOIN admins a ON p.updated_by = a.id
                WHERE p.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $page = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$page) return null;

            $page['id'] = (int)$page['id'];
            $page['is_active'] = (bool)$page['is_active'];
            $page['is_system'] = (bool)$page['is_system'];
            $page['word_count'] = str_word_count(strip_tags($page['content'] ?? ''));

            return $page;
        } catch (Exception $e) {
            error_log("Page::getPage error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve page by slug (for public storefront or admin preview).
     */
    public static function getPageBySlug(string $slug, bool $activeOnly = true): ?array {
        try {
            $db = Database::connect();
            $slug = strtolower(trim($slug));
            $sql = "SELECT * FROM static_pages WHERE slug = ?" . ($activeOnly ? " AND is_active = 1" : "");
            $stmt = $db->prepare($sql);
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            $page = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$page) return null;

            $page['id'] = (int)$page['id'];
            $page['is_active'] = (bool)$page['is_active'];
            $page['is_system'] = (bool)$page['is_system'];
            $page['word_count'] = str_word_count(strip_tags($page['content'] ?? ''));

            return $page;
        } catch (Exception $e) {
            error_log("Page::getPageBySlug error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new custom static page.
     */
    public static function createPage(array $data, int $adminId = 1): array {
        try {
            $db = Database::connect();
            $title = trim($data['title'] ?? '');
            $slug  = trim($data['slug'] ?? '');
            $content = trim($data['content'] ?? '');
            $excerpt = trim($data['excerpt'] ?? '');
            $metaTitle = trim($data['meta_title'] ?? '');
            $metaDesc  = trim($data['meta_description'] ?? '');
            $metaKeys  = trim($data['meta_keywords'] ?? '');
            $isActive  = isset($data['is_active']) ? (!empty($data['is_active']) ? 1 : 0) : 1;

            if ($title === '') {
                return ['success' => false, 'message' => 'Page title is required.'];
            }

            if ($slug === '') {
                $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', str_replace(' ', '-', $title)));
                $slug = trim($slug, '-');
            } else {
                $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $slug));
                $slug = trim($slug, '-');
            }

            if ($slug === '') {
                $slug = 'page-' . time();
            }

            // Check slug uniqueness
            $checkStmt = $db->prepare("SELECT id FROM static_pages WHERE slug = ?");
            $checkStmt->bind_param("s", $slug);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $slug .= '-' . time();
            }
            $checkStmt->close();

            $stmt = $db->prepare("
                INSERT INTO static_pages 
                (slug, title, excerpt, content, meta_title, meta_description, meta_keywords, is_active, is_system, updated_by, updated_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW(), NOW())
            ");
            $stmt->bind_param("sssssssii", $slug, $title, $excerpt, $content, $metaTitle, $metaDesc, $metaKeys, $isActive, $adminId);
            $success = $stmt->execute();
            $newId = $success ? $db->insert_id : 0;
            $stmt->close();

            return [
                'success' => $success,
                'id'      => $newId,
                'slug'    => $slug,
                'message' => $success ? "Page '{$title}' created successfully." : 'Failed to create static page.'
            ];
        } catch (Exception $e) {
            error_log("Page::createPage error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update an existing static page.
     */
    public static function updatePage(int $id, array $data, int $adminId = 1): array {
        try {
            $db = Database::connect();
            $existing = self::getPage($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Page not found.'];
            }

            $title = trim($data['title'] ?? $existing['title']);
            $content = isset($data['content']) ? trim($data['content']) : $existing['content'];
            $excerpt = isset($data['excerpt']) ? trim($data['excerpt']) : $existing['excerpt'];
            $metaTitle = isset($data['meta_title']) ? trim($data['meta_title']) : $existing['meta_title'];
            $metaDesc  = isset($data['meta_description']) ? trim($data['meta_description']) : $existing['meta_description'];
            $metaKeys  = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : $existing['meta_keywords'];
            $isActive  = isset($data['is_active']) ? (!empty($data['is_active']) ? 1 : 0) : (int)$existing['is_active'];

            // System pages preserve original slugs
            if ($existing['is_system']) {
                $slug = $existing['slug'];
            } else {
                $slug = !empty($data['slug']) ? strtolower(trim($data['slug'])) : $existing['slug'];
                $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
                $slug = trim($slug, '-');

                // Check slug uniqueness across other pages
                $checkStmt = $db->prepare("SELECT id FROM static_pages WHERE slug = ? AND id != ?");
                $checkStmt->bind_param("si", $slug, $id);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows > 0) {
                    $checkStmt->close();
                    return ['success' => false, 'message' => "Another page is already using the slug '{$slug}'."];
                }
                $checkStmt->close();
            }

            $stmt = $db->prepare("
                UPDATE static_pages
                SET slug = ?, title = ?, excerpt = ?, content = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, is_active = ?, updated_by = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssssssiii", $slug, $title, $excerpt, $content, $metaTitle, $metaDesc, $metaKeys, $isActive, $adminId, $id);
            $success = $stmt->execute();
            $stmt->close();

            return [
                'success' => $success,
                'message' => $success ? "Page '{$title}' updated successfully." : 'Failed to update page.'
            ];
        } catch (Exception $e) {
            error_log("Page::updatePage error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Toggle page publication status (Published / Draft).
     */
    public static function toggleStatus(int $id, bool $active, int $adminId = 1): array {
        try {
            $db = Database::connect();
            $page = self::getPage($id);
            if (!$page) {
                return ['success' => false, 'message' => 'Page not found.'];
            }

            $statusInt = $active ? 1 : 0;
            $stmt = $db->prepare("UPDATE static_pages SET is_active = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("iii", $statusInt, $adminId, $id);
            $success = $stmt->execute();
            $stmt->close();

            $statusText = $active ? 'published' : 'moved to drafts';
            return [
                'success' => $success,
                'message' => $success ? "Page '{$page['title']}' has been {$statusText}." : 'Failed to update page status.'
            ];
        } catch (Exception $e) {
            error_log("Page::toggleStatus error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete a custom page (guarded against deleting system pages).
     */
    public static function deletePage(int $id): array {
        try {
            $db = Database::connect();
            $page = self::getPage($id);
            if (!$page) {
                return ['success' => false, 'message' => 'Page not found.'];
            }

            if (!empty($page['is_system'])) {
                return [
                    'success' => false,
                    'message' => "Protected Page: Built-in system page '{$page['title']}' cannot be deleted because core store navigation depends on it."
                ];
            }

            $stmt = $db->prepare("DELETE FROM static_pages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();

            return [
                'success' => $success,
                'message' => $success ? "Page '{$page['title']}' permanently deleted." : 'Failed to delete page.'
            ];
        } catch (Exception $e) {
            error_log("Page::deletePage error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // 2. CONTACT ENQUIRIES MANAGEMENT
    // =========================================================================

    /**
     * Store customer message from the public Contact Us page.
     */
    public static function saveContactEnquiry(array $data): array {
        try {
            $db = Database::connect();
            $name    = trim($data['name'] ?? '');
            $email   = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $phone   = trim($data['phone'] ?? '');
            $subject = trim($data['subject'] ?? 'General Customer Inquiry');
            $message = trim($data['message'] ?? '');

            if ($name === '') {
                return ['success' => false, 'message' => 'Your full name is required.'];
            }

            if (!$email) {
                return ['success' => false, 'message' => 'A valid email address is required.'];
            }

            if ($message === '' || strlen($message) < 10) {
                return ['success' => false, 'message' => 'Please provide a detailed inquiry message (minimum 10 characters).'];
            }

            $stmt = $db->prepare("
                INSERT INTO contact_enquiries (name, email, phone, subject, message, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
            $success = $stmt->execute();
            $newId = $success ? $db->insert_id : 0;
            $stmt->close();

            return [
                'success' => $success,
                'id'      => $newId,
                'message' => $success
                    ? 'Thank you for reaching out to Jiyaji LX. Our royal concierge team will contact you within 24 hours.'
                    : 'We could not transmit your message. Please try again or reach us on WhatsApp.'
            ];
        } catch (Exception $e) {
            error_log("Page::saveContactEnquiry error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Retrieve paginated contact enquiries for the admin CMS inbox.
     */
    public static function getContactEnquiries(array $filters = [], int $page = 1, int $perPage = 10): array {
        try {
            $db = Database::connect();
            $whereClauses = ["1=1"];
            $params = [];
            $types = "";

            if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
                $whereClauses[] = "is_read = ?";
                $params[] = $filters['status'] === 'read' ? 1 : 0;
                $types .= "i";
            }

            if (!empty($filters['search'])) {
                $wild = "%" . trim($filters['search']) . "%";
                $whereClauses[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)";
                $params[] = $wild;
                $params[] = $wild;
                $params[] = $wild;
                $params[] = $wild;
                $params[] = $wild;
                $types .= "sssss";
            }

            $whereSql = implode(" AND ", $whereClauses);

            // Count
            $stmtC = $db->prepare("SELECT COUNT(*) FROM contact_enquiries WHERE $whereSql");
            if ($types !== "") {
                $stmtC->bind_param($types, ...$params);
            }
            $stmtC->execute();
            $total = (int)$stmtC->get_result()->fetch_row()[0];
            $stmtC->close();

            $totalPages = max(1, (int)ceil($total / $perPage));
            $page = max(1, min($page, $totalPages));
            $offset = ($page - 1) * $perPage;

            // Fetch
            $sql = "SELECT * FROM contact_enquiries WHERE $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
            $pagParams = array_merge($params, [$perPage, $offset]);
            $pagTypes = $types . "ii";

            $stmt = $db->prepare($sql);
            $stmt->bind_param($pagTypes, ...$pagParams);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return [
                'enquiries'  => $rows,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'has_prev'     => $page > 1,
                    'has_next'     => $page < $totalPages,
                ]
            ];
        } catch (Exception $e) {
            error_log("Page::getContactEnquiries error: " . $e->getMessage());
            return [
                'enquiries'  => [],
                'pagination' => ['total' => 0, 'per_page' => $perPage, 'current_page' => 1, 'total_pages' => 1, 'has_prev' => false, 'has_next' => false]
            ];
        }
    }

    /**
     * Toggle read/unread status on a customer inquiry.
     */
    public static function toggleEnquiryRead(int $id, bool $isRead): bool {
        try {
            $db = Database::connect();
            $readInt = $isRead ? 1 : 0;
            $stmt = $db->prepare("UPDATE contact_enquiries SET is_read = ? WHERE id = ?");
            $stmt->bind_param("ii", $readInt, $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("Page::toggleEnquiryRead error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete customer inquiry.
     */
    public static function deleteEnquiry(int $id): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM contact_enquiries WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("Page::deleteEnquiry error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 3. EXECUTIVE KPIS
    // =========================================================================

    /**
     * Aggregate executive metrics for the CMS command center.
     */
    public static function getPageKPIs(): array {
        self::ensureDefaults();

        try {
            $db = Database::connect();

            $totalPages = (int)$db->query("SELECT COUNT(*) FROM static_pages")->fetch_row()[0];
            $publishedPages = (int)$db->query("SELECT COUNT(*) FROM static_pages WHERE is_active = 1")->fetch_row()[0];
            $draftPages = (int)$db->query("SELECT COUNT(*) FROM static_pages WHERE is_active = 0")->fetch_row()[0];
            $systemPages = (int)$db->query("SELECT COUNT(*) FROM static_pages WHERE is_system = 1")->fetch_row()[0];
            $customPages = (int)$db->query("SELECT COUNT(*) FROM static_pages WHERE is_system = 0")->fetch_row()[0];

            // Inquiries metrics
            $totalEnquiries = (int)$db->query("SELECT COUNT(*) FROM contact_enquiries")->fetch_row()[0];
            $unreadEnquiries = (int)$db->query("SELECT COUNT(*) FROM contact_enquiries WHERE is_read = 0")->fetch_row()[0];

            return [
                'total_pages'      => $totalPages,
                'published_pages'  => $publishedPages,
                'draft_pages'      => $draftPages,
                'system_pages'     => $systemPages,
                'custom_pages'     => $customPages,
                'total_enquiries'  => $totalEnquiries,
                'unread_enquiries' => $unreadEnquiries,
            ];
        } catch (Exception $e) {
            error_log("Page::getPageKPIs error: " . $e->getMessage());
            return [
                'total_pages'      => 0,
                'published_pages'  => 0,
                'draft_pages'      => 0,
                'system_pages'     => 0,
                'custom_pages'     => 0,
                'total_enquiries'  => 0,
                'unread_enquiries' => 0,
            ];
        }
    }

    // =========================================================================
    // 4. ENSURE DEFAULTS & SEEDING (7 CORE PAGES)
    // =========================================================================

    /**
     * Idempotently seeds 7 comprehensive baseline pages matching the luxury Indian ethnic aesthetic.
     */
    public static function ensureDefaults(): void {
        try {
            $db = Database::connect();

            $defaults = [
                [
                    'slug'             => 'about-us',
                    'title'            => 'About Us — The House of Jiyaji LX',
                    'excerpt'          => 'Crafting bespoke royal Indian ethnic couture with generational master artisans from the heart of Rajasthan.',
                    'content'          => '<h2>The Heritage of Jiyaji LX</h2>
<p>Welcome to <strong>Jiyaji LX</strong>, where timeless Indian heritage meets contemporary royal couture. Born from the rich artistic traditions of Jaipur, Rajasthan, our maison is dedicated to resurrecting the grandeur of ancestral Indian dressing for modern connoisseurs of elegance.</p>

<h3>Artisanal Craftsmanship & Generational Mastery</h3>
<p>Every Jiyaji creation represents weeks of devoted needlework by hereditary master craftsmen (<em>kaarigars</em>). From intricate Zardozi, Gota Patti, and Aari embroidery to handwoven Chanderi, Banarasi Katan silks, and pure Raw Silks, each garment embodies centuries of unhurried handloom excellence.</p>

<blockquote>"True luxury is not merely worn; it is inherited. Every stitch carries the heartbeat of Rajasthan\'s royal ateliers."</blockquote>

<h3>Our Couture Philosophy</h3>
<ul>
    <li><strong>Authentic Handlooms:</strong> Sourced directly from weaver cooperatives in Varanasi, Chanderi, and Kota.</li>
    <li><strong>Made-to-Measure Excellence:</strong> Bespoke silhouettes calibrated to individual measurements with hand-finished linings.</li>
    <li><strong>Ethical Patronage:</strong> Ensuring fair living wages and preserving ancient textile crafts for future generations.</li>
</ul>

<h3>The Flagship Experience</h3>
<p>Visit our flagship boutique in the heritage quarter of Jaipur to experience personal VIP styling consultations, touch our archive fabrics, and commission one-of-a-kind bridal and festive ensembles.</p>',
                    'meta_title'       => 'About Jiyaji LX | Royal Indian Ethnic Couture & Bespoke Craftsmanship',
                    'meta_description' => 'Discover the regal heritage of Jiyaji LX — luxurious handloom sherwanis, lehengas, and royal ethnic attire crafted by master artisans in Jaipur, Rajasthan.',
                    'meta_keywords'    => 'Jiyaji LX, luxury ethnic wear, royal sherwani, designer lehengas, handloom silks, Jaipur couture, Zardozi craftsmanship',
                    'is_system'        => 1,
                ],

                [
                    'slug'             => 'privacy-policy',
                    'title'            => 'Privacy & Confidentiality Policy',
                    'excerpt'          => 'How Jiyaji LX safeguards your personal information, bespoke measurements, and payment security.',
                    'content'          => '<h2>Your Privacy Matters to Jiyaji LX</h2>
<p>At <strong>Jiyaji LX</strong>, we hold client trust and discretion in the highest regard. This Privacy Policy delineates how we collect, safeguard, and utilize your personal information across our website, VIP styling concierge, and retail ateliers in accordance with the Digital Personal Data Protection (DPDP) Act, 2023.</p>

<h3>1. Information We Collect</h3>
<ul>
    <li><strong>Identity & Contact Details:</strong> Name, delivery address, billing address, phone number, and email.</li>
    <li><strong>Bespoke Sizing & Measurements:</strong> Tailoring profiles, neck/chest/waist metrics, and alteration preferences.</li>
    <li><strong>Transactional Records:</strong> Order histories, encrypted payment gateway references (we do not store CVV or full card numbers).</li>
</ul>

<h3>2. How We Protect Your Data</h3>
<p>All sensitive communications are encrypted using 256-bit SSL technology. Payment processing is tokenized through PCI-DSS Level 1 compliant gateway partners (Razorpay). Your styling measurements and personal archives are stored within secure, access-restricted database systems.</p>

<h3>3. Client Rights</h3>
<p>You reserve the right to review, rectify, or request deletion of your account archives at any time by contacting our Privacy Concierge at <a href="mailto:privacy@jiyaji.com">privacy@jiyaji.com</a>.</p>',
                    'meta_title'       => 'Privacy Policy | Jiyaji LX Client Confidentiality',
                    'meta_description' => 'Review the official Privacy Policy of Jiyaji LX. Learn how your bespoke measurements, personal identity, and payment security are safeguarded.',
                    'meta_keywords'    => 'privacy policy, data protection, client confidentiality, secure payment, Jiyaji LX legal',
                    'is_system'        => 1,
                ],

                [
                    'slug'             => 'terms-conditions',
                    'title'            => 'Terms of Service & Bespoke Agreement',
                    'excerpt'          => 'Conditions governing online orders, bespoke garment commissions, intellectual property, and deliveries.',
                    'content'          => '<h2>Agreement of Service</h2>
<p>By accessing or placing an order through <strong>Jiyaji LX</strong>, you agree to comply with and be bound by the following terms and conditions. Please review them thoroughly before finalizing bespoke commissions or retail purchases.</p>

<h3>1. Bespoke & Ready-to-Wear Orders</h3>
<p>Due to the artisanal handcrafting involved, ready-to-wear items are fulfilled as per catalog specifications, while bespoke orders undergo custom pattern-making based on provided measurements. Subtle variations in dye lots, embroidery motifs, and weave textures are inherent signatures of authentic handlooms.</p>

<h3>2. Pricing & Currency</h3>
<p>All prices are listed in Indian Rupees (₹ INR) and include applicable Goods and Services Tax (GST). Jiyaji LX reserves the right to adjust catalog pricing without prior notification; orders already confirmed will remain unaffected.</p>

<h3>3. Intellectual Property</h3>
<p>All designs, photographs, typography, brand assets, and custom garment silhouettes are the proprietary intellectual property of Jiyaji LX and may not be reproduced without written authorization.</p>

<h3>4. Legal Jurisdiction</h3>
<p>Any disputes arising from transactions on this platform shall be governed by the laws of India and subject to the exclusive jurisdiction of the courts in Jaipur, Rajasthan.</p>',
                    'meta_title'       => 'Terms & Conditions | Jiyaji LX Legal & Purchasing Agreement',
                    'meta_description' => 'Official Terms of Service and purchasing agreement for Jiyaji LX couture orders, custom tailoring, and intellectual property guidelines.',
                    'meta_keywords'    => 'terms and conditions, bespoke agreement, purchasing policy, Jiyaji LX legal',
                    'is_system'        => 1,
                ],

                [
                    'slug'             => 'shipping-policy',
                    'title'            => 'Shipping, Transit & White-Glove Delivery',
                    'excerpt'          => 'Pan-India express logistics, dispatch timelines, and complimentary luxury transit policies.',
                    'content'          => '<h2>White-Glove Domestic Shipping</h2>
<p>Every Jiyaji LX garment is pressed, hand-inspected, and packaged in our signature keepsake gift boxes with breathable garment preservation covers before dispatch.</p>

<h3>Dispatch Timelines</h3>
<ul>
    <li><strong>Ready-to-Wear Catalog:</strong> Dispatched within 24 to 48 hours of payment verification.</li>
    <li><strong>Bespoke & Tailored Ensembles:</strong> Require 7 to 14 business days for custom pattern-making, hand embroidery, and fitting inspection.</li>
</ul>

<h3>Complimentary Luxury Delivery</h3>
<p>We are proud to provide <strong>Complimentary Express Shipping</strong> storewide on all domestic orders totaling <strong>₹2,999 or greater</strong>. For orders below this threshold, a flat nominal delivery fee of ₹150 applies.</p>

<h3>Carrier Partners & Tracking</h3>
<p>Shipments are consigned exclusively via premium express air carriers (<strong>BlueDart Express, Delhivery Air, and Shiprocket</strong>). Upon dispatch, tracking Air Waybill (AWB) links are transmitted via SMS and email for real-time transit tracking.</p>',
                    'meta_title'       => 'Shipping & Delivery Policy | Jiyaji LX White-Glove Logistics',
                    'meta_description' => 'Explore Jiyaji LX shipping and delivery policies — Pan-India express air transit, complimentary shipping above ₹2,999, and dispatch timelines.',
                    'meta_keywords'    => 'shipping policy, express delivery, complimentary shipping, BlueDart, luxury packaging, Jiyaji LX transit',
                    'is_system'        => 1,
                ],

                [
                    'slug'             => 'returns-policy',
                    'title'            => 'Returns, Exchanges & Client Satisfaction',
                    'excerpt'          => '7-day effortless reverse logistics for unworn, unaltered luxury attire.',
                    'content'          => '<h2>Hassle-Free 7-Day Returns</h2>
<p>At Jiyaji LX, your satisfaction with our craftsmanship is paramount. If you are not completely delighted with your ready-to-wear ensemble, we offer an effortless <strong>7-day return and exchange window</strong> from the date of package delivery.</p>

<h3>Eligibility Guidelines</h3>
<ul>
    <li>Garments must remain unworn, unwashed, and undamaged with all original luxury tags, garment bags, and gift boxes intact.</li>
    <li>Proof of purchase (invoice or order confirmation email) must accompany the return.</li>
    <li><strong>Non-Returnable Items:</strong> Custom-tailored bespoke creations, custom altered pieces, and personalized accessories cannot be returned due to individual customization.</li>
</ul>

<h3>Reverse Pickup Process</h3>
<ol>
    <li>Initiate a return request via your client portal or email our concierge at <a href="mailto:returns@jiyaji.com">returns@jiyaji.com</a>.</li>
    <li>Our logistics partner will arrange a doorstep reverse pickup from your registered address within 24-48 hours.</li>
    <li>Upon receipt and quality inspection at our Jaipur atelier, refunds are processed within 3-5 business days to your original payment method or issued as store credit.</li>
</ol>',
                    'meta_title'       => 'Returns & Refund Policy | Jiyaji LX 7-Day Return Window',
                    'meta_description' => 'Review the Jiyaji LX returns and exchange policy. Enjoy 7-day hassle-free reverse pickups, full refunds, and store credit options.',
                    'meta_keywords'    => 'returns policy, refund policy, exchange policy, 7-day returns, luxury customer service',
                    'is_system'        => 1,
                ],

                [
                    'slug'             => 'faq',
                    'title'            => 'Frequently Asked Questions & Client Assistance',
                    'excerpt'          => 'Answers regarding custom sizing, orders, tracking, fabric care, and payment rails.',
                    'content'          => '<h2>Client Concierge — FAQ</h2>
<p>Find answers to common questions about ordering, sizing, payments, and garment care at Jiyaji LX.</p>

<h3>1. Orders & Custom Sizing</h3>
<p><strong>Q: How do I select the right size for ethnic couture?</strong><br>
A: Every garment page includes an exact measurement chart in inches and centimeters. For tailored fits, our stylists can schedule a virtual measuring session via WhatsApp Concierge.</p>

<p><strong>Q: Can I request customized alterations before dispatch?</strong><br>
A: Yes! Simply add alteration notes during checkout or contact us within 12 hours of placing your order.</p>

<h3>2. Payments & Cash on Delivery (COD)</h3>
<p><strong>Q: What payment methods do you accept?</strong><br>
A: We accept all major Indian Credit/Debit Cards, UPI (Google Pay, PhonePe, Paytm), Net Banking, and Doorstep Cash on Delivery (COD) for eligible pincodes up to ₹40,000.</p>

<h3>3. Garment Care & Preservation</h3>
<p><strong>Q: How should I care for pure silk and embroidered garments?</strong><br>
A: All Jiyaji LX couture pieces require professional <strong>Dry Clean Only</strong>. Store garments in the breathable cloth covers provided, away from direct sunlight.</p>',
                    'meta_title'       => 'FAQ & Client Care | Jiyaji LX Helpful Questions & Answers',
                    'meta_description' => 'Frequently asked questions about Jiyaji LX sizing, orders, payment options, delivery tracking, and bespoke garment care.',
                    'meta_keywords'    => 'Jiyaji LX FAQ, ethnic wear sizing, dry clean care, COD questions, bespoke tailoring help',
                    'is_system'        => 1,
                ],

                [
                    'slug'             => 'contact-us',
                    'title'            => 'Contact Us & Flagship Concierge',
                    'excerpt'          => 'Connect with our royal stylists, visit our Jaipur flagship, or message our VIP customer care team.',
                    'content'          => '<h2>Connect with Jiyaji LX</h2>
<p>Whether you require styling advice for an upcoming wedding, need assistance with an existing order, or wish to book an exclusive atelier consultation, our concierge team is delighted to assist you.</p>

<h3>Jaipur Flagship Atelier</h3>
<p>
<strong>The House of Jiyaji LX</strong><br>
Plot 42, Heritage Craft Corridor, Near Johari Bazaar<br>
Jaipur, Rajasthan 302003, India<br>
<strong>Hours:</strong> Monday – Saturday: 10:30 AM – 8:00 PM IST (Closed Sundays)
</p>

<h3>Direct Assistance</h3>
<ul>
    <li><strong>Client Care Helpline:</strong> +91 141 234 5678 (10 AM – 7 PM IST)</li>
    <li><strong>WhatsApp Styling Concierge:</strong> +91 98290 12345 (24/7 Chat)</li>
    <li><strong>General Enquiries:</strong> <a href="mailto:concierge@jiyaji.com">concierge@jiyaji.com</a></li>
    <li><strong>Press & Partnerships:</strong> <a href="mailto:press@jiyaji.com">press@jiyaji.com</a></li>
</ul>

<p><em>You may also send us an inquiry directly using the contact form below.</em></p>',
                    'meta_title'       => 'Contact Jiyaji LX | Jaipur Flagship Atelier & Styling Concierge',
                    'meta_description' => 'Get in touch with Jiyaji LX. Visit our Jaipur atelier, chat with our WhatsApp styling concierge, or submit an inquiry online.',
                    'meta_keywords'    => 'contact Jiyaji LX, Jaipur boutique, customer concierge, WhatsApp styling, store address',
                    'is_system'        => 1,
                ],
            ];

            $stmt = $db->prepare("
                INSERT INTO static_pages 
                (slug, title, excerpt, content, meta_title, meta_description, meta_keywords, is_active, is_system, updated_by, updated_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    is_system = VALUES(is_system)
            ");

            foreach ($defaults as $d) {
                // Check if already exists
                $check = $db->prepare("SELECT id FROM static_pages WHERE slug = ?");
                $check->bind_param("s", $d['slug']);
                $check->execute();
                $exists = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$exists) {
                    $stmt->bind_param(
                        "sssssssi",
                        $d['slug'],
                        $d['title'],
                        $d['excerpt'],
                        $d['content'],
                        $d['meta_title'],
                        $d['meta_description'],
                        $d['meta_keywords'],
                        $d['is_system']
                    );
                    $stmt->execute();
                } else {
                    // Ensure system flag is 1 for default pages
                    $upd = $db->prepare("UPDATE static_pages SET is_system = 1 WHERE slug = ?");
                    $upd->bind_param("s", $d['slug']);
                    $upd->execute();
                    $upd->close();
                }
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Page::ensureDefaults error: " . $e->getMessage());
        }
    }
}
