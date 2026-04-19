<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'TIMAH ACADEMY - plateforme éducative pour réussir avec des cours, TD et quiz structurés.')">
    <title>@yield('title', 'TIMAH ACADEMY')</title>

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('timah-theme');
                if (savedTheme === 'dark' || savedTheme === 'light') {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                }
            } catch (e) {}
        })();
    </script>

    <style>
        :root {
            --bg: #f4f7fb;
            --bg-soft: #edf3ff;
            --panel: #ffffff;
            --panel-soft: #f7faff;
            --panel-strong: #eaf1ff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #dbe5f2;
            --line-strong: #c9d7ea;
            --primary: #1d6dff;
            --primary-dark: #0b3ea8;
            --primary-soft: rgba(29, 109, 255, 0.10);
            --success: #0f9d58;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow-xs: 0 6px 16px rgba(15, 23, 42, 0.04);
            --shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 20px 56px rgba(15, 23, 42, 0.12);
            --radius-xs: 12px;
            --radius-sm: 16px;
            --radius: 22px;
            --radius-lg: 30px;
            --container: 1240px;
            --header-height: 86px;
        }

        html[data-theme='dark'] {
            --bg: #07111f;
            --bg-soft: #0b1729;
            --panel: #0e1b31;
            --panel-soft: #10203a;
            --panel-strong: #142641;
            --text: #e8eefb;
            --muted: #9fb0cc;
            --line: rgba(190, 207, 238, 0.12);
            --line-strong: rgba(190, 207, 238, 0.18);
            --primary: #6ea1ff;
            --primary-dark: #9cc0ff;
            --primary-soft: rgba(110, 161, 255, 0.12);
            --success: #35c47b;
            --warning: #fbbf24;
            --danger: #f87171;
            --shadow-xs: 0 6px 16px rgba(0, 0, 0, 0.18);
            --shadow: 0 14px 34px rgba(0, 0, 0, 0.28);
            --shadow-lg: 0 24px 60px rgba(0, 0, 0, 0.35);
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
                radial-gradient(circle at top left, rgba(29, 109, 255, 0.10), transparent 30%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 28%),
                linear-gradient(180deg, var(--bg-soft), var(--bg));
            line-height: 1.6;
            transition: background .25s ease, color .25s ease;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            display: block;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        .container {
            width: min(var(--container), calc(100% - 32px));
            margin: 0 auto;
        }

        .public-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .public-header {
            position: sticky;
            top: 0;
            z-index: 60;
            backdrop-filter: blur(16px);
            background: rgba(244, 247, 251, 0.78);
            border-bottom: 1px solid var(--line);
            transition: background .25s ease, border-color .25s ease, box-shadow .25s ease;
        }

        html[data-theme='dark'] .public-header {
            background: rgba(7, 17, 31, 0.72);
        }

        .public-header.is-scrolled {
            box-shadow: var(--shadow-xs);
        }

        .public-header__inner {
            min-height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand__mark {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), #4f86ff);
            box-shadow: 0 14px 28px rgba(29, 109, 255, 0.26);
            letter-spacing: -0.02em;
        }

        .brand__text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .brand__title {
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.02em;
            white-space: nowrap;
        }

        .brand__subtitle {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        .public-header__right {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .public-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nav-link {
            padding: 10px 14px;
            border-radius: 999px;
            color: var(--muted);
            font-size: .94rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .nav-link:hover {
            color: var(--text);
            background: var(--primary-soft);
        }

        .header-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn,
        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--panel);
            color: var(--text);
            font-weight: 800;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease, color .2s ease;
            box-shadow: none;
        }

        .btn:hover,
        .theme-toggle:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-xs);
        }

        .btn--primary {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary), #4f86ff);
            box-shadow: 0 14px 28px rgba(29, 109, 255, 0.24);
        }

        .btn--ghost {
            background: rgba(255, 255, 255, 0.76);
        }

        html[data-theme='dark'] .btn--ghost {
            background: rgba(14, 27, 49, 0.92);
        }

        .btn--full {
            width: 100%;
        }

        .theme-toggle {
            width: 46px;
            min-width: 46px;
            padding: 0;
            font-size: 1rem;
        }

        .section {
            padding: 72px 0;
        }

        .section--tight {
            padding: 44px 0;
        }

        .section-title {
            margin: 0 0 10px;
            font-size: clamp(1.85rem, 2.8vw, 2.8rem);
            line-height: 1.08;
            letter-spacing: -0.03em;
        }

        .section-subtitle {
            margin: 0;
            max-width: 760px;
            color: var(--muted);
            font-size: 1rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.72);
            color: var(--primary);
            font-weight: 800;
            font-size: .84rem;
            letter-spacing: -0.01em;
        }

        html[data-theme='dark'] .eyebrow {
            background: rgba(14, 27, 49, 0.88);
        }

        .muted {
            color: var(--muted);
        }

        .feature-list {
            list-style: none;
            margin: 18px 0 22px;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--muted);
        }

        .feature-list li span:first-child {
            color: var(--success);
            font-weight: 900;
        }

        .plan-price {
            margin: 10px 0 10px;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
        }

        .public-main {
            flex: 1;
        }

        .public-footer {
            margin-top: 56px;
            color: #d7e2f8;
            background:
                radial-gradient(circle at top left, rgba(71, 120, 255, 0.22), transparent 30%),
                linear-gradient(180deg, #0b1630, #07101f);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .public-footer__inner {
            padding: 34px 0 28px;
            display: grid;
            grid-template-columns: 1.1fr .9fr .9fr;
            gap: 22px;
        }

        .footer-brand {
            display: grid;
            gap: 12px;
        }

        .footer-brand strong {
            font-size: 1rem;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .footer-brand p,
        .footer-links a,
        .footer-meta {
            color: rgba(231, 238, 252, 0.78);
            font-size: .95rem;
        }

        .footer-links {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .footer-links strong,
        .footer-contact strong {
            color: #fff;
            font-size: .95rem;
        }

        .footer-contact {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .footer-bottom {
            padding: 0 0 26px;
            color: rgba(231, 238, 252, 0.65);
            font-size: .88rem;
        }

        @media (max-width: 1080px) {
            .public-header__inner {
                min-height: auto;
                padding: 14px 0;
                flex-direction: column;
                align-items: stretch;
            }

            .public-header__right,
            .public-nav,
            .header-tools {
                justify-content: center;
            }

            .public-footer__inner {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            :root {
                --header-height: 76px;
            }

            .container {
                width: min(var(--container), calc(100% - 22px));
            }

            .brand__subtitle {
                white-space: normal;
            }

            .public-nav {
                gap: 6px;
            }

            .nav-link {
                padding: 9px 12px;
                font-size: .9rem;
            }

            .btn,
            .theme-toggle {
                min-height: 44px;
            }

            .section {
                padding: 58px 0;
            }

            .section--tight {
                padding: 36px 0;
            }

            .public-footer {
                margin-top: 42px;
            }
        }
    </style>

    <style>{!! file_get_contents(public_path('assets/css/ui-groups.css')) !!}</style>
    @stack('styles')
</head>
<body data-ui-group="@yield('ui_group', 'public')" data-ui-role="public">
<div class="public-shell">
    <header class="public-header" id="publicHeader">
        <div class="container public-header__inner">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand__mark">TA</span>
                <span class="brand__text">
                    <span class="brand__title">TIMAH ACADEMY</span>
                    <span class="brand__subtitle">Plateforme éducative moderne et premium</span>
                </span>
            </a>

            <div class="public-header__right">
                <nav class="public-nav">
                    <a href="{{ url('/') }}#classes" class="nav-link">Classes</a>
                    <a href="{{ url('/') }}#pricing" class="nav-link">Abonnements</a>
                    <a href="{{ url('/') }}#mini-faq" class="nav-link">FAQ</a>
                    <a href="{{ url('/') }}#help-support" class="nav-link">Support</a>
                </nav>

                <div class="header-tools">
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Changer le thème">🌙</button>

                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn--ghost">Connexion</a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn--primary">Créer un compte</a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="public-main">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="container">
            <div class="public-footer__inner">
                <div class="footer-brand">
                    <strong>TIMAH ACADEMY</strong>
                    <p>
                        Une plateforme pensée pour aider les élèves, les parents et les enseignants
                        à avancer avec des cours structurés, des quiz, des TD et un meilleur suivi.
                    </p>
                </div>

                <div class="footer-links">
                    <strong>Accès rapide</strong>
                    <a href="{{ url('/') }}#classes">Explorer les classes</a>
                    <a href="{{ url('/') }}#pricing">Voir les abonnements</a>
                    <a href="{{ url('/') }}#mini-faq">Questions fréquentes</a>
                    <a href="{{ url('/') }}#help-support">Support et contact</a>
                </div>

                <div class="footer-contact">
                    <strong>Plateforme</strong>
                    <div class="footer-meta">Apprendre aujourd’hui, réussir demain.</div>
                    <div class="footer-meta">Expérience claire, moderne et orientée progression.</div>
                </div>
            </div>

            <div class="footer-bottom">
                © {{ date('Y') }} TIMAH ACADEMY — Tous droits réservés.
            </div>
        </div>
    </footer>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.getElementById('themeToggle');
        var header = document.getElementById('publicHeader');

        function getCurrentTheme() {
            return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        }

        function refreshThemeButton() {
            if (!toggle) {
                return;
            }

            toggle.textContent = getCurrentTheme() === 'dark' ? '☀️' : '🌙';
            toggle.setAttribute('aria-label', getCurrentTheme() === 'dark' ? 'Activer le thème clair' : 'Activer le thème sombre');
        }

        function applyTheme(theme) {
            root.setAttribute('data-theme', theme);
            try {
                localStorage.setItem('timah-theme', theme);
            } catch (e) {}
            refreshThemeButton();
        }

        if (toggle) {
            refreshThemeButton();

            toggle.addEventListener('click', function () {
                applyTheme(getCurrentTheme() === 'dark' ? 'light' : 'dark');
            });
        }

        function updateHeaderState() {
            if (!header) {
                return;
            }

            if (window.scrollY > 12) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }
        }

        updateHeaderState();
        window.addEventListener('scroll', updateHeaderState, { passive: true });
    })();
</script>

@stack('scripts')
</body>
</html>
