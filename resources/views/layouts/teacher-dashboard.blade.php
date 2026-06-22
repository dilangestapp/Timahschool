@php
    $currentUser = auth()->user();
    $userName = $currentUser->full_name ?? $currentUser->name ?? $currentUser->username ?? 'Enseignant';
    $initial = mb_substr((string) $userName, 0, 1);
    $hasWeeklyProgram = \Illuminate\Support\Facades\Route::has('teacher.weekly-program.index');
    $hasAnnualProgram = \Illuminate\Support\Facades\Route::has('teacher.annual-programs.index');
    $hasTdCorrections = \Illuminate\Support\Facades\Route::has('teacher.td.corrections.index');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord enseignant') - TIMAH ACADEMY</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsible-dashboard.css') }}">
    <style>
        .resp-brand { min-width: 176px; }
        .resp-brand__title { font-size: 13px; }
        .resp-nav__item { padding-left: 10px; padding-right: 10px; }
        .resp-page { padding-top: 20px; }
        .teacher-dashboard-clean .resp-bottom-grid { align-items: start; }
        .teacher-dashboard-clean .resp-list--limited { max-height: 390px; overflow: auto; padding-right: 4px; }
        .teacher-dashboard-clean .resp-list--limited::-webkit-scrollbar { width: 6px; }
        .teacher-dashboard-clean .resp-list--limited::-webkit-scrollbar-thumb { background: #c5cbe8; border-radius: 999px; }
        .teacher-dashboard-clean .resp-list-row { min-height: 58px; }
        .teacher-dashboard-clean .resp-action-card { min-height: 46px; }
        .teacher-dashboard-clean .resp-card__title { font-size: 15px; }
        .teacher-dashboard-clean .resp-mini-label, .teacher-dashboard-clean .resp-stat-main__hint { line-height: 1.25; }
        @media (max-width: 1180px) { .resp-brand { min-width: 150px; } }
    </style>
    @stack('styles')
</head>
<body data-ui-role="technical-supervisor">
<div class="resp-shell">
    <header class="resp-navbar">
        <a href="{{ route('teacher.dashboard') }}" class="resp-brand">
            <div class="resp-brand__logo">A</div>
            <div>
                <div class="resp-brand__title">Espace<br>Enseignant</div>
                <div class="resp-brand__subtitle">TIMAH ACADEMY</div>
            </div>
        </a>
        <div class="resp-separator"></div>
        <nav class="resp-nav" aria-label="Navigation enseignant">
            <a href="{{ route('teacher.dashboard') }}" class="resp-nav__item {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}"><span class="resp-icon">▦</span> Tableau de bord</a>
            <a href="{{ route('teacher.classes.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.classes.*') ? 'is-active' : '' }}"><span class="resp-icon">▱</span> Classes</a>
            <a href="{{ route('teacher.courses.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.courses.*') ? 'is-active' : '' }}"><span class="resp-icon">▭</span> Cours</a>
            <a href="{{ route('teacher.td.sets.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.td.sets.*') ? 'is-active' : '' }}"><span class="resp-icon">☑</span> TD</a>
            @if($hasTdCorrections)
                <a href="{{ route('teacher.td.corrections.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.td.corrections.*') ? 'is-active' : '' }}"><span class="resp-icon">✓</span> Corrigés</a>
            @endif
            <a href="{{ route('teacher.td.questions.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.td.questions.*') ? 'is-active' : '' }}"><span class="resp-icon">?</span> Questions</a>
            <a href="{{ route('teacher.messages.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.messages.*') ? 'is-active' : '' }}"><span class="resp-icon">◌</span> Messages</a>
            <a href="{{ route('teacher.students.activity') }}" class="resp-nav__item {{ request()->routeIs('teacher.students.*') ? 'is-active' : '' }}"><span class="resp-icon">◉</span> Suivi</a>
            @if($hasAnnualProgram)
                <a href="{{ route('teacher.annual-programs.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.annual-programs.*') ? 'is-active' : '' }}"><span class="resp-icon">▥</span> Programme annuel</a>
            @elseif($hasWeeklyProgram)
                <a href="{{ route('teacher.weekly-program.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.weekly-program.*') ? 'is-active' : '' }}"><span class="resp-icon">▣</span> Programme</a>
            @endif
        </nav>
        <div class="resp-user">
            <div class="resp-user__avatar">{{ strtoupper($initial) }}</div>
            <div>
                <div class="resp-user__name">{{ $userName }}</div>
                <div class="resp-user__role">Compte enseignant</div>
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
        @if($errors->any())<div class="admin-alert admin-alert--error">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
@if(file_exists(public_path('assets/js/teacher-voice-cache.js')))
    <script src="{{ asset('assets/js/teacher-voice-cache.js') }}" defer></script>
@endif
@stack('scripts')
</body>
</html>
