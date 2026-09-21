<?php
$title      = $title ?? 'Customer Reviews & Moderation | Jiyaji LX Staff Portal';
$reviews    = $reviews ?? [];
$filters    = $filters ?? [];
$pagination = $pagination ?? ['has_prev' => false, 'has_next' => false, 'current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$kpis       = $kpis ?? [];
$canModerate = $canModerate ?? false;

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #D97706; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <span style="color: #64748B;">Customers &amp; Support</span>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Customer Reviews</span>
            </div>

            <!-- Hero Banner -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #78350F 55%, #D97706 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.25);">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #FEF3C7; margin-bottom: 8px; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        Customer Sentiment &bull; Verified Buyer Testimonials &bull; Brand Integrity
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">Customer Reviews &amp; Moderation</h1>
                    <p style="font-size: 0.88rem; color: #FDE68A; margin: 0;">
                        Moderate shopper ratings, authenticate verified buyers, inspect buyer apparel photos, and protect luxury brand standards.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <?php if (($kpis['pending_reviews'] ?? 0) > 0): ?>
                        <div style="background: rgba(245, 158, 11, 0.25); border: 1px solid rgba(245, 158, 11, 0.4); padding: 7px 14px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; color: #FEF3C7;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #F59E0B; display: inline-block; box-shadow: 0 0 8px #F59E0B;"></span>
                            <?= number_format($kpis['pending_reviews']) ?> Pending Review<?= $kpis['pending_reviews'] > 1 ? 's' : '' ?>
                        </div>
                    <?php endif; ?>

                    <a href="<?= url('portal/reviews/export?' . http_build_query(array_filter($filters, fn($v) => $v !== '' && $v !== 'all'))) ?>" 
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
                <!-- Total Reviews -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #D97706; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Total Reviews</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['total_reviews'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Shopper submissions</div>
                </div>

                <!-- Pending Queue -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #F59E0B; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Pending Queue</div>
                        <?php if (($kpis['pending_reviews'] ?? 0) > 0): ?>
                            <span style="background: rgba(245, 158, 11, 0.15); color: #B45309; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">Action Req.</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #D97706; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['pending_reviews'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Awaiting moderation</div>
                </div>

                <!-- Approved & Live -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #10B981; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #10B981; text-transform: uppercase; letter-spacing: 0.05em;">Approved &amp; Live</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['approved_reviews'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Live on storefront</div>
                </div>

                <!-- Flagged for Audit -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #EF4444; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #EF4444; text-transform: uppercase; letter-spacing: 0.05em;">Flagged for Audit</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['flagged_reviews'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Requires attention</div>
                </div>

                <!-- Average Storefront Rating -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #EAB308; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #CA8A04; text-transform: uppercase; letter-spacing: 0.05em;">Storefront Score</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em; display: flex; align-items: center; gap: 4px;">
                        <span><?= number_format($kpis['average_store_rating'] ?? 5.0, 1) ?></span>
                        <span style="font-size: 1.2rem; color: #EAB308;">★</span>
                    </div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">
                        <?= number_format($kpis['verified_reviews'] ?? 0) ?> verified &bull; <?= number_format($kpis['photo_reviews'] ?? 0) ?> photos
                    </div>
                </div>
            </div>

            <!-- Segment Tabs Bar -->
            <?php
            $currentStatus = $filters['status'] ?? 'all';
            $tabs = [
                'all'      => ['label' => 'All Reviews', 'count' => $kpis['total_reviews'] ?? 0, 'status' => 'all'],
                'pending'  => ['label' => 'Pending Queue', 'count' => $kpis['pending_reviews'] ?? 0, 'status' => 'pending'],
                'approved' => ['label' => 'Approved', 'count' => $kpis['approved_reviews'] ?? 0, 'status' => 'approved'],
                'flagged'  => ['label' => 'Flagged', 'count' => $kpis['flagged_reviews'] ?? 0, 'status' => 'flagged'],
                'rejected' => ['label' => 'Rejected', 'count' => $kpis['rejected_reviews'] ?? 0, 'status' => 'rejected'],
            ];
            ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
                <?php foreach ($tabs as $tKey => $tData):
                    $tabParams = $filters;
                    $tabParams['status'] = $tData['status'];
                    $tabParams['page'] = 1;
                    $isActive = ($tKey === 'all' && $currentStatus === 'all') || ($currentStatus === $tData['status']);
                ?>
                    <a href="<?= url('portal/reviews?' . http_build_query($tabParams)) ?>" 
                       style="padding: 8px 16px; border-radius: 999px; font-size: 0.82rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s ease; <?= $isActive ? 'background: #0F172A; color: #FFFFFF; box-shadow: 0 2px 6px rgba(15,23,42,0.15);' : 'background: #FFFFFF; color: #64748B; border: 1px solid #E2E8F0;' ?>">
                        <span><?= $tData['label'] ?></span>
                        <span style="background: <?= $isActive ? 'rgba(255,255,255,0.2)' : '#F1F5F9' ?>; color: <?= $isActive ? '#FFFFFF' : '#475569' ?>; font-size: 0.72rem; padding: 2px 7px; border-radius: 999px;">
                            <?= number_format($tData['count']) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; margin-bottom: 24px; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <form action="<?= url('portal/reviews') ?>" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? 'all') ?>">

                    <!-- Search -->
                    <div style="flex: 2; min-width: 240px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                            placeholder="Search by shopper name, email, garment, words in review..."
                            style="width: 100%; height: 40px; padding: 0 14px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none; transition: border-color 0.2s;"
                            onfocus="this.style.borderColor='#D97706';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>

                    <!-- Star Rating Filter -->
                    <div style="flex: 1; min-width: 145px;">
                        <select name="rating" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['rating'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Star Ratings</option>
                            <option value="5" <?= (($filters['rating'] ?? '') === '5') ? 'selected' : '' ?>>★★★★★ (5 Stars)</option>
                            <option value="4" <?= (($filters['rating'] ?? '') === '4') ? 'selected' : '' ?>>★★★★☆ (4 Stars)</option>
                            <option value="3" <?= (($filters['rating'] ?? '') === '3') ? 'selected' : '' ?>>★★★☆☆ (3 Stars)</option>
                            <option value="2" <?= (($filters['rating'] ?? '') === '2') ? 'selected' : '' ?>>★★☆☆☆ (2 Stars)</option>
                            <option value="1" <?= (($filters['rating'] ?? '') === '1') ? 'selected' : '' ?>>★☆☆☆☆ (1 Star)</option>
                        </select>
                    </div>

                    <!-- Verified Buyer Filter -->
                    <div style="flex: 1; min-width: 155px;">
                        <select name="verified" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['verified'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Verification</option>
                            <option value="verified_only" <?= (($filters['verified'] ?? '') === 'verified_only') ? 'selected' : '' ?>>Verified Buyers Only</option>
                        </select>
                    </div>

                    <!-- Photo Reviews Filter -->
                    <div style="flex: 1; min-width: 140px;">
                        <select name="photos" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="all" <?= (($filters['photos'] ?? 'all') === 'all') ? 'selected' : '' ?>>All Media</option>
                            <option value="with_photos" <?= (($filters['photos'] ?? '') === 'with_photos') ? 'selected' : '' ?>>With Photos Only</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div style="flex: 1; min-width: 155px;">
                        <select name="sort" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                            <option value="newest"      <?= (($filters['sort'] ?? '') === 'newest') ? 'selected' : '' ?>>Newest Submissions</option>
                            <option value="oldest"      <?= (($filters['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                            <option value="rating_high" <?= (($filters['sort'] ?? '') === 'rating_high') ? 'selected' : '' ?>>Highest Rating (5★)</option>
                            <option value="rating_low"  <?= (($filters['sort'] ?? '') === 'rating_low') ? 'selected' : '' ?>>Lowest Rating (1★)</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" 
                                style="height: 40px; padding: 0 18px; background: #0F172A; color: #fff; border: none; border-radius: 8px; font-size: 0.84rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;"
                                onmouseover="this.style.background='#1E293B';" onmouseout="this.style.background='#0F172A';">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Filter
                        </button>
                        <a href="<?= url('portal/reviews') ?>" 
                           style="height: 40px; padding: 0 14px; background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.84rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;" 
                           title="Reset All Filters">
                            ✕
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Moderation Form & Table -->
            <form action="<?= url('portal/reviews/bulk') ?>" method="POST" id="bulkReviewsForm">
                <?= csrf_field() ?>

                <!-- Floating Bulk Moderation Toolbar -->
                <?php if ($canModerate): ?>
                    <div id="bulkActionBar" style="display: none; background: #0F172A; color: #FFFFFF; border-radius: 12px; padding: 12px 20px; margin-bottom: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.2); align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="background: rgba(255,255,255,0.15); font-weight: 800; font-size: 0.84rem; color: #FDE68A; padding: 3px 10px; border-radius: 999px;" id="selectedCount">0</span>
                            <span style="font-size: 0.86rem; color: #E2E8F0; font-weight: 600;">review(s) selected</span>
                        </div>

                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="submit" name="bulk_status" value="approved" 
                                    style="background: #10B981; color: #FFFFFF; border: none; padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                Approve Selected
                            </button>
                            <button type="submit" name="bulk_status" value="rejected" 
                                    style="background: #EF4444; color: #FFFFFF; border: none; padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Reject Selected
                            </button>
                            <button type="submit" name="bulk_status" value="flagged" 
                                    style="background: #F59E0B; color: #FFFFFF; border: none; padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                Flag Selected
                            </button>
                            <button type="submit" name="bulk_status" value="pending" 
                                    style="background: rgba(255,255,255,0.12); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.25); padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
                                Reset to Pending
                            </button>
                            <button type="submit" name="bulk_status" value="delete" onclick="return confirm('Permanently delete selected reviews? This action cannot be undone.');"
                                    style="background: rgba(239, 68, 68, 0.2); color: #FCA5A5; border: 1px solid rgba(239, 68, 68, 0.4); padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer;">
                                Delete Selected
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reviews Data Ledger Table -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                            <thead>
                                <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.74rem; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.04em;">
                                    <?php if ($canModerate): ?>
                                        <th style="padding: 14px 16px; width: 44px; text-align: center;">
                                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="cursor: pointer; width: 16px; height: 16px; accent-color: #D97706;">
                                        </th>
                                    <?php endif; ?>
                                    <th style="padding: 14px 16px; width: 140px;">Rating &amp; Date</th>
                                    <th style="padding: 14px 16px; min-width: 180px;">Garment Product</th>
                                    <th style="padding: 14px 16px; min-width: 190px;">Shopper Profile</th>
                                    <th style="padding: 14px 16px; min-width: 260px;">Feedback &amp; Photos</th>
                                    <th style="padding: 14px 16px; width: 130px; text-align: center;">Status</th>
                                    <th style="padding: 14px 16px; width: 150px; text-align: right;">Moderation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reviews)): ?>
                                    <tr>
                                        <td colspan="<?= $canModerate ? 7 : 6 ?>" style="padding: 64px 20px; text-align: center; color: #64748B;">
                                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; font-size: 1.8rem;">
                                                ★
                                            </div>
                                            <div style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No Reviews Found</div>
                                            <div style="font-size: 0.85rem; max-width: 440px; margin: 0 auto; color: #64748B;">
                                                There are currently no reviews matching your filter parameters. Try resetting filters or switching queue tabs.
                                            </div>
                                            <div style="margin-top: 16px;">
                                                <a href="<?= url('portal/reviews') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; background: #0F172A; color: #fff; font-size: 0.82rem; font-weight: 700; text-decoration: none;">
                                                    Reset All Filters
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $rev): 
                                        $status = strtolower($rev['status'] ?? 'pending');
                                        $rating = (int)($rev['rating'] ?? 5);
                                    ?>
                                        <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s ease;" 
                                            class="review-row" 
                                            id="review-row-<?= $rev['encrypted_id'] ?>"
                                            onmouseover="this.style.background='#F8FAFC';" 
                                            onmouseout="this.style.background='#FFFFFF';">
                                            
                                            <!-- Bulk Checkbox -->
                                            <?php if ($canModerate): ?>
                                                <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                                    <input type="checkbox" name="selected_reviews[]" value="<?= $rev['encrypted_id'] ?>" class="review-checkbox" onchange="updateBulkBar()" style="cursor: pointer; width: 16px; height: 16px; accent-color: #D97706;">
                                                </td>
                                            <?php endif; ?>

                                            <!-- Rating & Date -->
                                            <td style="padding: 14px 16px; vertical-align: top;">
                                                <div style="display: flex; align-items: center; gap: 3px;">
                                                    <span style="color: #EAB308; font-size: 0.95rem; letter-spacing: 1px;">
                                                        <?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?>
                                                    </span>
                                                    <span style="font-size: 0.82rem; font-weight: 800; color: #0F172A; margin-left: 2px;">
                                                        <?= $rating ?>.0
                                                    </span>
                                                </div>
                                                <div style="font-size: 0.74rem; color: #64748B; margin-top: 4px;">
                                                    <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                                                </div>
                                                <div style="font-size: 0.68rem; color: #94A3B8;">
                                                    <?= date('h:i A', strtotime($rev['created_at'])) ?>
                                                </div>
                                            </td>

                                            <!-- Garment Product -->
                                            <td style="padding: 14px 16px; vertical-align: top;">
                                                <div style="display: flex; gap: 10px; align-items: flex-start;">
                                                    <img src="<?= htmlspecialchars(image_url($rev['product_image'] ?? '')) ?>" 
                                                         alt="<?= htmlspecialchars($rev['product_name'] ?? 'Garment') ?>"
                                                         style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #E2E8F0; flex-shrink: 0; background: #F8FAFC;">
                                                    <div>
                                                        <a href="<?= url('portal/products/' . ($rev['product_encrypted_id'] ?? '') . '/edit') ?>" 
                                                           style="font-weight: 700; font-size: 0.85rem; color: #0F172A; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;"
                                                           title="<?= htmlspecialchars($rev['product_name'] ?? '') ?>"
                                                           onmouseover="this.style.color='#D97706';"
                                                           onmouseout="this.style.color='#0F172A';">
                                                            <?= htmlspecialchars($rev['product_name'] ?? 'Garment Attire') ?>
                                                        </a>
                                                        <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">
                                                            Slug: <?= htmlspecialchars($rev['product_slug'] ?? '') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Shopper Profile -->
                                            <td style="padding: 14px 16px; vertical-align: top;">
                                                <div style="display: flex; gap: 9px; align-items: flex-start;">
                                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #FDE68A, #D97706); color: #78350F; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.78rem; flex-shrink: 0;">
                                                        <?= strtoupper(substr($rev['customer_name'] ?? 'C', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <a href="<?= url('portal/customers/' . ($rev['customer_encrypted_id'] ?? '')) ?>" 
                                                           style="font-weight: 700; font-size: 0.85rem; color: #0F172A; text-decoration: none;"
                                                           onmouseover="this.style.color='#0284C7';"
                                                           onmouseout="this.style.color='#0F172A';">
                                                            <?= htmlspecialchars($rev['customer_name'] ?? 'Shopper') ?>
                                                        </a>
                                                        <div style="font-size: 0.74rem; color: #64748B; margin-top: 1px;">
                                                            <?= htmlspecialchars($rev['customer_email'] ?? '') ?>
                                                        </div>
                                                        <div style="margin-top: 5px;">
                                                            <?php if (!empty($rev['is_verified_buyer'])): ?>
                                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 999px; font-size: 0.68rem; font-weight: 700; background: rgba(2, 132, 199, 0.1); color: #0284C7;">
                                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                                                    Verified Buyer
                                                                </span>
                                                            <?php else: ?>
                                                                <span style="font-size: 0.7rem; color: #94A3B8;">
                                                                    Unverified / Guest
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Feedback & Photos -->
                                            <td style="padding: 14px 16px; vertical-align: top;">
                                                <?php if (!empty($rev['title'])): ?>
                                                    <div style="font-weight: 700; font-size: 0.88rem; color: #0F172A; margin-bottom: 3px;">
                                                        <?= htmlspecialchars($rev['title']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div style="font-size: 0.82rem; color: #475569; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                    <?= nl2br(htmlspecialchars($rev['body'] ?? '')) ?>
                                                </div>

                                                <!-- Review Photos Strip -->
                                                <?php if (!empty($rev['photos'])): ?>
                                                    <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; align-items: center;">
                                                        <?php foreach ($rev['photos'] as $pUrl): ?>
                                                            <img src="<?= htmlspecialchars(image_url($pUrl)) ?>" 
                                                                 alt="Shopper Photo" 
                                                                 style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid #CBD5E1; cursor: pointer; transition: transform 0.15s ease;"
                                                                 onclick="inspectPhoto('<?= htmlspecialchars(image_url($pUrl)) ?>')"
                                                                 onmouseover="this.style.transform='scale(1.08)';"
                                                                 onmouseout="this.style.transform='scale(1)';"
                                                                 title="Click to view full photo">
                                                        <?php endforeach; ?>
                                                        <span style="font-size: 0.7rem; color: #64748B; font-weight: 600;">
                                                            <?= count($rev['photos']) ?> photo<?= count($rev['photos']) > 1 ? 's' : '' ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Moderation Status Badge -->
                                            <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                                <?php
                                                $badgeClass = match($status) {
                                                    'approved' => 'background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                                                    'rejected' => 'background: rgba(239, 68, 68, 0.12); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.25);',
                                                    'flagged'  => 'background: rgba(245, 158, 11, 0.12); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.25);',
                                                    default    => 'background: rgba(120, 53, 15, 0.1); color: #92400E; border: 1px solid rgba(120, 53, 15, 0.2);'
                                                };
                                                ?>
                                                <span id="badge-<?= $rev['encrypted_id'] ?>" style="display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; <?= $badgeClass ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>

                                                <?php if (!empty($rev['moderator_name'])): ?>
                                                    <div style="font-size: 0.68rem; color: #94A3B8; margin-top: 4px;">
                                                        by <?= htmlspecialchars($rev['moderator_name']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Moderation Actions -->
                                            <td style="padding: 14px 16px; text-align: right; vertical-align: top;">
                                                <div style="display: inline-flex; align-items: center; gap: 5px;">
                                                    <!-- Inspect Modal -->
                                                    <button type="button" 
                                                            onclick="openReviewInspectModal('<?= $rev['encrypted_id'] ?>')" 
                                                            style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E2E8F0; background: #FFFFFF; color: #475569; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;"
                                                            onmouseover="this.style.background='#F1F5F9'; this.style.color='#0F172A';"
                                                            onmouseout="this.style.background='#FFFFFF'; this.style.color='#475569';"
                                                            title="Inspect Full Review Details">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    </button>

                                                    <?php if ($canModerate): ?>
                                                        <!-- Quick Approve -->
                                                        <button type="button" 
                                                                onclick="submitQuickStatus('<?= $rev['encrypted_id'] ?>', 'approved')" 
                                                                style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.08); color: #059669; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;"
                                                                onmouseover="this.style.background='#10B981'; this.style.color='#FFFFFF';"
                                                                onmouseout="this.style.background='rgba(16, 185, 129, 0.08)'; this.style.color='#059669';"
                                                                title="Approve & Publish to Storefront">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                        </button>

                                                        <!-- Quick Reject -->
                                                        <button type="button" 
                                                                onclick="submitQuickStatus('<?= $rev['encrypted_id'] ?>', 'rejected')" 
                                                                style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.08); color: #DC2626; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;"
                                                                onmouseover="this.style.background='#EF4444'; this.style.color='#FFFFFF';"
                                                                onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#DC2626';"
                                                                title="Reject & Hide from Storefront">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        </button>

                                                        <!-- Quick Flag -->
                                                        <button type="button" 
                                                                onclick="submitQuickStatus('<?= $rev['encrypted_id'] ?>', 'flagged')" 
                                                                style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.08); color: #D97706; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;"
                                                                onmouseover="this.style.background='#F59E0B'; this.style.color='#FFFFFF';"
                                                                onmouseout="this.style.background='rgba(245, 158, 11, 0.08)'; this.style.color='#D97706';"
                                                                title="Flag for Compliance Audit">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                                        </button>

                                                        <!-- Delete Permanently -->
                                                        <button type="button" 
                                                                onclick="submitDeleteReview('<?= $rev['encrypted_id'] ?>')" 
                                                                style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E2E8F0; background: #FFFFFF; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;"
                                                                onmouseover="this.style.background='#FEE2E2'; this.style.color='#DC2626'; this.style.borderColor='#FCA5A5';"
                                                                onmouseout="this.style.background='#FFFFFF'; this.style.color='#94A3B8'; this.style.borderColor='#E2E8F0';"
                                                                title="Permanently Delete Review">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Bar -->
                    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid #E2E8F0; font-size: 0.84rem; color: #64748B; flex-wrap: wrap; gap: 12px; background: #F8FAFC;">
                            <div>
                                Showing <?= min($pagination['total_items'], ($pagination['offset'] ?? 0) + 1) ?> to <?= min($pagination['total_items'], ($pagination['offset'] ?? 0) + count($reviews)) ?> of <?= number_format($pagination['total_items']) ?> reviews
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <?php if ($pagination['has_prev']): 
                                    $prevParams = $filters;
                                    $prevParams['page'] = $pagination['current_page'] - 1;
                                ?>
                                    <a href="<?= url('portal/reviews?' . http_build_query($prevParams)) ?>" 
                                       style="padding: 6px 12px; border-radius: 6px; border: 1px solid #CBD5E1; background: #fff; color: #475569; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                                        &laquo; Previous
                                    </a>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): 
                                    $pageParams = $filters;
                                    $pageParams['page'] = $p;
                                    $isCur = ($p === $pagination['current_page']);
                                ?>
                                    <a href="<?= url('portal/reviews?' . http_build_query($pageParams)) ?>" 
                                       style="padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-decoration: none; min-width: 32px; text-align: center; <?= $isCur ? 'background: #0F172A; color: #fff; border: 1px solid #0F172A;' : 'background: #fff; color: #475569; border: 1px solid #CBD5E1;' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): 
                                    $nextParams = $filters;
                                    $nextParams['page'] = $pagination['current_page'] + 1;
                                ?>
                                    <a href="<?= url('portal/reviews?' . http_build_query($nextParams)) ?>" 
                                       style="padding: 6px 12px; border-radius: 6px; border: 1px solid #CBD5E1; background: #fff; color: #475569; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                                        Next &raquo;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Standalone Fallback Action Form for Single Moderate/Delete -->
<form id="singleActionForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="status" id="singleActionStatus" value="">
    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'portal/reviews') ?>">
</form>

<!-- Review Inspection Detail Modal -->
<div id="reviewInspectModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" onclick="closeReviewInspectModal()">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; padding: 28px;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #D97706; letter-spacing: 0.05em; margin-bottom: 4px;">
                    Review Dossier
                </div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin: 0;" id="modalReviewHeading">
                    Review Details
                </h2>
            </div>
            <button type="button" onclick="closeReviewInspectModal()" style="background: #F1F5F9; border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #64748B; cursor: pointer;">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div id="modalLoadingSpinner" style="text-align: center; padding: 40px; color: #64748B;">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">⏳</div>
            <div>Loading review details...</div>
        </div>

        <div id="modalContentBody" style="display: none;">
            <!-- Rating & Verified Bar -->
            <div style="background: #FFFBEB; border: 1px solid #FEF3C7; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="modalStars" style="color: #EAB308; font-size: 1.2rem; letter-spacing: 2px;"></span>
                    <span id="modalRatingScore" style="font-weight: 800; font-size: 1rem; color: #78350F;"></span>
                </div>
                <div id="modalVerifiedBadge"></div>
            </div>

            <!-- Garment Product Card -->
            <div style="border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px; display: flex; gap: 14px; align-items: center; margin-bottom: 20px; background: #F8FAFC;">
                <img id="modalProductImg" src="" alt="Product" style="width: 54px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid #E2E8F0;">
                <div style="flex: 1;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Reviewed Product</div>
                    <a id="modalProductNameLink" href="#" target="_blank" style="font-weight: 700; font-size: 0.92rem; color: #0F172A; text-decoration: none;"></a>
                    <div style="font-size: 0.74rem; color: #94A3B8; margin-top: 2px;" id="modalProductSlug"></div>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div style="border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Customer Information</div>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <a id="modalCustomerName" href="#" target="_blank" style="font-weight: 700; font-size: 0.92rem; color: #0284C7; text-decoration: none;"></a>
                        <div style="font-size: 0.8rem; color: #64748B;" id="modalCustomerEmail"></div>
                    </div>
                    <div style="font-size: 0.76rem; color: #94A3B8;" id="modalSubmissionDate"></div>
                </div>
            </div>

            <!-- Full Review Content -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Review Content</div>
                <div id="modalReviewTitle" style="font-weight: 700; font-size: 1rem; color: #0F172A; margin-bottom: 6px;"></div>
                <div id="modalReviewBody" style="font-size: 0.88rem; color: #334155; line-height: 1.6; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;"></div>
            </div>

            <!-- Photos Gallery -->
            <div id="modalPhotosSection" style="margin-bottom: 24px; display: none;">
                <div style="font-size: 0.74rem; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 8px;">Attached Shopper Photos</div>
                <div id="modalPhotosGrid" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
            </div>

            <!-- Audit Trail & Moderator Info -->
            <div style="background: #F1F5F9; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px; font-size: 0.78rem; color: #64748B; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div><strong>Current Status:</strong> <span id="modalCurrentStatus" style="font-weight: 700; text-transform: uppercase;"></span></div>
                <div><strong>Moderator:</strong> <span id="modalModeratorName">None</span></div>
                <div><strong>Moderated Date:</strong> <span id="modalModeratedDate">N/A</span></div>
            </div>

            <!-- Action Buttons Footer -->
            <?php if ($canModerate): ?>
                <div style="display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; border-top: 1px solid #E2E8F0; padding-top: 16px;">
                    <button type="button" id="modalBtnApprove" style="background: #10B981; color: #fff; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        ✓ Approve &amp; Publish
                    </button>
                    <button type="button" id="modalBtnReject" style="background: #EF4444; color: #fff; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        ✕ Reject &amp; Hide
                    </button>
                    <button type="button" id="modalBtnFlag" style="background: #F59E0B; color: #fff; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        ⚑ Flag Review
                    </button>
                    <button type="button" id="modalBtnDelete" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; padding: 9px 16px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer;">
                        Delete
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Photo Lightbox Modal -->
<div id="photoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;" onclick="closePhotoModal()">
    <div style="position: relative; max-width: 700px; max-height: 85vh; background: #000; border-radius: 12px; overflow: hidden; padding: 4px;" onclick="event.stopPropagation()">
        <img id="photoModalImg" src="" alt="Enlarged Review Photo" style="width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px; display: block;">
        <button type="button" onclick="closePhotoModal()" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: 18px; line-height: 1;">&times;</button>
    </div>
</div>

<script>
// CSRF Token for AJAX requests
const CSRF_TOKEN = '<?= csrf_token() ?>';

// Select All / Bulk toolbar logic
function toggleSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.review-checkbox:checked');
    const bar = document.getElementById('bulkActionBar');
    const countSpan = document.getElementById('selectedCount');
    if (!bar) return;

    if (checked.length > 0) {
        bar.style.display = 'flex';
        if (countSpan) countSpan.textContent = checked.length;
    } else {
        bar.style.display = 'none';
        const master = document.getElementById('selectAllCheckbox');
        if (master) master.checked = false;
    }
}

// Quick status change via AJAX with fallback
function submitQuickStatus(encId, status) {
    fetch('<?= url("portal/reviews") ?>/' + encId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            '_csrf': CSRF_TOKEN,
            'status': status
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update badge on row
            const badge = document.getElementById('badge-' + encId);
            if (badge) {
                badge.textContent = data.status_label || status.toUpperCase();
                badge.style.cssText = getBadgeStyle(status);
            }
            showToast('success', 'Status Updated', data.message || 'Review moderated successfully.');
        } else {
            fallbackStatusSubmit(encId, status);
        }
    })
    .catch(err => {
        fallbackStatusSubmit(encId, status);
    });
}

function fallbackStatusSubmit(encId, status) {
    const form = document.getElementById('singleActionForm');
    form.action = '<?= url("portal/reviews") ?>/' + encId + '/status';
    document.getElementById('singleActionStatus').value = status;
    form.submit();
}

function submitDeleteReview(encId) {
    if (confirm('Permanently delete this customer review? This will also update the product rating.')) {
        fetch('<?= url("portal/reviews") ?>/' + encId + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                '_csrf': CSRF_TOKEN
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('review-row-' + encId);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => row.remove(), 300);
                }
                showToast('success', 'Review Deleted', data.message || 'Review removed.');
            } else {
                const form = document.getElementById('singleActionForm');
                form.action = '<?= url("portal/reviews") ?>/' + encId + '/delete';
                form.submit();
            }
        })
        .catch(err => {
            const form = document.getElementById('singleActionForm');
            form.action = '<?= url("portal/reviews") ?>/' + encId + '/delete';
            form.submit();
        });
    }
}

function getBadgeStyle(status) {
    switch (status.toLowerCase()) {
        case 'approved':
            return 'display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);';
        case 'rejected':
            return 'display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: rgba(239, 68, 68, 0.12); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.25);';
        case 'flagged':
            return 'display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: rgba(245, 158, 11, 0.12); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.25);';
        default:
            return 'display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: rgba(120, 53, 15, 0.1); color: #92400E; border: 1px solid rgba(120, 53, 15, 0.2);';
    }
}

// Lightbox modal logic
function inspectPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    document.getElementById('photoModal').style.display = 'flex';
}

function closePhotoModal() {
    document.getElementById('photoModal').style.display = 'none';
    document.getElementById('photoModalImg').src = '';
}

// Detailed Inspection Modal
let currentInspectEncId = null;

function openReviewInspectModal(encId) {
    currentInspectEncId = encId;
    const modal = document.getElementById('reviewInspectModal');
    const spinner = document.getElementById('modalLoadingSpinner');
    const content = document.getElementById('modalContentBody');
    
    modal.style.display = 'flex';
    spinner.style.display = 'block';
    content.style.display = 'none';

    fetch('<?= url("portal/reviews") ?>/' + encId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.review) {
            alert(data.message || 'Could not load review details.');
            closeReviewInspectModal();
            return;
        }

        const rev = data.review;
        spinner.style.display = 'none';
        content.style.display = 'block';

        // Populate modal fields
        document.getElementById('modalReviewHeading').textContent = rev.title || ('Review #' + rev.id);
        
        const rating = parseInt(rev.rating || 5);
        document.getElementById('modalStars').textContent = '★'.repeat(rating) + '☆'.repeat(5 - rating);
        document.getElementById('modalRatingScore').textContent = rating + '.0 out of 5 Stars';

        const verifiedBadge = document.getElementById('modalVerifiedBadge');
        if (rev.is_verified_buyer) {
            verifiedBadge.innerHTML = '<span style="background: rgba(2, 132, 199, 0.1); color: #0284C7; font-size: 0.74rem; font-weight: 700; padding: 4px 10px; border-radius: 999px;">✓ Verified Buyer</span>';
        } else {
            verifiedBadge.innerHTML = '<span style="font-size: 0.74rem; color: #94A3B8;">Unverified Buyer</span>';
        }

        document.getElementById('modalProductImg').src = rev.product_image_url || '';
        const prodLink = document.getElementById('modalProductNameLink');
        prodLink.textContent = rev.product_name || 'Garment';
        prodLink.href = rev.product_portal_url || '#';
        document.getElementById('modalProductSlug').textContent = 'Slug: ' + (rev.product_slug || '');

        const custLink = document.getElementById('modalCustomerName');
        custLink.textContent = rev.customer_name || 'Customer';
        custLink.href = rev.customer_portal_url || '#';
        document.getElementById('modalCustomerEmail').textContent = rev.customer_email || '';
        document.getElementById('modalSubmissionDate').textContent = 'Submitted ' + (rev.formatted_date || '');

        document.getElementById('modalReviewTitle').textContent = rev.title || '';
        document.getElementById('modalReviewBody').textContent = rev.body || '';

        // Photos gallery
        const photosSec = document.getElementById('modalPhotosSection');
        const photosGrid = document.getElementById('modalPhotosGrid');
        photosGrid.innerHTML = '';
        if (rev.resolved_photos && rev.resolved_photos.length > 0) {
            photosSec.style.display = 'block';
            rev.resolved_photos.forEach(p => {
                const img = document.createElement('img');
                img.src = p.url;
                img.style.cssText = 'width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #CBD5E1; cursor: pointer;';
                img.onclick = () => inspectPhoto(p.url);
                photosGrid.appendChild(img);
            });
        } else {
            photosSec.style.display = 'none';
        }

        // Moderation details
        document.getElementById('modalCurrentStatus').textContent = rev.status || 'pending';
        document.getElementById('modalModeratorName').textContent = rev.moderator_name || 'System / None';
        document.getElementById('modalModeratedDate').textContent = rev.moderated_date || 'Not yet moderated';

        // Action Buttons binding
        bindModalAction('modalBtnApprove', () => { submitQuickStatus(encId, 'approved'); closeReviewInspectModal(); });
        bindModalAction('modalBtnReject',  () => { submitQuickStatus(encId, 'rejected'); closeReviewInspectModal(); });
        bindModalAction('modalBtnFlag',    () => { submitQuickStatus(encId, 'flagged');  closeReviewInspectModal(); });
        bindModalAction('modalBtnDelete',  () => { submitDeleteReview(encId); closeReviewInspectModal(); });
    })
    .catch(err => {
        alert('Error communicating with server.');
        closeReviewInspectModal();
    });
}

function bindModalAction(btnId, handler) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    newBtn.addEventListener('click', handler);
}

function closeReviewInspectModal() {
    document.getElementById('reviewInspectModal').style.display = 'none';
    currentInspectEncId = null;
}

// Simple dynamic toast banner
function showToast(type, title, message) {
    const toast = document.createElement('div');
    const color = type === 'success' ? '#10B981' : '#EF4444';
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #0F172A;
        color: #FFFFFF;
        padding: 14px 20px;
        border-radius: 12px;
        border-left: 4px solid ${color};
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        z-index: 10001;
        font-size: 0.86rem;
        display: flex;
        flex-direction: column;
        gap: 2px;
        animation: slideInToast 0.25s ease-out;
    `;
    toast.innerHTML = `<strong style="color: ${color};">${title}</strong><span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
</script>

<style>
@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
