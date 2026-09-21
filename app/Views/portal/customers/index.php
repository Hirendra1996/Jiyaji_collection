<?php
$title      = $title ?? 'Customer Directory | Jiyaji LX Staff Portal';
$customers  = $customers ?? [];
$filters    = $filters ?? [];
$pagination = $pagination ?? ['has_prev' => false, 'has_next' => false, 'current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
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
                <a href="<?= url('portal/dashboard') ?>" style="color: #0284C7; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Customer Directory</span>
            </div>

            <!-- Hero Banner -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #075985 55%, #0EA5E9 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.25);">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #BAE6FD; margin-bottom: 8px; display: inline-block;">
                        CRM &bull; Shopper Profiles &bull; Lifetime Value Intelligence
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">Customer Directory</h1>
                    <p style="font-size: 0.88rem; color: #BAE6FD; margin: 0;">
                        Complete shopper ledger — profiles, lifetime spend, order history, address books, and account status.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canManage): ?>
                        <a href="<?= url('portal/customers/create') ?>" style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.28); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Customer
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('portal/customers/export?' . http_build_query(array_filter($filters, fn($v) => $v !== '' && $v !== 'all'))) ?>" style="background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.25); color: #FFFFFF; font-weight: 700; font-size: 0.82rem; padding: 9px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px;" onmouseover="this.style.background='rgba(255,255,255,0.22)';" onmouseout="this.style.background='rgba(255,255,255,0.14)';">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- KPI Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <?php
                $kpiCards = [
                    ['label' => 'Total Shoppers', 'value' => number_format($kpis['total_customers'] ?? 0), 'sub' => 'Registered accounts', 'color' => '#0284C7', 'border' => '#0EA5E9'],
                    ['label' => 'Active Accounts', 'value' => number_format($kpis['active_customers'] ?? 0), 'sub' => 'Enabled shoppers', 'color' => '#10B981', 'border' => '#10B981'],
                    ['label' => 'Email Verified', 'value' => number_format($kpis['verified_customers'] ?? 0), 'sub' => 'Confirmed identities', 'color' => '#6366F1', 'border' => '#6366F1'],
                    ['label' => 'Repeat Buyers', 'value' => number_format($kpis['repeat_buyers'] ?? 0), 'sub' => '2+ orders placed', 'color' => '#F59E0B', 'border' => '#F59E0B'],
                    ['label' => 'Total Revenue', 'value' => '₹' . number_format($kpis['total_revenue'] ?? 0), 'sub' => 'Customer lifetime value', 'color' => '#DC2626', 'border' => '#EF4444'],
                ];
                foreach ($kpiCards as $card): ?>
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid <?= $card['border'] ?>; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 0.74rem; font-weight: 700; color: <?= $card['color'] ?>; text-transform: uppercase; letter-spacing: 0.05em;"><?= $card['label'] ?></div>
                        <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= $card['value'] ?></div>
                        <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;"><?= $card['sub'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Filters -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/customers') ?>" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 2; min-width: 240px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                            placeholder="Search by name, email, phone..."
                            style="width: 100%; height: 40px; padding: 0 14px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                            onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>
                    <div style="flex: 1; min-width: 155px;">
                        <select name="status" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['status'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Statuses</option>
                            <option value="active"   <?= (($filters['status'] ?? '') === 'active')   ? 'selected' : '' ?>>Active Only</option>
                            <option value="inactive" <?= (($filters['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 155px;">
                        <select name="verification" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['verification'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Verification</option>
                            <option value="verified"   <?= (($filters['verification'] ?? '') === 'verified')   ? 'selected' : '' ?>>Verified Email</option>
                            <option value="unverified" <?= (($filters['verification'] ?? '') === 'unverified') ? 'selected' : '' ?>>Unverified</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 155px;">
                        <select name="orders_filter" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all"      <?= (($filters['orders_filter'] ?? 'all') === 'all')      ? 'selected' : '' ?>>All Buyers</option>
                            <option value="buyers"   <?= (($filters['orders_filter'] ?? '') === 'buyers')   ? 'selected' : '' ?>>Has Orders</option>
                            <option value="repeat"   <?= (($filters['orders_filter'] ?? '') === 'repeat')   ? 'selected' : '' ?>>Repeat Buyers</option>
                            <option value="no_orders"<?= (($filters['orders_filter'] ?? '') === 'no_orders')? 'selected' : '' ?>>No Orders Yet</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 155px;">
                        <select name="sort" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="newest"      <?= (($filters['sort'] ?? '') === 'newest')      ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest"      <?= (($filters['sort'] ?? '') === 'oldest')      ? 'selected' : '' ?>>Oldest First</option>
                            <option value="name_asc"    <?= (($filters['sort'] ?? '') === 'name_asc')    ? 'selected' : '' ?>>Name: A → Z</option>
                            <option value="name_desc"   <?= (($filters['sort'] ?? '') === 'name_desc')   ? 'selected' : '' ?>>Name: Z → A</option>
                            <option value="spend_high"  <?= (($filters['sort'] ?? '') === 'spend_high')  ? 'selected' : '' ?>>Highest Spender</option>
                            <option value="orders_high" <?= (($filters['sort'] ?? '') === 'orders_high') ? 'selected' : '' ?>>Most Orders</option>
                        </select>
                    </div>
                    <button type="submit" style="height: 40px; padding: 0 20px; background: #0284C7; color: #FFFFFF; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer;">Apply</button>
                    <a href="<?= url('portal/customers') ?>" style="height: 40px; padding: 0 16px; background: #F1F5F9; color: #475569; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center;">Clear</a>
                </form>
            </div>

            <!-- Customer Table -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 24px;">
                <div style="padding: 16px 22px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 1rem; font-weight: 800; color: #0F172A;">Shopper Ledger</div>
                    <div style="font-size: 0.8rem; color: #64748B;">
                        <?= number_format($pagination['total_items'] ?? 0) ?> customers
                        &mdash; Page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['total_pages'] ?? 1 ?>
                    </div>
                </div>

                <?php if (empty($customers)): ?>
                    <div style="padding: 56px 24px; text-align: center;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #F0F9FF; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; border: 2px solid #BAE6FD;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0284C7" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div style="font-weight: 700; color: #475569; font-size: 1rem; margin-bottom: 4px;">No customers match your filters</div>
                        <div style="font-size: 0.82rem; color: #94A3B8;">Try clearing your filters or <a href="<?= url('portal/customers/create') ?>" style="color: #0284C7; text-decoration: none; font-weight: 700;">create a new customer profile</a>.</div>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.86rem;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                                    <th style="text-align: left; padding: 12px 20px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Shopper Profile</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Contact</th>
                                    <th style="text-align: center; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Status</th>
                                    <th style="text-align: center; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Orders</th>
                                    <th style="text-align: right; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Lifetime Spend</th>
                                    <th style="text-align: left; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">Last Order</th>
                                    <th style="text-align: center; padding: 12px 14px; font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.04em;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $c):
                                    $isActive   = (bool)(int)$c['is_active'];
                                    $isVerified = (bool)(int)$c['email_verified'];
                                    $orderCount = (int)$c['order_count'];
                                    $totalSpent = (int)$c['total_spent'];
                                    $encId      = $c['encrypted_id'] ?? '';
                                    $initials   = strtoupper(mb_substr($c['name'] ?? 'C', 0, 1));
                                    $avatar_colors = ['#0284C7', '#7C3AED', '#059669', '#DC2626', '#D97706', '#0891B2', '#9333EA'];
                                    $avatarColor = $avatar_colors[$c['id'] % count($avatar_colors)];
                                ?>
                                <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.12s;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">

                                    <!-- Profile -->
                                    <td style="padding: 14px 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <!-- Avatar -->
                                            <div style="width: 42px; height: 42px; border-radius: 50%; background: <?= $avatarColor ?>22; border: 2px solid <?= $avatarColor ?>44; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 800; font-size: 1rem; color: <?= $avatarColor ?>;">
                                                <?= htmlspecialchars($initials) ?>
                                            </div>
                                            <div>
                                                <a href="<?= url('portal/customers/' . $encId) ?>" style="font-weight: 700; font-size: 0.9rem; color: #0F172A; text-decoration: none; display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" onmouseover="this.style.color='#0284C7';" onmouseout="this.style.color='#0F172A';">
                                                    <?= htmlspecialchars($c['name']) ?>
                                                </a>
                                                <div style="font-size: 0.75rem; color: #64748B; margin-top: 2px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?= htmlspecialchars($c['email']) ?>
                                                </div>
                                                <?php if ($isVerified): ?>
                                                    <span style="font-size: 0.68rem; font-weight: 700; color: #059669; background: #DCFCE7; padding: 1px 6px; border-radius: 999px; margin-top: 2px; display: inline-block;">✓ Verified</span>
                                                <?php else: ?>
                                                    <span style="font-size: 0.68rem; font-weight: 700; color: #92400E; background: #FEF3C7; padding: 1px 6px; border-radius: 999px; margin-top: 2px; display: inline-block;">Unverified</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td style="padding: 14px 14px;">
                                        <?php if (!empty($c['phone'])): ?>
                                            <div style="font-size: 0.83rem; color: #374151; font-weight: 600;"><?= htmlspecialchars($c['phone']) ?></div>
                                        <?php else: ?>
                                            <div style="font-size: 0.78rem; color: #CBD5E1; font-style: italic;">No phone</div>
                                        <?php endif; ?>
                                        <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 3px;">
                                            Joined <?= !empty($c['created_at']) ? date('M Y', strtotime($c['created_at'])) : '—' ?>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td style="padding: 14px 14px; text-align: center;">
                                        <?php if ($isActive): ?>
                                            <span style="background: #DCFCE7; color: #16A34A; padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 700;">Active</span>
                                        <?php else: ?>
                                            <span style="background: #FEE2E2; color: #DC2626; padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 700;">Suspended</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Orders count -->
                                    <td style="padding: 14px 14px; text-align: center;">
                                        <div style="font-size: 1.1rem; font-weight: 800; color: <?= $orderCount > 0 ? '#0284C7' : '#94A3B8' ?>;"><?= $orderCount ?></div>
                                        <?php if ($orderCount > 1): ?>
                                            <div style="font-size: 0.68rem; color: #F59E0B; font-weight: 700;">Repeat</div>
                                        <?php elseif ($orderCount === 0): ?>
                                            <div style="font-size: 0.68rem; color: #CBD5E1;">none</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Lifetime spend -->
                                    <td style="padding: 14px 14px; text-align: right;">
                                        <div style="font-size: 1rem; font-weight: 800; color: <?= $totalSpent > 0 ? '#0F172A' : '#CBD5E1' ?>;">
                                            ₹<?= number_format($totalSpent) ?>
                                        </div>
                                        <?php if ($orderCount > 1): ?>
                                            <div style="font-size: 0.7rem; color: #94A3B8; margin-top: 1px;">avg ₹<?= number_format($orderCount > 0 ? (int)round($totalSpent / $orderCount) : 0) ?>/order</div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Last Order -->
                                    <td style="padding: 14px 14px; white-space: nowrap;">
                                        <?php if (!empty($c['last_order_at'])): ?>
                                            <div style="font-size: 0.83rem; color: #374151; font-weight: 600;"><?= date('M d, Y', strtotime($c['last_order_at'])) ?></div>
                                            <div style="font-size: 0.72rem; color: #94A3B8;"><?= date('h:i A', strtotime($c['last_order_at'])) ?></div>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: #CBD5E1;">Never ordered</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Actions -->
                                    <td style="padding: 14px 14px; text-align: center; white-space: nowrap;">
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                            <a href="<?= url('portal/customers/' . $encId) ?>"
                                                style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; border-radius: 7px; padding: 5px 10px; font-size: 0.76rem; font-weight: 700; text-decoration: none; white-space: nowrap;"
                                                onmouseover="this.style.background='#DBEAFE';" onmouseout="this.style.background='#EFF6FF';">
                                                View
                                            </a>
                                            <?php if ($canManage): ?>
                                                <a href="<?= url('portal/customers/' . $encId . '/edit') ?>"
                                                    style="background: #FFF7ED; color: #C2410C; border: 1px solid #FED7AA; border-radius: 7px; padding: 5px 10px; font-size: 0.76rem; font-weight: 700; text-decoration: none; white-space: nowrap;"
                                                    onmouseover="this.style.background='#FFEDD5';" onmouseout="this.style.background='#FFF7ED';">
                                                    Edit
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
                    <div style="font-size: 0.82rem; color: #64748B;">
                        Page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong>
                        &mdash; <?= number_format($pagination['total_items']) ?> total customers
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <?php if ($pagination['has_prev']): ?>
                            <?php $prevParams = $filters; $prevParams['page'] = $pagination['current_page'] - 1; ?>
                            <a href="<?= url('portal/customers?' . http_build_query($prevParams)) ?>" style="padding: 7px 16px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 0.84rem; font-weight: 600; color: #374151; text-decoration: none;">&larr; Previous</a>
                        <?php endif; ?>
                        <?php if ($pagination['has_next']): ?>
                            <?php $nextParams = $filters; $nextParams['page'] = $pagination['current_page'] + 1; ?>
                            <a href="<?= url('portal/customers?' . http_build_query($nextParams)) ?>" style="padding: 7px 16px; background: #0284C7; border: 1px solid #0284C7; border-radius: 8px; font-size: 0.84rem; font-weight: 700; color: #FFFFFF; text-decoration: none;">Next &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
