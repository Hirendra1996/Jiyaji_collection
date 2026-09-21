<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? ($page['title'] . ' | Jiyaji LX')) ?></title>
    
    <?php if (!empty($metaDescription)): ?>
        <meta name="description" content="<?= e($metaDescription) ?>">
        <meta property="og:description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if (!empty($metaKeywords)): ?>
        <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= e($title ?? $page['title']) ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= url($page['slug']) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-gold: #c5a880;
            --brand-dark: #0f172a;
            --brand-blue: #2563eb;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Header Bar */
        .storefront-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }
        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 1px;
            flex-shrink: 0;
        }
        .brand-badge {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #f8fafc;
            padding: 5px 9px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .lx-tag {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
        }
        .header-nav {
            display: flex;
            gap: 18px;
            align-items: center;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 2px;
        }
        .header-nav a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            white-space: nowrap;
            transition: color 0.15s ease;
        }
        .header-nav a:hover { color: var(--brand-blue); }

        /* Main Container */
        .page-container {
            max-width: 1200px;
            margin: 28px auto 60px auto;
            padding: 0 20px;
        }

        /* Breadcrumbs */
        .breadcrumbs {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .breadcrumbs a {
            color: var(--brand-blue);
            text-decoration: none;
        }

        /* Two-Column Grid */
        .layout-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* Left Navigation Card */
        .sidebar-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 84px;
        }
        .sidebar-title {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 24px;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 12px;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .sidebar-menu a:hover {
            background: var(--bg-page);
            color: var(--brand-blue);
        }
        .sidebar-menu a.active {
            background: rgba(37, 99, 235, 0.08);
            color: var(--brand-blue);
            font-weight: 700;
        }

        .concierge-box {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.03), rgba(15, 23, 42, 0.07));
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            font-size: 0.82rem;
        }

        /* Right Content Article */
        .article-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 36px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .article-header {
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-color);
        }
        .article-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--brand-dark);
            line-height: 1.25;
            margin-bottom: 12px;
            word-break: break-word;
        }
        .article-excerpt {
            font-size: 1.02rem;
            color: var(--text-secondary);
            line-height: 1.55;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .article-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Typography inside article body */
        .article-body {
            word-break: break-word;
        }
        .article-body h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--brand-dark);
            margin: 24px 0 10px 0;
        }
        .article-body h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--brand-dark);
            margin: 18px 0 8px 0;
        }
        .article-body p {
            margin-bottom: 14px;
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.7;
        }
        .article-body ul, .article-body ol {
            margin: 0 0 16px 20px;
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.7;
        }
        .article-body li { margin-bottom: 6px; }
        .article-body blockquote {
            border-left: 3px solid var(--brand-gold);
            background: rgba(197, 168, 128, 0.08);
            padding: 14px 18px;
            margin: 18px 0;
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            font-style: italic;
            color: #334155;
        }
        .article-body a {
            color: var(--brand-blue);
            text-decoration: underline;
        }
        .article-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0;
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .article-body th, .article-body td {
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        /* Contact Form Module */
        .contact-form-box {
            margin-top: 32px;
            padding-top: 28px;
            border-top: 1px solid var(--border-color);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .form-input, .form-textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--text-primary);
            background: #ffffff;
            transition: border-color 0.15s ease;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-submit {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #ffffff;
            border: none;
            padding: 12px 28px;
            font-size: 0.92rem;
            font-weight: 700;
            border-radius: var(--radius-md);
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, #0f172a, #020617);
        }

        /* Footer */
        .storefront-footer {
            background: #ffffff;
            border-top: 1px solid var(--border-color);
            padding: 32px 20px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Responsive Breakpoints */
        @media (max-width: 900px) {
            .layout-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .sidebar-card {
                position: static;
                top: auto;
            }
            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 6px;
                margin-bottom: 16px;
            }
            .article-card {
                padding: 24px 20px;
                border-radius: var(--radius-lg);
            }
            .article-title {
                font-size: 1.65rem;
            }
        }

        @media (max-width: 640px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
                padding: 12px 16px;
            }
            .header-nav {
                width: 100%;
                justify-content: flex-start;
                gap: 14px;
            }
            .page-container {
                margin: 16px auto 40px auto;
                padding: 0 14px;
            }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .btn-submit {
                width: 100%;
                text-align: center;
            }
            .article-card {
                padding: 18px 14px;
            }
            .article-title {
                font-size: 1.45rem;
            }
            .sidebar-menu {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="storefront-header">
    <div class="header-inner">
        <a href="<?= url('/') ?>" class="brand-logo">
            <div class="brand-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="12 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                <span>JIYAJI</span>
                <span class="lx-tag">LX</span>
            </div>
        </a>

        <nav class="header-nav">
            <a href="<?= url('/') ?>">Home</a>
            <a href="<?= url('about-us') ?>">Heritage</a>
            <a href="<?= url('faq') ?>">Assistance</a>
            <a href="<?= url('contact-us') ?>">Concierge</a>
            <a href="<?= url('admin/login') ?>" style="font-size:0.8rem; color:var(--text-muted);">Admin Portal</a>
        </nav>
    </div>
</header>

<!-- Main Body -->
<div class="page-container">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <a href="<?= url('/') ?>">Home</a>
        <span>/</span>
        <span>Brand &amp; Legal</span>
        <span>/</span>
        <span style="color:var(--text-primary); font-weight:600;"><?= e($page['title']) ?></span>
    </div>

    <div class="layout-grid">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar-card">
            <div class="sidebar-title">Brand &amp; Information</div>
            <ul class="sidebar-menu">
                <?php foreach ($allPages as $navItem): ?>
                    <li>
                        <a href="<?= url($navItem['slug']) ?>" class="<?= ($navItem['slug'] === $page['slug']) ? 'active' : '' ?>">
                            <span><?= e($navItem['title']) ?></span>
                            <?php if ($navItem['slug'] === $page['slug']): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="concierge-box">
                <div style="font-weight:700; color:var(--text-primary); margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    Royal Concierge
                </div>
                <div style="color:var(--text-secondary); margin-bottom:8px;">
                    Need tailored measurements or wedding entourage styling?
                </div>
                <div style="font-weight:600; color:var(--text-primary); margin-bottom:2px;">WhatsApp: +91 98290 12345</div>
                <div style="color:var(--text-muted); font-size:0.75rem;">Jaipur Atelier &bull; 10 AM – 8 PM IST</div>
            </div>
        </aside>

        <!-- Right Article Content -->
        <article class="article-card">
            <div class="article-header">
                <h1 class="article-title"><?= e($page['title']) ?></h1>
                <?php if (!empty($page['excerpt'])): ?>
                    <p class="article-excerpt"><?= e($page['excerpt']) ?></p>
                <?php endif; ?>
                <div class="article-meta">
                    <?php if (!empty($page['updated_at'])): ?>
                        <span>Last Updated: <?= date('F d, Y', strtotime($page['updated_at'])) ?></span>
                    <?php endif; ?>
                    <span>&bull;</span>
                    <span><?= str_word_count(strip_tags($page['content'])) ?> Words</span>
                    <span>&bull;</span>
                    <span>Jiyaji LX Official Editorial</span>
                </div>
            </div>

            <!-- Page HTML Content -->
            <div class="article-body">
                <?= $page['content'] ?>
            </div>

            <!-- Interactive Module: Contact Form (Only for contact-us) -->
            <?php if ($page['slug'] === 'contact-us'): ?>
                <div class="contact-form-box">
                    <h3 style="font-size:1.3rem; font-weight:700; color:var(--brand-dark); margin-bottom:8px;">Send a Direct Message to Our Concierge</h3>
                    <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:20px;">
                        Complete the form below and an imperial styling advisor will connect with you within 24 business hours.
                    </p>

                    <?php if (isset($_GET['status']) && $_GET['status'] === 'sent'): ?>
                        <div style="background:rgba(16, 185, 129, 0.1); border:1px solid #10b981; color:#065f46; padding:14px 18px; border-radius:var(--radius-md); font-size:0.9rem; margin-bottom:20px; font-weight:600;">
                            ✔ Thank you! Your inquiry has been transmitted to our Jaipur atelier concierge.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('contact-us/submit') ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Your Full Name *</label>
                                <input type="text" name="name" id="name" required placeholder="e.g. Vikramaditya Singh" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" name="email" id="email" required placeholder="name@domain.com" class="form-input">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="phone">Phone / WhatsApp Number</label>
                                <input type="tel" name="phone" id="phone" placeholder="+91 98765 43210" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="subject">Inquiry Subject</label>
                                <input type="text" name="subject" id="subject" placeholder="e.g. Bridal Trousseau Consultation" class="form-input">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:20px;">
                            <label for="message">Detailed Message *</label>
                            <textarea name="message" id="message" rows="5" required placeholder="Tell us how our royal stylists can assist you..." class="form-textarea"></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            Transmit Message to Concierge &rarr;
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    </div>
</div>

<!-- Footer -->
<footer class="storefront-footer">
    <div style="max-width:1200px; margin:0 auto;">
        <div style="font-weight:700; color:var(--text-primary); margin-bottom:6px;">JIYAJI LX &bull; ROYAL INDIAN ETHNIC COUTURE</div>
        <div style="color:var(--text-muted); margin-bottom:12px;">Handcrafted with generational mastery in Jaipur, Rajasthan, India.</div>
        <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap; font-size:0.8rem;">
            <a href="<?= url('about-us') ?>" style="color:var(--text-secondary); text-decoration:none;">About Us</a>
            <span>&bull;</span>
            <a href="<?= url('privacy-policy') ?>" style="color:var(--text-secondary); text-decoration:none;">Privacy Policy</a>
            <span>&bull;</span>
            <a href="<?= url('terms-conditions') ?>" style="color:var(--text-secondary); text-decoration:none;">Terms of Service</a>
            <span>&bull;</span>
            <a href="<?= url('shipping-policy') ?>" style="color:var(--text-secondary); text-decoration:none;">Shipping Policy</a>
            <span>&bull;</span>
            <a href="<?= url('returns-policy') ?>" style="color:var(--text-secondary); text-decoration:none;">Returns Policy</a>
            <span>&bull;</span>
            <a href="<?= url('faq') ?>" style="color:var(--text-secondary); text-decoration:none;">FAQ</a>
            <span>&bull;</span>
            <a href="<?= url('contact-us') ?>" style="color:var(--text-secondary); text-decoration:none;">Contact Concierge</a>
        </div>
    </div>
</footer>

</body>
</html>
