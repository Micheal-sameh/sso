<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Down for Maintenance - {{ config('app.name', 'Avarewase Auth') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F8F9FA;
            color: #0F172A;
            font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .card {
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
            padding: 48px 40px;
            max-width: 420px;
            width: calc(100% - 32px);
            text-align: center;
        }
        .logo { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 28px; }
        .logo span { font-weight: 800; font-size: 20px; letter-spacing: -0.02em; color: #1B2A4A; }
        .icon-tile {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.14), rgba(59, 130, 246, 0.05));
        }
        h1 { font-size: 20px; margin: 0 0 8px; color: #0F172A; }
        p { color: #64748B; font-size: 15px; line-height: 1.6; margin: 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <svg width="36" height="36" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="#1B2A4A" />
                <rect x="44" y="20" width="12" height="60" fill="#3B82F6" />
                <rect x="20" y="44" width="60" height="12" fill="#3B82F6" />
            </svg>
            <span>Avarewase</span>
        </div>

        <div class="icon-tile">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
            </svg>
        </div>

        <h1>Down for Maintenance</h1>
        <p>We're making some improvements and will be back shortly. Thanks for your patience.</p>
    </div>
</body>
</html>
