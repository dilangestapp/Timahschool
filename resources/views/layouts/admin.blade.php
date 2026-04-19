<!DOCTYPE html>
<html lang="fr" data-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portail administrateur sécurisé TIMAH ACADEMY">
    <title>@yield('title', 'Admin') - TIMAH ACADEMY</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <style>{!! file_get_contents(public_path('assets/css/admin.css')) !!}</style>
    <style>{!! file_get_contents(public_path('assets/css/ui-groups.css')) !!}</style>
    @stack('styles')
</head>
<body data-ui-group="@yield('ui_group', 'control')" data-ui-role="admin">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__top">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-light.svg') }}" alt="TIMAH ACADEMY" style="height:34px; width:auto;">
            </a>
        </div>

        <nav class="admin-nav">
            <div class="admin-nav__group-label">Pilotage</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Tableau de bord</a>
            <a href="{{ route('admin.homepage.edit') }}" class="admin-link {{ request()->routeIs('admin.homepage.*') ? 'is-active' : '' }}">Homepage</a>

            <div class="admin-nav__group-label">Utilisateurs</div>
            <a href="{{ route('admin.users.index') }}" class="admin-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Utilisateurs</a>
            <a href="{{ route('admin.teachers.index') }}" class="admin-link {{ request()->routeIs('admin.teachers.*') ? 'is-active' : '' }}">Enseignants</a>
            <a href="{{ route('admin.assignments.index') }}" class="admin-link {{ request()->routeIs('admin.assignments.*') ? 'is-active' : '' }}">Affectations</a>

            <div class="admin-nav__group-label">Pédagogie</div>
            <a href="{{ route('admin.classes.index') }}" class="admin-link {{ request()->routeIs('admin.classes.*') ? 'is-active' : '' }}">Classes</a>
            <a href="{{ route('admin.subjects.index') }}" class="admin-link {{ request()->routeIs('admin.subjects.*') ? 'is-active' : '' }}">Matières</a>
            <a href="{{ route('admin.courses.index') }}" class="admin-link {{ request()->routeIs('admin.courses.*') ? 'is-active' : '' }}">Cours</a>
            <a href="{{ route('admin.td.index') }}" class="admin-link {{ request()->routeIs('admin.td.*') ? 'is-active' : '' }}">TD</a>

            <div class="admin-nav__group-label">Business</div>
            <a href="{{ route('admin.plans.index') }}" class="admin-link {{ request()->routeIs('admin.plans.*') ? 'is-active' : '' }}">Plans</a>
            <a href="{{ route('admin.subscriptions.index') }}" class="admin-link {{ request()->routeIs('admin.subscriptions.*') ? 'is-active' : '' }}">Abonnements</a>
            <a href="{{ route('admin.payments.index') }}" class="admin-link {{ request()->routeIs('admin.payments.*') ? 'is-active' : '' }}">Paiements</a>
        </nav>

        <div class="admin-sidebar__bottom">
            <a href="{{ route('home') }}" class="admin-link admin-link--bottom">← Retour au site</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-logout">Déconnexion admin</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>@yield('page_title', 'Administration')</h1>
                <p>@yield('page_subtitle', 'Portail d’administration central de TIMAH ACADEMY.')</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="{{ route('admin.homepage.edit') }}" class="btn btn--ghost">Homepage</a>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn--ghost">+ Enseignant</a>
                <a href="{{ route('admin.assignments.index') }}" class="btn btn--ghost">+ Affectation</a>
                <button type="button" class="btn btn--ghost theme-toggle" data-theme-toggle>🌗 Thème</button>
                @if(request()->routeIs('admin.td.*'))
                    <a href="{{ route('admin.td.create') }}" class="btn btn--primary">+ Nouveau TD</a>
                @else
                    <a href="{{ route('admin.td.index') }}" class="btn btn--primary">Gérer les TD</a>
                @endif
                <div class="admin-userbox">
                    <strong>{{ auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username }}</strong>
                    <small>Compte administrateur</small>
                </div>
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))
                <div class="admin-alert admin-alert--success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="admin-alert admin-alert--error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="admin-alert admin-alert--error">{{ $errors->first() }}</div>
            @endif

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

@stack('scripts')
</body>
</html>
