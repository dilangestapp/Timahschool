@extends('layouts.public')

@section('title', 'Connexion Admin - Timah School')

@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card" style="max-width:560px;">
            <div class="auth-head">
                <div class="auth-head__logo">T</div>
                <h1>TIMAH SCHOOL</h1>
                <p>Portail admin sécurisé</p>
            </div>

            <div class="alert alert--info" style="margin-bottom:18px;">
                Cette page est réservée à l'administration. N'utilisez pas la connexion publique pour ce compte.
            </div>

            @if($errors->any())
                <div class="alert alert--error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="form-grid" style="margin-top:18px;">
                @csrf

                <div class="form-group">
                    <label for="username">Nom d'utilisateur admin</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Nom d'utilisateur admin" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" placeholder="Mot de passe admin" required>
                </div>

                <div class="form-row">
                    <label class="form-check" for="remember">
                        <input id="remember" name="remember" type="checkbox" value="1">
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                <button type="submit" class="btn btn--primary btn--full">Entrer dans l'administration</button>
            </form>

            <div class="auth-links" style="margin-top:18px;">
                URL admin actuelle : /{{ $adminPath }}
            </div>
        </div>
    </div>
</section>
@endsection
