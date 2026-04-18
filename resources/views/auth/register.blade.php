@extends('layouts.public')

@section('title', 'Inscription - TIMAH ACADEMY')
@section('meta_description', 'Créez votre compte TIMAH ACADEMY pour accéder à vos cours, quiz, TD et à votre suivi de progression.')

@php
    $classCount = isset($classes) ? $classes->count() : 0;
@endphp

@push('styles')
<style>
    .register-page {
        min-height: calc(100vh - 90px);
        padding: 34px 0 56px;
        position: relative;
        overflow: hidden;
    }

    .register-page::before,
    .register-page::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        z-index: 0;
        filter: blur(10px);
    }

    .register-page::before {
        width: 380px;
        height: 380px;
        top: -120px;
        right: -140px;
        background: radial-gradient(circle, rgba(29, 109, 255, 0.16), transparent 70%);
    }

    .register-page::after {
        width: 300px;
        height: 300px;
        left: -120px;
        bottom: -90px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.12), transparent 72%);
    }

    .register-page .container {
        position: relative;
        z-index: 1;
    }

    .register-wrap {
        display: grid;
        grid-template-columns: 1.02fr .98fr;
        gap: 24px;
        align-items: stretch;
    }

    .register-showcase,
    .register-card {
        border: 1px solid var(--line);
        border-radius: 30px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .register-showcase {
        padding: 30px;
        color: #eaf1ff;
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.22), transparent 30%),
            linear-gradient(180deg, #0d1a36, #081224);
        display: grid;
        gap: 20px;
        align-content: start;
    }

    .register-card {
        padding: 30px;
        background:
            radial-gradient(circle at top right, rgba(29, 109, 255, 0.12), transparent 26%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .register-badge {
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
    }

    .register-title {
        margin: 0;
        font-size: clamp(2rem, 3.4vw, 3.3rem);
        line-height: 1.02;
        letter-spacing: -0.05em;
        max-width: 10ch;
    }

    .register-title .accent {
        display: block;
        color: #8ec0ff;
    }

    .register-text {
        margin: 0;
        color: rgba(234, 241, 255, 0.78);
        font-size: 1rem;
        line-height: 1.75;
        max-width: 58ch;
    }

    .register-feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .register-feature {
        padding: 16px;
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
    }

    .register-feature strong {
        display: block;
        margin-bottom: 8px;
        font-size: 1rem;
        letter-spacing: -0.02em;
        color: #fff;
    }

    .register-feature p {
        margin: 0;
        color: rgba(234, 241, 255, 0.75);
        font-size: .94rem;
        line-height: 1.65;
    }

    .register-mini-stats {
        display: grid;
        gap: 12px;
    }

    .register-mini-stat {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
    }

    .register-mini-stat strong {
        font-size: 1rem;
        color: #fff;
    }

    .register-mini-stat span {
        color: rgba(234, 241, 255, 0.72);
    }

    .register-header {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
    }

    .register-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        width: fit-content;
    }

    .register-brand__mark {
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
    }

    .register-brand__text {
        display: grid;
        gap: 3px;
    }

    .register-brand__text strong {
        font-size: 1.05rem;
        letter-spacing: -0.03em;
    }

    .register-brand__text span {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 700;
    }

    .register-card h1 {
        margin: 0;
        font-size: clamp(1.9rem, 3vw, 2.6rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
    }

    .register-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .register-alert {
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(239, 68, 68, 0.18);
        background: rgba(239, 68, 68, 0.08);
        color: #b91c1c;
        font-size: .94rem;
        font-weight: 700;
    }

    html[data-theme='dark'] .register-alert {
        color: #fecaca;
        border-color: rgba(248, 113, 113, 0.24);
        background: rgba(127, 29, 29, 0.24);
    }

    .register-form {
        margin-top: 20px;
        display: grid;
        gap: 16px;
    }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .field {
        display: grid;
        gap: 8px;
    }

    .field--full {
        grid-column: 1 / -1;
    }

    .field label {
        font-size: .92rem;
        font-weight: 800;
        color: var(--text);
    }

    .field label .hint {
        color: var(--muted);
        font-size: .8rem;
        font-weight: 700;
    }

    .required {
        color: #dc2626;
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

    .select-arrow {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: var(--muted);
        pointer-events: none;
    }

    .form-input,
    .form-select {
        width: 100%;
        height: 52px;
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

    .form-select {
        appearance: none;
        padding-right: 40px;
    }

    .form-input:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(29, 109, 255, 0.12);
    }

    .helper-text,
    .error-text {
        font-size: .82rem;
    }

    .helper-text {
        color: var(--muted);
    }

    .error-text {
        color: #dc2626;
        font-weight: 700;
    }

    .register-actions {
        display: grid;
        gap: 12px;
        margin-top: 4px;
    }

    .register-note {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.06);
        color: var(--muted);
        font-size: .92rem;
        line-height: 1.65;
    }

    .register-alt {
        margin-top: 18px;
        text-align: center;
        color: var(--muted);
        font-size: .94rem;
    }

    .register-alt a,
    .legal-note a {
        color: var(--primary);
        font-weight: 800;
    }

    .legal-note {
        margin-top: 16px;
        text-align: center;
        color: var(--muted);
        font-size: .84rem;
        line-height: 1.7;
    }

    @media (max-width: 1180px) {
        .register-wrap {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 820px) {
        .field-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .register-page {
            padding: 22px 0 44px;
        }

        .register-showcase,
        .register-card {
            padding: 20px 18px;
            border-radius: 24px;
        }

        .register-feature-grid {
            grid-template-columns: 1fr;
        }

        .register-title {
            max-width: none;
            font-size: clamp(2rem, 11vw, 3rem);
        }
    }
</style>
@endpush

@section('content')
<section class="register-page">
    <div class="container">
        <div class="register-wrap">
            <div class="register-showcase reveal">
                <span class="register-badge">🚀 Commencez votre parcours sur TIMAH ACADEMY</span>

                <h2 class="register-title">
                    Créez votre compte
                    <span class="accent">et entrez dans la plateforme.</span>
                </h2>

                <p class="register-text">
                    Inscrivez-vous pour accéder à une expérience claire, moderne et pensée pour la progression :
                    cours structurés, quiz, TD corrigés et meilleur suivi du travail.
                </p>

                <div class="register-feature-grid">
                    <article class="register-feature">
                        <strong>Entrée simple</strong>
                        <p>Choisissez votre classe et commencez sans vous perdre dans une interface compliquée.</p>
                    </article>

                    <article class="register-feature">
                        <strong>Progression guidée</strong>
                        <p>Retrouvez des contenus organisés pour travailler avec méthode et régularité.</p>
                    </article>

                    <article class="register-feature">
                        <strong>Accès utile</strong>
                        <p>Cours, quiz, TD et suivi réunis dans un même espace cohérent.</p>
                    </article>

                    <article class="register-feature">
                        <strong>Plateforme sérieuse</strong>
                        <p>Un environnement pensé pour les élèves, les parents et les enseignants.</p>
                    </article>
                </div>

                <div class="register-mini-stats">
                    <div class="register-mini-stat">
                        <span>Classes disponibles</span>
                        <strong>{{ $classCount > 0 ? $classCount : 'Plusieurs' }}</strong>
                    </div>

                    <div class="register-mini-stat">
                        <span>Inscription</span>
                        <strong>Rapide</strong>
                    </div>

                    <div class="register-mini-stat">
                        <span>Expérience</span>
                        <strong>Claire et moderne</strong>
                    </div>
                </div>
            </div>

            <div class="register-card reveal">
                <div class="register-header">
                    <div class="register-brand">
                        <span class="register-brand__mark">TA</span>
                        <span class="register-brand__text">
                            <strong>TIMAH ACADEMY</strong>
                            <span>Création de compte</span>
                        </span>
                    </div>

                    <h1>Créer un compte</h1>
                    <p>Remplissez les informations ci-dessous pour accéder à votre espace d’apprentissage.</p>
                </div>

                @if ($errors->any())
                    <div class="register-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="register-form">
                    @csrf

                    <div class="field-grid">
                        <div class="field">
                            <label for="full_name">Nom complet <span class="required">*</span></label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input
                                    class="form-input"
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    value="{{ old('full_name') }}"
                                    placeholder="Jean Dupont"
                                    required
                                >
                            </div>
                            @error('full_name')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="username">Nom d'utilisateur <span class="required">*</span></label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input
                                    class="form-input"
                                    type="text"
                                    id="username"
                                    name="username"
                                    value="{{ old('username') }}"
                                    placeholder="jeandupont"
                                    required
                                >
                            </div>
                            @error('username')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="email">
                                Email <span class="hint">(optionnel)</span>
                            </label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m3 7 9 6 9-6"></path>
                                </svg>
                                <input
                                    class="form-input"
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="jean@exemple.com"
                                >
                            </div>
                            @error('email')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="phone">
                                Téléphone <span class="hint">(optionnel)</span>
                            </label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92V19a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h2.09a2 2 0 0 1 2 1.72c.12.9.35 1.78.68 2.62a2 2 0 0 1-.45 2.11L7.3 8.59a16 16 0 0 0 8.11 8.11l1.14-1.13a2 2 0 0 1 2.11-.45c.84.33 1.72.56 2.62.68A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <input
                                    class="form-input"
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="6XXXXXXXX"
                                >
                            </div>
                            @error('phone')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field field--full">
                            <label for="school_class_id">Votre classe <span class="required">*</span></label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 21h18"></path>
                                    <path d="M5 21V7l8-4 8 4v14"></path>
                                    <path d="M9 9h.01"></path>
                                    <path d="M9 13h.01"></path>
                                    <path d="M9 17h.01"></path>
                                    <path d="M15 9h.01"></path>
                                    <path d="M15 13h.01"></path>
                                    <path d="M15 17h.01"></path>
                                </svg>

                                <select class="form-select" id="school_class_id" name="school_class_id" required>
                                    <option value="">Choisir votre classe</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </div>
                            @error('school_class_id')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Mot de passe <span class="required">*</span></label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input
                                    class="form-input"
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="••••••••"
                                    required
                                >
                            </div>
                            <div class="helper-text">Minimum 8 caractères.</div>
                            @error('password')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password_confirmation">Confirmer le mot de passe <span class="required">*</span></label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input
                                    class="form-input"
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="••••••••"
                                    required
                                >
                            </div>
                            @error('password_confirmation')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="register-actions">
                        <button type="submit" class="btn btn--primary btn--full">Créer mon compte gratuitement</button>

                        <div class="register-note">
                            En créant votre compte, vous pourrez choisir votre classe, accéder à vos contenus
                            et commencer votre progression dans un environnement plus structuré.
                        </div>
                    </div>
                </form>

                <div class="register-alt">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}">Se connecter</a>
                </div>

                <div class="legal-note">
                    En créant un compte, vous acceptez nos
                    <a href="#">conditions d'utilisation</a>
                    et notre
                    <a href="#">politique de confidentialité</a>.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
