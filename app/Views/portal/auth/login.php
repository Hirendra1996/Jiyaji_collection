<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal Login | Jiyaji LX</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    
    <style>
        :root {
            --portal-navy: #0F172A;
            --portal-indigo: #4F46E5;
            --portal-sky: #0284C7;
        }

        body {
            background: linear-gradient(135deg, #0B0F19 0%, #111827 50%, #1E1B4B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #F8FAFC;
        }

        .portal-login-card {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .portal-login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4F46E5 0%, #3B82F6 50%, #06B6D4 100%);
        }

        .portal-brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .portal-badge-center {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(79, 70, 229, 0.15);
            border: 1px solid rgba(79, 70, 229, 0.3);
            color: #A5B4FC;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 16px;
        }

        .portal-title {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #FFFFFF;
            margin: 0 0 8px 0;
        }

        .portal-subtitle {
            font-size: 0.88rem;
            color: #94A3B8;
            margin: 0;
        }

        /* Demo Quick-Fill Roles Tabs */
        .demo-roles-container {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 24px;
        }

        .demo-roles-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            color: #64748B;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-roles-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .role-fill-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #E2E8F0;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .role-fill-btn:hover, .role-fill-btn.active {
            background: rgba(79, 70, 229, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .role-btn-title {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .role-btn-email {
            font-size: 0.68rem;
            color: #94A3B8;
        }

        /* Form Inputs */
        .portal-form-group {
            margin-bottom: 18px;
        }

        .portal-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #CBD5E1;
            margin-bottom: 6px;
        }

        .portal-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .portal-input-icon {
            position: absolute;
            left: 14px;
            color: #64748B;
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .portal-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #F8FAFC;
            padding: 12px 14px 12px 42px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .portal-input:focus {
            outline: none;
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
            background: rgba(15, 23, 42, 0.95);
        }

        .portal-input::placeholder {
            color: #475569;
        }

        .portal-submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);
            margin-top: 10px;
        }

        .portal-submit-btn:hover {
            background: linear-gradient(135deg, #4338CA 0%, #2563EB 100%);
            box-shadow: 0 6px 20px 0 rgba(79, 70, 229, 0.5);
            transform: translateY(-1px);
        }

        .portal-submit-btn:active {
            transform: translateY(0);
        }

        .portal-pw-toggle {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748B;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 4px;
        }
        .portal-pw-toggle:hover {
            color: #CBD5E1;
        }

        .flash-alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .flash-alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6EE7B7;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .portal-footer-links {
            margin-top: 24px;
            text-align: center;
            font-size: 0.78rem;
            color: #64748B;
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .portal-footer-links a {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .portal-footer-links a:hover {
            color: #6366F1;
            text-decoration: underline;
        }
    </style>

    <script>
        window.serverToasts = <?= json_encode(get_toasts()) ?>;
    </script>
</head>
<body>

<div id="toastContainer" class="toast-container"></div>

<div class="portal-login-card">
    <div class="portal-brand-header">
        <div class="portal-badge-center">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            RBAC Protected Portal
        </div>
        <h1 class="portal-title">Staff &amp; Operations</h1>
        <p class="portal-subtitle">Role-Authorized Workspace &bull; Non-Super Admin Staff</p>
    </div>

    <?php if ($flashErr = flash('error')): ?>
        <div class="flash-alert-error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span><?= htmlspecialchars($flashErr) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($flashSucc = flash('success')): ?>
        <div class="flash-alert-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span><?= htmlspecialchars($flashSucc) ?></span>
        </div>
    <?php endif; ?>

    <!-- Super Admin Notice & Redirect -->
    <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.09); border-radius: 10px; padding: 10px 14px; margin-bottom: 20px; font-size: 0.78rem; color: #94A3B8; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
        <span>Super Administrator access?</span>
        <a href="<?= url('admin/login') ?>" style="color: #A5B4FC; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
            <span>Executive Admin Panel</span> &rarr;
        </a>
    </div>

    <!-- Interactive Demo Quick-Fill Roles (Exclusively Staff Roles) -->
    <div class="demo-roles-container">
        <div class="demo-roles-label">
            <span>Staff Role Quick-Fill Demo</span>
            <span style="color:#A5B4FC; font-size:0.65rem;">Password: Admin@123</span>
        </div>
        <div class="demo-roles-grid">
            <button type="button" class="role-fill-btn" onclick="fillRoleCredentials('orders@jiyaji.com', 'Admin@123', 'Orders Manager')">
                <span class="role-btn-title" style="color: #60A5FA;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#3B82F6;"></span>
                    Orders Manager
                </span>
                <span class="role-btn-email">orders@jiyaji.com</span>
            </button>

            <button type="button" class="role-fill-btn" onclick="fillRoleCredentials('inventory@jiyaji.com', 'Admin@123', 'Inventory Specialist')">
                <span class="role-btn-title" style="color: #C084FC;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#A855F7;"></span>
                    Inventory Spec.
                </span>
                <span class="role-btn-email">inventory@jiyaji.com</span>
            </button>

            <button type="button" class="role-fill-btn" onclick="fillRoleCredentials('support@jiyaji.com', 'Admin@123', 'Support Concierge')">
                <span class="role-btn-title" style="color: #F472B6;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#EC4899;"></span>
                    Customer Support
                </span>
                <span class="role-btn-email">support@jiyaji.com</span>
            </button>

            <button type="button" class="role-fill-btn" onclick="fillRoleCredentials('qa.tester.1789823144@jiyaji.com', 'Admin@123', 'Priya Operations')">
                <span class="role-btn-title" style="color: #34D399;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#10B981;"></span>
                    Priya (QA Ops)
                </span>
                <span class="role-btn-email">qa.tester@jiyaji.com</span>
            </button>
        </div>
    </div>

    <!-- Login Form -->
    <form action="<?= url('portal/login') ?>" method="POST" id="portalLoginForm">
        <?= csrf_field() ?>

        <div class="portal-form-group">
            <label for="email" class="portal-label">Work Email</label>
            <div class="portal-input-wrap">
                <span class="portal-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </span>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="portal-input" 
                    placeholder="orders@jiyaji.com" 
                    value="<?= htmlspecialchars(flash('old_email') ?? 'orders@jiyaji.com') ?>" 
                    required 
                    autocomplete="email"
                    autofocus
                >
            </div>
        </div>

        <div class="portal-form-group">
            <label for="password" class="portal-label">Portal Password</label>
            <div class="portal-input-wrap">
                <span class="portal-input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="portal-input" 
                    placeholder="••••••••" 
                    value="Admin@123"
                    required 
                    autocomplete="current-password"
                >
                <button type="button" class="portal-pw-toggle" id="togglePasswordBtn" aria-label="Toggle Password Visibility">
                    <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="portal-submit-btn" id="portalSubmitBtn">
            <span>Sign In to Portal</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </form>

    <div class="portal-footer-links">
        <a href="<?= url('') ?>">&larr; Return to Storefront</a>
        <span>&bull;</span>
        <a href="<?= url('admin/login') ?>">Executive Admin</a>
    </div>
</div>

<script>
function fillRoleCredentials(email, password, roleName) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    
    // Provide instant feedback
    const submitBtn = document.getElementById('portalSubmitBtn');
    submitBtn.style.transform = 'scale(0.98)';
    setTimeout(() => {
        submitBtn.style.transform = 'scale(1)';
    }, 150);
}

// Password toggle
const togglePasswordBtn = document.getElementById('togglePasswordBtn');
const passwordInput = document.getElementById('password');
const eyeIcon = document.getElementById('eyeIcon');

if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', () => {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        
        eyeIcon.innerHTML = isPassword
            ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
            : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    });
}
</script>

</body>
</html>
