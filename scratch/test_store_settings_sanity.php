<?php
define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/StoreSetting.php';

use App\Models\StoreSetting;

echo "--- Testing StoreSetting Model Sanity ---\n";

// 1. Ensure Defaults
StoreSetting::ensureDefaults();
echo "[OK] StoreSetting::ensureDefaults() executed\n";

// 2. Read All Settings
$all = StoreSetting::getAllSettings();
echo "[OK] Total Groups loaded: " . count($all) . " (" . implode(', ', array_keys($all)) . ")\n";

foreach ($all as $groupKey => $group) {
    echo "  Group '{$groupKey}': " . count($group['settings']) . " settings\n";
}

// 3. Read specific values
$storeName = StoreSetting::get('store_name');
$whatsappNum = StoreSetting::get('whatsapp_number');
$metaPixel = StoreSetting::get('meta_pixel_id');
echo "[OK] Store Name: '$storeName'\n";
echo "[OK] WhatsApp: '$whatsappNum'\n";
echo "[OK] Meta Pixel ID: '$metaPixel'\n";

// 4. Test Update
$success = StoreSetting::updateSettings([
    'store_tagline' => 'Regal Splendor & Bespoke Indian Couture 2026',
    'whatsapp_button_title' => 'Chat with Bridal Stylist',
], 1);
echo "[OK] StoreSetting::updateSettings result: " . ($success ? "SUCCESS" : "FAILED") . "\n";
echo "[OK] Updated Tagline: '" . StoreSetting::get('store_tagline') . "'\n";

// 5. Test Toggle
$beforeToggle = StoreSetting::get('maintenance_mode');
StoreSetting::toggleSetting('maintenance_mode', 1);
$afterToggle = StoreSetting::get('maintenance_mode');
echo "[OK] Maintenance toggle: $beforeToggle -> $afterToggle\n";
// Revert back
StoreSetting::toggleSetting('maintenance_mode', 1);
echo "[OK] Maintenance reverted to: " . StoreSetting::get('maintenance_mode') . "\n";

// 6. Test KPIs
$kpis = StoreSetting::getKPIs();
echo "[OK] Store KPIs: " . json_encode($kpis, JSON_PRETTY_PRINT) . "\n";
