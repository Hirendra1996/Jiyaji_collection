<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Page.php';

use App\Models\Page;

echo "--- Testing Page Model Defaults & KPIs ---\n";
Page::ensureDefaults();
echo "[OK] Page::ensureDefaults() executed\n";

$kpis = Page::getPageKPIs();
echo "KPIs: " . json_encode($kpis, JSON_PRETTY_PRINT) . "\n";

$pagesData = Page::getPages();
echo "Total Pages fetched: " . count($pagesData['pages']) . "\n";
foreach ($pagesData['pages'] as $p) {
    echo " - [{$p['id']}] {$p['title']} ({$p['slug']}): is_system=" . ($p['is_system'] ? 'YES' : 'NO') . ", active=" . ($p['is_active'] ? 'YES' : 'NO') . ", words={$p['word_count']}\n";
}
