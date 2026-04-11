<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Timah School - Plateforme éducative moderne pour apprendre, réviser et réussir.">
    <title>@yield('title', 'Timah School')</title>
    <style>{!! file_get_contents(public_path('assets/css/app.css')) !!}</style>
</head>
<body>
<div class="site-shell">
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand__mark">T</span>
                <span class="brand__text">TIMAH SCHOOL</span>
            </a>

            <nav class="nav-links" aria-label="Navigation principale">
                <a href="{{ route('home') }}">Accueil</a>
                <a href="{{ route('home') }}#classes">Cours</a>
                <a href="{{ route('home') }}#classes">Quiz</a>
                <a href="{{ route('home') }}#pricing">Tarifs</a>
                <a href="{{ route('login') }}">Connexion</a>
            </nav>

            <div class="header-actions">
                @auth
                    <a href="{{ route('student.dashboard') }}" class="btn btn--ghost">Mon espace</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn--ghost">Connexion</a>
                    <a href="{{ route('register') }}" class="btn btn--primary">S'inscrire</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="{{ route('home') }}" class="brand" style="color:#fff; margin-bottom:14px;">
                        <span class="brand__mark">T</span>
                        <span class="brand__text">TIMAH SCHOOL</span>
                    </a>
                    <p class="footer-text">Apprendre aujourd'hui, réussir demain. Une plateforme pensée pour les élèves qui veulent progresser sérieusement.</p>
                </div>
                <div>
                    <h3 class="footer-title">Liens rapides</h3>
                    <ul class="footer-list">
                        <li><a href="{{ route('home') }}">Accueil</a></li>
                        <li><a href="{{ route('home') }}#classes">Cours</a></li>
                        <li><a href="{{ route('home') }}#pricing">Tarifs</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Ressources</h3>
                    <ul class="footer-list">
                        <li><a href="{{ route('login') }}">Connexion</a></li>
                        <li><a href="{{ route('register') }}">Inscription</a></li>
                        <li><a href="{{ route('student.subscription.index') }}">Abonnement</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Suivez-nous</h3>
                    <ul class="footer-list">
                        <li>Facebook</li>
                        <li>Twitter</li>
                        <li>Instagram</li>
                        <li>YouTube</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">© {{ date('Y') }} Timah School. Tous droits réservés.</div>
        </div>
    </footer>
</div>
</body>
</html>
