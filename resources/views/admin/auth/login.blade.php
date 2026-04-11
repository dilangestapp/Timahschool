<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion administrateur TIMAH SCHOOL">
    <title>Connexion Admin - TIMAH SCHOOL</title>
    <style>{!! file_get_contents(public_path('assets/css/admin.css')) !!}</style>
</head>
<body class="admin-auth-body">
<div class="admin-auth-wrap">
    <div class="admin-auth-card">
        <div class="admin-auth-brand">
            <span class="admin-brand__mark">T</span>
            <div>
                <h1>TIMAH SCHOOL</h1>
                <p>Portail admin sécurisé</p>
            </div>
        </div>

        <div class="admin-auth-note">
            Cette page est réservée à l'administration. N'utilisez pas la connexion publique pour ce compte.
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert--error" style="margin-bottom: 16px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="admin-form">
            @csrf

            <div class="admin-form-group">
                <label for="username">Nom d'utilisateur admin</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="admin-form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="admin-form-group">
                <label for="access_code">Code d'accès admin</label>
                <input type="password" id="access_code" name="access_code" required>
                <small>Ce code est séparé du mot de passe du compte et vient du fichier .env.</small>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="remember" value="1">
                <span>Se souvenir de moi</span>
            </label>

            <button type="submit" class="admin-btn admin-btn--primary">Entrer dans l'administration</button>
        </form>

        <div class="admin-auth-foot">
            <div>URL admin actuelle : /{{ $adminPath }}/login</div>
            <div>Cette page ne doit pas être liée au menu public.</div>
        </div>
    </div>
</div>
</body>
</html>
