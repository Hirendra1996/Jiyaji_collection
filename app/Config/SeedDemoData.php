<?php

namespace App\Config;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Database.php';

use Exception;

class SeedDemoData {
    public static function run(): void {
        try {
            $db = Database::connect();
            echo "Starting demo data seeding for Jiyaji Collection...\n";

            // 1. Seed Categories
            $categories = [
                ['name' => 'Royal Menswear', 'slug' => 'royal-menswear', 'description' => 'Bespoke suits, tuxedos, and bandhgalas.'],
                ['name' => 'Heritage Silk & Sherwanis', 'slug' => 'heritage-silk-sherwanis', 'description' => 'Handcrafted wedding and bridal groom attire.'],
                ['name' => 'Luxury Footwear', 'slug' => 'luxury-footwear', 'description' => 'Italian leather loafers and handcrafted mojaris.'],
                ['name' => 'Designer Accessories', 'slug' => 'designer-accessories', 'description' => 'Kashmiri stoles, cufflinks, and embossed belts.'],
                ['name' => 'Handcrafted Kurtas', 'slug' => 'handcrafted-kurtas', 'description' => 'Pure cotton and chanderi silk kurtas.']
            ];

            $catMap = [];
            foreach ($categories as $cat) {
                $check = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                $check->bind_param("s", $cat['slug']);
                $check->execute();
                $res = $check->get_result();
                if ($res->num_rows > 0) {
                    $catMap[$cat['slug']] = $res->fetch_assoc()['id'];
                } else {
                    $stmt = $db->prepare("INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->bind_param("sss", $cat['name'], $cat['slug'], $cat['description']);
                    $stmt->execute();
                    $catMap[$cat['slug']] = $stmt->insert_id;
                    echo "  - Added category: {$cat['name']}\n";
                }
            }

            // 2. Seed Customers
            $customers = [
                ['name' => 'Aditya Kapoor', 'email' => 'aditya.k@gmail.com', 'phone' => '9820112345'],
                ['name' => 'Meera Vardhan', 'email' => 'meera.v@outlook.com', 'phone' => '9811223344'],
                ['name' => 'Rohan Deshmukh', 'email' => 'rohan.deshmukh@yahoo.com', 'phone' => '9890334455'],
                ['name' => 'Siddharth Rao', 'email' => 'siddharth.rao@gmail.com', 'phone' => '9845001122'],
                ['name' => 'Ananya Birla', 'email' => 'ananya.b@gmail.com', 'phone' => '9829003344'],
                ['name' => 'Vikram Singhania', 'email' => 'vikram.s@singhania.co', 'phone' => '9830005566'],
                ['name' => 'Priya Sharma', 'email' => 'priya.sharma99@gmail.com', 'phone' => '9876007788'],
                ['name' => 'Kabir Mehta', 'email' => 'kabir.mehta@gmail.com', 'phone' => '9825009900']
            ];

            $custMap = [];
            $defaultPassword = password_hash('Customer@123', PASSWORD_BCRYPT);
            foreach ($customers as $c) {
                $check = $db->prepare("SELECT id FROM customers WHERE email = ?");
                $check->bind_param("s", $c['email']);
                $check->execute();
                $res = $check->get_result();
                if ($res->num_rows > 0) {
                    $custMap[$c['email']] = $res->fetch_assoc()['id'];
                } else {
                    $stmt = $db->prepare("INSERT INTO customers (name, email, phone, password_hash, email_verified, is_active) VALUES (?, ?, ?, ?, 1, 1)");
                    $stmt->bind_param("ssss", $c['name'], $c['email'], $c['phone'], $defaultPassword);
                    $stmt->execute();
                    $custMap[$c['email']] = $stmt->insert_id;
                    echo "  - Added customer: {$c['name']}\n";
                }
            }

            // 3. Seed Products & Variants
            $products = [
                [
                    'name' => 'Imperial Raw Silk Sherwani',
                    'slug' => 'imperial-raw-silk-sherwani',
                    'category' => 'heritage-silk-sherwanis',
                    'price' => 18999.00,
                    'sku' => 'JLX-SHW-01',
                    'stock' => 14
                ],
                [
                    'name' => 'Regal Velvet Bandhgala Tuxedo',
                    'slug' => 'regal-velvet-bandhgala-tuxedo',
                    'category' => 'royal-menswear',
                    'price' => 14499.00,
                    'sku' => 'JLX-BDG-02',
                    'stock' => 6 // Low stock
                ],
                [
                    'name' => 'Italian Handcrafted Leather Loafers',
                    'slug' => 'italian-handcrafted-leather-loafers',
                    'category' => 'luxury-footwear',
                    'price' => 8499.00,
                    'sku' => 'JLX-FTW-03',
                    'stock' => 22
                ],
                [
                    'name' => 'Embroidered Kashmiri Shawl Stole',
                    'slug' => 'embroidered-kashmiri-shawl-stole',
                    'category' => 'designer-accessories',
                    'price' => 4999.00,
                    'sku' => 'JLX-ACC-04',
                    'stock' => 35
                ],
                [
                    'name' => 'Chanderi Silk Festive Kurta',
                    'slug' => 'chanderi-silk-festive-kurta',
                    'category' => 'handcrafted-kurtas',
                    'price' => 5899.00,
                    'sku' => 'JLX-KRT-05',
                    'stock' => 18
                ],
                [
                    'name' => 'Embossed Leather Formal Belt',
                    'slug' => 'embossed-leather-formal-belt',
                    'category' => 'designer-accessories',
                    'price' => 2499.00,
                    'sku' => 'JLX-ACC-06',
                    'stock' => 40
                ],
                [
                    'name' => 'Zardozi Embroidered Jodhpuris',
                    'slug' => 'zardozi-embroidered-jodhpuris',
                    'category' => 'royal-menswear',
                    'price' => 12999.00,
                    'sku' => 'JLX-JOD-07',
                    'stock' => 9 // Low stock
                ],
                [
                    'name' => 'Pure Pashmina Nehru Jacket',
                    'slug' => 'pure-pashmina-nehru-jacket',
                    'category' => 'royal-menswear',
                    'price' => 9999.00,
                    'sku' => 'JLX-JKT-08',
                    'stock' => 12
                ],
                [
                    'name' => 'Suede Tassel Mojaris',
                    'slug' => 'suede-tassel-mojaris',
                    'category' => 'luxury-footwear',
                    'price' => 4299.00,
                    'sku' => 'JLX-FTW-09',
                    'stock' => 5 // Low stock
                ],
                [
                    'name' => 'Handwoven Banarasi Dupatta',
                    'slug' => 'handwoven-banarasi-dupatta',
                    'category' => 'designer-accessories',
                    'price' => 3799.00,
                    'sku' => 'JLX-ACC-10',
                    'stock' => 28
                ]
            ];

            $variantMap = [];
            foreach ($products as $p) {
                $check = $db->prepare("SELECT id FROM products WHERE slug = ?");
                $check->bind_param("s", $p['slug']);
                $check->execute();
                $res = $check->get_result();

                $catId = $catMap[$p['category']];
                if ($res->num_rows > 0) {
                    $prodId = $res->fetch_assoc()['id'];
                } else {
                    $shortDesc = "Exquisite " . $p['name'] . " crafted with perfection.";
                    $status = 'active';
                    $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, short_description, base_price, sale_price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssdds", $catId, $p['name'], $p['slug'], $shortDesc, $p['price'], $p['price'], $status);
                    $stmt->execute();
                    $prodId = $stmt->insert_id;
                    echo "  - Added product: {$p['name']}\n";
                }

                // Check / Insert Variant
                $vCheck = $db->prepare("SELECT id, stock_qty FROM product_variants WHERE sku = ?");
                $vCheck->bind_param("s", $p['sku']);
                $vCheck->execute();
                $vRes = $vCheck->get_result();
                if ($vRes->num_rows > 0) {
                    $vRow = $vRes->fetch_assoc();
                    $variantMap[$p['sku']] = [
                        'variant_id' => $vRow['id'],
                        'product_id' => $prodId,
                        'name'       => $p['name'],
                        'price'      => $p['price'],
                        'stock'      => $vRow['stock_qty']
                    ];
                } else {
                    $vStmt = $db->prepare("INSERT INTO product_variants (product_id, sku, price_override, stock_qty, is_active) VALUES (?, ?, ?, ?, 1)");
                    $vStmt->bind_param("isdi", $prodId, $p['sku'], $p['price'], $p['stock']);
                    $vStmt->execute();
                    $vId = $vStmt->insert_id;

                    $variantMap[$p['sku']] = [
                        'variant_id' => $vId,
                        'product_id' => $prodId,
                        'name'       => $p['name'],
                        'price'      => $p['price'],
                        'stock'      => $p['stock']
                    ];

                    // If stock < 10, add to stock_alerts
                    if ($p['stock'] < 10) {
                        $alStmt = $db->prepare("INSERT IGNORE INTO stock_alerts (variant_id, resolved) VALUES (?, 0)");
                        $alStmt->bind_param("i", $vId);
                        $alStmt->execute();
                    }
                }
            }

            // 4. Seed Orders & Order Items across diverse dates
            $ordersData = [
                [
                    'order_number'   => 'JLX-ORD-9024',
                    'customer_email' => 'aditya.k@gmail.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-15 minutes')),
                    'status'         => 'confirmed',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay (UPI)',
                    'city'           => 'Mumbai',
                    'state'          => 'Maharashtra',
                    'items'          => [
                        ['sku' => 'JLX-SHW-01', 'qty' => 1]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9023',
                    'customer_email' => 'meera.v@outlook.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'status'         => 'shipped',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay (Card)',
                    'city'           => 'New Delhi',
                    'state'          => 'Delhi',
                    'items'          => [
                        ['sku' => 'JLX-BDG-02', 'qty' => 1]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9022',
                    'customer_email' => 'rohan.deshmukh@yahoo.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-6 hours')),
                    'status'         => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay (NetBanking)',
                    'city'           => 'Pune',
                    'state'          => 'Maharashtra',
                    'items'          => [
                        ['sku' => 'JLX-FTW-03', 'qty' => 2],
                        ['sku' => 'JLX-ACC-06', 'qty' => 1]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9021',
                    'customer_email' => 'siddharth.rao@gmail.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'status'         => 'packed',
                    'payment_status' => 'pending',
                    'payment_method' => 'COD',
                    'city'           => 'Bengaluru',
                    'state'          => 'Karnataka',
                    'items'          => [
                        ['sku' => 'JLX-KRT-05', 'qty' => 1]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9020',
                    'customer_email' => 'ananya.b@gmail.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'status'         => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay',
                    'city'           => 'Jaipur',
                    'state'          => 'Rajasthan',
                    'items'          => [
                        ['sku' => 'JLX-ACC-04', 'qty' => 1],
                        ['sku' => 'JLX-ACC-10', 'qty' => 1]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9019',
                    'customer_email' => 'vikram.s@singhania.co',
                    'date'           => date('Y-m-d H:i:s', strtotime('-4 days')),
                    'status'         => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay',
                    'city'           => 'Kolkata',
                    'state'          => 'West Bengal',
                    'items'          => [
                        ['sku' => 'JLX-JOD-07', 'qty' => 1],
                        ['sku' => 'JLX-FTW-09', 'qty' => 1]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9018',
                    'customer_email' => 'priya.sharma99@gmail.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-8 days')),
                    'status'         => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay',
                    'city'           => 'Chandigarh',
                    'state'          => 'Punjab',
                    'items'          => [
                        ['sku' => 'JLX-JKT-08', 'qty' => 2]
                    ]
                ],
                [
                    'order_number'   => 'JLX-ORD-9017',
                    'customer_email' => 'kabir.mehta@gmail.com',
                    'date'           => date('Y-m-d H:i:s', strtotime('-15 days')),
                    'status'         => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'Razorpay',
                    'city'           => 'Ahmedabad',
                    'state'          => 'Gujarat',
                    'items'          => [
                        ['sku' => 'JLX-SHW-01', 'qty' => 1],
                        ['sku' => 'JLX-FTW-03', 'qty' => 1]
                    ]
                ]
            ];

            foreach ($ordersData as $od) {
                $oCheck = $db->prepare("SELECT id FROM orders WHERE order_number = ?");
                $oCheck->bind_param("s", $od['order_number']);
                $oCheck->execute();
                if ($oCheck->get_result()->num_rows > 0) {
                    continue;
                }

                $custId = $custMap[$od['customer_email']] ?? null;
                $custName = '';
                foreach ($customers as $c) {
                    if ($c['email'] === $od['customer_email']) {
                        $custName = $c['name'];
                        break;
                    }
                }

                // Calculate totals
                $subtotal = 0.00;
                foreach ($od['items'] as $item) {
                    $vInfo = $variantMap[$item['sku']];
                    $subtotal += ($vInfo['price'] * $item['qty']);
                }

                $tax = round($subtotal * 0.12); // 12% GST whole number
                $shipping = 0.00;
                $grandTotal = $subtotal + $tax + $shipping;

                $addr = "Flat 402, High Street Towers, " . $od['city'];
                $pincode = "400001";
                $phone = "9820011223";

                $oStmt = $db->prepare("
                    INSERT INTO orders (
                        customer_id, order_number, status, shipping_name, shipping_phone,
                        shipping_address1, shipping_city, shipping_state, shipping_pincode,
                        subtotal, tax_amount, grand_total, payment_method, payment_status, placed_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $oStmt->bind_param(
                    "issssssssdddsss",
                    $custId, $od['order_number'], $od['status'], $custName, $phone,
                    $addr, $od['city'], $od['state'], $pincode,
                    $subtotal, $tax, $grandTotal, $od['payment_method'], $od['payment_status'], $od['date']
                );
                $oStmt->execute();
                $orderId = $oStmt->insert_id;
                echo "  - Added order: {$od['order_number']} (₹{$grandTotal})\n";

                // Add Order Items
                foreach ($od['items'] as $item) {
                    $vInfo = $variantMap[$item['sku']];
                    $lineTotal = $vInfo['price'] * $item['qty'];
                    $itStmt = $db->prepare("
                        INSERT INTO order_items (order_id, variant_id, product_name, sku, quantity, unit_price, line_total)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $itStmt->bind_param(
                        "iissidd",
                        $orderId, $vInfo['variant_id'], $vInfo['name'], $item['sku'], $item['qty'], $vInfo['price'], $lineTotal
                    );
                    $itStmt->execute();
                }
            }

            // 5. Seed a return request
            $firstOrder = $db->query("SELECT id, customer_id FROM orders WHERE status = 'delivered' LIMIT 1")->fetch_assoc();
            if ($firstOrder) {
                $retCheck = $db->query("SELECT id FROM returns LIMIT 1");
                if ($retCheck->num_rows === 0) {
                    $reason = "Size slightly large, requested size exchange";
                    $status = 'requested';
                    $refundAmt = 0.00;
                    $rStmt = $db->prepare("INSERT INTO returns (order_id, customer_id, reason, status, refund_amount) VALUES (?, ?, ?, ?, ?)");
                    $rStmt->bind_param("iisss", $firstOrder['id'], $firstOrder['customer_id'], $reason, $status, $refundAmt);
                    $rStmt->execute();
                    echo "  - Added sample return request\n";
                }
            }

            echo "Demo data seeding completed successfully!\n";
        } catch (Exception $e) {
            echo "Seeding error: " . $e->getMessage() . "\n";
        }
    }
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    foreach ($_ENV as $k => $v) {
        putenv("$k=$v");
    }
    SeedDemoData::run();
}
