<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Jiyaji LX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }
        .error-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 48px 32px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 12px 32px -4px rgba(15, 23, 42, 0.08);
        }
        .error-code {
            font-size: clamp(3.5rem, 10vw, 5.5rem);
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #2D82FF, #8C30F5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }
        .error-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .error-desc {
            font-size: 0.92rem;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #2D82FF, #8C30F5);
            color: #FFFFFF;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: transform 0.2s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-desc">The royal chamber or resource you are looking for has been relocated or does not exist.</p>
        <a href="<?= function_exists('url') ? url('/') : '/' ?>" class="btn-home">
            &larr; Return to Home
        </a>
    </div>
</body>
</html>