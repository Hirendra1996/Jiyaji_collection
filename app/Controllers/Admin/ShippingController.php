<?php

namespace App\Controllers\Admin;

use App\Models\ShippingZone;
use App\Middleware\AuthMiddleware;
use Exception;

class ShippingController {

    // =========================================================================
    // 1. INDEX — Shipping Zones, Rates & Logistics Command Center
    // =========================================================================

    public function index(): void {
        AuthMiddleware::check();

        $activeTab = $_GET['tab'] ?? 'zones';
        $validTabs = ['zones', 'rates', 'settings', 'couriers', 'calculator'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'zones';
        }

        $zones    = ShippingZone::getZones();
        $rates    = ShippingZone::getRates();
        $settings = ShippingZone::getGlobalSettings();
        $couriers = ShippingZone::getCourierPartners();
        $kpis     = ShippingZone::getShippingKPIs();

        $title = 'Shipping Zones & Delivery Pincodes | Jiyaji LX';
        include __DIR__ . '/../../Views/admin/shipping_pincodes/index.php';
    }

    // =========================================================================
    // 2. ZONE ACTIONS (SAVE, DELETE, TOGGLE)
    // =========================================================================

    public function saveZone(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=zones');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired. Please try again.');
            redirect('admin/shipping-pincodes?tab=zones');
        }

        $zoneId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $name   = trim($_POST['name'] ?? '');

        if ($name === '') {
            set_toast('error', 'Validation Error', 'Zone name is required.');
            redirect('admin/shipping-pincodes?tab=zones');
        }

        $data = [
            'name'        => $name,
            'zone_code'   => strtoupper(trim($_POST['zone_code'] ?? '')),
            'description' => trim($_POST['description'] ?? ''),
            'pincodes'    => trim($_POST['pincodes'] ?? ''),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if ($zoneId > 0) {
            $saved = ShippingZone::updateZone($zoneId, $data);
            $msg = $saved ? 'Delivery zone updated successfully.' : 'Failed to update delivery zone.';
        } else {
            $newId = ShippingZone::createZone($data);
            $saved = $newId > 0;
            $msg = $saved ? 'New delivery zone created successfully.' : 'Failed to create delivery zone.';
        }

        if ($saved) {
            set_toast('success', 'Zone Saved', $msg);
        } else {
            set_toast('error', 'Save Failed', $msg);
        }

        redirect('admin/shipping-pincodes?tab=zones');
    }

    public function deleteZone(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=zones');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=zones');
        }

        $zoneId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($zoneId > 0) {
            $deleted = ShippingZone::deleteZone($zoneId);
            if ($deleted) {
                set_toast('success', 'Zone Deleted', 'Shipping zone and associated rate slabs removed.');
            } else {
                set_toast('error', 'Delete Failed', 'Could not delete shipping zone.');
            }
        }

        redirect('admin/shipping-pincodes?tab=zones');
    }

    public function toggleZone(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=zones');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=zones');
        }

        $zoneId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $active = !empty($_POST['is_active']) && $_POST['is_active'] === '1';

        if ($zoneId > 0) {
            $toggled = ShippingZone::toggleZone($zoneId, $active);
            if ($toggled) {
                $statusStr = $active ? 'activated' : 'deactivated';
                set_toast('success', 'Status Updated', "Zone has been {$statusStr}.");
            }
        }

        redirect('admin/shipping-pincodes?tab=zones');
    }

    // =========================================================================
    // 3. RATE SLAB ACTIONS (SAVE, DELETE, TOGGLE)
    // =========================================================================

    public function saveRate(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=rates');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=rates');
        }

        $rateId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $zoneId = isset($_POST['zone_id']) && is_numeric($_POST['zone_id']) ? (int)$_POST['zone_id'] : 0;

        if ($zoneId <= 0) {
            set_toast('error', 'Validation Error', 'Please select a valid delivery zone for this rate slab.');
            redirect('admin/shipping-pincodes?tab=rates');
        }

        $data = [
            'zone_id'                => $zoneId,
            'method'                 => $_POST['method'] ?? 'standard',
            'title'                  => trim($_POST['title'] ?? ''),
            'weight_from_g'          => (int)($_POST['weight_from_g'] ?? 0),
            'weight_to_g'            => (int)($_POST['weight_to_g'] ?? 5000),
            'flat_rate'              => (float)($_POST['flat_rate'] ?? 0.0),
            'free_above_order_value' => isset($_POST['free_above_order_value']) && $_POST['free_above_order_value'] !== '' ? (float)$_POST['free_above_order_value'] : null,
            'estimated_days'         => trim($_POST['estimated_days'] ?? '3-5 Business Days'),
            'is_active'              => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if ($rateId > 0) {
            $saved = ShippingZone::updateRate($rateId, $data);
            $msg = $saved ? 'Rate slab updated successfully.' : 'Failed to update rate slab.';
        } else {
            $newId = ShippingZone::createRate($data);
            $saved = $newId > 0;
            $msg = $saved ? 'New shipping rate slab created.' : 'Failed to create rate slab.';
        }

        if ($saved) {
            set_toast('success', 'Rate Saved', $msg);
        } else {
            set_toast('error', 'Save Failed', $msg);
        }

        redirect('admin/shipping-pincodes?tab=rates');
    }

    public function deleteRate(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=rates');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=rates');
        }

        $rateId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($rateId > 0) {
            $deleted = ShippingZone::deleteRate($rateId);
            if ($deleted) {
                set_toast('success', 'Rate Slab Deleted', 'Rate tier removed from matrix.');
            } else {
                set_toast('error', 'Delete Failed', 'Could not delete rate slab.');
            }
        }

        redirect('admin/shipping-pincodes?tab=rates');
    }

    public function toggleRate(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=rates');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=rates');
        }

        $rateId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        $active = !empty($_POST['is_active']) && $_POST['is_active'] === '1';

        if ($rateId > 0) {
            $toggled = ShippingZone::toggleRate($rateId, $active);
            if ($toggled) {
                $statusStr = $active ? 'activated' : 'deactivated';
                set_toast('success', 'Status Updated', "Rate slab {$statusStr}.");
            }
        }

        redirect('admin/shipping-pincodes?tab=rates');
    }

    // =========================================================================
    // 4. GLOBAL SETTINGS & COURIER PARTNERS
    // =========================================================================

    public function saveSettings(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=settings');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=settings');
        }

        $saved = ShippingZone::updateGlobalSettings($_POST);
        if ($saved) {
            set_toast('success', 'Settings Saved', 'Global free shipping threshold and fallback fees updated.');
        } else {
            set_toast('error', 'Save Failed', 'Could not update global settings.');
        }

        redirect('admin/shipping-pincodes?tab=settings');
    }

    public function saveCourier(): void {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/shipping-pincodes?tab=couriers');
        }

        if (!csrf_verify()) {
            set_toast('error', 'Security Error', 'CSRF token expired.');
            redirect('admin/shipping-pincodes?tab=couriers');
        }

        $partnerKey = trim($_POST['partner_key'] ?? '');
        $saved = ShippingZone::updateCourierPartner($partnerKey, $_POST);

        if ($saved) {
            set_toast('success', 'Courier Updated', "Carrier partner profile saved.");
        } else {
            set_toast('error', 'Update Failed', "Failed to update carrier partner.");
        }

        redirect('admin/shipping-pincodes?tab=couriers');
    }

    // =========================================================================
    // 5. LIVE CALCULATOR (AJAX / JSON)
    // =========================================================================

    public function calculate(): void {
        AuthMiddleware::check();

        $pincode = trim($_POST['pincode'] ?? ($_GET['pincode'] ?? ''));
        $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : (isset($_GET['subtotal']) ? (float)$_GET['subtotal'] : 0.0);
        $weightGrams = isset($_POST['weight_grams']) ? (int)$_POST['weight_grams'] : (isset($_GET['weight_grams']) ? (int)$_GET['weight_grams'] : 500);

        $result = ShippingZone::calculateShipping($pincode, $subtotal, $weightGrams);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}
