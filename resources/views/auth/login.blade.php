@extends('layouts.public')

@section('title', 'Connexion - TIMAH ACADEMY')
@section('meta_description', 'Connectez-vous à TIMAH ACADEMY pour accéder à vos cours, quiz, TD et à votre suivi de progression.')

@push('styles')
<style>
    .login-page {
        position: relative;
        min-height: calc(100vh - 90px);
        padding: 28px 0 52px;
        overflow: hidden;
    }

    .login-page::before,
    .login-page::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        z-index: 0;
        filter: blur(10px);
    }

    .login-page::before {
        width: 360px;
        height: 360px;
        top: -120px;
        right: -140px;
        background: radial-gradient(circle, rgba(29, 109, 255, 0.16), transparent 70%);
    }

    .login-page::after {
        width: 280px;
        height: 280px;
        left: -110px;
        bottom: -80px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.12), transparent 72%);
    }

    .login-page .container {
        position: relative;
        z-index: 1;
    }

    .login-wrap {
        display: grid;
        grid-template-columns: 1.02fr .98fr;
        gap: 24px;
        align-items: stretch;
    }

    .login-showcase,
    .login-card {
        border: 1px solid var(--line);
        border-radius: 30px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .login-showcase {
        padding: 30px;
        color: #eaf1ff;
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.22), transparent 30%),
            linear-gradient(180deg, #0d1a36, #081224);
        display: grid;
        gap: 20px;
        align-content: start;
    }

    .login-card {
        padding: 30px;
        background:
            radial-gradient(circle at top right, rgba(29, 109, 255, 0.12), transparent 26%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .login-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        width: fit-content;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.06);
        font-size: .84rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .login-title {
        margin: 0;
        font-size: clamp(2rem, 3.4vw, 3.4rem);
        line-height: 1.02;
        letter-spacing: -0.05em;
        max-width: 10ch;
    }

    .login-title .accent {
        display: block;
        color: #8ec0ff;
    }

    .login-text {
        margin: 0;
        color: rgba(234, 241, 255, 0.78);
        font-size: 1rem;
        line-height: 1.75;
        max-width: 58ch;
    }

    .login-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .login-feature {
        padding: 16px;
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
    }

    .login-feature strong {
        display: block;
        margin-bottom: 8px;
        font-size: 1rem;
        letter-spacing: -0.02em;
        color: #fff;
    }

    .login-feature p {
        margin: 0;
        color: rgba(234, 241, 255, 0.75);
        font-size: .94rem;
        line-height: 1.65;
    }

    .login-mini-stats {
        display: grid;
        gap: 12px;
    }

    .login-mini-stat {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
    }

    .login-mini-stat strong {
        font-size: 1rem;
        color: #fff;
    }

    .login-mini-stat span {
        color: rgba(234, 241, 255, 0.72);
    }

    .login-header {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
    }

    .login-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        width: fit-content;
        min-width: 0;
    }

    .login-brand__mark {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        box-shadow: 0 16px 30px rgba(29, 109, 255, 0.24);
        color: #fff;
        font-weight: 900;
        font-size: 1.1rem;
        letter-spacing: -0.03em;
        flex: 0 0 54px;
    }

    .login-brand__text {
        display: grid;
        gap: 3px;
        min-width: 0;
    }

    .login-brand__text strong {
        font-size: 1.05rem;
        letter-spacing: -0.03em;
        line-height: 1.2;
    }

    .login-brand__text span {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .login-card h1 {
        margin: 0;
        font-size: clamp(1.9rem, 3vw, 2.6rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
    }

    .login-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .login-alert {
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(239, 68, 68, 0.18);
        background: rgba(239, 68, 68, 0.08);
        color: #b91c1c;
        font-size: .94rem;
        font-weight: 700;
    }

    html[data-theme='dark'] .login-alert {
        color: #fecaca;
        border-color: rgba(248, 113, 113, 0.24);
        background: rgba(127, 29, 29, 0.24);
    }

    .login-form {
        margin-top: 20px;
        display: grid;
        gap: 16px;
    }

    .field {
        display: grid;
        gap: 8px;
    }

    .field label {
        font-size: .92rem;
        font-weight: 800;
        color: var(--text);
    }

    .input-wrap {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--muted);
        pointer-events: none;
    }

    .form-input {
        width: 100%;
        height: 54px;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--text);
        padding: 0 16px 0 44px;
        font-size: .95rem;
        outline: none;
        transition: .2s ease;
        box-sizing: border-box;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(29, 109, 255, 0.12);
    }

    .login-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .login-check {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--muted);
        font-size: .9rem;
        font-weight: 700;
    }

    .login-check input {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        flex: 0 0 16px;
    }

    .login-inline-link,
    .login-alt a,
    .login-forgot {
        color: var(--primary);
        font-weight: 800;
    }

    .login-actions {
        display: grid;
        gap: 12px;
        margin-top: 4px;
    }

    .login-note {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.06);
        color: var(--muted);
        font-size: .92rem;
        line-height: 1.65;
    }

    .login-links-stack {
        display: grid;
        gap: 10px;
    }

    .login-alt {
        margin-top: 18px;
        text-align: center;
        color: var(--muted);
        font-size: .94rem;
        line-height: 1.7;
    }

    @media (max-width: 1180px) {
        .login-wrap {
            grid-template-columns: 1fr;
        }

        .login-card {
            order: 1;
        }

        .login-showcase {
            order: 2;
        }
    }

    @media (max-width: 720px) {
        .login-page {
            padding: 22px 0 42px;
        }

        .login-showcase,
        .login-card {
            padding: 20px 18px;
            border-radius: 24px;
        }

        .login-feature-grid {
            grid-template-columns: 1fr;
        }

        .login-title {
            max-width: none;
            font-size: clamp(2rem, 11vw, 3rem);
        }

        .login-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .login-brand {
            align-items: flex-start;
        }

        .login-brand__text span {
            white-space: normal;
        }

        .login-check {
            width: 100%;
        }

        .login-inline-link,
        .login-forgot {
            display: inline-block;
        }
    }

    @media (max-width: 480px) {
        .login-page {
            padding: 18px 0 36px;
        }

        .login-showcase,
        .login-card {
            padding: 18px 14px;
            border-radius: 20px;
        }

        .login-badge {
            min-height: 34px;
            padding: 0 12px;
            font-size: .78rem;
            line-height: 1.2;
        }

        .login-card h1 {
            font-size: 1.75rem;
        }

        .login-title {
            font-size: clamp(1.85rem, 10vw, 2.5rem);
            line-height: 1.04;
        }

        .login-text,
        .login-note,
        .login-card p,
        .login-alt {
            font-size: .9rem;
        }

        .login-feature,
        .login-mini-stat,
        .login-note,
        .login-alert {
            padding: 14px;
            border-radius: 16px;
        }

        .form-input {
            height: 52px;
            font-size: .93rem;
        }

        .login-brand__mark {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            flex-basis: 48px;
            font-size: 1rem;
        }

        .login-brand__text strong {
            font-size: .98rem;
        }

        .login-brand__text span {
            font-size: .8rem;
        }
    }

    @media (max-width: 360px) {
        .login-showcase,
        .login-card {
            padding: 16px 12px;
        }

        .login-title {
            font-size: 1.7rem;
        }

        .form-input {
            padding-left: 42px;
        }
    }
</style>
@endpush

@section('content')
<section class="login-page">
    <div class="container">
        <div class="login-wrap">
            <div class="login-showcase reveal">
                <span class="login-badge">🔐 Accès sécurisé à votre espace</span>

                <h2 class="login-title">
                    Connectez-vous
                    <span class="accent">et reprenez votre progression.</span>
                </h2>

                <p class="login-text">
                    Retrouvez vos cours, vos quiz, vos TD corrigés et votre suivi personnel dans une interface
                    claire, moderne et pensée pour garder un vrai rythme d’apprentissage.
                </p>

                <div class="login-feature-grid">
                    <article class="login-feature">
                        <strong>Continuer sans perdre le fil</strong>
                        <p>Accédez directement à votre classe, à vos contenus et à votre progression.</p>
                    </article>

                    <article class="login-feature">
                        <strong>Réviser avec méthode</strong>
                        <p>Cours structurés, quiz ciblés et TD pour mieux vous organiser.</p>
                    </article>

                    <article class="login-feature">
                        <strong>Suivi plus visible</strong>
                        <p>Repérez rapidement vos avancées, vos résultats et les points à renforcer.</p>
                    </article>

                    <article class="login-feature">
                        <strong>Plateforme sérieuse</strong>
                        <p>Un espace pensé pour les élèves, les parents et les enseignants.</p>
                    </article>
                </div>

                <div class="login-mini-stats">
                    <div class="login-mini-stat">
                        <span>Accès à vos contenus</span>
                        <strong>Immédiat</strong>
                    </div>

                    <div class="login-mini-stat">
                        <span>Suivi de progression</span>
                        <strong>Visible</strong>
                    </div>

                    <div class="login-mini-stat">
                        <span>Expérience</span>
                        <strong>Claire et moderne</strong>
                    </div>
                </div>
            </div>

            <div class="login-card reveal">
                <div class="login-header">
                    <div class="login-brand">
                        <span class="login-brand__mark">TA</span>
                        <span class="login-brand__text">
                            <strong>TIMAH ACADEMY</strong>
                            <span>Connexion à votre espace</span>
                        </span>
                    </div>

                    <h1>Connexion</h1>
                    <p>Entrez vos informations pour accéder à votre espace d’apprentissage.</p>
                </div>

                @if ($errors->any())
                    <div class="login-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="login-form">
                    @csrf

                    <div class="field">
                        <label for="username">Nom d'utilisateur</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21a8 8 0 0 0-16 0"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                class="form-input"
                                value="{{ old('username') }}"
                                placeholder="Votre nom d'utilisateur"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-input"
                                placeholder="Votre mot de passe"
                                required
                            >
                        </div>
                    </div>

                    <div class="login-links-stack">
                        <div class="login-row">
                            <label class="login-check" for="remember">
                                <input id="remember" name="remember" type="checkbox">
                                <span>Se souvenir de moi</span>
                            </label>

                            <a href="{{ route('register') }}" class="login-inline-link">Créer un compte</a>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="login-forgot">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="btn btn--primary btn--full">Se connecter</button>

                        <div class="login-note">
                            En vous connectant, vous reprenez directement l’accès à vos cours, quiz, TD
                            et à votre progression personnelle.
                        </div>
                    </div>
                </form>

                <div class="login-alt">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}">Créer un compte gratuitement</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
