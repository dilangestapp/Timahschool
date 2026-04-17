@extends('layouts.public')

@section('title', 'TIMAH ACADEMY - Réussissez vos examens')

@section('content')
@php
    $cardColors = ['blue', 'green', 'indigo', 'violet'];
    $totalClasses = $classes->count();
@endphp

<section class="hero">
    <div class="container">
        <div class="hero-card">
            <div class="hero-copy">
                <div class="hero-badge">Essai gratuit 24h</div>
                <h1 class="hero-title">Réussissez vos <span>examens</span> avec TIMAH ACADEMY</h1>
                <p class="hero-text">Une plateforme claire, moderne et structurée pour apprendre avec des cours, quiz et TD adaptés à votre classe.</p>

                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn--primary">Commencer gratuitement</a>
                    <a href="#classes" class="btn btn--ghost">Voir les classes</a>
                </div>

                <div class="hero-pills">
                    <span class="hero-pill">Sans engagement</span>
                    <span class="hero-pill">Cours + quiz + TD</span>
                    <span class="hero-pill">Interface claire / sombre</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-dashboard-preview">
                    <div class="hero-dashboard-preview__head">
                        <strong>Mon espace élève</strong>
                        <span>Progression active</span>
                    </div>
                    <div class="hero-dashboard-preview__stats">
                        <div><small>Cours</small><strong>24+</strong></div>
                        <div><small>Quiz</small><strong>120+</strong></div>
                        <div><small>TD</small><strong>48+</strong></div>
                    </div>
                    <div class="hero-dashboard-preview__progress">
                        <div class="hero-dashboard-preview__label"><span>Progression</span><strong>75%</strong></div>
                        <div class="hero-dashboard-preview__bar"><span></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats-panel">
    <div class="container">
        <div class="stats-panel__grid">
            <div class="stat-box"><div><span class="stat-box__value">{{ $totalClasses }}</span><span class="stat-box__label">Classes disponibles</span></div></div>
            <div class="stat-box"><div><span class="stat-box__value">Quiz</span><span class="stat-box__label">Évaluations interactives</span></div></div>
            <div class="stat-box"><div><span class="stat-box__value">24h</span><span class="stat-box__label">Essai gratuit</span></div></div>
            <div class="stat-box"><div><span class="stat-box__value">3</span><span class="stat-box__label">Formules d'abonnement</span></div></div>
        </div>
    </div>
</section>

<section id="classes" class="section">
    <div class="container">
        <h2 class="section-title">Classes disponibles</h2>
        <p class="section-subtitle">TIMAH ACADEMY couvre le secondaire général et l'enseignement technique avec des contenus structurés.</p>

        @foreach($classGroups as $groupKey => $groupClasses)
            <div class="class-group-block">
                <div class="class-group-head">
                    <div>
                        <h3>{{ $classGroupLabels[$groupKey] ?? ucfirst(str_replace('_', ' ', $groupKey)) }}</h3>
                        <p>{{ $groupKey === 'enseignement_technique' ? 'Classes techniques organisées par années de formation.' : 'Classes du secondaire général organisées par niveau.' }}</p>
                    </div>
                    <span class="hero-pill">{{ $groupClasses->count() }} classes</span>
                </div>

                <div class="classes-grid">
                    @foreach($groupClasses as $index => $class)
                        @php $color = $cardColors[$index % count($cardColors)]; @endphp
                        <article class="class-card class-card--{{ $color }}">
                            <div>
                                <div class="class-card__icon">{{ mb_substr($class->name, 0, 1) }}</div>
                                <h4 class="class-card__title">{{ $class->name }}</h4>
                                <div class="class-card__meta">{{ $class->description ?: 'Cours structurés et ressources pédagogiques adaptées à ce niveau.' }}</div>
                            </div>
                            <a href="{{ route('register') }}" class="btn">Commencer</a>
                        </article>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="why-strip">
            <h2 class="why-strip__title">Pourquoi choisir TIMAH ACADEMY ?</h2>
            <div class="why-strip__grid">
                <div class="why-item"><h3>Cours organisés</h3><p>Leçons structurées par classe et matière.</p></div>
                <div class="why-item"><h3>Quiz pratiques</h3><p>Évaluation rapide avec correction automatique.</p></div>
                <div class="why-item"><h3>Accompagnement</h3><p>Un cadre conçu pour faire progresser l'élève.</p></div>
                <div class="why-item"><h3>Suivi</h3><p>Visualisez l'évolution et les résultats.</p></div>
                <div class="why-item"><h3>Accès sécurisé</h3><p>Compte protégé avec abonnement simple.</p></div>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="section section--tight">
    <div class="container">
        <h2 class="section-title">Formules d'abonnement</h2>
        <p class="section-subtitle">Choisissez la formule qui convient à votre rythme d'apprentissage.</p>

        <div class="subscription-grid">
            <article class="plan-card">
                <h3>Essentiel Mensuel</h3>
                <div class="plan-price">3 000 XAF</div>
                <p class="muted">Idéal pour démarrer avec les cours et quiz de base.</p>
                <ul class="feature-list">
                    <li><span>✔</span><span>Accès aux cours de votre classe</span></li>
                    <li><span>✔</span><span>Quiz de base</span></li>
                    <li><span>✔</span><span>Suivi simple de progression</span></li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir Essentiel</a>
            </article>

            <article class="plan-card plan-card--highlight">
                <div class="plan-badge">Le plus choisi</div>
                <h3>Standard Trimestriel</h3>
                <div class="plan-price">13 500 XAF</div>
                <p class="muted">Plus de confort pour réviser sans interruption.</p>
                <ul class="feature-list">
                    <li><span>✔</span><span>Cours + quiz</span></li>
                    <li><span>✔</span><span>Suivi de progression renforcé</span></li>
                    <li><span>✔</span><span>Économie sur 3 mois</span></li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir Standard</a>
            </article>

            <article class="plan-card">
                <h3>Premium Annuel</h3>
                <div class="plan-price">68 000 XAF</div>
                <p class="muted">Pour un accès long terme avec priorité.</p>
                <ul class="feature-list">
                    <li><span>✔</span><span>Accès complet toute l'année</span></li>
                    <li><span>✔</span><span>Interaction et priorité</span></li>
                    <li><span>✔</span><span>Continuité pédagogique</span></li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir Premium</a>
            </article>
        </div>
    </div>
</section>
@endsection
