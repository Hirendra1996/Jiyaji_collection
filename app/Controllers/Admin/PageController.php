<?php

namespace App\Controllers\Admin;

use App\Models\Page;
use App\Models\Staff;
use App\Middleware\AuthMiddleware;
use Exception;

class PageController {

    // =========================================================================
    // 1. ADMIN INDEX & COMMAND CENTER
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $activeTab = $_GET['tab'] ?? 'pages';
        $validTabs = ['pages', 'editor', 'enquiries'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'pages';
        }

        // Filters for Pages Directory
        $search = trim($_GET['search'] ?? '');
        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter = $_GET['type'] ?? 'all';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $filters = [
            'search' => $search,
            'status' => $statusFilter,
            'type'   => $typeFilter,
        ];

        // Seed defaults if necessary
        Page::ensureDefaults();

        $pagesData   = Page::getPages($filters, $page, $perPage);
        $kpis        = Page::getPageKPIs();
        $adminId     = (int)($_SESSION['admin']['id'] ?? 1);
        $canManage   = Staff::hasPermission($adminId, 'pages', 'manage');

        // Check if editing specific page
        $editId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
        $editingPage = ($editId > 0) ? Page::getPage($editId) : null;

        // Inquiries data if on enquiries tab
        $enqSearch = trim($_GET['enq_search'] ?? '');
        $enqStatus = $_GET['enq_status'] ?? 'all';
        $enqPage   = max(1, (int)($_GET['enq_page'] ?? 1));
        $enquiriesData = Page::getContactEnquiries([
            'search' => $enqSearch,
            'status' => $enqStatus,
        ], $enqPage, 10);

        $title = 'Static CMS Pages & Brand Content | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/pages/index.php';
    }

    // =========================================================================
    // 2. PAGE ACTIONS (SAVE, TOGGLE, DELETE)
    // =========================================================================

    public function save(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/pages?tab=pages');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid. Please try again.');
            redirect('admin/pages?tab=pages');
        }

        $adminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($adminId, 'pages', 'manage')) {
            set_toast('error', 'Access Denied', 'You do not have permission to manage CMS pages.');
            redirect('admin/pages?tab=pages');
        }

        $id       = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $title    = trim($_POST['title'] ?? '');
        $slug     = trim($_POST['slug'] ?? '');
        $excerpt  = trim($_POST['excerpt'] ?? '');
        $content  = trim($_POST['content'] ?? '');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDesc  = trim($_POST['meta_description'] ?? '');
        $metaKeys  = trim($_POST['meta_keywords'] ?? '');
        $isActive  = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if ($title === '') {
            set_toast('error', 'Validation Error', 'Page title is strictly required.');
            redirect('admin/pages?tab=' . ($id > 0 ? "editor&id={$id}" : 'editor'));
        }

        $data = [
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => $excerpt,
            'content'          => $content,
            'meta_title'       => $metaTitle,
            'meta_description' => $metaDesc,
            'meta_keywords'    => $metaKeys,
            'is_active'        => $isActive,
        ];

        if ($id > 0) {
            $result = Page::updatePage($id, $data, $adminId);
            if ($result['success']) {
                set_toast('success', 'Page Updated', $result['message']);
            } else {
                set_toast('error', 'Update Failed', $result['message']);
            }
        } else {
            $result = Page::createPage($data, $adminId);
            if ($result['success']) {
                set_toast('success', 'Page Created', $result['message']);
            } else {
                set_toast('error', 'Creation Failed', $result['message']);
            }
        }

        redirect('admin/pages?tab=pages');
    }

    public function toggle(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/pages?tab=pages');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/pages?tab=pages');
        }

        $adminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($adminId, 'pages', 'manage')) {
            set_toast('error', 'Access Denied', 'You do not have permission to publish or unpublish CMS pages.');
            redirect('admin/pages?tab=pages');
        }

        $id     = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $active = !empty($_POST['is_active']) && $_POST['is_active'] === '1';

        $result = Page::toggleStatus($id, $active, $adminId);
        if ($result['success']) {
            set_toast('success', 'Status Updated', $result['message']);
        } else {
            set_toast('error', 'Update Failed', $result['message']);
        }

        redirect('admin/pages?tab=pages');
    }

    public function delete(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/pages?tab=pages');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/pages?tab=pages');
        }

        $adminId = (int)($_SESSION['admin']['id'] ?? 1);
        if (!Staff::hasPermission($adminId, 'pages', 'manage')) {
            set_toast('error', 'Access Denied', 'You do not have permission to delete CMS pages.');
            redirect('admin/pages?tab=pages');
        }

        $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $result = Page::deletePage($id);

        if ($result['success']) {
            set_toast('success', 'Page Deleted', $result['message']);
        } else {
            set_toast('error', 'Deletion Prevented', $result['message']);
        }

        redirect('admin/pages?tab=pages');
    }

    // =========================================================================
    // 3. ENQUIRY MODERATION (TOGGLE READ, DELETE)
    // =========================================================================

    public function toggleEnquiry(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/pages?tab=enquiries');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/pages?tab=enquiries');
        }

        $id     = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $isRead = !empty($_POST['is_read']) && $_POST['is_read'] === '1';

        $updated = Page::toggleEnquiryRead($id, $isRead);
        if ($updated) {
            $msg = $isRead ? 'Customer inquiry marked as read.' : 'Customer inquiry marked as unread.';
            set_toast('success', 'Status Updated', $msg);
        }

        redirect('admin/pages?tab=enquiries');
    }

    public function deleteEnquiry(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/pages?tab=enquiries');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired or invalid.');
            redirect('admin/pages?tab=enquiries');
        }

        $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $deleted = Page::deleteEnquiry($id);

        if ($deleted) {
            set_toast('success', 'Inquiry Removed', 'Customer inquiry permanently deleted.');
        }

        redirect('admin/pages?tab=enquiries');
    }

    // =========================================================================
    // 4. PUBLIC STOREFRONT RENDERING & CONTACT FORM
    // =========================================================================

    /**
     * Render public brand or compliance page by slug.
     */
    public function showPublicPage(string $slug = ''): void {
        Page::ensureDefaults();

        if ($slug === '') {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $slug = basename($uri);
        }

        $page = Page::getPageBySlug($slug, true);
        if (!$page) {
            http_response_code(404);
            include __DIR__ . '/../../Views/errors/404.php';
            exit;
        }

        // Fetch all active pages for the navigation list
        $allPages = Page::getPages(['status' => 'active'], 1, 50)['pages'];

        $title = !empty($page['meta_title']) ? $page['meta_title'] : ($page['title'] . ' | Jiyaji LX');
        $metaDescription = $page['meta_description'] ?? $page['excerpt'];
        $metaKeywords = $page['meta_keywords'] ?? '';

        include __DIR__ . '/../../Views/pages/show.php';
    }

    public function aboutUs(): void { $this->showPublicPage('about-us'); }
    public function privacyPolicy(): void { $this->showPublicPage('privacy-policy'); }
    public function termsConditions(): void { $this->showPublicPage('terms-conditions'); }
    public function shippingPolicy(): void { $this->showPublicPage('shipping-policy'); }
    public function returnsPolicy(): void { $this->showPublicPage('returns-policy'); }
    public function faq(): void { $this->showPublicPage('faq'); }
    public function contactUs(): void { $this->showPublicPage('contact-us'); }

    /**
     * Process public contact inquiry submission.
     */
    public function submitContact(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('contact-us');
        }

        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? 'General Customer Inquiry');
        $message = trim($_POST['message'] ?? '');

        $result = Page::saveContactEnquiry([
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'subject' => $subject,
            'message' => $message,
        ]);

        if ($result['success']) {
            set_toast('success', 'Message Received', $result['message']);
            redirect('contact-us?status=sent');
        } else {
            set_toast('error', 'Transmission Failed', $result['message']);
            redirect('contact-us?status=error');
        }
    }
}
