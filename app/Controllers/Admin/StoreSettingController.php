<?php

namespace App\Controllers\Admin;

use App\Models\StoreSetting;
use App\Middleware\AuthMiddleware;
use Exception;

class StoreSettingController {

    // =========================================================================
    // 1. INDEX — Store Settings Command Center
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $activeTab = $_GET['tab'] ?? 'brand';
        $validTabs = ['brand', 'contact', 'whatsapp', 'localization', 'marketing', 'social', 'operations'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'brand';
        }

        $allSettings = StoreSetting::getAllSettings();
        $kpis        = StoreSetting::getKPIs();
        $flatValues  = StoreSetting::getFlatValues();

        $title = 'Store Settings & Brand Configuration | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/settings/index.php';
    }

    // =========================================================================
    // 2. UPDATE — Save Settings by Section / Group
    // =========================================================================

    public function update(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF verification token expired. Please reload and try again.');
            redirect('admin/settings');
        }

        $tab = $_POST['_tab'] ?? 'brand';
        $validTabs = ['brand', 'contact', 'whatsapp', 'localization', 'marketing', 'social', 'operations'];
        if (!in_array($tab, $validTabs, true)) {
            $tab = 'brand';
        }

        $adminId = $_SESSION['admin']['id'] ?? null;
        if ($adminId !== null) {
            $adminId = (int)$adminId;
        }

        // Collect all POST parameters excluding system routing fields
        $dataToUpdate = [];
        $catalog = StoreSetting::getCatalog();
        $groupMeta = $catalog[$tab]['settings'] ?? [];

        foreach ($groupMeta as $key => $meta) {
            if ($meta['type'] === 'boolean') {
                // If checkbox is unchecked, it won't be sent in POST
                $dataToUpdate[$key] = isset($_POST[$key]) ? '1' : '0';
            } elseif ($meta['type'] === 'asset') {
                // Check if text URL was passed directly
                if (isset($_POST[$key])) {
                    $dataToUpdate[$key] = trim((string)$_POST[$key]);
                }
            } else {
                if (isset($_POST[$key])) {
                    $dataToUpdate[$key] = trim((string)$_POST[$key]);
                }
            }
        }

        // Handle direct file uploads for brand assets if present
        if (!empty($_FILES['store_logo_file']['tmp_name'])) {
            $uploaded = StoreSetting::uploadAsset($_FILES['store_logo_file'], 'logo', $adminId);
            if ($uploaded) {
                $dataToUpdate['store_logo_url'] = $uploaded;
            }
        }
        if (!empty($_FILES['store_logo_dark_file']['tmp_name'])) {
            $uploaded = StoreSetting::uploadAsset($_FILES['store_logo_dark_file'], 'logo_dark', $adminId);
            if ($uploaded) {
                $dataToUpdate['store_logo_dark_url'] = $uploaded;
            }
        }
        if (!empty($_FILES['store_favicon_file']['tmp_name'])) {
            $uploaded = StoreSetting::uploadAsset($_FILES['store_favicon_file'], 'favicon', $adminId);
            if ($uploaded) {
                $dataToUpdate['store_favicon_url'] = $uploaded;
            }
        }

        $success = StoreSetting::updateSettings($dataToUpdate, $adminId);

        if ($success) {
            $tabTitle = $catalog[$tab]['title'] ?? 'Store Settings';
            set_toast('success', 'Settings Saved', "$tabTitle has been successfully updated and applied.");
        } else {
            set_toast('error', 'Update Failed', 'An error occurred while saving your store settings. Please try again.');
        }

        redirect('admin/settings?tab=' . $tab);
    }

    // =========================================================================
    // 3. TOGGLE — Fast Switch Toggle (AJAX or Standard POST)
    // =========================================================================

    public function toggle(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings');
        }

        if (!csrf_verify()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'CSRF verification token expired.']);
                exit;
            }
            set_toast('error', 'Security Error', 'CSRF verification token expired.');
            redirect('admin/settings');
        }

        $key = trim($_POST['key'] ?? '');
        $tab = $_POST['_tab'] ?? 'operations';

        $allowedToggles = [
            'whatsapp_enabled',
            'whatsapp_popup_enabled',
            'meta_pixel_enabled',
            'ga4_enabled',
            'maintenance_mode',
            'order_acceptance',
            'allow_guest_checkout'
        ];

        if (!in_array($key, $allowedToggles, true)) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid setting toggle key.']);
                exit;
            }
            set_toast('error', 'Error', 'Invalid setting toggle key.');
            redirect('admin/settings?tab=' . $tab);
        }

        $adminId = $_SESSION['admin']['id'] ?? null;
        if ($adminId !== null) {
            $adminId = (int)$adminId;
        }

        $success = StoreSetting::toggleSetting($key, $adminId);
        $newVal = StoreSetting::get($key);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => $success,
                'key'       => $key,
                'new_value' => $newVal,
                'message'   => 'Setting status updated successfully.'
            ]);
            exit;
        }

        if ($success) {
            set_toast('success', 'Status Updated', 'Setting status updated successfully.');
        } else {
            set_toast('error', 'Error', 'Failed to toggle setting status.');
        }

        redirect('admin/settings?tab=' . $tab);
    }

    // =========================================================================
    // 4. UPLOAD ASSET — Dedicated Upload Endpoint
    // =========================================================================

    public function uploadAsset(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings?tab=brand');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF verification token expired.');
            redirect('admin/settings?tab=brand');
        }

        $type = $_POST['asset_type'] ?? 'logo';
        $adminId = $_SESSION['admin']['id'] ?? null;
        if ($adminId !== null) {
            $adminId = (int)$adminId;
        }

        if (empty($_FILES['asset_file']['tmp_name'])) {
            set_toast('error', 'Upload Error', 'Please select a valid image file to upload.');
            redirect('admin/settings?tab=brand');
        }

        $path = StoreSetting::uploadAsset($_FILES['asset_file'], $type, $adminId);

        if ($path) {
            set_toast('success', 'Asset Uploaded', 'Your store brand asset has been uploaded and applied successfully.');
        } else {
            set_toast('error', 'Upload Failed', 'Failed to upload image. Please check file format (PNG, JPG, SVG, WEBP, ICO) and size (< 2.5MB).');
        }

        redirect('admin/settings?tab=brand');
    }

    /**
     * Check if request is an AJAX request.
     */
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
