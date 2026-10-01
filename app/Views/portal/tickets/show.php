<?php
$title      = $title ?? 'Support Ticket Thread | Jiyaji LX Operations Portal';
$ticket     = $ticket ?? [];
$messages   = $messages ?? [];
$admins     = $admins ?? [];
$canReply   = $canReply ?? false;
$canResolve = $canResolve ?? false;

include __DIR__ . '/../layouts/header.php';

$pri = strtolower($ticket['priority'] ?? 'medium');
$priStyle = match($pri) {
    'critical' => 'background: rgba(239, 68, 68, 0.15); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.3);',
    'high'     => 'background: rgba(245, 158, 11, 0.15); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.3);',
    'low'      => 'background: rgba(148, 163, 184, 0.15); color: #64748B; border: 1px solid rgba(148, 163, 184, 0.3);',
    default    => 'background: rgba(79, 70, 229, 0.12); color: #4F46E5; border: 1px solid rgba(79, 70, 229, 0.25);'
};

$st = strtolower($ticket['status'] ?? 'open');
$stStyle = match($st) {
    'open'         => 'background: rgba(245, 158, 11, 0.12); color: #B45309; border: 1px solid rgba(245, 158, 11, 0.3);',
    'acknowledged' => 'background: rgba(147, 51, 234, 0.1); color: #7E22CE; border: 1px solid rgba(147, 51, 234, 0.25);',
    'in_progress'  => 'background: rgba(2, 132, 199, 0.1); color: #0284C7; border: 1px solid rgba(2, 132, 199, 0.25);',
    'resolved'     => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
    'closed'       => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);',
    default        => 'background: rgba(79, 70, 229, 0.1); color: #4F46E5; border: 1px solid rgba(79, 70, 229, 0.25);'
};
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <a href="<?= url('portal/dashboard') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Dashboard</a>
                    <span>&rsaquo;</span>
                    <a href="<?= url('portal/tickets') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Support Desk</a>
                    <span>&rsaquo;</span>
                    <span style="color: #0F172A; font-weight: 700; font-family: monospace;"><?= htmlspecialchars($ticket['ticket_code']) ?></span>
                </div>

                <a href="<?= url('portal/tickets') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.82rem; font-weight: 600; color: #334155; text-decoration: none;">
                    &larr; Back to Ticket Queue
                </a>
            </div>

            <!-- Ticket Header Card -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span style="font-family: monospace; font-size: 1.15rem; font-weight: 800; color: #0F172A;">
                            <?= htmlspecialchars($ticket['ticket_code']) ?>
                        </span>
                        <span style="padding: 3px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; <?= $stStyle ?>">
                            <?= ucwords(str_replace('_', ' ', $st)) ?>
                        </span>
                        <span style="padding: 3px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; <?= $priStyle ?>">
                            <?= $pri ?> Priority
                        </span>
                        <?php if (!empty($ticket['linked_order_number'])): ?>
                            <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; padding: 3px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; font-family: monospace;">
                                Order #<?= htmlspecialchars($ticket['linked_order_number']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 1.4rem; font-weight: 800; color: #0F172A; margin: 0 0 6px 0; letter-spacing: -0.01em;">
                        <?= htmlspecialchars($ticket['subject']) ?>
                    </h1>
                    <div style="font-size: 0.8rem; color: #64748B; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <span>Opened: <strong><?= !empty($ticket['created_at']) ? date('M d, Y h:i A', strtotime($ticket['created_at'])) : '—' ?></strong></span>
                        <span>&bull;</span>
                        <span>Last Activity: <strong><?= !empty($ticket['updated_at']) ? date('M d, Y h:i A', strtotime($ticket['updated_at'])) : '—' ?></strong></span>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <?php if ($canResolve): ?>
                        <form action="<?= url('portal/tickets/' . $ticket['encrypted_id'] . '/status') ?>" method="POST" style="margin: 0; display: inline-flex; align-items: center; gap: 6px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_url" value="portal/tickets/<?= $ticket['encrypted_id'] ?>">
                            <select name="status" onchange="this.form.submit()" style="height: 38px; padding: 0 10px; font-size: 0.8rem; font-weight: 600; border: 1px solid #CBD5E1; border-radius: 8px; background: #FFF; outline: none; cursor: pointer;">
                                <option value="" disabled selected>Update Status...</option>
                                <option value="open">Mark as Open</option>
                                <option value="acknowledged">Mark as Acknowledged</option>
                                <option value="in_progress">Mark as In Progress</option>
                                <option value="resolved">Mark as Resolved</option>
                                <option value="closed">Mark as Closed</option>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2-Column Main Workspace -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">

                <!-- LEFT COLUMN: Conversation Thread & Reply Box -->
                <div>
                    <!-- Conversation Thread Header -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <h2 style="font-size: 1.05rem; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span>Conversation History</span>
                            <span style="background: #E0E7FF; color: #4338CA; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 999px;">
                                <?= count($messages) ?> Messages
                            </span>
                        </h2>
                    </div>

                    <!-- Messages Timeline -->
                    <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                        <?php if (empty($messages)): ?>
                            <div style="background: #FFFFFF; border: 1px dashed #CBD5E1; border-radius: 12px; padding: 32px; text-align: center; color: #64748B;">
                                No conversation messages logged for this ticket yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $idx => $m): 
                                $isStaff = ($m['sender_type'] === 'admin');
                                $senderName = $m['sender_name'] ?: ($isStaff ? 'Staff Concierge' : 'Customer');
                                $initial = strtoupper(substr($senderName, 0, 1));
                            ?>
                                <div style="background: #FFFFFF; border: 1px solid <?= $isStaff ? '#C7D2FE' : '#E2E8F0' ?>; border-left: 4px solid <?= $isStaff ? '#4F46E5' : '#0284C7' ?>; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                    <!-- Message Sender Info -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $isStaff ? 'linear-gradient(135deg, #312E81, #4F46E5)' : 'linear-gradient(135deg, #0369A1, #0EA5E9)' ?>; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0;">
                                                <?= $initial ?>
                                            </div>
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <span style="font-weight: 800; font-size: 0.9rem; color: #0F172A;">
                                                        <?= htmlspecialchars($senderName) ?>
                                                    </span>
                                                    <?php if ($isStaff): ?>
                                                        <span style="background: #EEF2FF; color: #4338CA; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px; text-transform: uppercase;">
                                                            Staff Concierge
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="background: #F0FDF4; color: #166534; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 999px; text-transform: uppercase;">
                                                            Client Inquiry
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.74rem; color: #64748B;">
                                                    <?= htmlspecialchars($m['sender_email'] ?? '') ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">
                                            <?= !empty($m['created_at']) ? date('M d, Y &bull; h:i A', strtotime($m['created_at'])) : '' ?>
                                        </div>
                                    </div>

                                    <!-- Message Body -->
                                    <div style="font-size: 0.88rem; line-height: 1.65; color: #1E293B; white-space: pre-line; padding-left: 46px;">
                                        <?= htmlspecialchars($m['message']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Concierge Staff Reply Composer -->
                    <?php if ($canReply): ?>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2.5">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    Post Concierge Reply
                                </h3>

                                <!-- Canned response helpers -->
                                <div style="font-size: 0.75rem; color: #64748B;">
                                    Quick Macro Snippets:
                                </div>
                            </div>

                            <!-- Quick Macros Toolbar -->
                            <div style="display: flex; gap: 6px; margin-bottom: 12px; flex-wrap: wrap;">
                                <button type="button" onclick="insertMacro('Namaste! Thank you for reaching out. We are currently coordinating with our master tailor regarding your bespoke measurements.')"
                                        style="font-size: 0.72rem; font-weight: 600; padding: 4px 10px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 6px; color: #334155; cursor: pointer;">
                                    ✂️ Tailor Atelier Check
                                </button>
                                <button type="button" onclick="insertMacro('We are pleased to confirm that your shipment has been expedited with our premium courier partner. You will receive an SMS milestone update shortly.')"
                                        style="font-size: 0.72rem; font-weight: 600; padding: 4px 10px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 6px; color: #334155; cursor: pointer;">
                                    🚚 Shipment Expedited
                                </button>
                                <button type="button" onclick="insertMacro('Your alteration and return exchange request has been approved. Our representative will arrange a home concierge pickup.')"
                                        style="font-size: 0.72rem; font-weight: 600; padding: 4px 10px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 6px; color: #334155; cursor: pointer;">
                                    ✨ Exchange Approved
                                </button>
                                <button type="button" onclick="insertMacro('We have addressed and fulfilled your inquiry. Thank you for shopping with Jiyaji Collection. Wishing you an extraordinary celebration!')"
                                        style="font-size: 0.72rem; font-weight: 600; padding: 4px 10px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 6px; color: #334155; cursor: pointer;">
                                    ✅ Issue Resolved
                                </button>
                            </div>

                            <form action="<?= url('portal/tickets/' . $ticket['encrypted_id'] . '/reply') ?>" method="POST" id="ticketReplyForm">
                                <?= csrf_field() ?>

                                <div style="margin-bottom: 14px;">
                                    <textarea name="message" id="replyMessage" required rows="5" placeholder="Write a courteous, professional concierge reply to the client..." 
                                              style="width: 100%; border: 1px solid #CBD5E1; border-radius: 10px; padding: 14px; font-size: 0.88rem; outline: none; resize: vertical; line-height: 1.5;"
                                              onfocus="this.style.borderColor='#4F46E5';" onblur="this.style.borderColor='#CBD5E1';"></textarea>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-top: 1px solid #F1F5F9; padding-top: 14px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <label style="font-size: 0.78rem; font-weight: 700; color: #475569;">After Replying:</label>
                                        <select name="status_action" style="height: 38px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 10px; font-size: 0.82rem; background: #FFF; outline: none;">
                                            <option value="">Keep current status (<?= ucwords(str_replace('_', ' ', $st)) ?>)</option>
                                            <option value="in_progress" <?= $st !== 'in_progress' ? 'selected' : '' ?>>Set to In Progress</option>
                                            <option value="resolved">Mark as Resolved</option>
                                            <option value="closed">Mark as Closed</option>
                                        </select>
                                    </div>

                                    <button type="submit" style="padding: 10px 24px; background: #4F46E5; color: #FFFFFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 2px 8px rgba(79,70,229,0.35);">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="22" y1="2" x2="11" y2="13"></line>
                                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                        </svg>
                                        <span>Send Reply</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RIGHT COLUMN: Context & Control Dossier -->
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    <!-- Customer Profile Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="font-size: 0.72rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                            Client Profile
                        </div>

                        <?php if (!empty($ticket['customer_name'])): ?>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, rgba(79,70,229,0.2), rgba(14,165,233,0.2)); color: #4F46E5; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; flex-shrink: 0;">
                                    <?= strtoupper(substr($ticket['customer_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.95rem; color: #0F172A;">
                                        <?= htmlspecialchars($ticket['customer_name']) ?>
                                    </div>
                                    <div style="font-size: 0.78rem; color: #64748B;">
                                        <?= htmlspecialchars($ticket['customer_email']) ?>
                                    </div>
                                    <?php if (!empty($ticket['customer_phone'])): ?>
                                        <div style="font-size: 0.76rem; color: #475569; margin-top: 2px;">
                                            📞 <?= htmlspecialchars($ticket['customer_phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Customer Quick Metrics -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px;">
                                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; text-align: center;">
                                    <div style="font-size: 0.68rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Lifetime Orders</div>
                                    <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-top: 2px;">
                                        <?= (int)($ticket['customer_orders_count'] ?? 0) ?>
                                    </div>
                                </div>
                                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; text-align: center;">
                                    <div style="font-size: 0.68rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Spent</div>
                                    <div style="font-size: 1.15rem; font-weight: 800; color: #0F172A; margin-top: 2px;">
                                        <?= currency($ticket['customer_lifetime_spend'] ?? 0) ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($ticket['customer_encrypted_id'])): ?>
                                <a href="<?= url('portal/customers/' . $ticket['customer_encrypted_id']) ?>" 
                                   style="display: block; text-align: center; padding: 8px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 8px; color: #334155; font-size: 0.8rem; font-weight: 700; text-decoration: none;">
                                    View Full Customer CRM Dossier &rarr;
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="color: #64748B; font-size: 0.85rem; font-style: italic;">
                                Guest visitor or client details not linked.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Associated Order Card -->
                    <?php if (!empty($ticket['linked_order_number'])): ?>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="font-size: 0.72rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                                Associated Order
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-family: monospace; font-weight: 800; font-size: 1rem; color: #4F46E5;">
                                    #<?= htmlspecialchars($ticket['linked_order_number']) ?>
                                </span>
                                <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background: #EFF6FF; color: #1D4ED8;">
                                    <?= htmlspecialchars($ticket['linked_order_status'] ?? '') ?>
                                </span>
                            </div>

                            <div style="font-size: 1.1rem; font-weight: 800; color: #0F172A; margin-bottom: 4px;">
                                <?= currency($ticket['linked_order_total'] ?? 0) ?>
                            </div>
                            <div style="font-size: 0.74rem; color: #64748B; margin-bottom: 14px;">
                                Placed: <?= !empty($ticket['linked_order_date']) ? date('M d, Y', strtotime($ticket['linked_order_date'])) : '—' ?>
                            </div>

                            <?php if (!empty($ticket['order_encrypted_id'])): ?>
                                <a href="<?= url('portal/orders/' . $ticket['order_encrypted_id']) ?>" 
                                   style="display: block; text-align: center; padding: 8px; background: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 8px; color: #334155; font-size: 0.8rem; font-weight: 700; text-decoration: none;">
                                    View Order &amp; Invoice &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Agent Assignment Card -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="font-size: 0.72rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                            Assigned Concierge Staff
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 800;">
                                <?= !empty($ticket['assigned_agent_name']) ? strtoupper(substr($ticket['assigned_agent_name'], 0, 1)) : '?' ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.88rem; color: #0F172A;">
                                    <?= htmlspecialchars($ticket['assigned_agent_name'] ?? 'Unassigned') ?>
                                </div>
                                <div style="font-size: 0.74rem; color: #64748B;">
                                    <?= htmlspecialchars($ticket['assigned_agent_email'] ?? 'No active staff owner') ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($canResolve): ?>
                            <form action="<?= url('portal/tickets/' . $ticket['encrypted_id'] . '/assign') ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="return_url" value="portal/tickets/<?= $ticket['encrypted_id'] ?>">
                                <div style="display: flex; gap: 8px;">
                                    <select name="admin_id" style="flex: 1; height: 38px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 10px; font-size: 0.8rem; background: #FFF; outline: none;">
                                        <option value="">-- Unassigned --</option>
                                        <?php foreach ($admins as $adm): ?>
                                            <option value="<?= $adm['id'] ?>" <?= (string)($ticket['assigned_to'] ?? '') === (string)$adm['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($adm['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" style="padding: 0 14px; height: 38px; background: #0F172A; color: #FFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                        Reassign
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Priority / Urgency Card -->
                    <?php if ($canResolve): ?>
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="font-size: 0.72rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                                Adjust Urgency Level
                            </div>

                            <form action="<?= url('portal/tickets/' . $ticket['encrypted_id'] . '/priority') ?>" method="POST" style="margin: 0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="return_url" value="portal/tickets/<?= $ticket['encrypted_id'] ?>">
                                <div style="display: flex; gap: 8px;">
                                    <select name="priority" style="flex: 1; height: 38px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0 10px; font-size: 0.8rem; background: #FFF; outline: none;">
                                        <option value="low" <?= $pri === 'low' ? 'selected' : '' ?>>⚪ Low (General Inquiry)</option>
                                        <option value="medium" <?= $pri === 'medium' ? 'selected' : '' ?>>🔵 Medium (Standard 24h SLA)</option>
                                        <option value="high" <?= $pri === 'high' ? 'selected' : '' ?>>🟠 High (Order Alteration/Courier)</option>
                                        <option value="critical" <?= $pri === 'critical' ? 'selected' : '' ?>>🔴 Critical (VIP Escalation)</option>
                                    </select>
                                    <button type="submit" style="padding: 0 14px; height: 38px; background: #0F172A; color: #FFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                                        Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Danger Zone Card -->
                    <?php if ($canResolve): ?>
                        <div style="background: #FFF1F2; border: 1px solid #FECDD3; border-radius: 14px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="font-size: 0.72rem; font-weight: 800; color: #E11D48; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                                Danger Zone
                            </div>
                            <p style="font-size: 0.78rem; color: #9F1239; margin: 0 0 12px 0;">
                                Permanently delete this entire support conversation thread. This action cannot be reversed.
                            </p>
                            <form action="<?= url('portal/tickets/' . $ticket['encrypted_id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Permanently remove this ticket and all conversation messages?');">
                                <?= csrf_field() ?>
                                <button type="submit" style="width: 100%; padding: 8px; background: #E11D48; color: #FFF; border: none; border-radius: 8px; font-weight: 700; font-size: 0.78rem; cursor: pointer;">
                                    Permanently Delete Ticket
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>

<script>
function insertMacro(text) {
    const area = document.getElementById('replyMessage');
    if (area) {
        if (area.value.trim() !== '') {
            area.value += "\n\n" + text;
        } else {
            area.value = text;
        }
        area.focus();
    }
}
</script>
