<?php
$awbNumber = htmlspecialchars($order['awb_number'] ?? 'AWB-PENDING');
$orderNumber = htmlspecialchars($order['order_number']);
$carrier = htmlspecialchars($order['courier_partner'] ?? 'Standard Surface');
$payMode = strtoupper($order['payment_method'] ?? 'PREPAID');
$payStatus = strtolower($order['payment_status'] ?? 'pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Label #<?= $awbNumber ?> | Jiyaji LX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Libre+Barcode+128&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: #F1F5F9;
            color: #0F172A;
            padding: 30px 20px;
            font-size: 13px;
            line-height: 1.4;
        }
        .no-print-bar {
            max-width: 480px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-print {
            background: #047857;
            color: #FFFFFF;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.86rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.86rem;
        }

        /* 4x6 Thermal Label Container */
        .shipping-label {
            width: 440px;
            margin: 0 auto;
            background: #FFFFFF;
            border: 2px solid #000000;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .carrier-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .tier-badge {
            font-size: 0.72rem;
            font-weight: 800;
            border: 1px solid #000000;
            padding: 2px 6px;
            text-transform: uppercase;
        }

        /* Barcode Area */
        .barcode-section {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding: 12px 0;
            margin-bottom: 10px;
        }
        .barcode-stripes {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            margin: 0 auto 6px auto;
        }
        .barcode-num {
            font-family: 'Space Mono', monospace;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.15em;
        }

        /* Pincode Routing Box */
        .pincode-routing {
            display: grid;
            grid-template-columns: 1fr 120px;
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .destination-pin {
            border: 2px solid #000000;
            text-align: center;
            padding: 6px;
        }
        .pin-label {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pin-val {
            font-family: 'Space Mono', monospace;
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.1;
            margin-top: 2px;
        }

        /* Addresses */
        .address-box {
            border-bottom: 1px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .addr-title {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .addr-name {
            font-size: 0.95rem;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .addr-text {
            font-size: 0.84rem;
            line-height: 1.4;
        }

        /* Consignment Specs Table */
        .specs-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 8px;
            font-size: 0.78rem;
            gap: 6px;
        }
        .spec-item span:first-child {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.68rem;
            color: #475569;
            display: block;
        }
        .spec-item span:last-child {
            font-weight: 800;
            font-size: 0.86rem;
        }

        .label-footer {
            font-size: 0.68rem;
            text-align: center;
            color: #64748B;
            line-height: 1.3;
        }

        @media print {
            body { background: #FFFFFF; padding: 0; }
            .no-print-bar { display: none; }
            .shipping-label {
                width: 100%;
                border: 2px solid #000000;
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <a href="<?= url('portal/shipments/' . htmlspecialchars($order['encrypted_id'] ?? encrypt_id($order['id']))) ?>" class="btn-back">
        &larr; Back to Shipment Details
    </a>
    <button onclick="window.print()" class="btn-print">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <span>Print Shipping Label</span>
    </button>
</div>

<div class="shipping-label">
    <!-- Carrier Header -->
    <div class="label-header">
        <div>
            <div class="carrier-title"><?= $carrier ?></div>
            <div style="font-size: 0.72rem; color: #475569;">Domestic Express Delivery Network</div>
        </div>
        <div class="tier-badge">
            PRIORITY AIR
        </div>
    </div>

    <!-- Barcode Section -->
    <div class="barcode-section">
        <!-- SVG Generated Crisp Barcode Representation -->
        <svg width="260" height="42" style="display: block; margin: 0 auto 6px auto;">
            <rect x="5" y="0" width="3" height="42" fill="#000"/>
            <rect x="11" y="0" width="2" height="42" fill="#000"/>
            <rect x="16" y="0" width="5" height="42" fill="#000"/>
            <rect x="24" y="0" width="2" height="42" fill="#000"/>
            <rect x="29" y="0" width="4" height="42" fill="#000"/>
            <rect x="36" y="0" width="6" height="42" fill="#000"/>
            <rect x="45" y="0" width="3" height="42" fill="#000"/>
            <rect x="51" y="0" width="5" height="42" fill="#000"/>
            <rect x="59" y="0" width="2" height="42" fill="#000"/>
            <rect x="64" y="0" width="6" height="42" fill="#000"/>
            <rect x="73" y="0" width="3" height="42" fill="#000"/>
            <rect x="79" y="0" width="5" height="42" fill="#000"/>
            <rect x="87" y="0" width="4" height="42" fill="#000"/>
            <rect x="94" y="0" width="2" height="42" fill="#000"/>
            <rect x="99" y="0" width="5" height="42" fill="#000"/>
            <rect x="107" y="0" width="3" height="42" fill="#000"/>
            <rect x="113" y="0" width="6" height="42" fill="#000"/>
            <rect x="122" y="0" width="2" height="42" fill="#000"/>
            <rect x="127" y="0" width="4" height="42" fill="#000"/>
            <rect x="134" y="0" width="5" height="42" fill="#000"/>
            <rect x="142" y="0" width="3" height="42" fill="#000"/>
            <rect x="148" y="0" width="6" height="42" fill="#000"/>
            <rect x="157" y="0" width="2" height="42" fill="#000"/>
            <rect x="162" y="0" width="5" height="42" fill="#000"/>
            <rect x="170" y="0" width="3" height="42" fill="#000"/>
            <rect x="176" y="0" width="6" height="42" fill="#000"/>
            <rect x="185" y="0" width="4" height="42" fill="#000"/>
            <rect x="192" y="0" width="2" height="42" fill="#000"/>
            <rect x="197" y="0" width="5" height="42" fill="#000"/>
            <rect x="205" y="0" width="3" height="42" fill="#000"/>
            <rect x="211" y="0" width="6" height="42" fill="#000"/>
            <rect x="220" y="0" width="2" height="42" fill="#000"/>
            <rect x="225" y="0" width="4" height="42" fill="#000"/>
            <rect x="232" y="0" width="5" height="42" fill="#000"/>
            <rect x="240" y="0" width="3" height="42" fill="#000"/>
            <rect x="246" y="0" width="6" height="42" fill="#000"/>
        </svg>
        <div class="barcode-num"><?= $awbNumber ?></div>
    </div>

    <!-- Pincode & Routing Hub -->
    <div class="pincode-routing">
        <div>
            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #475569;">Destination City &amp; Hub</div>
            <div style="font-size: 1.1rem; font-weight: 800;"><?= htmlspecialchars($order['shipping_city'] ?? 'METRO') ?> (<?= htmlspecialchars($order['shipping_state'] ?? 'IN') ?>)</div>
        </div>
        <div class="destination-pin">
            <div class="pin-label">PINCODE</div>
            <div class="pin-val"><?= htmlspecialchars($order['shipping_pincode'] ?? '000000') ?></div>
        </div>
    </div>

    <!-- Recipient Address -->
    <div class="address-box">
        <div class="addr-title">Deliver To (Consignee)</div>
        <div class="addr-name"><?= htmlspecialchars($order['shipping_name'] ?? $order['customer_name'] ?? 'Recipient') ?></div>
        <div class="addr-text">
            <?= htmlspecialchars($order['shipping_address1'] ?? '') ?>
            <?php if (!empty($order['shipping_address2'])): ?>
                , <?= htmlspecialchars($order['shipping_address2']) ?>
            <?php endif; ?><br>
            <?= htmlspecialchars($order['shipping_city'] ?? '') ?>, <?= htmlspecialchars($order['shipping_state'] ?? '') ?> - <?= htmlspecialchars($order['shipping_pincode'] ?? '') ?><br>
            <strong>Phone: <?= htmlspecialchars($order['shipping_phone'] ?? $order['customer_phone'] ?? 'N/A') ?></strong>
        </div>
    </div>

    <!-- Shipper Return Address -->
    <div class="address-box">
        <div class="addr-title">Shipped By (Return Address If Undelivered)</div>
        <div style="font-weight: 800; font-size: 0.88rem;">JIYAJI LUXURY APPAREL ATELIER</div>
        <div style="font-size: 0.78rem; color: #334155; line-height: 1.35;">
            124 Luxury Avenue, Fashion District, Lower Parel, Mumbai, MH - 400001<br>
            Contact: care@jiyaji.com &bull; +91 (022) 8900-5400
        </div>
    </div>

    <!-- Consignment Specs Grid -->
    <div class="specs-grid">
        <div class="spec-item">
            <span>Order Reference</span>
            <span>#<?= $orderNumber ?></span>
        </div>
        <div class="spec-item">
            <span>Payment Method</span>
            <span><?= $payMode ?> (<?= ucfirst($payStatus) ?>)</span>
        </div>
        <div class="spec-item">
            <span>Package Dead Weight</span>
            <span>1.25 KG</span>
        </div>
        <div class="spec-item">
            <span>Dispatch Date</span>
            <span><?= date('d-M-Y', strtotime($order['placed_at'])) ?></span>
        </div>
    </div>

    <!-- Legal Declaration Footer -->
    <div class="label-footer">
        Handcrafted luxury garments &bull; Handle with utmost care &bull; Keep dry &bull; Valid tax invoice enclosed inside parcel
    </div>
</div>

</body>
</html>
