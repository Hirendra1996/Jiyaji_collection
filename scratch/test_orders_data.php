<?php
putenv('DB_HOST=localhost');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=jiyaji_collection');

require_once __DIR__ . '/../app/Config/Database.php';
$db = App\Config\Database::connect();

$res = $db->query("SELECT id, order_number, grand_total, status, payment_status, payment_method, placed_at FROM orders");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
