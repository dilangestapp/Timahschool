<!DOCTYPE html>
@php
    $footerConfig = \App\Models\HomepageSetting::defaults()['footer'] ?? [];
    if (\Illuminate\Support\Facades\Schema::hasTable('homepage_settings')) {
        $homepageSetting = \App\Models\HomepageSetting::query()->where('key', 'homepage')->first();
        $footerConfig = array_merge(
            $footerConfig,
            ($homepageSetting->value['footer'] ?? [])
        );
    }
@endphp
<html lang="fr" data-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TIMAH ACADEMY - Plateforme éducative premium pour apprendre, progresser et réussir.">
    <title>@yield('title', 'TIMAH ACADEMY')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <style>{!! file_get_contents(public_path('assets/css/app.css')) !!}</style>
</head>
<body>
<div class="site-shell">
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-light.svg') }}" alt="TIMAH ACADEMY" style="height:36px; width:auto;">
            </a>

            <nav class="nav-links" aria-label="Navigation principale">
                <a href="{{ route('home') }}">Accueil</a>
                <a href="{{ route('home') }}#classes">Cours</a>
                <a href="{{ route('home') }}#classes">Quiz</a>
                <a href="{{ route('home') }}#pricing">Tarifs</a>
                <a href="{{ route('home') }}#help-support">Aide</a>
                <a href="{{ route('home') }}#help-support">Contact</a>
            </nav>

            <div class="header-actions">
                <button type="button" class="theme-toggle" data-theme-toggle aria-label="Basculer le thème">🌗 Thème</button>
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
                        <img src="{{ asset('assets/brand/timah-academy-logo-horizontal-dark.svg') }}" alt="TIMAH ACADEMY" style="height:36px; width:auto;">
                    </a>
                    <p class="footer-text">{{ $footerConfig['about'] ?? "Apprendre aujourd'hui, réussir demain. Une plateforme pensée pour les élèves qui veulent progresser sérieusement." }}</p>
                </div>
                <div>
                    <h3 class="footer-title">Plateforme</h3>
                    <ul class="footer-list">
                        <li><a href="{{ route('home') }}">Accueil</a></li>
                        <li><a href="{{ route('home') }}#classes">Classes</a></li>
                        <li><a href="{{ route('home') }}#pricing">Tarifs</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Accès</h3>
                    <ul class="footer-list">
                        <li><a href="{{ route('login') }}">Connexion</a></li>
                        <li><a href="{{ route('register') }}">Inscription</a></li>
                        <li><a href="{{ route('student.subscription.index') }}">Abonnement</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Support</h3>
                    <h3 class="footer-title">TIMAH ACADEMY</h3>
                    <ul class="footer-list">
                        <li><a href="{{ route('home') }}#mini-faq">FAQ</a></li>
                        <li><a href="{{ route('home') }}#help-support">Centre d'aide</a></li>
                        <li><a href="{{ route('home') }}#help-support">Assistance</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Entreprise</h3>
                    <ul class="footer-list">
                        @foreach(($footerConfig['company_links'] ?? []) as $link)
                            <li><a href="{{ $link['href'] ?? '#' }}">{{ $link['label'] ?? 'Lien' }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">© {{ date('Y') }} TIMAH ACADEMY. Tous droits réservés.</div>
        </div>
    </footer>
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
        if ((root.getAttribute('data-theme') || 'auto') === 'auto') {
            updateToggleLabels();
        }
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
</body>
</html>
