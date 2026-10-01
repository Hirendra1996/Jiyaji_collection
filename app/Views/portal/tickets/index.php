<?php
$title      = $title ?? 'Support Desk & Client Concierge | Jiyaji LX Operations Portal';
$tickets    = $tickets ?? [];
$filters    = $filters ?? [];
$pagination = $pagination ?? ['has_prev' => false, 'has_next' => false, 'current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$kpis       = $kpis ?? [];
$admins     = $admins ?? [];
$customers  = $customers ?? [];
$canReply   = $canReply ?? false;
$canResolve = $canResolve ?? false;

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <span style="color: #64748B;">Customers &amp; Support</span>
                <span>&rsaquo;</span>
                <span style="color: #0F172A; font-weight: 600;">Support Desk</span>
            </div>

            <!-- Hero Banner -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #312E81 55%, #4F46E5 100%); border-radius: 16px; padding: 1.75rem 2rem; color: #FFFFFF; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.28);">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.18); padding: 3px 10px; border-radius: 999px; color: #E0E7FF; margin-bottom: 8px; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        Customer Concierge &bull; Priority Inquiries &bull; SLA Resolution
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 4px 0;">Support Desk &amp; Concierge</h1>
                    <p style="font-size: 0.88rem; color: #C7D2FE; margin: 0;">
                        Manage client bespoke inquiries, alterations, VIP care escalations, and order support with live conversation threads.
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button type="button" onclick="openNewTicketModal()" 
                            style="background: #FFFFFF; color: #4338CA; font-weight: 700; font-size: 0.84rem; padding: 10px 18px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s ease;"
                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Open New Ticket</span>
                    </button>

                    <a href="<?= url('portal/tickets/export?' . http_build_query(array_filter($filters, fn($v) => $v !== '' && $v !== 'all'))) ?>" 
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
                <!-- Total Inquiries -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #4F46E5; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #4F46E5; text-transform: uppercase; letter-spacing: 0.05em;">Total Inquiries</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['total_tickets'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Logged client cases</div>
                </div>

                <!-- Open Queue -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #F59E0B; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em;">Open Queue</div>
                        <?php if (($kpis['open_tickets'] ?? 0) > 0): ?>
                            <span style="background: rgba(245, 158, 11, 0.15); color: #B45309; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">Action Req.</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #D97706; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['open_tickets'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Awaiting staff reply</div>
                </div>

                <!-- In Progress -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #0284C7; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #0284C7; text-transform: uppercase; letter-spacing: 0.05em;">In Progress</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['in_progress'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Active conversations</div>
                </div>

                <!-- Critical Priority -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #EF4444; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.74rem; font-weight: 700; color: #DC2626; text-transform: uppercase; letter-spacing: 0.05em;">Critical Cases</div>
                        <?php if (($kpis['critical_cases'] ?? 0) > 0): ?>
                            <span style="background: rgba(239, 68, 68, 0.15); color: #DC2626; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px;">Urgent</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #DC2626; margin-top: 6px; letter-spacing: -0.02em;"><?= number_format($kpis['critical_cases'] ?? 0) ?></div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Requires immediate care</div>
                </div>

                <!-- Resolved & Closed -->
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #10B981; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #10B981; text-transform: uppercase; letter-spacing: 0.05em;">Resolved Rate</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #0F172A; margin-top: 6px; letter-spacing: -0.02em; display: flex; align-items: baseline; gap: 6px;">
                        <span><?= number_format($kpis['resolved_cases'] ?? 0) ?></span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #10B981;">(<?= $kpis['resolution_rate'] ?? 100 ?>%)</span>
                    </div>
                    <div style="font-size: 0.72rem; color: #94A3B8; margin-top: 2px;">Completed resolutions</div>
                </div>
            </div>

            <!-- Segment Tabs -->
            <?php
            $currentStatus      = $filters['status'] ?? 'all';
            $currentPriority    = $filters['priority'] ?? 'all';
            $currentAssigned    = $filters['assigned_to'] ?? 'all';
            $currentOrderLinked = $filters['order_linked'] ?? 'all';

            $tabs = [
                'all'         => ['label' => 'All Tickets (' . ($kpis['total_tickets'] ?? 0) . ')', 'status' => 'all'],
                'open'        => ['label' => 'Open Queue (' . ($kpis['open_tickets'] ?? 0) . ')', 'status' => 'open'],
                'in_progress' => ['label' => 'In Progress (' . ($kpis['in_progress'] ?? 0) . ')', 'status' => 'in_progress'],
                'resolved'    => ['label' => 'Resolved (' . ($kpis['resolved_cases'] ?? 0) . ')', 'status' => 'resolved'],
                'closed'      => ['label' => 'Closed Archive', 'status' => 'closed'],
            ];
            ?>
            <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
                <?php foreach ($tabs as $tKey => $tData): 
                    $tabParams = $filters;
                    $tabParams['status'] = $tData['status'];
                    $tabParams['page'] = 1;
                    $isActive = ($currentStatus === $tData['status']);
                ?>
                    <a href="<?= url('portal/tickets?' . http_build_query($tabParams)) ?>" 
                       style="padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; text-decoration: none; white-space: nowrap; transition: all 0.2s ease; <?= $isActive ? 'background: #4F46E5; color: #FFFFFF; box-shadow: 0 2px 6px rgba(79,70,229,0.3);' : 'background: #FFFFFF; color: #64748B; border: 1px solid #E2E8F0;' ?>">
                        <?= $tData['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Filter Toolbar -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <form action="<?= url('portal/tickets') ?>" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">

                    <div style="flex: 2; min-width: 220px; position: relative;">
                        <input type="text" name="search" placeholder="Search by Ticket #, Subject, Client Name, Email, or Order #..." 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                               style="width: 100%; height: 40px; padding: 0 14px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; outline: none; transition: border-color 0.2s;"
                               onfocus="this.style.borderColor='#4F46E5';" onblur="this.style.borderColor='#CBD5E1';">
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="priority" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; background: #FFF; outline: none;">
                            <option value="all">All Priorities</option>
                            <option value="critical" <?= $currentPriority === 'critical' ? 'selected' : '' ?>>🔴 Critical</option>
                            <option value="high" <?= $currentPriority === 'high' ? 'selected' : '' ?>>🟠 High</option>
                            <option value="medium" <?= $currentPriority === 'medium' ? 'selected' : '' ?>>🔵 Medium</option>
                            <option value="low" <?= $currentPriority === 'low' ? 'selected' : '' ?>>⚪ Low</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <select name="assigned_to" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; background: #FFF; outline: none;">
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
                        <select name="order_linked" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; background: #FFF; outline: none;">
                            <option value="all">All Associations</option>
                            <option value="order_only" <?= $currentOrderLinked === 'order_only' ? 'selected' : '' ?>>Order Linked</option>
                            <option value="general_only" <?= $currentOrderLinked === 'general_only' ? 'selected' : '' ?>>General Inquiry</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <select name="sort" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.85rem; background: #FFF; outline: none;">
                            <option value="last_updated" <?= ($filters['sort'] ?? '') === 'last_updated' ? 'selected' : '' ?>>Recently Active</option>
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="priority_desc" <?= ($filters['sort'] ?? '') === 'priority_desc' ? 'selected' : '' ?>>Highest Priority</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" style="height: 40px; padding: 0 18px; background: #4F46E5; color: #FFFFFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            Filter
                        </button>
                        <a href="<?= url('portal/tickets') ?>" style="height: 40px; padding: 0 14px; background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center;" title="Reset Filters">
                            ✕
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tickets Ledger Table -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 24px;">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                        <thead>
                            <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; font-size: 0.74rem; text-transform: uppercase; color: #64748B; letter-spacing: 0.05em;">
                                <th style="padding: 14px 18px;">Ticket &amp; Urgency</th>
                                <th style="padding: 14px 18px;">Client Profile</th>
                                <th style="padding: 14px 18px;">Subject &amp; Inquiry</th>
                                <th style="padding: 14px 18px;">Associated Order</th>
                                <th style="padding: 14px 18px; text-align: center;">Status</th>
                                <th style="padding: 14px 18px;">Assigned Agent</th>
                                <th style="padding: 14px 18px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="7" style="padding: 56px 20px; text-align: center; color: #64748B;">
                                        <div style="font-size: 2.5rem; margin-bottom: 12px;">🎟️</div>
                                        <div style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-bottom: 6px;">No Support Tickets Found</div>
                                        <div style="font-size: 0.86rem; color: #64748B; max-width: 440px; margin: 0 auto 16px;">
                                            All client requests in this view have been resolved, or no tickets match the specified filters.
                                        </div>
                                        <button type="button" onclick="openNewTicketModal()" style="padding: 9px 18px; background: #4F46E5; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer;">
                                            + Open Support Ticket
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $tkt): ?>
                                    <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='#FFFFFF';">
                                        <!-- Ticket Code & Priority -->
                                        <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                                            <a href="<?= url('portal/tickets/' . $tkt['encrypted_id']) ?>" style="font-weight: 800; font-size: 0.9rem; color: #0F172A; text-decoration: none; font-family: monospace;" onmouseover="this.style.color='#4F46E5';" onmouseout="this.style.color='#0F172A';">
                                                <?= htmlspecialchars($tkt['ticket_code']) ?>
                                            </a>
                                            <div style="margin-top: 6px;">
                                                <?php
                                                $pri = strtolower($tkt['priority'] ?? 'medium');
                                                $priStyle = match($pri) {
                                                    'critical' => 'background: rgba(239, 68, 68, 0.1); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.25);',
                                                    'high'     => 'background: rgba(245, 158, 11, 0.1); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.25);',
                                                    'low'      => 'background: rgba(148, 163, 184, 0.1); color: #64748B; border: 1px solid rgba(148, 163, 184, 0.25);',
                                                    default    => 'background: rgba(79, 70, 229, 0.08); color: #4F46E5; border: 1px solid rgba(79, 70, 229, 0.22);'
                                                };
                                                ?>
                                                <span style="display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; <?= $priStyle ?>">
                                                    <?= $pri ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Client Profile -->
                                        <td style="padding: 14px 18px; vertical-align: top;">
                                            <?php if (!empty($tkt['customer_name'])): ?>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(79,70,229,0.15), rgba(14,165,233,0.15)); color: #4F46E5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; flex-shrink: 0;">
                                                        <?= strtoupper(substr($tkt['customer_name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <?php if (!empty($tkt['customer_encrypted_id'])): ?>
                                                            <a href="<?= url('portal/customers/' . $tkt['customer_encrypted_id']) ?>" style="font-weight: 700; font-size: 0.86rem; color: #0F172A; text-decoration: none;" onmouseover="this.style.color='#4F46E5';" onmouseout="this.style.color='#0F172A';">
                                                                <?= htmlspecialchars($tkt['customer_name']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span style="font-weight: 700; font-size: 0.86rem; color: #0F172A;"><?= htmlspecialchars($tkt['customer_name']) ?></span>
                                                        <?php endif; ?>
                                                        <div style="font-size: 0.74rem; color: #64748B; margin-top: 1px;"><?= htmlspecialchars($tkt['customer_email']) ?></div>
                                                        <?php if (!empty($tkt['customer_phone'])): ?>
                                                            <div style="font-size: 0.72rem; color: #94A3B8;"><?= htmlspecialchars($tkt['customer_phone']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.82rem; color: #94A3B8; font-style: italic;">Guest Shopper</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Subject & Inquiry Snippet -->
                                        <td style="padding: 14px 18px; vertical-align: top; max-width: 300px;">
                                            <a href="<?= url('portal/tickets/' . $tkt['encrypted_id']) ?>" style="font-weight: 700; font-size: 0.88rem; color: #0F172A; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;" title="<?= htmlspecialchars($tkt['subject']) ?>" onmouseover="this.style.color='#4F46E5';" onmouseout="this.style.color='#0F172A';">
                                                <?= htmlspecialchars($tkt['subject']) ?>
                                            </a>
                                            <?php if (!empty($tkt['latest_message_snippet'])): ?>
                                                <div style="font-size: 0.78rem; color: #64748B; margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                                    <?= htmlspecialchars($tkt['latest_message_snippet']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="font-size: 0.71rem; color: #94A3B8; margin-top: 5px; display: flex; align-items: center; gap: 6px;">
                                                <span style="background: #F1F5F9; color: #475569; padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                                                    💬 <?= (int)($tkt['message_count'] ?? 1) ?> msg
                                                </span>
                                                <span>&bull;</span>
                                                <span>Updated <?= !empty($tkt['updated_at']) ? date('M d, h:i A', strtotime($tkt['updated_at'])) : 'Recently' ?></span>
                                            </div>
                                        </td>

                                        <!-- Associated Order -->
                                        <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                                            <?php if (!empty($tkt['linked_order_number'])): ?>
                                                <div style="font-size: 0.82rem; font-weight: 700; font-family: monospace;">
                                                    <?php if (!empty($tkt['order_encrypted_id'])): ?>
                                                        <a href="<?= url('portal/orders/' . $tkt['order_encrypted_id']) ?>" style="color: #4F46E5; text-decoration: none;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                                                            #<?= htmlspecialchars($tkt['linked_order_number']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span>#<?= htmlspecialchars($tkt['linked_order_number']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.75rem; color: #0F172A; font-weight: 700; margin-top: 2px;">
                                                    <?= currency($tkt['linked_order_total'] ?? 0) ?>
                                                </div>
                                                <div style="margin-top: 3px;">
                                                    <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; padding: 1px 6px; border-radius: 4px; background: #F1F5F9; color: #475569;">
                                                        <?= htmlspecialchars($tkt['linked_order_status'] ?? '') ?>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.76rem; color: #94A3B8; background: #F8FAFC; border: 1px dashed #E2E8F0; padding: 2px 7px; border-radius: 4px; display: inline-block;">
                                                    General Inquiry
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status -->
                                        <td style="padding: 14px 18px; vertical-align: top; text-align: center; white-space: nowrap;">
                                            <?php
                                            $st = strtolower($tkt['status'] ?? 'open');
                                            $stBadge = match($st) {
                                                'open'         => 'background: rgba(245, 158, 11, 0.12); color: #B45309; border: 1px solid rgba(245, 158, 11, 0.3);',
                                                'acknowledged' => 'background: rgba(147, 51, 234, 0.1); color: #7E22CE; border: 1px solid rgba(147, 51, 234, 0.25);',
                                                'in_progress'  => 'background: rgba(2, 132, 199, 0.1); color: #0284C7; border: 1px solid rgba(2, 132, 199, 0.25);',
                                                'resolved'     => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                                                'closed'       => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);',
                                                default        => 'background: rgba(79, 70, 229, 0.1); color: #4F46E5; border: 1px solid rgba(79, 70, 229, 0.25);'
                                            };
                                            ?>
                                            <span id="status-badge-<?= $tkt['id'] ?>" style="display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; <?= $stBadge ?>">
                                                <?= ucwords(str_replace('_', ' ', $st)) ?>
                                            </span>

                                            <?php if ($canResolve): ?>
                                                <div style="margin-top: 6px;">
                                                    <select onchange="updateTicketStatusQuick('<?= $tkt['encrypted_id'] ?>', this.value, <?= $tkt['id'] ?>)" 
                                                            style="font-size: 0.72rem; font-weight: 600; padding: 2px 5px; border: 1px solid #CBD5E1; border-radius: 6px; background: #FFF; color: #475569; outline: none; cursor: pointer;">
                                                        <option value="" disabled selected>Change Status...</option>
                                                        <option value="open" <?= $st === 'open' ? 'disabled' : '' ?>>Open</option>
                                                        <option value="acknowledged" <?= $st === 'acknowledged' ? 'disabled' : '' ?>>Acknowledged</option>
                                                        <option value="in_progress" <?= $st === 'in_progress' ? 'disabled' : '' ?>>In Progress</option>
                                                        <option value="resolved" <?= $st === 'resolved' ? 'disabled' : '' ?>>Resolved</option>
                                                        <option value="closed" <?= $st === 'closed' ? 'disabled' : '' ?>>Closed</option>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Assigned Agent -->
                                        <td style="padding: 14px 18px; vertical-align: top; white-space: nowrap;">
                                            <?php if (!empty($tkt['assigned_agent_name'])): ?>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <div style="width: 22px; height: 22px; border-radius: 50%; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 800;">
                                                        <?= strtoupper(substr($tkt['assigned_agent_name'], 0, 1)) ?>
                                                    </div>
                                                    <span style="font-weight: 600; font-size: 0.82rem; color: #0F172A;">
                                                        <?= htmlspecialchars($tkt['assigned_agent_name']) ?>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 0.75rem; color: #94A3B8; font-weight: 600; background: #F8FAFC; border: 1px dashed #CBD5E1; padding: 2px 8px; border-radius: 6px;">
                                                    Unassigned
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Action -->
                                        <td style="padding: 14px 18px; vertical-align: top; text-align: right; white-space: nowrap;">
                                            <a href="<?= url('portal/tickets/' . $tkt['encrypted_id']) ?>" 
                                               style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 8px; background: #0F172A; color: #FFFFFF; font-size: 0.78rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease;"
                                               onmouseover="this.style.background='#4F46E5';" onmouseout="this.style.background='#0F172A';">
                                                <span>View Thread</span>
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            </a>
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
                            Showing <?= min($pagination['total_items'], $pagination['offset'] + 1) ?> to <?= min($pagination['total_items'], $pagination['offset'] + count($tickets)) ?> of <?= number_format($pagination['total_items']) ?> tickets
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <?php
                            $prevParams = $filters;
                            $prevParams['page'] = max(1, $pagination['current_page'] - 1);
                            $nextParams = $filters;
                            $nextParams['page'] = min($pagination['total_pages'], $pagination['current_page'] + 1);
                            ?>
                            <?php if ($pagination['has_prev']): ?>
                                <a href="<?= url('portal/tickets?' . http_build_query($prevParams)) ?>" style="padding: 6px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; color: #334155; font-weight: 600; text-decoration: none;">&lsaquo; Prev</a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): 
                                $pageParams = $filters;
                                $pageParams['page'] = $p;
                                $isCurr = ($p === $pagination['current_page']);
                            ?>
                                <?php if ($p === 1 || $p === $pagination['total_pages'] || abs($p - $pagination['current_page']) <= 2): ?>
                                    <a href="<?= url('portal/tickets?' . http_build_query($pageParams)) ?>" 
                                       style="padding: 6px 12px; border-radius: 6px; font-weight: 700; text-decoration: none; <?= $isCurr ? 'background: #4F46E5; color: #FFFFFF;' : 'background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155;' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php elseif (abs($p - $pagination['current_page']) === 3): ?>
                                    <span style="color: #94A3B8; padding: 0 4px;">&hellip;</span>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <a href="<?= url('portal/tickets?' . http_build_query($nextParams)) ?>" style="padding: 6px 12px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; color: #334155; font-weight: 600; text-decoration: none;">Next &rsaquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<!-- Quick Open New Support Ticket Modal -->
<div id="newTicketModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 620px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #0F172A 0%, #312E81 100%); padding: 20px 24px; color: #FFFFFF; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; letter-spacing: -0.01em;">Open New Support Ticket</h3>
                <p style="font-size: 0.78rem; color: #C7D2FE; margin: 2px 0 0 0;">Create a verified inquiry or concierge service request for a client.</p>
            </div>
            <button type="button" onclick="closeNewTicketModal()" style="background: rgba(255,255,255,0.15); border: none; color: #FFF; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">&times;</button>
        </div>

        <!-- Modal Form -->
        <form action="<?= url('portal/tickets/store') ?>" method="POST" style="padding: 24px;">
            <?= csrf_field() ?>

            <!-- Client & Order Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Select Client Profile <span style="color: #EF4444;">*</span></label>
                    <select name="customer_id" id="modalCustomerSelect" onchange="loadCustomerOrders(this.value)" required style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 0.85rem; outline: none; background: #FFF;">
                        <option value="">-- Choose Registered Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['encrypted_id'] ?>">
                                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Associated Order (Optional)</label>
                    <select name="order_id" id="modalOrderSelect" style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 0.85rem; outline: none; background: #FFF;">
                        <option value="">-- No Order Associated --</option>
                    </select>
                </div>
            </div>

            <!-- Urgency Priority & Assignee -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Urgency Priority <span style="color: #EF4444;">*</span></label>
                    <select name="priority" required style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 0.85rem; outline: none; background: #FFF;">
                        <option value="medium" selected>🔵 Medium Priority (Standard 24h SLA)</option>
                        <option value="low">⚪ Low Priority (General Question)</option>
                        <option value="high">🟠 High Priority (Order Alteration/Dispatch)</option>
                        <option value="critical">🔴 Critical Priority (Urgent Complaint)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Assign Concierge Agent</label>
                    <select name="assigned_to" style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 12px; font-size: 0.85rem; outline: none; background: #FFF;">
                        <option value="">-- Assign Automatically / Unassigned --</option>
                        <?php foreach ($admins as $adm): ?>
                            <option value="<?= $adm['id'] ?>">
                                <?= htmlspecialchars($adm['name']) ?> (<?= htmlspecialchars($adm['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Subject -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Inquiry Subject <span style="color: #EF4444;">*</span></label>
                <input type="text" name="subject" required placeholder="e.g. Bespoke Sherwani Sizing Adjustment or Delivery Tracking Delay" 
                       style="width: 100%; height: 42px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 14px; font-size: 0.85rem; outline: none;">
            </div>

            <!-- Message -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 6px;">Initial Inquiry / Details <span style="color: #EF4444;">*</span></label>
                <textarea name="message" required rows="4" placeholder="Detail the client's request, feedback, or alteration specifics..." 
                          style="width: 100%; border: 1px solid #CBD5E1; border-radius: 8px; padding: 12px 14px; font-size: 0.85rem; outline: none; resize: vertical;"></textarea>
            </div>

            <!-- Actions -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 16px;">
                <button type="button" onclick="closeNewTicketModal()" style="padding: 10px 18px; border: 1px solid #CBD5E1; background: #FFF; color: #475569; border-radius: 8px; font-weight: 600; font-size: 0.84rem; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding: 10px 22px; border: none; background: #4F46E5; color: #FFF; border-radius: 8px; font-weight: 700; font-size: 0.84rem; cursor: pointer; box-shadow: 0 2px 8px rgba(79,70,229,0.35);">
                    Create Support Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
function openNewTicketModal() {
    const modal = document.getElementById('newTicketModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeNewTicketModal() {
    const modal = document.getElementById('newTicketModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewTicketModal();
    }
});

function loadCustomerOrders(encCustId) {
    const orderSelect = document.getElementById('modalOrderSelect');
    if (!orderSelect) return;
    
    orderSelect.innerHTML = '<option value="">Loading customer orders...</option>';
    
    if (!encCustId) {
        orderSelect.innerHTML = '<option value="">-- No Order Associated --</option>';
        return;
    }

    fetch('<?= url("portal/tickets/customer-orders") ?>/' + encodeURIComponent(encCustId))
        .then(res => res.json())
        .then(data => {
            orderSelect.innerHTML = '<option value="">-- No Order Associated --</option>';
            if (data.success && data.orders && data.orders.length > 0) {
                data.orders.forEach(ord => {
                    const opt = document.createElement('option');
                    opt.value = ord.encrypted_id;
                    opt.textContent = '#' + ord.order_number + ' (₹' + Number(ord.grand_total).toLocaleString() + ' - ' + ord.status.toUpperCase() + ')';
                    orderSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- No Previous Orders Found --';
                orderSelect.appendChild(opt);
            }
        })
        .catch(err => {
            console.error('Failed to load orders', err);
            orderSelect.innerHTML = '<option value="">-- No Order Associated --</option>';
        });
}

function updateTicketStatusQuick(encTicketId, newStatus, ticketId) {
    if (!newStatus) return;
    
    const formData = new FormData();
    formData.append('status', newStatus);
    formData.append('_csrf_token', '<?= csrf_token() ?>');

    fetch('<?= url("portal/tickets") ?>/' + encTicketId + '/status', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('status-badge-' + ticketId);
            if (badge) {
                badge.textContent = data.label;
                const statusStyles = {
                    'open': 'background: rgba(245, 158, 11, 0.12); color: #B45309; border: 1px solid rgba(245, 158, 11, 0.3);',
                    'acknowledged': 'background: rgba(147, 51, 234, 0.1); color: #7E22CE; border: 1px solid rgba(147, 51, 234, 0.25);',
                    'in_progress': 'background: rgba(2, 132, 199, 0.1); color: #0284C7; border: 1px solid rgba(2, 132, 199, 0.25);',
                    'resolved': 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                    'closed': 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);'
                };
                badge.style.cssText = 'display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; ' + (statusStyles[data.status] || '');
            }
        } else {
            alert(data.message || 'Status update failed.');
        }
    })
    .catch(err => {
        console.error('Quick status update error:', err);
    });
}
</script>
