<?php
include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$curPeriod   = $filters['period'] ?? '30d';
$curInterval = $filters['interval'] ?? 'daily';
$curTab      = $activeTab ?? 'overview';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">

            <!-- ============================================================ -->
            <!-- PAGE HEADER & BREADCRUMB                                     -->
            <!-- ============================================================ -->
            <div class="welcome-banner" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color:var(--brand-blue); text-decoration:none;">Dashboard</a>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span>Reports &amp; Finance</span>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Sales Analytics</span>
                    </div>
                    <h1 class="welcome-title">Sales Analytics &amp; Financial Reporting</h1>
                    <p class="welcome-subtitle">Comprehensive executive reporting on store revenue trajectory, order volume dynamics, category market share, regional demand distribution, and data exports.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Recalculate / Sync Summary Form -->
                    <form method="POST" action="<?= url('admin/sales-analytics/sync') ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit"
                                style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:var(--transition);"
                                onmouseover="this.style.borderColor='var(--status-success)'; this.style.color='var(--status-success)'"
                                onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'"
                                title="Recalculate and synchronize daily sales summary aggregation">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                            Sync Ledger
                        </button>
                    </form>

                    <!-- Export Dropdown -->
                    <div style="position:relative; display:inline-block;" id="salesExportDropdown">
                        <button type="button" onclick="toggleSalesExportMenu(event)"
                                style="display:inline-flex; align-items:center; gap:8px; background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); border:none; cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                                onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.opacity='1'; this.style.transform=''">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export Financials
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="salesExportMenu" style="display:none; position:absolute; right:0; top:calc(100% + 6px); background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:0 10px 25px -5px rgba(0,0,0,0.12); width:230px; z-index:100; overflow:hidden;">
                            <a href="<?= url('admin/sales-analytics/export?export_type=daily_summary&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--text-primary); text-decoration:none; border-bottom:1px solid var(--border-color-light);" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--status-success)" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                Daily Sales Summary
                            </a>
                            <a href="<?= url('admin/sales-analytics/export?export_type=orders_ledger&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--text-primary); text-decoration:none; border-bottom:1px solid var(--border-color-light);" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Orders Financial Ledger
                            </a>
                            <a href="<?= url('admin/sales-analytics/export?export_type=products&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--text-primary); text-decoration:none; border-bottom:1px solid var(--border-color-light);" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                Products Performance
                            </a>
                            <a href="<?= url('admin/sales-analytics/export?export_type=categories&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--text-primary); text-decoration:none;" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                Category Share Breakdown
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- PERIOD & INTERVAL CONTROLS                                    -->
            <!-- ============================================================ -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <!-- Period presets -->
                <div style="display:flex; gap:6px; background:var(--bg-surface-secondary); padding:4px; border-radius:var(--radius-md); border:1px solid var(--border-color); flex-wrap:wrap;">
                    <?php
                    $periods = [
                        'today'      => 'Today',
                        '7d'         => 'Last 7 Days',
                        '30d'        => 'Last 30 Days',
                        '90d'        => 'Last 90 Days',
                        'this_month' => 'This Month',
                        'year'       => 'Year 2026',
                        'all'        => 'All Time',
                    ];
                    foreach ($periods as $pKey => $pLabel):
                        $isActive = ($curPeriod === $pKey);
                    ?>
                        <a href="<?= url('admin/sales-analytics?period=' . $pKey . '&interval=' . $curInterval . '&tab=' . $curTab) ?>"
                           style="padding:6px 14px; font-size:0.82rem; font-weight:<?= $isActive ? '700' : '500' ?>; border-radius:var(--radius-sm); text-decoration:none; transition:var(--transition); <?= $isActive ? 'background:var(--bg-surface); color:var(--status-success); box-shadow:0 1px 3px rgba(0,0,0,0.08);' : 'color:var(--text-secondary);' ?>">
                            <?= $pLabel ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Interval Selector for Trajectory Chart -->
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Grouping:</span>
                    <div style="display:flex; gap:4px; background:var(--bg-surface-secondary); padding:3px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                        <a href="<?= url('admin/sales-analytics?period=' . $curPeriod . '&interval=daily&tab=' . $curTab) ?>" style="padding:4px 10px; font-size:0.75rem; font-weight:<?= $curInterval === 'daily' ? '700' : '500' ?>; text-decoration:none; border-radius:4px; <?= $curInterval === 'daily' ? 'background:var(--bg-surface); color:var(--brand-blue); box-shadow:0 1px 2px rgba(0,0,0,0.05);' : 'color:var(--text-secondary);' ?>">Daily</a>
                        <a href="<?= url('admin/sales-analytics?period=' . $curPeriod . '&interval=weekly&tab=' . $curTab) ?>" style="padding:4px 10px; font-size:0.75rem; font-weight:<?= $curInterval === 'weekly' ? '700' : '500' ?>; text-decoration:none; border-radius:4px; <?= $curInterval === 'weekly' ? 'background:var(--bg-surface); color:var(--brand-blue); box-shadow:0 1px 2px rgba(0,0,0,0.05);' : 'color:var(--text-secondary);' ?>">Weekly</a>
                        <a href="<?= url('admin/sales-analytics?period=' . $curPeriod . '&interval=monthly&tab=' . $curTab) ?>" style="padding:4px 10px; font-size:0.75rem; font-weight:<?= $curInterval === 'monthly' ? '700' : '500' ?>; text-decoration:none; border-radius:4px; <?= $curInterval === 'monthly' ? 'background:var(--bg-surface); color:var(--brand-blue); box-shadow:0 1px 2px rgba(0,0,0,0.05);' : 'color:var(--text-secondary);' ?>">Monthly</a>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 5 EXECUTIVE FINANCIAL KPI CARDS                              -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px;">
                <!-- 1. Gross Revenue -->
                <div class="kpi-card" style="border-left:3px solid var(--status-success);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon teal" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <?php if ($kpis['revenue_growth'] != 0): ?>
                            <span style="font-size:0.7rem; font-weight:700; background:<?= $kpis['revenue_growth'] > 0 ? 'var(--status-success-bg)' : 'var(--brand-orange-light)' ?>; color:<?= $kpis['revenue_growth'] > 0 ? 'var(--status-success)' : 'var(--brand-orange)' ?>; padding:2px 8px; border-radius:var(--radius-full);">
                                <?= $kpis['revenue_growth'] > 0 ? '+' : '' ?><?= $kpis['revenue_growth'] ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-value" style="color:var(--status-success); font-size:1.6rem;"><?= currency($kpis['gross_revenue']) ?></div>
                    <div class="kpi-label">Gross Revenue</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Today: <?= currency($kpis['today_revenue']) ?> (<?= $kpis['today_orders'] ?> orders)
                    </div>
                </div>

                <!-- 2. Net Revenue -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-purple);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon purple" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                        </div>
                        <span style="font-size:0.7rem; font-weight:700; background:rgba(140,48,245,0.1); color:var(--brand-purple); padding:2px 8px; border-radius:var(--radius-full);">
                            Post-Discount
                        </span>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-purple); font-size:1.6rem;"><?= currency($kpis['net_revenue']) ?></div>
                    <div class="kpi-label">Net Sales Revenue</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Discounts deducted: <?= currency($kpis['total_discounts']) ?>
                    </div>
                </div>

                <!-- 3. Total Orders & Paid Conversion -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-blue);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon blue" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><rect x="6" y="2" width="12" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        </div>
                        <span style="font-size:0.7rem; font-weight:700; background:rgba(45,130,255,0.1); color:var(--brand-blue); padding:2px 8px; border-radius:var(--radius-full);">
                            <?= $kpis['paid_rate'] ?>% Paid
                        </span>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-blue);"><?= number_format($kpis['total_orders']) ?></div>
                    <div class="kpi-label">Total Store Orders</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        <?= number_format($kpis['paid_orders']) ?> paid &bull; <?= number_format($kpis['delivered_orders']) ?> delivered
                    </div>
                </div>

                <!-- 4. Average Order Value (AOV) -->
                <div class="kpi-card" style="border-left:3px solid #06b6d4;">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); width:40px; height:40px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:#0891b2;"><?= currency($kpis['avg_order_value']) ?></div>
                    <div class="kpi-label">Average Order Value (AOV)</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        <?= $kpis['units_per_order'] ?> units per transaction
                    </div>
                </div>

                <!-- 5. Total Discounts Issued -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-orange);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon orange" style="width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-orange);"><?= currency($kpis['total_discounts']) ?></div>
                    <div class="kpi-label">Promotional Discounts</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Tax collected: <?= currency($kpis['total_tax']) ?>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SALES TRAJECTORY INTERACTIVE SVG SPLINE CHART                -->
            <!-- ============================================================ -->
            <div class="card-panel" style="margin-bottom:24px;">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--status-success)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            <span>Revenue Trajectory &amp; Order Volume</span>
                        </h2>
                        <div class="panel-subtitle">Financial performance trajectory plotted in <?= htmlspecialchars($curInterval) ?> intervals</div>
                    </div>
                    <div style="display:flex; gap:16px; align-items:center;">
                        <span class="badge" style="background:rgba(16,185,129,0.1); color:var(--status-success); font-weight:700;">
                            <span style="width:8px; height:8px; border-radius:50%; background:var(--status-success); display:inline-block; margin-right:6px;"></span>
                            Gross Revenue (₹)
                        </span>
                        <span class="badge" style="background:rgba(45,130,255,0.1); color:var(--brand-blue); font-weight:700;">
                            <span style="width:8px; height:8px; border-radius:50%; background:var(--brand-blue); display:inline-block; margin-right:6px;"></span>
                            Orders Volume
                        </span>
                    </div>
                </div>

                <?php
                // Build dynamic SVG geometry for the dual sales trend chart
                $trendPoints = !empty($salesTrend) ? $salesTrend : [];
                $pointCount  = count($trendPoints);

                $maxRev = 1;
                $maxOrd = 1;
                foreach ($trendPoints as $pt) {
                    if ($pt['revenue'] > $maxRev) $maxRev = $pt['revenue'];
                    if ($pt['orders'] > $maxOrd)  $maxOrd  = $pt['orders'];
                }
                $chartRevMax = (int)ceil($maxRev * 1.25);
                if ($chartRevMax < 1000) $chartRevMax = 10000;

                $chartOrdMax = (int)ceil($maxOrd * 1.3);
                if ($chartOrdMax < 5) $chartOrdMax = 5;

                $svgW = 760;
                $svgH = 240;
                $padL = 60;
                $padR = 40;
                $padT = 20;
                $padB = 35;

                $usableW = $svgW - $padL - $padR;
                $usableH = $svgH - $padT - $padB;

                $coordsRev = [];
                $coordsOrd = [];

                for ($i = 0; $i < $pointCount; $i++) {
                    $x = $pointCount > 1 ? $padL + ($i * ($usableW / ($pointCount - 1))) : $padL + ($usableW / 2);
                    $yRev = $padT + $usableH - (($trendPoints[$i]['revenue'] / $chartRevMax) * $usableH);
                    $yOrd = $padT + $usableH - (($trendPoints[$i]['orders'] / $chartOrdMax) * $usableH);

                    $coordsRev[] = ['x' => round($x, 1), 'y' => round($yRev, 1), 'val' => $trendPoints[$i]['revenue'], 'item' => $trendPoints[$i]];
                    $coordsOrd[] = ['x' => round($x, 1), 'y' => round($yOrd, 1), 'val' => $trendPoints[$i]['orders'],  'item' => $trendPoints[$i]];
                }

                $revPath = "";
                $revArea = "";
                $ordPath = "";

                if ($pointCount > 0) {
                    $revPath = "M " . $coordsRev[0]['x'] . "," . $coordsRev[0]['y'];
                    $ordPath = "M " . $coordsOrd[0]['x'] . "," . $coordsOrd[0]['y'];

                    for ($i = 1; $i < $pointCount; $i++) {
                        $prevR = $coordsRev[$i - 1];
                        $currR = $coordsRev[$i];
                        $cxR   = $prevR['x'] + ($currR['x'] - $prevR['x']) / 2;
                        $revPath .= " C $cxR,{$prevR['y']} $cxR,{$currR['y']} {$currR['x']},{$currR['y']}";

                        $prevO = $coordsOrd[$i - 1];
                        $currO = $coordsOrd[$i];
                        $cxO   = $prevO['x'] + ($currO['x'] - $prevO['x']) / 2;
                        $ordPath .= " C $cxO,{$prevO['y']} $cxO,{$currO['y']} {$currO['x']},{$currO['y']}";
                    }

                    $bottomY = $padT + $usableH;
                    $firstX  = $coordsRev[0]['x'];
                    $lastX   = $coordsRev[$pointCount - 1]['x'];
                    $revArea = $revPath . " L $lastX,$bottomY L $firstX,$bottomY Z";
                }
                ?>

                <div class="chart-container" style="position:relative; width:100%; overflow-x:auto;">
                    <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width:100%; height:240px; display:block;">
                        <defs>
                            <linearGradient id="salesGreenGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10B981" stop-opacity="0.30"/>
                                <stop offset="100%" stop-color="#10B981" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>

                        <!-- Horizontal Grid Lines with Revenue Labels -->
                        <?php for ($g = 0; $g <= 4; $g++):
                            $gridY = $padT + ($usableH * ($g / 4));
                            $gridVal = round($chartRevMax - ($chartRevMax * ($g / 4)));
                        ?>
                            <line x1="<?= $padL ?>" y1="<?= $gridY ?>" x2="<?= $svgW - $padR ?>" y2="<?= $gridY ?>" stroke="#E2E8F0" stroke-dasharray="3,3" stroke-width="1"/>
                            <text x="<?= $padL - 10 ?>" y="<?= $gridY + 4 ?>" font-size="10" fill="#94A3B8" font-weight="600" text-anchor="end">₹<?= number_format($gridVal) ?></text>
                        <?php endfor; ?>

                        <!-- Revenue Area Fill & Spline -->
                        <?php if ($revArea !== ""): ?>
                            <path d="<?= $revArea ?>" fill="url(#salesGreenGrad)"/>
                            <path d="<?= $revPath ?>" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round"/>
                        <?php endif; ?>

                        <!-- Orders Volume Spline -->
                        <?php if ($ordPath !== ""): ?>
                            <path d="<?= $ordPath ?>" fill="none" stroke="#2D82FF" stroke-width="2.5" stroke-dasharray="4,3" stroke-linecap="round"/>
                        <?php endif; ?>

                        <!-- Data Points & Tooltips -->
                        <?php foreach ($coordsRev as $idx => $pt): ?>
                            <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="4.5" fill="#FFFFFF" stroke="#10B981" stroke-width="2.5" style="cursor:pointer;" onmouseover="showSalesTooltip(event, '<?= $pt['item']['label'] ?>', 'Revenue: ₹<?= number_format($pt['val']) ?>', 'Orders: <?= $coordsOrd[$idx]['val'] ?>', 'AOV: ₹<?= number_format($pt['item']['aov']) ?>')" onmouseout="hideSalesTooltip()"/>
                        <?php endforeach; ?>

                        <?php foreach ($coordsOrd as $pt): ?>
                            <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="3.5" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2" style="cursor:pointer;" onmouseover="showSalesTooltip(event, '<?= $pt['item']['label'] ?>', 'Orders: <?= $pt['val'] ?>', '', '')" onmouseout="hideSalesTooltip()"/>
                        <?php endforeach; ?>

                        <!-- X-Axis Labels -->
                        <?php
                        $step = max(1, (int)ceil($pointCount / 8));
                        for ($i = 0; $i < $pointCount; $i += $step):
                            $c = $coordsRev[$i];
                        ?>
                            <text x="<?= $c['x'] ?>" y="<?= $svgH - 8 ?>" font-size="10" fill="#64748B" font-weight="600" text-anchor="middle"><?= $c['item']['label'] ?></text>
                        <?php endfor; ?>
                    </svg>

                    <!-- Chart Tooltip -->
                    <div id="salesChartTooltip" style="display:none; position:absolute; background:rgba(15,23,42,0.95); color:#fff; padding:8px 14px; border-radius:6px; font-size:0.76rem; pointer-events:none; z-index:10; box-shadow:0 4px 14px rgba(0,0,0,0.2); transform:translate(-50%, -120%); min-width:140px;">
                        <div id="sTooltipDate" style="font-weight:700; border-bottom:1px solid rgba(255,255,255,0.15); padding-bottom:4px; margin-bottom:4px;"></div>
                        <div id="sTooltipRev" style="color:#34d399; font-weight:700;"></div>
                        <div id="sTooltipOrd" style="color:#60a5fa;"></div>
                        <div id="sTooltipAov" style="color:#fcd34d; font-size:0.72rem; margin-top:2px;"></div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SEGMENTED ANALYSIS TABS                                       -->
            <!-- ============================================================ -->
            <div class="card-panel">

                <!-- Navigation Tabs -->
                <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:20px; gap:8px; overflow-x:auto;">
                    <a href="<?= url('admin/sales-analytics?tab=overview&period=' . $curPeriod . '&interval=' . $curInterval) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'overview' ? '700' : '500' ?>; color:<?= $curTab === 'overview' ? 'var(--status-success)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'overview' ? 'var(--status-success)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Daily Summary Ledger
                        <span style="background:var(--bg-surface-secondary); color:var(--text-muted); font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= count($summaries) ?></span>
                    </a>

                    <a href="<?= url('admin/sales-analytics?tab=products&period=' . $curPeriod . '&interval=' . $curInterval) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'products' ? '700' : '500' ?>; color:<?= $curTab === 'products' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'products' ? 'var(--brand-blue)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Top Garments
                        <span style="background:rgba(45,130,255,0.1); color:var(--brand-blue); font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= count($topProducts) ?></span>
                    </a>

                    <a href="<?= url('admin/sales-analytics?tab=categories&period=' . $curPeriod . '&interval=' . $curInterval) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'categories' ? '700' : '500' ?>; color:<?= $curTab === 'categories' ? 'var(--brand-purple)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'categories' ? 'var(--brand-purple)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        Category Share
                        <span style="background:rgba(140,48,245,0.1); color:var(--brand-purple); font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= count($categories) ?></span>
                    </a>

                    <a href="<?= url('admin/sales-analytics?tab=regions&period=' . $curPeriod . '&interval=' . $curInterval) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'regions' ? '700' : '500' ?>; color:<?= $curTab === 'regions' ? '#0891b2' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'regions' ? '#0891b2' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        Regional Markets
                        <span style="background:rgba(6,182,212,0.1); color:#0891b2; font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= count($regions) ?></span>
                    </a>

                    <a href="<?= url('admin/sales-analytics?tab=payments&period=' . $curPeriod . '&interval=' . $curInterval) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'payments' ? '700' : '500' ?>; color:<?= $curTab === 'payments' ? 'var(--brand-orange)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'payments' ? 'var(--brand-orange)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Payment &amp; Fulfillment
                    </a>
                </div>

                <!-- ======================================================== -->
                <!-- TAB 1: DAILY SALES SUMMARY LEDGER                        -->
                <!-- ======================================================== -->
                <?php if ($curTab === 'overview'): ?>
                    <?php if (empty($summaries)): ?>
                        <div class="empty-state" style="padding:48px 24px; text-align:center;">
                            <div style="width:56px; height:56px; border-radius:50%; background:var(--bg-surface-secondary); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                            </div>
                            <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">No Daily Sales Records Found</h3>
                            <p style="color:var(--text-muted); font-size:0.88rem; max-width:420px; margin:0 auto 16px;">Click the "Sync Ledger" button above to aggregate store orders into the daily summary.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Summary Date</th>
                                        <th style="text-align:center;">Orders</th>
                                        <th style="text-align:right;">Gross Revenue</th>
                                        <th style="text-align:right;">Discounts</th>
                                        <th style="text-align:right;">Net Sales</th>
                                        <th style="text-align:right;">AOV</th>
                                        <th style="text-align:center;">New Shoppers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($summaries as $s):
                                        $sTs = validTs($s['summary_date'] . ' 00:00:00');
                                        $sNet = (float)$s['total_revenue'] - (float)$s['total_discount'];
                                    ?>
                                        <tr>
                                            <td>
                                                <span style="font-weight:700; color:var(--text-primary);">
                                                    <?= $sTs ? date('M j, Y (D)', $sTs) : e($s['summary_date']) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="background:rgba(45,130,255,0.08); color:var(--brand-blue); font-weight:700; padding:3px 10px; border-radius:var(--radius-full); font-size:0.82rem;">
                                                    <?= number_format($s['total_orders']) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:right; font-weight:700; color:var(--status-success);">
                                                <?= currency((float)$s['total_revenue']) ?>
                                            </td>
                                            <td style="text-align:right; color:var(--brand-orange); font-size:0.85rem;">
                                                <?= (float)$s['total_discount'] > 0 ? '-' . currency((float)$s['total_discount']) : '₹0' ?>
                                            </td>
                                            <td style="text-align:right; font-weight:700; color:var(--brand-purple);">
                                                <?= currency($sNet) ?>
                                            </td>
                                            <td style="text-align:right; font-weight:600; color:var(--text-primary);">
                                                <?= currency((float)$s['avg_order_value']) ?>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="font-size:0.82rem; color:var(--text-secondary);">
                                                    +<?= (int)$s['new_customers'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:16px; border-top:1px solid var(--border-color); flex-wrap:wrap; gap:12px;">
                                <div style="font-size:0.84rem; color:var(--text-muted);">
                                    Showing page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong> (<?= number_format($pagination['total']) ?> days)
                                </div>
                                <div style="display:flex; gap:6px;">
                                    <?php if ($pagination['has_prev']): ?>
                                        <a href="<?= url('admin/sales-analytics?tab=overview&period=' . $curPeriod . '&page=' . ($pagination['current_page'] - 1)) ?>" style="padding:6px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.82rem; color:var(--text-primary); text-decoration:none;">
                                            &larr; Previous
                                        </a>
                                    <?php endif; ?>

                                    <?php for ($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['total_pages'], $pagination['current_page'] + 2); $p++): ?>
                                        <a href="<?= url('admin/sales-analytics?tab=overview&period=' . $curPeriod . '&page=' . $p) ?>" style="padding:6px 12px; border-radius:var(--radius-sm); font-size:0.82rem; text-decoration:none; <?= $p === $pagination['current_page'] ? 'background:var(--status-success); color:#fff; font-weight:700;' : 'border:1px solid var(--border-color); color:var(--text-primary);' ?>">
                                            <?= $p ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <a href="<?= url('admin/sales-analytics?tab=overview&period=' . $curPeriod . '&page=' . ($pagination['current_page'] + 1)) ?>" style="padding:6px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.82rem; color:var(--text-primary); text-decoration:none;">
                                            Next &rarr;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <!-- ======================================================== -->
                <!-- TAB 2: BEST SELLING GARMENTS                             -->
                <!-- ======================================================== -->
                <?php elseif ($curTab === 'products'): ?>
                    <?php if (empty($topProducts)): ?>
                        <div class="empty-state" style="padding:48px 24px; text-align:center;">
                            <p style="color:var(--text-muted); font-size:0.88rem;">No garment sales recorded in the selected period.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th style="width:30px;">#</th>
                                        <th>Luxury Garment</th>
                                        <th>Category</th>
                                        <th style="text-align:center;">Units Sold</th>
                                        <th style="text-align:right;">Gross Revenue</th>
                                        <th style="text-align:right;">Avg Unit Price</th>
                                        <th style="text-align:center;">Orders</th>
                                        <th style="text-align:right;">Catalog</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topProducts as $idx => $prod):
                                        $prodEnc = !empty($prod['product_id']) ? encrypt_id($prod['product_id']) : '';
                                    ?>
                                        <tr>
                                            <td style="color:var(--text-muted); font-size:0.8rem;"><?= $idx + 1 ?></td>
                                            <td>
                                                <div style="font-weight:700; color:var(--text-primary); font-size:0.9rem;">
                                                    <?= e($prod['product_name']) ?>
                                                </div>
                                                <div style="font-size:0.75rem; color:var(--text-muted);">
                                                    SKU: <?= e($prod['sku']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="background:var(--bg-surface-secondary); color:var(--text-secondary); font-size:0.75rem; padding:2px 8px; border-radius:var(--radius-full);">
                                                    <?= e($prod['category_name']) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="font-weight:700; color:var(--brand-blue); background:rgba(45,130,255,0.08); padding:3px 10px; border-radius:var(--radius-full); font-size:0.85rem;">
                                                    <?= number_format($prod['units_sold']) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:right; font-weight:700; color:var(--status-success); font-size:0.92rem;">
                                                <?= currency($prod['gross_revenue']) ?>
                                            </td>
                                            <td style="text-align:right; color:var(--text-secondary); font-size:0.85rem;">
                                                <?= currency($prod['avg_unit_price']) ?>
                                            </td>
                                            <td style="text-align:center; color:var(--text-muted); font-size:0.85rem;">
                                                <?= $prod['orders_count'] ?>
                                            </td>
                                            <td style="text-align:right;">
                                                <?php if ($prodEnc !== ''): ?>
                                                    <a href="<?= url('admin/products/' . $prodEnc . '/edit') ?>" style="font-size:0.78rem; color:var(--brand-blue); font-weight:600; text-decoration:none;">
                                                        Edit &rarr;
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <!-- ======================================================== -->
                <!-- TAB 3: CATEGORY SHARE BREAKDOWN                          -->
                <!-- ======================================================== -->
                <?php elseif ($curTab === 'categories'): ?>
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <?php foreach ($categories as $cat): ?>
                            <div style="background:var(--bg-surface-secondary); padding:16px 20px; border-radius:var(--radius-md); border:1px solid var(--border-color-light);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div style="width:34px; height:34px; border-radius:var(--radius-sm); background:rgba(140,48,245,0.1); color:var(--brand-purple); display:flex; align-items:center; justify-content:center; font-weight:700;">
                                            👗
                                        </div>
                                        <div>
                                            <div style="font-weight:700; font-size:0.92rem; color:var(--text-primary);"><?= e($cat['category_name']) ?></div>
                                            <div style="font-size:0.75rem; color:var(--text-muted);"><?= number_format($cat['units_sold']) ?> units sold across <?= $cat['orders_count'] ?> orders</div>
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:700; font-size:1.05rem; color:var(--status-success);"><?= currency($cat['revenue']) ?></div>
                                        <div style="font-size:0.75rem; font-weight:700; color:var(--brand-purple);"><?= $cat['share_pct'] ?>% of revenue</div>
                                    </div>
                                </div>
                                <!-- Progress Bar -->
                                <div style="height:8px; background:var(--bg-surface); border-radius:999px; overflow:hidden; border:1px solid var(--border-color-light);">
                                    <div style="height:100%; width:<?= min(100, max(2, $cat['share_pct'])) ?>%; background:var(--gradient-primary); border-radius:999px;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <!-- ======================================================== -->
                <!-- TAB 4: REGIONAL MARKETS                                  -->
                <!-- ======================================================== -->
                <?php elseif ($curTab === 'regions'): ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th style="width:30px;">#</th>
                                    <th>Metro / Destination City</th>
                                    <th>State</th>
                                    <th style="text-align:center;">Orders</th>
                                    <th style="text-align:right;">Revenue</th>
                                    <th style="text-align:right;">Market Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($regions as $idx => $r): ?>
                                    <tr>
                                        <td style="color:var(--text-muted); font-size:0.8rem;"><?= $idx + 1 ?></td>
                                        <td>
                                            <div style="font-weight:700; color:var(--text-primary); font-size:0.9rem;">
                                                📍 <?= e($r['city']) ?>
                                            </div>
                                        </td>
                                        <td style="color:var(--text-secondary); font-size:0.85rem;">
                                            <?= e($r['state']) ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <span style="background:rgba(6,182,212,0.1); color:#0891b2; font-weight:700; padding:2px 8px; border-radius:var(--radius-full); font-size:0.82rem;">
                                                <?= number_format($r['orders_count']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align:right; font-weight:700; color:var(--status-success); font-size:0.92rem;">
                                            <?= currency($r['revenue']) ?>
                                        </td>
                                        <td style="text-align:right;">
                                            <span style="font-weight:700; color:var(--text-primary); font-size:0.85rem;">
                                                <?= $r['share_pct'] ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- ======================================================== -->
                <!-- TAB 5: PAYMENT & FULFILLMENT FUNNEL                      -->
                <!-- ======================================================== -->
                <?php elseif ($curTab === 'payments'): ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">

                        <!-- Payment Methods Distribution -->
                        <div style="background:var(--bg-surface-secondary); padding:20px; border-radius:var(--radius-md); border:1px solid var(--border-color-light);">
                            <h3 style="font-size:0.95rem; font-weight:700; color:var(--text-primary); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                                💳 Payment Gateways &amp; Methods
                            </h3>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <?php foreach ($payments['methods'] as $pm): ?>
                                    <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-surface); padding:10px 14px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                                        <div style="font-weight:600; font-size:0.86rem; color:var(--text-primary);"><?= e($pm['payment_method']) ?></div>
                                        <div style="text-align:right;">
                                            <div style="font-weight:700; color:var(--status-success); font-size:0.88rem;"><?= currency((float)$pm['revenue']) ?></div>
                                            <div style="font-size:0.72rem; color:var(--text-muted);"><?= $pm['count'] ?> orders</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Lifecycle Status Distribution -->
                        <div style="background:var(--bg-surface-secondary); padding:20px; border-radius:var(--radius-md); border:1px solid var(--border-color-light);">
                            <h3 style="font-size:0.95rem; font-weight:700; color:var(--text-primary); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                                📦 Order Fulfillment Funnel
                            </h3>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <?php foreach ($payments['order_statuses'] as $os):
                                    $sPillStyle = match($os['status']) {
                                        'delivered' => 'background:var(--status-success-bg); color:var(--status-success);',
                                        'shipped'   => 'background:rgba(45,130,255,0.1); color:var(--brand-blue);',
                                        'packed'    => 'background:rgba(140,48,245,0.1); color:var(--brand-purple);',
                                        'cancelled' => 'background:var(--status-danger-bg); color:var(--status-danger);',
                                        default     => 'background:var(--bg-surface-secondary); color:var(--text-secondary);',
                                    };
                                ?>
                                    <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-surface); padding:10px 14px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                                        <span class="badge" style="<?= $sPillStyle ?> text-transform:capitalize; font-size:0.75rem; font-weight:700;">
                                            <?= e($os['status']) ?>
                                        </span>
                                        <span style="font-weight:700; font-size:0.9rem; color:var(--text-primary);">
                                            <?= $os['count'] ?> orders
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </div>

        </main>
    </div>
</div>

<script>
// Financial Export Dropdown Toggle
function toggleSalesExportMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('salesExportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function() {
    const menu = document.getElementById('salesExportMenu');
    if (menu) menu.style.display = 'none';
});

// Sales Spline Chart Tooltip
function showSalesTooltip(e, date, rev, ord, aov) {
    const tt = document.getElementById('salesChartTooltip');
    const container = document.querySelector('.chart-container');
    const rect = container.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    document.getElementById('sTooltipDate').textContent = date;
    document.getElementById('sTooltipRev').textContent = rev;
    document.getElementById('sTooltipOrd').textContent = ord;
    document.getElementById('sTooltipAov').textContent = aov;

    tt.style.left = x + 'px';
    tt.style.top = y + 'px';
    tt.style.display = 'block';
}
function hideSalesTooltip() {
    const tt = document.getElementById('salesChartTooltip');
    if (tt) tt.style.display = 'none';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
