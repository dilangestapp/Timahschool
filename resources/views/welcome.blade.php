@extends('layouts.public')

@section('title', 'Accueil - Timah School')

@section('content')
<section class="hero">
    <div class="container">
        <div class="hero-card">
            <div class="hero-copy">
                <span class="hero-badge">Essai gratuit de 24h offert</span>
                <h1 class="hero-title">Réussissez vos examens avec <span>Timah School</span></h1>
                <p class="hero-text">
                    Plateforme éducative complète avec cours structurés, quiz interactifs et accompagnement personnalisé.
                    Commencez votre essai gratuit dès maintenant.
                </p>

                <div class="hero-actions">
                    @auth
                        <a href="{{ route('student.dashboard') }}" class="btn btn--primary">Accéder à mon espace</a>
                        <a href="{{ route('student.subscription.index') }}" class="btn btn--ghost">Voir les abonnements</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn--primary">Commencer gratuitement</a>
                        <a href="{{ route('login') }}" class="btn btn--ghost">J'ai déjà un compte</a>
                    @endauth
                </div>
            </div>

            <div class="hero-visual hero-visual--safe">
                <div class="hero-visual__panel">
                    <div class="hero-visual__panel-top">
                        <span class="pill pill--blue">Cours</span>
                        <span class="pill pill--green">Quiz</span>
                        <span class="pill pill--violet">Suivi</span>
                    </div>

                    <div class="hero-visual__panel-body">
                        <div class="hero-mini-card hero-mini-card--blue">
                            <strong>Leçons</strong>
                            <span>Contenus organisés</span>
                        </div>
                        <div class="hero-mini-card hero-mini-card--green">
                            <strong>Évaluations</strong>
                            <span>Révisions plus simples</span>
                        </div>
                        <div class="hero-mini-card hero-mini-card--violet">
                            <strong>Progression</strong>
                            <span>Suivi clair de l'élève</span>
                        </div>
                    </div>

                    <div class="hero-visual__stats">
                        <div>
                            <strong>3</strong>
                            <span>Formules</span>
                        </div>
                        <div>
                            <strong>24h</strong>
                            <span>Essai</span>
                        </div>
                        <div>
                            <strong>100%</strong>
                            <span>En ligne</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-panel">
            <div class="stats-panel__grid">
                <div class="stat-box">
                    <div class="stat-box__icon">🎓</div>
                    <div>
                        <span class="stat-box__value">3</span>
                        <span class="stat-box__label">Formules d'abonnement</span>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-box__icon">📚</div>
                    <div>
                        <span class="stat-box__value">Cours</span>
                        <span class="stat-box__label">Contenus pédagogiques organisés</span>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-box__icon">📝</div>
                    <div>
                        <span class="stat-box__value">Quiz</span>
                        <span class="stat-box__label">Évaluations et révisions</span>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-box__icon">📈</div>
                    <div>
                        <span class="stat-box__value">Suivi</span>
                        <span class="stat-box__label">Progression de l'élève</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="classes" class="section">
    <div class="container">
        <h2 class="section-title">Un espace simple pour apprendre efficacement</h2>
        <p class="section-subtitle">
            Timah School met à disposition une interface claire pour consulter les cours, réviser avec des quiz
            et avancer avec une meilleure organisation.
        </p>

        <div class="classes-grid">
            <article class="class-card class-card--blue">
                <div>
                    <div class="class-card__icon">📘</div>
                    <h3 class="class-card__title">Cours clairs</h3>
                    <p class="class-card__meta">Consultez vos leçons dans un espace propre et agréable.</p>
                </div>
                <a href="{{ route('login') }}" class="btn">Découvrir</a>
            </article>

            <article class="class-card class-card--green">
                <div>
                    <div class="class-card__icon">📝</div>
                    <h3 class="class-card__title">Quiz pratiques</h3>
                    <p class="class-card__meta">Révisez et testez vos connaissances plus facilement.</p>
                </div>
                <a href="{{ route('login') }}" class="btn">Essayer</a>
            </article>

            <article class="class-card class-card--indigo">
                <div>
                    <div class="class-card__icon">📊</div>
                    <h3 class="class-card__title">Suivi de progression</h3>
                    <p class="class-card__meta">Visualisez votre avancement et vos activités.</p>
                </div>
                <a href="{{ route('login') }}" class="btn">Voir plus</a>
            </article>

            <article class="class-card class-card--violet">
                <div>
                    <div class="class-card__icon">💳</div>
                    <h3 class="class-card__title">Abonnements simples</h3>
                    <p class="class-card__meta">Choisissez une formule adaptée à votre rythme d'étude.</p>
                </div>
                <a href="{{ route('register') }}" class="btn">S'inscrire</a>
            </article>
        </div>
    </div>
</section>

<section id="pricing" class="section">
    <div class="container">
        <h2 class="section-title">Des formules adaptées aux besoins des élèves</h2>
        <p class="section-subtitle">Choisissez la formule qui correspond le mieux à votre rythme d'apprentissage.</p>

        <div class="subscription-grid" style="margin-top:28px;">
            <article class="plan-card">
                <h3>Essentiel</h3>
                <p class="muted">Pour bien commencer avec les bases.</p>
                <div class="plan-price">3 000 XAF <small>/ mois</small></div>
                <ul class="feature-list">
                    <li><span>✔</span><span>Accès aux cours de la classe</span></li>
                    <li><span>✔</span><span>Quiz de base</span></li>
                    <li><span>✔</span><span>Suivi simple de progression</span></li>
                </ul>
                @auth
                    <a href="{{ route('student.subscription.index') }}" class="btn btn--primary btn--full">Choisir Essentiel</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn--primary btn--full">S'inscrire</a>
                @endauth
            </article>

            <article class="plan-card plan-card--highlight">
                <span class="plan-badge">Le plus choisi</span>
                <h3>Standard</h3>
                <p class="muted">Un accès plus confortable pour réviser davantage.</p>
                <div class="plan-price">5 000 XAF <small>/ mois</small></div>
                <ul class="feature-list">
                    <li><span>✔</span><span>Cours + quiz</span></li>
                    <li><span>✔</span><span>Suivi plus confortable</span></li>
                    <li><span>✔</span><span>Expérience plus complète</span></li>
                </ul>
                @auth
                    <a href="{{ route('student.subscription.index') }}" class="btn btn--primary btn--full">Choisir Standard</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn--primary btn--full">S'inscrire</a>
                @endauth
            </article>

            <article class="plan-card">
                <h3>Premium</h3>
                <p class="muted">Pour un accès plus complet et prioritaire.</p>
                <div class="plan-price">7 000 XAF <small>/ mois</small></div>
                <ul class="feature-list">
                    <li><span>✔</span><span>Cours + quiz + suivi</span></li>
                    <li><span>✔</span><span>Accès plus complet</span></li>
                    <li><span>✔</span><span>Priorité sur les évolutions futures</span></li>
                </ul>
                @auth
                    <a href="{{ route('student.subscription.index') }}" class="btn btn--primary btn--full">Choisir Premium</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn--primary btn--full">S'inscrire</a>
                @endauth
            </article>
        </div>
    </div>
</section>
@endsection
