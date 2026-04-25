<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TIMAH ACADEMY - Portail administrateur">
    <title>@yield('title', 'TIMAH ACADEMY - Admin')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/brand/timah-academy-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @if(file_exists(public_path('assets/css/theme-stability.css')))
        <link rel="stylesheet" href="{{ asset('assets/css/theme-stability.css') }}">
    @endif
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('timah-theme');
                document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>
<body>
<main>
    @yield('content')
</main>
<script>
(() => {
    const root = document.documentElement;
    const storageKey = 'timah-theme';
    const getStoredTheme = () => localStorage.getItem(storageKey);
    const applyTheme = (theme) => root.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
    const nextTheme = () => root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    const updateToggleLabels = () => {
        const active = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.textContent = active === 'dark' ? '☀️ Clair' : '🌙 Sombre';
        });
    };

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
})();
</script>
</body>
</html>
