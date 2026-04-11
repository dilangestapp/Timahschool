@extends('layouts.public')

@section('title', 'TIMAHSCHOOL - Réussissez vos examens')

@section('content')
@php
    $cardColors = ['blue', 'green', 'indigo', 'violet'];
    $totalClasses = $classes->count();
@endphp

<section class="hero">
    <div class="container">
        <div class="hero-card">
            <div class="hero-copy">
                <div class="hero-badge">Essai gratuit de 24h offert</div>
                <h1 class="hero-title">Réussissez vos <span>examens</span> avec TIMAH SCHOOL</h1>
                <p class="hero-text">
                    Plateforme éducative complète avec cours structurés, quiz interactifs et accompagnement pédagogique.
                    Commencez votre essai gratuit et progressez à votre rythme.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn--primary">Commencer gratuitement</a>
                    <a href="#classes" class="btn btn--ghost">Voir les classes</a>
                </div>

                <div class="hero-actions" style="margin-top:18px;">
                    <div class="hero-badge" style="background:#ecfdf5; color:#15803d; text-transform:none; letter-spacing:0;">Sans engagement</div>
                    <div class="hero-badge" style="background:#eff6ff; color:#1d4ed8; text-transform:none; letter-spacing:0;">Cours + quiz + abonnement simple</div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-visual__frame">
                    <div class="hero-visual__chip">Accès élève sécurisé</div>
                    <div class="hero-visual__ring hero-visual__ring--one"></div>
                    <div class="hero-visual__ring hero-visual__ring--two"></div>

                    <div style="padding:30px; background:linear-gradient(135deg,#ffffff 0%, #f8fbff 100%); min-height:360px; display:flex; flex-direction:column; gap:18px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                            <div>
                                <div style="font-size:1.4rem; font-weight:800; color:#0f172a;">Mon espace élève</div>
                                <div style="color:#475569; margin-top:4px;">Cours, quiz et progression</div>
                            </div>
                            <div style="width:62px; height:62px; border-radius:18px; background:linear-gradient(135deg,#2563eb,#4f46e5); color:#fff; display:grid; place-items:center; font-weight:800; font-size:1.3rem; box-shadow:0 12px 24px rgba(37,99,235,.18);">T</div>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px;">
                            <div style="border:1px solid #dbe3f0; border-radius:18px; padding:16px; background:#fff;">
                                <div style="color:#475569; font-size:.92rem;">Cours disponibles</div>
                                <div style="font-size:1.8rem; font-weight:800; color:#2563eb; margin-top:4px;">24+</div>
                            </div>
                            <div style="border:1px solid #dbe3f0; border-radius:18px; padding:16px; background:#fff;">
                                <div style="color:#475569; font-size:.92rem;">Quiz interactifs</div>
                                <div style="font-size:1.8rem; font-weight:800; color:#4f46e5; margin-top:4px;">120+</div>
                            </div>
                        </div>

                        <div style="border:1px solid #dbe3f0; border-radius:20px; padding:18px; background:#fff; box-shadow:0 8px 18px rgba(15,23,42,.06);">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px;">
                                <strong style="color:#0f172a;">Progression de l'élève</strong>
                                <span style="color:#2563eb; font-weight:800;">75%</span>
                            </div>
                            <div style="height:12px; border-radius:999px; background:#e5edf7; overflow:hidden;">
                                <div style="width:75%; height:100%; background:linear-gradient(135deg,#2563eb,#4f46e5);"></div>
                            </div>
                            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:14px;">
                                <span class="hero-badge" style="text-transform:none; letter-spacing:0;">Mathématiques</span>
                                <span class="hero-badge" style="text-transform:none; letter-spacing:0;">Français</span>
                                <span class="hero-badge" style="text-transform:none; letter-spacing:0;">Sciences</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats-panel">
    <div class="container">
        <div class="stats-panel__grid">
            <div class="stat-box">
                <div class="stat-box__icon">📘</div>
                <div>
                    <span class="stat-box__value">{{ $totalClasses }}</span>
                    <span class="stat-box__label">Classes disponibles</span>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box__icon">📝</div>
                <div>
                    <span class="stat-box__value">Quiz</span>
                    <span class="stat-box__label">Évaluations interactives</span>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box__icon">🎓</div>
                <div>
                    <span class="stat-box__value">24h</span>
                    <span class="stat-box__label">Essai gratuit</span>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box__icon">💳</div>
                <div>
                    <span class="stat-box__value">3</span>
                    <span class="stat-box__label">Formules d'abonnement</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="classes" class="section">
    <div class="container">
        <h2 class="section-title">Classes disponibles</h2>
        <p class="section-subtitle">
            Du secondaire général à l'enseignement technique, TIMAH SCHOOL couvre plusieurs niveaux pour apprendre et réviser efficacement.
        </p>

        @foreach($classGroups as $groupKey => $groupClasses)
            <div style="margin-top:32px;">
                <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px;">
                    <div>
                        <h3 style="margin:0; font-size:1.55rem; font-weight:800; color:#0f172a;">
                            {{ $classGroupLabels[$groupKey] ?? ucfirst(str_replace('_', ' ', $groupKey)) }}
                        </h3>
                        <p style="margin:8px 0 0; color:#475569;">
                            {{ $groupKey === 'enseignement_technique' ? 'Classes techniques organisées par années de formation.' : 'Classes du secondaire général organisées par niveau.' }}
                        </p>
                    </div>
                    <div class="hero-badge" style="text-transform:none; letter-spacing:0;">{{ $groupClasses->count() }} classes</div>
                </div>

                <div class="classes-grid">
                    @foreach($groupClasses as $index => $class)
                        @php $color = $cardColors[$index % count($cardColors)]; @endphp
                        <div class="class-card class-card--{{ $color }}">
                            <div>
                                <div class="class-card__icon">{{ mb_substr($class->name, 0, 1) }}</div>
                                <h4 class="class-card__title">{{ $class->name }}</h4>
                                <div class="class-card__meta">
                                    {{ $class->description ?: 'Cours structurés et ressources pédagogiques adaptées à ce niveau.' }}
                                </div>
                            </div>
                            <a href="{{ route('register') }}" class="btn">Commencer</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="why-strip">
            <h2 class="why-strip__title">Pourquoi choisir TIMAH SCHOOL ?</h2>
            <div class="why-strip__grid">
                <div class="why-item">
                    <div class="why-item__icon">📚</div>
                    <h3>Cours organisés</h3>
                    <p>Leçons structurées par classe et par matière.</p>
                </div>
                <div class="why-item">
                    <div class="why-item__icon">✅</div>
                    <h3>Quiz pratiques</h3>
                    <p>Évaluation rapide avec correction automatique.</p>
                </div>
                <div class="why-item">
                    <div class="why-item__icon">👨🏽‍🏫</div>
                    <h3>Accompagnement</h3>
                    <p>Un cadre pensé pour aider l'élève à progresser.</p>
                </div>
                <div class="why-item">
                    <div class="why-item__icon">📈</div>
                    <h3>Suivi</h3>
                    <p>Visualisez la progression et les résultats.</p>
                </div>
                <div class="why-item">
                    <div class="why-item__icon">🔒</div>
                    <h3>Accès sécurisé</h3>
                    <p>Compte élève protégé avec abonnement simple.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="section" style="padding-top:0;">
    <div class="container">
        <h2 class="section-title">Formules d'abonnement</h2>
        <p class="section-subtitle">
            Choisissez la formule qui convient à votre rythme d'apprentissage.
        </p>

        <div class="subscription-grid" style="margin-top:28px;">
            <div class="plan-card">
                <h3 style="margin:0; font-size:1.6rem; font-weight:800;">Essentiel Mensuel</h3>
                <div class="plan-price">3 000 XAF</div>
                <p class="muted">Idéal pour démarrer avec les cours et quiz de base.</p>
                <ul class="feature-list">
                    <li><span>✔</span><span>Accès aux cours de votre classe</span></li>
                    <li><span>✔</span><span>Quiz de base</span></li>
                    <li><span>✔</span><span>Suivi simple de progression</span></li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir Essentiel</a>
            </div>

            <div class="plan-card plan-card--highlight">
                <div class="plan-badge">Le plus choisi</div>
                <h3 style="margin:0; font-size:1.6rem; font-weight:800;">Standard Trimestriel</h3>
                <div class="plan-price">13 500 XAF</div>
                <p class="muted">Plus de confort pour réviser plusieurs mois sans interruption.</p>
                <ul class="feature-list">
                    <li><span>✔</span><span>Cours + quiz</span></li>
                    <li><span>✔</span><span>Suivi de progression plus confortable</span></li>
                    <li><span>✔</span><span>Économie sur 3 mois</span></li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir Standard</a>
            </div>

            <div class="plan-card">
                <h3 style="margin:0; font-size:1.6rem; font-weight:800;">Premium Annuel</h3>
                <div class="plan-price">68 000 XAF</div>
                <p class="muted">Pour un accès long terme avec priorité et stabilité.</p>
                <ul class="feature-list">
                    <li><span>✔</span><span>Accès complet toute l'année</span></li>
                    <li><span>✔</span><span>Interaction et priorité</span></li>
                    <li><span>✔</span><span>Meilleure continuité pédagogique</span></li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir Premium</a>
            </div>
        </div>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="cta-box">
            <h3>Prêt à commencer ?</h3>
            <p>Créez votre compte, activez votre essai gratuit et commencez à apprendre dès aujourd'hui.</p>
            <div class="cta-box__actions">
                <a href="{{ route('register') }}" class="btn btn--primary">Créer mon compte gratuit</a>
                <a href="{{ route('login') }}" class="btn btn--ghost">J'ai déjà un compte</a>
            </div>
        </div>
    </div>
</section>
@endsection
