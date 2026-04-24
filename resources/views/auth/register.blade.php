@extends('layouts.public')

@section('title', 'Inscription - TIMAH ACADEMY')
@section('meta_description', 'Créez rapidement votre compte TIMAH ACADEMY avec votre nom d’utilisateur, votre classe, votre ville et votre mot de passe.')

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
        background: radial-gradient(circle, rgba(49, 87, 255, 0.16), transparent 70%);
    }

    .register-page::after {
        width: 300px;
        height: 300px;
        left: -120px;
        bottom: -90px;
        background: radial-gradient(circle, rgba(15, 118, 110, 0.13), transparent 72%);
    }

    .register-page .container {
        position: relative;
        z-index: 1;
    }

    .register-wrap {
        display: grid;
        grid-template-columns: .92fr 1.08fr;
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
            radial-gradient(circle at top right, rgba(45, 212, 191, 0.23), transparent 30%),
            radial-gradient(circle at bottom left, rgba(99, 102, 241, 0.22), transparent 32%),
            linear-gradient(135deg, #0d1a36, #081224 55%, #0f766e);
        display: grid;
        gap: 20px;
        align-content: start;
    }

    .register-card {
        padding: 30px;
        background:
            radial-gradient(circle at top right, rgba(49, 87, 255, 0.12), transparent 26%),
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
        border: 1px solid rgba(255,255,255,.16);
        background: rgba(255,255,255,.08);
        font-size: .84rem;
        font-weight: 900;
    }

    .register-title {
        margin: 0;
        font-size: clamp(2rem, 3.4vw, 3.25rem);
        line-height: 1.02;
        letter-spacing: -0.055em;
        max-width: 11ch;
    }

    .register-title .accent {
        display: block;
        color: #67e8f9;
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
        gap: 14px;
    }

    .register-feature {
        padding: 16px;
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.06);
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
        background: rgba(255,255,255,.06);
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
        background: linear-gradient(135deg, #3157ff, #6938ef);
        box-shadow: 0 16px 30px rgba(49, 87, 255, 0.24);
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

    .form-select {
        appearance: none;
        padding-right: 40px;
    }

    .form-input:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(49, 87, 255, 0.12);
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
        background: rgba(49, 87, 255, 0.06);
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
                <span class="register-badge">🚀 Inscription rapide</span>

                <h2 class="register-title">
                    Créez votre compte
                    <span class="accent">en quelques secondes.</span>
                </h2>

                <p class="register-text">
                    TIMAH ACADEMY simplifie l’accès : choisissez votre nom d’utilisateur, votre classe,
                    votre ville et votre mot de passe pour commencer votre essai gratuit.
                </p>

                <div class="register-feature-grid">
                    <article class="register-feature">
                        <strong>Formulaire réduit</strong>
                        <p>Seulement les informations nécessaires pour créer rapidement le compte élève.</p>
                    </article>

                    <article class="register-feature">
                        <strong>Accès direct</strong>
                        <p>Après l’inscription, l’élève arrive directement dans son espace de travail.</p>
                    </article>

                    <article class="register-feature">
                        <strong>Classe obligatoire</strong>
                        <p>Les TD, cours, rappels et comptes à rebours sont adaptés à la classe choisie.</p>
                    </article>
                </div>

                <div class="register-mini-stats">
                    <div class="register-mini-stat">
                        <span>Classes disponibles</span>
                        <strong>{{ $classCount > 0 ? $classCount : 'Plusieurs' }}</strong>
                    </div>

                    <div class="register-mini-stat">
                        <span>Inscription</span>
                        <strong>4 champs</strong>
                    </div>
                </div>
            </div>

            <div class="register-card reveal">
                <div class="register-header">
                    <div class="register-brand">
                        <span class="register-brand__mark">TA</span>
                        <span class="register-brand__text">
                            <strong>TIMAH ACADEMY</strong>
                            <span>Création de compte élève</span>
                        </span>
                    </div>

                    <h1>Créer un compte</h1>
                    <p>Remplissez uniquement ces informations pour accéder à votre espace.</p>
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
                                    placeholder="exemple: toukam237"
                                    required
                                >
                            </div>
                            @error('username')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="city">Ville <span class="required">*</span></label>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z"></path>
                                    <circle cx="12" cy="10" r="2.5"></circle>
                                </svg>
                                <input
                                    class="form-input"
                                    type="text"
                                    id="city"
                                    name="city"
                                    value="{{ old('city') }}"
                                    placeholder="exemple: Douala"
                                    required
                                >
                            </div>
                            @error('city')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field field--full">
                            <label for="school_class_id">Classe <span class="required">*</span></label>
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

                        <div class="field field--full">
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
                                    placeholder="Minimum 8 caractères"
                                    required
                                >
                            </div>
                            <div class="helper-text">Retenez bien ce mot de passe pour vos prochaines connexions.</div>
                            @error('password')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="register-actions">
                        <button type="submit" class="btn btn--primary btn--full">Créer mon compte gratuitement</button>

                        <div class="register-note">
                            Votre compte élève sera créé avec un essai gratuit de 24h.
                        </div>
                    </div>
                </form>

                <div class="register-alt">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}">Se connecter</a>
                </div>

                <div class="legal-note">
                    En créant un compte, vous acceptez les conditions d’utilisation de la plateforme.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
