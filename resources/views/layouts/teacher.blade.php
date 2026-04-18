<!DOCTYPE html>
<html lang="fr" data-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace enseignant TIMAH ACADEMY">
    <title>@yield('title', 'Espace enseignant') - TIMAH ACADEMY</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <style>{!! file_get_contents(public_path('assets/css/teacher.css')) !!}</style>
</head>
<body>
<div class="teacher-shell">
    <aside class="teacher-sidebar">
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
            <div>
                <h1>@yield('page_title', 'Espace enseignant')</h1>
                <p>@yield('page_subtitle', 'Gestion de vos TD, corrigés et questions liées à vos affectations.')</p>
            </div>
            <div class="teacher-userbox">
                <strong>{{ auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username }}</strong>
                <small>Compte enseignant</small>
            </div>
            <button type="button" class="teacher-btn teacher-btn--ghost theme-toggle" data-theme-toggle>🌗 Thème</button>
        </header>

        <main class="teacher-content">
            @if(session('success'))<div class="teacher-alert teacher-alert--success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="teacher-alert teacher-alert--error">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="teacher-alert teacher-alert--error">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script>
(() => {
    const root = document.documentElement;
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
    applyTheme(getStoredTheme() || 'auto');
    updateToggleLabels();
    media.addEventListener('change', () => {
        if ((root.getAttribute('data-theme') || 'auto') === 'auto') updateToggleLabels();
    });
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = nextTheme();
            localStorage.setItem(storageKey, value);
            applyTheme(value);
            updateToggleLabels();
        });
    });
})();
</script>
</body>
</html>
