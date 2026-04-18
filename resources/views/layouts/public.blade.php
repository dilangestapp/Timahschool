<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'TIMAH ACADEMY - plateforme éducative pour réussir avec des cours, TD et quiz structurés.')">
    <title>@yield('title', 'TIMAH ACADEMY')</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #ffffff;
            --panel-soft: #eef4ff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #d9e2f0;
            --primary: #1d6dff;
            --primary-dark: #0b3ea8;
            --success: #0f9d58;
            --shadow: 0 10px 28px rgba(15, 23, 42, .08);
            --shadow-lg: 0 18px 48px rgba(15, 23, 42, .12);
            --radius: 18px;
            --radius-sm: 12px;
            --container: 1200px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.55;
        }

        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }

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
            z-index: 30;
            backdrop-filter: blur(14px);
            background: rgba(244, 247, 251, .88);
            border-bottom: 1px solid var(--line);
        }

        .public-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 78px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .brand__mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), #5f8dff);
            color: #fff;
            box-shadow: var(--shadow);
            font-size: 1.05rem;
        }

        .brand small {
            display: block;
            color: var(--muted);
            font-weight: 600;
            font-size: .76rem;
            letter-spacing: 0;
        }

        .public-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-link {
            padding: 10px 14px;
            border-radius: 999px;
            color: var(--muted);
            transition: .2s ease;
            font-size: .94rem;
        }

        .nav-link:hover {
            color: var(--text);
            background: rgba(29, 109, 255, .08);
        }

        .btn {
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
            font-weight: 700;
            transition: .2s ease;
            cursor: pointer;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn--primary {
            background: linear-gradient(135deg, var(--primary), #4f72ff);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 12px 24px rgba(29, 109, 255, .22);
        }
        .btn--ghost {
            background: rgba(255,255,255,.75);
        }
        .btn--full { width: 100%; }

        .section {
            padding: 56px 0;
        }
        .section--tight {
            padding: 34px 0;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: clamp(1.7rem, 2.5vw, 2.4rem);
            line-height: 1.1;
            letter-spacing: -.02em;
        }
        .section-subtitle {
            margin: 0 0 20px;
            color: var(--muted);
            max-width: 70ch;
        }

        .muted { color: var(--muted); }
        .feature-list {
            list-style: none;
            margin: 16px 0 20px;
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
            font-weight: 800;
        }
        .plan-price {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            margin: 8px 0 10px;
        }

        .public-main {
            flex: 1;
        }

        .public-footer {
            margin-top: 40px;
            border-top: 1px solid var(--line);
            background: #fff;
        }

        .public-footer__inner {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 26px 0;
            color: var(--muted);
            font-size: .92rem;
        }

        @media (max-width: 900px) {
            .public-header__inner {
                min-height: auto;
                padding: 14px 0;
                flex-direction: column;
                align-items: stretch;
            }
            .public-nav {
                justify-content: center;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="public-shell">
    <header class="public-header">
        <div class="container public-header__inner">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand__mark">T</span>
                <span>
                    TIMAH ACADEMY
                    <small>Plateforme EdTech premium</small>
                </span>
            </a>

            <nav class="public-nav">
                <a href="{{ url('/') }}#classes" class="nav-link">Classes</a>
                <a href="{{ url('/') }}#pricing" class="nav-link">Abonnements</a>
                <a href="{{ url('/') }}#mini-faq" class="nav-link">FAQ</a>
                <a href="{{ url('/') }}#help-support" class="nav-link">Support</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn--ghost">Connexion</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn--primary">Créer un compte</a>
                @endif
            </nav>
        </div>
    </header>

    <main class="public-main">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="container public-footer__inner">
            <div>
                <strong>TIMAH ACADEMY</strong><br>
                <span>Réussissez vos examens avec une plateforme claire, moderne et structurée.</span>
            </div>
            <div>
                © {{ date('Y') }} TIMAH ACADEMY — Tous droits réservés.
            </div>
        </div>
    </footer>
</div>
@stack('scripts')
</body>
</html>
