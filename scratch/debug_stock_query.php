<?php
require_once __DIR__ . '/../vendor/autoload.php';
$d = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$d->load();
foreach ($_ENV as $k => $v) putenv("$k=$v");
require_once __DIR__ . '/../app/Helpers/Helper.php';

$db = App\Config\Database::connect();

$tables = ['product_variants', 'products', 'product_images', 'stock_alerts', 'categories'];

foreach ($tables as $table) {
    echo "=== $table ===\n";
    $res = $db->query("DESCRIBE `$table`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "  ERROR: " . $db->error . "\n";
    }
    echo "\n";
}

// Now test the exact query that fails
echo "=== Testing data query prepare ===\n";
$sql = "
    SELECT
        pv.id           AS variant_id,
        pv.sku,
        pv.variant_name,
        pv.color_name,
        pv.color_code,
        pv.size,
        pv.price_override,
        pv.stock_qty,
        p.id            AS product_id,
        p.name          AS product_name,
        p.slug          AS product_slug,
        p.base_price,
        p.sale_price,
        p.low_stock_threshold,
        p.allow_backorder,
        p.status        AS product_status,
        c.name          AS category_name,
        sa.id           AS alert_id,
        sa.resolved     AS alert_resolved,
        sa.alerted_at,
        (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS cover_image
    FROM product_variants pv
    JOIN products p ON pv.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN stock_alerts sa ON sa.variant_id = pv.id
    WHERE pv.is_active = 1
    ORDER BY pv.stock_qty ASC, p.name ASC
    LIMIT 0, 15
";

$stmt = $db->prepare($sql);
if ($stmt === false) {
    echo "PREPARE FAILED: " . $db->error . "\n";
} else {
    echo "Prepare OK!\n";
    $stmt->execute();
    $res = $stmt->get_result();
    echo "Rows returned: " . $res->num_rows . "\n";
}
