<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="dashboard-content">
            <!-- Breadcrumbs & Header -->
            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 8px;">
                    <a href="<?= url('admin/dashboard') ?>" style="color: var(--brand-blue);">Dashboard</a>
                    <span>&nbsp;/&nbsp;</span>
                    <a href="<?= url('admin/tickets') ?>" style="color: var(--brand-blue);">Support Tickets</a>
                    <span>&nbsp;/&nbsp;</span>
                    <span><?= htmlspecialchars($ticket['ticket_code']) ?></span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                            <h1 class="welcome-title" style="margin-bottom: 0;">
                                <?= htmlspecialchars($ticket['subject']) ?>
                            </h1>
                            <span style="font-size: 0.88rem; font-weight: 800; color: var(--text-muted); background: var(--border-light); padding: 3px 8px; border-radius: 6px;">
                                <?= $ticket['ticket_code'] ?>
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span>Opened <?= date('M d, Y \a\t h:i A', strtotime($ticket['created_at'])) ?></span>
                            <span>•</span>
                            <span>Last active <?= date('M d, Y \a\t h:i A', strtotime($ticket['updated_at'])) ?></span>
                        </div>
                    </div>

                    <!-- Top Action Controls -->
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <!-- Priority Dropdown -->
                        <form action="<?= url('admin/tickets/' . $ticket['encrypted_id'] . '/priority') ?>" method="POST" style="margin: 0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_url" value="admin/tickets/<?= $ticket['encrypted_id'] ?>">
                            <select name="priority" onchange="this.form.submit()" class="form-input" style="height: 38px; padding: 0 10px; font-size: 0.78rem; font-weight: 700; width: auto;">
                                <option value="critical" <?= $ticket['priority'] === 'critical' ? 'selected' : '' ?>>🔴 Critical</option>
                                <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>🟠 High</option>
                                <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>🔵 Medium</option>
                                <option value="low" <?= $ticket['priority'] === 'low' ? 'selected' : '' ?>>⚪ Low</option>
                            </select>
                        </form>

                        <!-- Status Dropdown -->
                        <form action="<?= url('admin/tickets/' . $ticket['encrypted_id'] . '/status') ?>" method="POST" style="margin: 0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_url" value="admin/tickets/<?= $ticket['encrypted_id'] ?>">
                            <select name="status" onchange="this.form.submit()" class="form-input" style="height: 38px; padding: 0 10px; font-size: 0.78rem; font-weight: 700; width: auto;">
                                <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>🟣 Open</option>
                                <option value="acknowledged" <?= $ticket['status'] === 'acknowledged' ? 'selected' : '' ?>>🟡 Acknowledged</option>
                                <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>🔵 In Progress</option>
                                <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>🟢 Resolved</option>
                                <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>⚫ Closed</option>
                            </select>
                        </form>

                        <a href="<?= url('admin/tickets') ?>" class="btn-secondary" style="height: 38px; padding: 0 14px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px;">
                            <span>&larr; Back to Queue</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2-Column Layout: Left Conversation Stream, Right Context Dossier -->
            <div class="product-form-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
                <!-- LEFT: Conversation Thread Timeline & Reply Composer -->
                <div>
                    <!-- Message Thread Stream Card -->
                    <div class="card-panel" style="padding: 24px; margin-bottom: 20px;">
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
                            <span>Conversation Timeline</span>
                            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);"><?= count($messages) ?> Messages</span>
                        </h3>

                        <?php if (empty($messages)): ?>
                            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                                No message entries recorded for this inquiry.
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 18px;">
                                <?php foreach ($messages as $msg): ?>
                                    <?php
                                    $isAdmin = ($msg['sender_type'] === 'admin');
                                    ?>
                                    <div style="display: flex; gap: 14px; align-items: flex-start; <?= $isAdmin ? 'flex-direction: row-reverse;' : '' ?>">
                                        <!-- Avatar -->
                                        <div style="width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; <?= $isAdmin ? 'background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff;' : 'background: linear-gradient(135deg, rgba(45, 130, 255, 0.2), rgba(140, 48, 245, 0.2)); color: var(--brand-blue);' ?>">
                                            <?= strtoupper(substr($msg['sender_name'] ?: 'U', 0, 1)) ?>
                                        </div>

                                        <!-- Message Bubble -->
                                        <div style="max-width: 82%; width: 100%;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; <?= $isAdmin ? 'flex-direction: row-reverse;' : '' ?>">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span style="font-weight: 700; font-size: 0.84rem; color: var(--text-primary);">
                                                        <?= htmlspecialchars($msg['sender_name']) ?>
                                                    </span>
                                                    <?php if ($isAdmin): ?>
                                                        <span style="background: rgba(140, 48, 245, 0.1); color: var(--brand-purple); font-size: 0.65rem; font-weight: 800; padding: 1px 6px; border-radius: 4px; text-transform: uppercase;">
                                                            Concierge Staff
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="background: rgba(45, 130, 255, 0.1); color: var(--brand-blue); font-size: 0.65rem; font-weight: 800; padding: 1px 6px; border-radius: 4px; text-transform: uppercase;">
                                                            Client
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span style="font-size: 0.72rem; color: var(--text-muted);">
                                                    <?= date('M d, h:i A', strtotime($msg['created_at'])) ?>
                                                </span>
                                            </div>

                                            <div style="padding: 14px 16px; border-radius: 10px; font-size: 0.86rem; line-height: 1.55; white-space: pre-line; <?= $isAdmin ? 'background: rgba(140, 48, 245, 0.05); border: 1px solid rgba(140, 48, 245, 0.2); color: var(--text-primary);' : 'background: var(--bg-surface-alt, #fafafc); border: 1px solid var(--border-light); color: var(--text-primary);' ?>">
                                                <?= htmlspecialchars($msg['message']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Composer Card -->
                    <div class="card-panel" style="padding: 24px;">
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-top: 0; margin-bottom: 14px;">
                            Post Concierge Response
                        </h3>

                        <form action="<?= url('admin/tickets/' . $ticket['encrypted_id'] . '/reply') ?>" method="POST" id="ticketReplyForm">
                            <?= csrf_field() ?>

                            <div style="margin-bottom: 16px;">
                                <textarea 
                                    name="message" 
                                    id="replyMessageInput" 
                                    class="form-input" 
                                    rows="5" 
                                    style="height: auto; padding: 12px 14px; font-size: 0.88rem; line-height: 1.5;" 
                                    placeholder="Compose your response to the client regarding this inquiry or order alteration..." 
                                    required
                                ></textarea>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div style="font-size: 0.76rem; color: var(--text-muted);">
                                    Posting as <strong><?= htmlspecialchars(auth_admin()['name'] ?? 'Administrator') ?></strong>
                                </div>

                                <div style="display: flex; gap: 8px;">
                                    <button type="submit" name="status_action" value="in_progress" class="btn-secondary" style="padding: 9px 15px; font-size: 0.82rem; font-weight: 600;">
                                        Reply & Set In Progress
                                    </button>

                                    <button type="submit" name="status_action" value="resolved" class="btn-secondary" style="padding: 9px 15px; font-size: 0.82rem; font-weight: 600; color: #059669; border-color: rgba(16, 185, 129, 0.4);">
                                        ✓ Reply & Resolve
                                    </button>

                                    <button type="submit" name="status_action" value="" class="btn-primary" style="padding: 9px 18px; font-size: 0.82rem; font-weight: 700;">
                                        Send Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- RIGHT: Context Dossiers (Customer, Order, Ticket Metadata) -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Customer Dossier Card -->
                    <div class="card-panel" style="padding: 20px;">
                        <h4 style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800; color: var(--text-muted); margin-top: 0; margin-bottom: 14px;">
                            Client Profile
                        </h4>

                        <?php if (!empty($ticket['customer_name'])): ?>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, rgba(219, 39, 119, 0.2), rgba(140, 48, 245, 0.2)); color: #db2777; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                                    <?= strtoupper(substr($ticket['customer_name'], 0, 1)) ?>
                                </div>
                                <div style="overflow: hidden;">
                                    <div style="font-weight: 700; font-size: 0.94rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars($ticket['customer_name']) ?>
                                    </div>
                                    <div style="font-size: 0.76rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars($ticket['customer_email']) ?>
                                    </div>
                                    <?php if (!empty($ticket['customer_phone'])): ?>
                                        <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                                            📞 <?= htmlspecialchars($ticket['customer_phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 12px; background: var(--bg-surface-alt, #fafafc); border-radius: 8px; margin-bottom: 14px; text-align: center;">
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Lifetime Spend</div>
                                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary); margin-top: 2px;">
                                        <?= currency($ticket['customer_lifetime_spend'] ?? 0) ?>
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Orders Placed</div>
                                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary); margin-top: 2px;">
                                        <?= (int)($ticket['customer_orders_count'] ?? 0) ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($ticket['customer_encrypted_id'])): ?>
                                <a href="<?= url('admin/customers/' . $ticket['customer_encrypted_id']) ?>" class="btn-secondary" style="display: block; text-align: center; font-size: 0.8rem; font-weight: 600; padding: 8px 12px;">
                                    View Full Client Dossier &rarr;
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="font-size: 0.84rem; color: var(--text-muted); font-style: italic;">
                                Guest inquiry without registered client profile.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Associated Order Card -->
                    <?php if (!empty($ticket['linked_order_number'])): ?>
                        <div class="card-panel" style="padding: 20px; border-left: 4px solid var(--brand-purple);">
                            <h4 style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800; color: var(--brand-purple); margin-top: 0; margin-bottom: 12px;">
                                Associated Order
                            </h4>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary);">
                                    <?= htmlspecialchars($ticket['linked_order_number']) ?>
                                </div>
                                <span style="font-size: 0.72rem; font-weight: 700; padding: 2px 7px; border-radius: 999px; background: rgba(140, 48, 245, 0.1); color: var(--brand-purple);">
                                    <?= ucfirst($ticket['linked_order_status'] ?? '') ?>
                                </span>
                            </div>

                            <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 4px;">
                                Total: <strong style="color: var(--text-primary);"><?= currency($ticket['linked_order_total']) ?></strong>
                            </div>

                            <div style="font-size: 0.76rem; color: var(--text-muted); margin-bottom: 14px;">
                                Placed: <?= date('M d, Y', strtotime($ticket['linked_order_date'])) ?>
                            </div>

                            <?php if (!empty($ticket['order_encrypted_id'])): ?>
                                <a href="<?= url('admin/orders/' . $ticket['order_encrypted_id']) ?>" class="btn-secondary" style="display: block; text-align: center; font-size: 0.8rem; font-weight: 600; padding: 8px 12px;">
                                    View Order Details &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Ticket Assignment & Metadata Card -->
                    <div class="card-panel" style="padding: 20px;">
                        <h4 style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800; color: var(--text-muted); margin-top: 0; margin-bottom: 14px;">
                            Staff Assignment & Case Meta
                        </h4>

                        <!-- Assign Staff Form -->
                        <form action="<?= url('admin/tickets/' . $ticket['encrypted_id'] . '/assign') ?>" method="POST" style="margin-bottom: 16px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return_url" value="admin/tickets/<?= $ticket['encrypted_id'] ?>">
                            <label class="form-label" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 5px; color: var(--text-muted);">Assigned Concierge</label>
                            <div style="display: flex; gap: 8px;">
                                <select name="assigned_to" class="form-input" style="height: 38px; font-size: 0.82rem;">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($admins as $adm): ?>
                                        <option value="<?= $adm['id'] ?>" <?= (string)$ticket['assigned_to'] === (string)$adm['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($adm['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-secondary" style="padding: 0 12px; height: 38px; font-size: 0.8rem;">Save</button>
                            </div>
                        </form>

                        <div style="border-top: 1px solid var(--border-light); padding-top: 12px; font-size: 0.78rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Ticket Code:</span>
                                <strong style="color: var(--text-primary);"><?= $ticket['ticket_code'] ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Priority Urgency:</span>
                                <strong style="text-transform: uppercase; color: var(--text-primary);"><?= $ticket['priority'] ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Current Status:</span>
                                <strong style="text-transform: capitalize; color: var(--text-primary);"><?= str_replace('_', ' ', $ticket['status']) ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Opened:</span>
                                <span><?= date('M d, Y h:i A', strtotime($ticket['created_at'])) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Last Activity:</span>
                                <span><?= date('M d, Y h:i A', strtotime($ticket['updated_at'])) ?></span>
                            </div>
                        </div>

                        <!-- Danger Zone: Delete Ticket -->
                        <div style="border-top: 1px solid var(--border-light); padding-top: 14px; margin-top: 14px;">
                            <form action="<?= url('admin/tickets/' . $ticket['encrypted_id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Permanently delete this entire support ticket and thread? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-secondary" style="width: 100%; color: #dc2626; border-color: rgba(239, 68, 68, 0.3); font-size: 0.78rem; padding: 8px;">
                                    🗑️ Delete Ticket & Thread
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
