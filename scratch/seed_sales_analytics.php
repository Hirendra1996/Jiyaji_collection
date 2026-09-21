<?php
/**
 * Realistic Sales Analytics & Historical Financial Data Seeder
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Models/SalesAnalytics.php';

use App\Config\Database;
use App\Models\SalesAnalytics;

$db = Database::connect();
echo "Starting Sales Analytics Historical Seeder...\n";

// 1. Fetch available customers
$customerIds = [];
$cRes = $db->query("SELECT id FROM customers LIMIT 30");
if ($cRes) {
    while ($r = $cRes->fetch_assoc()) {
        $customerIds[] = (int)$r['id'];
    }
}

// 2. Fetch available products and variants
$products = [];
$pRes = $db->query("SELECT id, name, base_price, slug FROM products WHERE status = 'active' LIMIT 20");
if ($pRes) {
    while ($p = $pRes->fetch_assoc()) {
        $vRes = $db->query("SELECT id, sku FROM product_variants WHERE product_id = " . (int)$p['id'] . " LIMIT 1");
        $v = $vRes ? $vRes->fetch_assoc() : null;
        $products[] = [
            'id'         => (int)$p['id'],
            'name'       => $p['name'],
            'base_price' => (float)$p['base_price'],
            'variant_id' => $v ? (int)$v['id'] : null,
            'sku'        => $v ? $v['sku'] : 'JLX-SKU-' . rand(100, 999),
        ];
    }
}

if (empty($products)) {
    // Fallback luxury garments if DB is sparse
    $products = [
        ['id' => 1, 'name' => 'Royal Raw Silk Sherwani', 'base_price' => 24999.00, 'variant_id' => null, 'sku' => 'JLX-SHER-01'],
        ['id' => 2, 'name' => 'Handcrafted Bandhgala Suit', 'base_price' => 18499.00, 'variant_id' => null, 'sku' => 'JLX-BANDH-02'],
        ['id' => 3, 'name' => 'Bespoke Midnight Tuxedo', 'base_price' => 28999.00, 'variant_id' => null, 'sku' => 'JLX-TUX-03'],
        ['id' => 4, 'name' => 'Pure Mulberry Silk Kurta', 'base_price' => 8999.00, 'variant_id' => null, 'sku' => 'JLX-KURTA-04'],
        ['id' => 5, 'name' => 'Embroidered Velvet Blazer', 'base_price' => 15999.00, 'variant_id' => null, 'sku' => 'JLX-BLZ-05'],
    ];
}

$destinations = [
    ['city' => 'Mumbai',     'state' => 'Maharashtra', 'pincode' => '400001'],
    ['city' => 'New Delhi',  'state' => 'Delhi',       'pincode' => '110001'],
    ['city' => 'Bengaluru',  'state' => 'Karnataka',   'pincode' => '560001'],
    ['city' => 'Jaipur',     'state' => 'Rajasthan',   'pincode' => '302001'],
    ['city' => 'Hyderabad',  'state' => 'Telangana',   'pincode' => '500001'],
    ['city' => 'Kolkata',    'state' => 'West Bengal', 'pincode' => '700001'],
    ['city' => 'Chennai',    'state' => 'Tamil Nadu',  'pincode' => '600001'],
    ['city' => 'Pune',       'state' => 'Maharashtra', 'pincode' => '411001'],
    ['city' => 'Ahmedabad',  'state' => 'Gujarat',     'pincode' => '380001'],
];

$paymentMethods = [
    'Razorpay (UPI)', 'Razorpay (Card)', 'Razorpay (NetBanking)', 'COD'
];

$names = [
    'Vikramaditya Singhania', 'Kabir Malhotra', 'Arjun Kapoor', 'Devendra Oberoi',
    'Rohan Mittal', 'Siddharth Roy', 'Aditya Birla', 'Aarav Mehta', 'Ranveer Tandon',
    'Raghav Singhal', 'Aryan Goenka', 'Reyansh Vardhan', 'Karan Johar', 'Nikhil Advani'
];

// Check max order ID to avoid collision
$maxIdRow = $db->query("SELECT MAX(id) as max_id FROM orders")->fetch_assoc();
$startNum = 9025;

$orderStmt = $db->prepare("
    INSERT INTO orders (
        customer_id, order_number, status, shipping_name, shipping_phone,
        shipping_address1, shipping_city, shipping_state, shipping_pincode, shipping_country,
        subtotal, discount_amount, coupon_code_used, shipping_charge, tax_amount, grand_total,
        payment_method, payment_status, placed_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$itemStmt = $db->prepare("
    INSERT INTO order_items (order_id, variant_id, product_name, variant_info, sku, quantity, unit_price, line_total)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$ordersToSeed = 75;
$seededOrders = 0;

for ($i = 0; $i < $ordersToSeed; $i++) {
    $orderNum = 'JLX-ORD-' . ($startNum + $i);

    // Distribution: More orders in the last 30 days
    $daysAgo = rand(0, 88);
    if (rand(1, 100) <= 60) {
        $daysAgo = rand(0, 28);
    }
    $hoursAgo = rand(0, 23);
    $minsAgo  = rand(0, 59);
    $placedAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days -$hoursAgo hours -$minsAgo minutes"));

    $custId = !empty($customerIds) && (rand(1, 100) <= 65) ? $customerIds[array_rand($customerIds)] : null;
    $dest   = $destinations[array_rand($destinations)];
    $client = $names[array_rand($names)];
    $phone  = '+91 98' . rand(10000000, 99999999);
    $addr   = rand(10, 99) . ', Luxury Enclave, Prime Boulevard';

    // Status distributions
    $roll = rand(1, 100);
    if ($daysAgo > 7) {
        $status = ($roll <= 92) ? 'delivered' : 'cancelled';
    } else {
        $status = ($roll <= 45) ? 'delivered' : (($roll <= 75) ? 'shipped' : 'confirmed');
    }

    $payMethod = $paymentMethods[array_rand($paymentMethods)];
    $payStatus = ($status === 'cancelled') ? 'refunded' : (($payMethod === 'COD' && $status !== 'delivered') ? 'pending' : 'paid');

    // Pick 1 to 3 order items
    $itemCount = rand(1, 2);
    $subtotal = 0.0;
    $selectedProducts = [];

    for ($j = 0; $j < $itemCount; $j++) {
        $p = $products[array_rand($products)];
        $qty = 1;
        $unitPrice = $p['base_price'];
        $lineTotal = $unitPrice * $qty;
        $subtotal += $lineTotal;

        $selectedProducts[] = [
            'product_name' => $p['name'],
            'variant_id'   => $p['variant_id'],
            'sku'          => $p['sku'],
            'quantity'     => $qty,
            'unit_price'   => $unitPrice,
            'line_total'   => $lineTotal,
        ];
    }

    $discount = (rand(1, 100) <= 35) ? rand(500, 2000) : 0.0;
    if ($discount > $subtotal) $discount = 0.0;
    $couponCode = $discount > 0 ? 'ROYAL' . rand(10, 50) : null;

    $tax = round(($subtotal - $discount) * 0.05); // 5% GST
    $shipping = ($subtotal > 15000) ? 0.0 : 250.0;
    $grandTotal = ($subtotal - $discount) + $tax + $shipping;

    $country = 'India';

    $orderStmt->bind_param(
        "isssssssssddddddssss",
        $custId, $orderNum, $status, $client, $phone,
        $addr, $dest['city'], $dest['state'], $dest['pincode'], $country,
        $subtotal, $discount, $couponCode, $shipping, $tax, $grandTotal,
        $payMethod, $payStatus, $placedAt, $placedAt
    );

    if ($orderStmt->execute()) {
        $orderId = $db->insert_id;
        $seededOrders++;

        foreach ($selectedProducts as $sp) {
            $vInfo = 'Size: L | Fabric: Pure Silk';
            $itemStmt->bind_param(
                "iisssidd",
                $orderId, $sp['variant_id'], $sp['product_name'], $vInfo,
                $sp['sku'], $sp['quantity'], $sp['unit_price'], $sp['line_total']
            );
            $itemStmt->execute();
        }
    }
}

$orderStmt->close();
$itemStmt->close();

echo "Seeded {$seededOrders} historical orders across the last 90 days!\n";

// 3. Synchronize daily_sales_summary
echo "Synchronizing daily_sales_summary table...\n";
$syncedDays = SalesAnalytics::syncDailySummary();
echo "Successfully synchronized {$syncedDays} daily sales summary records!\n";

// 4. Verify in DB
$verifyKPIs = SalesAnalytics::getFinancialKPIs(['period' => 'all']);
echo "\nVerification of Financial KPIs (All Time):\n";
echo "  Gross Revenue:    " . currency($verifyKPIs['gross_revenue']) . "\n";
echo "  Net Revenue:      " . currency($verifyKPIs['net_revenue']) . "\n";
echo "  Total Orders:     " . $verifyKPIs['total_orders'] . " (Paid: {$verifyKPIs['paid_orders']} - {$verifyKPIs['paid_rate']}%)\n";
echo "  Avg Order Value:  " . currency($verifyKPIs['avg_order_value']) . "\n";
echo "  Total Discounts:  " . currency($verifyKPIs['total_discounts']) . "\n";
echo "  Units Sold:       " . $verifyKPIs['units_sold'] . "\n";
