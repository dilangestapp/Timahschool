@php
    $generalSettings = \App\Models\PlatformSetting::group('general');
    $platformName = $generalSettings['platform_name'] ?? 'TIMAH ACADEMY';
    $platformSlogan = $generalSettings['platform_slogan'] ?? 'Plateforme éducative moderne et premium';
    $platformLogo = \App\Models\PlatformSetting::logoUrl($generalSettings['logo_path'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'TIMAH ACADEMY - plateforme éducative pour réussir avec des cours, TD et quiz structurés.')">
    <title>@yield('title', $platformName)</title>

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
            --primary: #3157ff;
            --primary-dark: #1734b7;
            --primary-soft: rgba(49, 87, 255, 0.10);
            --success: #0f9d58;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow-xs: 0 6px 16px rgba(15, 23, 42, 0.04);
            --shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 24px 60px rgba(15, 23, 42, 0.14);
            --radius-xs: 12px;
            --radius-sm: 16px;
            --radius: 22px;
            --radius-lg: 30px;
            --container: 1240px;
            --header-height: 88px;
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
            --primary: #8aaeff;
            --primary-dark: #b8ccff;
            --primary-soft: rgba(138, 174, 255, 0.12);
            --success: #35c47b;
            --warning: #fbbf24;
            --danger: #f87171;
            --shadow-xs: 0 6px 16px rgba(0, 0, 0, 0.18);
            --shadow: 0 14px 34px rgba(0, 0, 0, 0.28);
            --shadow-lg: 0 24px 60px rgba(0, 0, 0, 0.35);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(49, 87, 255, 0.10), transparent 30%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 28%),
                linear-gradient(180deg, var(--bg-soft), var(--bg));
            line-height: 1.6;
            transition: background .25s ease, color .25s ease;
        }

        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }
        button, input, textarea, select { font: inherit; }

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
            backdrop-filter: blur(18px);
            background: rgba(244, 247, 251, 0.88);
            border-bottom: 1px solid var(--line);
            transition: background .25s ease, border-color .25s ease, box-shadow .25s ease;
        }

        html[data-theme='dark'] .public-header {
            background: rgba(7, 17, 31, 0.82);
        }

        .public-header.is-scrolled {
            box-shadow: var(--shadow-xs);
        }

        .public-header__inner {
            min-height: var(--header-height);
            display: grid;
            grid-template-columns: minmax(260px, 360px) minmax(320px, 1fr) auto;
            align-items: center;
            gap: 18px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand__mark {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 950;
            color: #fff;
            background: linear-gradient(135deg, #0f172a, #3157ff 58%, #0f766e);
            box-shadow: 0 14px 28px rgba(49, 87, 255, 0.24);
            letter-spacing: -0.04em;
        }

        .brand__logo {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border-radius: 16px;
            object-fit: contain;
            background: rgba(255,255,255,.74);
            border: 1px solid var(--line);
            padding: 5px;
        }

        .brand__logo.is-hidden { display: none; }
        .brand__logo:not(.is-hidden) + .brand__mark { display: none; }

        .brand__text { display: flex; flex-direction: column; min-width: 0; }
        .brand__title { font-size: 1.05rem; font-weight: 950; line-height: 1.1; letter-spacing: -0.03em; white-space: nowrap; color: var(--text); }
        .brand__subtitle { color: var(--muted); font-size: .78rem; font-weight: 700; line-height: 1.2; white-space: normal; max-width: 270px; }
        .public-header__right { display: contents; }
        .public-nav { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; justify-content: center; }
        .nav-link { padding: 10px 12px; border-radius: 999px; color: var(--muted); font-size: .9rem; font-weight: 800; transition: .2s ease; }
        .nav-link:hover { color: var(--text); background: var(--primary-soft); }
        .header-tools { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; justify-content: flex-end; }

        .btn, .theme-toggle {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 0 18px;
            border-radius: 999px; border: 1px solid var(--line); background: var(--panel); color: var(--text); font-weight: 900;
            cursor: pointer; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease, color .2s ease;
            box-shadow: none; white-space: nowrap;
        }
        .btn:hover, .theme-toggle:hover { transform: translateY(-1px); box-shadow: var(--shadow-xs); }
        .btn--primary { color: #fff; border-color: transparent; background: linear-gradient(135deg, #3157ff, #6938ef); box-shadow: 0 14px 28px rgba(49, 87, 255, 0.24); }
        .btn--ghost { background: rgba(255, 255, 255, 0.76); }
        html[data-theme='dark'] .btn--ghost { background: rgba(14, 27, 49, 0.92); }
        .theme-toggle { width: 46px; min-width: 46px; padding: 0; font-size: 1rem; }
        .public-main { flex: 1; }

        .public-footer {
            margin-top: 56px; color: #d7e2f8;
            background: radial-gradient(circle at top left, rgba(71, 120, 255, 0.22), transparent 30%), linear-gradient(180deg, #0b1630, #07101f);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .public-footer__inner { padding: 34px 0 28px; display: grid; grid-template-columns: 1.1fr .9fr .9fr; gap: 22px; }
        .footer-brand { display: grid; gap: 12px; }
        .footer-brand strong { font-size: 1rem; color: #fff; letter-spacing: -0.02em; }
        .footer-brand p, .footer-links a, .footer-meta { color: rgba(231, 238, 252, 0.78); font-size: .95rem; }
        .footer-links { display: grid; gap: 10px; align-content: start; }
        .footer-links strong, .footer-contact strong { color: #fff; font-size: .95rem; }
        .footer-contact { display: grid; gap: 10px; align-content: start; }
        .footer-bottom { padding: 0 0 26px; color: rgba(231, 238, 252, 0.65); font-size: .88rem; }

        @media (max-width: 1180px) {
            .public-header__inner { grid-template-columns: 1fr auto; padding: 14px 0; min-height: auto; }
            .public-nav { grid-column: 1 / -1; grid-row: 2; justify-content: flex-start; padding-top: 2px; }
            .header-tools { grid-column: 2; grid-row: 1; }
        }
        @media (max-width: 720px) {
            :root { --header-height: 76px; }
            .container { width: min(var(--container), calc(100% - 22px)); }
            .public-header__inner { grid-template-columns: 1fr; gap: 12px; }
            .brand__subtitle { white-space: normal; max-width: none; }
            .public-nav { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 2px; }
            .nav-link { padding: 9px 12px; font-size: .88rem; }
            .header-tools { justify-content: flex-start; flex-wrap: wrap; grid-column: auto; grid-row: auto; }
            .btn, .theme-toggle { min-height: 44px; }
            .public-footer { margin-top: 42px; }
            .public-footer__inner { grid-template-columns: 1fr; }
        }
    </style>

    <style>{!! file_get_contents(public_path('assets/css/ui-groups.css')) !!}</style>
    @if(file_exists(public_path('assets/css/home-card-colors.css')))
        <style>{!! file_get_contents(public_path('assets/css/home-card-colors.css')) !!}</style>
    @endif
    @stack('styles')
</head>
<body data-ui-group="@yield('ui_group', 'public')" data-ui-role="public">
<div class="public-shell">
    <header class="public-header" id="publicHeader">
        <div class="container public-header__inner">
            <a href="{{ url('/') }}" class="brand">
                @if($platformLogo)
                    <img src="{{ $platformLogo }}" alt="" class="brand__logo" onerror="this.classList.add('is-hidden')">
                    <span class="brand__mark">TA</span>
                @else
                    <span class="brand__mark">TA</span>
                @endif

                <span class="brand__text">
                    <span class="brand__title">{{ $platformName }}</span>
                    <span class="brand__subtitle">{{ $platformSlogan }}</span>
                </span>
            </a>

            <div class="public-header__right">
                <nav class="public-nav">
                    <a href="{{ url('/') }}#classes" class="nav-link">Classes</a>
                    <a href="{{ url('/') }}#exam-countdowns" class="nav-link">Examens 2026</a>
                    <a href="{{ url('/') }}#pricing" class="nav-link">Abonnements</a>
                    <a href="{{ url('/') }}#mini-faq" class="nav-link">FAQ</a>
                    <a href="{{ url('/') }}#help-support" class="nav-link">Support</a>
                </nav>

                <div class="header-tools">
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Changer le thème">🌙</button>
                    @if (Route::has('login'))<a href="{{ route('login') }}" class="btn btn--ghost">Connexion</a>@endif
                    @if (Route::has('register'))<a href="{{ route('register') }}" class="btn btn--primary">Créer un compte</a>@endif
                </div>
            </div>
        </div>
    </header>

    <main class="public-main">@yield('content')</main>

    <footer class="public-footer">
        <div class="container">
            <div class="public-footer__inner">
                <div class="footer-brand"><strong>{{ $platformName }}</strong><p>Une plateforme pensée pour aider les élèves, les parents et les enseignants à avancer avec des cours structurés, des quiz, des TD et un meilleur suivi.</p></div>
                <div class="footer-links"><strong>Accès rapide</strong><a href="{{ url('/') }}#classes">Explorer les classes</a><a href="{{ url('/') }}#exam-countdowns">Examens officiels 2026</a><a href="{{ url('/') }}#pricing">Voir les abonnements</a><a href="{{ url('/') }}#mini-faq">Questions fréquentes</a><a href="{{ url('/') }}#help-support">Support et contact</a></div>
                <div class="footer-contact"><strong>Plateforme</strong><div class="footer-meta">Apprendre aujourd’hui, réussir demain.</div><div class="footer-meta">Expérience claire, moderne et orientée progression.</div></div>
            </div>
            <div class="footer-bottom">© {{ date('Y') }} {{ $platformName }} — Tous droits réservés.</div>
        </div>
    </footer>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.getElementById('themeToggle');
        var header = document.getElementById('publicHeader');
        function getCurrentTheme() { return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; }
        function refreshThemeButton() { if (!toggle) return; toggle.textContent = getCurrentTheme() === 'dark' ? '☀️' : '🌙'; toggle.setAttribute('aria-label', getCurrentTheme() === 'dark' ? 'Activer le thème clair' : 'Activer le thème sombre'); }
        function applyTheme(theme) { root.setAttribute('data-theme', theme); try { localStorage.setItem('timah-theme', theme); } catch (e) {} refreshThemeButton(); }
        if (toggle) { refreshThemeButton(); toggle.addEventListener('click', function () { applyTheme(getCurrentTheme() === 'dark' ? 'light' : 'dark'); }); }
        function updateHeaderState() { if (!header) return; if (window.scrollY > 12) header.classList.add('is-scrolled'); else header.classList.remove('is-scrolled'); }
        updateHeaderState(); window.addEventListener('scroll', updateHeaderState, { passive: true });
    })();
</script>
@stack('scripts')
</body>
</html>
