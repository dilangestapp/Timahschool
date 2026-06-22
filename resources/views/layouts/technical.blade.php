@php
    $generalSettings = \App\Models\PlatformSetting::group('general');
    $platformName = $generalSettings['platform_name'] ?? 'TIMAH ACADEMY';
    $platformSlogan = $generalSettings['platform_slogan'] ?? 'Pour apprendre, reviser et reussir.';
    $platformLogo = \App\Models\PlatformSetting::logoUrl($generalSettings['logo_path'] ?? null);
    $initialTheme = request()->cookie('timah-admin-theme', 'light') === 'dark' ? 'dark' : 'light';
@endphp
<!DOCTYPE html>
<html lang="fr" data-theme="{{ $initialTheme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace Responsable Enseignement Technique {{ $platformName }}">
    <title>@yield('title', 'Responsable Technique') - {{ $platformName }}</title>
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
    @if(file_exists(public_path('assets/css/admin-mobile-fix.css')))
        <style>{!! file_get_contents(public_path('assets/css/admin-mobile-fix.css')) !!}</style>
    @endif
    <style>
        .technical-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.technical-kpi{padding:18px;border-radius:22px;background:var(--admin-card-bg,#fff);border:1px solid var(--admin-border,rgba(15,23,42,.08));box-shadow:0 18px 45px rgba(15,23,42,.06)}.technical-kpi strong{display:block;font-size:30px;line-height:1}.technical-kpi span{display:block;margin-top:7px;color:var(--admin-muted,#64748b);font-size:13px}.technical-grid{display:grid;grid-template-columns:1.35fr .9fr;gap:18px}.technical-panel{background:var(--admin-card-bg,#fff);border:1px solid var(--admin-border,rgba(15,23,42,.08));border-radius:24px;padding:18px;box-shadow:0 18px 45px rgba(15,23,42,.05)}.technical-panel h2{margin:0 0 8px;font-size:18px}.technical-panel p{margin:0;color:var(--admin-muted,#64748b)}.technical-list{display:grid;gap:12px;margin-top:16px}.technical-row{display:flex;justify-content:space-between;gap:12px;padding:14px;border-radius:18px;background:rgba(148,163,184,.08)}.technical-row strong{display:block}.technical-row span{display:block;color:var(--admin-muted,#64748b);font-size:13px;margin-top:4px}.technical-badges{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}.technical-alert{padding:13px 14px;border-radius:18px;border:1px solid rgba(148,163,184,.20);background:rgba(148,163,184,.08)}.technical-alert--danger{background:rgba(239,68,68,.10)}.technical-alert--warning{background:rgba(245,158,11,.12)}.technical-alert--info{background:rgba(59,130,246,.10)}@media(max-width:980px){.technical-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.technical-grid{grid-template-columns:1fr}}@media(max-width:620px){.technical-kpi-grid{grid-template-columns:1fr}.technical-row{display:block}.technical-badges{justify-content:flex-start;margin-top:10px}}
    </style>
    @stack('styles')
</head>
<body data-ui-group="control" data-ui-role="technical-supervisor">
<button type="button" class="admin-mobile-nav-toggle" data-admin-nav-toggle aria-label="Ouvrir le menu responsable technique">☰</button>
<div class="admin-nav-overlay" data-admin-nav-close></div>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__top">
            <a href="{{ route('technical.dashboard') }}" class="admin-brand" data-tech-nav="overview">
                @if($platformLogo)
                    <img src="{{ $platformLogo }}" alt="{{ $platformName }}" style="height:44px;width:auto;display:block;">
                @else
                    <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-light.svg') }}" alt="{{ $platformName }}" style="height:34px;width:auto;display:block;">
                @endif
            </a>
        </div>

        <nav class="admin-nav">
            <div class="admin-nav__group-label">Section technique</div>
            <a href="{{ route('technical.dashboard') }}" class="admin-link {{ request()->routeIs('technical.dashboard') ? 'is-active' : '' }}" data-tech-nav="overview">Tableau de bord</a>
            <a href="#technical-classes" class="admin-link" data-tech-nav="classes">Classes techniques</a>
            <a href="#technical-teachers" class="admin-link" data-tech-nav="teachers">Enseignants</a>
            <a href="#technical-courses" class="admin-link" data-tech-nav="courses">Cours</a>
            <a href="#technical-td" class="admin-link" data-tech-nav="td">TD / controles</a>
            <a href="#technical-alerts" class="admin-link" data-tech-nav="alerts">Alertes</a>
        </nav>

        <div class="admin-sidebar__bottom">
            <a href="{{ route('home') }}" class="admin-link admin-link--bottom">Retour au site</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="admin-logout">Deconnexion</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>@yield('page_title', 'Responsable Enseignement Technique')</h1>
                <p>@yield('page_subtitle', 'Supervision pedagogique des classes, enseignants, cours et TD techniques.')</p>
            </div>
            <div class="admin-topbar__actions">
                <button type="button" class="btn btn--ghost theme-toggle" data-theme-toggle>🌗 Theme</button>
                <div class="admin-userbox">
                    <strong>{{ auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username }}</strong>
                    <small>Responsable technique</small>
                </div>
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))<div class="admin-alert admin-alert--success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="admin-alert admin-alert--warning">{{ session('warning') }}</div>@endif
            @if(session('error'))<div class="admin-alert admin-alert--error">{{ session('error') }}</div>@endif
            @yield('content')
            @if(file_exists(public_path('assets/css/technical-dashboard-polish.css')))
                <style>{!! file_get_contents(public_path('assets/css/technical-dashboard-polish.css')) !!}</style>
            @endif
            @if(file_exists(public_path('assets/css/technical-dashboard-panels.css')))
                <style>{!! file_get_contents(public_path('assets/css/technical-dashboard-panels.css')) !!}</style>
            @endif
        </main>
    </div>
</div>
<script>
(() => {
    const root = document.documentElement;
    const storageKey = 'timah-admin-theme';
    const applyTheme = (theme) => {
        const safeTheme = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', safeTheme);
        localStorage.setItem(storageKey, safeTheme);
        document.cookie = storageKey + '=' + safeTheme + '; path=/; max-age=31536000; SameSite=Lax';
    };
    const updateToggleLabels = () => {
        const active = root.getAttribute('data-theme') || 'light';
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => button.textContent = active === 'dark' ? '☀️ Clair' : '🌙 Sombre');
    };
    applyTheme(localStorage.getItem(storageKey) || root.getAttribute('data-theme') || 'light');
    updateToggleLabels();
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => button.addEventListener('click', () => { applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); updateToggleLabels(); }));

    const openNav = () => document.body.classList.add('admin-nav-open');
    const closeNav = () => document.body.classList.remove('admin-nav-open');
    document.querySelectorAll('[data-admin-nav-toggle]').forEach((button) => button.addEventListener('click', openNav));
    document.querySelectorAll('[data-admin-nav-close]').forEach((item) => item.addEventListener('click', closeNav));
    document.querySelectorAll('.admin-sidebar .admin-link').forEach((link) => link.addEventListener('click', closeNav));

    const navMap = { overview: null, classes: '#technical-classes', teachers: '#technical-teachers', courses: '#technical-courses', td: '#technical-courses', alerts: '#technical-alerts' };
    const clearPanelClasses = () => document.body.classList.remove('tech-panel-mode', 'tech-panel-classes', 'tech-panel-teachers', 'tech-panel-courses', 'tech-panel-td', 'tech-panel-alerts');
    const setActiveNav = (panel) => document.querySelectorAll('[data-tech-nav]').forEach((link) => link.classList.toggle('is-active', link.getAttribute('data-tech-nav') === panel));
    const openDetailsFor = (selector) => {
        if (!selector) return null;
        const target = document.querySelector(selector);
        if (!target) return null;
        const details = target.tagName && target.tagName.toLowerCase() === 'details' ? target : target.closest('details');
        if (details) details.open = true;
        return target;
    };
    const showTechPanel = (panel, pushHistory = true) => {
        if (!document.querySelector('.technical-page')) return;
        const safePanel = Object.prototype.hasOwnProperty.call(navMap, panel) ? panel : 'overview';
        clearPanelClasses();
        setActiveNav(safePanel);
        if (safePanel === 'overview') {
            if (pushHistory) history.replaceState(null, '', '{{ route('technical.dashboard') }}');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        document.body.classList.add('tech-panel-mode', 'tech-panel-' + safePanel);
        const selector = navMap[safePanel];
        const target = openDetailsFor(selector);
        if (safePanel === 'td') {
            const tdTarget = document.querySelector('#technical-td');
            if (tdTarget) setTimeout(() => tdTarget.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
        } else if (target) {
            setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
        }
        if (pushHistory) history.replaceState(null, '', safePanel === 'td' ? '#technical-td' : selector);
    };
    document.querySelectorAll('[data-tech-nav]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const panel = link.getAttribute('data-tech-nav') || 'overview';
            if (!document.querySelector('.technical-page')) return;
            event.preventDefault();
            showTechPanel(panel);
            closeNav();
        });
    });
    const hashPanel = { '#technical-classes': 'classes', '#technical-teachers': 'teachers', '#technical-courses': 'courses', '#technical-td': 'td', '#technical-alerts': 'alerts' };
    if (document.querySelector('.technical-page') && hashPanel[window.location.hash]) showTechPanel(hashPanel[window.location.hash], false);
})();
</script>
@stack('scripts')
</body>
</html>
