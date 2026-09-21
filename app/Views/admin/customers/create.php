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
                        <span>Create Client</span>
                    </div>
                    <h1 class="welcome-title">Create Luxury Customer Profile</h1>
                    <p class="welcome-subtitle">Register a new client account with contact credentials, verification status, and default destination address.</p>
                </div>
                <div class="banner-controls">
                    <a href="<?= url('admin/customers') ?>" class="btn-secondary" style="height: 42px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px;">
                        &larr; Back to Customers
                    </a>
                </div>
            </div>

            <!-- Customer Creation Form -->
            <form action="<?= url('admin/customers/store') ?>" method="POST" id="createCustomerForm">
                <?= csrf_field() ?>

                <div class="product-form-grid">
                    <!-- Left Column: Primary Account Details & Optional Address -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Account Details Card -->
                        <div class="card-panel">
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0 0 18px 0; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                                Client Credentials
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
                                        placeholder="e.g. Radhika Merchant"
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
                                            placeholder="client@luxury.com"
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
                                            placeholder="9820011223"
                                            style="width: 100%;"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                        Initial Account Password
                                    </label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        class="form-input" 
                                        placeholder="Defaults to Welcome@123 if left empty"
                                        style="width: 100%;"
                                    >
                                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px;">
                                        Client can reset or change this password at any time via login portal.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Initial Delivery Destination Card -->
                        <div class="card-panel">
                            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0 0 18px 0; border-bottom: 1px solid var(--border-light); padding-bottom: 10px;">
                                Initial Delivery Address (Optional)
                            </h2>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            Address Label
                                        </label>
                                        <input 
                                            type="text" 
                                            name="address_label" 
                                            class="form-input" 
                                            value="Home"
                                            style="width: 100%;"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            Recipient Name
                                        </label>
                                        <input 
                                            type="text" 
                                            name="address_recipient" 
                                            class="form-input" 
                                            placeholder="Leave empty to use client name"
                                            style="width: 100%;"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                        Address Line 1
                                    </label>
                                    <input 
                                        type="text" 
                                        name="address_line1" 
                                        class="form-input" 
                                        placeholder="Penthouse 4B, Signature Towers, Altamount Road"
                                        style="width: 100%;"
                                    >
                                </div>

                                <div>
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                        Address Line 2 (Optional)
                                    </label>
                                    <input 
                                        type="text" 
                                        name="address_line2" 
                                        class="form-input" 
                                        placeholder="Near Imperial Heights"
                                        style="width: 100%;"
                                    >
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            City
                                        </label>
                                        <input 
                                            type="text" 
                                            name="city" 
                                            class="form-input" 
                                            placeholder="Mumbai"
                                            style="width: 100%;"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            State
                                        </label>
                                        <input 
                                            type="text" 
                                            name="state" 
                                            class="form-input" 
                                            placeholder="Maharashtra"
                                            style="width: 100%;"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                                            PIN Code
                                        </label>
                                        <input 
                                            type="text" 
                                            name="pincode" 
                                            class="form-input" 
                                            placeholder="400026"
                                            style="width: 100%;"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Account Status, Permissions & Actions -->
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
                                        <option value="1" selected>🟢 Active (Can Login & Order)</option>
                                        <option value="0">🔴 Suspended (Access Blocked)</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.86rem; cursor: pointer; padding-top: 4px;">
                                        <input type="checkbox" name="email_verified" value="1" checked>
                                        <span style="font-weight: 600;">Mark Email as Verified</span>
                                    </label>
                                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 4px; margin-left: 24px;">
                                        Grants immediate access without requiring OTP confirmation.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Controls Card -->
                        <div class="card-panel" style="background: var(--bg-surface-alt, #fafafc);">
                            <button type="submit" class="btn-primary-gradient" style="width: 100%; height: 44px; font-weight: 700; font-size: 0.95rem; margin-bottom: 10px;" id="btnSubmitCreateCustomer">
                                Create Customer Profile
                            </button>
                            <a href="<?= url('admin/customers') ?>" class="btn-secondary" style="width: 100%; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
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
