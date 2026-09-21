<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Header Section -->
            <div class="welcome-banner" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 8px;">
                        <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Customers & Support</span>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Support Tickets</span>
                    </div>
                    <h1 class="welcome-title">Support Tickets & Priority Resolution</h1>
                    <p class="welcome-subtitle">Manage client bespoke inquiries, alterations, VIP concierge escalations, and order support with conversational timelines.</p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" class="btn-primary" onclick="openNewTicketModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-weight: 700; border-radius: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Open New Ticket</span>
                    </button>
                    <a href="<?= url('admin/tickets/create') ?>" class="btn-secondary" style="display: inline-flex; align-items: center; padding: 10px 14px; border-radius: 8px;" title="Full Create Page">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="catalog-kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card" style="padding: 16px;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Inquiries</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-top: 4px;"><?= number_format($kpis['total_tickets']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-orange);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-orange); text-transform: uppercase;">Open & Urgent</div>
                        <?php if ($kpis['open_tickets'] > 0): ?>
                            <span style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 0.7rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">Action Required</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-orange); margin-top: 4px;"><?= number_format($kpis['open_tickets']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--brand-blue);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--brand-blue); text-transform: uppercase;">In Progress</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--brand-blue); margin-top: 4px;"><?= number_format($kpis['in_progress']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid #ef4444;">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #ef4444; text-transform: uppercase;">Critical Priority</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #ef4444; margin-top: 4px;"><?= number_format($kpis['critical_cases']) ?></div>
                </div>

                <div class="kpi-card" style="padding: 16px; border-left: 4px solid var(--status-success);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: var(--status-success); text-transform: uppercase;">Resolved & Closed</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--status-success); margin-top: 4px; display: flex; align-items: baseline; gap: 6px;">
                        <span><?= number_format($kpis['resolved_cases']) ?></span>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">(<?= $kpis['resolution_rate'] ?>%)</span>
                    </div>
                </div>
            </div>

            <!-- Segment Tabs -->
            <?php
            $currentStatus      = $filters['status'] ?? 'all';
            $currentPriority    = $filters['priority'] ?? 'all';
            $currentAssigned    = $filters['assigned_to'] ?? 'all';
            $currentOrderLinked = $filters['order_linked'] ?? 'all';
            ?>
            <div class="catalog-tabs-bar" style="margin-bottom: 20px;">
                <?php
                $tabs = [
                    'all'         => ['label' => 'All Tickets (' . $kpis['total_tickets'] . ')', 'status' => 'all'],
                    'open'        => ['label' => 'Open Queue (' . $kpis['open_tickets'] . ')', 'status' => 'open'],
                    'in_progress' => ['label' => 'In Progress (' . $kpis['in_progress'] . ')', 'status' => 'in_progress'],
                    'resolved'    => ['label' => 'Resolved (' . $kpis['resolved_cases'] . ')', 'status' => 'resolved'],
                    'closed'      => ['label' => 'Closed Archive', 'status' => 'closed'],
                ];
                foreach ($tabs as $tKey => $tData):
                    $tabParams = $filters;
                    $tabParams['status'] = $tData['status'];
                    $tabParams['page'] = 1;
                    $isActive = ($currentStatus === $tData['status']);
                ?>
                    <a href="<?= url('admin/tickets?' . http_build_query($tabParams)) ?>" class="catalog-tab-btn <?= $isActive ? 'active' : '' ?>">
                        <?= $tData['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter Toolbar -->
            <div class="card-panel" style="margin-bottom: 20px; padding: 16px 20px;">
                <form action="<?= url('admin/tickets') ?>" method="GET" class="catalog-filter-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">

                    <div class="search-box" style="flex: 2; min-width: 200px;">
                        <input 
                            type="text" 
                            name="search" 
                            class="form-input" 
                            placeholder="Search by Ticket #, Subject, Client Name, Email, or Order #..." 
                            value="<?= htmlspecialchars($filters['search']) ?>"
                        >
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="priority" class="form-input">
                            <option value="all">All Priorities</option>
                            <option value="critical" <?= $currentPriority === 'critical' ? 'selected' : '' ?>>🔴 Critical</option>
                            <option value="high" <?= $currentPriority === 'high' ? 'selected' : '' ?>>🟠 High</option>
                            <option value="medium" <?= $currentPriority === 'medium' ? 'selected' : '' ?>>🔵 Medium</option>
                            <option value="low" <?= $currentPriority === 'low' ? 'selected' : '' ?>>⚪ Low</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="assigned_to" class="form-input">
                            <option value="all">All Staff Agents</option>
                            <option value="unassigned" <?= $currentAssigned === 'unassigned' ? 'selected' : '' ?>>Unassigned</option>
                            <?php foreach ($admins as $adm): ?>
                                <option value="<?= $adm['id'] ?>" <?= (string)$currentAssigned === (string)$adm['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($adm['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="order_linked" class="form-input">
                            <option value="all">All Associations</option>
                            <option value="order_only" <?= $currentOrderLinked === 'order_only' ? 'selected' : '' ?>>Order Linked</option>
                            <option value="general_only" <?= $currentOrderLinked === 'general_only' ? 'selected' : '' ?>>General Inquiry</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="sort" class="form-input">
                            <option value="last_updated" <?= $filters['sort'] === 'last_updated' ? 'selected' : '' ?>>Recently Active</option>
                            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="priority_desc" <?= $filters['sort'] === 'priority_desc' ? 'selected' : '' ?>>Highest Priority</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                        <a href="<?= url('admin/tickets') ?>" class="btn-secondary" style="height: 42px; padding: 0 14px; display: inline-flex; align-items: center;" title="Reset Filters">✕</a>
                    </div>
                </form>
            </div>

            <!-- Tickets Ledger Table -->
            <div class="card-panel" style="padding: 0; overflow: hidden;">
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--bg-surface-alt, #fafafc); border-bottom: 1px solid var(--border-light); font-size: 0.76rem; text-transform: uppercase; color: var(--text-muted);">
                                <th style="padding: 14px 16px; text-align: left;">Ticket & Priority</th>
                                <th style="padding: 14px 16px; text-align: left;">Client Profile</th>
                                <th style="padding: 14px 16px; text-align: left;">Subject & Inquiry</th>
                                <th style="padding: 14px 16px; text-align: left;">Associated Order</th>
                                <th style="padding: 14px 16px; text-align: center;">Status</th>
                                <th style="padding: 14px 16px; text-align: left;">Assigned Agent</th>
                                <th style="padding: 14px 16px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="7" style="padding: 48px; text-align: center; color: var(--text-muted);">
                                        <div style="font-size: 2.2rem; margin-bottom: 10px;">🎟️</div>
                                        <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">No Support Tickets Found</div>
                                        <div style="font-size: 0.85rem;">All customer requests in this view have been resolved or match no filter parameters.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $tkt): ?>
                                    <tr style="border-bottom: 1px solid var(--border-light); transition: background 0.15s ease;" class="ticket-row">
                                        <!-- Ticket Code & Priority -->
                                        <td style="padding: 14px 16px; vertical-align: top; white-space: nowrap;">
                                            <div style="font-weight: 800; font-size: 0.88rem; color: var(--text-primary);">
                                                <a href="<?= url('admin/tickets/' . $tkt['encrypted_id']) ?>" style="color: inherit; text-decoration: none;">
                                                    <?= $tkt['ticket_code'] ?>
                                                </a>
                                            </div>
                                            <div style="margin-top: 5px;">
                                                <?php
                                                $pri = strtolower($tkt['priority'] ?? 'medium');
                                                $priStyle = match($pri) {
                                                    'critical' => 'background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25);',
                                                    'high'     => 'background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25);',
                                                    'low'      => 'background: rgba(148, 163, 184, 0.1); color: #64748b; border: 1px solid rgba(148, 163, 184, 0.25);',
                                                    default    => 'background: rgba(59, 130, 246, 0.1); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.25);'
                                                };
                                                ?>
                                                <span style="display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; <?= $priStyle ?>">
                                                    <?= $pri ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Client Profile -->
                                        <td style="padding: 14px 16px; vertical-align: top;">
                                            <?php if (!empty($tkt['customer_name'])): ?>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, rgba(45, 130, 255, 0.15), rgba(140, 48, 245, 0.15)); color: var(--brand-blue); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;">
                                                        <?= strtoupper(substr($tkt['customer_name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <?php if (!empty($tkt['customer_encrypted_id'])): ?>
                                                            <a href="<?= url('admin/customers/' . $tkt['customer_encrypted_id']) ?>" style="font-weight: 600; font-size: 0.84rem; color: var(--text-primary); text-decoration: none;">
                                                                <?= htmlspecialchars($tkt['customer_name']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span style="font-weight: 600; font-size: 0.84rem; color: var(--text-primary);"><?= htmlspecialchars($tkt['customer_name']) ?></span>
                                                        <?php endif; ?>
                                                        <div style="font-size: 0.74rem; color: var(--text-muted);"><?= htmlspecialchars($tkt['customer_email']) ?></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.82rem; color: var(--text-muted); font-style: italic;">Guest Visitor</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Subject & Inquiry Snippet -->
                                        <td style="padding: 14px 16px; vertical-align: top; max-width: 280px;">
                                            <a href="<?= url('admin/tickets/' . $tkt['encrypted_id']) ?>" style="font-weight: 700; font-size: 0.86rem; color: var(--text-primary); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;" title="<?= htmlspecialchars($tkt['subject']) ?>">
                                                <?= htmlspecialchars($tkt['subject']) ?>
                                            </a>
                                            <?php if (!empty($tkt['latest_message_snippet'])): ?>
                                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;">
                                                    <?= htmlspecialchars($tkt['latest_message_snippet']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                                <span>💬 <?= $tkt['message_count'] ?> messages</span>
                                                <span>•</span>
                                                <span>Updated <?= date('M d, h:i A', strtotime($tkt['updated_at'])) ?></span>
                                            </div>
                                        </td>

                                        <!-- Associated Order -->
                                        <td style="padding: 14px 16px; vertical-align: top; white-space: nowrap;">
                                            <?php if (!empty($tkt['linked_order_number'])): ?>
                                                <div style="display: inline-flex; align-items: center; gap: 5px; background: rgba(140, 48, 245, 0.08); padding: 4px 8px; border-radius: 6px;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--brand-purple);">
                                                        <rect x="6" y="2" width="12" height="20" rx="2"></rect>
                                                    </svg>
                                                    <?php if (!empty($tkt['order_encrypted_id'])): ?>
                                                        <a href="<?= url('admin/orders/' . $tkt['order_encrypted_id']) ?>" style="font-weight: 700; font-size: 0.78rem; color: var(--brand-purple); text-decoration: none;">
                                                            <?= htmlspecialchars($tkt['linked_order_number']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span style="font-weight: 700; font-size: 0.78rem; color: var(--brand-purple);"><?= htmlspecialchars($tkt['linked_order_number']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                                    <?= currency($tkt['linked_order_total']) ?> (<?= ucfirst($tkt['linked_order_status'] ?? '') ?>)
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.74rem; color: var(--text-muted);">— General Inquiry —</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Badge -->
                                        <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                            <?php
                                            $st = strtolower($tkt['status'] ?? 'open');
                                            $stStyle = match($st) {
                                                'resolved'     => 'background: rgba(16, 185, 129, 0.1); color: #059669;',
                                                'closed'       => 'background: rgba(148, 163, 184, 0.1); color: #475569;',
                                                'in_progress'  => 'background: rgba(59, 130, 246, 0.1); color: #2563eb;',
                                                'acknowledged' => 'background: rgba(245, 158, 11, 0.1); color: #d97706;',
                                                default        => 'background: rgba(140, 48, 245, 0.1); color: var(--brand-purple);'
                                            };
                                            ?>
                                            <span style="display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; <?= $stStyle ?>">
                                                <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                            </span>
                                        </td>

                                        <!-- Assigned Staff -->
                                        <td style="padding: 14px 16px; vertical-align: top;">
                                            <?php if (!empty($tkt['assigned_agent_name'])): ?>
                                                <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 5px;">
                                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                                                    <?= htmlspecialchars($tkt['assigned_agent_name']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.76rem; color: var(--text-muted); font-style: italic;">Unassigned</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td style="padding: 14px 16px; text-align: right; vertical-align: top; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <a href="<?= url('admin/tickets/' . $tkt['encrypted_id']) ?>" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="View Conversation Thread">
                                                    <span>View Thread</span>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <polyline points="9 18 15 12 9 6"></polyline>
                                                    </svg>
                                                </a>

                                                <button type="button" class="btn-secondary" onclick="confirmDeleteTicket('<?= $tkt['encrypted_id'] ?>')" style="padding: 6px 8px; color: #dc2626; border-radius: 6px;" title="Delete Ticket">
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
                            Showing <?= min($pagination['total_items'], $pagination['offset'] + 1) ?> to <?= min($pagination['total_items'], $pagination['offset'] + count($tickets)) ?> of <?= $pagination['total_items'] ?> tickets
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <?php if ($pagination['has_prev']): ?>
                                <?php
                                $prevParams = $filters;
                                $prevParams['page'] = $pagination['current_page'] - 1;
                                ?>
                                <a href="<?= url('admin/tickets?' . http_build_query($prevParams)) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">&laquo; Previous</a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                <?php
                                $pageParams = $filters;
                                $pageParams['page'] = $p;
                                ?>
                                <a href="<?= url('admin/tickets?' . http_build_query($pageParams)) ?>" class="<?= $p === $pagination['current_page'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 6px 12px; font-size: 0.8rem; min-width: 32px; text-align: center;">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <?php
                                $nextParams = $filters;
                                $nextParams['page'] = $pagination['current_page'] + 1;
                                ?>
                                <a href="<?= url('admin/tickets?' . http_build_query($nextParams)) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Quick Create Ticket Modal -->
<div id="newTicketModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" onclick="closeNewTicketModal()">
    <div style="background: #ffffff; border-radius: 12px; max-width: 600px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); padding: 24px; position: relative; max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); padding-bottom: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Open Support Ticket</h3>
                <p style="margin: 3px 0 0 0; font-size: 0.78rem; color: var(--text-muted);">Create a client service inquiry or alteration case.</p>
            </div>
            <button type="button" onclick="closeNewTicketModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('admin/tickets/store') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Client Selection -->
            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">Select Client <span style="color: #dc2626;">*</span></label>
                <select name="customer_id" id="modalCustomerSelect" class="form-input" required onchange="onModalCustomerChange(this.value)">
                    <option value="">-- Choose Client Profile --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['encrypted_id'] ?>">
                            <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Optional Linked Order -->
            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">Linked Order (Optional)</label>
                <select name="order_id" id="modalOrderSelect" class="form-input">
                    <option value="">-- None / General Inquiry --</option>
                </select>
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">Orders list dynamically updates once a client is chosen above.</div>
            </div>

            <!-- Subject -->
            <div style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">Subject <span style="color: #dc2626;">*</span></label>
                <input type="text" name="subject" class="form-input" placeholder="e.g. Alteration request for Velvet Bandhgala" required>
            </div>

            <!-- Priority & Staff Assignment -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">Priority</label>
                    <select name="priority" class="form-input">
                        <option value="medium" selected>🔵 Medium (Standard)</option>
                        <option value="high">🟠 High (Urgent)</option>
                        <option value="critical">🔴 Critical (VIP Escalation)</option>
                        <option value="low">⚪ Low (Inquiry)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">Assign Concierge</label>
                    <select name="assigned_to" class="form-input">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($admins as $adm): ?>
                            <option value="<?= $adm['id'] ?>"><?= htmlspecialchars($adm['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Initial Message -->
            <div style="margin-bottom: 20px;">
                <label class="form-label" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; color: var(--text-primary);">Initial Inquiry / Case Notes <span style="color: #dc2626;">*</span></label>
                <textarea name="message" class="form-input" rows="4" style="height: auto; padding: 10px 14px;" placeholder="Describe the client's request or inquiry details in full..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-light); padding-top: 14px;">
                <button type="button" class="btn-secondary" onclick="closeNewTicketModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 20px; font-weight: 700;">Open Support Ticket</button>
            </div>
        </form>
    </div>
</div>

<!-- Standalone Delete Action Form -->
<form id="deleteTicketForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
function openNewTicketModal() {
    document.getElementById('newTicketModal').style.display = 'flex';
}

function closeNewTicketModal() {
    document.getElementById('newTicketModal').style.display = 'none';
}

function confirmDeleteTicket(encId) {
    if (confirm('Permanently delete this support ticket and its conversation history?')) {
        const form = document.getElementById('deleteTicketForm');
        form.action = '<?= url("admin/tickets") ?>/' + encId + '/delete';
        form.submit();
    }
}

function onModalCustomerChange(encCustId) {
    const orderSelect = document.getElementById('modalOrderSelect');
    orderSelect.innerHTML = '<option value="">Loading client orders...</option>';

    if (!encCustId) {
        orderSelect.innerHTML = '<option value="">-- None / General Inquiry --</option>';
        return;
    }

    fetch('<?= url("admin/tickets/customer-orders") ?>/' + encCustId)
        .then(res => res.json())
        .then(data => {
            orderSelect.innerHTML = '<option value="">-- None / General Inquiry --</option>';
            if (data.success && data.orders.length > 0) {
                data.orders.forEach(ord => {
                    const opt = document.createElement('option');
                    opt.value = ord.encrypted_id;
                    opt.textContent = ord.order_number + ' (₹' + Math.round(ord.grand_total) + ' - ' + ord.status + ')';
                    orderSelect.appendChild(opt);
                });
            }
        })
        .catch(() => {
            orderSelect.innerHTML = '<option value="">-- None / General Inquiry --</option>';
        });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
