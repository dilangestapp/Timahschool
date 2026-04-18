@extends('layouts.public')

@section('title', 'Connexion - Timah School')

@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-head">
                <div class="auth-head__logo"><img src="{{ asset('assets/brand/timah-academy-icon.svg') }}" alt="TIMAH ACADEMY" style="width:100%;height:100%;object-fit:contain;"></div>
                <h1>Connexion</h1>
                <p>Accédez à votre espace d'apprentissage.</p>
            </div>

            @if($errors->any())
                <div class="alert alert--error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="form-grid" style="margin-top:18px;">
                @csrf
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Votre nom d'utilisateur" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" placeholder="Votre mot de passe" required>
                </div>
                <div class="form-row">
                    <label class="form-check" for="remember">
                        <input id="remember" name="remember" type="checkbox">
                        <span>Se souvenir de moi</span>
                    </label>
                    <a href="{{ route('register') }}" style="font-weight:700; color:var(--primary);">Créer un compte</a>
                </div>
                <button type="submit" class="btn btn--primary btn--full">Se connecter</button>
            </form>

            <div class="auth-links">Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte gratuitement</a></div>
        </div>
    </div>
</section>
@endsection
