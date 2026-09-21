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
                        <span>Reviews Moderation</span>
                    </div>
                    <h1 class="welcome-title">Verified-Buyer Reviews Moderation</h1>
                    <p class="welcome-subtitle">Moderate customer ratings (1–5 Stars), authenticate verified purchases, publish testimonials, and protect brand integrity.</p>
                </div>
            </div>

            <!-- Reviews KPI Summary Cards -->
            <div class="catalog-kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Testimonials</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total_reviews']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-orange);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-orange); text-transform: uppercase;">Pending Moderation</div>
                        <?php if ($kpis['pending_reviews'] > 0): ?>
                            <span style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 0.7rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">Action Required</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($kpis['pending_reviews']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--status-success);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--status-success); text-transform: uppercase;">Approved & Live</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px;"><?= number_format($kpis['approved_reviews']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid #ef4444;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #ef4444; text-transform: uppercase;">Flagged for Audit</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #ef4444; margin-top: 4px;"><?= number_format($kpis['flagged_reviews']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid #eab308;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #ca8a04; text-transform: uppercase;">Storefront Rating</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #ca8a04; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                        <span><?= number_format($kpis['average_store_rating'], 1) ?></span>
                        <span style="font-size: 1.1rem; color: #eab308;">★</span>
                    </div>
                </div>
            </div>

            <!-- Quick Segment Filter Tabs -->
            <?php
            $currentStatus   = $filters['status'] ?? 'all';
            $currentRating   = $filters['rating'] ?? 'all';
            $currentVerified = $filters['verified'] ?? 'all';
            ?>
            <div class="catalog-tabs-bar" style="margin-bottom: 20px;">
                <?php
                $tabs = [
                    'all'      => ['label' => 'All Reviews (' . $kpis['total_reviews'] . ')', 'status' => 'all'],
                    'pending'  => ['label' => 'Pending Queue (' . $kpis['pending_reviews'] . ')', 'status' => 'pending'],
                    'approved' => ['label' => 'Approved (' . $kpis['approved_reviews'] . ')', 'status' => 'approved'],
                    'flagged'  => ['label' => 'Flagged (' . $kpis['flagged_reviews'] . ')', 'status' => 'flagged'],
                    'rejected' => ['label' => 'Rejected (' . $kpis['rejected_reviews'] . ')', 'status' => 'rejected'],
                ];
                foreach ($tabs as $tKey => $tData):
                    $tabParams = $filters;
                    $tabParams['status'] = $tData['status'];
                    $tabParams['page'] = 1;
                    $tabUrl = url('admin/reviews?' . http_build_query($tabParams));
                    $isActive = ($tKey === 'all' && $currentStatus === 'all')
                             || ($currentStatus === $tData['status']);
                ?>
                    <a href="<?= $tabUrl ?>" class="tab-btn <?= $isActive ? 'active' : '' ?>" style="padding: 8px 18px; font-size: 0.84rem;">
                        <?= htmlspecialchars($tData['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Filter Toolbar -->
            <div class="card-panel" style="margin-bottom: 24px; padding: 18px 22px;">
                <form action="<?= url('admin/reviews') ?>" method="GET" class="catalog-filter-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">

                    <div style="flex: 2; min-width: 240px;">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?= htmlspecialchars($filters['search']) ?>" 
                            class="form-input" 
                            placeholder="Search by client, garment, review words..."
                            style="width: 100%;"
                            id="reviewSearchInput"
                        >
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="rating" class="form-input" style="width: 100%;">
                            <option value="all" <?= $filters['rating'] === 'all' ? 'selected' : '' ?>>All Star Ratings</option>
                            <option value="5" <?= $filters['rating'] === '5' ? 'selected' : '' ?>>★★★★★ (5 Stars)</option>
                            <option value="4" <?= $filters['rating'] === '4' ? 'selected' : '' ?>>★★★★☆ (4 Stars)</option>
                            <option value="3" <?= $filters['rating'] === '3' ? 'selected' : '' ?>>★★★☆☆ (3 Stars)</option>
                            <option value="2" <?= $filters['rating'] === '2' ? 'selected' : '' ?>>★★☆☆☆ (2 Stars)</option>
                            <option value="1" <?= $filters['rating'] === '1' ? 'selected' : '' ?>>★☆☆☆☆ (1 Star)</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <select name="verified" class="form-input" style="width: 100%;">
                            <option value="all" <?= $filters['verified'] === 'all' ? 'selected' : '' ?>>All Feedback</option>
                            <option value="verified_only" <?= $filters['verified'] === 'verified_only' ? 'selected' : '' ?>>Verified Buyers Only</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 160px;">
                        <select name="sort" class="form-input" style="width: 100%;">
                            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest Submissions</option>
                            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="rating_high" <?= $filters['sort'] === 'rating_high' ? 'selected' : '' ?>>Highest Rating (5★)</option>
                            <option value="rating_low" <?= $filters['sort'] === 'rating_low' ? 'selected' : '' ?>>Lowest Rating (1★)</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn-primary" style="height: 42px; padding: 0 16px;">
                            Filter
                        </button>
                        <a href="<?= url('admin/reviews') ?>" class="btn-secondary" style="height: 42px; padding: 0 14px; display: inline-flex; align-items: center;" title="Reset Filters">
                            ✕
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions Form & Moderation Queue Table -->
            <form action="<?= url('admin/reviews/bulk') ?>" method="POST" id="bulkReviewsForm">
                <?= csrf_field() ?>

                <!-- Floating Bulk Moderation Bar -->
                <div id="bulkActionBar" style="display: none; background: #ffffff; border: 1px solid var(--border-light); border-radius: 8px; padding: 12px 18px; margin-bottom: 16px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary);" id="selectedCount">0</span>
                        <span style="font-size: 0.84rem; color: var(--text-muted);">review(s) selected</span>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" name="bulk_status" value="approved" class="btn-primary" style="background: #059669; border-color: #059669; padding: 6px 14px; font-size: 0.82rem;">
                            ✓ Approve Selected
                        </button>
                        <button type="submit" name="bulk_status" value="rejected" class="btn-secondary" style="color: #dc2626; padding: 6px 14px; font-size: 0.82rem;">
                            ✕ Reject Selected
                        </button>
                        <button type="submit" name="bulk_status" value="flagged" class="btn-secondary" style="color: #d97706; padding: 6px 14px; font-size: 0.82rem;">
                            ⚑ Flag Selected
                        </button>
                    </div>
                </div>

                <div class="card-panel" style="padding: 0; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--bg-surface-alt, #fafafc); border-bottom: 1px solid var(--border-light); font-size: 0.76rem; text-transform: uppercase; color: var(--text-muted);">
                                    <th style="padding: 14px 16px; width: 40px; text-align: center;">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="cursor: pointer;">
                                    </th>
                                    <th style="padding: 14px 16px; text-align: left;">Rating</th>
                                    <th style="padding: 14px 16px; text-align: left;">Garment</th>
                                    <th style="padding: 14px 16px; text-align: left;">Review & Photos</th>
                                    <th style="padding: 14px 16px; text-align: left;">Client</th>
                                    <th style="padding: 14px 16px; text-align: center;">Status</th>
                                    <th style="padding: 14px 16px; text-align: right;">Moderation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reviews)): ?>
                                    <tr>
                                        <td colspan="7" style="padding: 48px; text-align: center; color: var(--text-muted);">
                                            <div style="font-size: 2.2rem; margin-bottom: 10px;">⭐</div>
                                            <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">No Reviews in this Queue</div>
                                            <div style="font-size: 0.85rem;">All customer feedback in this segment has been processed or matches no filter criteria.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reviews as $rev): ?>
                                        <tr style="border-bottom: 1px solid var(--border-light); transition: background 0.15s ease;" class="review-row" id="review-row-<?= $rev['id'] ?>">
                                            <!-- Checkbox -->
                                            <td style="padding: 14px 16px; text-align: center;">
                                                <input type="checkbox" name="selected_reviews[]" value="<?= $rev['encrypted_id'] ?>" class="review-checkbox" onchange="updateBulkBar()" style="cursor: pointer;">
                                            </td>

                                            <!-- Rating Stars -->
                                            <td style="padding: 14px 16px; vertical-align: top;">
                                                <div style="display: flex; align-items: center; gap: 4px;">
                                                    <span style="color: #eab308; font-size: 1rem; letter-spacing: 1px;">
                                                        <?= str_repeat('★', (int)$rev['rating']) . str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                                    </span>
                                                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); margin-left: 2px;">
                                                        <?= $rev['rating'] ?>.0
                                                    </span>
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                                    <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                                                </div>
                                            </td>

                                            <!-- Product Garment -->
                                            <td style="padding: 14px 16px; vertical-align: top; max-width: 180px;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <img 
                                                        src="<?= htmlspecialchars(image_url($rev['product_image'] ?? '')) ?>" 
                                                        alt="Garment" 
                                                        style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-light); flex-shrink: 0;"
                                                    >
                                                    <div>
                                                        <a href="<?= url('admin/products/' . $rev['product_encrypted_id'] . '/edit') ?>" style="font-weight: 600; font-size: 0.84rem; color: var(--text-primary); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.3;" title="<?= htmlspecialchars($rev['product_name']) ?>">
                                                            <?= htmlspecialchars($rev['product_name']) ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Review Content & Photos -->
                                            <td style="padding: 14px 16px; vertical-align: top; max-width: 320px;">
                                                <?php if (!empty($rev['title'])): ?>
                                                    <div style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary); margin-bottom: 3px;">
                                                        <?= htmlspecialchars($rev['title']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                                    <?= nl2br(htmlspecialchars($rev['body'])) ?>
                                                </div>

                                                <!-- Review Photos (if attached) -->
                                                <?php if (!empty($rev['photos'])): ?>
                                                    <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                                                        <?php foreach ($rev['photos'] as $pUrl): ?>
                                                            <img 
                                                                src="<?= htmlspecialchars(image_url($pUrl)) ?>" 
                                                                alt="Customer Review Photo" 
                                                                style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-light); cursor: pointer;"
                                                                onclick="inspectPhoto('<?= htmlspecialchars(image_url($pUrl)) ?>')"
                                                                title="Click to expand"
                                                            >
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Customer / Verified Buyer -->
                                            <td style="padding: 14px 16px; vertical-align: top;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, rgba(219, 39, 119, 0.15), rgba(140, 48, 245, 0.15)); color: #db2777; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;">
                                                        <?= strtoupper(substr($rev['customer_name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <a href="<?= url('admin/customers/' . $rev['customer_encrypted_id']) ?>" style="font-weight: 600; font-size: 0.84rem; color: var(--text-primary); text-decoration: none;">
                                                            <?= htmlspecialchars($rev['customer_name']) ?>
                                                        </a>
                                                        <div style="font-size: 0.74rem; color: var(--text-muted);"><?= htmlspecialchars($rev['customer_email']) ?></div>
                                                    </div>
                                                </div>

                                                <div style="margin-top: 6px;">
                                                    <?php if ($rev['is_verified_buyer']): ?>
                                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 999px; font-size: 0.68rem; font-weight: 700; background: rgba(45, 130, 255, 0.1); color: var(--brand-blue);">
                                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                            Verified Buyer
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="font-size: 0.7rem; color: var(--text-muted);">Guest / Unverified</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <!-- Status Badge -->
                                            <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                                <?php
                                                $status = strtolower($rev['status'] ?? 'pending');
                                                $badgeStyle = match($status) {
                                                    'approved' => 'background: rgba(16, 185, 129, 0.1); color: #059669;',
                                                    'rejected' => 'background: rgba(239, 68, 68, 0.1); color: #dc2626;',
                                                    'flagged'  => 'background: rgba(245, 158, 11, 0.1); color: #d97706;',
                                                    default    => 'background: rgba(140, 48, 245, 0.1); color: var(--brand-purple);'
                                                };
                                                ?>
                                                <span style="display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; <?= $badgeStyle ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>

                                                <?php if (!empty($rev['moderator_name'])): ?>
                                                    <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 4px;">
                                                        by <?= htmlspecialchars($rev['moderator_name']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Moderation Quick Actions -->
                                            <td style="padding: 14px 16px; text-align: right; vertical-align: top;">
                                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                    <!-- Quick Approve -->
                                                    <?php if ($rev['status'] !== 'approved'): ?>
                                                        <button type="button" class="btn-secondary" onclick="submitQuickStatus('<?= $rev['encrypted_id'] ?>', 'approved')" style="padding: 5px 8px; color: #059669;" title="Approve & Publish to Storefront">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- Quick Reject -->
                                                    <?php if ($rev['status'] !== 'rejected'): ?>
                                                        <button type="button" class="btn-secondary" onclick="submitQuickStatus('<?= $rev['encrypted_id'] ?>', 'rejected')" style="padding: 5px 8px; color: #dc2626;" title="Reject & Hide">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                                            </svg>
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- Quick Flag -->
                                                    <?php if ($rev['status'] !== 'flagged'): ?>
                                                        <button type="button" class="btn-secondary" onclick="submitQuickStatus('<?= $rev['encrypted_id'] ?>', 'flagged')" style="padding: 5px 8px; color: #d97706;" title="Flag for Review">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                                                <line x1="4" y1="22" x2="4" y2="15"></line>
                                                            </svg>
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- Delete -->
                                                    <button type="button" class="btn-secondary" onclick="submitDeleteReview('<?= $rev['encrypted_id'] ?>')" style="padding: 5px 8px; color: #dc2626;" title="Delete Review">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                    </button>
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
                                Showing <?= min($pagination['total_items'], $pagination['offset'] + 1) ?> to <?= min($pagination['total_items'], $pagination['offset'] + count($reviews)) ?> of <?= $pagination['total_items'] ?> reviews
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <?php if ($pagination['has_prev']): ?>
                                    <?php
                                    $prevParams = $filters;
                                    $prevParams['page'] = $pagination['current_page'] - 1;
                                    ?>
                                    <a href="<?= url('admin/reviews?' . http_build_query($prevParams)) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">&laquo; Previous</a>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                    <?php
                                    $pageParams = $filters;
                                    $pageParams['page'] = $p;
                                    ?>
                                    <a href="<?= url('admin/reviews?' . http_build_query($pageParams)) ?>" class="<?= $p === $pagination['current_page'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 6px 12px; font-size: 0.8rem; min-width: 32px; text-align: center;">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <?php
                                    $nextParams = $filters;
                                    $nextParams['page'] = $pagination['current_page'] + 1;
                                    ?>
                                    <a href="<?= url('admin/reviews?' . http_build_query($nextParams)) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- Standalone Action Form for Single Moderate/Delete -->
<form id="singleActionForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="status" id="singleActionStatus" value="">
</form>

<!-- Photo Lightbox Modal -->
<div id="photoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" onclick="closePhotoModal()">
    <div style="position: relative; max-width: 600px; max-height: 80vh; background: #ffffff; border-radius: 12px; overflow: hidden; padding: 8px;" onclick="event.stopPropagation()">
        <img id="photoModalImg" src="" alt="Enlarged Review Photo" style="width: 100%; max-height: 75vh; object-fit: contain; border-radius: 8px; display: block;">
        <button type="button" onclick="closePhotoModal()" style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 16px;">&times;</button>
    </div>
</div>

<script>
function toggleSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.review-checkbox:checked');
    const bar = document.getElementById('bulkActionBar');
    const countSpan = document.getElementById('selectedCount');
    if (checked.length > 0) {
        bar.style.display = 'flex';
        countSpan.textContent = checked.length;
    } else {
        bar.style.display = 'none';
        const master = document.getElementById('selectAllCheckbox');
        if (master) master.checked = false;
    }
}

function submitQuickStatus(encId, status) {
    const form = document.getElementById('singleActionForm');
    form.action = '<?= url("admin/reviews") ?>/' + encId + '/status';
    document.getElementById('singleActionStatus').value = status;
    form.submit();
}

function submitDeleteReview(encId) {
    if (confirm('Permanently delete this customer review?')) {
        const form = document.getElementById('singleActionForm');
        form.action = '<?= url("admin/reviews") ?>/' + encId + '/delete';
        form.submit();
    }
}

function inspectPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    document.getElementById('photoModal').style.display = 'flex';
}

function closePhotoModal() {
    document.getElementById('photoModal').style.display = 'none';
    document.getElementById('photoModalImg').src = '';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
