<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label #<?= htmlspecialchars($order['order_number']) ?> | Jiyaji LX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Libre+Barcode+39+Text&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F1F5F9;
            color: #0F172A;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Action Toolbar */
        .actions-bar {
            width: 100%;
            max-width: 500px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .btn-print {
            background: #2D82FF;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(45, 130, 255, 0.25);
        }
        .btn-print:hover {
            background: #1B6DE0;
        }
        .btn-back {
            background: #FFFFFF;
            color: #475569;
            border-color: #CBD5E1;
        }
        .btn-back:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        /* Shipping Label Container: 4"x6" Proportion */
        .label-container {
            width: 100%;
            max-width: 480px;
            background: #FFFFFF;
            border: 2px solid #000000;
            border-radius: 4px;
            padding: 0;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            font-size: 11px;
            line-height: 1.35;
            color: #000000;
        }

        /* Section dividers */
        .label-section {
            border-bottom: 2px solid #000000;
            padding: 10px 14px;
        }
        .label-section:last-child {
            border-bottom: none;
        }

        /* Top Header: Brand + Courier + Routing */
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-logo-text {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .brand-logo-text span {
            background: #000000;
            color: #FFFFFF;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 10px;
            margin-left: 2px;
        }
        .courier-badge-box {
            border: 1.5px solid #000000;
            padding: 4px 8px;
            text-align: right;
            border-radius: 3px;
        }
        .courier-name {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .routing-code {
            font-family: 'Courier Prime', monospace;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        /* AWB Barcode Block */
        .barcode-block {
            text-align: center;
            padding: 14px 10px 10px;
            background: #FFFFFF;
        }
        .barcode-svg {
            width: 85%;
            height: 52px;
            margin: 0 auto;
            display: block;
        }
        .awb-text {
            font-family: 'Courier Prime', monospace;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        /* Payment & Mode Banner */
        .payment-banner {
            display: flex;
            align-items: stretch;
            border-bottom: 2px solid #000000;
        }
        .payment-mode-box {
            flex: 1;
            padding: 8px 12px;
            border-right: 2px solid #000000;
        }
        .payment-mode-title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .payment-mode-value {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .cod-amount-box {
            flex: 1.2;
            padding: 8px 12px;
            background: #FAFAFA;
        }
        .cod-amount-val {
            font-size: 17px;
            font-weight: 800;
        }

        /* Consignee (Deliver To) */
        .consignee-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .consignee-name {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .consignee-address {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 6px;
        }
        .pincode-highlight {
            display: inline-block;
            font-size: 16px;
            font-weight: 800;
            background: #000000;
            color: #FFFFFF;
            padding: 2px 6px;
            border-radius: 2px;
            margin-top: 4px;
        }
        .consignee-contact {
            font-size: 11px;
            font-weight: 700;
            margin-top: 6px;
        }

        /* Return / Shipper Address & Order Meta */
        .split-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 10px;
        }
        .shipper-box, .meta-box {
            line-height: 1.35;
        }
        .box-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #000000;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }

        /* Package Details Table */
        .package-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 4px;
        }
        .package-table th, .package-table td {
            border: 1px solid #CBD5E1;
            padding: 4px 6px;
            text-align: left;
        }
        .package-table th {
            background: #F8FAFC;
            font-weight: 700;
        }

        /* Print Specific Styling */
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .actions-bar {
                display: none !important;
            }
            .label-container {
                max-width: 100%;
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Actions Bar -->
    <div class="actions-bar">
        <a href="<?= url('admin/orders/' . $order['encrypted_id']) ?>" class="btn btn-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Order
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print Shipping Label (4"x6")
        </button>
    </div>

    <!-- Printable Label Container -->
    <div class="label-container">
        <!-- 1. Header with Brand & Courier Info -->
        <div class="label-section label-header">
            <div>
                <div class="brand-logo-text">JIYAJI <span>LX</span></div>
                <div style="font-size: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; color: #333;">Express Luxury Logistics</div>
            </div>
            <div class="courier-badge-box">
                <div class="courier-name"><?= htmlspecialchars($order['courier_partner'] ?: 'DELHIVERY AIR') ?></div>
                <div class="routing-code"><?= strtoupper(substr($order['shipping_city'], 0, 3)) ?> / <?= htmlspecialchars($order['shipping_pincode']) ?></div>
            </div>
        </div>

        <!-- 2. AWB Barcode Section -->
        <div class="label-section barcode-block">
            <!-- Vector Barcode Simulation Lines -->
            <svg class="barcode-svg" viewBox="0 0 260 50" preserveAspectRatio="none">
                <rect x="0" y="0" width="2" height="50" fill="#000"/>
                <rect x="4" y="0" width="1" height="50" fill="#000"/>
                <rect x="7" y="0" width="3" height="50" fill="#000"/>
                <rect x="13" y="0" width="2" height="50" fill="#000"/>
                <rect x="18" y="0" width="4" height="50" fill="#000"/>
                <rect x="25" y="0" width="1" height="50" fill="#000"/>
                <rect x="28" y="0" width="3" height="50" fill="#000"/>
                <rect x="34" y="0" width="2" height="50" fill="#000"/>
                <rect x="39" y="0" width="1" height="50" fill="#000"/>
                <rect x="42" y="0" width="4" height="50" fill="#000"/>
                <rect x="49" y="0" width="2" height="50" fill="#000"/>
                <rect x="54" y="0" width="3" height="50" fill="#000"/>
                <rect x="60" y="0" width="1" height="50" fill="#000"/>
                <rect x="63" y="0" width="2" height="50" fill="#000"/>
                <rect x="68" y="0" width="4" height="50" fill="#000"/>
                <rect x="75" y="0" width="2" height="50" fill="#000"/>
                <rect x="80" y="0" width="1" height="50" fill="#000"/>
                <rect x="84" y="0" width="3" height="50" fill="#000"/>
                <rect x="90" y="0" width="2" height="50" fill="#000"/>
                <rect x="95" y="0" width="4" height="50" fill="#000"/>
                <rect x="102" y="0" width="1" height="50" fill="#000"/>
                <rect x="106" y="0" width="3" height="50" fill="#000"/>
                <rect x="112" y="0" width="2" height="50" fill="#000"/>
                <rect x="117" y="0" width="4" height="50" fill="#000"/>
                <rect x="124" y="0" width="1" height="50" fill="#000"/>
                <rect x="128" y="0" width="3" height="50" fill="#000"/>
                <rect x="134" y="0" width="2" height="50" fill="#000"/>
                <rect x="139" y="0" width="1" height="50" fill="#000"/>
                <rect x="143" y="0" width="4" height="50" fill="#000"/>
                <rect x="150" y="0" width="2" height="50" fill="#000"/>
                <rect x="155" y="0" width="3" height="50" fill="#000"/>
                <rect x="161" y="0" width="1" height="50" fill="#000"/>
                <rect x="165" y="0" width="2" height="50" fill="#000"/>
                <rect x="170" y="0" width="4" height="50" fill="#000"/>
                <rect x="177" y="0" width="2" height="50" fill="#000"/>
                <rect x="182" y="0" width="1" height="50" fill="#000"/>
                <rect x="186" y="0" width="3" height="50" fill="#000"/>
                <rect x="192" y="0" width="2" height="50" fill="#000"/>
                <rect x="197" y="0" width="4" height="50" fill="#000"/>
                <rect x="204" y="0" width="1" height="50" fill="#000"/>
                <rect x="208" y="0" width="3" height="50" fill="#000"/>
                <rect x="214" y="0" width="2" height="50" fill="#000"/>
                <rect x="219" y="0" width="3" height="50" fill="#000"/>
                <rect x="225" y="0" width="1" height="50" fill="#000"/>
                <rect x="229" y="0" width="4" height="50" fill="#000"/>
                <rect x="236" y="0" width="2" height="50" fill="#000"/>
                <rect x="241" y="0" width="1" height="50" fill="#000"/>
                <rect x="245" y="0" width="3" height="50" fill="#000"/>
                <rect x="251" y="0" width="2" height="50" fill="#000"/>
                <rect x="256" y="0" width="4" height="50" fill="#000"/>
            </svg>
            <div class="awb-text">AWB: <?= htmlspecialchars($order['awb_number'] ?: ('AWB' . strtoupper(substr(md5($order['order_number']), 0, 10)))) ?></div>
        </div>

        <!-- 3. Payment Mode Banner -->
        <?php $isCod = stripos($order['payment_method'], 'cash') !== false || stripos($order['payment_method'], 'cod') !== false; ?>
        <div class="payment-banner">
            <div class="payment-mode-box">
                <div class="payment-mode-title">Payment Type</div>
                <div class="payment-mode-value" style="color: <?= $isCod ? '#C2410C' : '#059669' ?>;">
                    <?= $isCod ? 'CASH ON DELIVERY' : 'PREPAID' ?>
                </div>
            </div>
            <div class="cod-amount-box">
                <div class="payment-mode-title"><?= $isCod ? 'Amount to Collect' : 'Amount Paid' ?></div>
                <div class="cod-amount-val">₹<?= number_format((int)round((float)$order['grand_total'])) ?></div>
            </div>
        </div>

        <!-- 4. Consignee Delivery Address -->
        <div class="label-section">
            <div class="consignee-title">Deliver To / Shipping Address:</div>
            <div class="consignee-name"><?= htmlspecialchars($order['shipping_name'] ?: $order['customer_name']) ?></div>
            <div class="consignee-address">
                <?= htmlspecialchars($order['shipping_address1']) ?><br>
                <?php if (!empty($order['shipping_address2'])): ?>
                    <?= htmlspecialchars($order['shipping_address2']) ?><br>
                <?php endif; ?>
                <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> - <?= htmlspecialchars($order['shipping_pincode']) ?>
            </div>
            <div class="pincode-highlight">PIN: <?= htmlspecialchars($order['shipping_pincode']) ?></div>
            <div class="consignee-contact">
                Phone: <?= htmlspecialchars($order['shipping_phone'] ?: ($order['customer_phone'] ?? 'N/A')) ?>
            </div>
        </div>

        <!-- 5. Package Dimensions & Content Summary -->
        <div class="label-section">
            <div class="box-title">Order Content & Details</div>
            <table class="package-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th style="width: 50px;">SKU</th>
                        <th style="width: 40px; text-align: center;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order['items'])): ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                    <?php if (!empty($item['variant_info'])): ?>
                                        <div style="font-size: 9px; color: #555;"><?= htmlspecialchars($item['variant_info']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['sku'] ?? 'GEN-SKU') ?></td>
                                <td style="text-align: center;"><?= (int)$item['quantity'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Luxury Designer Apparel (Standard Package)</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 6. Shipper / Return Address & Meta -->
        <div class="label-section split-grid">
            <div class="shipper-box">
                <div class="box-title">Return If Undelivered To:</div>
                <strong>JIYAJI LUXURY COLLECTION</strong><br>
                Central Logistics Hub, Unit #4<br>
                Connaught Place Outer Ring<br>
                New Delhi - 110001, India<br>
                Tel: +91 87709 02424
            </div>
            <div class="meta-box">
                <div class="box-title">Shipping Specifications</div>
                <strong>Order #:</strong> <?= htmlspecialchars($order['order_number']) ?><br>
                <strong>Date:</strong> <?= date('d M Y', strtotime($order['placed_at'])) ?><br>
                <strong>Weight:</strong> 1.25 KG (Approx)<br>
                <strong>Dimensions:</strong> 35 x 28 x 8 CM<br>
                <strong>Tier:</strong> Express Air Priority
            </div>
        </div>
    </div>

</body>
</html>
