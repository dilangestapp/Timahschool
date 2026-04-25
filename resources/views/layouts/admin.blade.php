@php
    $generalSettings = \App\Models\PlatformSetting::group('general');
    $platformName = $generalSettings['platform_name'] ?? 'TIMAH ACADEMY';
    $platformSlogan = $generalSettings['platform_slogan'] ?? 'Plateforme éducative moderne et premium';
    $platformLogo = \App\Models\PlatformSetting::logoUrl($generalSettings['logo_path'] ?? null);
    $initialTheme = request()->cookie('timah-admin-theme', 'light') === 'dark' ? 'dark' : 'light';
@endphp
<!DOCTYPE html>
<html lang="fr" data-theme="{{ $initialTheme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portail administrateur {{ $platformName }}">
    <title>@yield('title', 'Admin') - {{ $platformName }}</title>
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('timah-admin-theme');
                var safeTheme = savedTheme === 'dark' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', safeTheme);
                document.cookie = 'timah-admin-theme=' + safeTheme + '; path=/; max-age=31536000; SameSite=Lax';
            } catch (e) {
                document.documentElement.setAttribute('data-theme', '{{ $initialTheme }}');
            }
        })();
    </script>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <style>{!! file_get_contents(public_path('assets/css/admin.css')) !!}</style>
    <style>{!! file_get_contents(public_path('assets/css/ui-groups.css')) !!}</style>
    @if(file_exists(public_path('assets/css/admin-navigation.css')))
        <style>{!! file_get_contents(public_path('assets/css/admin-navigation.css')) !!}</style>
    @endif
    @if(file_exists(public_path('assets/css/admin-readability.css')))
        <style>{!! file_get_contents(public_path('assets/css/admin-readability.css')) !!}</style>
    @endif
    @stack('styles')
    @if(file_exists(public_path('assets/css/theme-stability.css')))
        <style>{!! file_get_contents(public_path('assets/css/theme-stability.css')) !!}</style>
    @endif
</head>
<body data-ui-group="@yield('ui_group', 'control')" data-ui-role="admin">
<button type="button" class="admin-mobile-nav-toggle" data-admin-nav-toggle aria-label="Ouvrir le menu admin">☰</button>
<div class="admin-nav-overlay" data-admin-nav-close></div>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__top">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                @if($platformLogo)
                    <img src="{{ $platformLogo }}" alt="{{ $platformName }}" style="height:44px; width:auto; display:block;">
                @else
                    <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-light.svg') }}" alt="{{ $platformName }}" style="height:34px; width:auto; display:block;">
                @endif
            </a>
        </div>

        <nav class="admin-nav">
            <div class="admin-nav__group-label">Pilotage</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Tableau de bord</a>
            <a href="{{ route('admin.settings.edit') }}" class="admin-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">Paramètres</a>
            <a href="{{ route('admin.homepage.edit') }}" class="admin-link {{ request()->routeIs('admin.homepage.*') ? 'is-active' : '' }}">Homepage</a>

            <div class="admin-nav__group-label">Utilisateurs</div>
            <a href="{{ route('admin.users.index') }}" class="admin-link {{ request()->routeIs('admin.users.index') ? 'is-active' : '' }}">Utilisateurs</a>
            <a href="{{ route('admin.users.activity') }}" class="admin-link {{ request()->routeIs('admin.users.activity') ? 'is-active' : '' }}">Connexions</a>
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
            <a href="{{ route('home') }}" class="admin-link admin-link--bottom">Retour au site</a>
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
                <p>@yield('page_subtitle', 'Portail d’administration central de ' . $platformName . '.')</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="{{ route('admin.settings.edit') }}" class="btn btn--ghost">Paramètres</a>
                <a href="{{ route('admin.homepage.edit') }}" class="btn btn--ghost">Homepage</a>
                <a href="{{ route('admin.users.activity') }}" class="btn btn--ghost">Connexions</a>
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
            @if(session('warning'))
                <div class="admin-alert admin-alert--warning">{{ session('warning') }}</div>
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
    const storageKey = 'timah-admin-theme';
    const getStoredTheme = () => localStorage.getItem(storageKey);
    const applyTheme = (theme) => {
        const safeTheme = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', safeTheme);
        localStorage.setItem(storageKey, safeTheme);
        document.cookie = storageKey + '=' + safeTheme + '; path=/; max-age=31536000; SameSite=Lax';
    };
    const nextTheme = () => (root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    const updateToggleLabels = () => {
        const active = root.getAttribute('data-theme') || 'light';
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.textContent = active === 'dark' ? '☀️ Clair' : '🌙 Sombre';
        });
    };
    applyTheme(getStoredTheme() || root.getAttribute('data-theme') || 'light');
    updateToggleLabels();
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            applyTheme(nextTheme());
            updateToggleLabels();
        });
    });

    const openNav = () => document.body.classList.add('admin-nav-open');
    const closeNav = () => document.body.classList.remove('admin-nav-open');
    document.querySelectorAll('[data-admin-nav-toggle]').forEach((button) => button.addEventListener('click', openNav));
    document.querySelectorAll('[data-admin-nav-close]').forEach((item) => item.addEventListener('click', closeNav));
    document.querySelectorAll('.admin-sidebar .admin-link').forEach((link) => link.addEventListener('click', closeNav));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeNav();
    });
})();
</script>
@stack('scripts')
</body>
</html>
