<?php
$title = 'Initiate Return / Exchange | Jiyaji LX Staff Portal';
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumb Navigation -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.84rem;">
                    <a href="<?= url('portal/returns') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        &larr; Returns &amp; Exchanges
                    </a>
                    <span style="color: #CBD5E1;">/</span>
                    <span style="color: #64748B; font-weight: 600;">Initiate RMA Case</span>
                </div>
            </div>

            <!-- Page Title Card -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="font-size: 0.75rem; font-weight: 700; color: #4F46E5; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                    Staff-Assisted RMA
                </div>
                <h1 style="font-size: 1.55rem; font-weight: 800; color: #0F172A; margin: 0 0 6px 0;">
                    Initiate Return or Garment Exchange
                </h1>
                <p style="font-size: 0.86rem; color: #64748B; margin: 0;">
                    Create a formal return merchandise authorization (RMA) on behalf of a customer for an eligible order.
                </p>
            </div>

            <?php if (empty($selectedOrder)): ?>
                <!-- Order Selection Step -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 26px; margin-bottom: 24px; max-width: 680px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <h3 style="margin: 0 0 16px 0; font-size: 1rem; font-weight: 800; color: #0F172A;">
                        Step 1: Select Eligible Order
                    </h3>

                    <form action="<?= url('portal/returns/create') ?>" method="GET">
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                                Choose from Recent Delivered Orders
                            </label>
                            <select name="order_id" style="width: 100%; height: 42px; border-radius: 10px; border: 1px solid #CBD5E1; padding: 0 12px; font-size: 0.86rem; font-weight: 600; background: #FFFFFF;" required>
                                <option value="">-- Select an Order --</option>
                                <?php if (!empty($recentOrders)): ?>
                                    <?php foreach ($recentOrders as $ro): ?>
                                        <?php if (empty($ro) || !is_array($ro)) continue; ?>
                                        <option value="<?= htmlspecialchars($ro['encrypted_id'] ?? '') ?>">
                                            #<?= htmlspecialchars($ro['order_number'] ?? 'Order') ?> &bull; <?= htmlspecialchars($ro['customer_name'] ?? 'Guest') ?> &bull; ₹<?= number_format((float)($ro['grand_total'] ?? 0), 2) ?> (<?= ucfirst($ro['status'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <button type="submit" style="padding: 10px 22px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.84rem; border: none; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <span>Continue to RMA Details</span>
                            &rarr;
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- RMA Registration Form with Selected Order -->
                <form action="<?= url('portal/returns/create') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= (int)$selectedOrder['id'] ?>">

                    <div style="display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start;">

                        <!-- Left Column: Items & Details -->
                        <div style="display: flex; flex-direction: column; gap: 24px;">

                            <!-- Order Context Summary Banner -->
                            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                                <div>
                                    <div style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Selected Order Reference</div>
                                    <div style="font-size: 1rem; font-weight: 800; color: #0F172A; margin-top: 2px;">
                                        Order #<?= htmlspecialchars($selectedOrder['order_number']) ?>
                                    </div>
                                    <div style="font-size: 0.78rem; color: #64748B; margin-top: 2px;">
                                        Customer: <strong><?= htmlspecialchars($selectedOrder['shipping_name'] ?? $selectedOrder['customer_name'] ?? 'Guest') ?></strong> &bull; Total: ₹<?= number_format((float)$selectedOrder['grand_total'], 2) ?>
                                    </div>
                                </div>
                                <div>
                                    <a href="<?= url('portal/returns/create') ?>" style="font-size: 0.76rem; color: #4F46E5; font-weight: 700; text-decoration: none;">
                                        Change Order
                                    </a>
                                </div>
                            </div>

                            <!-- Step 2: Select Items -->
                            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                <div style="padding: 16px 20px; border-bottom: 1px solid #E2E8F0; background: #FAFBFD;">
                                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                        Step 2: Select Items to Return or Exchange
                                    </h3>
                                    <p style="font-size: 0.78rem; color: #64748B; margin: 4px 0 0 0;">Check the box for each garment being returned and confirm quantity.</p>
                                </div>

                                <div style="padding: 16px 20px;">
                                    <?php if (!empty($selectedOrder['items'])): ?>
                                        <?php foreach ($selectedOrder['items'] as $item): ?>
                                            <?php
                                            $orderItemId = (int)$item['id'];
                                            $unitPrice = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                                            $maxQty = (int)($item['quantity'] ?? 1);
                                            ?>
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 10px; background: #FFFFFF;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <input type="checkbox" name="items[]" value="<?= $orderItemId ?>" id="item_<?= $orderItemId ?>" style="width: 18px; height: 18px; accent-color: #4F46E5;" checked>
                                                    <div>
                                                        <label for="item_<?= $orderItemId ?>" style="font-weight: 700; color: #0F172A; font-size: 0.88rem; cursor: pointer;">
                                                            <?= htmlspecialchars($item['product_name'] ?? 'Garment') ?>
                                                        </label>
                                                        <div style="font-size: 0.74rem; color: #64748B;">
                                                            <?= htmlspecialchars($item['variant_info'] ?? 'Standard') ?> &bull; SKU: <?= htmlspecialchars($item['sku'] ?? 'N/A') ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: center; gap: 16px;">
                                                    <div style="font-size: 0.85rem; font-weight: 700; color: #0F172A;">
                                                        ₹<?= number_format($unitPrice, 2) ?>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <label style="font-size: 0.72rem; font-weight: 700; color: #64748B;">Qty:</label>
                                                        <input type="number" name="qty_<?= $orderItemId ?>" value="1" min="1" max="<?= $maxQty ?>" style="width: 55px; height: 32px; border-radius: 6px; border: 1px solid #CBD5E1; text-align: center; font-weight: 700; font-size: 0.85rem;">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="color: #94A3B8; font-size: 0.84rem; margin: 0;">No items found for this order.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Step 3: Reason & Instructions -->
                            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                <h3 style="margin: 0 0 16px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                    Step 3: Reason Code &amp; Client Description
                                </h3>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                    <div>
                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                                            Request Type
                                        </label>
                                        <select name="request_type" id="reqTypeSelect" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.85rem; font-weight: 600;" onchange="toggleExchangeField(this.value)">
                                            <option value="return">Return for Full Refund</option>
                                            <option value="exchange">Exchange for Different Size / Variant</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                                            Primary Reason Code
                                        </label>
                                        <select name="reason" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; font-size: 0.85rem; font-weight: 600;">
                                            <option value="Size slightly loose, requested smaller size">Size too large / loose fit</option>
                                            <option value="Size tight across chest or shoulders">Size too small / tight fit</option>
                                            <option value="Fabric or embroidery defect observed">Fabric or embroidery defect</option>
                                            <option value="Color or shade variation from website">Color variation from digital catalogue</option>
                                            <option value="Ordered duplicate item by mistake">Duplicate item ordered by mistake</option>
                                            <option value="Delivered later than required event date">Arrived after customer's event date</option>
                                            <option value="Bespoke customer dissatisfaction">Client preference / dissatisfaction</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="margin-bottom: 16px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                                        Detailed Customer Explanation
                                    </label>
                                    <textarea name="description" rows="3" placeholder="Provide notes regarding the customer's return request..." style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px; font-size: 0.84rem; box-sizing: border-box;"></textarea>
                                </div>

                                <div id="exchangeFieldWrap" style="display: none; margin-bottom: 16px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #6D28D9; margin-bottom: 6px;">
                                        Requested Replacement Details (Size, Color, Variant)
                                    </label>
                                    <textarea name="exchange_notes" rows="2" placeholder="e.g. Please replace with Size 40 (Chest 42) in same shade." style="width: 100%; border-radius: 8px; border: 1px solid #7C3AED; background: #FAF5FF; padding: 10px; font-size: 0.84rem; box-sizing: border-box;"></textarea>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column: Submission & Policies -->
                        <div style="display: flex; flex-direction: column; gap: 20px;">

                            <!-- Administrative Action Card -->
                            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                <h3 style="margin: 0 0 14px 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                                    Staff Verification Memo
                                </h3>

                                <div style="margin-bottom: 16px;">
                                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                                        Internal Concierge Note
                                    </label>
                                    <textarea name="admin_note" rows="3" placeholder="Initial observation by staff (e.g. Customer contacted via WhatsApp concierge)." style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px; font-size: 0.82rem; box-sizing: border-box;"></textarea>
                                </div>

                                <button type="submit" style="width: 100%; height: 42px; border-radius: 10px; background: #4F46E5; color: #FFFFFF; font-weight: 700; font-size: 0.86rem; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338CA';" onmouseout="this.style.background='#4F46E5';">
                                    Register RMA Case
                                </button>
                            </div>

                            <!-- Policy Guidelines Card -->
                            <div style="background: #FAFBFD; border: 1px solid #E2E8F0; border-radius: 14px; padding: 18px; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                                <div style="font-weight: 700; color: #0F172A; margin-bottom: 6px;">Jiyaji Return Guidelines:</div>
                                <ul style="margin: 0; padding-left: 18px;">
                                    <li>Returns must be requested within 7 days of verified delivery.</li>
                                    <li>Items must retain luxury brand tags, original packaging, and show no signs of fragrance or alterations.</li>
                                    <li>Refunds are credited to customer store credit or original source after atelier physical QA.</li>
                                </ul>
                            </div>

                        </div>

                    </div>
                </form>

                <script>
                function toggleExchangeField(type) {
                    var wrap = document.getElementById('exchangeFieldWrap');
                    if (wrap) {
                        wrap.style.display = (type === 'exchange') ? 'block' : 'none';
                    }
                }
                </script>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
