<?php
/**
 * End-to-End HTTP Integration Test for Static CMS Pages & Contact System
 */

$cookieFile = __DIR__ . '/test_cms_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function httpReq(string $url, string $method = 'GET', array $postData = [], string $cookieFile = ''): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) JiyajiE2E');

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $info['http_code'] ?? 0,
        'url'  => $info['url'] ?? '',
        'body' => $body ?: '',
        'err'  => $err
    ];
}

echo "=== Jiyaji LX — Static CMS Pages HTTP End-to-End Test ===\n\n";

// 1. Test Public About Us page
$aboutResp = httpReq('http://localhost/Jiyaji_collection/about-us');
echo "[1] GET /about-us -> HTTP " . $aboutResp['code'] . " (" . strlen($aboutResp['body']) . " bytes)\n";
$hasHeritage = strpos($aboutResp['body'], 'The Heritage of Jiyaji LX') !== false;
$hasSidebar = strpos($aboutResp['body'], 'Brand &amp; Information') !== false;
echo "    Heritage Content: " . ($hasHeritage ? 'YES' : 'NO') . "\n";
echo "    Sidebar Links: " . ($hasSidebar ? 'YES' : 'NO') . "\n";

// 2. Test Public FAQ page
$faqResp = httpReq('http://localhost/Jiyaji_collection/faq');
echo "[2] GET /faq -> HTTP " . $faqResp['code'] . " (" . strlen($faqResp['body']) . " bytes)\n";
$hasFaqTitle = strpos($faqResp['body'], 'Frequently Asked Questions') !== false;
echo "    FAQ Content: " . ($hasFaqTitle ? 'YES' : 'NO') . "\n";

// 3. Test Public Contact Us page
$contactResp = httpReq('http://localhost/Jiyaji_collection/contact-us');
echo "[3] GET /contact-us -> HTTP " . $contactResp['code'] . " (" . strlen($contactResp['body']) . " bytes)\n";
$hasForm = strpos($contactResp['body'], 'contact-us/submit') !== false;
echo "    Contact Form Present: " . ($hasForm ? 'YES' : 'NO') . "\n";

// 4. Submit public message to Contact Us
$testName = 'Rao Raja Jodhpur ' . time();
$submitResp = httpReq('http://localhost/Jiyaji_collection/contact-us/submit', 'POST', [
    'name'    => $testName,
    'email'   => 'raoraja@jodhpur.in',
    'phone'   => '+91 99880 11223',
    'subject' => 'Royal Achkan & Safa Inquiries',
    'message' => 'Please provide fabric availability for the handloom brocade achkan ensemble.'
]);
echo "[4] POST /contact-us/submit -> HTTP " . $submitResp['code'] . " (Final URL: " . $submitResp['url'] . ")\n";

// 5. Admin Login & Check Inquiries
$loginUrl = 'http://localhost/Jiyaji_collection/admin/login';
$loginPage = httpReq($loginUrl, 'GET', [], $cookieFile);
preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $matches);
$csrf = $matches[1] ?? '';

$authResp = httpReq($loginUrl, 'POST', [
    'email' => 'admin@jiyaji.com',
    'password' => 'Admin@123',
    '_csrf_token' => $csrf
], $cookieFile);
echo "[5] POST /admin/login -> HTTP " . $authResp['code'] . " (Redirected to: " . $authResp['url'] . ")\n";

// 6. Check Admin Pages Directory
$adminPages = httpReq('http://localhost/Jiyaji_collection/admin/pages?tab=pages', 'GET', [], $cookieFile);
echo "[6] GET /admin/pages?tab=pages -> HTTP " . $adminPages['code'] . " (" . strlen($adminPages['body']) . " bytes)\n";
$hasPagesTitle = strpos($adminPages['body'], 'Static CMS Pages') !== false;
$hasTable = strpos($adminPages['body'], 'about-us') !== false;
echo "    Admin CMS Title: " . ($hasPagesTitle ? 'YES' : 'NO') . "\n";
echo "    Pages Table: " . ($hasTable ? 'YES' : 'NO') . "\n";

// 7. Check Admin Editor
$adminEditor = httpReq('http://localhost/Jiyaji_collection/admin/pages?tab=editor', 'GET', [], $cookieFile);
echo "[7] GET /admin/pages?tab=editor -> HTTP " . $adminEditor['code'] . " (" . strlen($adminEditor['body']) . " bytes)\n";
$hasEditor = strpos($adminEditor['body'], 'pageContentArea') !== false;
echo "    Content Editor: " . ($hasEditor ? 'YES' : 'NO') . "\n";

// 8. Check Admin Inquiries Tab (Verifying our submitted message is in the inbox!)
$adminEnq = httpReq('http://localhost/Jiyaji_collection/admin/pages?tab=enquiries', 'GET', [], $cookieFile);
echo "[8] GET /admin/pages?tab=enquiries -> HTTP " . $adminEnq['code'] . " (" . strlen($adminEnq['body']) . " bytes)\n";
$hasSubmittedEnq = strpos($adminEnq['body'], $testName) !== false;
echo "    Submitted Inquiry in Inbox: " . ($hasSubmittedEnq ? 'YES' : 'NO') . "\n";

if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n=== End-to-End HTTP Test Complete ===\n";
