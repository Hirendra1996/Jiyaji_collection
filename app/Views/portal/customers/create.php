<?php
$title    = $title ?? 'Add New Customer | Jiyaji LX Staff Portal';
$customer = $customer ?? null; // null for create, populated for edit
$isEdit   = $customer !== null;
$encId    = $customer['encrypted_id'] ?? '';
$formAction = $isEdit
    ? url("portal/customers/{$encId}/update")
    : url('portal/customers/store');

include __DIR__ . '/../layouts/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="admin-main">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="dashboard-content" style="padding: 1.75rem 2rem;">

            <!-- Breadcrumbs -->
            <div style="font-size: 0.82rem; color: #64748B; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                <a href="<?= url('portal/dashboard') ?>" style="color: #0284C7; text-decoration: none; font-weight: 600;">Dashboard</a>
                <span>&rsaquo;</span>
                <a href="<?= url('portal/customers') ?>" style="color: #0284C7; text-decoration: none; font-weight: 600;">Customer Directory</a>
                <span>&rsaquo;</span>
                <?php if ($isEdit): ?>
                    <a href="<?= url('portal/customers/' . $encId) ?>" style="color: #0284C7; text-decoration: none; font-weight: 600;"><?= htmlspecialchars($customer['name'] ?? '') ?></a>
                    <span>&rsaquo;</span>
                    <span style="color: #0F172A; font-weight: 600;">Edit Profile</span>
                <?php else: ?>
                    <span style="color: #0F172A; font-weight: 600;">Add New Customer</span>
                <?php endif; ?>
            </div>

            <!-- Page Banner -->
            <div style="background: linear-gradient(135deg, #0F172A 0%, #075985 55%, #0284C7 100%); border-radius: 14px; padding: 1.5rem 2rem; color: #FFFFFF; margin-bottom: 24px; box-shadow: 0 8px 20px -5px rgba(2,132,199,0.3);">
                <h1 style="font-size: 1.4rem; font-weight: 800; margin: 0 0 4px 0; letter-spacing: -0.02em;">
                    <?= $isEdit ? 'Edit Customer Profile' : 'Add New Customer' ?>
                </h1>
                <p style="font-size: 0.86rem; color: #BAE6FD; margin: 0;">
                    <?= $isEdit ? 'Update account details, verification status, and security credentials.' : 'Create a new shopper account with optional initial delivery address.' ?>
                </p>
            </div>

            <div style="max-width: 760px;">
                <form action="<?= $formAction ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <?= csrf_field() ?>

                    <!-- Account Details -->
                    <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="padding: 14px 20px; border-bottom: 1px solid #F1F5F9; background: #F8FAFC;">
                            <div style="font-size: 0.9rem; font-weight: 800; color: #0F172A;">Account Details</div>
                        </div>
                        <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

                            <!-- Full Name -->
                            <div>
                                <label for="name" style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Full Name <span style="color: #EF4444;">*</span></label>
                                <input type="text" id="name" name="name" value="<?= htmlspecialchars($customer['name'] ?? '') ?>"
                                    placeholder="e.g. Priya Sharma" required
                                    style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.9rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                                    onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>"
                                    placeholder="+91 98765 43210"
                                    style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.9rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                                    onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                            </div>

                            <!-- Email -->
                            <div style="grid-column: 1 / -1;">
                                <label for="email" style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Email Address <span style="color: #EF4444;">*</span></label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>"
                                    placeholder="shopper@example.com" required
                                    style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.9rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                                    onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">
                                    <?= $isEdit ? 'New Password' : 'Password' ?>
                                    <?php if (!$isEdit): ?><span style="color: #EF4444;">*</span><?php endif; ?>
                                    <?php if ($isEdit): ?><span style="font-weight: 400; color: #94A3B8;">(leave blank to keep current)</span><?php endif; ?>
                                </label>
                                <input type="password" id="password" name="password"
                                    placeholder="<?= $isEdit ? '••••••••' : 'Min. 8 characters' ?>"
                                    <?= $isEdit ? '' : 'required' ?>
                                    style="width: 100%; height: 42px; padding: 0 14px; font-size: 0.9rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;"
                                    onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                            </div>

                            <!-- Account Status -->
                            <div>
                                <label for="is_active" style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Account Status</label>
                                <select id="is_active" name="is_active" style="width: 100%; height: 42px; padding: 0 12px; font-size: 0.9rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                                    <option value="1" <?= (int)($customer['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Active — Can log in and shop</option>
                                    <option value="0" <?= (int)($customer['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Suspended — Account blocked</option>
                                </select>
                            </div>

                            <!-- Email verified -->
                            <div style="grid-column: 1 / -1;">
                                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.86rem; color: #374151; font-weight: 600; cursor: pointer; padding: 12px 14px; border: 1px solid #E2E8F0; border-radius: 8px; background: #F8FAFC;">
                                    <input type="checkbox" name="email_verified" value="1"
                                        <?= !empty($customer['email_verified']) ? 'checked' : '' ?>
                                        style="width: 18px; height: 18px; accent-color: #0284C7;">
                                    <div>
                                        <div style="font-weight: 700;">Mark Email as Verified</div>
                                        <div style="font-size: 0.76rem; font-weight: 400; color: #64748B; margin-top: 1px;">Customer will be treated as a confirmed shopper. Useful when onboarding manually.</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <?php if (!$isEdit): ?>
                        <!-- Initial Address (create only) -->
                        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="padding: 14px 20px; border-bottom: 1px solid #F1F5F9; background: #F8FAFC; display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-size: 0.9rem; font-weight: 800; color: #0F172A;">Initial Delivery Address <span style="font-weight: 400; font-size: 0.8rem; color: #94A3B8;">(optional)</span></div>
                            </div>
                            <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Address Label</label>
                                    <select name="address_label" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; background: #fff; outline: none;">
                                        <option value="Home">Home</option>
                                        <option value="Work">Work</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Recipient Name</label>
                                    <input type="text" name="address_recipient" placeholder="Same as customer" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Address Line 1</label>
                                    <input type="text" name="address_line1" placeholder="Street, building, flat..." style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Address Line 2</label>
                                    <input type="text" name="address_line2" placeholder="Landmark, floor, wing..." style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">City</label>
                                    <input type="text" name="city" placeholder="Mumbai" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">State</label>
                                    <input type="text" name="state" placeholder="Maharashtra" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Pincode</label>
                                    <input type="text" name="pincode" placeholder="400001" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                                <div>
                                    <label style="font-size: 0.82rem; font-weight: 700; color: #374151; display: block; margin-bottom: 6px;">Country</label>
                                    <input type="text" name="country" value="India" style="width: 100%; height: 40px; padding: 0 12px; font-size: 0.88rem; border: 1px solid #CBD5E1; border-radius: 8px; outline: none;" onfocus="this.style.borderColor='#0284C7';" onblur="this.style.borderColor='#CBD5E1';">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Submit -->
                    <div style="display: flex; gap: 12px; padding-bottom: 32px;">
                        <button type="submit" style="height: 46px; padding: 0 32px; background: linear-gradient(135deg, #075985 0%, #0284C7 100%); color: #FFFFFF; border: none; border-radius: 10px; font-size: 0.92rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(2,132,199,0.35);">
                            <?= $isEdit ? '✓  Save Changes' : '+ Create Customer' ?>
                        </button>
                        <a href="<?= $isEdit ? url('portal/customers/' . $encId) : url('portal/customers') ?>" style="height: 46px; padding: 0 24px; background: #F1F5F9; color: #475569; border-radius: 10px; font-size: 0.9rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </main>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</div>
