<?php
/**
 * Coupons & Promos — Automated Test Suite
 * Tests the Coupon model methods, DB queries, and business rules.
 */

// Bootstrap
define('BASE_URL', '/Jiyaji_collection');
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');
putenv('APP_KEY=base64:SmxYZFhNMjAyNl9KaXlhSmlMWF9TZWN1cmVfS2V5XzkxOA==');

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Models/Coupon.php';

use App\Models\Coupon;

$tests = [];
$pass  = 0;
$fail  = 0;

function test(string $name, bool $result, string $detail = ''): void {
    global $tests, $pass, $fail;
    $tests[] = ['name' => $name, 'result' => $result, 'detail' => $detail];
    $result ? $pass++ : $fail++;
    echo ($result ? '✓' : '✗') . " $name" . ($detail ? " — $detail" : '') . "\n";
}

echo "==============================\n";
echo " Coupon & Promos Test Suite\n";
echo "==============================\n\n";

// ===========================================================================
// TEST 1: getAll — Default listing
// ===========================================================================
$data = Coupon::getAll([], 1, 20);
test('getAll returns array with coupons key', is_array($data) && isset($data['coupons']), 'array structure');
test('getAll returns pagination key', isset($data['pagination']), 'pagination present');
test('getAll pagination has required keys', isset($data['pagination']['total'], $data['pagination']['total_pages'], $data['pagination']['has_prev'], $data['pagination']['has_next']), 'all pagination keys');

// ===========================================================================
// TEST 2: getAll — Filter by type
// ===========================================================================
$flat = Coupon::getAll(['type' => 'flat'], 1, 50);
$allFlat = true;
foreach ($flat['coupons'] as $c) {
    if ($c['type'] !== 'flat') { $allFlat = false; break; }
}
test('getAll filters by type=flat correctly', $allFlat && !empty($flat['coupons']), 'type filter');

$pct = Coupon::getAll(['type' => 'percentage'], 1, 50);
$allPct = true;
foreach ($pct['coupons'] as $c) {
    if ($c['type'] !== 'percentage') { $allPct = false; break; }
}
test('getAll filters by type=percentage correctly', $allPct && !empty($pct['coupons']), 'percentage filter');

// ===========================================================================
// TEST 3: getAll — Search by code
// ===========================================================================
$search = Coupon::getAll(['search' => 'WELCOME'], 1, 10);
$found  = false;
foreach ($search['coupons'] as $c) {
    if (stripos($c['code'], 'WELCOME') !== false) { $found = true; break; }
}
test('getAll search by code works', $found, 'search=WELCOME');

// ===========================================================================
// TEST 4: getAll — Status filter
// ===========================================================================
$expired = Coupon::getAll(['status' => 'expired'], 1, 50);
test('getAll returns expired coupons', isset($expired['coupons']), 'expired status filter');

// ===========================================================================
// TEST 5: getKPIs
// ===========================================================================
$kpis = Coupon::getKPIs();
test('getKPIs returns expected keys', isset($kpis['total_coupons'], $kpis['active_count'], $kpis['expired_count'], $kpis['expiring_soon'], $kpis['total_redemptions'], $kpis['total_savings']), 'all KPI keys present');
test('getKPIs total_coupons is int >= 8', is_int($kpis['total_coupons']) && $kpis['total_coupons'] >= 8, "total={$kpis['total_coupons']}");
test('getKPIs active_count is non-negative', $kpis['active_count'] >= 0, "active={$kpis['active_count']}");

// ===========================================================================
// TEST 6: codeExists
// ===========================================================================
test('codeExists returns true for WELCOME200', Coupon::codeExists('WELCOME200'), 'known code');
test('codeExists is case insensitive (lowercase)', Coupon::codeExists('welcome200'), 'lowercase match');
test('codeExists returns false for non-existent code', !Coupon::codeExists('XYZNONEXISTENT999'), 'unknown code');

// ===========================================================================
// TEST 7: find
// ===========================================================================
$allCoupons = Coupon::getAll([], 1, 5);
$testCoupon = $allCoupons['coupons'][0] ?? null;

if ($testCoupon) {
    $found = Coupon::find($testCoupon['id']);
    test('find returns correct coupon by ID', $found && $found['id'] === $testCoupon['id'], "id={$testCoupon['id']}");
    test('find includes computed_status', isset($found['computed_status']), 'computed_status key');
    test('find includes category_restrictions array', isset($found['category_restrictions']) && is_array($found['category_restrictions']), 'restrictions');
    test('find includes product_restrictions array', isset($found['product_restrictions']) && is_array($found['product_restrictions']), 'product restrictions');
} else {
    test('find — skipped (no coupons in DB)', false, 'no test data');
    test('find includes computed_status', false);
    test('find includes category_restrictions array', false);
    test('find includes product_restrictions array', false);
}

test('find returns null for invalid ID', Coupon::find(999999) === null, 'id=999999');

// ===========================================================================
// TEST 8: create
// ===========================================================================
$uniqueCode = 'TESTCOUPON' . time();
$newId = Coupon::create([
    'code'                => $uniqueCode,
    'description'         => 'Automated test coupon',
    'type'                => 'flat',
    'value'               => 100,
    'max_discount_cap'    => '',
    'min_cart_value'      => 500,
    'usage_limit_global'  => 10,
    'usage_limit_per_user'=> 1,
    'is_public'           => 1,
    'starts_at'           => '',
    'expires_at'          => '',
    'created_by'          => null,
]);
test('create returns a new integer ID', is_int($newId) && $newId > 0, "id=$newId");

$created = $newId ? Coupon::find($newId) : null;
test('created coupon is retrievable via find', $created !== null, $uniqueCode);
test('created coupon has correct code', $created && $created['code'] === $uniqueCode, 'code match');
test('created coupon has correct value', $created && (int)$created['value'] === 100, 'value match');

// ===========================================================================
// TEST 9: update
// ===========================================================================
if ($newId) {
    $updatedCode = 'UPDTD' . strtoupper(substr(md5($newId), 0, 6));
    $result = Coupon::update($newId, [
        'code'                => $updatedCode,
        'description'         => 'Updated description',
        'type'                => 'percentage',
        'value'               => 15,
        'max_discount_cap'    => 500,
        'min_cart_value'      => 1000,
        'usage_limit_global'  => 50,
        'usage_limit_per_user'=> 2,
        'is_public'           => 0,
        'starts_at'           => '',
        'expires_at'          => date('Y-m-d H:i:s', strtotime('+7 days')),
    ]);
    test('update returns true', $result === true, "update result");
    $updated = Coupon::find($newId);
    test('updated coupon has new code', $updated && $updated['code'] === $updatedCode, "code=$updatedCode");
    test('updated coupon has new type', $updated && $updated['type'] === 'percentage', 'type=percentage');
}

// ===========================================================================
// TEST 10: toggleStatus
// ===========================================================================
if ($newId) {
    $current = Coupon::find($newId);
    $originalStatus = (bool)$current['is_active'];
    $toggled = Coupon::toggleStatus($newId);
    test('toggleStatus returns bool', is_bool($toggled), 'returns bool');
    test('toggleStatus flips the status', $toggled !== $originalStatus, "was=$originalStatus, now=$toggled");
    // Toggle back
    Coupon::toggleStatus($newId);
}

// ===========================================================================
// TEST 11: generateCode
// ===========================================================================
$code1 = Coupon::generateCode('JIYAJI');
$code2 = Coupon::generateCode('SALE');
test('generateCode returns non-empty string', !empty($code1), "code=$code1");
test('generateCode uses prefix', str_starts_with($code1, 'JIYAJI'), "prefix=JIYAJI");
test('generateCode returns unique codes', $code1 !== $code2, 'unique');

// ===========================================================================
// TEST 12: getUsageHistory
// ===========================================================================
if ($testCoupon) {
    $history = Coupon::getUsageHistory($testCoupon['id'], 10);
    test('getUsageHistory returns array', is_array($history), 'array type');
}

// ===========================================================================
// TEST 13: delete — cleanup test coupon
// ===========================================================================
if ($newId) {
    $deleted = Coupon::delete($newId);
    test('delete removes the test coupon', $deleted === true, "id=$newId");
    test('deleted coupon is no longer findable', Coupon::find($newId) === null, 'find returns null');
}

// ===========================================================================
// SUMMARY
// ===========================================================================
echo "\n==============================\n";
echo " Results: $pass passed, $fail failed\n";
echo "==============================\n";

if ($fail === 0) {
    echo " ✅ ALL TESTS PASSED\n";
} else {
    echo " ❌ $fail TEST(S) FAILED\n";
    echo "\n Failed Tests:\n";
    foreach ($tests as $t) {
        if (!$t['result']) echo "   - {$t['name']}" . ($t['detail'] ? " ({$t['detail']})" : '') . "\n";
    }
}
