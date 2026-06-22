@php
    $generalSettings = \App\Models\PlatformSetting::group('general');
    $platformName = $generalSettings['platform_name'] ?? 'TIMAH ACADEMY';
    $hasWeeklyProgram = \Illuminate\Support\Facades\Route::has('teacher.weekly-program.index');
    $hasAnnualProgram = \Illuminate\Support\Facades\Route::has('teacher.annual-programs.index');
    $hasTdCorrections = \Illuminate\Support\Facades\Route::has('teacher.td.corrections.index');
    $currentUser = auth()->user();
    $userName = $currentUser->full_name ?? $currentUser->name ?? $currentUser->username ?? 'Enseignant';
    $initial = mb_substr((string) $userName, 0, 1);
    $pedagogicalResponsibilities = auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities')
        ? \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')->where('user_id', auth()->id())->where('is_active', true)->get()
        : collect();
    $hasPedagogicalResponsibility = $pedagogicalResponsibilities->isNotEmpty();
    $hasSecretaryResponsibility = $pedagogicalResponsibilities->contains(function ($responsibility) {
        $title = (string) ($responsibility->role_title ?? '');
        return ($responsibility->scope_type ?? '') === 'platform' && (str_contains($title, 'Secrétaire général') || str_contains($title, 'Coordinateur général'));
    });
    $hasDivisionResponsibility = $pedagogicalResponsibilities->contains(fn($responsibility) => ($responsibility->scope_type ?? '') === 'division');
    $hasDepartmentResponsibility = $pedagogicalResponsibilities->contains(fn($responsibility) => ($responsibility->scope_type ?? '') === 'department');
    $hasReferentResponsibility = $pedagogicalResponsibilities->contains(fn($responsibility) => str_contains((string) ($responsibility->role_title ?? ''), 'Référent pédagogique'));
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace enseignant {{ $platformName }}">
    <title>@yield('title', 'Espace enseignant') - {{ $platformName }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsible-dashboard.css') }}">
    @if(file_exists(public_path('assets/css/teacher.css')))
        <style>{!! file_get_contents(public_path('assets/css/teacher.css')) !!}</style>
    @endif
    @if(file_exists(public_path('assets/css/td-file-preview.css')))
        <style>{!! file_get_contents(public_path('assets/css/td-file-preview.css')) !!}</style>
    @endif
    @if(file_exists(public_path('assets/css/teacher-subpages-clean.css')))
        <link rel="stylesheet" href="{{ asset('assets/css/teacher-subpages-clean.css') }}">
    @endif
    <style>
        .resp-brand { min-width: 176px; }
        .resp-brand__title { font-size: 13px; }
        .resp-nav__item { padding-left: 10px; padding-right: 10px; }
        .resp-page { padding-top: 0; }
        body[data-ui-role="teacher"] .teacher-subpage-main a { text-decoration: none; }
        @media (max-width: 1180px) { .resp-brand { min-width: 150px; } }
    </style>
    @stack('head')
    @stack('styles')
</head>
<body data-ui-role="teacher" data-ui-group="@yield('ui_group', 'workspace')">
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
            @if($hasSecretaryResponsibility && \Illuminate\Support\Facades\Route::has('secretariat.dashboard'))
                <a href="{{ route('secretariat.dashboard') }}" class="resp-nav__item {{ request()->routeIs('secretariat.dashboard') ? 'is-active' : '' }}"><span class="resp-icon">▣</span> Secrétariat</a>
            @endif
            @if($hasDivisionResponsibility && \Illuminate\Support\Facades\Route::has('responsible.division.dashboard'))
                <a href="{{ route('responsible.division.dashboard') }}" class="resp-nav__item {{ request()->routeIs('responsible.division.dashboard') ? 'is-active' : '' }}"><span class="resp-icon">▥</span> Type ens.</a>
            @endif
            @if($hasDepartmentResponsibility && \Illuminate\Support\Facades\Route::has('responsible.department.dashboard'))
                <a href="{{ route('responsible.department.dashboard') }}" class="resp-nav__item {{ request()->routeIs('responsible.department.dashboard') ? 'is-active' : '' }}"><span class="resp-icon">▤</span> Département</a>
            @endif
            @if($hasReferentResponsibility && \Illuminate\Support\Facades\Route::has('referent.pedagogical.dashboard'))
                <a href="{{ route('referent.pedagogical.dashboard') }}" class="resp-nav__item {{ request()->routeIs('referent.pedagogical.dashboard') ? 'is-active' : '' }}"><span class="resp-icon">⌕</span> Référent</a>
            @endif
            @if($hasPedagogicalResponsibility && !$hasSecretaryResponsibility && !$hasDivisionResponsibility && !$hasDepartmentResponsibility && !$hasReferentResponsibility && \Illuminate\Support\Facades\Route::has('supervision.tb'))
                <a href="{{ route('supervision.tb') }}" class="resp-nav__item {{ request()->routeIs('supervision.tb') ? 'is-active' : '' }}"><span class="resp-icon">◉</span> Responsable</a>
            @endif
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
            @if(\Illuminate\Support\Facades\Route::has('teacher.td.sources.index'))
                <a href="{{ route('teacher.td.sources.index') }}" class="resp-nav__item {{ request()->routeIs('teacher.td.sources.*') ? 'is-active' : '' }}"><span class="resp-icon">⇣</span> Sources TD</a>
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

    <main class="resp-page teacher-subpage-main">
        <section class="teacher-subpage-header">
            <div>
                <h1>@yield('page_title', 'Espace enseignant')</h1>
                <p>@yield('page_subtitle', 'Gestion de vos cours, TD, corrigés et questions liées à vos affectations.')</p>
            </div>
            <div class="teacher-subpage-actions">
                <a href="{{ route('teacher.dashboard') }}" class="teacher-back-link">Tableau de bord</a>
                <a href="{{ route('home') }}" class="teacher-back-link">Retour au site</a>
            </div>
        </section>

        @if(session('success'))<div class="teacher-alert teacher-alert--success">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="teacher-alert teacher-alert--success">{{ session('warning') }}</div>@endif
        @if(session('error'))<div class="teacher-alert teacher-alert--error">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="teacher-alert teacher-alert--error">{{ $errors->first() }}</div>@endif

        <section class="teacher-subpage-content">
            @yield('content')
        </section>
    </main>
</div>
@stack('scripts')
</body>
</html>
