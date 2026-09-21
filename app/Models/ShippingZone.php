<?php

namespace App\Models;

use App\Config\Database;
use App\Models\PaymentGateway;
use Exception;

class ShippingZone {

    // =========================================================================
    // 1. ZONES RETRIEVAL & MANAGEMENT
    // =========================================================================

    /**
     * Retrieve all shipping zones with rates count and calculated mapped pincode counts.
     */
    public static function getZones(bool $onlyActive = false): array {
        self::ensureDefaults();

        try {
            $db = Database::connect();
            $sql = "
                SELECT 
                    z.*,
                    COUNT(r.id) AS rates_count,
                    SUM(CASE WHEN r.is_active = 1 THEN 1 ELSE 0 END) AS active_rates_count
                FROM shipping_zones z
                LEFT JOIN shipping_rates r ON z.id = r.zone_id
            ";
            if ($onlyActive) {
                $sql .= " WHERE z.is_active = 1 ";
            }
            $sql .= " GROUP BY z.id ORDER BY z.id ASC ";

            $res = $db->query($sql);
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

            $zones = [];
            foreach ($rows as $r) {
                $pincodesRaw = trim($r['pincodes'] ?? '');
                preg_match_all('/\b\d{6}\b/', $pincodesRaw, $matches);
                $uniquePins = !empty($matches[0]) ? array_unique($matches[0]) : [];

                $zones[] = [
                    'id'                 => (int)$r['id'],
                    'name'               => $r['name'],
                    'zone_code'          => $r['zone_code'] ?? 'ZONE_' . $r['id'],
                    'description'        => $r['description'] ?? '',
                    'pincodes'           => $pincodesRaw,
                    'pincode_count'      => count($uniquePins),
                    'is_active'          => (bool)$r['is_active'],
                    'rates_count'        => (int)$r['rates_count'],
                    'active_rates_count' => (int)$r['active_rates_count'],
                    'created_at'         => $r['created_at'],
                    'updated_at'         => $r['updated_at'] ?? $r['created_at'],
                ];
            }

            return $zones;
        } catch (Exception $e) {
            error_log("ShippingZone::getZones error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve a specific zone with its associated rates.
     */
    public static function getZone(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM shipping_zones WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $zone = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$zone) {
                return null;
            }

            $pincodesRaw = trim($zone['pincodes'] ?? '');
            preg_match_all('/\b\d{6}\b/', $pincodesRaw, $matches);
            $uniquePins = !empty($matches[0]) ? array_unique($matches[0]) : [];

            $zone['id'] = (int)$zone['id'];
            $zone['is_active'] = (bool)$zone['is_active'];
            $zone['pincode_count'] = count($uniquePins);
            $zone['rates'] = self::getRates($zone['id']);

            return $zone;
        } catch (Exception $e) {
            error_log("ShippingZone::getZone error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new shipping zone.
     */
    public static function createZone(array $data): int {
        try {
            $db = Database::connect();
            $name = trim($data['name'] ?? '');
            $code = strtoupper(trim($data['zone_code'] ?? ''));
            $desc = trim($data['description'] ?? '');
            $pincodes = trim($data['pincodes'] ?? '');
            $isActive = !empty($data['is_active']) ? 1 : 0;

            if ($name === '') {
                return 0;
            }

            // Deduplicate and clean pincodes
            preg_match_all('/\b\d{6}\b/', $pincodes, $matches);
            $cleanPins = !empty($matches[0]) ? implode(', ', array_unique($matches[0])) : '';

            $stmt = $db->prepare("
                INSERT INTO shipping_zones (name, zone_code, description, pincodes, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssssi", $name, $code, $desc, $cleanPins, $isActive);
            $success = $stmt->execute();
            $newId = $success ? $db->insert_id : 0;
            $stmt->close();

            return $newId;
        } catch (Exception $e) {
            error_log("ShippingZone::createZone error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update an existing shipping zone.
     */
    public static function updateZone(int $id, array $data): bool {
        try {
            $db = Database::connect();
            $name = trim($data['name'] ?? '');
            $code = strtoupper(trim($data['zone_code'] ?? ''));
            $desc = trim($data['description'] ?? '');
            $pincodes = trim($data['pincodes'] ?? '');
            $isActive = isset($data['is_active']) ? (!empty($data['is_active']) ? 1 : 0) : 1;

            if ($name === '') {
                return false;
            }

            preg_match_all('/\b\d{6}\b/', $pincodes, $matches);
            $cleanPins = !empty($matches[0]) ? implode(', ', array_unique($matches[0])) : '';

            $stmt = $db->prepare("
                UPDATE shipping_zones
                SET name = ?, zone_code = ?, description = ?, pincodes = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssii", $name, $code, $desc, $cleanPins, $isActive, $id);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            error_log("ShippingZone::updateZone error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a shipping zone and its rate slabs.
     */
    public static function deleteZone(int $id): bool {
        try {
            $db = Database::connect();
            // Delete rates first
            $stmtR = $db->prepare("DELETE FROM shipping_rates WHERE zone_id = ?");
            $stmtR->bind_param("i", $id);
            $stmtR->execute();
            $stmtR->close();

            // Delete zone
            $stmt = $db->prepare("DELETE FROM shipping_zones WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            error_log("ShippingZone::deleteZone error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle active status of a zone.
     */
    public static function toggleZone(int $id, bool $active): bool {
        try {
            $db = Database::connect();
            $val = $active ? 1 : 0;
            $stmt = $db->prepare("UPDATE shipping_zones SET is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $val, $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("ShippingZone::toggleZone error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 2. SHIPPING RATES & WEIGHT SLABS MANAGEMENT
    // =========================================================================

    /**
     * Retrieve all rates, optionally filtered by zone.
     */
    public static function getRates(?int $zoneId = null): array {
        try {
            $db = Database::connect();
            $where = $zoneId ? "WHERE r.zone_id = " . (int)$zoneId : "";
            $sql = "
                SELECT 
                    r.*,
                    z.name AS zone_name,
                    z.zone_code
                FROM shipping_rates r
                LEFT JOIN shipping_zones z ON r.zone_id = z.id
                $where
                ORDER BY r.zone_id ASC, r.method ASC, r.weight_from_g ASC
            ";
            $res = $db->query($sql);
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

            $rates = [];
            foreach ($rows as $r) {
                $rates[] = [
                    'id'                     => (int)$r['id'],
                    'zone_id'                => (int)$r['zone_id'],
                    'zone_name'              => $r['zone_name'] ?? 'Zone #' . $r['zone_id'],
                    'zone_code'              => $r['zone_code'] ?? 'ZONE',
                    'method'                 => $r['method'],
                    'title'                  => $r['title'] ?? ucfirst($r['method']) . ' Delivery',
                    'weight_from_g'          => (int)($r['weight_from_g'] ?? 0),
                    'weight_to_g'            => (int)($r['weight_to_g'] ?? 0),
                    'flat_rate'              => (float)$r['flat_rate'],
                    'free_above_order_value' => $r['free_above_order_value'] !== null ? (float)$r['free_above_order_value'] : null,
                    'estimated_days'         => $r['estimated_days'] ?? '3-5 Business Days',
                    'is_active'              => (bool)$r['is_active'],
                    'created_at'             => $r['created_at'],
                ];
            }

            return $rates;
        } catch (Exception $e) {
            error_log("ShippingZone::getRates error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve a single rate slab.
     */
    public static function getRate(int $id): ?array {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT r.*, z.name AS zone_name, z.zone_code
                FROM shipping_rates r
                LEFT JOIN shipping_zones z ON r.zone_id = z.id
                WHERE r.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$r) return null;

            return [
                'id'                     => (int)$r['id'],
                'zone_id'                => (int)$r['zone_id'],
                'zone_name'              => $r['zone_name'] ?? '',
                'zone_code'              => $r['zone_code'] ?? '',
                'method'                 => $r['method'],
                'title'                  => $r['title'] ?? ucfirst($r['method']) . ' Delivery',
                'weight_from_g'          => (int)($r['weight_from_g'] ?? 0),
                'weight_to_g'            => (int)($r['weight_to_g'] ?? 0),
                'flat_rate'              => (float)$r['flat_rate'],
                'free_above_order_value' => $r['free_above_order_value'] !== null ? (float)$r['free_above_order_value'] : null,
                'estimated_days'         => $r['estimated_days'] ?? '',
                'is_active'              => (bool)$r['is_active'],
            ];
        } catch (Exception $e) {
            error_log("ShippingZone::getRate error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new shipping rate slab.
     */
    public static function createRate(array $data): int {
        try {
            $db = Database::connect();
            $zoneId = (int)$data['zone_id'];
            $method = in_array($data['method'] ?? '', ['standard', 'express', 'free'], true) ? $data['method'] : 'standard';
            $title = trim($data['title'] ?? (ucfirst($method) . ' Delivery'));
            $wFrom = isset($data['weight_from_g']) ? max(0, (int)$data['weight_from_g']) : 0;
            $wTo = isset($data['weight_to_g']) ? max(0, (int)$data['weight_to_g']) : 5000;
            $rate = isset($data['flat_rate']) ? max(0, (float)$data['flat_rate']) : 0.00;
            $freeAbove = isset($data['free_above_order_value']) && $data['free_above_order_value'] !== '' ? (float)$data['free_above_order_value'] : null;
            $estDays = trim($data['estimated_days'] ?? '3-5 Business Days');
            $isActive = !empty($data['is_active']) ? 1 : 0;

            $stmt = $db->prepare("
                INSERT INTO shipping_rates (zone_id, method, title, weight_from_g, weight_to_g, flat_rate, free_above_order_value, estimated_days, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("issiddssi", $zoneId, $method, $title, $wFrom, $wTo, $rate, $freeAbove, $estDays, $isActive);
            $success = $stmt->execute();
            $newId = $success ? $db->insert_id : 0;
            $stmt->close();

            return $newId;
        } catch (Exception $e) {
            error_log("ShippingZone::createRate error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update an existing rate slab.
     */
    public static function updateRate(int $id, array $data): bool {
        try {
            $db = Database::connect();
            $zoneId = (int)$data['zone_id'];
            $method = in_array($data['method'] ?? '', ['standard', 'express', 'free'], true) ? $data['method'] : 'standard';
            $title = trim($data['title'] ?? (ucfirst($method) . ' Delivery'));
            $wFrom = isset($data['weight_from_g']) ? max(0, (int)$data['weight_from_g']) : 0;
            $wTo = isset($data['weight_to_g']) ? max(0, (int)$data['weight_to_g']) : 5000;
            $rate = isset($data['flat_rate']) ? max(0, (float)$data['flat_rate']) : 0.00;
            $freeAbove = isset($data['free_above_order_value']) && $data['free_above_order_value'] !== '' ? (float)$data['free_above_order_value'] : null;
            $estDays = trim($data['estimated_days'] ?? '3-5 Business Days');
            $isActive = isset($data['is_active']) ? (!empty($data['is_active']) ? 1 : 0) : 1;

            $stmt = $db->prepare("
                UPDATE shipping_rates
                SET zone_id = ?, method = ?, title = ?, weight_from_g = ?, weight_to_g = ?, flat_rate = ?, free_above_order_value = ?, estimated_days = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("issiddssii", $zoneId, $method, $title, $wFrom, $wTo, $rate, $freeAbove, $estDays, $isActive, $id);
            $success = $stmt->execute();
            $stmt->close();

            return $success;
        } catch (Exception $e) {
            error_log("ShippingZone::updateRate error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a rate slab.
     */
    public static function deleteRate(int $id): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM shipping_rates WHERE id = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("ShippingZone::deleteRate error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle rate slab active status.
     */
    public static function toggleRate(int $id, bool $active): bool {
        try {
            $db = Database::connect();
            $val = $active ? 1 : 0;
            $stmt = $db->prepare("UPDATE shipping_rates SET is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $val, $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log("ShippingZone::toggleRate error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 3. GLOBAL SHIPPING POLICIES & SITE SETTINGS
    // =========================================================================

    /**
     * Retrieve global shipping configuration from site_settings.
     */
    public static function getGlobalSettings(): array {
        try {
            $db = Database::connect();
            $res = $db->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'shipping_%' OR setting_key LIKE 'free_shipping_%'");
            $map = [];
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $map[$r['setting_key']] = $r['setting_value'];
                }
            }

            return [
                'free_shipping_threshold'    => isset($map['free_shipping_threshold']) ? (float)$map['free_shipping_threshold'] : 2999.00,
                'default_shipping_fee'       => isset($map['shipping_default_fee']) ? (float)$map['shipping_default_fee'] : 150.00,
                'default_express_fee'        => isset($map['shipping_express_fee']) ? (float)$map['shipping_express_fee'] : 250.00,
                'express_shipping_enabled'   => !isset($map['shipping_express_enabled']) || $map['shipping_express_enabled'] === '1',
                'same_day_delivery_enabled'  => !empty($map['shipping_same_day_enabled']) && $map['shipping_same_day_enabled'] === '1',
                'luxury_packaging_fee'       => isset($map['shipping_packaging_fee']) ? (float)$map['shipping_packaging_fee'] : 0.00,
                'tax_rate_pct'               => isset($map['shipping_tax_rate']) ? (float)$map['shipping_tax_rate'] : 18.0,
            ];
        } catch (Exception $e) {
            error_log("ShippingZone::getGlobalSettings error: " . $e->getMessage());
            return [
                'free_shipping_threshold'    => 2999.00,
                'default_shipping_fee'       => 150.00,
                'default_express_fee'        => 250.00,
                'express_shipping_enabled'   => true,
                'same_day_delivery_enabled'  => false,
                'luxury_packaging_fee'       => 0.00,
                'tax_rate_pct'               => 18.0,
            ];
        }
    }

    /**
     * Update global shipping configuration in site_settings.
     */
    public static function updateGlobalSettings(array $data): bool {
        try {
            $freeThreshold = isset($data['free_shipping_threshold']) ? (float)$data['free_shipping_threshold'] : 2999.00;
            $defaultFee    = isset($data['default_shipping_fee']) ? (float)$data['default_shipping_fee'] : 150.00;
            $expressFee    = isset($data['default_express_fee']) ? (float)$data['default_express_fee'] : 250.00;
            $expressEn     = !empty($data['express_shipping_enabled']) ? '1' : '0';
            $sameDayEn     = !empty($data['same_day_delivery_enabled']) ? '1' : '0';
            $packagingFee  = isset($data['luxury_packaging_fee']) ? (float)$data['luxury_packaging_fee'] : 0.00;

            self::setSiteSetting('free_shipping_threshold', (string)$freeThreshold, 'Global Free Shipping Threshold');
            self::setSiteSetting('shipping_default_fee', (string)$defaultFee, 'Default Standard Shipping Fee');
            self::setSiteSetting('shipping_express_fee', (string)$expressFee, 'Default Express Shipping Fee');
            self::setSiteSetting('shipping_express_enabled', $expressEn, 'Express Shipping Enabled');
            self::setSiteSetting('shipping_same_day_enabled', $sameDayEn, 'Same Day Delivery Enabled');
            self::setSiteSetting('shipping_packaging_fee', (string)$packagingFee, 'Luxury Packaging Fee');

            return true;
        } catch (Exception $e) {
            error_log("ShippingZone::updateGlobalSettings error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 4. COURIER PARTNERS CONFIGURATION
    // =========================================================================

    /**
     * Retrieve configured courier partners with tracking profiles.
     */
    public static function getCourierPartners(): array {
        try {
            $db = Database::connect();
            $res = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'courier_partners_config'");
            $val = $res ? $res->fetch_row()[0] ?? null : null;
            if ($val) {
                $decoded = json_decode($val, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (Exception $e) {
            error_log("ShippingZone::getCourierPartners error: " . $e->getMessage());
        }

        // Default partners catalog
        return [
            'bluedart' => [
                'name'         => 'BlueDart Express',
                'type'         => 'Air Priority & Surface',
                'code'         => 'BLUEDART',
                'is_active'    => true,
                'is_default'   => true,
                'tracking_url' => 'https://www.bluedart.com/tracking?track={AWB}',
                'contact'      => '1860 233 1234',
                'badge_color'  => '#2563eb',
            ],
            'delhivery' => [
                'name'         => 'Delhivery Air & Surface',
                'type'         => 'Pan-India Express',
                'code'         => 'DELHIVERY',
                'is_active'    => true,
                'is_default'   => false,
                'tracking_url' => 'https://www.delhivery.com/track/package/{AWB}',
                'contact'      => '+91 80698 56101',
                'badge_color'  => '#059669',
            ],
            'shiprocket' => [
                'name'         => 'Shiprocket Multi-Carrier',
                'type'         => 'Smart AI Aggregator',
                'code'         => 'SHIPROCKET',
                'is_active'    => true,
                'is_default'   => false,
                'tracking_url' => 'https://shiprocket.co/tracking/{AWB}',
                'contact'      => 'support@shiprocket.in',
                'badge_color'  => '#7c3aed',
            ],
            'dtdc' => [
                'name'         => 'DTDC Express Premier',
                'type'         => 'Domestic Heavy Cargo',
                'code'         => 'DTDC',
                'is_active'    => false,
                'is_default'   => false,
                'tracking_url' => 'https://www.dtdc.in/tracking/shipment-tracking.asp?awb={AWB}',
                'contact'      => '080-25365032',
                'badge_color'  => '#ea580c',
            ],
        ];
    }

    /**
     * Update courier partner details.
     */
    public static function updateCourierPartner(string $partnerKey, array $data): bool {
        $partners = self::getCourierPartners();
        if (!isset($partners[$partnerKey])) {
            return false;
        }

        $partners[$partnerKey]['name'] = trim($data['name'] ?? $partners[$partnerKey]['name']);
        $partners[$partnerKey]['tracking_url'] = trim($data['tracking_url'] ?? $partners[$partnerKey]['tracking_url']);
        $partners[$partnerKey]['is_active'] = !empty($data['is_active']);

        if (!empty($data['is_default'])) {
            foreach ($partners as $k => $v) {
                $partners[$k]['is_default'] = ($k === $partnerKey);
            }
        }

        $json = json_encode($partners, JSON_UNESCAPED_SLASHES);
        self::setSiteSetting('courier_partners_config', $json, 'Courier Partners Configuration');
        return true;
    }

    // =========================================================================
    // 5. LIVE PINCODE & SHIPPING RATE CALCULATOR ENGINE
    // =========================================================================

    /**
     * Calculate shipping methods, charges, transit times, and COD compatibility for a given PIN code.
     */
    public static function calculateShipping(string $pincode, float $orderSubtotal = 0.0, int $weightGrams = 500): array {
        $cleanPincode = preg_replace('/[^0-9]/', '', trim($pincode));

        if (strlen($cleanPincode) !== 6) {
            return [
                'valid'       => false,
                'pincode'     => $pincode,
                'message'     => 'Invalid postal code. Must be a valid 6-digit Indian PIN code.',
                'zone'        => null,
                'methods'     => [],
                'cod'         => ['eligible' => false, 'reason' => 'Invalid postal code'],
            ];
        }

        $zones = self::getZones(true); // Active zones
        $globalSettings = self::getGlobalSettings();

        // 1. Identify Zone
        $matchedZone = null;
        $roiZone = null;

        foreach ($zones as $z) {
            if ($z['zone_code'] === 'ROI' || stripos($z['name'], 'Rest of India') !== false) {
                $roiZone = $z;
            }

            // Check if clean pincode is present in zone's pincode list
            if (!empty($z['pincodes']) && preg_match('/\b' . $cleanPincode . '\b/', $z['pincodes'])) {
                $matchedZone = $z;
                break;
            }
        }

        // Fall back to Rest of India if not in specialized zone
        if (!$matchedZone) {
            $matchedZone = $roiZone ?? ($zones[0] ?? [
                'id'          => 0,
                'name'        => 'Rest of India (Standard Zone)',
                'zone_code'   => 'ROI',
                'description' => 'Pan-India standard delivery territory',
            ]);
        }

        // 2. Fetch Rates for Matched Zone
        $zoneRates = self::getRates($matchedZone['id']);
        $applicableMethods = [];

        $globalFreeThreshold = $globalSettings['free_shipping_threshold'];
        $qualifiesGlobalFree = ($orderSubtotal > 0 && $orderSubtotal >= $globalFreeThreshold);

        foreach ($zoneRates as $r) {
            if (empty($r['is_active'])) {
                continue;
            }

            // Weight filter
            if ($r['weight_to_g'] > 0 && ($weightGrams < $r['weight_from_g'] || $weightGrams > $r['weight_to_g'])) {
                continue;
            }

            $methodKey = $r['method']; // 'standard', 'express', 'free'
            $baseFee = (float)$r['flat_rate'];
            $freeAbove = $r['free_above_order_value'] !== null ? (float)$r['free_above_order_value'] : $globalFreeThreshold;

            $isFree = false;
            $fee = $baseFee;

            if ($methodKey === 'free') {
                $isFree = true;
                $fee = 0.00;
            } elseif ($qualifiesGlobalFree || ($orderSubtotal > 0 && $orderSubtotal >= $freeAbove)) {
                $isFree = true;
                $fee = 0.00;
            }

            // Express check
            if ($methodKey === 'express' && empty($globalSettings['express_shipping_enabled'])) {
                continue;
            }

            $applicableMethods[$methodKey] = [
                'method'         => $methodKey,
                'title'          => $r['title'],
                'fee'            => $fee,
                'base_fee'       => $baseFee,
                'is_free'        => $isFree,
                'free_above'     => $freeAbove,
                'estimated_days' => $r['estimated_days'],
            ];
        }

        // Fallback default standard rate if no specific slab was configured
        if (empty($applicableMethods)) {
            $stdFee = $qualifiesGlobalFree ? 0.00 : $globalSettings['default_shipping_fee'];
            $applicableMethods['standard'] = [
                'method'         => 'standard',
                'title'          => 'Standard Surface Cargo',
                'fee'            => $stdFee,
                'base_fee'       => $globalSettings['default_shipping_fee'],
                'is_free'        => $qualifiesGlobalFree,
                'free_above'     => $globalFreeThreshold,
                'estimated_days' => '4-6 Business Days',
            ];

            if (!empty($globalSettings['express_shipping_enabled'])) {
                $applicableMethods['express'] = [
                    'method'         => 'express',
                    'title'          => 'Express Air Priority',
                    'fee'            => $globalSettings['default_express_fee'],
                    'base_fee'       => $globalSettings['default_express_fee'],
                    'is_free'        => false,
                    'free_above'     => null,
                    'estimated_days' => '2-3 Business Days',
                ];
            }
        }

        // 3. Cross-reference COD Eligibility
        $codCheck = PaymentGateway::isPincodeEligibleForCOD($cleanPincode, $orderSubtotal > 0 ? $orderSubtotal : 1000.0);

        // 4. Threshold progression message
        $thresholdMessage = '';
        if (!$qualifiesGlobalFree && $orderSubtotal > 0 && $orderSubtotal < $globalFreeThreshold) {
            $diff = $globalFreeThreshold - $orderSubtotal;
            $thresholdMessage = "Add ₹" . number_format($diff) . " more to unlock Free Luxury Delivery!";
        } elseif ($qualifiesGlobalFree) {
            $thresholdMessage = "Qualified for Free Doorstep Luxury Delivery!";
        }

        return [
            'valid'             => true,
            'pincode'           => $cleanPincode,
            'zone'              => [
                'id'          => $matchedZone['id'],
                'name'        => $matchedZone['name'],
                'zone_code'   => $matchedZone['zone_code'],
                'description' => $matchedZone['description'],
            ],
            'methods'           => array_values($applicableMethods),
            'cod'               => $codCheck,
            'order_subtotal'    => $orderSubtotal,
            'weight_grams'      => $weightGrams,
            'threshold_message' => $thresholdMessage,
        ];
    }

    // =========================================================================
    // 6. EXECUTIVE SHIPPING KPIS
    // =========================================================================

    /**
     * Compute executive statistics for shipping operations.
     */
    public static function getShippingKPIs(): array {
        try {
            $db = Database::connect();
            $zones = self::getZones();
            $global = self::getGlobalSettings();

            $activeZones = 0;
            $totalMappedPins = 0;

            foreach ($zones as $z) {
                if (!empty($z['is_active'])) {
                    $activeZones++;
                }
                $totalMappedPins += $z['pincode_count'];
            }

            $rateRow = $db->query("
                SELECT 
                    COUNT(*) AS total_rates,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_rates,
                    COALESCE(AVG(flat_rate), 0) AS avg_rate
                FROM shipping_rates
            ")->fetch_assoc();

            $couriers = self::getCourierPartners();
            $activeCouriers = 0;
            foreach ($couriers as $c) {
                if (!empty($c['is_active'])) {
                    $activeCouriers++;
                }
            }

            return [
                'total_zones'            => count($zones),
                'active_zones'           => $activeZones,
                'total_mapped_pincodes'  => $totalMappedPins,
                'active_rates'           => (int)($rateRow['active_rates'] ?? 0),
                'total_rates'            => (int)($rateRow['total_rates'] ?? 0),
                'avg_shipping_fee'       => (int)round((float)($rateRow['avg_rate'] ?? 150)),
                'free_shipping_limit'    => (int)round($global['free_shipping_threshold']),
                'active_couriers'        => $activeCouriers,
                'total_couriers'         => count($couriers),
            ];
        } catch (Exception $e) {
            error_log("ShippingZone::getShippingKPIs error: " . $e->getMessage());
            return [
                'total_zones'            => 4,
                'active_zones'           => 4,
                'total_mapped_pincodes'  => 50,
                'active_rates'           => 8,
                'total_rates'            => 8,
                'avg_shipping_fee'       => 150,
                'free_shipping_limit'    => 2999,
                'active_couriers'        => 3,
                'total_couriers'         => 4,
            ];
        }
    }

    // =========================================================================
    // 7. ENSURE DEFAULTS (SEEDS STANDARD INDIAN LUXURY ZONES & RATES)
    // =========================================================================

    /**
     * Seed initial standard Indian delivery zones, rate slabs, and global settings.
     */
    public static function ensureDefaults(): void {
        try {
            $db = Database::connect();

            // Check if zones exist
            $checkZ = $db->query("SELECT COUNT(*) FROM shipping_zones");
            $countZ = $checkZ ? (int)$checkZ->fetch_row()[0] : 0;

            if ($countZ === 0) {
                // Zone 1: Metro Capitals
                $metroPins = "400001, 400050, 400078, 110001, 110016, 110024, 560001, 560034, 560068, 500001, 500034, 500081, 700001, 700019, 700091, 600001, 600028, 600086, 411001, 411004, 411014, 380001, 380015";
                $db->query("
                    INSERT INTO shipping_zones (name, zone_code, description, pincodes, is_active, created_at, updated_at)
                    VALUES (
                        'Metro Express Hubs',
                        'METRO',
                        'Direct air-connected major metropolitan cities (Mumbai, Delhi NCR, Bengaluru, Hyderabad, Kolkata, Chennai, Pune, Ahmedabad)',
                        '$metroPins',
                        1,
                        NOW(),
                        NOW()
                    )
                ");
                $metroId = $db->insert_id;

                // Zone 2: Tier 1 & 2 Heritage Cities
                $tierPins = "302001, 302015, 302020, 226001, 226010, 160001, 160017, 395001, 452001, 682001, 462001, 390001, 641001";
                $db->query("
                    INSERT INTO shipping_zones (name, zone_code, description, pincodes, is_active, created_at, updated_at)
                    VALUES (
                        'Tier 1 & 2 Heritage Hubs',
                        'TIER1_2',
                        'Key commercial and royal cultural centers (Jaipur, Lucknow, Chandigarh, Surat, Indore, Kochi, Bhopal, Vadodara)',
                        '$tierPins',
                        1,
                        NOW(),
                        NOW()
                    )
                ");
                $tierId = $db->insert_id;

                // Zone 3: Rest of India (Catch-all)
                $db->query("
                    INSERT INTO shipping_zones (name, zone_code, description, pincodes, is_active, created_at, updated_at)
                    VALUES (
                        'Rest of India (Pan-India)',
                        'ROI',
                        'All other Indian states, districts, and pin codes outside designated express clusters',
                        '',
                        1,
                        NOW(),
                        NOW()
                    )
                ");
                $roiId = $db->insert_id;

                // Zone 4: Special Remote & Hill States
                $remotePins = "190001, 194101, 795001, 793001, 797001, 799001, 790001, 796001, 744101, 682555";
                $db->query("
                    INSERT INTO shipping_zones (name, zone_code, description, pincodes, is_active, created_at, updated_at)
                    VALUES (
                        'Special Remote & Hill States',
                        'REMOTE',
                        'High-altitude and island postal routes (Jammu & Kashmir, Ladakh, North-East, Andaman & Nicobar)',
                        '$remotePins',
                        1,
                        NOW(),
                        NOW()
                    )
                ");
                $remoteId = $db->insert_id;

                // Seed Rate Slabs
                // Metro rates
                $db->query("
                    INSERT INTO shipping_rates (zone_id, method, title, weight_from_g, weight_to_g, flat_rate, free_above_order_value, estimated_days, is_active, created_at, updated_at)
                    VALUES 
                    ($metroId, 'standard', 'Standard Surface Cargo', 0, 5000, 99.00, 2499.00, '2-3 Business Days', 1, NOW(), NOW()),
                    ($metroId, 'express', 'Express Air Priority (24-48h)', 0, 5000, 199.00, 4999.00, '1-2 Business Days', 1, NOW(), NOW())
                ");

                // Tier 1 & 2 rates
                $db->query("
                    INSERT INTO shipping_rates (zone_id, method, title, weight_from_g, weight_to_g, flat_rate, free_above_order_value, estimated_days, is_active, created_at, updated_at)
                    VALUES 
                    ($tierId, 'standard', 'Standard Surface Cargo', 0, 5000, 149.00, 2999.00, '3-4 Business Days', 1, NOW(), NOW()),
                    ($tierId, 'express', 'Express Air Priority', 0, 5000, 249.00, 5999.00, '2 Business Days', 1, NOW(), NOW())
                ");

                // ROI rates
                $db->query("
                    INSERT INTO shipping_rates (zone_id, method, title, weight_from_g, weight_to_g, flat_rate, free_above_order_value, estimated_days, is_active, created_at, updated_at)
                    VALUES 
                    ($roiId, 'standard', 'Pan-India Surface Courier', 0, 5000, 199.00, 2999.00, '4-6 Business Days', 1, NOW(), NOW()),
                    ($roiId, 'express', 'Express Air Courier', 0, 5000, 299.00, 7999.00, '2-3 Business Days', 1, NOW(), NOW())
                ");

                // Remote rates
                $db->query("
                    INSERT INTO shipping_rates (zone_id, method, title, weight_from_g, weight_to_g, flat_rate, free_above_order_value, estimated_days, is_active, created_at, updated_at)
                    VALUES 
                    ($remoteId, 'standard', 'Insured Hill & Island Cargo', 0, 5000, 299.00, 4999.00, '5-8 Business Days', 1, NOW(), NOW())
                ");
            }

            // Ensure global settings exist
            $checkSet = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'free_shipping_threshold'");
            if (!$checkSet || $checkSet->num_rows === 0) {
                self::updateGlobalSettings([
                    'free_shipping_threshold'    => 2999.00,
                    'default_shipping_fee'       => 150.00,
                    'default_express_fee'        => 250.00,
                    'express_shipping_enabled'   => 1,
                    'same_day_delivery_enabled'  => 0,
                    'luxury_packaging_fee'       => 0.00,
                ]);
            }
        } catch (Exception $e) {
            error_log("ShippingZone::ensureDefaults error: " . $e->getMessage());
        }
    }

    /**
     * Upsert a key-value pair in site_settings.
     */
    private static function setSiteSetting(string $key, string $value, string $label): void {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, label, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    label = VALUES(label),
                    updated_at = NOW()
            ");
            $stmt->bind_param("sss", $key, $value, $label);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("ShippingZone::setSiteSetting error: " . $e->getMessage());
        }
    }
}
