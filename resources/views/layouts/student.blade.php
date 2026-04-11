<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace Élève') - Timah School</title>
    <style>{!! file_get_contents(public_path('assets/css/app.css')) !!}</style>
</head>
<body>
<div class="student-shell">
    <aside class="student-sidebar">
        <div class="student-sidebar__top">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand__mark">T</span>
                <span class="brand__text">TIMAH SCHOOL</span>
            </a>
        </div>

        <nav class="student-nav">
            <a href="{{ route('student.dashboard') }}" class="student-link {{ request()->routeIs('student.dashboard') ? 'is-active' : '' }}"><span>🏠</span><span>Tableau de bord</span></a>
            <a href="{{ route('student.td.index') }}" class="student-link {{ request()->routeIs('student.td.*') ? 'is-active' : '' }}"><span>📝</span><span>Mes TD</span></a>
            <a href="{{ route('student.messages.index') }}" class="student-link {{ request()->routeIs('student.messages.*') ? 'is-active' : '' }}"><span>💬</span><span>Messagerie</span></a>
            <a href="{{ route('student.subscription.index') }}" class="student-link {{ request()->routeIs('student.subscription.*') ? 'is-active' : '' }}"><span>💳</span><span>Abonnement</span></a>
        </nav>

        <div class="student-sidebar__bottom" style="margin-top:auto;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="student-link" style="color:#dc2626; background:#fff1f2;">🚪 Déconnexion</button>
            </form>
        </div>
    </aside>

    <div class="student-main">
        <div class="student-topbar">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand__mark">T</span>
                <span class="brand__text">TIMAH SCHOOL</span>
            </a>
            <a href="{{ route('student.subscription.index') }}" class="btn btn--ghost">Abonnement</a>
        </div>

        <main class="student-content">
            @if(session('success'))<div class="alert" style="background:#ecfdf3; border:1px solid #bbf7d0; color:#166534; margin-bottom:18px;">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert--error" style="margin-bottom:18px;">{{ session('error') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
