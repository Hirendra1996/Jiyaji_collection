<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice #<?= htmlspecialchars($order['order_number']) ?> | Jiyaji LX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            font-size: 14px;
            line-height: 1.5;
            padding: 30px 20px;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            padding: 40px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
        }
        .invoice-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #F1F5F9;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .brand-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .brand-tag {
            background: rgba(140, 48, 245, 0.1);
            color: #8C30F5;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid rgba(140, 48, 245, 0.2);
        }
        .invoice-meta {
            text-align: right;
            font-size: 0.88rem;
        }
        .invoice-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2D82FF;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .party-box {
            background: #F8FAFC;
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid #F1F5F9;
        }
        .party-title {
            font-size: 0.76rem;
            font-weight: 700;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table th {
            background: #F1F5F9;
            color: #475569;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            text-align: left;
        }
        .items-table td {
            padding: 14px;
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.88rem;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }
        .totals-box {
            width: 280px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.88rem;
            color: #475569;
        }
        .totals-row.grand-total {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0F172A;
            border-top: 2px solid #E2E8F0;
            padding-top: 8px;
        }
        .signature-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px solid #E2E8F0;
            font-size: 0.82rem;
            color: #64748B;
        }
        .print-toolbar {
            max-width: 800px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: linear-gradient(135deg, #2D82FF 0%, #8C30F5 100%);
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(45, 130, 255, 0.35);
        }
        @media print {
            body { background: #FFFFFF; padding: 0; }
            .invoice-wrapper { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="print-toolbar no-print">
    <a href="<?= url('admin/orders/' . htmlspecialchars($order['encrypted_id'])) ?>" style="color: #2D82FF; text-decoration: none; font-weight: 600;">
        &larr; Back to Order Details
    </a>
    <button type="button" class="btn-print" onclick="window.print()">
        Print Tax Invoice
    </button>
</div>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div>
            <div class="brand-name">
                <span>JIYAJI</span>
                <span class="brand-tag">LX</span>
            </div>
            <div style="font-size: 0.82rem; color: #64748B; margin-top: 6px;">
                <strong>Jiyaji Collection Pvt. Ltd.</strong><br>
                GSTIN: 27AABCT1345K1ZM<br>
                Luxury Fashion District, Bandra West, Mumbai 400050<br>
                support@jiyajicollection.com &bull; +91 98200 11223
            </div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-title">Tax Invoice</div>
            <div style="font-weight: 700; margin-top: 4px;">INV-<?= htmlspecialchars($order['order_number']) ?></div>
            <div style="color: #64748B; margin-top: 2px;">Date: <?= date('d M Y', strtotime($order['placed_at'])) ?></div>
            <div style="color: #64748B;">Order: <?= htmlspecialchars($order['order_number']) ?></div>
            <div style="margin-top: 4px;">
                <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; background: rgba(16, 185, 129, 0.1); color: #10B981; font-weight: 700; font-size: 0.76rem;">
                    Payment: <?= ucfirst($order['payment_status']) ?> (<?= htmlspecialchars($order['payment_method']) ?>)
                </span>
            </div>
        </div>
    </div>

    <div class="parties-grid">
        <div class="party-box">
            <div class="party-title">Billed & Shipped To</div>
            <div style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars($order['shipping_name']) ?></div>
            <div style="color: #475569; font-size: 0.84rem; margin-top: 4px;">
                <?= htmlspecialchars($order['shipping_address1']) ?><br>
                <?php if (!empty($order['shipping_address2'])): ?>
                    <?= htmlspecialchars($order['shipping_address2']) ?><br>
                <?php endif; ?>
                <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> &ndash; <?= htmlspecialchars($order['shipping_pincode']) ?><br>
                <?= htmlspecialchars($order['shipping_country']) ?>
            </div>
            <div style="font-size: 0.82rem; color: #64748B; margin-top: 4px;">
                Phone: <?= htmlspecialchars($order['shipping_phone'] ?? 'N/A') ?>
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">Logistics & Dispatch</div>
            <div style="color: #475569; font-size: 0.84rem;">
                <strong>Courier:</strong> <?= htmlspecialchars($order['courier_partner'] ?? 'Standard Surface') ?><br>
                <strong>AWB Tracking:</strong> <?= htmlspecialchars($order['awb_number'] ?? 'Assigned at dispatch') ?><br>
                <strong>Status:</strong> <?= ucfirst(str_replace('_', ' ', $order['status'])) ?><br>
                <strong>Place of Supply:</strong> <?= htmlspecialchars($order['shipping_state']) ?> (27)
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Item Description</th>
                <th>SKU</th>
                <th>Rate (₹)</th>
                <th>Qty</th>
                <th style="text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                        <?php if (!empty($item['variant_info'])): ?>
                            <div style="font-size: 0.76rem; color: #64748B;"><?= htmlspecialchars($item['variant_info']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><code><?= htmlspecialchars($item['sku'] ?? 'N/A') ?></code></td>
                    <td><?= number_format((int)round((float)$item['unit_price'])) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td style="text-align: right; font-weight: 600;"><?= number_format((int)round((float)$item['line_total'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-box">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>₹<?= number_format((int)round((float)$order['subtotal'])) ?></span>
            </div>
            <?php if ((float)$order['discount_amount'] > 0): ?>
                <div class="totals-row" style="color: #10B981;">
                    <span>Discount:</span>
                    <span>-₹<?= number_format((int)round((float)$order['discount_amount'])) ?></span>
                </div>
            <?php endif; ?>
            <div class="totals-row">
                <span>IGST / CGST+SGST (12%):</span>
                <span>₹<?= number_format((int)round((float)$order['tax_amount'])) ?></span>
            </div>
            <div class="totals-row">
                <span>Shipping Fee:</span>
                <span><?= (float)$order['shipping_charge'] > 0 ? '₹' . number_format((int)round((float)$order['shipping_charge'])) : 'FREE' ?></span>
            </div>
            <div class="totals-row grand-total">
                <span>Grand Total:</span>
                <span style="color: #2D82FF;">₹<?= number_format((int)round((float)$order['grand_total'])) ?></span>
            </div>
        </div>
    </div>

    <div class="signature-row">
        <div>
            Thank you for shopping with <strong>Jiyaji LX</strong>.<br>
            This is a computer-generated tax invoice and requires no physical signature.
        </div>
        <div style="text-align: right;">
            <div style="font-weight: 700;">For Jiyaji Collection Pvt. Ltd.</div>
            <div style="margin-top: 30px; border-top: 1px dashed #CBD5E1; padding-top: 4px; font-size: 0.76rem; color: #94A3B8;">
                Authorized Signatory
            </div>
        </div>
    </div>
</div>

</body>
</html>
