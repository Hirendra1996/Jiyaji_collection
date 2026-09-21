<?php

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/PaymentGateway.php';
require_once __DIR__ . '/../app/Models/ShippingZone.php';

use App\Models\ShippingZone;

echo "=== Testing ShippingZone Model ===\n";

ShippingZone::ensureDefaults();
echo "✓ ensureDefaults executed successfully.\n";

$zones = ShippingZone::getZones();
echo "Zones count: " . count($zones) . "\n";
foreach ($zones as $z) {
    echo " - Zone #{$z['id']} [{$z['zone_code']}] {$z['name']} | Pins: {$z['pincode_count']} | Rates: {$z['rates_count']}\n";
}

$kpis = ShippingZone::getShippingKPIs();
echo "\nShipping KPIs:\n";
print_r($kpis);

$calcMetro = ShippingZone::calculateShipping('400001', 3500.0, 800);
echo "\nCalculate for 400001 (₹3,500, 800g):\n";
echo "Zone: " . $calcMetro['zone']['name'] . "\n";
echo "Methods count: " . count($calcMetro['methods']) . "\n";
foreach ($calcMetro['methods'] as $m) {
    echo "  * {$m['title']}: ₹{$m['fee']} (Free: " . ($m['is_free'] ? 'Yes' : 'No') . ", Est: {$m['estimated_days']})\n";
}
echo "COD Eligible: " . ($calcMetro['cod']['eligible'] ? 'Yes' : 'No') . " (" . $calcMetro['cod']['reason'] . ")\n";

$calcROI = ShippingZone::calculateShipping('999999', 1200.0, 1500);
echo "\nCalculate for unmapped PIN 999999 (₹1,200, 1500g):\n";
echo "Zone: " . $calcROI['zone']['name'] . "\n";
foreach ($calcROI['methods'] as $m) {
    echo "  * {$m['title']}: ₹{$m['fee']} (Free: " . ($m['is_free'] ? 'Yes' : 'No') . ", Est: {$m['estimated_days']})\n";
}
echo "Threshold msg: " . $calcROI['threshold_message'] . "\n";

echo "\nDone!\n";
