@extends('layouts.public')

@section('title', 'Inscription - TIMAH SCHOOL')

@section('content')
<style>
    .register-page {
        min-height: calc(100vh - 90px);
        padding: 120px 16px 60px;
        background: linear-gradient(135deg, #eef4ff 0%, #ffffff 50%, #f5f0ff 100%);
    }
    .register-wrap {
        max-width: 560px;
        margin: 0 auto;
    }
    .register-header {
        text-align: center;
        margin-bottom: 22px;
    }
    .register-badge {
        width: 74px;
        height: 74px;
        margin: 0 auto 16px;
        border-radius: 22px;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: 800;
        box-shadow: 0 18px 34px rgba(37, 99, 235, 0.25);
    }
    .register-title {
        margin: 0 0 8px;
        font-size: 34px;
        font-weight: 800;
        color: #0f172a;
    }
    .register-subtitle {
        margin: 0;
        color: #475569;
        font-size: 15px;
    }
    .register-subtitle strong { color: #2563eb; }
    .register-card {
        background: #fff;
        border: 1px solid #dbe3f0;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.10);
        padding: 30px;
    }
    .form-grid { display: grid; gap: 18px; }
    .field label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .field .hint { color: #64748b; font-size: 12px; }
    .required { color: #dc2626; }
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
        color: #64748b;
        pointer-events: none;
    }
    .form-input,
    .form-select {
        width: 100%;
        height: 52px;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        background: #fff;
        color: #0f172a;
        padding: 0 16px 0 46px;
        font-size: 15px;
        outline: none;
        transition: .2s ease;
        box-sizing: border-box;
    }
    .form-select { appearance: none; padding-right: 42px; }
    .select-arrow {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #64748b;
        pointer-events: none;
    }
    .form-input:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }
    .error-text {
        margin-top: 6px;
        color: #dc2626;
        font-size: 13px;
    }
    .helper-text {
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
    }
    .submit-btn {
        width: 100%;
        height: 54px;
        border: 0;
        border-radius: 16px;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        color: #fff;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 18px 30px rgba(37, 99, 235, 0.22);
    }
    .submit-btn:hover { filter: brightness(1.03); }
    .auth-alt {
        margin-top: 18px;
        text-align: center;
        font-size: 14px;
        color: #475569;
    }
    .auth-alt a { color: #2563eb; font-weight: 700; }
    .legal-note {
        margin-top: 18px;
        text-align: center;
        font-size: 12px;
        color: #64748b;
        line-height: 1.6;
    }
    .legal-note a { color: #2563eb; }
    @media (max-width: 640px) {
        .register-page { padding-top: 104px; }
        .register-title { font-size: 28px; }
        .register-card { padding: 22px 18px; border-radius: 18px; }
    }
</style>

<div class="register-page">
    <div class="register-wrap">
        <div class="register-header">
            <div class="register-badge">T</div>
            <h1 class="register-title">Créer un compte</h1>
            <p class="register-subtitle">Commencez votre essai gratuit de <strong>24 heures</strong></p>
        </div>

        <div class="register-card">
            <form method="POST" action="{{ route('register') }}" class="form-grid">
                @csrf

                <div class="field">
                    <label for="full_name">Nom complet <span class="required">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input class="form-input" type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Jean Dupont" required>
                    </div>
                    @error('full_name')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="username">Nom d'utilisateur <span class="required">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input class="form-input" type="text" id="username" name="username" value="{{ old('username') }}" placeholder="jeandupont" required>
                    </div>
                    @error('username')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="email">Email <span class="hint">(optionnel)</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                        <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="jean@exemple.com">
                    </div>
                    @error('email')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="phone">Téléphone <span class="hint">(optionnel)</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92V19a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h2.09a2 2 0 0 1 2 1.72c.12.9.35 1.78.68 2.62a2 2 0 0 1-.45 2.11L7.3 8.59a16 16 0 0 0 8.11 8.11l1.14-1.13a2 2 0 0 1 2.11-.45c.84.33 1.72.56 2.62.68A2 2 0 0 1 22 16.92z"></path></svg>
                        <input class="form-input" type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="6XXXXXXXX">
                    </div>
                    @error('phone')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="school_class_id">Votre classe <span class="required">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l8-4 8 4v14"></path><path d="M9 9h.01"></path><path d="M9 13h.01"></path><path d="M9 17h.01"></path><path d="M15 9h.01"></path><path d="M15 13h.01"></path><path d="M15 17h.01"></path></svg>
                        <select class="form-select" id="school_class_id" name="school_class_id" required>
                            <option value="">Choisir votre classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"></path></svg>
                    </div>
                    @error('school_class_id')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password">Mot de passe <span class="required">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input class="form-input" type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="helper-text">Minimum 8 caractères.</div>
                    @error('password')<div class="error-text">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmer le mot de passe <span class="required">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input class="form-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Créer mon compte gratuitement</button>

                <div class="auth-alt">
                    Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
                </div>
            </form>
        </div>

        <div class="legal-note">
            En créant un compte, vous acceptez nos <a href="#">conditions d'utilisation</a>
            et notre <a href="#">politique de confidentialité</a>.
        </div>
    </div>
</div>
@endsection
