<?php
/**
 * Seed test coupons for development — clean version.
 * starts_at = null means "immediately active" (no scheduled start).
 */
$conn = new mysqli('localhost', 'root', '', 'jiyaji_collection');
if ($conn->connect_errno) die("Connection failed: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

// Clear existing seeded data
$codes = ['WELCOME200','FLAT500','SUMMER10','FLASH50','BULK15','JIYAJIFEST','EXPIREDTEST','UNLIMITED20'];
$placeholders = implode(',', array_fill(0, count($codes), '?'));
$del = $conn->prepare("DELETE FROM coupon_usages WHERE coupon_id IN (SELECT id FROM coupons WHERE code IN ($placeholders))");
$del->bind_param(str_repeat('s', count($codes)), ...$codes);
$del->execute();
$del2 = $conn->prepare("DELETE FROM coupons WHERE code IN ($placeholders)");
$del2->bind_param(str_repeat('s', count($codes)), ...$codes);
$del2->execute();
echo "Cleared old seed data.\n\n";

$future30 = date('Y-m-d H:i:s', strtotime('+30 days'));
$future2  = date('Y-m-d H:i:s', strtotime('+2 days'));
$future7  = date('Y-m-d H:i:s', strtotime('+7 days'));
$past10   = date('Y-m-d H:i:s', strtotime('-10 days'));
$future5d = date('Y-m-d H:i:s', strtotime('+5 days'));

/**
 * Coupon rows:
 * [code, description, type, value, max_cap, min_cart, usage_global, usage_per_user, is_public, starts_at, expires_at, times_used]
 * NULL for starts_at = immediately active (no date stored in DB, avoids zero-date bug)
 * NULL for expires_at = no expiry
 * NULL for usage_global = unlimited
 * NULL for max_cap = no cap
 */
$coupons = [
    ['WELCOME200',  'New customer welcome discount',        'flat',       200,  null, 999,  null, 1, 1, null,       $future30, 0],
    ['FLAT500',     'Flat ₹500 off on orders above ₹2999', 'flat',       500,  null, 2999, 100,  1, 1, null,       $future30, 14],
    ['SUMMER10',    '10% summer sale — max ₹800 off',       'percentage', 10,   800,  500,  500,  2, 1, null,       $future30, 37],
    ['FLASH50',     'Flash sale: 50% off capped at ₹300',   'percentage', 50,   300,  0,    50,   1, 1, $future5d,  $future7,  2],
    ['BULK15',      '15% on bulk orders above ₹5000',       'percentage', 15,   1500, 5000, 200,  3, 1, null,       $future30, 3],
    ['JIYAJIFEST',  'Jiyaji Anniversary Festival Offer',    'flat',       1000, null, 4999, 25,   1, 1, null,       $future30, 3],
    ['EXPIREDTEST', 'Already expired test coupon',          'flat',       150,  null, 0,    null, 1, 0, null,       $past10,   2],
    ['UNLIMITED20', 'Unlimited 20% private code',           'percentage', 20,   2000, 1000, null, 5, 0, null,       null,      5],
];

$inserted = 0;
foreach ($coupons as $c) {
    [$code, $desc, $type, $value, $maxCap, $minCart, $usageGlobal, $usagePerUser, $isPublic, $startsAt, $expiresAt, $timesUsed] = $c;

    $stmt = $conn->prepare("
        INSERT INTO coupons
            (code, description, type, value, max_discount_cap, min_cart_value,
             usage_limit_global, usage_limit_per_user, is_public, starts_at, expires_at, is_active, times_used)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
    ");

    // Use explicit null binding for nullable fields
    $stmt->bind_param("sssdddiiissi",
        $code, $desc, $type, $value, $maxCap, $minCart,
        $usageGlobal, $usagePerUser, $isPublic, $startsAt, $expiresAt, $timesUsed
    );

    if ($stmt->execute()) {
        echo "✓ Inserted: $code\n";
        $inserted++;
    } else {
        echo "✗ Failed:   $code — " . $conn->error . "\n";
    }
}

echo "\nDone. Inserted $inserted / " . count($coupons) . " coupons.\n";

// Verify dates stored correctly
echo "\n--- Date Verification ---\n";
$r = $conn->query("SELECT code, starts_at, expires_at FROM coupons WHERE code IN ('$codes[0]','$codes[3]','$codes[6]') ORDER BY id DESC LIMIT 3");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['code']}: starts_at=" . ($row['starts_at'] ?? 'NULL') . " | expires_at=" . ($row['expires_at'] ?? 'NULL') . "\n";
}
