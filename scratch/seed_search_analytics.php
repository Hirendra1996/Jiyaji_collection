<?php
/**
 * Search Analytics Realistic Data Seeder
 * Populates realistic search queries, zero-result terms, and activity logs.
 */

putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';

$db = App\Config\Database::connect();
echo "Seeding search analytics data...\n";

// Fetch customer IDs if any
$customerIds = [];
$cRes = $db->query("SELECT id FROM customers LIMIT 20");
if ($cRes) {
    while ($r = $cRes->fetch_assoc()) {
        $customerIds[] = (int)$r['id'];
    }
}

// Clear any existing test logs to avoid duplication if re-run
$db->query("TRUNCATE TABLE search_logs");

// Catalog-matching high demand search keywords with typical result counts
$catalogKeywords = [
    ['keyword' => 'Sherwani',               'min_res' => 5,  'max_res' => 14, 'weight' => 18],
    ['keyword' => 'Silk Kurta',             'min_res' => 6,  'max_res' => 16, 'weight' => 15],
    ['keyword' => 'Bandhgala',              'min_res' => 3,  'max_res' => 8,  'weight' => 14],
    ['keyword' => 'Tuxedo',                 'min_res' => 4,  'max_res' => 10, 'weight' => 12],
    ['keyword' => 'Velvet Blazer',          'min_res' => 2,  'max_res' => 7,  'weight' => 10],
    ['keyword' => 'Nehru Jacket',           'min_res' => 4,  'max_res' => 11, 'weight' => 11],
    ['keyword' => 'Embroidered Kurta',      'min_res' => 3,  'max_res' => 9,  'weight' => 9],
    ['keyword' => 'Wedding Collection',     'min_res' => 8,  'max_res' => 22, 'weight' => 10],
    ['keyword' => 'Linen Shirt',            'min_res' => 4,  'max_res' => 12, 'weight' => 8],
    ['keyword' => 'Jodhpuri Suit',          'min_res' => 2,  'max_res' => 6,  'weight' => 7],
    ['keyword' => 'Black Tuxedo',           'min_res' => 3,  'max_res' => 8,  'weight' => 6],
    ['keyword' => 'Royal Blue Sherwani',    'min_res' => 1,  'max_res' => 4,  'weight' => 5],
];

// Zero-result keywords (high-value unmet customer intent)
$zeroKeywords = [
    ['keyword' => 'Pashmina Shawl',         'weight' => 9],
    ['keyword' => 'Raw Silk Pagdi',         'weight' => 7],
    ['keyword' => 'Mojari Shoes',           'weight' => 8],
    ['keyword' => 'Gold Cufflinks',         'weight' => 6],
    ['keyword' => 'Dhoti Kurta Set',        'weight' => 5],
    ['keyword' => 'Kashmiri Stole',         'weight' => 4],
    ['keyword' => 'Brocade Achkan',         'weight' => 4],
    ['keyword' => 'Ivory Safa',             'weight' => 3],
    ['keyword' => 'Floral Bundi',           'weight' => 3],
    ['keyword' => 'Embroidered Jutti',      'weight' => 3],
];

$devices = ['desktop', 'desktop', 'desktop', 'mobile', 'mobile', 'tablet'];
$ips = [
    '103.21.124.55', '182.74.19.12', '157.34.88.201', '49.36.112.90',
    '122.161.45.18', '223.187.9.76', '106.51.24.110', '115.112.80.34'
];

$stmt = $db->prepare("
    INSERT INTO search_logs (customer_id, keyword, results_count, ip_address, device_type, searched_at)
    VALUES (?, ?, ?, ?, ?, ?)
");

$seededCount = 0;

// Seed successful searches across 30 days
foreach ($catalogKeywords as $item) {
    $count = $item['weight'];
    for ($i = 0; $i < $count; $i++) {
        // Random days ago between 0 and 29
        $daysAgo = rand(0, 28);
        // More searches in the last 7 days
        if (rand(1, 100) <= 45) {
            $daysAgo = rand(0, 6);
        }
        $hoursAgo = rand(0, 23);
        $minsAgo = rand(0, 59);
        $dateStr = date('Y-m-d H:i:s', strtotime("-$daysAgo days -$hoursAgo hours -$minsAgo minutes"));

        $custId = null;
        if (!empty($customerIds) && rand(1, 100) <= 40) {
            $custId = $customerIds[array_rand($customerIds)];
        }

        $resCount = rand($item['min_res'], $item['max_res']);
        $dev = $devices[array_rand($devices)];
        $ip = $ips[array_rand($ips)];

        $stmt->bind_param("isisss", $custId, $item['keyword'], $resCount, $ip, $dev, $dateStr);
        $stmt->execute();
        $seededCount++;
    }
}

// Seed zero-result searches across 30 days
foreach ($zeroKeywords as $item) {
    $count = $item['weight'];
    for ($i = 0; $i < $count; $i++) {
        $daysAgo = rand(0, 28);
        if (rand(1, 100) <= 50) {
            $daysAgo = rand(0, 7);
        }
        $hoursAgo = rand(0, 23);
        $minsAgo = rand(0, 59);
        $dateStr = date('Y-m-d H:i:s', strtotime("-$daysAgo days -$hoursAgo hours -$minsAgo minutes"));

        $custId = null;
        if (!empty($customerIds) && rand(1, 100) <= 35) {
            $custId = $customerIds[array_rand($customerIds)];
        }

        $resCount = 0;
        $dev = $devices[array_rand($devices)];
        $ip = $ips[array_rand($ips)];

        $stmt->bind_param("isisss", $custId, $item['keyword'], $resCount, $ip, $dev, $dateStr);
        $stmt->execute();
        $seededCount++;
    }
}

// Seed a few queries explicitly for today
$todayKeywords = [
    ['Sherwani', 8], ['Silk Kurta', 12], ['Pashmina Shawl', 0], ['Tuxedo', 6], ['Mojari Shoes', 0]
];
foreach ($todayKeywords as $tk) {
    $dateStr = date('Y-m-d H:i:s', strtotime('-' . rand(10, 360) . ' minutes'));
    $custId = !empty($customerIds) ? $customerIds[0] : null;
    $dev = 'desktop';
    $ip = '103.21.124.55';
    $resCount = $tk[1];
    $kw = $tk[0];
    $stmt->bind_param("isisss", $custId, $kw, $resCount, $ip, $dev, $dateStr);
    $stmt->execute();
    $seededCount++;
}

$stmt->close();
echo "Successfully seeded {$seededCount} search log entries!\n";

$verify = $db->query("
    SELECT
        COUNT(*) as total,
        COUNT(DISTINCT keyword) as unique_terms,
        SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as zero_results
    FROM search_logs
")->fetch_assoc();

echo "Database verification:\n";
echo "  Total searches: {$verify['total']}\n";
echo "  Unique keywords: {$verify['unique_terms']}\n";
echo "  Zero-result searches: {$verify['zero_results']}\n";
