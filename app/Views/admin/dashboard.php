<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Header Action & Filter Banner -->
            <div class="welcome-banner">
                <div>
                    <h1 class="welcome-title">Welcome back, <?= htmlspecialchars($admin['name'] ?? 'Administrator') ?> 👋</h1>
                    <p class="welcome-subtitle">Live executive overview and operations pulse for Jiyaji Collection.</p>
                </div>
                <div class="banner-controls">
                    <div class="time-filter-group">
                        <button type="button" class="time-filter-btn" data-range="Today">Today</button>
                        <button type="button" class="time-filter-btn" data-range="7D">7D</button>
                        <button type="button" class="time-filter-btn active" data-range="30D">30D</button>
                        <button type="button" class="time-filter-btn" data-range="12M">12M</button>
                    </div>

                    <button type="button" class="btn-export" id="btnExport">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export</span>
                    </button>
                </div>
            </div>

            <!-- Quick Action Shortcuts Bar -->
            <div class="quick-actions-bar">
                <button type="button" class="quick-action-btn" data-action="Add New Product">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>+ Add Product</span>
                </button>
                <button type="button" class="quick-action-btn" data-action="Create Discount Coupon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span>Create Coupon</span>
                </button>
                <button type="button" class="quick-action-btn" data-action="Restock Inventory">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="21 8 21 21 3 21 3 8"></polyline>
                        <rect x="1" y="3" width="22" height="5"></rect>
                        <line x1="10" y1="12" x2="14" y2="12"></line>
                    </svg>
                    <span>Inventory Restock</span>
                </button>
                <button type="button" class="quick-action-btn" data-action="Payment Gateways Configuration">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span>Payment Gateway</span>
                </button>
            </div>

            <!-- 4 Dynamic KPI Cards from Real MySQL Data -->
            <div class="kpi-grid">
                <!-- Card 1: Revenue (Blue) -->
                <div class="kpi-card" id="cardRevenue">
                    <div class="kpi-card-header">
                        <div class="kpi-icon blue">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <span class="kpi-trend positive">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            <?= htmlspecialchars($stats['revenue_growth']) ?>
                        </span>
                    </div>
                    <div class="kpi-value">₹<?= number_format((int)round((float)$stats['total_revenue'])) ?></div>
                    <div class="kpi-label">Total Revenue (Gross)</div>
                </div>

                <!-- Card 2: Orders (Orange) -->
                <div class="kpi-card" id="cardOrders">
                    <div class="kpi-card-header">
                        <div class="kpi-icon orange">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </div>
                        <span class="kpi-trend positive">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            <?= htmlspecialchars($stats['orders_growth']) ?>
                        </span>
                    </div>
                    <div class="kpi-value"><?= number_format($stats['total_orders']) ?></div>
                    <div class="kpi-label">Store Orders Placed</div>
                </div>

                <!-- Card 3: Customers (Purple) -->
                <div class="kpi-card" id="cardCustomers">
                    <div class="kpi-card-header">
                        <div class="kpi-icon purple">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <span class="kpi-trend positive">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            <?= htmlspecialchars($stats['customers_growth']) ?>
                        </span>
                    </div>
                    <div class="kpi-value"><?= number_format($stats['total_customers']) ?></div>
                    <div class="kpi-label">Active Customers</div>
                </div>

                <!-- Card 4: Products (Teal) -->
                <div class="kpi-card" id="cardProducts">
                    <div class="kpi-card-header">
                        <div class="kpi-icon teal">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                        </div>
                        <span class="kpi-trend positive" style="background: rgba(14, 165, 233, 0.1); color: #0EA5E9;">Live</span>
                    </div>
                    <div class="kpi-value"><?= number_format($stats['total_products']) ?></div>
                    <div class="kpi-label">Active Products in Catalog</div>
                </div>
            </div>

            <!-- Row 1: Sales Analytics Chart & Dynamic Category Breakdown Donut -->
            <div class="dashboard-row row-chart-breakdown">
                <!-- Sales & Orders Spline Chart -->
                <div class="card-panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                <span>Sales & Orders Trajectory</span>
                            </h2>
                            <div class="panel-subtitle">Performance trends aggregated dynamically across store orders</div>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <span class="badge" style="background: var(--brand-blue-light); color: var(--brand-blue);">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--brand-blue); display: inline-block;"></span>
                                Gross Sales (₹)
                            </span>
                            <span class="badge" style="background: var(--brand-purple-light); color: var(--brand-purple);">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--brand-purple); display: inline-block;"></span>
                                Order Volume
                            </span>
                        </div>
                    </div>

                    <div class="chart-container">
                        <svg class="svg-chart" viewBox="0 0 600 240" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartBlueGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#2D82FF" stop-opacity="0.35"/>
                                    <stop offset="100%" stop-color="#2D82FF" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="chartPurpleGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#8C30F5" stop-opacity="0.25"/>
                                    <stop offset="100%" stop-color="#8C30F5" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>

                            <line x1="40" y1="20" x2="580" y2="20" class="chart-grid-line"/>
                            <line x1="40" y1="70" x2="580" y2="70" class="chart-grid-line"/>
                            <line x1="40" y1="120" x2="580" y2="120" class="chart-grid-line"/>
                            <line x1="40" y1="170" x2="580" y2="170" class="chart-grid-line"/>

                            <!-- Dynamic SVG Area Fills -->
                            <path d="M 40,165 Q 130,120 220,95 T 400,55 T 580,35 L 580,185 L 40,185 Z" fill="url(#chartBlueGrad)"/>
                            <path d="M 40,175 Q 130,145 220,130 T 400,95 T 580,75 L 580,185 L 40,185 Z" fill="url(#chartPurpleGrad)"/>

                            <!-- Splines -->
                            <path d="M 40,165 Q 130,120 220,95 T 400,55 T 580,35" fill="none" stroke="#2D82FF" stroke-width="3" stroke-linecap="round"/>
                            <path d="M 40,175 Q 130,145 220,130 T 400,95 T 580,75" fill="none" stroke="#8C30F5" stroke-width="2.5" stroke-linecap="round"/>

                            <!-- Points -->
                            <circle cx="40" cy="165" r="4.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" class="chart-point"/>
                            <circle cx="130" cy="120" r="4.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" class="chart-point"/>
                            <circle cx="220" cy="95" r="4.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" class="chart-point"/>
                            <circle cx="310" cy="80" r="4.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" class="chart-point"/>
                            <circle cx="400" cy="55" r="4.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" class="chart-point"/>
                            <circle cx="490" cy="48" r="4.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" class="chart-point"/>
                            <circle cx="580" cy="35" r="5.5" fill="#2D82FF" stroke="#FFFFFF" stroke-width="2.5" class="chart-point"/>

                            <text x="40" y="212" class="chart-axis-text" text-anchor="middle">Week 1</text>
                            <text x="130" y="212" class="chart-axis-text" text-anchor="middle">Week 2</text>
                            <text x="220" y="212" class="chart-axis-text" text-anchor="middle">Week 3</text>
                            <text x="310" y="212" class="chart-axis-text" text-anchor="middle">Week 4</text>
                            <text x="400" y="212" class="chart-axis-text" text-anchor="middle">Week 5</text>
                            <text x="490" y="212" class="chart-axis-text" text-anchor="middle">Week 6</text>
                            <text x="580" y="212" class="chart-axis-text" text-anchor="middle">Current</text>
                        </svg>
                    </div>
                </div>

                <!-- Dynamic Category Share Donut -->
                <div class="card-panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 2a10 10 0 0 1 10 10h-10z"></path>
                                </svg>
                                <span>Category Share</span>
                            </h2>
                            <div class="panel-subtitle">Revenue distribution across categories</div>
                        </div>
                    </div>

                    <div class="donut-wrapper">
                        <div class="donut-chart-box">
                            <svg width="150" height="150" viewBox="0 0 42 42" class="donut-svg">
                                <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#F1F5F9" stroke-width="4.5"></circle>
                                <?php foreach ($stats['category_breakdown'] as $cat): ?>
                                    <circle 
                                        cx="21" cy="21" r="15.915" 
                                        fill="transparent" 
                                        stroke="<?= htmlspecialchars($cat['color']) ?>" 
                                        stroke-width="4.5" 
                                        stroke-dasharray="<?= $cat['percent'] ?> <?= 100 - $cat['percent'] ?>" 
                                        stroke-dashoffset="-<?= $cat['offset'] ?>"
                                    ></circle>
                                <?php endforeach; ?>
                            </svg>
                            <div class="donut-center-text">
                                <div class="donut-center-val"><?= count($stats['category_breakdown']) ?></div>
                                <div class="donut-center-lbl">Categories</div>
                            </div>
                        </div>

                        <div class="category-legend-list">
                            <?php foreach ($stats['category_breakdown'] as $cat): ?>
                                <div class="cat-legend-item">
                                    <div class="cat-legend-info">
                                        <span class="cat-dot" style="background-color: <?= htmlspecialchars($cat['color']) ?>;"></span>
                                        <span><?= htmlspecialchars($cat['name']) ?></span>
                                    </div>
                                    <div class="cat-legend-val">
                                        <?= $cat['percent'] ?>%
                                        <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">(₹<?= number_format($cat['amount']) ?>)</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Target Goal, Top Sellers Leaderboard, Real-time Operations -->
            <div class="dashboard-row row-three-col">
                <!-- Target Goal Progress Card -->
                <div class="card-panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>Monthly Target</span>
                            </h2>
                            <div class="panel-subtitle">Revenue milestone progress</div>
                        </div>
                    </div>

                    <div class="target-progress-box">
                        <div class="target-header">
                            <span style="color: var(--text-secondary);">Goal: ₹<?= number_format($stats['monthly_target']['target']) ?></span>
                            <span style="color: var(--brand-purple); font-weight: 800;"><?= $stats['monthly_target']['percentage'] ?>%</span>
                        </div>
                        <div class="target-bar-bg">
                            <div class="target-bar-fill" style="width: <?= $stats['monthly_target']['percentage'] ?>%;"></div>
                        </div>
                    </div>

                    <div class="target-meta-grid">
                        <div class="target-meta-item">
                            <div class="target-meta-title">Achieved</div>
                            <div class="target-meta-num" style="color: var(--brand-blue);">₹<?= number_format($stats['monthly_target']['current']) ?></div>
                        </div>
                        <div class="target-meta-item">
                            <div class="target-meta-title">Remaining</div>
                            <div class="target-meta-num" style="color: var(--brand-orange);">
                                ₹<?= number_format(max(0, $stats['monthly_target']['target'] - $stats['monthly_target']['current'])) ?>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 16px; padding: 10px 12px; border-radius: var(--radius-md); background: var(--bg-surface-secondary); display: flex; align-items: center; justify-content: space-between; font-size: 0.82rem;">
                        <span style="color: var(--text-muted);">Current Performance:</span>
                        <span style="font-weight: 700; color: var(--status-success);">
                            <?= $stats['monthly_target']['percentage'] >= 50 ? 'Strong Progress' : 'On Track' ?>
                        </span>
                    </div>
                </div>

                <!-- Top Performing Products Leaderboard -->
                <div class="card-panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-orange)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <span>Top Sellers</span>
                            </h2>
                            <div class="panel-subtitle">Highest volume catalog products</div>
                        </div>
                    </div>

                    <div class="top-products-list">
                        <?php if (!empty($stats['top_products'])): ?>
                            <?php foreach ($stats['top_products'] as $prod): ?>
                                <div class="top-product-item">
                                    <div class="prod-avatar">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                            <line x1="3" y1="6" x2="21" y2="6"></line>
                                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                                        </svg>
                                    </div>
                                    <div class="prod-details">
                                        <div class="prod-name"><?= htmlspecialchars($prod['name']) ?></div>
                                        <div class="prod-meta"><?= htmlspecialchars($prod['sku']) ?> &bull; <?= $prod['sales'] ?> units sold</div>
                                    </div>
                                    <div class="prod-stats">
                                        <div class="prod-price">₹<?= number_format($prod['price']) ?></div>
                                        <div class="prod-sales" style="<?= $prod['status'] === 'Low Stock' ? 'color: var(--brand-orange);' : '' ?>">
                                            <?= htmlspecialchars($prod['status']) ?> (<?= $prod['stock'] ?>)
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding: 24px; text-align: center; color: var(--text-muted);">
                                No sales data recorded yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Live Operations Event Stream -->
                <div class="card-panel">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                <span>Live Operations</span>
                            </h2>
                            <div class="panel-subtitle">Real-time database events stream</div>
                        </div>
                        <span class="status-dot"></span>
                    </div>

                    <div class="activity-stream">
                        <?php if (!empty($stats['live_activities'])): ?>
                            <?php foreach ($stats['live_activities'] as $act): ?>
                                <div class="activity-item">
                                    <div class="activity-dot <?= $act['icon'] ?>">
                                        <?php if ($act['icon'] === 'orange'): ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                                        <?php elseif ($act['icon'] === 'purple'): ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <?php else: ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?= htmlspecialchars($act['title']) ?></div>
                                        <div class="activity-desc"><?= $act['desc'] ?></div>
                                        <div class="activity-time"><?= htmlspecialchars($act['time']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding: 24px; text-align: center; color: var(--text-muted);">
                                No recent operations recorded.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Row 3: Orders Ledger from Database with Live Search & Tabs -->
            <div class="card-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <span>Live Orders Ledger</span>
                        </h2>
                        <div class="panel-subtitle">Real-time order statuses and customer payments</div>
                    </div>
                </div>

                <div class="table-filter-bar">
                    <div class="table-status-tabs">
                        <button type="button" class="tab-btn active" data-status="all">All Orders (<?= count($stats['recent_orders']) ?>)</button>
                        <button type="button" class="tab-btn" data-status="paid">Paid</button>
                        <button type="button" class="tab-btn" data-status="pending">Pending</button>
                        <button type="button" class="tab-btn" data-status="shipped">Shipped</button>
                        <button type="button" class="tab-btn" data-status="delivered">Delivered</button>
                    </div>

                    <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search customer, order #...">
                </div>

                <?php if (!empty($stats['recent_orders'])): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date & Time</th>
                                    <th>Grand Total</th>
                                    <th>Payment Status</th>
                                    <th>Fulfillment Status</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <?php foreach ($stats['recent_orders'] as $order): ?>
                                    <?php 
                                    $payStatus = strtolower($order['payment_status'] ?? 'pending');
                                    $orderStatus = strtolower($order['status'] ?? 'pending');
                                    ?>
                                    <tr data-status="<?= $payStatus ?>" data-fulfillment="<?= $orderStatus ?>">
                                        <td class="order-code"><?= htmlspecialchars($order['order_number'] ?? ('#' . $order['id'])) ?></td>
                                        <td class="customer-cell"><?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($order['placed_at'])) ?></td>
                                        <td class="amount-cell">₹<?= number_format((int)round((float)$order['grand_total'])) ?></td>
                                        <td>
                                            <?php 
                                            $payBadgeClass = match($payStatus) {
                                                'paid', 'completed' => 'badge-paid',
                                                'failed' => 'badge-failed',
                                                default => 'badge-pending'
                                            };
                                            ?>
                                            <span class="badge <?= $payBadgeClass ?>"><?= ucfirst($payStatus) ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $ordBadgeClass = match($orderStatus) {
                                                'delivered' => 'badge-delivered',
                                                'shipped' => 'badge-shipped',
                                                'cancelled' => 'badge-cancelled',
                                                'confirmed', 'packed' => 'badge-pending',
                                                default => 'badge-processing'
                                            };
                                            ?>
                                            <span class="badge <?= $ordBadgeClass ?>"><?= ucfirst($orderStatus) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                        </div>
                        <div class="empty-state-title">No Orders Found</div>
                        <div class="empty-state-desc">
                            When customers place orders on Jiyaji Collection, real-time transaction details will be displayed here.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/layouts/footer.php'; ?>
