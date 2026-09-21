<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Header Section -->
            <div class="welcome-banner" style="margin-bottom: 24px;">
                <div>
                    <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Customers & Support</span>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Customer Directory</span>
                    </div>
                    <h1 class="welcome-title">Customer Portfolio & Ledger</h1>
                    <p class="welcome-subtitle">Manage client dossiers, monitor lifetime value (LTV), repeat purchase velocity, and account security.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/customers/create') ?>" class="btn-primary-gradient" style="height: 42px; padding: 0 18px; display: inline-flex; align-items: center; gap: 8px; width: auto;" id="btnCreateCustomer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>+ Add New Customer</span>
                    </a>
                </div>
            </div>

            <!-- Customer KPI Summary Cards -->
            <div class="catalog-kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Clients</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total_customers']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--status-success);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--status-success); text-transform: uppercase;">Active Accounts</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;"><?= number_format($kpis['active_customers']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-blue);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-blue); text-transform: uppercase;">Verified Emails</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-blue); margin-top: 4px;"><?= number_format($kpis['verified_customers']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-purple);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-purple); text-transform: uppercase;">Repeat Buyers</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-purple); margin-top: 4px;"><?= number_format($kpis['repeat_buyers']) ?></div>
                </div>
                <div class="kpi-card" style="padding: 16px; border-left: 4px solid #db2777;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #db2777; text-transform: uppercase;">Lifetime Client Spend</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #db2777; margin-top: 4px;">₹<?= number_format((int)round((float)$kpis['total_revenue'])) ?></div>
                </div>
            </div>

            <!-- Quick Segment Filter Tabs -->
            <?php
            $currentStatus       = $filters['status'] ?? 'all';
            $currentVerification = $filters['verification'] ?? 'all';
            $currentOrdersFilter = $filters['orders_filter'] ?? 'all';
            ?>
            <div class="catalog-tabs-bar" style="margin-bottom: 20px;">
                <?php
                $tabs = [
                    'all'      => ['label' => 'All Clients (' . $kpis['total_customers'] . ')', 'status' => 'all', 'verification' => 'all', 'orders_filter' => 'all'],
                    'active'   => ['label' => 'Active (' . $kpis['active_customers'] . ')', 'status' => 'active', 'verification' => 'all', 'orders_filter' => 'all'],
                    'inactive' => ['label' => 'Suspended (' . ($kpis['total_customers'] - $kpis['active_customers']) . ')', 'status' => 'inactive', 'verification' => 'all', 'orders_filter' => 'all'],
                    'verified' => ['label' => 'Verified (' . $kpis['verified_customers'] . ')', 'status' => 'all', 'verification' => 'verified', 'orders_filter' => 'all'],
                    'repeat'   => ['label' => 'Repeat Buyers (' . $kpis['repeat_buyers'] . ')', 'status' => 'all', 'verification' => 'all', 'orders_filter' => 'repeat'],
                ];
                foreach ($tabs as $tKey => $tData):
                    $tabParams = $filters;
                    $tabParams['status']        = $tData['status'];
                    $tabParams['verification']  = $tData['verification'];
                    $tabParams['orders_filter'] = $tData['orders_filter'];
                    $tabParams['page']          = 1;
                    $tabUrl = url('admin/customers?' . http_build_query($tabParams));
                    $isActive = ($tKey === 'all' && $currentStatus === 'all' && $currentVerification === 'all' && $currentOrdersFilter === 'all')
                             || ($tKey === 'active' && $currentStatus === 'active')
                             || ($tKey === 'inactive' && $currentStatus === 'inactive')
                             || ($tKey === 'verified' && $currentVerification === 'verified')
                             || ($tKey === 'repeat' && $currentOrdersFilter === 'repeat');
                ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($tData['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 18px 22px;">
                <form action="<?= url('admin/customers') ?>" method="GET" class="catalog-filter-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            class="form-input" 
                            placeholder="Search by client name, email, or phone..."
                            style="width: 100%;"
                            id="customerSearchInput"
                        >
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="verification" class="form-input" style="width: 100%;">
                            <option value="all" <?= $filters['verification'] === 'all' ? 'selected' : '' ?>>All Verification</option>
                            <option value="verified" <?= $filters['verification'] === 'verified' ? 'selected' : '' ?>>Verified Only</option>
                            <option value="unverified" <?= $filters['verification'] === 'unverified' ? 'selected' : '' ?>>Unverified</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <select name="orders_filter" class="form-input" style="width: 100%;">
                            <option value="all" <?= $filters['orders_filter'] === 'all' ? 'selected' : '' ?>>All Purchase Tiers</option>
                            <option value="buyers" <?= $filters['orders_filter'] === 'buyers' ? 'selected' : '' ?>>With Orders (>= 1)</option>
                            <option value="repeat" <?= $filters['orders_filter'] === 'repeat' ? 'selected' : '' ?>>Repeat Buyers (>= 2)</option>
                            <option value="no_orders" <?= $filters['orders_filter'] === 'no_orders' ? 'selected' : '' ?>>No Orders Placed</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <select name="sort" class="form-input" style="width: 100%;">
                            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest Registered</option>
                            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest Registered</option>
                            <option value="spend_high" <?= $filters['sort'] === 'spend_high' ? 'selected' : '' ?>>Highest Spend (₹)</option>
                            <option value="orders_high" <?= $filters['sort'] === 'orders_high' ? 'selected' : '' ?>>Most Orders</option>
                            <option value="name_asc" <?= $filters['sort'] === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                            <option value="name_desc" <?= $filters['sort'] === 'name_desc' ? 'selected' : '' ?>>Name: Z to A</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn-primary" style="height: 42px; padding: 0 16px;">
                            Filter
                        </button>
                        <a href="<?= url('admin/customers') ?>" class="btn-secondary" style="height: 42px; padding: 0 14px; display: inline-flex; align-items: center;" title="Reset Filters">
                            ✕
                        </a>
                    </div>
                </form>
            </div>

            <!-- Customers Ledger Table -->
            <div class="card-panel" style="padding: 0; overflow: hidden;">
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-surface-alt, #fafafc); border-bottom: 1px solid var(--border-light); font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted);">
                                <th style="padding: 14px 18px; text-align: left;">Customer</th>
                                <th style="padding: 14px 18px; text-align: left;">Contact</th>
                                <th style="padding: 14px 18px; text-align: center;">Verification</th>
                                <th style="padding: 14px 18px; text-align: center;">Orders</th>
                                <th style="padding: 14px 18px; text-align: right;">Lifetime Spend</th>
                                <th style="padding: 14px 18px; text-align: center;">Status</th>
                                <th style="padding: 14px 18px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="7" style="padding: 48px; text-align: center; color: var(--text-muted);">
                                        <div style="font-size: 2rem; margin-bottom: 12px;">👥</div>
                                        <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">No Customers Found</div>
                                        <div style="font-size: 0.85rem;">No customer records match your filter criteria. Try adjusting filters or search terms.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $c): ?>
                                    <tr style="border-bottom: 1px solid var(--border-light); transition: background 0.15s ease;" class="customer-row" id="customer-row-<?= $c['id'] ?>">
                                        <!-- Customer Name & Avatar -->
                                        <td style="padding: 14px 18px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, rgba(219, 39, 119, 0.15), rgba(140, 48, 245, 0.15)); color: #db2777; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; border: 1px solid rgba(219, 39, 119, 0.2); flex-shrink: 0;">
                                                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <a href="<?= url('admin/customers/' . $c['encrypted_id']) ?>" style="font-weight: 700; color: var(--text-primary); text-decoration: none; display: block; font-size: 0.92rem;" class="customer-name-link">
                                                        <?= htmlspecialchars($c['name']) ?>
                                                    </a>
                                                    <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                                                        Joined <?= date('M d, Y', strtotime($c['created_at'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Contact (Email & Phone) -->
                                        <td style="padding: 14px 18px;">
                                            <div style="font-size: 0.85rem; color: var(--text-primary); font-weight: 500;">
                                                <?= htmlspecialchars($c['email']) ?>
                                            </div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= !empty($c['phone']) ? htmlspecialchars($c['phone']) : '<span style="color: var(--text-placeholder);">No phone registered</span>' ?>
                                            </div>
                                        </td>

                                        <!-- Email Verification Status -->
                                        <td style="padding: 14px 18px; text-align: center;">
                                            <?php if ($c['email_verified']): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: rgba(16, 185, 129, 0.1); color: #059669;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                    Verified
                                                </span>
                                            <?php else: ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: rgba(245, 158, 11, 0.1); color: #d97706;">
                                                    Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Orders Count -->
                                        <td style="padding: 14px 18px; text-align: center;">
                                            <?php if ($c['order_count'] > 0): ?>
                                                <a href="<?= url('admin/customers/' . $c['encrypted_id']) ?>" style="display: inline-flex; align-items: center; gap: 5px; text-decoration: none; padding: 3px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; background: var(--bg-surface-alt, #f3f4f6); color: var(--brand-purple);">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                                    </svg>
                                                    <?= $c['order_count'] ?> <?= $c['order_count'] === 1 ? 'Order' : 'Orders' ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size: 0.78rem; color: var(--text-muted);">0 Orders</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Lifetime Spend -->
                                        <td style="padding: 14px 18px; text-align: right;">
                                            <div style="font-size: 0.95rem; font-weight: 800; color: <?= $c['total_spent'] > 0 ? '#059669' : 'var(--text-muted)' ?>;">
                                                ₹<?= number_format((int)round((float)$c['total_spent'])) ?>
                                            </div>
                                            <?php if ($c['last_order_at']): ?>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                    Last: <?= date('M d, Y', strtotime($c['last_order_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Account Status -->
                                        <td style="padding: 14px 18px; text-align: center;">
                                            <?php if ($c['is_active']): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(16, 185, 129, 0.1); color: #059669;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span>
                                                    Suspended
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td style="padding: 14px 18px; text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <!-- View Profile Dossier -->
                                                <a href="<?= url('admin/customers/' . $c['encrypted_id']) ?>" class="btn-secondary" style="padding: 5px 10px; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 4px;" title="View Luxury Client Dossier">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    <span>View</span>
                                                </a>

                                                <!-- Edit Customer -->
                                                <a href="<?= url('admin/customers/' . $c['encrypted_id'] . '/edit') ?>" class="btn-secondary" style="padding: 5px 9px; font-size: 0.78rem; display: inline-flex; align-items: center;" title="Edit Profile">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </a>

                                                <!-- In-line Status Toggle -->
                                                <form action="<?= url('admin/customers/' . $c['encrypted_id'] . '/status') ?>" method="POST" style="display: inline; margin: 0;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'admin/customers') ?>">
                                                    <button type="submit" class="btn-secondary" style="padding: 5px 9px; font-size: 0.78rem; color: <?= $c['is_active'] ? '#dc2626' : '#059669' ?>;" title="<?= $c['is_active'] ? 'Suspend Account' : 'Activate Account' ?>">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <?php if ($c['is_active']): ?>
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                                            <?php else: ?>
                                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                            <?php endif; ?>
                                                        </svg>
                                                    </button>
                                                </form>

                                                <!-- Delete / Safeguard -->
                                                <?php if ($c['order_count'] == 0): ?>
                                                    <form action="<?= url('admin/customers/' . $c['encrypted_id'] . '/delete') ?>" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to permanently delete this customer profile?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn-secondary" style="padding: 5px 9px; font-size: 0.78rem; color: #dc2626;" title="Delete Customer">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
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
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid var(--border-light); font-size: 0.84rem; color: var(--text-muted); flex-wrap: wrap; gap: 12px;">
                        <div>
                            Showing <?= min($pagination['total_items'], $pagination['offset'] + 1) ?> to <?= min($pagination['total_items'], $pagination['offset'] + count($customers)) ?> of <?= $pagination['total_items'] ?> clients
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <?php if ($pagination['has_prev']): ?>
                                <?php
                                $prevParams = $filters;
                                $prevParams['page'] = $pagination['current_page'] - 1;
                                ?>
                                <a href="<?= url('admin/customers?' . http_build_query($prevParams)) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">&laquo; Previous</a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                <?php
                                $pageParams = $filters;
                                $pageParams['page'] = $p;
                                ?>
                                <a href="<?= url('admin/customers?' . http_build_query($pageParams)) ?>" class="<?= $p === $pagination['current_page'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 6px 12px; font-size: 0.8rem; min-width: 32px; text-align: center;">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <?php
                                $nextParams = $filters;
                                $nextParams['page'] = $pagination['current_page'] + 1;
                                ?>
                                <a href="<?= url('admin/customers?' . http_build_query($nextParams)) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
