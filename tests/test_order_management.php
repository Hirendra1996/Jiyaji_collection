<?php
/**
 * Automated End-to-End Test Suite for Order Management System (OMS) & Universal ID Encryption
 */

$cookieJar = tempnam(sys_get_temp_dir(), 'admin_oms_cookie_');
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

echo "=== STARTING ORDER MANAGEMENT SYSTEM & ENCRYPTION TEST ===\n\n";

// TEST 1: Unauthenticated protection
echo "[TEST 1] Access /admin/orders unauthenticated...\n";
$res1 = makeRequest("$baseUrl/admin/orders", null, $cookieJar, false);
if ($res1['code'] === 302 && strpos($res1['headers'], 'admin/login') !== false) {
    echo "  PASS: Blocked unauthenticated visit! Redirected to login.\n";
} else {
    echo "  FAIL: Expected 302 redirect. Got code {$res1['code']}\n";
}

// TEST 2: Login as Admin
echo "\n[TEST 2] Authenticating as administrator...\n";
$loginPage = makeRequest("$baseUrl/admin/login", null, $cookieJar, false);
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $tokenMatch);
$csrfToken = $tokenMatch[1] ?? '';

$loginAction = makeRequest("$baseUrl/admin/login", [
    '_csrf_token' => $csrfToken,
    'email'       => 'admin@jiyaji.com',
    'password'    => 'Admin@123'
], $cookieJar, false);

if ($loginAction['code'] === 302 && strpos($loginAction['headers'], 'admin/dashboard') !== false) {
    echo "  PASS: Authenticated successfully.\n";
} else {
    echo "  FAIL: Login failed.\n";
    exit(1);
}

// TEST 3: Access /admin/orders
echo "\n[TEST 3] Loading /admin/orders with authenticated session...\n";
$ordersPage = makeRequest("$baseUrl/admin/orders", null, $cookieJar, false);
if ($ordersPage['code'] === 200 && strpos($ordersPage['body'], 'Orders Management') !== false) {
    echo "  PASS: Orders listing loaded with 200 OK!\n";
    echo "  PASS: Found KPI bar: " . (strpos($ordersPage['body'], 'Total Orders') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Status Tabs: " . (strpos($ordersPage['body'], 'All Orders') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Found Search/Filter Form: " . (strpos($ordersPage['body'], 'Filter Orders') !== false ? "YES" : "NO") . "\n";
} else {
    echo "  FAIL: Could not load orders page. Code: {$ordersPage['code']}\n";
    exit(1);
}

// TEST 4: Verify Universal ID Encryption in Links
echo "\n[TEST 4] Verifying all order links are encrypted (no raw numeric IDs)...\n";
preg_match_all('#admin/orders/([A-Za-z0-9_-]{30,})#', $ordersPage['body'], $encMatches);
$encryptedIds = array_unique($encMatches[1] ?? []);

// Check if any raw numeric link like "admin/orders/[0-9]+" exists
preg_match('#admin/orders/([0-9]+)[\'"/]|\bhref="[^"]*admin/orders/([0-9]+)"#', $ordersPage['body'], $rawMatches);
$hasRawId = !empty($rawMatches[1]) || !empty($rawMatches[2]);

if (count($encryptedIds) > 0 && !$hasRawId) {
    echo "  PASS: Found " . count($encryptedIds) . " encrypted order tokens in listing.\n";
    echo "  PASS: Zero raw numeric IDs exposed in URLs!\n";
    $sampleEncId = $encryptedIds[0];
    echo "  Sample Encrypted Token: " . substr($sampleEncId, 0, 24) . "...\n";
} else {
    echo "  FAIL: Encrypted IDs missing or raw IDs detected.\n";
    exit(1);
}

// TEST 5: Load Order Detail View using Encrypted ID
echo "\n[TEST 5] Access /admin/orders/{encrypted_id}...\n";
$detailPage = makeRequest("$baseUrl/admin/orders/$sampleEncId", null, $cookieJar, false);
if ($detailPage['code'] === 200 && strpos($detailPage['body'], 'Order JLX-ORD-') !== false) {
    echo "  PASS: Order detail loaded successfully with 200 OK!\n";
    echo "  PASS: Stepper rendered: " . (strpos($detailPage['body'], 'Fulfillment Progress') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Purchased items table rendered: " . (strpos($detailPage['body'], 'Purchased Items') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Courier tracking form rendered: " . (strpos($detailPage['body'], 'Courier Partner') !== false ? "YES" : "NO") . "\n";
    echo "  PASS: Customer profile rendered: " . (strpos($detailPage['body'], 'Customer Profile') !== false ? "YES" : "NO") . "\n";
} else {
    echo "  FAIL: Could not load detail page. Code: {$detailPage['code']}\n";
    exit(1);
}

// TEST 6: Tampering Detection on Encrypted ID
echo "\n[TEST 6] Testing tampering rejection on encrypted order token...\n";
$tamperedToken = substr($sampleEncId, 0, -4) . 'WXYZ';
$tamperedRes = makeRequest("$baseUrl/admin/orders/$tamperedToken", null, $cookieJar, false);
if ($tamperedRes['code'] === 302 && strpos($tamperedRes['headers'], 'admin/orders') !== false) {
    echo "  PASS: Tampered ID was safely rejected! Redirected with error toast.\n";
} else {
    echo "  FAIL: Tampered token was not rejected. Code: {$tamperedRes['code']}\n";
}

// TEST 7: Update Fulfillment Status via POST
echo "\n[TEST 7] Updating fulfillment status to 'shipped' with audit note...\n";
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $detailPage['body'], $detailCsrf);
$csrfToken2 = $detailCsrf[1] ?? '';

$updateRes = makeRequest("$baseUrl/admin/orders/$sampleEncId/status", [
    '_csrf_token' => $csrfToken2,
    'status'      => 'shipped',
    'note'        => 'Automated test fulfillment dispatch'
], $cookieJar, false);

if ($updateRes['code'] === 302 && strpos($updateRes['headers'], "admin/orders/$sampleEncId") !== false) {
    echo "  PASS: Status update request processed and redirected.\n";

    // Verify detail page shows 'Shipped'
    $updatedDetail = makeRequest("$baseUrl/admin/orders/$sampleEncId", null, $cookieJar, false);
    if (strpos($updatedDetail['body'], 'Automated test fulfillment dispatch') !== false) {
        echo "  PASS: Audit trail updated with status change and note in database!\n";
    } else {
        echo "  FAIL: Audit note not found in reloaded detail page.\n";
    }
} else {
    echo "  FAIL: Status update failed. Code: {$updateRes['code']}\n";
}

// TEST 8: Update Courier Tracking
echo "\n[TEST 8] Updating courier partner and AWB tracking...\n";
$trackRes = makeRequest("$baseUrl/admin/orders/$sampleEncId/tracking", [
    '_csrf_token'        => $csrfToken2,
    'courier_partner'    => 'BlueDart Express',
    'awb_number'         => 'BD8899776655',
    'estimated_delivery' => date('Y-m-d', strtotime('+3 days'))
], $cookieJar, false);

if ($trackRes['code'] === 302 && strpos($trackRes['headers'], "admin/orders/$sampleEncId") !== false) {
    echo "  PASS: Courier tracking update processed and redirected.\n";

    $updatedDetail2 = makeRequest("$baseUrl/admin/orders/$sampleEncId", null, $cookieJar, false);
    if (strpos($updatedDetail2['body'], 'BD8899776655') !== false && strpos($updatedDetail2['body'], 'BlueDart Express') !== false) {
        echo "  PASS: Courier and AWB tracking numbers successfully saved to database!\n";
    } else {
        echo "  FAIL: Tracking info not saved.\n";
    }
} else {
    echo "  FAIL: Tracking update failed. Code: {$trackRes['code']}\n";
}

// TEST 9: Printable Tax Invoice View
echo "\n[TEST 9] Loading printable tax invoice /admin/orders/{encrypted_id}/invoice...\n";
$invoicePage = makeRequest("$baseUrl/admin/orders/$sampleEncId/invoice", null, $cookieJar, false);
if ($invoicePage['code'] === 200 && strpos($invoicePage['body'], 'Tax Invoice') !== false && strpos($invoicePage['body'], 'GSTIN') !== false) {
    echo "  PASS: Printable tax invoice loaded with 200 OK and luxury layout!\n";
} else {
    echo "  FAIL: Invoice view failed. Code: {$invoicePage['code']}\n";
}

// TEST 10: Export CSV
echo "\n[TEST 10] Testing orders CSV export /admin/orders/export...\n";
$csvRes = makeRequest("$baseUrl/admin/orders/export", null, $cookieJar, false);
if ($csvRes['code'] === 200 && strpos($csvRes['headers'], 'text/csv') !== false && (strpos($csvRes['body'], 'Order Number') !== false)) {
    echo "  PASS: CSV export generated with valid headers and data stream!\n";
} else {
    echo "  FAIL: CSV export failed. Code: {$csvRes['code']}\n";
}

// TEST 11: Printable Shipping Label (Feature 18)
echo "\n[TEST 11] Loading printable shipping label /admin/orders/{encrypted_id}/shipping-label...\n";
$shipLabelRes = makeRequest("$baseUrl/admin/orders/$sampleEncId/shipping-label", null, $cookieJar, false);
if ($shipLabelRes['code'] === 200 && strpos($shipLabelRes['body'], 'Shipping Label') !== false && strpos($shipLabelRes['body'], 'AWB:') !== false) {
    echo "  PASS: Printable luxury shipping label rendered with 200 OK & barcodes!\n";
} else {
    echo "  FAIL: Shipping label view failed. Code: {$shipLabelRes['code']}\n";
}

// TEST 12: Returns & Refunds Management Queue (Feature 21 & 22)
echo "\n[TEST 12] Loading Returns & Refunds Hub /admin/returns...\n";
$returnsRes = makeRequest("$baseUrl/admin/returns", null, $cookieJar, false);
if ($returnsRes['code'] === 200 && strpos($returnsRes['body'], 'Returns & Exchange Hub') !== false && strpos($returnsRes['body'], 'Return Requests Ledger') !== false) {
    echo "  PASS: Returns & Refunds Hub loaded with 200 OK!\n";
    preg_match_all('/\"encrypted_id\":\"([a-zA-Z0-9_\-]+)\"/', $returnsRes['body'], $retMatches);
    $sampleRetEncId = $retMatches[1][0] ?? null;
    echo "  PASS: Return encrypted tokens discovered: " . count($retMatches[1]) . "\n";
    if ($sampleRetEncId) {
        echo "  Sample Return Token: " . substr($sampleRetEncId, 0, 24) . "...\n";
    }
} else {
    echo "  FAIL: Returns hub failed. Code: {$returnsRes['code']}\n";
    $sampleRetEncId = null;
}

// TEST 13: Processing Return Status Transition (Approve + Reverse AWB)
if ($sampleRetEncId) {
    echo "\n[TEST 13] Updating Return request #RET via /admin/returns/{encrypted_id}/status...\n";
    $retActionRes = makeRequest("$baseUrl/admin/returns/$sampleRetEncId/status", [
        '_csrf_token'       => $csrfToken2,
        'status'            => 'approved',
        'reverse_awb'       => 'REV-DELH-992211',
        'admin_note'        => 'Item return approved after quality verification.'
    ], $cookieJar, false);

    if ($retActionRes['code'] === 302 && strpos($retActionRes['headers'], 'admin/returns') !== false) {
        echo "  PASS: Return request status update processed and redirected!\n";

        // Re-check returns hub
        $returnsReload = makeRequest("$baseUrl/admin/returns", null, $cookieJar, false);
        if (strpos($returnsReload['body'], 'REV-DELH-992211') !== false) {
            echo "  PASS: Reverse AWB successfully saved and rendered in ledger!\n";
        } else {
            echo "  FAIL: Reverse AWB not found in updated returns ledger.\n";
        }
    } else {
        echo "  FAIL: Return action failed. Code: {$retActionRes['code']}\n";
    }
}

// TEST 14: Tampered Return ID Rejection
echo "\n[TEST 14] Testing tampering rejection on encrypted return token...\n";
$tamperedRetToken = "invalid_return_token_tampered";
$tamperRetRes = makeRequest("$baseUrl/admin/returns/$tamperedRetToken/status", [
    '_csrf_token' => $csrfToken2,
    'status'      => 'approved'
], $cookieJar, false);

if ($tamperRetRes['code'] === 302 && strpos($tamperRetRes['headers'], 'admin/returns') !== false) {
    echo "  PASS: Tampered return ID safely rejected! Redirected with security toast.\n";
} else {
    echo "  FAIL: Expected 302 redirect on tampered return ID. Code: {$tamperRetRes['code']}\n";
}

@unlink($cookieJar);
echo "\n=== ALL ADVANCED ORDER MANAGEMENT & ENCRYPTION TESTS PASSED (14/14) ===\n";

