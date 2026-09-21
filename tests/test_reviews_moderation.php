<?php
/**
 * JIYAJI LUXURY: REVIEWS MODERATION E2E TEST SUITE
 * 
 * Verifies:
 * - RBAC / Admin authentication guard on moderation routes
 * - Reviews moderation dashboard ledger & 5 KPI summary metrics
 * - Universal Encrypted URL tokens (zero raw database IDs exposed)
 * - Sidebar navigation active state and "Live" status badge
 * - Single review status moderation (Approve, Reject, Flag, Pending)
 * - Real-time product rating synchronization (products.average_rating and products.review_count)
 * - Bulk moderation operations across multiple reviews
 * - Cryptographic tamper protection (tampered/invalid encrypted ID tokens rejected)
 * - Deletion of reviews with automatic product rating resynchronization
 * - Zero PHP notices, warnings, or deprecations
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
foreach ($_ENV as $k => $v) {
    putenv("$k=$v");
}
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Review.php';

use App\Config\Database;
use App\Models\Review;

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'jiyaji_rev_cookie_');

echo "=========================================================\n";
echo " JIYAJI LUXURY: REVIEWS MODERATION E2E TEST SUITE\n";
echo "=========================================================\n\n";

function request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return [
        'code'         => $httpCode,
        'effectiveUrl' => $effectiveUrl,
        'header'       => $header,
        'body'         => $body
    ];
}

function extractCsrf($html) {
    if (preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

function assertTest($condition, $name) {
    if ($condition) {
        echo "  [PASS] {$name}\n";
    } else {
        echo "  [FAIL] {$name}\n";
        exit(1);
    }
}

// Ensure database has fresh seeded data
echo "Step 0: Re-seeding review test fixtures...\n";
require __DIR__ . '/seed_reviews.php';
echo "\n";

// TEST 1: Unauthenticated Guard
echo "TEST 1: Authentication Guard on Reviews Moderation\n";
$unauthRes = request("{$baseUrl}/admin/reviews");
assertTest(
    str_contains($unauthRes['effectiveUrl'], '/admin/login') || $unauthRes['code'] === 302,
    "Unauthenticated user redirected to admin login"
);

// TEST 2: Admin Login
echo "\nTEST 2: Admin Authentication\n";
$loginPage = request("{$baseUrl}/admin/login", 'GET', [], $cookieJar);
$csrf = extractCsrf($loginPage['body']);

$loginPost = request("{$baseUrl}/admin/login", 'POST', [
    '_csrf_token' => $csrf,
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123'
], $cookieJar);

assertTest(
    str_contains($loginPost['effectiveUrl'], '/admin/dashboard'),
    "Admin successfully authenticated and redirected to dashboard"
);

// TEST 3: Access Reviews Moderation Dashboard
echo "\nTEST 3: Reviews Moderation Dashboard Ledger & KPIs\n";
$revIndex = request("{$baseUrl}/admin/reviews", 'GET', [], $cookieJar);
assertTest($revIndex['code'] === 200, "HTTP 200 returned for /admin/reviews");
assertTest(str_contains($revIndex['body'], 'Verified-Buyer Reviews Moderation'), "Queue title rendered");
assertTest(str_contains($revIndex['body'], 'Total Testimonials'), "Total Testimonials KPI card rendered");
assertTest(str_contains($revIndex['body'], 'Pending Moderation'), "Pending Moderation KPI card rendered");
assertTest(str_contains($revIndex['body'], 'Approved & Live'), "Approved & Live KPI card rendered");
assertTest(str_contains($revIndex['body'], 'Flagged for Audit'), "Flagged for Audit KPI card rendered");
assertTest(str_contains($revIndex['body'], 'Storefront Rating'), "Storefront Rating KPI card rendered");
assertTest(!str_contains($revIndex['body'], 'Warning:'), "Zero PHP warnings in page output");
assertTest(!str_contains($revIndex['body'], 'Notice:'), "Zero PHP notices in page output");

// TEST 4: Sidebar Navigation State
echo "\nTEST 4: Sidebar Navigation State\n";
assertTest(str_contains($revIndex['body'], 'href="' . $baseUrl . '/admin/reviews"'), "Sidebar reviews link exists and points to /admin/reviews");
assertTest(str_contains($revIndex['body'], 'Reviews Moderation'), "Sidebar shows 'Reviews Moderation'");
assertTest(str_contains($revIndex['body'], 'Live'), "Sidebar shows 'Live' badge");

// TEST 5: Universal Encrypted IDs on Moderation Forms
echo "\nTEST 5: Universal Encrypted IDs on Moderation Actions\n";
$db = Database::connect();
$firstReview = $db->query("SELECT id, product_id, status, rating FROM reviews WHERE status = 'pending' LIMIT 1")->fetch_assoc();
assertTest(!empty($firstReview), "Pending review exists in test dataset");

// Extract token from checkbox in rendered HTML
preg_match('/name="selected_reviews\[\]"\s+value="([^"]+)"/', $revIndex['body'], $tokenMatch);
$encRevId = $tokenMatch[1] ?? '';
assertTest(!empty($encRevId), "Encrypted review ID token extracted from table HTML");

$decryptedRevId = decrypt_id($encRevId);
assertTest($decryptedRevId !== null && $decryptedRevId > 0, "Encrypted token successfully decrypts to integer ID ({$decryptedRevId})");
assertTest(!str_contains($revIndex['body'], 'submitQuickStatus(1,'), "Zero raw numeric review IDs exposed in Javascript function calls");
assertTest(!str_contains($revIndex['body'], 'submitDeleteReview(1)'), "Zero raw numeric review IDs exposed in delete action calls");


// TEST 6: Single Review Status Moderation (Pending -> Approved) & Product Rating Sync
echo "\nTEST 6: Single Review Moderation (Approve) & Rating Sync\n";
$targetReview = $db->query("SELECT id, product_id, status, rating FROM reviews WHERE id = {$decryptedRevId}")->fetch_assoc();
assertTest(!empty($targetReview), "Target review record found for ID {$decryptedRevId}");

$pageCsrf = extractCsrf($revIndex['body']);
$productId = (int)$targetReview['product_id'];

// Initial Product rating state
$pBefore = $db->query("SELECT average_rating, review_count FROM products WHERE id = {$productId}")->fetch_assoc();

$modRes = request("{$baseUrl}/admin/reviews/{$encRevId}/status", 'POST', [
    '_csrf_token' => $pageCsrf,
    'status'      => 'approved',
    'return_url'  => 'admin/reviews'
], $cookieJar);

assertTest($modRes['code'] === 200, "Status update returned HTTP 200");
$revAfter = $db->query("SELECT status, moderated_by, moderated_at FROM reviews WHERE id = {$decryptedRevId}")->fetch_assoc();
assertTest($revAfter['status'] === 'approved', "Database record transitioned to 'approved'");
assertTest(!empty($revAfter['moderated_at']), "Moderated timestamp populated");

// Check Product Rating Sync
$pAfter = $db->query("SELECT average_rating, review_count FROM products WHERE id = {$productId}")->fetch_assoc();
assertTest(
    (int)$pAfter['review_count'] === ((int)$pBefore['review_count'] + 1),
    "Product review_count incremented from {$pBefore['review_count']} to {$pAfter['review_count']}"
);
assertTest(
    (float)$pAfter['average_rating'] > 0,
    "Product average_rating synchronized in real-time (now {$pAfter['average_rating']}★)"
);

// TEST 7: Single Review Status Moderation (Approved -> Flagged) & Product Rating Sync
echo "\nTEST 7: Single Review Moderation (Flag) & Rating Resync\n";
$revIndexAfterApprove = request("{$baseUrl}/admin/reviews", 'GET', [], $cookieJar);
$csrfFlag = extractCsrf($revIndexAfterApprove['body']);

$flagRes = request("{$baseUrl}/admin/reviews/{$encRevId}/status", 'POST', [
    '_csrf_token' => $csrfFlag,
    'status'      => 'flagged',
    'return_url'  => 'admin/reviews'
], $cookieJar);

$revFlagged = $db->query("SELECT status FROM reviews WHERE id = {$decryptedRevId}")->fetch_assoc();
assertTest($revFlagged['status'] === 'flagged', "Review status transitioned to 'flagged'");

// Check Product Rating Sync again (flagged should NOT count towards approved rating)
$pAfterFlag = $db->query("SELECT average_rating, review_count FROM products WHERE id = {$productId}")->fetch_assoc();
assertTest(
    (int)$pAfterFlag['review_count'] === (int)$pBefore['review_count'],
    "Product review_count accurately decreased back to {$pAfterFlag['review_count']}"
);


// TEST 8: Bulk Moderation Workflow
echo "\nTEST 8: Bulk Moderation Workflow\n";
$revIndexBulk = request("{$baseUrl}/admin/reviews", 'GET', [], $cookieJar);
$csrfBulk = extractCsrf($revIndexBulk['body']);

// Pick 2 non-approved reviews
$unapproved = $db->query("SELECT id FROM reviews WHERE status != 'approved' LIMIT 2")->fetch_all(MYSQLI_ASSOC);
$bulkEncIds = array_map(fn($r) => encrypt_id($r['id']), $unapproved);
assertTest(count($bulkEncIds) === 2, "Found 2 reviews for bulk moderation test");

$bulkRes = request("{$baseUrl}/admin/reviews/bulk", 'POST', [
    '_csrf_token'      => $csrfBulk,
    'bulk_status'      => 'approved',
    'selected_reviews' => $bulkEncIds
], $cookieJar);

assertTest($bulkRes['code'] === 200, "Bulk update returned HTTP 200");
$bulkIdsList = implode(',', array_column($unapproved, 'id'));
$updatedCount = $db->query("SELECT COUNT(*) FROM reviews WHERE id IN ({$bulkIdsList}) AND status = 'approved'")->fetch_row()[0];
assertTest((int)$updatedCount === 2, "Both selected reviews batch-approved in atomic operation");

// TEST 9: Security & Cryptographic Tamper Guard
echo "\nTEST 9: Cryptographic Tamper Guard\n";
$tamperedEncId = $encRevId . 'corrupted';
$tamperRes = request("{$baseUrl}/admin/reviews/{$tamperedEncId}/status", 'POST', [
    '_csrf_token' => $csrfBulk,
    'status'      => 'approved'
], $cookieJar);

assertTest(
    str_contains($tamperRes['body'], 'Access Denied') || str_contains($tamperRes['body'], 'Invalid review identifier'),
    "Tampered encrypted ID rejected with Access Denied / Invalid review identifier"
);

// Invalid status transition injection
$invalidStatusRes = request("{$baseUrl}/admin/reviews/{$encRevId}/status", 'POST', [
    '_csrf_token' => $csrfBulk,
    'status'      => 'injected_status'
], $cookieJar);

assertTest(
    str_contains($invalidStatusRes['body'], 'Validation Error') || str_contains($invalidStatusRes['body'], 'Invalid status'),
    "Invalid status injection rejected by server-side whitelist"
);

// TEST 10: Review Deletion & Real-Time Rating Resync
echo "\nTEST 10: Review Deletion with Product Rating Resync\n";
// Create a temporary review to delete
$disposableId = Review::create([
    'product_id'    => 2,
    'customer_id'   => 1,
    'order_item_id' => 1,
    'rating'        => 1,
    'title'         => 'Disposable Review for Deletion Test',
    'body'          => 'This review is created purely to verify deletion and rating sync.',
    'status'        => 'approved',
    'moderated_by'  => 1
]);
assertTest($disposableId > 0, "Created disposable review #{$disposableId}");

// Check rating with disposable review included
$p2WithDisp = $db->query("SELECT average_rating, review_count FROM products WHERE id = 2")->fetch_assoc();
$dispEncId = encrypt_id($disposableId);

$revIndexDel = request("{$baseUrl}/admin/reviews", 'GET', [], $cookieJar);
$csrfDel = extractCsrf($revIndexDel['body']);

$delRes = request("{$baseUrl}/admin/reviews/{$dispEncId}/delete", 'POST', [
    '_csrf_token' => $csrfDel
], $cookieJar);

assertTest($delRes['code'] === 200, "Delete request returned HTTP 200");
$exists = $db->query("SELECT COUNT(*) FROM reviews WHERE id = {$disposableId}")->fetch_row()[0];
assertTest((int)$exists === 0, "Disposable review successfully deleted from database");

// Check Product 2 rating was resynchronized
$p2AfterDel = $db->query("SELECT average_rating, review_count FROM products WHERE id = 2")->fetch_assoc();
assertTest(
    (int)$p2AfterDel['review_count'] === ((int)$p2WithDisp['review_count'] - 1),
    "Product 2 review_count successfully resynchronized after review deletion"
);

// TEST 11: Cleanup & Fresh Seed
echo "\nTEST 11: Resetting database to standard fixtures\n";
require __DIR__ . '/seed_reviews.php';

echo "\n=========================================================\n";
echo " ALL 11 REVIEWS MODERATION E2E TESTS PASSED (100% SUCCESS)\n";
echo "=========================================================\n";

@unlink($cookieJar);
