<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CheckPass &middot; Core API Engine</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #0A5C38;
            --brand-dark: #022315;
            --brand-light: #EBF7EE;
            --brand-emerald: #059669;
            --surface-bg: #F8FAF9;
            --card-bg: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: var(--surface-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 40px 20px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #0A5C38 0%, #022315 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(10, 92, 56, 0.25);
        }

        .brand-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--brand-dark);
            letter-spacing: -0.5px;
        }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }

        .main-container {
            width: 100%;
            max-width: 680px;
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .hero-banner {
            background: linear-gradient(145deg, #0A5C38 0%, #063C24 60%, #022315 100%);
            padding: 36px 32px;
            color: #ffffff;
            position: relative;
        }

        .hero-banner h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .hero-banner p {
            font-size: 14px;
            color: #E2E8F0;
            line-height: 1.5;
        }

        .content-body {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .portal-card {
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }

        .portal-card:hover {
            border-color: var(--brand-emerald);
            box-shadow: 0 4px 14px rgba(10, 92, 56, 0.08);
        }

        .portal-card.primary {
            border: 1.5px solid #10B981;
            background: #FAFDFB;
        }

        .portal-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .portal-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .portal-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .credentials-pill {
            background: #F1F5F9;
            border-radius: 8px;
            padding: 8px 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .btn-launch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--brand-primary);
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(10, 92, 56, 0.25);
        }

        .btn-primary:hover {
            background: #064A2D;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #F1F5F9;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #E2E8F0;
        }

        .api-status-footer {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
        }

        .api-link {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .api-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <header class="header-brand">
        <div class="brand-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>
        <div>
            <div class="brand-title">CheckPass</div>
            <div class="badge-live">
                <span class="status-dot"></span>
                <span>REST API Engine Operational &middot; Port 8000</span>
            </div>
        </div>
    </header>

    <main class="main-container">
        <div class="hero-banner">
            <h1>Backend API &amp; Services Gateway</h1>
            <p>You have accessed the core REST API backend. The Super Admin web portal and Resident mobile application are hosted separately on client web servers.</p>
        </div>

        <div class="content-body">
            <!-- Super Admin Portal Card -->
            <div class="portal-card primary">
                <div class="portal-card-header">
                    <span class="portal-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Super Admin &amp; Estate Management Portal
                    </span>
                    <span style="font-size: 11px; background: #DEF7EC; color: #03543F; font-weight: 700; padding: 2px 8px; border-radius: 6px;">Port 3100</span>
                </div>
                <p class="portal-desc">
                    Access CRM leads, estate properties, passes, artisans, dues, and platform analytics.
                </p>
                <div class="credentials-pill">
                    <span>Email: <strong>admin@checkpass.com</strong></span>
                    <span>Pass: <strong>123456</strong></span>
                </div>
                <a href="{{ env('ADMIN_FRONTEND_URL', 'http://localhost:3100') }}/login" class="btn-launch btn-primary">
                    Launch Super Admin Portal &rarr;
                </a>
            </div>

            <!-- Resident & Security App Card -->
            <div class="portal-card">
                <div class="portal-card-header">
                    <span class="portal-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>
                        Resident &amp; Gate Security App
                    </span>
                    <span style="font-size: 11px; background: #F1F5F9; color: #475569; font-weight: 700; padding: 2px 8px; border-radius: 6px;">Port 5173</span>
                </div>
                <p class="portal-desc">
                    Create visitor passes, check in visitors, view gate alerts, and manage home profile.
                </p>
                <div class="credentials-pill">
                    <span>Resident: <strong>mango@gmail.com</strong> / <strong>123456</strong></span>
                </div>
                <a href="{{ env('RESIDENT_APP_URL', 'http://localhost:5173') }}" class="btn-launch btn-secondary">
                    Launch Resident App &rarr;
                </a>
            </div>
        </div>
    </main>

    <footer class="api-status-footer">
        <div>Health check endpoint: <a href="{{ url('/api/v1/ping') }}" class="api-link">{{ url('/api/v1/ping') }}</a></div>
        <div>CheckPass &copy; {{ date('Y') }} &middot; Powered by Sheffy Technologies</div>
    </footer>

</body>
</html>
