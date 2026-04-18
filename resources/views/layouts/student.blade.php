<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace Élève') - TIMAH ACADEMY</title>

    <style>
        :root {
            --bg: #f5f7fb;
            --bg-soft: #eef3ff;
            --panel: #ffffff;
            --panel-soft: #f9fbff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #dbe5f2;
            --line-strong: #c9d7ea;
            --primary: #2563eb;
            --primary-strong: #1d4ed8;
            --primary-soft: rgba(37, 99, 235, 0.10);
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow-xs: 0 6px 16px rgba(15, 23, 42, 0.04);
            --shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 22px 52px rgba(15, 23, 42, 0.12);
            --sidebar-width: 278px;
            --topbar-height: 78px;
        }

        html[data-theme='dark'] {
            --bg: #08111f;
            --bg-soft: #0c1728;
            --panel: #101c31;
            --panel-soft: #13233b;
            --text: #e8eefb;
            --muted: #9db0cb;
            --line: rgba(190, 207, 238, 0.12);
            --line-strong: rgba(190, 207, 238, 0.18);
            --primary: #6ea1ff;
            --primary-strong: #8bb4ff;
            --primary-soft: rgba(110, 161, 255, 0.12);
            --shadow-xs: 0 6px 18px rgba(0, 0, 0, 0.18);
            --shadow: 0 18px 36px rgba(0, 0, 0, 0.26);
            --shadow-lg: 0 24px 58px rgba(0, 0, 0, 0.34);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.06), transparent 24%),
                linear-gradient(180deg, var(--bg-soft), var(--bg));
            transition: background .25s ease, color .25s ease;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        .student-app {
            min-height: 100vh;
            display: flex;
        }

        .student-sidebar {
            width: var(--sidebar-width);
            flex: 0 0 var(--sidebar-width);
            min-height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding: 22px 18px 18px;
            border-right: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.74);
            backdrop-filter: blur(16px);
        }

        html[data-theme='dark'] .student-sidebar {
            background: rgba(8, 17, 31, 0.76);
        }

        .student-main {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .student-topbar {
            min-height: var(--topbar-height);
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 22px;
            border-bottom: 1px solid var(--line);
            background: rgba(245, 247, 251, 0.82);
            backdrop-filter: blur(16px);
        }

        html[data-theme='dark'] .student-topbar {
            background: rgba(8, 17, 31, 0.82);
        }

        .student-content {
            padding: 22px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand__mark {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), #4f86ff);
            color: #fff;
            font-weight: 900;
            font-size: 1rem;
            letter-spacing: -0.03em;
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.20);
            flex: 0 0 44px;
        }

        .brand__text {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .brand__text strong {
            font-size: 1rem;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .brand__text span {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .student-nav {
            display: grid;
            gap: 8px;
        }

        .student-link,
        .student-link--button {
            width: 100%;
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 0 14px;
            border-radius: 16px;
            border: 1px solid transparent;
            color: var(--muted);
            background: transparent;
            font-size: .96rem;
            font-weight: 800;
            cursor: pointer;
            transition: .2s ease;
        }

        .student-link:hover,
        .student-link--button:hover {
            color: var(--text);
            background: var(--primary-soft);
            border-color: var(--line);
        }

        .student-link.is-active {
            color: var(--primary);
            background: rgba(37, 99, 235, 0.12);
            border-color: rgba(37, 99, 235, 0.16);
            box-shadow: var(--shadow-xs);
        }

        .student-link__icon {
            width: 18px;
            height: 18px;
            color: currentColor;
            flex: 0 0 18px;
        }

        .student-sidebar__spacer {
            flex: 1;
        }

        .student-logout {
            margin-top: auto;
            min-height: 50px;
            border-radius: 16px;
            border: 1px solid rgba(239, 68, 68, 0.18);
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            font-weight: 800;
            cursor: pointer;
            transition: .2s ease;
        }

        .student-logout:hover {
            background: rgba(239, 68, 68, 0.14);
        }

        html[data-theme='dark'] .student-logout {
            color: #fecaca;
            background: rgba(127, 29, 29, 0.24);
            border-color: rgba(248, 113, 113, 0.24);
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-menu-btn,
        .theme-btn,
        .topbar-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--panel);
            color: var(--text);
            font-weight: 800;
            cursor: pointer;
            transition: .2s ease;
            box-shadow: var(--shadow-xs);
        }

        .mobile-menu-btn:hover,
        .theme-btn:hover,
        .topbar-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .theme-btn {
            padding: 0 14px;
        }

        .topbar-btn--primary {
            background: linear-gradient(135deg, var(--primary), #4f86ff);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.24);
        }

        .topbar-btn--primary:hover {
            box-shadow: 0 18px 36px rgba(37, 99, 235, 0.28);
        }

        .mobile-menu-btn {
            display: none;
            padding: 0 14px;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 39;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(4px);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid #bbf7d0;
            background: #ecfdf3;
            color: #166534;
            font-size: .94rem;
            font-weight: 700;
        }

        .alert--error {
            border-color: rgba(239, 68, 68, 0.18);
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
        }

        html[data-theme='dark'] .alert {
            background: rgba(5, 46, 22, 0.34);
            border-color: rgba(34, 197, 94, 0.24);
            color: #bbf7d0;
        }

        html[data-theme='dark'] .alert--error {
            color: #fecaca;
            border-color: rgba(248, 113, 113, 0.24);
            background: rgba(127, 29, 29, 0.24);
        }

        @media (max-width: 1024px) {
            .student-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform .25s ease;
                box-shadow: var(--shadow-lg);
            }

            .student-app.is-sidebar-open .student-sidebar {
                transform: translateX(0);
            }

            .student-app.is-sidebar-open .mobile-overlay {
                display: block;
            }

            .mobile-menu-btn {
                display: inline-flex;
            }

            .student-topbar {
                padding-left: 16px;
                padding-right: 16px;
            }

            .student-content {
                padding: 16px;
            }

            .topbar-right .topbar-btn {
                display: none;
            }
        }

        @media (max-width: 720px) {
            .brand__text span {
                display: none;
            }

            .student-topbar {
                min-height: 70px;
            }

            .student-content {
                padding: 14px;
            }

            .theme-btn,
            .mobile-menu-btn {
                min-height: 42px;
                border-radius: 12px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="student-app" id="studentApp">
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <aside class="student-sidebar" id="studentSidebar">
        <a href="{{ route('student.dashboard') }}" class="brand">
            <span class="brand__mark">TA</span>
            <span class="brand__text">
                <strong>TIMAH ACADEMY</strong>
                <span>Espace élève moderne</span>
            </span>
        </a>

        <nav class="student-nav">
            <a href="{{ route('student.dashboard') }}" class="student-link {{ request()->routeIs('student.dashboard') ? 'is-active' : '' }}">
                <svg class="student-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 13h8V3H3z"></path>
                    <path d="M13 21h8V11h-8z"></path>
                    <path d="M13 3h8v6h-8z"></path>
                    <path d="M3 21h8v-6H3z"></path>
                </svg>
                <span>Tableau de bord</span>
            </a>

            <a href="{{ route('student.td.index') }}" class="student-link {{ request()->routeIs('student.td.*') ? 'is-active' : '' }}">
                <svg class="student-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <span>Mes TD</span>
            </a>

            <a href="{{ route('student.messages.index') }}" class="student-link {{ request()->routeIs('student.messages.*') ? 'is-active' : '' }}">
                <svg class="student-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>Messagerie</span>
            </a>

            <a href="{{ route('student.subscription.index') }}" class="student-link {{ request()->routeIs('student.subscription.*') ? 'is-active' : '' }}">
                <svg class="student-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <path d="M2 10h20"></path>
                </svg>
                <span>Abonnement</span>
            </a>

            <a href="{{ route('student.courses.index') }}" class="student-link {{ request()->routeIs('student.courses.*') ? 'is-active' : '' }}">
                <svg class="student-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span>Mes cours</span>
            </a>

            <button type="button" class="student-link--button theme-btn" data-theme-toggle>
                <span>🌙</span>
                <span>Thème</span>
            </button>
        </nav>

        <div class="student-sidebar__spacer"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="student-link--button student-logout">Déconnexion</button>
        </form>
    </aside>

    <div class="student-main">
        <div class="student-topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-menu-btn" id="mobileMenuBtn">☰</button>

                <a href="{{ route('student.dashboard') }}" class="brand">
                    <span class="brand__mark">TA</span>
                    <span class="brand__text">
                        <strong>TIMAH ACADEMY</strong>
                        <span>Espace élève</span>
                    </span>
                </a>
            </div>

            <div class="topbar-right">
                <button type="button" class="theme-btn" data-theme-toggle>🌙 Thème</button>
                <a href="{{ route('student.subscription.index') }}" class="topbar-btn">Abonnement</a>
                <a href="{{ route('student.messages.create') }}" class="topbar-btn topbar-btn--primary">Poser une question</a>
            </div>
        </div>

        <main class="student-content">
            @if(session('success'))
                <div class="alert" style="margin-bottom:18px;">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert--error" style="margin-bottom:18px;">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
(() => {
    const root = document.documentElement;
    const storageKey = 'timah-student-theme';
    const app = document.getElementById('studentApp');
    const sidebar = document.getElementById('studentSidebar');
    const overlay = document.getElementById('mobileOverlay');
    const menuBtn = document.getElementById('mobileMenuBtn');
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    const getPreferredTheme = () => media.matches ? 'dark' : 'light';

    const applyTheme = (theme) => {
        root.setAttribute('data-theme', theme);
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.innerHTML = theme === 'dark' ? '☀️ Thème' : '🌙 Thème';
        });
    };

    const storedTheme = localStorage.getItem(storageKey);
    applyTheme(storedTheme || getPreferredTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem(storageKey, next);
            applyTheme(next);
        });
    });

    media.addEventListener('change', () => {
        if (!localStorage.getItem(storageKey)) {
            applyTheme(getPreferredTheme());
        }
    });

    const closeSidebar = () => app.classList.remove('is-sidebar-open');
    const openSidebar = () => app.classList.add('is-sidebar-open');

    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            if (app.classList.contains('is-sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            closeSidebar();
        }
    });

    document.querySelectorAll('.student-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });
})();
</script>

@stack('scripts')
</body>
</html>
