@extends('layouts.public')

@section('title', 'Création du compte administrateur - Timah School')

@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card" style="max-width: 760px;">
            <div class="auth-head">
                <div class="auth-head__logo">T</div>
                <h1>Création du compte administrateur</h1>
                <p>Cette page ne sert qu'une seule fois pour créer le premier compte admin.</p>
            </div>

            @if(session('status'))
                <div class="alert alert--success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert--error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.setup.store') }}" method="POST" class="form-grid" style="margin-top:18px;">
                @csrf

                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Ex: Admin principal" required>
                </div>

                <div class="form-group">
                    <label for="username">Nom d'utilisateur admin</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Ex: adminprincipal" required>
                </div>

                <div class="form-group">
                    <label for="email">Email admin</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Ex: admin@timahschool.local" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe admin</label>
                    <input id="password" name="password" type="password" placeholder="Choisissez un mot de passe" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirmez le mot de passe" required>
                </div>

                <button type="submit" class="btn btn--primary btn--full">Créer le compte administrateur</button>
            </form>

            <div class="auth-links" style="margin-top:18px;">
                Quand le compte admin est créé, cette page se ferme automatiquement et tu utiliseras ensuite
                <strong>/{{ $adminPath }}/login</strong>.
            </div>
        </div>
    </div>
</section>
@endsection
