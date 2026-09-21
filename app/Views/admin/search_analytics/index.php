<?php
include __DIR__ . '/../layouts/header.php';

if (!function_exists('validTs')) {
    function validTs(?string $d): int|false {
        if (empty($d) || $d === '0000-00-00 00:00:00') return false;
        $ts = strtotime($d);
        return ($ts && $ts > 946684800) ? $ts : false;
    }
}

$curPeriod = $filters['period'] ?? '30d';
$curTab    = $activeTab ?? 'top';
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
                        <span>Marketing &amp; Growth</span>
                        <span style="margin:0 5px; opacity:.5;">/</span>
                        <span style="color:var(--text-primary); font-weight:600;">Search Analytics</span>
                    </div>
                    <h1 class="welcome-title">Search Keyword Analytics &amp; Zero Results</h1>
                    <p class="welcome-subtitle">Gain deep visibility into customer shopping intent, monitor top-converting terms, uncover catalog gaps from zero-result queries, and simulate store search behavior.</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <!-- Query Simulator Trigger -->
                    <button type="button" onclick="openSimulatorModal()"
                            style="display:inline-flex; align-items:center; gap:8px; background:var(--bg-surface); color:var(--text-primary); border:1px solid var(--border-color); font-size:0.88rem; font-weight:600; padding:9px 16px; border-radius:var(--radius-md); cursor:pointer; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:var(--transition);"
                            onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'"
                            onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Test Simulator
                    </button>

                    <!-- Export Dropdown -->
                    <div style="position:relative; display:inline-block;" id="exportDropdownWrapper">
                        <button type="button" onclick="toggleExportMenu(event)"
                                style="display:inline-flex; align-items:center; gap:8px; background:var(--gradient-primary); color:#fff; font-size:0.88rem; font-weight:700; padding:10px 18px; border-radius:var(--radius-md); border:none; cursor:pointer; box-shadow:var(--shadow-glow-blue); transition:var(--transition);"
                                onmouseover="this.style.opacity='0.92'; this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.opacity='1'; this.style.transform=''">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export CSV
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="exportMenu" style="display:none; position:absolute; right:0; top:calc(100% + 6px); background:var(--bg-surface); border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:0 10px 25px -5px rgba(0,0,0,0.12); width:210px; z-index:100; overflow:hidden;">
                            <a href="<?= url('admin/search-analytics/export?export_type=all&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--text-primary); text-decoration:none; border-bottom:1px solid var(--border-color-light);" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Full Activity Ledger
                            </a>
                            <a href="<?= url('admin/search-analytics/export?export_type=top&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--text-primary); text-decoration:none; border-bottom:1px solid var(--border-color-light);" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-purple)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                                Top Keywords Summary
                            </a>
                            <a href="<?= url('admin/search-analytics/export?export_type=zero&period=' . $curPeriod) ?>" style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:0.85rem; color:var(--brand-orange); text-decoration:none;" onmouseover="this.style.background='var(--bg-surface-secondary)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--brand-orange)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                Zero Results Gaps (CSV)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- PERIOD FILTER BAR                                             -->
            <!-- ============================================================ -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; gap:6px; background:var(--bg-surface-secondary); padding:4px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                    <?php
                    $periods = [
                        'today' => 'Today',
                        '7d'    => 'Last 7 Days',
                        '30d'   => 'Last 30 Days',
                        '90d'   => 'Last 90 Days',
                        'all'   => 'All Time',
                    ];
                    foreach ($periods as $pKey => $pLabel):
                        $isActive = ($curPeriod === $pKey);
                    ?>
                        <a href="<?= url('admin/search-analytics?period=' . $pKey . '&tab=' . $curTab) ?>"
                           style="padding:6px 14px; font-size:0.82rem; font-weight:<?= $isActive ? '700' : '500' ?>; border-radius:var(--radius-sm); text-decoration:none; transition:var(--transition); <?= $isActive ? 'background:var(--bg-surface); color:var(--brand-blue); box-shadow:0 1px 3px rgba(0,0,0,0.08);' : 'color:var(--text-secondary);' ?>">
                            <?= $pLabel ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Housekeeping Action -->
                <div>
                    <button type="button" onclick="openPurgeModal()" style="background:transparent; border:none; color:var(--text-muted); font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" onmouseover="this.style.color='var(--status-danger)'" onmouseout="this.style.color='var(--text-muted)'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Purge Old Logs
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- 5 KPI EXECUTIVE CARDS                                         -->
            <!-- ============================================================ -->
            <div class="catalog-kpi-grid" style="margin-bottom:24px;">
                <!-- 1. Total Searches -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-blue);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg, #2D82FF 0%, #1a62d0 100%); width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <span style="font-size:0.7rem; font-weight:700; background:rgba(45,130,255,0.1); color:var(--brand-blue); padding:2px 8px; border-radius:var(--radius-full);">
                            <?= strtoupper($curPeriod) ?>
                        </span>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-blue);"><?= number_format($kpis['total_searches']) ?></div>
                    <div class="kpi-label">Total Store Searches</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        <?= number_format($kpis['registered_searches']) ?> members &bull; <?= number_format($kpis['guest_searches']) ?> guests
                    </div>
                </div>

                <!-- 2. Unique Keywords -->
                <div class="kpi-card" style="border-left:3px solid var(--brand-purple);">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg, #8C30F5 0%, #6e1ecc 100%); width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--brand-purple);"><?= number_format($kpis['unique_keywords']) ?></div>
                    <div class="kpi-label">Distinct Keywords</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Avg <?= $kpis['avg_results_count'] ?> products yielded
                    </div>
                </div>

                <!-- 3. Zero-Result Rate % -->
                <?php
                $zeroRate = $kpis['zero_results_rate'];
                $isHighZero = $zeroRate > 15;
                ?>
                <div class="kpi-card" style="border-left:3px solid <?= $isHighZero ? 'var(--brand-orange)' : 'var(--status-success)' ?>;">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg, #FF5100 0%, #d44300 100%); width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        </div>
                        <span style="font-size:0.7rem; font-weight:700; background:<?= $isHighZero ? 'var(--brand-orange-light)' : 'var(--status-success-bg)' ?>; color:<?= $isHighZero ? 'var(--brand-orange)' : 'var(--status-success)' ?>; padding:2px 8px; border-radius:var(--radius-full);">
                            <?= $isHighZero ? 'Catalog Gaps' : 'Optimal' ?>
                        </span>
                    </div>
                    <div class="kpi-value" style="color:<?= $isHighZero ? 'var(--brand-orange)' : 'var(--status-success)' ?>;"><?= $zeroRate ?>%</div>
                    <div class="kpi-label">Zero-Result Rate</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        <?= number_format($kpis['zero_results_count']) ?> searches with 0 results
                    </div>
                </div>

                <!-- 4. Top Query Overall -->
                <div class="kpi-card" style="border-left:3px solid #06b6d4;">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <?php if ($kpis['top_keyword_count'] > 0): ?>
                            <span style="font-size:0.7rem; font-weight:700; background:rgba(6,182,212,0.1); color:#0891b2; padding:2px 8px; border-radius:var(--radius-full);">
                                #1 Popular
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-value" style="font-size:1.35rem; color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?= e($kpis['top_keyword']) ?>">
                        <?php if ($kpis['top_keyword'] !== 'N/A'): ?>
                            <a href="<?= url('admin/search-analytics/term/' . urlencode($kpis['top_keyword'])) ?>" style="color:var(--text-primary); text-decoration:none;" onmouseover="this.style.color='var(--brand-blue)'" onmouseout="this.style.color='var(--text-primary)'">
                                &ldquo;<?= e($kpis['top_keyword']) ?>&rdquo;
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                    <div class="kpi-label">Top Searched Term</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        <?= number_format($kpis['top_keyword_count']) ?> searches in period
                    </div>
                </div>

                <!-- 5. Searches Today -->
                <div class="kpi-card" style="border-left:3px solid #10b981;">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%); width:40px; height:40px; border-radius:var(--radius-md);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value" style="color:var(--status-success);"><?= number_format($kpis['searches_today']) ?></div>
                    <div class="kpi-label">Searches Today</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                        Yesterday: <?= number_format($kpis['searches_yesterday']) ?> searches
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SEARCH VOLUME & ZERO-RESULTS TREND SVG CHART                 -->
            <!-- ============================================================ -->
            <div class="card-panel" style="margin-bottom:24px;">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            <span>Search Volume &amp; Zero-Result Trajectory</span>
                        </h2>
                        <div class="panel-subtitle">Daily shopping inquiry volume tracked alongside unmet zero-result searches</div>
                    </div>
                    <div style="display:flex; gap:14px; align-items:center;">
                        <span class="badge" style="background:rgba(45,130,255,0.1); color:var(--brand-blue); font-weight:600;">
                            <span style="width:8px; height:8px; border-radius:50%; background:var(--brand-blue); display:inline-block; margin-right:5px;"></span>
                            Total Searches
                        </span>
                        <span class="badge" style="background:var(--brand-orange-light); color:var(--brand-orange); font-weight:600;">
                            <span style="width:8px; height:8px; border-radius:50%; background:var(--brand-orange); display:inline-block; margin-right:5px;"></span>
                            Zero Results (Gaps)
                        </span>
                    </div>
                </div>

                <?php
                // Build dynamic SVG geometry for the trend chart
                $trendPoints = !empty($volumeTrend) ? $volumeTrend : [];
                $pointCount = count($trendPoints);
                $maxVal = 1;
                foreach ($trendPoints as $pt) {
                    if ($pt['total_count'] > $maxVal) $maxVal = $pt['total_count'];
                }
                // Add ceiling padding
                $chartMax = (int)ceil($maxVal * 1.25);
                if ($chartMax < 5) $chartMax = 5;

                $svgWidth = 700;
                $svgHeight = 220;
                $padLeft = 40;
                $padRight = 20;
                $padTop = 20;
                $padBottom = 35;

                $usableW = $svgWidth - $padLeft - $padRight;
                $usableH = $svgHeight - $padTop - $padBottom;

                $coordsTotal = [];
                $coordsZero  = [];

                for ($i = 0; $i < $pointCount; $i++) {
                    $x = $pointCount > 1 ? $padLeft + ($i * ($usableW / ($pointCount - 1))) : $padLeft + ($usableW / 2);
                    $yTotal = $padTop + $usableH - (($trendPoints[$i]['total_count'] / $chartMax) * $usableH);
                    $yZero  = $padTop + $usableH - (($trendPoints[$i]['zero_count'] / $chartMax) * $usableH);

                    $coordsTotal[] = ['x' => round($x, 1), 'y' => round($yTotal, 1), 'val' => $trendPoints[$i]['total_count'], 'item' => $trendPoints[$i]];
                    $coordsZero[]  = ['x' => round($x, 1), 'y' => round($yZero, 1),  'val' => $trendPoints[$i]['zero_count'],  'item' => $trendPoints[$i]];
                }

                // Build path string
                $totalPath = "";
                $totalArea = "";
                $zeroPath  = "";

                if ($pointCount > 0) {
                    $totalPath = "M " . $coordsTotal[0]['x'] . "," . $coordsTotal[0]['y'];
                    $zeroPath  = "M " . $coordsZero[0]['x'] . "," . $coordsZero[0]['y'];

                    for ($i = 1; $i < $pointCount; $i++) {
                        $prev = $coordsTotal[$i - 1];
                        $curr = $coordsTotal[$i];
                        $cx1 = $prev['x'] + ($curr['x'] - $prev['x']) / 2;
                        $totalPath .= " C $cx1,{$prev['y']} $cx1,{$curr['y']} {$curr['x']},{$curr['y']}";

                        $prevZ = $coordsZero[$i - 1];
                        $currZ = $coordsZero[$i];
                        $cxZ1 = $prevZ['x'] + ($currZ['x'] - $prevZ['x']) / 2;
                        $zeroPath .= " C $cxZ1,{$prevZ['y']} $cxZ1,{$currZ['y']} {$currZ['x']},{$currZ['y']}";
                    }

                    $bottomY = $padTop + $usableH;
                    $firstX = $coordsTotal[0]['x'];
                    $lastX  = $coordsTotal[$pointCount - 1]['x'];
                    $totalArea = $totalPath . " L $lastX,$bottomY L $firstX,$bottomY Z";
                }
                ?>

                <div class="chart-container" style="position:relative; width:100%; overflow-x:auto;">
                    <svg viewBox="0 0 <?= $svgWidth ?> <?= $svgHeight ?>" style="width:100%; height:220px; display:block;">
                        <defs>
                            <linearGradient id="searchBlueGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2D82FF" stop-opacity="0.30"/>
                                <stop offset="100%" stop-color="#2D82FF" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>

                        <!-- Horizontal Grid Lines -->
                        <?php for ($g = 0; $g <= 4; $g++):
                            $gridY = $padTop + ($usableH * ($g / 4));
                            $gridVal = round($chartMax - ($chartMax * ($g / 4)));
                        ?>
                            <line x1="<?= $padLeft ?>" y1="<?= $gridY ?>" x2="<?= $svgWidth - $padRight ?>" y2="<?= $gridY ?>" stroke="#E2E8F0" stroke-dasharray="3,3" stroke-width="1"/>
                            <text x="<?= $padLeft - 8 ?>" y="<?= $gridY + 4 ?>" font-size="10" fill="#94A3B8" text-anchor="end"><?= $gridVal ?></text>
                        <?php endfor; ?>

                        <!-- Total Search Area & Spline -->
                        <?php if ($totalArea !== ""): ?>
                            <path d="<?= $totalArea ?>" fill="url(#searchBlueGrad)"/>
                            <path d="<?= $totalPath ?>" fill="none" stroke="#2D82FF" stroke-width="3" stroke-linecap="round"/>
                        <?php endif; ?>

                        <!-- Zero Results Spline -->
                        <?php if ($zeroPath !== ""): ?>
                            <path d="<?= $zeroPath ?>" fill="none" stroke="#FF5100" stroke-width="2.2" stroke-dasharray="4,3" stroke-linecap="round"/>
                        <?php endif; ?>

                        <!-- Interactive Data Points -->
                        <?php foreach ($coordsTotal as $idx => $pt): ?>
                            <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="4" fill="#FFFFFF" stroke="#2D82FF" stroke-width="2.5" style="cursor:pointer; transition:transform 0.2s;" onmouseover="showChartTooltip(event, '<?= $pt['item']['formatted_date'] ?>', 'Total: <?= $pt['val'] ?>', 'Zero: <?= $coordsZero[$idx]['val'] ?>')" onmouseout="hideChartTooltip()"/>
                        <?php endforeach; ?>

                        <?php foreach ($coordsZero as $pt): ?>
                            <circle cx="<?= $pt['x'] ?>" cy="<?= $pt['y'] ?>" r="3.5" fill="#FFFFFF" stroke="#FF5100" stroke-width="2" style="cursor:pointer;" onmouseover="showChartTooltip(event, '<?= $pt['item']['formatted_date'] ?>', 'Zero Results: <?= $pt['val'] ?>', '')" onmouseout="hideChartTooltip()"/>
                        <?php endforeach; ?>

                        <!-- X Axis Labels (sample evenly) -->
                        <?php
                        $step = max(1, (int)ceil($pointCount / 7));
                        for ($i = 0; $i < $pointCount; $i += $step):
                            $c = $coordsTotal[$i];
                        ?>
                            <text x="<?= $c['x'] ?>" y="<?= $svgHeight - 8 ?>" font-size="10" fill="#64748B" font-weight="600" text-anchor="middle"><?= $c['item']['formatted_date'] ?></text>
                        <?php endfor; ?>
                    </svg>

                    <!-- Chart Tooltip Popup -->
                    <div id="chartTooltip" style="display:none; position:absolute; background:rgba(15,23,42,0.92); color:#fff; padding:6px 12px; border-radius:6px; font-size:0.75rem; pointer-events:none; z-index:10; box-shadow:0 4px 12px rgba(0,0,0,0.15); transform:translate(-50%, -120%);">
                        <div id="tooltipDate" style="font-weight:700; margin-bottom:2px;"></div>
                        <div id="tooltipLine1" style="color:#60a5fa;"></div>
                        <div id="tooltipLine2" style="color:#fb923c;"></div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TABBED ANALYTICS COMMAND CENTER                              -->
            <!-- ============================================================ -->
            <div class="card-panel">

                <!-- Navigation Tabs -->
                <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:20px; gap:8px; overflow-x:auto;">
                    <a href="<?= url('admin/search-analytics?tab=top&period=' . $curPeriod) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'top' ? '700' : '500' ?>; color:<?= $curTab === 'top' ? 'var(--brand-blue)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'top' ? 'var(--brand-blue)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Top Search Terms
                        <span style="background:var(--bg-surface-secondary); color:var(--text-muted); font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= count($topQueries) ?></span>
                    </a>

                    <a href="<?= url('admin/search-analytics?tab=zero&period=' . $curPeriod) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'zero' ? '700' : '500' ?>; color:<?= $curTab === 'zero' ? 'var(--brand-orange)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'zero' ? 'var(--brand-orange)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        Zero Results Opportunity Log
                        <span style="background:var(--brand-orange-light); color:var(--brand-orange); font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= count($zeroQueries) ?></span>
                    </a>

                    <a href="<?= url('admin/search-analytics?tab=stream&period=' . $curPeriod) ?>"
                       style="padding:12px 18px; font-size:0.9rem; font-weight:<?= $curTab === 'stream' ? '700' : '500' ?>; color:<?= $curTab === 'stream' ? 'var(--brand-purple)' : 'var(--text-secondary)' ?>; border-bottom:2px solid <?= $curTab === 'stream' ? 'var(--brand-purple)' : 'transparent' ?>; text-decoration:none; display:inline-flex; align-items:center; gap:8px; white-space:nowrap;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        Live Activity Ledger
                        <span style="background:var(--bg-surface-secondary); color:var(--text-muted); font-size:0.72rem; padding:1px 7px; border-radius:var(--radius-full); font-weight:700;"><?= number_format($pagination['total']) ?></span>
                    </a>
                </div>

                <!-- Search & Filters Toolbar -->
                <form method="GET" action="<?= url('admin/search-analytics') ?>" style="margin-bottom:20px;">
                    <input type="hidden" name="tab" value="<?= e($curTab) ?>">
                    <input type="hidden" name="period" value="<?= e($curPeriod) ?>">

                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <!-- Keyword Search -->
                        <div style="position:relative; flex:1; min-width:220px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" name="search" class="form-input" value="<?= e($filters['search']) ?>" placeholder="Filter by search keyword..." style="padding-left:36px; width:100%;">
                        </div>

                        <?php if ($curTab === 'stream'): ?>
                            <!-- Result Type Filter -->
                            <select name="results_filter" class="form-input" style="width:auto; min-width:150px;">
                                <option value="all" <?= ($filters['results_filter'] ?? '') === 'all' ? 'selected' : '' ?>>All Results</option>
                                <option value="success" <?= ($filters['results_filter'] ?? '') === 'success' ? 'selected' : '' ?>>Yielded Products</option>
                                <option value="zero" <?= ($filters['results_filter'] ?? '') === 'zero' ? 'selected' : '' ?>>Zero Results Only</option>
                            </select>

                            <!-- Device Filter -->
                            <select name="device" class="form-input" style="width:auto; min-width:130px;">
                                <option value="all" <?= ($filters['device'] ?? '') === 'all' ? 'selected' : '' ?>>All Devices</option>
                                <option value="desktop" <?= ($filters['device'] ?? '') === 'desktop' ? 'selected' : '' ?>>Desktop</option>
                                <option value="mobile" <?= ($filters['device'] ?? '') === 'mobile' ? 'selected' : '' ?>>Mobile</option>
                                <option value="tablet" <?= ($filters['device'] ?? '') === 'tablet' ? 'selected' : '' ?>>Tablet</option>
                            </select>

                            <!-- User Type -->
                            <select name="user_type" class="form-input" style="width:auto; min-width:140px;">
                                <option value="all" <?= ($filters['user_type'] ?? '') === 'all' ? 'selected' : '' ?>>All Shoppers</option>
                                <option value="registered" <?= ($filters['user_type'] ?? '') === 'registered' ? 'selected' : '' ?>>Registered</option>
                                <option value="guest" <?= ($filters['user_type'] ?? '') === 'guest' ? 'selected' : '' ?>>Guests</option>
                            </select>
                        <?php endif; ?>

                        <button type="submit" style="background:var(--brand-blue); color:#fff; border:none; padding:10px 18px; border-radius:var(--radius-md); font-weight:600; font-size:0.88rem; cursor:pointer;">
                            Filter
                        </button>

                        <?php if (!empty($filters['search']) || ($filters['results_filter'] ?? 'all') !== 'all' || ($filters['device'] ?? 'all') !== 'all'): ?>
                            <a href="<?= url('admin/search-analytics?tab=' . $curTab . '&period=' . $curPeriod) ?>" style="color:var(--text-muted); font-size:0.85rem; text-decoration:none;">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- ======================================================== -->
                <!-- TAB 1: TOP SEARCH TERMS                                  -->
                <!-- ======================================================== -->
                <?php if ($curTab === 'top'): ?>
                    <?php if (empty($topQueries)): ?>
                        <div class="empty-state" style="padding:48px 24px; text-align:center;">
                            <div style="width:56px; height:56px; border-radius:50%; background:var(--bg-surface-secondary); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            </div>
                            <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">No Search Keywords Found</h3>
                            <p style="color:var(--text-muted); font-size:0.88rem; max-width:420px; margin:0 auto;">No customer searches matched your filter criteria for this period.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th style="width:30px;">#</th>
                                        <th>Search Keyword</th>
                                        <th style="text-align:center;">Frequency</th>
                                        <th style="text-align:center;">Avg Results Yield</th>
                                        <th style="text-align:center;">Zero Results</th>
                                        <th style="text-align:center;">Shoppers</th>
                                        <th>Last Searched</th>
                                        <th style="text-align:right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topQueries as $i => $q):
                                        $lastTs = validTs($q['last_searched']);
                                        $termEnc = encrypt_id($q['sample_id']);
                                        $hasZero = (int)$q['zero_count'] > 0;
                                    ?>
                                        <tr>
                                            <td style="color:var(--text-muted); font-size:0.8rem;"><?= $i + 1 ?></td>
                                            <td>
                                                <a href="<?= url('admin/search-analytics/term/' . $termEnc) ?>" style="font-weight:700; color:var(--text-primary); text-decoration:none; display:inline-flex; align-items:center; gap:6px;" onmouseover="this.style.color='var(--brand-blue)'" onmouseout="this.style.color='var(--text-primary)'">
                                                    <span><?= e($q['keyword']) ?></span>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                </a>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="font-weight:700; color:var(--brand-blue); background:rgba(45,130,255,0.08); padding:3px 10px; border-radius:var(--radius-full); font-size:0.85rem;">
                                                    <?= number_format($q['search_count']) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="font-weight:600; color:<?= (float)$q['avg_results'] > 0 ? 'var(--status-success)' : 'var(--brand-orange)' ?>;">
                                                    <?= $q['avg_results'] ?> items
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <?php if ($hasZero): ?>
                                                    <span style="background:var(--brand-orange-light); color:var(--brand-orange); font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:var(--radius-full);">
                                                        <?= $q['zero_count'] ?> zero
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-size:0.8rem;">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:center; color:var(--text-secondary); font-size:0.85rem;">
                                                <?= $q['unique_users'] ?>
                                            </td>
                                            <td style="font-size:0.82rem; color:var(--text-muted);">
                                                <?= $lastTs ? date('M j, Y h:i A', $lastTs) : '—' ?>
                                            </td>
                                            <td style="text-align:right;">
                                                <div style="display:inline-flex; gap:6px;">
                                                    <button type="button" onclick="testSimulatorQuery('<?= e(addslashes($q['keyword'])) ?>')" title="Simulate Catalog Response" style="background:none; border:1px solid var(--border-color); color:var(--text-secondary); padding:5px 8px; border-radius:var(--radius-sm); cursor:pointer;" onmouseover="this.style.borderColor='var(--brand-blue)'; this.style.color='var(--brand-blue)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)'">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                                    </button>
                                                    <a href="<?= url('admin/products?search=' . urlencode($q['keyword'])) ?>" target="_blank" title="Search in Products Catalog" style="background:none; border:1px solid var(--border-color); color:var(--text-secondary); padding:5px 8px; border-radius:var(--radius-sm); text-decoration:none; display:inline-flex; align-items:center;" onmouseover="this.style.borderColor='var(--brand-purple)'; this.style.color='var(--brand-purple)'" onmouseout="this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)'">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                                    </a>
                                                    <a href="<?= url('admin/search-analytics/term/' . $termEnc) ?>" style="background:var(--brand-blue); color:#fff; padding:5px 10px; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:600; text-decoration:none;">
                                                        Inspect
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <!-- ======================================================== -->
                <!-- TAB 2: ZERO RESULTS GAPS                                 -->
                <!-- ======================================================== -->
                <?php elseif ($curTab === 'zero'): ?>
                    <div style="background:rgba(255,81,0,0.06); border:1px solid rgba(255,81,0,0.2); border-radius:var(--radius-md); padding:16px 20px; margin-bottom:20px; display:flex; gap:14px; align-items:flex-start;">
                        <div style="color:var(--brand-orange); margin-top:2px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div>
                            <div style="font-weight:700; color:var(--text-primary); font-size:0.92rem; margin-bottom:4px;">Unmet Luxury Fashion Demand Opportunities</div>
                            <div style="font-size:0.84rem; color:var(--text-secondary); line-height:1.5;">
                                These search terms were actively entered by customers but yielded 0 results in the store catalog. Add matching garments or assign these search terms to existing product descriptions/tags to unlock lost sales.
                            </div>
                        </div>
                    </div>

                    <?php if (empty($zeroQueries)): ?>
                        <div class="empty-state" style="padding:48px 24px; text-align:center;">
                            <div style="width:56px; height:56px; border-radius:50%; background:var(--status-success-bg); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--status-success)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Zero Results Log Clean!</h3>
                            <p style="color:var(--text-muted); font-size:0.88rem; max-width:420px; margin:0 auto;">No queries yielded 0 results during this selected period. Catalog coverage is optimal.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th style="width:30px;">#</th>
                                        <th>Unmet Search Term</th>
                                        <th style="text-align:center;">Missed Searches</th>
                                        <th style="text-align:center;">Unique Shoppers</th>
                                        <th>First Attempted</th>
                                        <th>Last Attempted</th>
                                        <th style="text-align:right;">Merchandising Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($zeroQueries as $i => $zq):
                                        $lastTs = validTs($zq['last_searched']);
                                        $firstTs = validTs($zq['first_searched']);
                                        $termEnc = encrypt_id($zq['sample_id']);
                                    ?>
                                        <tr>
                                            <td style="color:var(--text-muted); font-size:0.8rem;"><?= $i + 1 ?></td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:8px;">
                                                    <span style="font-weight:700; color:var(--brand-orange); font-size:0.92rem;">&ldquo;<?= e($zq['keyword']) ?>&rdquo;</span>
                                                    <span style="background:var(--brand-orange-light); color:var(--brand-orange); font-size:0.68rem; font-weight:700; padding:1px 6px; border-radius:var(--radius-full);">0 Products</span>
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="font-weight:700; color:var(--brand-orange); background:var(--brand-orange-light); padding:3px 10px; border-radius:var(--radius-full); font-size:0.85rem;">
                                                    <?= number_format($zq['search_count']) ?> times
                                                </span>
                                            </td>
                                            <td style="text-align:center; font-size:0.85rem; color:var(--text-secondary);">
                                                <?= $zq['unique_users'] ?>
                                            </td>
                                            <td style="font-size:0.82rem; color:var(--text-muted);">
                                                <?= $firstTs ? date('M j, Y', $firstTs) : '—' ?>
                                            </td>
                                            <td style="font-size:0.82rem; color:var(--text-muted);">
                                                <?= $lastTs ? date('M j, Y h:i A', $lastTs) : '—' ?>
                                            </td>
                                            <td style="text-align:right;">
                                                <div style="display:inline-flex; gap:6px;">
                                                    <a href="<?= url('admin/products/create?name=' . urlencode($zq['keyword'])) ?>" style="background:var(--brand-blue); color:#fff; padding:6px 12px; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                        Add Garment
                                                    </a>
                                                    <a href="<?= url('admin/search-analytics/term/' . $termEnc) ?>" style="background:none; border:1px solid var(--border-color); color:var(--text-secondary); padding:6px 10px; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:600; text-decoration:none;">
                                                        Analyze
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <!-- ======================================================== -->
                <!-- TAB 3: LIVE ACTIVITY STREAM                              -->
                <!-- ======================================================== -->
                <?php elseif ($curTab === 'stream'): ?>
                    <?php if (empty($logs)): ?>
                        <div class="empty-state" style="padding:48px 24px; text-align:center;">
                            <div style="width:56px; height:56px; border-radius:50%; background:var(--bg-surface-secondary); display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
                            </div>
                            <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">No Search Events Recorded</h3>
                            <p style="color:var(--text-muted); font-size:0.88rem; max-width:420px; margin:0 auto;">No search queries match the active filter criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Search Keyword</th>
                                        <th style="text-align:center;">Yield / Results</th>
                                        <th>Shopper Profile</th>
                                        <th style="text-align:center;">Device</th>
                                        <th>IP Address</th>
                                        <th>Searched At</th>
                                        <th style="text-align:right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log):
                                        $ts = validTs($log['searched_at']);
                                        $isZero = (int)$log['results_count'] === 0;
                                        $logEnc = encrypt_id($log['id']);
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('admin/search-analytics/term/' . $logEnc) ?>" style="font-weight:700; color:var(--text-primary); text-decoration:none;" onmouseover="this.style.color='var(--brand-blue)'" onmouseout="this.style.color='var(--text-primary)'">
                                                    &ldquo;<?= e($log['keyword']) ?>&rdquo;
                                                </a>
                                            </td>
                                            <td style="text-align:center;">
                                                <?php if ($isZero): ?>
                                                    <span style="background:var(--brand-orange-light); color:var(--brand-orange); font-size:0.72rem; font-weight:700; padding:2px 8px; border-radius:var(--radius-full);">
                                                        0 items (Gap)
                                                    </span>
                                                <?php else: ?>
                                                    <span style="background:var(--status-success-bg); color:var(--status-success); font-size:0.72rem; font-weight:700; padding:2px 8px; border-radius:var(--radius-full);">
                                                        <?= $log['results_count'] ?> items
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($log['customer_id'])): ?>
                                                    <div style="font-weight:600; color:var(--text-primary); font-size:0.86rem;"><?= e($log['customer_name'] ?? 'Member #' . $log['customer_id']) ?></div>
                                                    <div style="font-size:0.75rem; color:var(--text-muted);"><?= e($log['customer_email'] ?? '') ?></div>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-size:0.82rem; font-style:italic;">Guest Shopper</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:center;">
                                                <span style="background:var(--bg-surface-secondary); color:var(--text-secondary); font-size:0.72rem; padding:2px 8px; border-radius:var(--radius-full); text-transform:capitalize;">
                                                    <?= e($log['device_type'] ?? 'desktop') ?>
                                                </span>
                                            </td>
                                            <td style="font-size:0.8rem; color:var(--text-muted); font-family:monospace;">
                                                <?= e($log['ip_address'] ?? '—') ?>
                                            </td>
                                            <td style="font-size:0.82rem; color:var(--text-muted);">
                                                <?= $ts ? date('M j, Y h:i A', $ts) : '—' ?>
                                            </td>
                                            <td style="text-align:right;">
                                                <form method="POST" action="<?= url('admin/search-analytics/delete') ?>" onsubmit="return confirm('Delete this search log entry?');" style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="log_id" value="<?= $logEnc ?>">
                                                    <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;" title="Delete log" onmouseover="this.style.color='var(--status-danger)'" onmouseout="this.style.color='var(--text-muted)'">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </form>
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
                                    Showing page <strong><?= $pagination['current_page'] ?></strong> of <strong><?= $pagination['total_pages'] ?></strong> (<?= number_format($pagination['total']) ?> entries)
                                </div>
                                <div style="display:flex; gap:6px;">
                                    <?php if ($pagination['has_prev']): ?>
                                        <a href="<?= url('admin/search-analytics?tab=stream&period=' . $curPeriod . '&page=' . ($pagination['current_page'] - 1) . '&search=' . urlencode($filters['search'])) ?>" style="padding:6px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.82rem; color:var(--text-primary); text-decoration:none;">
                                            &larr; Previous
                                        </a>
                                    <?php endif; ?>

                                    <?php for ($p = max(1, $pagination['current_page'] - 2); $p <= min($pagination['total_pages'], $pagination['current_page'] + 2); $p++): ?>
                                        <a href="<?= url('admin/search-analytics?tab=stream&period=' . $curPeriod . '&page=' . $p . '&search=' . urlencode($filters['search'])) ?>" style="padding:6px 12px; border-radius:var(--radius-sm); font-size:0.82rem; text-decoration:none; <?= $p === $pagination['current_page'] ? 'background:var(--brand-blue); color:#fff; font-weight:700;' : 'border:1px solid var(--border-color); color:var(--text-primary);' ?>">
                                            <?= $p ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <a href="<?= url('admin/search-analytics?tab=stream&period=' . $curPeriod . '&page=' . ($pagination['current_page'] + 1) . '&search=' . urlencode($filters['search'])) ?>" style="padding:6px 12px; border:1px solid var(--border-color); border-radius:var(--radius-sm); font-size:0.82rem; color:var(--text-primary); text-decoration:none;">
                                            Next &rarr;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

            </div>

        </main>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: LIVE STORE SEARCH SIMULATOR                                        -->
<!-- ========================================================================= -->
<div id="simulatorModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(4px); padding:20px;">
    <div style="background:var(--bg-surface); width:100%; max-width:640px; border-radius:var(--radius-md); box-shadow:0 20px 40px rgba(0,0,0,0.25); overflow:hidden; border:1px solid var(--border-color);">
        <div style="padding:20px 24px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:var(--radius-sm); background:rgba(45,130,255,0.1); color:var(--brand-blue); display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div>
                    <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:0;">Catalog Search Simulator</h3>
                    <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">Test how customer store queries perform in real time</p>
                </div>
            </div>
            <button type="button" onclick="closeSimulatorModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.4rem; cursor:pointer;">&times;</button>
        </div>

        <div style="padding:24px;">
            <div style="display:flex; gap:10px; margin-bottom:14px;">
                <input type="text" id="simKeywordInput" class="form-input" placeholder="Type luxury garment, e.g., Sherwani, Silk Kurta, Tuxedo..." style="flex:1;" onkeydown="if(event.key==='Enter'){event.preventDefault(); executeSimulation();}">
                <button type="button" onclick="executeSimulation()" style="background:var(--gradient-primary); color:#fff; border:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:700; font-size:0.88rem; cursor:pointer;">
                    Simulate
                </button>
            </div>

            <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px;">
                <input type="checkbox" id="simLogCheckbox" style="accent-color:var(--brand-blue);">
                <label for="simLogCheckbox" style="font-size:0.82rem; color:var(--text-secondary); cursor:pointer;">Log this simulation inquiry in search analytics</label>
            </div>

            <!-- Results container -->
            <div id="simResultsArea" style="max-height:300px; overflow-y:auto; border-radius:var(--radius-sm); border:1px solid var(--border-color-light); padding:12px; background:var(--bg-surface-secondary); display:none;">
                <!-- dynamic -->
            </div>
        </div>

        <div style="padding:14px 24px; background:var(--bg-surface-secondary); border-top:1px solid var(--border-color); display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeSimulatorModal()" style="background:var(--bg-surface); border:1px solid var(--border-color); padding:8px 16px; border-radius:var(--radius-sm); font-size:0.85rem; font-weight:600; color:var(--text-secondary); cursor:pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: PURGE OLD LOGS                                                     -->
<!-- ========================================================================= -->
<div id="purgeModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(4px); padding:20px;">
    <div style="background:var(--bg-surface); width:100%; max-width:440px; border-radius:var(--radius-md); box-shadow:0 20px 40px rgba(0,0,0,0.25); overflow:hidden; border:1px solid var(--border-color);">
        <form method="POST" action="<?= url('admin/search-analytics/clear-old') ?>">
            <?= csrf_field() ?>
            <div style="padding:20px 24px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:0;">Purge Retention Logs</h3>
                <button type="button" onclick="closePurgeModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.4rem; cursor:pointer;">&times;</button>
            </div>
            <div style="padding:24px;">
                <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:16px; line-height:1.5;">
                    Optimize database storage by clearing historical search inquiries older than the specified duration:
                </p>
                <div class="form-group">
                    <label class="form-label" style="font-size:0.82rem; font-weight:600;">Purge records older than:</label>
                    <select name="days" class="form-input">
                        <option value="30">Older than 30 Days</option>
                        <option value="60">Older than 60 Days</option>
                        <option value="90" selected>Older than 90 Days (Recommended)</option>
                        <option value="180">Older than 180 Days</option>
                    </select>
                </div>
            </div>
            <div style="padding:14px 24px; background:var(--bg-surface-secondary); border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closePurgeModal()" style="background:var(--bg-surface); border:1px solid var(--border-color); padding:8px 16px; border-radius:var(--radius-sm); font-size:0.85rem; font-weight:600; color:var(--text-secondary); cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" style="background:var(--status-danger); color:#fff; border:none; padding:8px 16px; border-radius:var(--radius-sm); font-size:0.85rem; font-weight:700; cursor:pointer;">
                    Confirm Purge
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Export dropdown toggle
function toggleExportMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('exportMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function() {
    const menu = document.getElementById('exportMenu');
    if (menu) menu.style.display = 'none';
});

// Chart tooltip
function showChartTooltip(e, date, line1, line2) {
    const tt = document.getElementById('chartTooltip');
    const container = document.querySelector('.chart-container');
    const rect = container.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    document.getElementById('tooltipDate').textContent = date;
    document.getElementById('tooltipLine1').textContent = line1;
    document.getElementById('tooltipLine2').textContent = line2;
    tt.style.left = x + 'px';
    tt.style.top = y + 'px';
    tt.style.display = 'block';
}
function hideChartTooltip() {
    const tt = document.getElementById('chartTooltip');
    if (tt) tt.style.display = 'none';
}

// Simulator Modal
function openSimulatorModal() {
    const m = document.getElementById('simulatorModal');
    m.style.display = 'flex';
    document.getElementById('simKeywordInput').focus();
}
function closeSimulatorModal() {
    document.getElementById('simulatorModal').style.display = 'none';
}
function testSimulatorQuery(keyword) {
    document.getElementById('simKeywordInput').value = keyword;
    openSimulatorModal();
    executeSimulation();
}

function executeSimulation() {
    const kw = document.getElementById('simKeywordInput').value.trim();
    if (!kw) return;

    const logIt = document.getElementById('simLogCheckbox').checked ? 1 : 0;
    const resArea = document.getElementById('simResultsArea');
    resArea.style.display = 'block';
    resArea.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-muted); font-size:0.85rem;">Searching catalog for "' + kw + '"...</div>';

    const formData = new FormData();
    formData.append('keyword', kw);
    formData.append('log_simulation', logIt);

    fetch('<?= url("admin/search-analytics/simulate") ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.total === 0) {
                resArea.innerHTML = `
                    <div style="text-align:center; padding:24px 16px;">
                        <div style="color:var(--brand-orange); font-size:1.6rem; margin-bottom:8px;">⚠️</div>
                        <div style="font-weight:700; color:var(--text-primary); font-size:0.95rem; margin-bottom:4px;">0 Catalog Items Matched</div>
                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:12px;">This inquiry represents a customer zero-result gap!</p>
                        <a href="<?= url('admin/products/create') ?>?name="` + encodeURIComponent(kw) + `" style="background:var(--brand-blue); color:#fff; padding:6px 14px; border-radius:6px; font-size:0.8rem; font-weight:700; text-decoration:none; display:inline-block;">+ Add Garment Now</a>
                    </div>
                `;
            } else {
                let html = '<div style="font-size:0.82rem; font-weight:700; color:var(--status-success); margin-bottom:10px;">Found ' + data.total + ' catalog products:</div><div style="display:flex; flex-direction:column; gap:8px;">';
                data.products.forEach(p => {
                    html += `
                        <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-surface); padding:8px 12px; border-radius:6px; border:1px solid var(--border-color);">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:36px; height:36px; border-radius:4px; background:var(--bg-surface-secondary); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:var(--text-muted);">
                                    👗
                                </div>
                                <div>
                                    <div style="font-weight:600; font-size:0.85rem; color:var(--text-primary);">${p.name}</div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">SKU: ${p.sku} &bull; ${p.price}</div>
                                </div>
                            </div>
                            <a href="${p.view_url}" target="_blank" style="font-size:0.75rem; color:var(--brand-blue); font-weight:600; text-decoration:none;">View &rarr;</a>
                        </div>
                    `;
                });
                html += '</div>';
                resArea.innerHTML = html;
            }
        } else {
            resArea.innerHTML = '<div style="color:var(--status-danger); padding:12px; font-size:0.85rem;">Error: ' + data.message + '</div>';
        }
    })
    .catch(err => {
        resArea.innerHTML = '<div style="color:var(--status-danger); padding:12px; font-size:0.85rem;">Connection error. Please try again.</div>';
    });
}

// Purge Modal
function openPurgeModal() {
    document.getElementById('purgeModal').style.display = 'flex';
}
function closePurgeModal() {
    document.getElementById('purgeModal').style.display = 'none';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
