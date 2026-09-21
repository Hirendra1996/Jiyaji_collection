<?php
$conn = new mysqli('localhost', 'root', '', 'jiyaji_collection');
$r = $conn->query("DESCRIBE orders");
while ($c = $r->fetch_assoc()) echo $c['Field'] . "\n";

// Also test the usage history query manually
echo "\n--- Testing usage history query ---\n";
$r2 = $conn->query("
    SELECT
        cu.id, cu.used_at,
        cust.name AS customer_name, cust.email AS customer_email,
        o.order_number, o.total_amount, o.discount_amount
    FROM coupon_usages cu
    JOIN customers cust ON cu.customer_id = cust.id
    LEFT JOIN orders o ON cu.order_id = o.id
    WHERE cu.coupon_id = 1
    LIMIT 5
");
if ($r2 === false) {
    echo "Query error: " . $conn->error . "\n";
} else {
    echo "Query OK. Rows: " . $r2->num_rows . "\n";
}
