<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Jiyaji LX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <script>
        window.serverToasts = <?= json_encode(get_toasts()) ?>;
    </script>
</head>
<body>

<!-- Toast Notification Container -->
<div id="toastContainer" class="toast-container"></div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="brand-badge">
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <span>JIYAJI</span>
                <span class="lx-tag">LX</span>
            </div>
            <h1 class="auth-title">Admin Portal</h1>
            <p class="auth-subtitle">Sign in to manage your e-commerce store</p>
        </div>

        <!-- Demo Credentials Helper -->
        <div class="demo-credentials-box">
            <div class="demo-credentials-text">
                <strong>Demo Admin:</strong> admin@jiyaji.com<br>
                <strong>Password:</strong> Admin@123
            </div>
            <button type="button" class="demo-fill-btn" id="demoFillBtn">
                Auto-fill
            </button>
        </div>

        <form action="<?= url('admin/login') ?>" method="POST" id="adminLoginForm">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-input" 
                        placeholder="admin@jiyaji.com" 
                        value="<?= htmlspecialchars(flash('old_email') ?? '') ?>" 
                        required 
                        autocomplete="email"
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-input" 
                        placeholder="••••••••" 
                        required 
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary-gradient" id="btnLoginSubmit">
                <span>Sign In to Dashboard</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div class="auth-footer">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline; vertical-align:middle; margin-right:4px;">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Protected by Jiyaji LX Security Engine</span>
        </div>
    </div>
</div>

<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
