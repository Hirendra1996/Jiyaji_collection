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
                        <a href="<?= url('admin/customers') ?>" style="color: var(--brand-blue);">Customers & Support</a>
                        <span>&nbsp;/&nbsp;</span>
                        <a href="<?= url('admin/customers/' . $customer['encrypted_id']) ?>" style="color: var(--brand-blue);"><?= htmlspecialchars($customer['name']) ?></a>
                        <span>&nbsp;/&nbsp;</span>
                        <span>Edit Profile</span>
                    </div>
                    <h1 class="welcome-title">Edit Customer: <?= htmlspecialchars($customer['name']) ?></h1>
                    <p class="welcome-subtitle">Modify contact credentials, account security flags, and verification status.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/customers/' . $customer['encrypted_id']) ?>" class="btn-secondary" style="height: 42px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px;">
                        &larr; Return to Profile
                    </a>
                </div>
            </div>

            <!-- Customer Edit Form -->
            <form action="<?= url('admin/customers/' . $customer['encrypted_id'] . '/update') ?>" method="POST" id="editCustomerForm">
                <?= csrf_field() ?>

                <div class="product-form-grid">
                    <!-- Left Column: Primary Account Information -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Account Details Card -->
                        <div class="card-panel">
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0 0 18px 0; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                                Client Identity & Contact
                            </h2>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                        Full Name <span style="color: var(--status-danger);">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        id="customerNameInput"
                                        class="form-input" 
                                        value="<?= htmlspecialchars($customer['name']) ?>"
                                        style="width: 100%;"
                                        required
                                    >
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            Email Address <span style="color: var(--status-danger);">*</span>
                                        </label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            id="customerEmailInput"
                                            class="form-input" 
                                            value="<?= htmlspecialchars($customer['email']) ?>"
                                            style="width: 100%;"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            Phone Number
                                        </label>
                                        <input 
                                            type="tel" 
                                            name="phone" 
                                            id="customerPhoneInput"
                                            class="form-input" 
                                            value="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                                            placeholder="e.g. 9820011223"
                                            style="width: 100%;"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                        Reset Account Password
                                    </label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        class="form-input" 
                                        placeholder="Leave blank to retain current password"
                                        style="width: 100%;"
                                    >
                                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;">
                                        Only enter a new password if the customer has requested an administrative credentials reset.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Account Status & Actions -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Account Settings Card -->
                        <div class="card-panel">
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0 0 18px 0; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                                Account Settings
                            </h2>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                        Account Status
                                    </label>
                                    <select name="is_active" class="form-input" style="width: 100%;">
                                        <option value="1" <?= $customer['is_active'] ? 'selected' : '' ?>>🟢 Active (Can Login & Order)</option>
                                        <option value="0" <?= !$customer['is_active'] ? 'selected' : '' ?>>🔴 Suspended (Access Blocked)</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.86rem; cursor: pointer; padding-top: 4px;">
                                        <input type="checkbox" name="email_verified" value="1" <?= $customer['email_verified'] ? 'checked' : '' ?>>
                                        <span style="font-weight: 600;">Email Verified</span>
                                    </label>
                                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px; margin-left: 24px;">
                                        Whether client has verified their email address.
                                    </div>
                                </div>

                                <div style="border-top: 1px solid var(--border-light); padding-top: 12px; font-size: 0.78rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                                    <div>Registered: <?= date('M d, Y, h:i A', strtotime($customer['created_at'])) ?></div>
                                    <div>Last Updated: <?= date('M d, Y, h:i A', strtotime($customer['updated_at'])) ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Controls Card -->
                        <div class="card-panel" style="background: var(--bg-surface-alt, #fafafc);">
                            <button type="submit" class="btn-primary-gradient" style="width: 100%; height: 44px; font-weight: 700; font-size: 0.95rem; margin-bottom: 10px;" id="btnSubmitEditCustomer">
                                Save Profile Changes
                            </button>
                            <a href="<?= url('admin/customers/' . $customer['encrypted_id']) ?>" class="btn-secondary" style="width: 100%; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
