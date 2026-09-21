<?php
$baseUrl = 'http://localhost/Jiyaji_collection';
$cookieJar = tempnam(sys_get_temp_dir(), 'test_ck_');

// 1. Login
$ch = curl_init("$baseUrl/admin/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$loginHtml = curl_exec($ch);
preg_match('/name="_csrf_token"\s+value="([a-f0-9]+)"/', $loginHtml, $m);
$csrf = $m[1] ?? '';

curl_setopt($ch, CURLOPT_URL, "$baseUrl/admin/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'admin@jiyaji.com',
    'password' => 'Admin@123',
    '_csrf_token' => $csrf
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);

// 2. Fetch /admin/products
curl_setopt($ch, CURLOPT_URL, "$baseUrl/admin/products");
curl_setopt($ch, CURLOPT_POST, false);
$prodHtml = curl_exec($ch);
curl_close($ch);

echo "Total bytes received: " . strlen($prodHtml) . "\n";
echo "Has stockAdjustModal: " . (strpos($prodHtml, 'stockAdjustModal') !== false ? 'YES' : 'NO') . "\n";
echo "Last 300 chars of response:\n";
echo substr($prodHtml, -300) . "\n";
