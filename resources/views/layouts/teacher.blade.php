<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Espace enseignant TIMAH SCHOOL">
    <title>@yield('title', 'Espace enseignant') - TIMAH SCHOOL</title>
    <style>{!! file_get_contents(public_path('assets/css/teacher.css')) !!}</style>
</head>
<body>
<div class="teacher-shell">
    <aside class="teacher-sidebar">
        <div class="teacher-sidebar__top">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-brand">
                <span class="teacher-brand__mark">T</span>
                <span>
                    <strong>TIMAH SCHOOL</strong>
                    <small>Espace enseignant</small>
                </span>
            </a>
        </div>

        <nav class="teacher-nav">
            <a href="{{ route('teacher.dashboard') }}" class="teacher-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}">🏠 Tableau de bord</a>
            <a href="{{ route('teacher.classes.index') }}" class="teacher-link {{ request()->routeIs('teacher.classes.*') ? 'is-active' : '' }}">🏫 Mes classes</a>
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.sets.*') ? 'is-active' : '' }}">📝 Mes TD</a>
            <a href="{{ route('teacher.td.questions.index') }}" class="teacher-link {{ request()->routeIs('teacher.td.questions.*') ? 'is-active' : '' }}">💬 Questions TD</a>
            <a href="{{ route('teacher.messages.index') }}" class="teacher-link {{ request()->routeIs('teacher.messages.*') ? 'is-active' : '' }}">✉️ Messagerie</a>
        </nav>

        <div class="teacher-sidebar__bottom">
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
        </header>

        <main class="teacher-content">
            @if(session('success'))<div class="teacher-alert teacher-alert--success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="teacher-alert teacher-alert--error">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="teacher-alert teacher-alert--error">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
