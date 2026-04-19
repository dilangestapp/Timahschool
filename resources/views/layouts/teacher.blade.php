<!DOCTYPE html>
<html lang="fr" data-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace enseignant TIMAH ACADEMY">
    <title>@yield('title', 'Espace enseignant') - TIMAH ACADEMY</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <style>{!! file_get_contents(public_path('assets/css/teacher.css')) !!}</style>
    <style>
        .teacher-layout-body {
            min-height: 100vh;
        }

        .teacher-drawer-toggle {
            display: none;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1px solid var(--teacher-border, #dbe6f3);
            background: rgba(255,255,255,.72);
            color: inherit;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            flex: 0 0 44px;
        }

        .teacher-drawer-backdrop {
            display: none;
        }

        .teacher-topbar__left {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }

        .teacher-topbar__left > div:last-child {
            min-width: 0;
        }

        .teacher-topbar__actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 1100px) {
            .teacher-shell {
                display: block !important;
            }

            .teacher-sidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                bottom: 0;
                width: min(84vw, 320px) !important;
                height: 100vh !important;
                z-index: 1200;
                transform: translateX(-100%);
                transition: transform .25s ease;
                box-shadow: 0 20px 60px rgba(15, 23, 42, .32);
            }

            body.teacher-drawer-open .teacher-sidebar {
                transform: translateX(0);
            }

            .teacher-drawer-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1100;
                background: rgba(15, 23, 42, .48);
            }

            body.teacher-drawer-open .teacher-drawer-backdrop {
                display: block;
            }

            .teacher-main {
                width: 100%;
                min-width: 0;
            }

            .teacher-drawer-toggle {
                display: inline-flex;
            }

            .teacher-topbar {
                position: sticky;
                top: 0;
                z-index: 100;
                background: rgba(244,248,253,.92);
                backdrop-filter: blur(10px);
                padding: 14px 16px 0 !important;
                margin-bottom: 0;
            }

            html[data-theme='dark'] .teacher-topbar {
                background: rgba(10,13,20,.92);
            }

            .teacher-content {
                padding: 16px !important;
            }

            .teacher-userbox {
                padding: 10px 12px !important;
                border-radius: 14px !important;
            }

            .teacher-topbar__actions {
                width: 100%;
                justify-content: space-between;
            }

            .teacher-topbar .theme-toggle {
                min-height: 42px;
            }
        }

        @media (max-width: 640px) {
            .teacher-topbar {
                gap: 12px;
            }

            .teacher-topbar__left h1 {
                font-size: 1.3rem !important;
            }

            .teacher-topbar__left p {
                font-size: .86rem !important;
                line-height: 1.45 !important;
            }

            .teacher-topbar__actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                align-items: stretch;
            }

            .teacher-topbar__actions .teacher-userbox,
            .teacher-topbar__actions .theme-toggle {
                width: 100%;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="teacher-layout-body">
<div class="teacher-drawer-backdrop" data-teacher-drawer-close></div>

<div class="teacher-shell">
    <aside class="teacher-sidebar" id="teacherSidebar">
        <div class="teacher-sidebar__top">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-brand">
                <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-light.svg') }}" alt="TIMAH ACADEMY" style="height:34px; width:auto;">
            </a>
        </div>

        <nav class="teacher-nav">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}">Tableau de bord</a>
            <a href="{{ route('teacher.classes.index') }}" class="teacher-link {{ request()->routeIs('teacher.classes.*') ? 'is-active' : '' }}">Mes classes</a>
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.sets.*') ? 'is-active' : '' }}">Mes TD</a>
            <a href="{{ route('teacher.td.questions.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.questions.*') ? 'is-active' : '' }}">Questions TD</a>
            <a href="{{ route('teacher.messages.index') }}" class="teacher-link {{ request()->routeIs('teacher.messages.*') ? 'is-active' : '' }}">Messagerie</a>
        </nav>

        <div class="teacher-sidebar__bottom">
            <a href="{{ route('home') }}" class="teacher-link teacher-link--bottom">← Retour au site</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="teacher-logout">Déconnexion</button>
            </form>
        </div>
    </aside>

    <div class="teacher-main">
        <header class="teacher-topbar">
            <div class="teacher-topbar__left">
                <button type="button" class="teacher-drawer-toggle" data-teacher-drawer-open>☰</button>

                <div>
                    <h1>@yield('page_title', 'Espace enseignant')</h1>
                    <p>@yield('page_subtitle', 'Gestion de vos TD, corrigés et questions liées à vos affectations.')</p>
                </div>
            </div>

            <div class="teacher-topbar__actions">
                <div class="teacher-userbox">
                    <strong>{{ auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username }}</strong>
                    <small>Compte enseignant</small>
                </div>

                <button type="button" class="teacher-btn teacher-btn--ghost theme-toggle" data-theme-toggle>🌗 Thème</button>
            </div>
        </header>

        <main class="teacher-content">
            @if(session('success'))
                <div class="teacher-alert teacher-alert--success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="teacher-alert teacher-alert--error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="teacher-alert teacher-alert--error">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
(() => {
    const root = document.documentElement;
    const body = document.body;
    const storageKey = 'timah-theme';
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    const prefersDark = () => media.matches;
    const getStoredTheme = () => localStorage.getItem(storageKey);
    const applyTheme = (theme) => root.setAttribute('data-theme', theme || 'auto');

    const currentEffectiveTheme = () => {
        const active = root.getAttribute('data-theme') || 'auto';
        return active === 'auto' ? (prefersDark() ? 'dark' : 'light') : active;
    };

    const nextTheme = () => {
        const active = root.getAttribute('data-theme') || 'auto';
        if (active === 'auto') return prefersDark() ? 'light' : 'dark';
        return active === 'dark' ? 'light' : 'dark';
    };

    const updateToggleLabels = () => {
        const effective = currentEffectiveTheme();
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.textContent = effective === 'dark' ? '☀️ Clair' : '🌙 Sombre';
        });
    };

    const closeDrawer = () => {
        body.classList.remove('teacher-drawer-open');
    };

    const openDrawer = () => {
        body.classList.add('teacher-drawer-open');
    };

    applyTheme(getStoredTheme() || 'auto');
    updateToggleLabels();

    media.addEventListener('change', () => {
        if ((root.getAttribute('data-theme') || 'auto') === 'auto') {
            updateToggleLabels();
        }
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = nextTheme();
            localStorage.setItem(storageKey, value);
            applyTheme(value);
            updateToggleLabels();
        });
    });

    document.querySelectorAll('[data-teacher-drawer-open]').forEach((button) => {
        button.addEventListener('click', openDrawer);
    });

    document.querySelectorAll('[data-teacher-drawer-close]').forEach((button) => {
        button.addEventListener('click', closeDrawer);
    });

    document.querySelectorAll('.teacher-sidebar .teacher-link').forEach((link) => {
        link.addEventListener('click', closeDrawer);
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1100) {
            closeDrawer();
        }
    });
})();
</script>

@stack('scripts')
</body>
</html>
