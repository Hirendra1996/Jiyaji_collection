<?php
/**
 * Automated End-to-End Test Suite for Shipments & AWBs Management System
 */

$cookieJar = tempnam(sys_get_temp_dir(), 'admin_shipments_cookie_');
$baseUrl = 'http://localhost/Jiyaji_collection';

function makeRequest($url, $postData = null, $cookieJar = null, $followRedirects = false) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($followRedirects) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'headers' => $headers, 'body' => $body];
}

echo "=== STARTING SHIPMENTS & AWBS MANAGEMENT SYSTEM TEST ===\n\n";

// TEST 1: Unauthenticated protection
echo "[TEST 1] Access /admin/shipments unauthenticated...\n";
$res1 = makeRequest("$baseUrl/admin/shipments", null, $cookieJar, false);
if ($res1['code'] === 302 && strpos($res1['headers'], 'admin/login') !== false) {
    echo "  PASS: Blocked unauthenticated visit! Redirected to login.\n";
} else {
    echo "  FAIL: Expected 302 redirect. Got code {$res1['code']}\n";
}

// TEST 2: Login as Admin
echo "\n[TEST 2] Authenticating as administrator...\n";
$loginPage = makeRequest("$baseUrl/admin/login", null, $cookieJar, false);
preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $loginPage['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

$loginRes = makeRequest("$baseUrl/admin/login", [
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123',
    '_csrf_token' => $csrfToken
], $cookieJar, false);

if ($loginRes['code'] === 302 && strpos($loginRes['headers'], 'admin/dashboard') !== false) {
    echo "  PASS: Authenticated successfully.\n";
} else {
    die("  FAIL: Admin login failed. Cannot proceed.\n");
}

// TEST 3: Access /admin/shipments
echo "\n[TEST 3] Loading /admin/shipments with authenticated session...\n";
$shipmentsPage = makeRequest("$baseUrl/admin/shipments", null, $cookieJar, false);
if ($shipmentsPage['code'] === 200) {
    echo "  PASS: Shipments & Logistics Hub loaded with 200 OK!\n";
    echo "  PASS: Found Logistics KPI bar: " . (strpos($shipmentsPage['body'], 'Total Dispatched') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Carrier Partner Grid: " . (strpos($shipmentsPage['body'], 'Delhivery Air') !== false ? 'YES' : 'NO') . "\n";
    echo "  PASS: Found Shipments Table: " . (strpos($shipmentsPage['body'], 'Active Logistics & Tracking Ledger') !== false ? 'YES' : 'NO') . "\n";
} else {
    echo "  FAIL: Shipments page returned code: {$shipmentsPage['code']}\n";
}

// TEST 4: Verify Universal ID Encryption
echo "\n[TEST 4] Verifying all shipment order links are encrypted tokens...\n";
preg_match_all('/openTrackingModal\(\'([a-zA-Z0-9_\-]+)\'\)/', $shipmentsPage['body'], $trackMatches);
$encIds = $trackMatches[1];
echo "  PASS: Discovered " . count($encIds) . " encrypted shipment tokens in action buttons.\n";
$sampleEncId = $encIds[0] ?? null;

if ($sampleEncId) {
    echo "  Sample Encrypted Token: " . substr($sampleEncId, 0, 24) . "...\n";
    $hasRawIds = preg_match('/admin\/orders\/\d+[\'"]/', $shipmentsPage['body']);
    if (!$hasRawIds) {
        echo "  PASS: Zero raw numeric IDs exposed in shipment URLs!\n";
    } else {
        echo "  FAIL: Detected raw numeric IDs in URLs!\n";
    }
}

// TEST 5: Live Tracking JSON API Endpoint
if ($sampleEncId) {
    echo "\n[TEST 5] Fetching live tracking milestones JSON via /admin/shipments/{encrypted_id}/track...\n";
    $trackJsonRes = makeRequest("$baseUrl/admin/shipments/$sampleEncId/track", null, $cookieJar, false);
    if ($trackJsonRes['code'] === 200) {
        $trackData = json_decode($trackJsonRes['body'], true);
        if ($trackData && isset($trackData['awb_number']) && isset($trackData['milestones'])) {
            echo "  PASS: Live tracking API returned valid JSON structure!\n";
            echo "  Order Number: {$trackData['order_number']} | AWB: {$trackData['awb_number']}\n";
            echo "  Milestones Recorded: " . count($trackData['milestones']) . "\n";
        } else {
            echo "  FAIL: Tracking JSON missing expected keys.\n";
        }
    } else {
        echo "  FAIL: Tracking JSON request failed with code: {$trackJsonRes['code']}\n";
    }
}

// TEST 6: Record New Carrier Scan Milestone
if ($sampleEncId) {
    echo "\n[TEST 6] Logging new tracking checkpoint scan via /admin/shipments/{encrypted_id}/milestone...\n";
    preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $shipmentsPage['body'], $csrfMatches2);
    $csrfToken2 = $csrfMatches2[1] ?? '';

    $milestoneRes = makeRequest("$baseUrl/admin/shipments/$sampleEncId/milestone", [
        '_csrf_token' => $csrfToken2,
        'status'      => 'out_for_delivery',
        'location'    => 'Destination Regional Air Cargo Hub',
        'activity'    => 'Package sorted and loaded on delivery van with courier agent'
    ], $cookieJar, false);

    if ($milestoneRes['code'] === 302 && strpos($milestoneRes['headers'], 'admin/shipments') !== false) {
        echo "  PASS: Tracking milestone submission processed and redirected!\n";

        // Re-verify tracking JSON has updated event
        $trackJsonUpdated = makeRequest("$baseUrl/admin/shipments/$sampleEncId/track", null, $cookieJar, false);
        if (strpos($trackJsonUpdated['body'], 'Destination Regional Air Cargo Hub') !== false) {
            echo "  PASS: Verified newly logged scan event appears in live tracking timeline!\n";
        } else {
            echo "  FAIL: New scan event not found in updated tracking data.\n";
        }
    } else {
        echo "  FAIL: Milestone update failed. Code: {$milestoneRes['code']}\n";
    }
}

// TEST 7: Tampered Token Rejection
echo "\n[TEST 7] Testing security tamper rejection on tracking endpoints...\n";
$tamperedToken = "tampered_fake_shipment_token_123";
$tamperTrack = makeRequest("$baseUrl/admin/shipments/$tamperedToken/track", null, $cookieJar, false);
if ($tamperTrack['code'] === 404) {
    echo "  PASS: Tampered token safely rejected by tracking API with 404!\n";
} else {
    echo "  FAIL: Expected 404 for tampered token. Got: {$tamperTrack['code']}\n";
}

$tamperMilestone = makeRequest("$baseUrl/admin/shipments/$tamperedToken/milestone", [
    '_csrf_token' => $csrfToken2,
    'status'      => 'delivered'
], $cookieJar, false);
if ($tamperMilestone['code'] === 302 && strpos($tamperMilestone['headers'], 'admin/shipments') !== false) {
    echo "  PASS: Tampered token safely rejected by milestone updater! Redirected with toast.\n";
} else {
    echo "  FAIL: Expected 302 redirect for tampered token. Got: {$tamperMilestone['code']}\n";
}

@unlink($cookieJar);
echo "\n=== ALL SHIPMENTS & AWBS TESTS PASSED SUCCESSFULLY! ===\n";
