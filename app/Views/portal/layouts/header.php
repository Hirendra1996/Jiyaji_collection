<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($title ?? 'Staff Operations Portal | Jiyaji LX') ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Core Admin & Framework CSS -->
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">

    <!-- Staff Portal Dedicated Styles -->
    <style>
        :root {
            --portal-primary: #4F46E5;
            --portal-primary-hover: #4338CA;
            --portal-accent: #0EA5E9;
            --portal-dark: #0F172A;
            --portal-surface: #FFFFFF;
            --portal-border: #E2E8F0;
            --portal-text-muted: #64748B;
        }

        /* Enhanced Topbar Styles */
        .portal-topbar {
            height: 70px;
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 0 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .portal-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .portal-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Role Badges */
        .role-pill-orders {
            background: rgba(37, 99, 235, 0.08);
            color: #1D4ED8;
            border: 1px solid rgba(37, 99, 235, 0.22);
        }
        .role-pill-inventory {
            background: rgba(124, 58, 237, 0.08);
            color: #6D28D9;
            border: 1px solid rgba(124, 58, 237, 0.22);
        }
        .role-pill-support {
            background: rgba(236, 72, 153, 0.08);
            color: #DB2777;
            border: 1px solid rgba(236, 72, 153, 0.22);
        }
        .role-pill-marketing {
            background: rgba(16, 185, 129, 0.08);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.22);
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 9999px;
            letter-spacing: 0.01em;
            transition: all 0.2s ease;
        }

        /* Pulse Dot for Live Store */
        @keyframes portalPulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .portal-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22C55E;
            animation: portalPulse 2s infinite;
        }

        /* Topbar Indicators */
        .portal-header-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            color: #475569;
        }

        .portal-store-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 700;
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        /* User Profile Pill & Logout */
        .portal-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 6px 4px 10px;
            border-radius: 12px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            transition: all 0.2s ease;
        }
        .portal-user-card:hover {
            background: #FFFFFF;
            border-color: #CBD5E1;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        }

        .portal-avatar-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%);
            color: #FFFFFF;
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }

        .portal-signout-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #DC2626;
            background: rgba(220, 38, 38, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.15);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .portal-signout-btn:hover {
            background: #DC2626;
            color: #FFFFFF;
            border-color: #DC2626;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
        }

        /* Sidebar Brand & Badges */
        .sidebar-brand-portal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color, #E2E8F0);
        }
        .portal-subtag {
            background: #EEF2FF;
            color: #4F46E5;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 6px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
    </style>

    <script>
        window.serverToasts = <?= json_encode(get_toasts()) ?>;
    </script>
</head>
<body>

<!-- Floating Toast Notifications Container -->
<div id="toastContainer" class="toast-container"></div>
