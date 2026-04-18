@extends('layouts.student')

@section('title', 'Tableau de bord')

@php
    $studentName = auth()->user()->full_name;
    $className = $studentProfile->schoolClass->name ?? 'Classe non définie';
    $isSubscriptionActive = $subscription && $subscription->isActive();
    $subscriptionState = $isSubscriptionActive ? 'Actif' : 'Inactif';
    $subscriptionSymbol = $isSubscriptionActive ? '✓' : '!';
    $courseCount = $recentCourses->count();
    $tdCount = $recentTdSets->count();
    $progressPercent = min(100, ($tdOpenedCount * 20));
@endphp

@push('styles')
<style>
    .student-dashboard-v2 {
        display: grid;
        gap: 22px;
    }

    .student-dashboard-v2 .dashboard-hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 28%),
            radial-gradient(circle at 20% 100%, rgba(56, 189, 248, 0.18), transparent 30%),
            linear-gradient(135deg, #2563eb 0%, #3b82f6 40%, #4f46e5 100%);
        color: #fff;
        padding: 28px;
        box-shadow: var(--shadow-lg);
    }

    .student-dashboard-v2 .dashboard-hero::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        top: -70px;
        right: -40px;
    }

    .student-dashboard-v2 .dashboard-hero::after {
        content: "";
        position: absolute;
        width: 140px;
        height: 140px;
        border-radius: 999px;
        background: rgba(255,255,255,.06);
        bottom: -50px;
        left: -30px;
    }

    .student-dashboard-v2 .dashboard-hero__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 20px;
        align-items: stretch;
    }

    .student-dashboard-v2 .dashboard-hero__left,
    .student-dashboard-v2 .dashboard-hero__right {
        display: grid;
        gap: 16px;
    }

    .student-dashboard-v2 .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        width: fit-content;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.10);
        font-size: .84rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .student-dashboard-v2 .hero-title {
        margin: 0;
        font-size: clamp(2rem, 3.4vw, 3.5rem);
        line-height: 1.02;
        letter-spacing: -0.05em;
        max-width: 10ch;
    }

    .student-dashboard-v2 .hero-title span {
        display: block;
        color: #dbeafe;
    }

    .student-dashboard-v2 .hero-text {
        margin: 0;
        color: rgba(255,255,255,.82);
        line-height: 1.75;
        max-width: 60ch;
        font-size: 1rem;
    }

    .student-dashboard-v2 .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-dashboard-v2 .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.16);
        background: rgba(255,255,255,.08);
        font-size: .84rem;
        font-weight: 800;
        color: #eef6ff;
    }

    .student-dashboard-v2 .hero-side-card {
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        padding: 18px;
        backdrop-filter: blur(10px);
    }

    .student-dashboard-v2 .hero-side-card strong {
        display: block;
        font-size: 1.1rem;
        letter-spacing: -0.02em;
        margin-bottom: 8px;
    }

    .student-dashboard-v2 .hero-side-card p {
        margin: 0;
        color: rgba(255,255,255,.78);
        line-height: 1.65;
        font-size: .94rem;
    }

    .student-dashboard-v2 .hero-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
    }

    .student-dashboard-v2 .hero-status__left {
        display: grid;
        gap: 4px;
    }

    .student-dashboard-v2 .hero-status__left span {
        color: rgba(255,255,255,.76);
        font-size: .88rem;
    }

    .student-dashboard-v2 .hero-status__left strong {
        font-size: 1.05rem;
        letter-spacing: -0.02em;
    }

    .student-dashboard-v2 .hero-status__badge {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.14);
        font-size: 1.4rem;
        font-weight: 900;
        color: #fff;
        flex: 0 0 58px;
    }

    .student-dashboard-v2 .quick-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .student-dashboard-v2 .quick-action {
        position: relative;
        overflow: hidden;
        display: grid;
        gap: 12px;
        min-height: 142px;
        padding: 18px;
        border-radius: 26px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-v2 .quick-action:hover,
    .student-dashboard-v2 .dashboard-stat:hover,
    .student-dashboard-v2 .dashboard-panel:hover,
    .student-dashboard-v2 .dashboard-side-card:hover,
    .student-dashboard-v2 .dashboard-shortcut:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .student-dashboard-v2 .quick-action::before {
        content: "";
        position: absolute;
        width: 90px;
        height: 90px;
        border-radius: 999px;
        top: -24px;
        right: -18px;
        background: rgba(37, 99, 235, 0.08);
    }

    .student-dashboard-v2 .quick-action--primary {
        background:
            radial-gradient(circle at top right, rgba(29,109,255,.14), transparent 28%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-v2 .quick-action__icon {
        width: 50px;
        height: 50px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37,99,235,.10);
        font-size: 1.25rem;
    }

    .student-dashboard-v2 .quick-action__title {
        margin: 0;
        font-size: 1.02rem;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-dashboard-v2 .quick-action__text {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
        font-size: .9rem;
    }

    .student-dashboard-v2 .quick-action__link {
        margin-top: auto;
        color: var(--primary);
        font-weight: 800;
        font-size: .92rem;
    }

    .student-dashboard-v2 .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .student-dashboard-v2 .dashboard-stat {
        padding: 20px;
        border-radius: 26px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        display: grid;
        gap: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-v2 .dashboard-stat__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .student-dashboard-v2 .dashboard-stat__icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        background: rgba(37,99,235,.10);
    }

    .student-dashboard-v2 .dashboard-stat__value {
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: var(--text);
    }

    .student-dashboard-v2 .dashboard-stat__label {
        display: block;
        font-size: .95rem;
        font-weight: 800;
        color: var(--text);
    }

    .student-dashboard-v2 .dashboard-stat__note {
        color: var(--muted);
        font-size: .88rem;
        line-height: 1.6;
    }

    .student-dashboard-v2 .dashboard-main-grid {
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 18px;
        align-items: start;
    }

    .student-dashboard-v2 .dashboard-panel,
    .student-dashboard-v2 .dashboard-side-card,
    .student-dashboard-v2 .dashboard-shortcut {
        border: 1px solid var(--line);
        border-radius: 28px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-v2 .dashboard-panel {
        overflow: hidden;
    }

    .student-dashboard-v2 .dashboard-panel__head {
        padding: 22px 22px 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line);
    }

    .student-dashboard-v2 .dashboard-panel__head h2 {
        margin: 0;
        font-size: 1.45rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-v2 .dashboard-panel__head p,
    .student-dashboard-v2 .dashboard-panel__head span {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
    }

    .student-dashboard-v2 .dashboard-list {
        display: grid;
    }

    .student-dashboard-v2 .dashboard-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--line);
    }

    .student-dashboard-v2 .dashboard-list-item:last-child {
        border-bottom: 0;
    }

    .student-dashboard-v2 .dashboard-list-item__left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .student-dashboard-v2 .dashboard-subject-mark {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 900;
        flex: 0 0 48px;
        box-shadow: var(--shadow-xs);
    }

    .student-dashboard-v2 .dashboard-list-item__text {
        min-width: 0;
        display: grid;
        gap: 2px;
    }

    .student-dashboard-v2 .dashboard-list-item__text strong {
        font-size: 1rem;
        line-height: 1.35;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-dashboard-v2 .dashboard-list-item__text strong a {
        color: inherit;
    }

    .student-dashboard-v2 .dashboard-list-item__text span {
        color: var(--muted);
        font-size: .88rem;
    }

    .student-dashboard-v2 .dashboard-tag {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.06);
        color: var(--primary);
        font-size: .82rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .student-dashboard-v2 .dashboard-side {
        display: grid;
        gap: 18px;
    }

    .student-dashboard-v2 .dashboard-side-card {
        padding: 22px;
        display: grid;
        gap: 14px;
    }

    .student-dashboard-v2 .dashboard-side-card h3 {
        margin: 0;
        font-size: 1.15rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-v2 .dashboard-side-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .student-dashboard-v2 .dashboard-side-list {
        display: grid;
        gap: 10px;
    }

    .student-dashboard-v2 .dashboard-side-list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.06);
    }

    .student-dashboard-v2 .dashboard-side-list div strong {
        font-size: .95rem;
    }

    .student-dashboard-v2 .dashboard-side-list div span {
        color: var(--muted);
        font-size: .88rem;
    }

    .student-dashboard-v2 .dashboard-side-note {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.06);
        color: var(--muted);
        line-height: 1.7;
        font-size: .9rem;
    }

    .student-dashboard-v2 .dashboard-shortcuts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .student-dashboard-v2 .dashboard-shortcut {
        padding: 22px;
        display: grid;
        gap: 14px;
    }

    .student-dashboard-v2 .dashboard-shortcut__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .student-dashboard-v2 .dashboard-shortcut__icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        background: rgba(37,99,235,.10);
        flex: 0 0 54px;
    }

    .student-dashboard-v2 .dashboard-shortcut h3 {
        margin: 0;
        font-size: 1.2rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-v2 .dashboard-shortcut p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .student-dashboard-v2 .dashboard-shortcut__link {
        color: var(--primary);
        font-weight: 800;
    }

    .student-dashboard-v2 .empty-state {
        padding: 20px 22px;
        color: var(--muted);
        line-height: 1.7;
    }

    @media (max-width: 1180px) {
        .student-dashboard-v2 .dashboard-hero__grid,
        .student-dashboard-v2 .dashboard-main-grid,
        .student-dashboard-v2 .dashboard-shortcuts,
        .student-dashboard-v2 .quick-actions,
        .student-dashboard-v2 .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .student-dashboard-v2 .dashboard-hero__grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-v2 .dashboard-main-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-v2 .quick-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .student-dashboard-v2 .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .student-dashboard-v2 {
            gap: 18px;
        }

        .student-dashboard-v2 .dashboard-hero,
        .student-dashboard-v2 .quick-action,
        .student-dashboard-v2 .dashboard-stat,
        .student-dashboard-v2 .dashboard-side-card,
        .student-dashboard-v2 .dashboard-shortcut {
            border-radius: 22px;
        }

        .student-dashboard-v2 .dashboard-hero {
            padding: 20px 16px;
        }

        .student-dashboard-v2 .quick-actions,
        .student-dashboard-v2 .stats-grid,
        .student-dashboard-v2 .dashboard-shortcuts,
        .student-dashboard-v2 .login-feature-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-v2 .hero-title {
            max-width: none;
            font-size: clamp(1.9rem, 10vw, 2.8rem);
        }

        .student-dashboard-v2 .dashboard-panel__head,
        .student-dashboard-v2 .dashboard-list-item,
        .student-dashboard-v2 .dashboard-side-card,
        .student-dashboard-v2 .dashboard-shortcut {
            padding-left: 16px;
            padding-right: 16px;
        }

        .student-dashboard-v2 .dashboard-list-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .student-dashboard-v2 .dashboard-list-item__left {
            width: 100%;
        }

        .student-dashboard-v2 .hero-status {
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .student-dashboard-v2 .dashboard-hero {
            padding: 18px 14px;
        }

        .student-dashboard-v2 .dashboard-panel__head,
        .student-dashboard-v2 .dashboard-list-item,
        .student-dashboard-v2 .dashboard-side-card,
        .student-dashboard-v2 .dashboard-shortcut {
            padding-left: 14px;
            padding-right: 14px;
        }

        .student-dashboard-v2 .quick-action,
        .student-dashboard-v2 .dashboard-stat {
            padding: 16px;
        }

        .student-dashboard-v2 .hero-badge,
        .student-dashboard-v2 .hero-pill,
        .student-dashboard-v2 .dashboard-tag {
            font-size: .78rem;
        }

        .student-dashboard-v2 .dashboard-subject-mark,
        .student-dashboard-v2 .dashboard-shortcut__icon,
        .student-dashboard-v2 .dashboard-stat__icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            flex-basis: 46px;
        }

        .student-dashboard-v2 .dashboard-stat__value {
            font-size: 1.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="student-dashboard-v2">
    <section class="dashboard-hero">
        <div class="dashboard-hero__grid">
            <div class="dashboard-hero__left">
                <span class="hero-badge">✨ Tableau de bord élève</span>

                <h1 class="hero-title">
                    Bonjour, {{ $studentName }}
                    <span>prêt à continuer ? 👋</span>
                </h1>

                <p class="hero-text">
                    Retrouvez votre classe, vos TD, vos cours et les repères essentiels dans un espace
                    plus clair, plus attractif et mieux organisé pour votre progression.
                </p>

                <div class="hero-meta">
                    <span class="hero-pill">Classe : {{ $className }}</span>
                    <span class="hero-pill">TD ouverts : {{ $tdOpenedCount }}</span>
                    <span class="hero-pill">Progression estimée : {{ $progressPercent }}%</span>
                </div>
            </div>

            <div class="dashboard-hero__right">
                <div class="hero-status">
                    <div class="hero-status__left">
                        <span>État de l’abonnement</span>
                        <strong>{{ $subscriptionState }}</strong>
                    </div>
                    <div class="hero-status__badge">{{ $subscriptionSymbol }}</div>
                </div>

                <article class="hero-side-card">
                    <strong>Votre espace de travail</strong>
                    <p>
                        Utilisez ce tableau de bord pour accéder rapidement à vos TD, consulter vos cours,
                        poser une question et suivre votre rythme de travail.
                    </p>
                </article>

                <article class="hero-side-card">
                    <strong>Objectif du moment</strong>
                    <p>
                        Garder un bon rythme sur vos TD récents et revenir régulièrement sur les matières où
                        vous devez encore progresser.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="quick-actions">
        <a href="{{ route('student.courses.index') }}" class="quick-action">
            <span class="quick-action__icon">📘</span>
            <h3 class="quick-action__title">Mes cours</h3>
            <p class="quick-action__text">Retrouvez vos contenus et reprenez votre parcours d’apprentissage.</p>
            <span class="quick-action__link">Voir mes cours</span>
        </a>

        <a href="{{ route('student.td.index') }}" class="quick-action quick-action--primary">
            <span class="quick-action__icon">📝</span>
            <h3 class="quick-action__title">Mes TD</h3>
            <p class="quick-action__text">Accédez aux TD disponibles, aux corrigés et aux publications récentes.</p>
            <span class="quick-action__link">Accéder à mes TD</span>
        </a>

        <a href="{{ route('student.messages.create') }}" class="quick-action">
            <span class="quick-action__icon">💬</span>
            <h3 class="quick-action__title">Poser une question</h3>
            <p class="quick-action__text">Écrivez à votre enseignant ou demandez de l’aide sur un TD.</p>
            <span class="quick-action__link">Ouvrir la messagerie</span>
        </a>

        <a href="{{ route('student.subscription.index') }}" class="quick-action">
            <span class="quick-action__icon">💳</span>
            <h3 class="quick-action__title">Mon abonnement</h3>
            <p class="quick-action__text">Vérifiez votre accès, votre formule et les options disponibles.</p>
            <span class="quick-action__link">Gérer mon abonnement</span>
        </a>
    </section>

    <section class="stats-grid">
        <article class="dashboard-stat">
            <div class="dashboard-stat__top">
                <div>
                    <span class="dashboard-stat__label">Cours disponibles</span>
                    <div class="dashboard-stat__value">{{ $courseCount }}</div>
                </div>
                <span class="dashboard-stat__icon">📚</span>
            </div>
            <div class="dashboard-stat__note">Vos contenus récents et accessibles depuis votre classe.</div>
        </article>

        <article class="dashboard-stat">
            <div class="dashboard-stat__top">
                <div>
                    <span class="dashboard-stat__label">TD disponibles</span>
                    <div class="dashboard-stat__value">{{ $tdCount }}</div>
                </div>
                <span class="dashboard-stat__icon">📝</span>
            </div>
            <div class="dashboard-stat__note">Travaux dirigés publiés récemment pour votre classe.</div>
        </article>

        <article class="dashboard-stat">
            <div class="dashboard-stat__top">
                <div>
                    <span class="dashboard-stat__label">TD ouverts</span>
                    <div class="dashboard-stat__value">{{ $tdOpenedCount }}</div>
                </div>
                <span class="dashboard-stat__icon">📂</span>
            </div>
            <div class="dashboard-stat__note">Éléments déjà consultés pour poursuivre sans perdre le fil.</div>
        </article>

        <article class="dashboard-stat">
            <div class="dashboard-stat__top">
                <div>
                    <span class="dashboard-stat__label">Progression</span>
                    <div class="dashboard-stat__value">{{ $progressPercent }}%</div>
                </div>
                <span class="dashboard-stat__icon">📈</span>
            </div>
            <div class="dashboard-stat__note">Indication visuelle simple de votre activité récente.</div>
        </article>
    </section>

    <section class="dashboard-main-grid">
        <section class="dashboard-panel">
            <div class="dashboard-panel__head">
                <div>
                    <h2>TD récents</h2>
                    <p>Les dernières publications de votre classe, prêtes à être consultées.</p>
                </div>
                <span class="muted">Dernières activités</span>
            </div>

            <div class="dashboard-list">
                @forelse ($recentTdSets as $td)
                    <div class="dashboard-list-item">
                        <div class="dashboard-list-item__left">
                            <div class="dashboard-subject-mark" style="background-color: {{ $td->subject->color ?? '#4F46E5' }};">
                                {{ $td->subject->initials ?? 'TD' }}
                            </div>

                            <div class="dashboard-list-item__text">
                                <strong>
                                    <a href="{{ route('student.td.show', $td) }}">{{ $td->title }}</a>
                                </strong>
                                <span>{{ $td->subject->name }}</span>
                            </div>
                        </div>

                        <span class="dashboard-tag">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span>
                    </div>
                @empty
                    <div class="empty-state">Aucun TD disponible pour le moment.</div>
                @endforelse
            </div>
        </section>

        <aside class="dashboard-side">
            <article class="dashboard-side-card">
                <h3>Repères rapides</h3>
                <p>Gardez sous les yeux les informations essentielles liées à votre espace.</p>

                <div class="dashboard-side-list">
                    <div>
                        <strong>Classe</strong>
                        <span>{{ $className }}</span>
                    </div>
                    <div>
                        <strong>Abonnement</strong>
                        <span>{{ $subscriptionState }}</span>
                    </div>
                    <div>
                        <strong>TD disponibles</strong>
                        <span>{{ $tdCount }}</span>
                    </div>
                </div>
            </article>

            <article class="dashboard-side-card">
                <h3>Conseil du moment</h3>
                <p>
                    Commencez par les TD récents, puis revenez sur les matières où vous avez encore besoin
                    d’un meilleur rythme de travail.
                </p>

                <div class="dashboard-side-note">
                    Une bonne habitude : consulter vos TD, relire le cours lié et poser une question dès qu’un point bloque.
                </div>
            </article>
        </aside>
    </section>

    <section class="dashboard-shortcuts">
        <a href="{{ route('student.td.index') }}" class="dashboard-shortcut">
            <div class="dashboard-shortcut__top">
                <div>
                    <h3>Mes TD</h3>
                    <p>Accédez à vos TD, corrigés, niveaux d’accès et dernières publications.</p>
                </div>
                <span class="dashboard-shortcut__icon">🗂️</span>
            </div>
            <span class="dashboard-shortcut__link">Ouvrir cet espace</span>
        </a>

        <a href="{{ route('student.messages.create') }}" class="dashboard-shortcut">
            <div class="dashboard-shortcut__top">
                <div>
                    <h3>Messagerie enseignant</h3>
                    <p>Posez vos questions liées à la matière, au cours ou au TD concerné.</p>
                </div>
                <span class="dashboard-shortcut__icon">✉️</span>
            </div>
            <span class="dashboard-shortcut__link">Écrire maintenant</span>
        </a>
    </section>
</div>
@endsection
