@php
    $currentUser = auth()->user();
    $userName = $currentUser->full_name ?? $currentUser->name ?? $currentUser->username ?? 'Responsable';
    $initial = mb_substr((string) $userName, 0, 1);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord technique') - TIMAH ACADEMY</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsible-dashboard.css') }}">
    @stack('styles')
</head>
<body data-ui-role="technical-supervisor">
<div class="resp-shell">
    <header class="resp-navbar">
        <a href="{{ route('technical.dashboard') }}" class="resp-brand" data-resp-nav="overview">
            <div class="resp-brand__logo">A</div>
            <div>
                <div class="resp-brand__title">Section<br>Technique</div>
                <div class="resp-brand__subtitle">Espace admin</div>
            </div>
        </a>
        <div class="resp-separator"></div>
        <nav class="resp-nav" aria-label="Navigation responsable technique">
            <a href="{{ route('technical.dashboard') }}" class="resp-nav__item is-active" data-resp-nav="overview"><span class="resp-icon">▦</span> Tableau de bord</a>
            <a href="#technical-classes" class="resp-nav__item" data-resp-nav="classes"><span class="resp-icon">▱</span> Classes</a>
            <a href="#technical-teachers" class="resp-nav__item" data-resp-nav="teachers"><span class="resp-icon">♙</span> Enseignants</a>
            <a href="#technical-courses" class="resp-nav__item" data-resp-nav="courses"><span class="resp-icon">▭</span> Cours</a>
            <a href="#technical-td" class="resp-nav__item" data-resp-nav="td"><span class="resp-icon">☑</span> TD / Contrôles</a>
            <a href="#technical-alerts" class="resp-nav__item" data-resp-nav="alerts"><span class="resp-icon">♢</span> Alertes <span class="resp-badge-alert">@yield('alert_count', '0')</span></a>
        </nav>
        <div class="resp-user">
            <div class="resp-user__avatar">{{ strtoupper($initial) }}</div>
            <div>
                <div class="resp-user__name">{{ $userName }}</div>
                <div class="resp-user__role">Responsable technique</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="resp-logout" title="Déconnexion">↪</button>
            </form>
        </div>
    </header>

    <main class="resp-page">
        @if(session('success'))<div class="admin-alert admin-alert--success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="admin-alert admin-alert--warning">{{ session('warning') }}</div>@endif
        @if(session('error'))<div class="admin-alert admin-alert--error">{{ session('error') }}</div>@endif
        @yield('content')
    </main>
</div>

<script>
(() => {
    const navMap = {overview: null, classes: '#technical-classes', teachers: '#technical-teachers', courses: '#technical-courses', td: '#technical-td', alerts: '#technical-alerts'};
    const setActiveNav = (panel) => document.querySelectorAll('[data-resp-nav]').forEach((link) => link.classList.toggle('is-active', link.getAttribute('data-resp-nav') === panel));
    const showPanel = (panel, pushHistory = true) => {
        const safePanel = Object.prototype.hasOwnProperty.call(navMap, panel) ? panel : 'overview';
        document.querySelectorAll('[data-resp-panel]').forEach((item) => item.classList.remove('is-active'));
        setActiveNav(safePanel);
        if (safePanel === 'overview') {
            document.body.classList.remove('resp-section-mode');
            if (pushHistory) history.replaceState(null, '', window.location.pathname);
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        document.body.classList.add('resp-section-mode');
        const selector = navMap[safePanel];
        const target = selector ? document.querySelector(selector) : null;
        if (target) {
            target.classList.add('is-active');
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (pushHistory && selector) history.replaceState(null, '', selector);
    };
    document.querySelectorAll('[data-resp-nav]').forEach((link) => link.addEventListener('click', (event) => {
        if (!document.querySelector('.resp-dashboard')) return;
        event.preventDefault();
        showPanel(link.getAttribute('data-resp-nav') || 'overview');
    }));
    const hashPanel = {'#technical-classes': 'classes', '#technical-teachers': 'teachers', '#technical-courses': 'courses', '#technical-td': 'td', '#technical-alerts': 'alerts'};
    if (document.querySelector('.resp-dashboard') && hashPanel[window.location.hash]) showPanel(hashPanel[window.location.hash], false);
})();
</script>
@stack('scripts')
</body>
</html>
