<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jiyaji LX | Royal Indian Ethnic Couture</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #2D82FF;
            --brand-purple: #8C30F5;
            --brand-orange: #FF5100;
            --bg-page: #F8FAFC;
            --bg-surface: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border-color: #E2E8F0;
            --radius-lg: 16px;
            --radius-md: 12px;
            --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            --shadow-elevated: 0 12px 32px -4px rgba(15, 23, 42, 0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            -webkit-font-smoothing: antialiased;
        }

        .header-bar {
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1.25rem;
            font-weight: 800;
            text-decoration: none;
            color: var(--text-primary);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2D82FF, #8C30F5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .lx-tag {
            background: rgba(140, 48, 245, 0.1);
            color: var(--brand-purple);
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            border: 1px solid rgba(140, 48, 245, 0.2);
        }

        .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--brand-blue);
        }

        .hero-section {
            max-width: 1100px;
            margin: 48px auto;
            padding: 0 20px;
            text-align: center;
            width: 100%;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #0F172A 0%, #2D82FF 50%, #8C30F5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            color: var(--text-secondary);
            max-width: 680px;
            margin: 0 auto 36px auto;
            line-height: 1.6;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            text-align: left;
            margin-top: 32px;
        }

        .portal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-card);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .portal-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-elevated);
            border-color: rgba(45, 130, 255, 0.4);
        }

        .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .card-desc {
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .card-action {
            font-size: 0.86rem;
            font-weight: 700;
            color: var(--brand-blue);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .footer {
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            padding: 24px 20px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 640px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
            }
            .hero-section {
                margin: 28px auto;
            }
            .card-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header class="header-bar">
    <div class="header-container">
        <a href="<?= url('/') ?>" class="brand-badge">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="12 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
            </div>
            <span>JIYAJI</span>
            <span class="lx-tag">LX</span>
        </a>

        <nav class="nav-links">
            <a href="<?= url('about-us') ?>">Heritage</a>
            <a href="<?= url('faq') ?>">FAQ</a>
            <a href="<?= url('contact-us') ?>">Concierge</a>
            <a href="<?= url('admin/login') ?>">Admin Login</a>
        </nav>
    </div>
</header>

<main class="hero-section">
    <h1 class="hero-title">Welcome to Jiyaji LX</h1>
    <p class="hero-subtitle">
        Royal Indian Ethnic Couture &bull; Modern Multi-Channel Luxury Commerce Engine
    </p>

    <div class="card-grid">
        <a href="<?= url('admin/dashboard') ?>" class="portal-card">
            <div>
                <div class="card-icon" style="background:rgba(45,130,255,0.1); color:var(--brand-blue);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                </div>
                <div class="card-title">Executive Admin Portal</div>
                <div class="card-desc">Access catalog management, live dispatch control, analytics, coupon engines, and staff permissions.</div>
            </div>
            <div class="card-action">Launch Admin &rarr;</div>
        </a>

        <a href="<?= url('about-us') ?>" class="portal-card">
            <div>
                <div class="card-icon" style="background:rgba(140,48,245,0.1); color:var(--brand-purple);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <div class="card-title">Brand Heritage &amp; Legal</div>
                <div class="card-desc">Explore our Jaipur atelier story, brand heritage, shipping policy, returns policy, and customer FAQs.</div>
            </div>
            <div class="card-action">Explore Heritage &rarr;</div>
        </a>

        <a href="<?= url('contact-us') ?>" class="portal-card">
            <div>
                <div class="card-icon" style="background:rgba(255,81,0,0.1); color:var(--brand-orange);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <div class="card-title">Royal Concierge</div>
                <div class="card-desc">Connect with our dedicated bespoke styling advisors for personalized bridal and festive couture.</div>
            </div>
            <div class="card-action">Contact Concierge &rarr;</div>
        </a>
    </div>
</main>

<footer class="footer">
    <div>&copy; <?= date('Y') ?> JIYAJI LX &bull; Royal Indian Ethnic Couture &bull; All Rights Reserved.</div>
</footer>

</body>
</html>