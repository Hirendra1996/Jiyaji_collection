<?php
$title      = $title ?? 'Coupons & Promotional Campaigns | Jiyaji LX Operations Portal';
$coupons    = $coupons ?? [];
$filters    = $filters ?? [];
$pagination = $pagination ?? ['has_prev' => false, 'has_next' => false, 'current_page' => 1, 'total_pages' => 1, 'total' => 0];
$kpis       = $kpis ?? [];
$canManage  = $canManage ?? false;

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #059669; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <span style="color: #64748B;">Marketing &amp; Growth</span>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Coupons &amp; Offers</span>
            </div>

            <!-- Hero Banner -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #064E3B 55%, #059669 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.28);">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #D1FAE5; margin-bottom: 8px; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 12 20 22 4 22 4 12"></polyline>
                            <rect x="2" y="7" width="20" height="5"></rect>
                            <line x1="12" y1="22" x2="12" y2="7"></line>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                        </svg>
                        Promotions &bull; Basket Rules &bull; Customer Redemptions
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">Coupons &amp; Promotional Offers</h1>
                    <p style="font-size: 0.88rem; color: #A7F3D0; margin: 0;">
                        Design discount codes, percentage flash promotions, minimum cart incentives, and customer usage caps.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canManage): ?>
                        <a href="<?= url('portal/coupons/create') ?>" 
                           style="background: #FFFFFF; color: #065F46; font-weight: 700; font-size: 0.84rem; padding: 10px 18px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s ease;"
                           onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';"
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Launch New Coupon</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('portal/coupons/export?' . http_build_query(array_filter($filters, fn($v) => $v !== '' && $v !== 'all'))) ?>" 
                       style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s ease;" 
                       onmouseover="this.style.background='rgba(255,255,255,0.22)';" 
                       onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <!-- Total Coupons -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #64748B; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em;">Total Campaigns</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['total_coupons'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Registered promo codes</div>
                </div>

                <!-- Active & Live -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #10B981; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">Active &amp; Live</div>
                        <?php if (($kpis['active_count'] ?? 0) > 0): ?>
                            <span style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">Live</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #059669; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['active_count'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Redeemable on store</div>
                </div>

                <!-- Expiring Soon -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #F59E0B; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Expiring Soon</div>
                        <?php if (($kpis['expiring_soon'] ?? 0) > 0): ?>
                            <span style="background: rgba(245, 158, 11, 0.15); color: #B45309; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">7 Days</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #D97706; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['expiring_soon'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Approaching expiration</div>
                </div>

                <!-- Total Redemptions -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #0284C7; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #0284C7; text-transform: uppercase; letter-spacing: 0.05em;">Total Redemptions</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['total_redemptions'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Applied in checkout</div>
                </div>

                <!-- Customer Savings -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #8B5CF6; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #7C3AED; text-transform: uppercase; letter-spacing: 0.05em;">Total Savings Given</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #7C3AED; margin-top: 6px; letter-spacing: -0.02em;">₹<?= number_format((float)($kpis['total_savings'] ?? 0)) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Benefiting buyers</div>
                </div>
            </div>

            <!-- Segment Tabs -->
            <?php
            $currentStatus = $filters['status'] ?? 'all';
            $currentType   = $filters['type']   ?? 'all';

            $tabs = [
                'all'       => 'All Coupons (' . ($kpis['total_coupons'] ?? 0) . ')',
                'active'    => 'Active & Live (' . ($kpis['active_count'] ?? 0) . ')',
                'expired'   => 'Expired (' . ($kpis['expired_count'] ?? 0) . ')',
                'disabled'  => 'Disabled',
                'exhausted' => 'Exhausted',
            ];
            ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
                <?php foreach ($tabs as $tKey => $tLabel): 
                    $tabParams = $filters;
                    $tabParams['status'] = $tKey;
                    $tabParams['page'] = 1;
                    $isActive = ($currentStatus === $tKey);
                ?>
                    <a href="<?= url('portal/coupons?' . http_build_query($tabParams)) ?>" 
                       style="padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; text-decoration: none; white-space: nowrap; transition: all 0.2s ease; <?= $isActive ? 'background: #059669; color: #FFFFFF; box-shadow: 0 2px 6px rgba(5,150,105,0.3);' : 'background: #FFFFFF; color: #64748B; border: 1px solid #E2E8F0;' ?>">
                        <?= $tLabel ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter Toolbar -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <form action="<?= url('portal/coupons') ?>" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">

                    <div style="flex: 2; min-width: 220px;">
                        <input type="text" name="search" placeholder="Search by Coupon Code or Description..." 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                               style="width: 100%; height: 40px; padding: 0 14px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; outline: none; transition: border-color 0.2s;"
                               onfocus="this.style.borderColor='#059669';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="type" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; background: #FFF; outline: none;">
                            <option value="all">All Discount Types</option>
                            <option value="percentage" <?= $currentType === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                            <option value="flat" <?= $currentType === 'flat' ? 'selected' : '' ?>>Flat Amount (₹)</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="sort" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; background: #FFF; outline: none;">
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest Created</option>
                            <option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="most_used" <?= ($filters['sort'] ?? '') === 'most_used' ? 'selected' : '' ?>>Most Redeemed</option>
                            <option value="expiring" <?= ($filters['sort'] ?? '') === 'expiring' ? 'selected' : '' ?>>Expiring Soonest</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" style="height: 40px; padding: 0 18px; background: #059669; color: #FFFFFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            Filter
                        </button>
                        <a href="<?= url('portal/coupons') ?>" style="height: 40px; padding: 0 14px; background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center;" title="Reset Filters">
                            ✕
                        </a>
                    </div>
                </form>
            </div>

            <!-- Coupons Ledger Table -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 24px;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                        <thead>
                            <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.74rem; text-transform: uppercase; color: #64748B; letter-spacing: 0.05em;">
                                <th style="padding: 14px 18px;">Promo Code</th>
                                <th style="padding: 14px 18px;">Discount Benefit</th>
                                <th style="padding: 14px 18px;">Basket Rules</th>
                                <th style="padding: 14px 18px;">Validity Window</th>
                                <th style="padding: 14px 18px;">Redemptions</th>
                                <th style="padding: 14px 18px; text-align: center;">Status</th>
                                <th style="padding: 14px 18px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coupons)): ?>
                                <tr>
                                    <td colspan="7" style="padding: 56px 20px; text-align: center; color: #64748B;">
                                        <div style="font-size: 2.5rem; margin-bottom: 12px;">🏷️</div>
                                        <div style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No Promotional Coupons Found</div>
                                        <div style="font-size: 0.86rem; color: #64748B; max-width: 440px; margin: 0 auto 16px;">
                                            No discount campaigns match the specified criteria or status tab.
                                        </div>
                                        <?php if ($canManage): ?>
                                            <a href="<?= url('portal/coupons/create') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: #059669; color: #fff; border-radius: 8px; font-weight: 700; font-size: 0.82rem; text-decoration: none;">
                                                + Launch First Coupon
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($coupons as $cpn): 
                                    $encId = encrypt_id($cpn['id']);
                                    $cStatus = $cpn['computed_status'] ?? 'active';

                                    $statusStyle = match($cStatus) {
                                        'active'    => 'background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);',
                                        'expired'   => 'background: rgba(239, 68, 68, 0.1); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.25);',
                                        'disabled'  => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);',
                                        'exhausted' => 'background: rgba(245, 158, 11, 0.12); color: #B45309; border: 1px solid rgba(245, 158, 11, 0.3);',
                                        default     => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);'
                                    };
                                ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        
                                        <!-- Code & Description -->
                                        <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <a href="<?= url('portal/coupons/' . $encId) ?>" 
                                                   style="font-family: monospace; font-size: 0.95rem; font-weight: 900; letter-spacing: 0.04em; color: #0F172A; text-decoration: none; background: #F1F5F9; border: 1px solid #CBD5E1; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;"
                                                   onmouseover="this.style.color='#059669'; this.style.borderColor='#059669';" onmouseout="this.style.color='#0F172A'; this.style.borderColor='#CBD5E1';">
                                                    <?= htmlspecialchars($cpn['code']) ?>
                                                </a>
                                                <button type="button" onclick="copyCode('<?= htmlspecialchars($cpn['code']) ?>', this)" 
                                                        style="background: none; border: none; cursor: pointer; color: #94A3B8; padding: 4px;" title="Copy Code">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </button>
                                            </div>
                                            <?php if (!empty($cpn['description'])): ?>
                                                <div style="font-size: 0.76rem; color: #64748B; margin-top: 5px; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?= htmlspecialchars($cpn['description']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="margin-top: 4px;">
                                                <span style="font-size: 0.68rem; font-weight: 700; color: <?= $cpn['is_public'] ? '#059669' : '#64748B' ?>;">
                                                    <?= $cpn['is_public'] ? '● Public on Store' : '○ Private / VIP' ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Discount Value & Type -->
                                        <td style="padding: 14px 18px; vertical-align: top;">
                                            <div style="font-weight: 800; font-size: 1rem; color: #0F172A;">
                                                <?php if ($cpn['type'] === 'percentage'): ?>
                                                    <span style="color: #059669;"><?= (int)$cpn['value'] ?>% OFF</span>
                                                <?php else: ?>
                                                    <span style="color: #0284C7;">₹<?= number_format((float)$cpn['value']) ?> FLAT</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($cpn['type'] === 'percentage' && !empty($cpn['max_discount_cap'])): ?>
                                                <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; margin-top: 2px;">
                                                    Max cap: ₹<?= number_format((float)$cpn['max_discount_cap']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px; text-transform: uppercase;">
                                                <?= $cpn['type'] === 'percentage' ? 'Percentage' : 'Flat Basket' ?>
                                            </div>
                                        </td>

                                        <!-- Basket Rules -->
                                        <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                                            <div style="font-weight: 700; font-size: 0.84rem; color: #0F172A;">
                                                <?= (float)$cpn['min_cart_value'] > 0 ? '₹' . number_format((float)$cpn['min_cart_value']) : 'No Minimum' ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748B; margin-top: 2px;">
                                                Min Order Requirement
                                            </div>
                                            <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 3px;">
                                                <?= (int)$cpn['usage_limit_per_user'] ?>× per customer
                                            </div>
                                        </td>

                                        <!-- Validity Window -->
                                        <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                                            <div style="font-size: 0.82rem; color: #0F172A; font-weight: 600;">
                                                <?= !empty($cpn['starts_at']) ? date('M d, Y', strtotime($cpn['starts_at'])) : 'Ongoing' ?>
                                                <span style="color: #94A3B8;">&rarr;</span>
                                                <?= !empty($cpn['expires_at']) ? date('M d, Y', strtotime($cpn['expires_at'])) : 'No Expiry' ?>
                                            </div>
                                            <?php
                                            if (!empty($cpn['expires_at'])) {
                                                $expTs = strtotime($cpn['expires_at']);
                                                $nowTs = time();
                                                if ($expTs < $nowTs) {
                                                    echo '<div style="font-size: 0.72rem; color: #DC2626; font-weight: 700; margin-top: 3px;">Expired</div>';
                                                } else {
                                                    $daysLeft = ceil(($expTs - $nowTs) / 86400);
                                                    if ($daysLeft <= 7) {
                                                        echo '<div style="font-size: 0.72rem; color: #D97706; font-weight: 700; margin-top: 3px;">Expires in ' . $daysLeft . ' day' . ($daysLeft > 1 ? 's' : '') . '</div>';
                                                    } else {
                                                        echo '<div style="font-size: 0.72rem; color: #059669; font-weight: 600; margin-top: 3px;">Active (' . $daysLeft . ' days left)</div>';
                                                    }
                                                }
                                            } else {
                                                echo '<div style="font-size: 0.72rem; color: #64748B; font-weight: 600; margin-top: 3px;">Perpetual validity</div>';
                                            }
                                            ?>
                                        </td>

                                        <!-- Redemptions & Capacity -->
                                        <td style="padding: 14px 18px; vertical-align: top; min-width: 140px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                                <span style="font-weight: 800; font-size: 0.88rem; color: #0F172A;">
                                                    <?= number_format((int)$cpn['times_used']) ?>
                                                </span>
                                                <span style="font-size: 0.74rem; color: #64748B;">
                                                    <?= !empty($cpn['usage_limit_global']) ? 'of ' . number_format((int)$cpn['usage_limit_global']) : 'unlimited' ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($cpn['usage_limit_global'])): 
                                                $pct = min(100, round(($cpn['times_used'] / $cpn['usage_limit_global']) * 100));
                                                $barColor = $pct >= 90 ? '#DC2626' : ($pct >= 75 ? '#D97706' : '#059669');
                                            ?>
                                                <div style="width: 100%; height: 5px; background: #E2E8F0; border-radius: 999px; overflow: hidden;">
                                                    <div style="width: <?= $pct ?>%; height: 100%; background: <?= $barColor ?>;"></div>
                                                </div>
                                                <div style="font-size: 0.68rem; color: #94A3B8; margin-top: 3px;"><?= $pct ?>% redeemed</div>
                                            <?php else: ?>
                                                <div style="font-size: 0.7rem; color: #94A3B8;">No total cap</div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status -->
                                        <td style="padding: 14px 18px; vertical-align: top; text-align: center; white-space: nowrap;">
                                            <span id="coupon-status-badge-<?= $cpn['id'] ?>" style="display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; <?= $statusStyle ?>">
                                                <?= ucfirst($cStatus) ?>
                                            </span>

                                            <?php if ($canManage): ?>
                                                <div style="margin-top: 6px;">
                                                    <button type="button" onclick="toggleCouponStatus('<?= $encId ?>', <?= $cpn['id'] ?>)" 
                                                            style="font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border: 1px solid #CBD5E1; border-radius: 6px; background: #FFF; color: #475569; cursor: pointer;">
                                                        <?= (int)$cpn['is_active'] ? 'Deactivate' : 'Activate' ?>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Action -->
                                        <td style="padding: 14px 18px; vertical-align: top; text-align: right; white-space: nowrap;">
                                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                                <a href="<?= url('portal/coupons/' . $encId) ?>" 
                                                   style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; background: #0F172A; color: #FFFFFF; font-size: 0.78rem; font-weight: 700; text-decoration: none;"
                                                   onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#0F172A';">
                                                    <span>View</span>
                                                </a>

                                                <?php if ($canManage): ?>
                                                    <a href="<?= url('portal/coupons/' . $encId . '/edit') ?>" 
                                                       style="display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 8px; background: #F1F5F9; border: 1px solid #CBD5E1; color: #334155; font-size: 0.78rem; font-weight: 700; text-decoration: none;"
                                                       title="Edit Coupon">
                                                        ✎
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                    <div style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 0.82rem;">
                        <div style="color: #64748B;">
                            Showing <?= min($pagination['total'], (($pagination['current_page'] - 1) * $pagination['per_page']) + 1) ?> to <?= min($pagination['total'], $pagination['current_page'] * $pagination['per_page']) ?> of <?= number_format($pagination['total']) ?> coupons
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <?php
                            $prevParams = $filters;
                            $prevParams['page'] = max(1, $pagination['current_page'] - 1);
                            $nextParams = $filters;
                            $nextParams['page'] = min($pagination['total_pages'], $pagination['current_page'] + 1);
                            ?>
                            <?php if ($pagination['has_prev']): ?>
                                <a href="<?= url('portal/coupons?' . http_build_query($prevParams)) ?>" style="padding: 6px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; color: #334155; font-weight: 600; text-decoration: none;">&lsaquo; Prev</a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): 
                                $pageParams = $filters;
                                $pageParams['page'] = $p;
                                $isCurr = ($p === $pagination['current_page']);
                            ?>
                                <?php if ($p === 1 || $p === $pagination['total_pages'] || abs($p - $pagination['current_page']) <= 2): ?>
                                    <a href="<?= url('portal/coupons?' . http_build_query($pageParams)) ?>" 
                                       style="padding: 6px 12px; border-radius: 6px; font-weight: 700; text-decoration: none; <?= $isCurr ? 'background: #059669; color: #FFFFFF;' : 'background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155;' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php elseif (abs($p - $pagination['current_page']) === 3): ?>
                                    <span style="color: #94A3B8; padding: 0 4px;">&hellip;</span>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <a href="<?= url('portal/coupons?' . http_build_query($nextParams)) ?>" style="padding: 6px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; color: #334155; font-weight: 600; text-decoration: none;">Next &rsaquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<script>
function copyCode(code, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '✓';
            btn.style.color = '#059669';
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.style.color = '#94A3B8';
            }, 1500);
        });
    }
}

function toggleCouponStatus(encId, id) {
    const formData = new FormData();
    formData.append('_csrf_token', '<?= csrf_token() ?>');

    fetch('<?= url("portal/coupons") ?>/' + encId + '/status', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Action failed.');
        }
    })
    .catch(err => {
        console.error('Toggle status error', err);
    });
}
</script>
