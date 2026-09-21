<?php
/**
 * JIYAJI LUXURY: SUPPORT TICKETS & RESOLUTION E2E TEST SUITE
 * 
 * Verifies:
 * - RBAC / Admin authentication guard on support ticket routes
 * - Support tickets ledger & 5 KPI summary metrics
 * - Universal Encrypted URL tokens (zero raw database IDs exposed)
 * - Sidebar navigation active state and "Live" status badge
 * - Ticket creation with client profile & optional order linkage
 * - Complete conversation timeline & message bubbles
 * - Posting concierge staff replies with status transitions
 * - Real-time status, priority, and staff assignment updates
 * - Cryptographic tamper protection (tampered/invalid tokens rejected)
 * - Clean deletion of support tickets and associated conversation threads
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
require_once __DIR__ . '/../app/Models/Ticket.php';

use App\Config\Database;
use App\Models\Ticket;

$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'jiyaji_tkt_cookie_');

echo "=========================================================\n";
echo " JIYAJI LUXURY: SUPPORT TICKETS E2E TEST SUITE\n";
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

// Step 0: Ensure fresh fixtures
echo "Step 0: Re-seeding support ticket test fixtures...\n";
require __DIR__ . '/seed_tickets.php';
echo "\n";

// TEST 1: Unauthenticated Guard
echo "TEST 1: Authentication Guard on Support Tickets\n";
$unauthRes = request("{$baseUrl}/admin/tickets");
assertTest(
    str_contains($unauthRes['effectiveUrl'], '/admin/login') || $unauthRes['code'] === 302,
    "Unauthenticated visitor redirected to admin login"
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

// TEST 3: Access Support Tickets Ledger & KPIs
echo "\nTEST 3: Support Tickets Ledger & KPI Summary Cards\n";
$tktIndex = request("{$baseUrl}/admin/tickets", 'GET', [], $cookieJar);
assertTest($tktIndex['code'] === 200, "HTTP 200 returned for /admin/tickets");
assertTest(str_contains($tktIndex['body'], 'Support Tickets & Priority Resolution'), "Queue title rendered");
assertTest(str_contains($tktIndex['body'], 'Total Inquiries'), "Total Inquiries KPI card rendered");
assertTest(str_contains($tktIndex['body'], 'Open & Urgent'), "Open & Urgent KPI card rendered");
assertTest(str_contains($tktIndex['body'], 'In Progress'), "In Progress KPI card rendered");
assertTest(str_contains($tktIndex['body'], 'Critical Priority'), "Critical Priority KPI card rendered");
assertTest(str_contains($tktIndex['body'], 'Resolved & Closed'), "Resolved & Closed KPI card rendered");
assertTest(!str_contains($tktIndex['body'], 'Warning:'), "Zero PHP warnings in page output");
assertTest(!str_contains($tktIndex['body'], 'Notice:'), "Zero PHP notices in page output");

// TEST 4: Sidebar Navigation State
echo "\nTEST 4: Sidebar Navigation State\n";
assertTest(str_contains($tktIndex['body'], 'href="' . $baseUrl . '/admin/tickets"'), "Sidebar ticket link points to /admin/tickets");
assertTest(str_contains($tktIndex['body'], 'Support Tickets</span>'), "Sidebar shows 'Support Tickets'");
assertTest(str_contains($tktIndex['body'], 'id="navTickets"'), "Sidebar has navTickets ID");

// TEST 5: Universal Encrypted IDs on Ticket URLs
echo "\nTEST 5: Universal Encrypted IDs on Ticket Actions\n";
preg_match('/href="[^"]*\/admin\/tickets\/([a-zA-Z0-9_-]{20,})"/', $tktIndex['body'], $ticketEncMatch);
assertTest(!empty($ticketEncMatch[1]), "Encrypted ticket token found in table URL");

$extractedEncId = $ticketEncMatch[1];
$decryptedTicketId = decrypt_id($extractedEncId);
assertTest($decryptedTicketId !== null && $decryptedTicketId > 0, "Ticket token decrypts to integer ID ({$decryptedTicketId})");
assertTest(!str_contains($tktIndex['body'], 'admin/tickets/1"'), "Zero raw database IDs exposed in ticket URLs");

// TEST 6: Creating a New Support Ticket
echo "\nTEST 6: Create New Support Ticket\n";
$pageCsrf = extractCsrf($tktIndex['body']);
$db = Database::connect();
$firstCustomer = $db->query("SELECT id, name FROM customers LIMIT 1")->fetch_assoc();
$encCustId = encrypt_id($firstCustomer['id']);

$createRes = request("{$baseUrl}/admin/tickets/store", 'POST', [
    '_csrf_token' => $pageCsrf,
    'customer_id' => $encCustId,
    'order_id'    => '',
    'subject'     => 'Bespoke fitting appointment request for wedding season',
    'priority'    => 'high',
    'assigned_to' => '1',
    'message'     => 'Client requested an in-person measurement session at the flagship atelier.'
], $cookieJar);

assertTest($createRes['code'] === 200, "Ticket creation returned HTTP 200");
assertTest(str_contains($createRes['body'], 'Bespoke fitting appointment request'), "New ticket subject rendered in thread");
assertTest(str_contains($createRes['body'], 'Client requested an in-person measurement session'), "Initial inquiry message rendered");

// Get the created ticket's ID from database
$newTicketRow = $db->query("SELECT id FROM support_tickets WHERE subject = 'Bespoke fitting appointment request for wedding season' ORDER BY id DESC LIMIT 1")->fetch_assoc();
assertTest(!empty($newTicketRow), "Created ticket confirmed in database");
$newTicketId = (int)$newTicketRow['id'];
$newTicketEncId = encrypt_id($newTicketId);

// TEST 7: Viewing Conversation Thread & Context Dossiers
echo "\nTEST 7: View Conversation Thread & Customer Dossier\n";
$showPage = request("{$baseUrl}/admin/tickets/{$newTicketEncId}", 'GET', [], $cookieJar);
assertTest($showPage['code'] === 200, "Thread page returned HTTP 200");
assertTest(str_contains($showPage['body'], 'Conversation Timeline'), "Conversation timeline rendered");
assertTest(str_contains($showPage['body'], 'Client Profile'), "Client Profile dossier sidepanel rendered");
assertTest(str_contains($showPage['body'], htmlspecialchars($firstCustomer['name'])), "Customer name present in dossier");
assertTest(str_contains($showPage['body'], 'Post Concierge Response'), "Reply composer card rendered");

// TEST 8: Posting Concierge Staff Reply
echo "\nTEST 8: Post Staff Reply to Ticket Conversation\n";
$showCsrf = extractCsrf($showPage['body']);
$replyText = "We have reserved an executive fitting suite for Saturday at 3:00 PM with our head master tailor.";

$replyRes = request("{$baseUrl}/admin/tickets/{$newTicketEncId}/reply", 'POST', [
    '_csrf_token'   => $showCsrf,
    'message'       => $replyText,
    'status_action' => 'in_progress'
], $cookieJar);

assertTest($replyRes['code'] === 200, "Reply submission returned HTTP 200");
assertTest(str_contains($replyRes['body'], htmlspecialchars($replyText)), "Reply message successfully added to thread");

// Verify status was updated to in_progress
$ticketAfterReply = $db->query("SELECT status FROM support_tickets WHERE id = {$newTicketId}")->fetch_assoc();
assertTest($ticketAfterReply['status'] === 'in_progress', "Ticket status automatically transitioned to 'in_progress'");

// TEST 9: Status Transition to Resolved & Closed
echo "\nTEST 9: Ticket Status Lifecycle Transitions\n";
$showPageAfterReply = request("{$baseUrl}/admin/tickets/{$newTicketEncId}", 'GET', [], $cookieJar);
$csrfStatus = extractCsrf($showPageAfterReply['body']);

// Transition to resolved
$resolveRes = request("{$baseUrl}/admin/tickets/{$newTicketEncId}/status", 'POST', [
    '_csrf_token' => $csrfStatus,
    'status'      => 'resolved'
], $cookieJar);

assertTest($resolveRes['code'] === 200, "Status update returned HTTP 200");
$ticketResolved = $db->query("SELECT status FROM support_tickets WHERE id = {$newTicketId}")->fetch_assoc();
assertTest($ticketResolved['status'] === 'resolved', "Ticket status transitioned to 'resolved'");

// TEST 10: Priority & Agent Assignment
echo "\nTEST 10: Priority & Staff Assignment Updates\n";
// Update priority to critical
$priRes = request("{$baseUrl}/admin/tickets/{$newTicketEncId}/priority", 'POST', [
    '_csrf_token' => $csrfStatus,
    'priority'    => 'critical'
], $cookieJar);

assertTest($priRes['code'] === 200, "Priority update returned HTTP 200");
$ticketCrit = $db->query("SELECT priority FROM support_tickets WHERE id = {$newTicketId}")->fetch_assoc();
assertTest($ticketCrit['priority'] === 'critical', "Ticket priority updated to 'critical'");

// Assign agent
$assignRes = request("{$baseUrl}/admin/tickets/{$newTicketEncId}/assign", 'POST', [
    '_csrf_token' => $csrfStatus,
    'assigned_to' => '1'
], $cookieJar);

assertTest($assignRes['code'] === 200, "Assignment update returned HTTP 200");
$ticketAssigned = $db->query("SELECT assigned_to FROM support_tickets WHERE id = {$newTicketId}")->fetch_assoc();
assertTest((int)$ticketAssigned['assigned_to'] === 1, "Staff agent successfully assigned to ticket");

// TEST 11: Cryptographic Tamper Protection
echo "\nTEST 11: Cryptographic Tamper Protection\n";
$tamperedToken = $newTicketEncId . 'corrupted_token';
$tamperRes = request("{$baseUrl}/admin/tickets/{$tamperedToken}", 'GET', [], $cookieJar);
assertTest(
    str_contains($tamperRes['body'], 'Access Denied') || str_contains($tamperRes['body'], 'Invalid ticket identifier'),
    "Tampered token safely rejected with Access Denied / Invalid ticket identifier"
);

// TEST 12: Ticket Deletion & Thread Cleanup
echo "\nTEST 12: Ticket Deletion with Cascade Cleanup\n";
$delRes = request("{$baseUrl}/admin/tickets/{$newTicketEncId}/delete", 'POST', [
    '_csrf_token' => $csrfStatus
], $cookieJar);

assertTest($delRes['code'] === 200, "Delete request returned HTTP 200");
$ticketExists = $db->query("SELECT COUNT(*) FROM support_tickets WHERE id = {$newTicketId}")->fetch_row()[0];
$msgExists = $db->query("SELECT COUNT(*) FROM support_ticket_messages WHERE ticket_id = {$newTicketId}")->fetch_row()[0];
assertTest((int)$ticketExists === 0, "Ticket deleted from database");
assertTest((int)$msgExists === 0, "Associated messages cleanly cascaded and removed");

// TEST 13: Re-seed fixtures & verify clean exit
echo "\nTEST 13: Resetting database to standard fixtures\n";
require __DIR__ . '/seed_tickets.php';

echo "\n=========================================================\n";
echo " ALL 13 SUPPORT TICKETS E2E TESTS PASSED (100% SUCCESS)\n";
echo "=========================================================\n";

@unlink($cookieJar);
