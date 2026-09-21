<?php
/**
 * Static CMS Pages System — Automated Test Suite
 * Validates Page Model, Database Schema, 7 Baseline Pages, Custom Page CRUD,
 * System Immutability Safeguards, SEO Metadata, Customer Contact Enquiries,
 * Admin Controller & Public Storefront Views.
 */

define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin'] = ['id' => 1, 'name' => 'Root Super Admin', 'email' => 'admin@jiyaji.com', 'role' => 'super_admin'];

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/Page.php';
require_once __DIR__ . '/../app/Models/Staff.php';
require_once __DIR__ . '/../app/Controllers/Admin/PageController.php';

use App\Config\Database;
use App\Models\Page;
use App\Models\Staff;
use App\Controllers\Admin\PageController;

class StaticPagesCMSTestSuite {
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];
    private \mysqli $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    private function assert(string $desc, bool $condition, string $details = ''): void {
        if ($condition) {
            $this->passed++;
            echo "  \033[32m✔ PASS:\033[0m {$desc}\n";
        } else {
            $this->failed++;
            $msg = "  \033[31m✘ FAIL:\033[0m {$desc}" . ($details ? " — {$details}" : "");
            echo "{$msg}\n";
            $this->errors[] = $msg;
        }
    }

    public function runAll(): void {
        echo "\n\033[1;34m============================================================\033[0m\n";
        echo "\033[1;34m  JIYAJI LX — STATIC CMS PAGES TEST SUITE (FEATURE 14)      \033[0m\n";
        echo "\033[1;34m============================================================\033[0m\n\n";

        $this->testSchemaIntegrity();
        $this->testBaselinePagesSeeding();
        $this->testCustomPageCRUD();
        $this->testSystemPageSafeguards();
        $this->testSEOMetadataHandling();
        $this->testCustomerContactEnquiries();
        $this->testPageKPICalculations();
        $this->testControllerAndStorefrontViews();

        echo "\n\033[1;34m============================================================\033[0m\n";
        echo "  Test Summary: \033[32m{$this->passed} Passed\033[0m, ";
        if ($this->failed > 0) {
            echo "\033[31m{$this->failed} Failed\033[0m\n";
            echo "\033[1;31mFailures:\033[0m\n" . implode("\n", $this->errors) . "\n";
        } else {
            echo "\033[32m0 Failed (100% Success Rate)\033[0m\n";
        }
        echo "\033[1;34m============================================================\033[0m\n\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    // =========================================================================
    // 1. SCHEMA INTEGRITY
    // =========================================================================
    private function testSchemaIntegrity(): void {
        echo "\033[1m[1/8] Testing Database Schema Integrity...\033[0m\n";

        $cols = [];
        $res = $this->db->query("DESCRIBE static_pages");
        while ($r = $res->fetch_assoc()) {
            $cols[] = strtolower($r['Field']);
        }

        $this->assert("static_pages table has slug column", in_array('slug', $cols));
        $this->assert("static_pages table has title column", in_array('title', $cols));
        $this->assert("static_pages table has excerpt column", in_array('excerpt', $cols));
        $this->assert("static_pages table has content column", in_array('content', $cols));
        $this->assert("static_pages table has meta_title column", in_array('meta_title', $cols));
        $this->assert("static_pages table has meta_description column", in_array('meta_description', $cols));
        $this->assert("static_pages table has meta_keywords column", in_array('meta_keywords', $cols));
        $this->assert("static_pages table has is_system column", in_array('is_system', $cols));
        $this->assert("static_pages table has created_at column", in_array('created_at', $cols));

        $resCE = $this->db->query("SHOW TABLES LIKE 'contact_enquiries'");
        $this->assert("contact_enquiries table exists", $resCE && $resCE->num_rows > 0);
    }

    // =========================================================================
    // 2. BASELINE PAGES SEEDING
    // =========================================================================
    private function testBaselinePagesSeeding(): void {
        echo "\n\033[1m[2/8] Testing 7 Baseline Pages Seeding (Feature 14)...\033[0m\n";

        Page::ensureDefaults();

        $expectedSlugs = [
            'about-us',
            'privacy-policy',
            'terms-conditions',
            'shipping-policy',
            'returns-policy',
            'faq',
            'contact-us'
        ];

        foreach ($expectedSlugs as $slug) {
            $page = Page::getPageBySlug($slug, false);
            $this->assert("Baseline page '{$slug}' exists in database", $page !== null);
            $this->assert("Baseline page '{$slug}' is marked is_system = 1", $page && $page['is_system'] === true);
            $this->assert("Baseline page '{$slug}' is published (is_active = 1)", $page && $page['is_active'] === true);
            $this->assert("Baseline page '{$slug}' has non-empty content", $page && strlen($page['content']) > 50);
        }
    }

    // =========================================================================
    // 3. CUSTOM PAGE CRUD
    // =========================================================================
    private function testCustomPageCRUD(): void {
        echo "\n\033[1m[3/8] Testing Custom Page Creation, Slugs & Content Updates...\033[0m\n";

        $testTitle = 'Artisanal Silk Archive ' . time();
        $createRes = Page::createPage([
            'title'            => $testTitle,
            'excerpt'          => 'Discover our curated archive of pure mulberry silks.',
            'content'          => '<p>Every piece in our silk archive represents months of meticulous weaving.</p>',
            'meta_title'       => 'Silk Archive | Jiyaji LX',
            'meta_description' => 'Browse royal mulberry silks handcrafted in Varanasi.',
            'meta_keywords'    => 'silk, handloom, jiyaji',
            'is_active'        => 1,
        ], 1);

        $this->assert("Custom page created successfully", $createRes['success'], $createRes['message'] ?? '');
        $pageId = $createRes['id'] ?? 0;
        $this->assert("Created page ID > 0", $pageId > 0);

        // Fetch page
        $page = Page::getPage($pageId);
        $this->assert("Fetched page matches title", $page && $page['title'] === $testTitle);
        $this->assert("Fetched page marked is_system = false", $page && $page['is_system'] === false);
        $this->assert("Word count calculated accurately", $page && $page['word_count'] > 0);

        // Update page
        $newTitle = 'Artisanal Silk & Zari Archive ' . time();
        $updRes = Page::updatePage($pageId, [
            'title'   => $newTitle,
            'excerpt' => 'Updated excerpt with golden zari detailing.',
            'content' => '<p>Updated content reflecting pure silver zari threads.</p>',
        ], 1);

        $this->assert("Custom page updated successfully", $updRes['success']);
        $updatedPage = Page::getPage($pageId);
        $this->assert("Updated page reflects new title", $updatedPage['title'] === $newTitle);

        // Toggle status to Draft
        $toggleRes = Page::toggleStatus($pageId, false, 1);
        $this->assert("Page toggled to draft", $toggleRes['success']);
        $draftPage = Page::getPage($pageId);
        $this->assert("Page is now inactive", !$draftPage['is_active']);

        // Inactive page should not be returned by getPageBySlug(activeOnly=true)
        $publicLookup = Page::getPageBySlug($draftPage['slug'], true);
        $this->assert("Draft page not visible to public storefront", $publicLookup === null);

        // Delete custom page
        $delRes = Page::deletePage($pageId);
        $this->assert("Custom page deleted successfully", $delRes['success']);
        $this->assert("Deleted page no longer exists", Page::getPage($pageId) === null);
    }

    // =========================================================================
    // 4. SYSTEM PAGE SAFEGUARDS
    // =========================================================================
    private function testSystemPageSafeguards(): void {
        echo "\n\033[1m[4/8] Testing System Page Immutability & Safeguards...\033[0m\n";

        $aboutPage = Page::getPageBySlug('about-us', false);
        $this->assert("Found About Us system page", $aboutPage !== null);

        // Safeguard 1: Attempt to delete system page must fail
        $delSysRes = Page::deletePage($aboutPage['id']);
        $this->assert("Deletion of system page 'about-us' is prevented", !$delSysRes['success']);
        $this->assert("System page remains in database", Page::getPage($aboutPage['id']) !== null);

        // Safeguard 2: Attempt to change system page slug is ignored
        $slugChangeRes = Page::updatePage($aboutPage['id'], [
            'slug'  => 'illegal-modified-slug',
            'title' => $aboutPage['title'],
        ], 1);
        $this->assert("Update succeeded", $slugChangeRes['success']);
        $recheckAbout = Page::getPage($aboutPage['id']);
        $this->assert("System page slug remains 'about-us'", $recheckAbout['slug'] === 'about-us');
    }

    // =========================================================================
    // 5. SEO METADATA HANDLING
    // =========================================================================
    private function testSEOMetadataHandling(): void {
        echo "\n\033[1m[5/8] Testing SEO Title, Description & Keywords...\033[0m\n";

        $termsPage = Page::getPageBySlug('terms-conditions', false);
        $this->assert("Terms page has meta_title", !empty($termsPage['meta_title']));
        $this->assert("Terms page has meta_description", !empty($termsPage['meta_description']));
        $this->assert("Terms page has meta_keywords", !empty($termsPage['meta_keywords']));

        // Custom page with specific SEO fields
        $seoTitle = 'Exclusive Wedding Trousseau Guide';
        $seoDesc = 'Discover bespoke wedding lehengas and royal sherwanis for luxury Indian weddings.';
        $seoKeys = 'wedding lehenga, bridal sherwani, trousseau guide';

        $cRes = Page::createPage([
            'title'            => 'Bridal Guide ' . time(),
            'meta_title'       => $seoTitle,
            'meta_description' => $seoDesc,
            'meta_keywords'    => $seoKeys,
            'content'          => '<p>Comprehensive bridal planning guide.</p>',
        ], 1);

        $created = Page::getPage($cRes['id']);
        $this->assert("Stored meta_title matches input", $created['meta_title'] === $seoTitle);
        $this->assert("Stored meta_description matches input", $created['meta_description'] === $seoDesc);
        $this->assert("Stored meta_keywords matches input", $created['meta_keywords'] === $seoKeys);

        // Cleanup
        Page::deletePage($cRes['id']);
    }

    // =========================================================================
    // 6. CUSTOMER CONTACT ENQUIRIES
    // =========================================================================
    private function testCustomerContactEnquiries(): void {
        echo "\n\033[1m[6/8] Testing Customer Contact Enquiries Submission & Moderation...\033[0m\n";

        // 1. Validation: Rejects empty name
        $noNameRes = Page::saveContactEnquiry([
            'name'    => '',
            'email'   => 'valid@domain.com',
            'message' => 'Valid message content goes here...',
        ]);
        $this->assert("Empty name is rejected", !$noNameRes['success']);

        // 2. Validation: Rejects invalid email
        $badEmailRes = Page::saveContactEnquiry([
            'name'    => 'Maharaja Singh',
            'email'   => 'not-an-email',
            'message' => 'Valid message content goes here...',
        ]);
        $this->assert("Invalid email is rejected", !$badEmailRes['success']);

        // 3. Validation: Rejects too short message
        $shortMsgRes = Page::saveContactEnquiry([
            'name'    => 'Maharaja Singh',
            'email'   => 'maharaja@jaipur.in',
            'message' => 'hi',
        ]);
        $this->assert("Message shorter than 10 characters rejected", !$shortMsgRes['success']);

        // 4. Valid Submission
        $goodRes = Page::saveContactEnquiry([
            'name'    => 'Yuvraj Pratap Singh',
            'email'   => 'pratap.' . time() . '@jaipur.in',
            'phone'   => '+91 98290 99999',
            'subject' => 'Bespoke Royal Sherwani Consultation',
            'message' => 'I would like to commission a bespoke raw silk sherwani with antique gold Zardozi embroidery.',
        ]);
        $this->assert("Valid inquiry saved successfully", $goodRes['success']);
        $enqId = $goodRes['id'];
        $this->assert("Inquiry ID > 0", $enqId > 0);

        // 5. Retrieve from admin inbox
        $inbox = Page::getContactEnquiries(['search' => 'Yuvraj Pratap Singh']);
        $this->assert("Inquiry appears in admin inbox", count($inbox['enquiries']) > 0);
        $found = $inbox['enquiries'][0];
        $this->assert("Inquiry initially marked unread (is_read = 0)", (int)$found['is_read'] === 0);

        // 6. Toggle Read status
        $readRes = Page::toggleEnquiryRead($enqId, true);
        $this->assert("Inquiry marked as read", $readRes);

        // 7. Delete inquiry
        $delEnqRes = Page::deleteEnquiry($enqId);
        $this->assert("Inquiry deleted cleanly", $delEnqRes);
    }

    // =========================================================================
    // 7. PAGE KPI CALCULATIONS
    // =========================================================================
    private function testPageKPICalculations(): void {
        echo "\n\033[1m[7/8] Testing Static CMS Pages KPI Aggregation...\033[0m\n";

        $kpis = Page::getPageKPIs();

        $this->assert("KPI: total_pages >= 7", isset($kpis['total_pages']) && $kpis['total_pages'] >= 7);
        $this->assert("KPI: published_pages >= 7", isset($kpis['published_pages']) && $kpis['published_pages'] >= 7);
        $this->assert("KPI: system_pages == 7", isset($kpis['system_pages']) && $kpis['system_pages'] === 7);
        $this->assert("KPI: total_enquiries is numeric", isset($kpis['total_enquiries']));
        $this->assert("KPI: unread_enquiries is numeric", isset($kpis['unread_enquiries']));
    }

    // =========================================================================
    // 8. CONTROLLER & STOREFRONT VIEWS
    // =========================================================================
    private function testControllerAndStorefrontViews(): void {
        echo "\n\033[1m[8/8] Testing Controller Instantiation & View Rendering...\033[0m\n";

        $controller = new PageController();
        $this->assert("PageController instantiated successfully", $controller instanceof PageController);

        // Test Admin View exists
        $adminView = __DIR__ . '/../app/Views/admin/pages/index.php';
        $this->assert("Admin CMS pages view file exists", file_exists($adminView));

        // Test Public View exists
        $publicView = __DIR__ . '/../app/Views/pages/show.php';
        $this->assert("Public storefront page view file exists", file_exists($publicView));

        // Test Admin Tab 1: pages
        $_GET['tab'] = 'pages';
        ob_start();
        try {
            $controller->index();
            $outPages = ob_get_clean();
            $this->assert("Admin index (tab=pages) renders without fatal error", strlen($outPages) > 0);
            $this->assert("Admin index contains 'Static CMS Pages'", strpos($outPages, 'Static CMS Pages') !== false);
            $this->assert("Admin index lists About Us page", strpos($outPages, 'about-us') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Admin index rendering threw exception", false, $t->getMessage());
        }

        // Test Admin Tab 2: editor
        $_GET['tab'] = 'editor';
        ob_start();
        try {
            $controller->index();
            $outEditor = ob_get_clean();
            $this->assert("Admin editor renders cleanly", strpos($outEditor, 'Page Title') !== false && strpos($outEditor, 'Editorial Body Content') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Admin editor rendering threw exception", false, $t->getMessage());
        }

        // Test Admin Tab 3: enquiries
        $_GET['tab'] = 'enquiries';
        ob_start();
        try {
            $controller->index();
            $outEnq = ob_get_clean();
            $this->assert("Admin inquiries tab renders cleanly", strpos($outEnq, 'Client Inquiries Inbox') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Admin enquiries rendering threw exception", false, $t->getMessage());
        }

        // Test Public Storefront: showPublicPage('about-us')
        ob_start();
        try {
            $controller->showPublicPage('about-us');
            $outAbout = ob_get_clean();
            $this->assert("Public about-us renders cleanly", strlen($outAbout) > 0);
            $this->assert("Public about-us contains brand heritage", strpos($outAbout, 'The Heritage of Jiyaji LX') !== false);
            $this->assert("Public about-us has SEO meta description", strpos($outAbout, '<meta name="description"') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Public about-us rendering threw exception", false, $t->getMessage());
        }

        // Test Public Storefront: contact-us contains contact form
        ob_start();
        try {
            $controller->showPublicPage('contact-us');
            $outContact = ob_get_clean();
            $this->assert("Public contact-us renders cleanly", strlen($outContact) > 0);
            $this->assert("Public contact-us contains interactive form", strpos($outContact, 'contact-us/submit') !== false);
        } catch (\Throwable $t) {
            ob_end_clean();
            $this->assert("Public contact-us rendering threw exception", false, $t->getMessage());
        }
    }
}

// Execute test suite
$suite = new StaticPagesCMSTestSuite();
$suite->runAll();
