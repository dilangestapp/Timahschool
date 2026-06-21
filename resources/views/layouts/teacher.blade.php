@php
    $generalSettings = \App\Models\PlatformSetting::group('general');
    $platformName = $generalSettings['platform_name'] ?? 'TIMAH ACADEMY';
    $platformSlogan = $generalSettings['platform_slogan'] ?? 'Plateforme éducative moderne et premium';
    $platformLogo = \App\Models\PlatformSetting::logoUrl($generalSettings['logo_path'] ?? null);
    $hasWeeklyProgram = \Illuminate\Support\Facades\Route::has('teacher.weekly-program.index');
    $pedagogicalResponsibilities = auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities')
        ? \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->get()
        : collect();
    $hasPedagogicalResponsibility = $pedagogicalResponsibilities->isNotEmpty();
    $hasSecretaryResponsibility = $pedagogicalResponsibilities->contains(function ($responsibility) {
        $title = (string) ($responsibility->role_title ?? '');
        return ($responsibility->scope_type ?? '') === 'platform'
            && (str_contains($title, 'Secrétaire général') || str_contains($title, 'Coordinateur général'));
    });
    $hasDivisionResponsibility = $pedagogicalResponsibilities->contains(fn($responsibility) => ($responsibility->scope_type ?? '') === 'division');
    $hasDepartmentResponsibility = $pedagogicalResponsibilities->contains(fn($responsibility) => ($responsibility->scope_type ?? '') === 'department');
    $hasReferentResponsibility = $pedagogicalResponsibilities->contains(function ($responsibility) {
        return str_contains((string) ($responsibility->role_title ?? ''), 'Référent pédagogique');
    });
@endphp
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace enseignant {{ $platformName }}">
    <title>@yield('title', 'Espace enseignant') - {{ $platformName }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <style>{!! file_get_contents(public_path('assets/css/teacher.css')) !!}</style>
    <style>{!! file_get_contents(public_path('assets/css/ui-groups.css')) !!}</style>
    @if(file_exists(public_path('assets/css/theme-stability.css')))
        <style>{!! file_get_contents(public_path('assets/css/theme-stability.css')) !!}</style>
    @endif
    @if(file_exists(public_path('assets/css/td-file-preview.css')))
        <style>{!! file_get_contents(public_path('assets/css/td-file-preview.css')) !!}</style>
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/timah-mobile-polish.css') }}">
    <style>
        :root { color-scheme: light; }
        html[data-theme='dark'] { color-scheme: light; }
        .teacher-layout-body { min-height: 100vh; }
        .teacher-drawer-toggle {
            display: none; width: 44px; height: 44px; border-radius: 14px;
            border: 1px solid var(--teacher-border, #dbe6f3); background: rgba(255,255,255,.72);
            color: inherit; align-items: center; justify-content: center; font-size: 1.1rem;
            cursor: pointer; flex: 0 0 44px;
        }
        .teacher-drawer-backdrop { display: none; }
        .teacher-topbar__left { display: flex; align-items: flex-start; gap: 12px; min-width: 0; }
        .teacher-topbar__left > div:last-child { min-width: 0; }
        .teacher-topbar__actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .teacher-nav { overflow-y: auto; padding-bottom: 18px; }
        .teacher-link { gap: 10px; }
        .teacher-link__icon { width: 22px; flex: 0 0 22px; text-align: center; opacity: .95; }
        .teacher-link__text { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .teacher-sidebar__bottom { border-top: 1px solid rgba(255,255,255,.08); }
        @media (prefers-color-scheme: dark) { :root, html, body, input, textarea, select, button { color-scheme: light; } }
        @media (max-width: 1100px) {
            .teacher-shell { display: block !important; }
            .teacher-sidebar {
                position: fixed !important; top: 0; left: 0; bottom: 0; width: min(86vw, 330px) !important;
                height: 100vh !important; z-index: 1200; transform: translateX(-100%);
                transition: transform .25s ease; box-shadow: 0 20px 60px rgba(15, 23, 42, .32);
            }
            body.teacher-drawer-open .teacher-sidebar { transform: translateX(0); }
            .teacher-drawer-backdrop { position: fixed; inset: 0; z-index: 1100; background: rgba(15, 23, 42, .48); }
            body.teacher-drawer-open .teacher-drawer-backdrop { display: block; }
            .teacher-main { width: 100%; min-width: 0; }
            .teacher-drawer-toggle { display: inline-flex; }
            .teacher-topbar {
                position: sticky; top: 0; z-index: 100; background: rgba(244,248,253,.92);
                backdrop-filter: blur(10px); padding: 14px 16px 0 !important; margin-bottom: 0;
            }
            html[data-theme='dark'] .teacher-topbar { background: rgba(244,248,253,.92); }
            .teacher-content { padding: 16px !important; }
            .teacher-userbox { padding: 10px 12px !important; border-radius: 14px !important; }
            .teacher-topbar__actions { width: 100%; justify-content: space-between; }
            .teacher-topbar .theme-toggle { min-height: 42px; }
        }
        @media (max-width: 640px) {
            .teacher-topbar { gap: 12px; }
            .teacher-topbar__left h1 { font-size: 1.3rem !important; }
            .teacher-topbar__left p { font-size: .86rem !important; line-height: 1.45 !important; }
            .teacher-topbar__actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; align-items: stretch; }
            .teacher-topbar__actions .teacher-userbox, .teacher-topbar__actions .theme-toggle { width: 100%; }
        }
    </style>
    @stack('styles')
</head>
<body class="teacher-layout-body" data-ui-group="@yield('ui_group', 'workspace')" data-ui-role="teacher">
<div class="teacher-drawer-backdrop" data-teacher-drawer-close></div>

<div class="teacher-shell">
    <aside class="teacher-sidebar" id="teacherSidebar">
        <div class="teacher-sidebar__top">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-brand">
                @if($platformLogo)
                    <img src="{{ $platformLogo }}" alt="{{ $platformName }}" style="height:44px; width:auto; display:block;">
                @else
                    <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-light.svg') }}" alt="{{ $platformName }}" style="height:34px; width:auto; display:block;">
                @endif
            </a>
        </div>

        <nav class="teacher-nav">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}"><span class="teacher-link__icon">🏠</span><span class="teacher-link__text">Tableau de bord</span></a>
            @if($hasSecretaryResponsibility && \Illuminate\Support\Facades\Route::has('secretariat.dashboard'))
                <a href="{{ route('secretariat.dashboard') }}" class="teacher-link {{ request()->routeIs('secretariat.dashboard') ? 'is-active' : '' }}"><span class="teacher-link__icon">🛡️</span><span class="teacher-link__text">TB Secrétaire</span></a>
            @endif
            @if($hasDivisionResponsibility && \Illuminate\Support\Facades\Route::has('responsible.division.dashboard'))
                <a href="{{ route('responsible.division.dashboard') }}" class="teacher-link {{ request()->routeIs('responsible.division.dashboard') ? 'is-active' : '' }}"><span class="teacher-link__icon">🏛️</span><span class="teacher-link__text">TB type enseignement</span></a>
            @endif
            @if($hasDepartmentResponsibility && \Illuminate\Support\Facades\Route::has('responsible.department.dashboard'))
                <a href="{{ route('responsible.department.dashboard') }}" class="teacher-link {{ request()->routeIs('responsible.department.dashboard') ? 'is-active' : '' }}"><span class="teacher-link__icon">🧩</span><span class="teacher-link__text">TB département</span></a>
            @endif
            @if($hasReferentResponsibility && \Illuminate\Support\Facades\Route::has('referent.pedagogical.dashboard'))
                <a href="{{ route('referent.pedagogical.dashboard') }}" class="teacher-link {{ request()->routeIs('referent.pedagogical.dashboard') ? 'is-active' : '' }}"><span class="teacher-link__icon">🔎</span><span class="teacher-link__text">TB référent</span></a>
            @endif
            @if($hasPedagogicalResponsibility && !$hasSecretaryResponsibility && !$hasDivisionResponsibility && !$hasDepartmentResponsibility && !$hasReferentResponsibility && \Illuminate\Support\Facades\Route::has('supervision.tb'))
                <a href="{{ route('supervision.tb') }}" class="teacher-link {{ request()->routeIs('supervision.tb') ? 'is-active' : '' }}"><span class="teacher-link__icon">🧭</span><span class="teacher-link__text">TB responsable</span></a>
            @endif
            <a href="{{ route('teacher.classes.index') }}" class="teacher-link {{ request()->routeIs('teacher.classes.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">🏫</span><span class="teacher-link__text">Mes classes</span></a>
            <a href="{{ route('teacher.courses.index') }}" class="teacher-link {{ request()->routeIs('teacher.courses.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">📚</span><span class="teacher-link__text">Mes cours</span></a>
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.sets.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">📝</span><span class="teacher-link__text">Mes TD</span></a>
            <a href="{{ route('teacher.td.questions.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.questions.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">❓</span><span class="teacher-link__text">Questions élèves</span></a>
            <a href="{{ route('teacher.messages.index') }}" class="teacher-link {{ request()->routeIs('teacher.messages.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">💬</span><span class="teacher-link__text">Messagerie</span></a>
            <a href="{{ route('teacher.students.activity') }}" class="teacher-link {{ request()->routeIs('teacher.students.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">📊</span><span class="teacher-link__text">Suivi élèves</span></a>
            @if($hasWeeklyProgram)
                <a href="{{ route('teacher.weekly-program.index') }}" class="teacher-link {{ request()->routeIs('teacher.weekly-program.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">🗓️</span><span class="teacher-link__text">Programme</span></a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('teacher.td.sources.index'))
                <a href="{{ route('teacher.td.sources.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.sources.*') ? 'is-active' : '' }}"><span class="teacher-link__icon">📥</span><span class="teacher-link__text">Sources TD</span></a>
            @endif
        </nav>

        <div class="teacher-sidebar__bottom">
            <a href="{{ route('home') }}" class="teacher-link teacher-link--bottom"><span class="teacher-link__icon">←</span><span class="teacher-link__text">Retour au site</span></a>
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
                    <p>@yield('page_subtitle', 'Gestion de vos cours, TD, corrigés et questions liées à vos affectations.')</p>
                </div>
            </div>

            <div class="teacher-topbar__actions">
                <div class="teacher-userbox">
                    <strong>{{ auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username }}</strong>
                    <small>Compte enseignant</small>
                </div>
                <button type="button" class="teacher-btn teacher-btn--ghost theme-toggle" data-theme-toggle>🌙 Thème</button>
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
    const storageKey = 'timah-teacher-theme';
    const getStoredTheme = () => localStorage.getItem(storageKey);
    const applyTheme = (theme) => root.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
    const nextTheme = () => root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    const updateToggleLabels = () => {
        const active = root.getAttribute('data-theme') || 'light';
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.textContent = active === 'dark' ? '☀️ Clair' : '🌙 Sombre';
        });
    };
    const closeDrawer = () => body.classList.remove('teacher-drawer-open');
    const openDrawer = () => body.classList.add('teacher-drawer-open');
    applyTheme(getStoredTheme() || 'light');
    updateToggleLabels();
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = nextTheme();
            localStorage.setItem(storageKey, value);
            applyTheme(value);
            updateToggleLabels();
        });
    });
    document.querySelectorAll('[data-teacher-drawer-open]').forEach((button) => button.addEventListener('click', openDrawer));
    document.querySelectorAll('[data-teacher-drawer-close]').forEach((button) => button.addEventListener('click', closeDrawer));
    document.querySelectorAll('.teacher-sidebar .teacher-link').forEach((link) => link.addEventListener('click', closeDrawer));
    window.addEventListener('resize', () => { if (window.innerWidth > 1100) closeDrawer(); });
})()
</script>
@if(file_exists(public_path('assets/js/td-file-preview.js')))
    <script>{!! file_get_contents(public_path('assets/js/td-file-preview.js')) !!}</script>
@endif
@stack('scripts')
</body>
</html>
