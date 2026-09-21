<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice #<?= htmlspecialchars($order['order_number'] ?? '0000') ?> | Jiyaji LX Staff Portal</title>
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
            background: rgba(79, 70, 229, 0.1);
            color: #4F46E5;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid rgba(79, 70, 229, 0.2);
        }
        .invoice-meta {
            text-align: right;
            font-size: 0.88rem;
        }
        .invoice-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #4F46E5;
            margin-bottom: 4px;
        }
        .invoice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            margin-bottom: 8px;
        }
        .party-details {
            font-size: 0.88rem;
            line-height: 1.6;
        }
        .party-name {
            font-weight: 700;
            font-size: 1rem;
            color: #0F172A;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th {
            background: #F8FAFC;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            padding: 10px 14px;
            text-align: left;
            border-top: 1px solid #E2E8F0;
            border-bottom: 1px solid #E2E8F0;
        }
        td {
            padding: 14px;
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.88rem;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 320px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.88rem;
            color: #64748B;
        }
        .totals-row.grand-total {
            border-top: 2px solid #0F172A;
            margin-top: 8px;
            padding-top: 10px;
            font-weight: 800;
            font-size: 1.1rem;
            color: #0F172A;
        }
        .invoice-footer {
            border-top: 1px solid #E2E8F0;
            padding-top: 20px;
            text-align: center;
            font-size: 0.78rem;
            color: #94A3B8;
        }
        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-print {
            background: #4F46E5;
            color: #FFFFFF;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
        }
        @media print {
            body { background: #FFFFFF; padding: 0; }
            .invoice-wrapper { border: none; box-shadow: none; padding: 0; }
            .no-print-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <a href="<?= url('portal/orders/' . htmlspecialchars($order['encrypted_id'] ?? encrypt_id($order['id']))) ?>" class="btn-back">
        &larr; Back to Order Details
    </a>
    <button onclick="window.print()" class="btn-print">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <span>Print Tax Invoice</span>
    </button>
</div>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div>
            <div class="brand-name">
                <span>JIYAJI</span>
                <span class="brand-tag">LX</span>
            </div>
            <div style="font-size: 0.78rem; color: #64748B; margin-top: 4px;">
                Bespoke Attire &bull; Premium Handcrafted Apparel
            </div>
            <div style="font-size: 0.78rem; color: #64748B;">
                <?= htmlspecialchars(store_setting('store_address', '124 Luxury Avenue, Fashion District, Mumbai, MH - 400001')) ?>
            </div>
            <div style="font-size: 0.78rem; color: #64748B;">
                GSTIN: <strong><?= htmlspecialchars(store_setting('store_gstin', '27AAPCJ1234F1Z5')) ?></strong>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="invoice-title">TAX INVOICE</div>
            <div>Invoice #: <strong>INV-<?= htmlspecialchars($order['order_number'] ?? '0000') ?></strong></div>
            <div>Order Date: <?= date('d M Y', strtotime($order['placed_at'] ?? $order['created_at'] ?? 'now')) ?></div>
            <div>Payment: <strong><?= ucfirst($order['payment_status'] ?? 'pending') ?></strong> (<?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?>)</div>
        </div>
    </div>

    <div class="invoice-grid">
        <div>
            <div class="section-title">Billed &amp; Shipped To</div>
            <div class="party-details">
                <div class="party-name"><?= htmlspecialchars($order['shipping_name'] ?? $order['customer_name'] ?? 'Customer') ?></div>
                <div><?= htmlspecialchars($order['shipping_address'] ?? '') ?></div>
                <div>
                    <?= htmlspecialchars($order['shipping_city'] ?? '') ?>, 
                    <?= htmlspecialchars($order['shipping_state'] ?? '') ?> - 
                    <?= htmlspecialchars($order['shipping_pincode'] ?? '') ?>
                </div>
                <div>Phone: <?= htmlspecialchars($order['shipping_phone'] ?? $order['customer_phone'] ?? 'N/A') ?></div>
                <div>Email: <?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?></div>
            </div>
        </div>

        <div>
            <div class="section-title">Logistics &amp; Fulfillment</div>
            <div class="party-details">
                <div>Courier: <strong><?= htmlspecialchars($order['courier_partner'] ?? 'Standard Surface') ?></strong></div>
                <div>AWB Tracking: <strong><?= htmlspecialchars($order['awb_number'] ?? 'Assigned upon dispatch') ?></strong></div>
                <div>Order Status: <strong><?= ucfirst(str_replace('_', ' ', $order['status'] ?? 'pending')) ?></strong></div>
                <div>Place of Supply: <?= htmlspecialchars($order['shipping_state'] ?? 'Maharashtra') ?> (<?= htmlspecialchars($order['shipping_country'] ?? 'India') ?>)</div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>SKU</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($order['items'])): ?>
                <?php foreach ($order['items'] as $i => $item): ?>
                    <?php
                    $unitPrice = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 1);
                    $lineTotal = (float)($item['line_total'] ?? $item['total'] ?? ($unitPrice * $qty));
                    $variantDesc = $item['variant_info'] ?? $item['variant_name'] ?? '';
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item['product_name'] ?? 'Garment') ?></strong>
                            <?php if (!empty($variantDesc) || !empty($item['size'])): ?>
                                <div style="font-size: 0.75rem; color: #64748B;">
                                    <?= htmlspecialchars($variantDesc) ?>
                                    <?= !empty($item['size']) && strpos($variantDesc, $item['size']) === false ? ' &bull; Size: ' . htmlspecialchars($item['size']) : '' ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="color: #64748B; font-family: monospace;"><?= htmlspecialchars($item['sku'] ?? 'N/A') ?></td>
                        <td class="text-center"><?= $qty ?></td>
                        <td class="text-right">₹<?= number_format($unitPrice, 2) ?></td>
                        <td class="text-right">₹<?= number_format($lineTotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center" style="color: #94A3B8;">No items recorded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="totals-table">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>₹<?= number_format((float)($order['subtotal'] ?? 0), 2) ?></span>
            </div>
            <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                <div class="totals-row" style="color: #059669;">
                    <span>Promotional Discount</span>
                    <span>-₹<?= number_format((float)$order['discount_amount'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="totals-row">
                <span>Shipping &amp; Handling</span>
                <span>₹<?= number_format((float)($order['shipping_amount'] ?? 0), 2) ?></span>
            </div>
            <div class="totals-row">
                <span>Taxes &amp; GST (Inclusive)</span>
                <span>₹<?= number_format((float)($order['tax_amount'] ?? 0), 2) ?></span>
            </div>
            <div class="totals-row grand-total">
                <span>Grand Total</span>
                <span>₹<?= number_format((float)($order['grand_total'] ?? 0), 2) ?></span>
            </div>
        </div>
    </div>

    <div class="invoice-footer">
        <p>Thank you for choosing Jiyaji LX. For any questions regarding this invoice, please reach out to customer concierge at care@jiyaji.com.</p>
        <p style="margin-top: 4px;">This is a computer-generated invoice and does not require a physical signature.</p>
    </div>
</div>

</body>
</html>
