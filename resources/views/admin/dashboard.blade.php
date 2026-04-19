@extends('layouts.admin')

@section('title', 'Tableau de bord admin')
@section('page_title', $dashboardText['page_title'] ?? 'Tableau de bord administrateur')
@section('page_subtitle', $dashboardText['page_subtitle'] ?? 'Vue globale des activités TIMAH ACADEMY : utilisateurs, pédagogique et monétisation.')

@section('content')
<div class="admin-grid admin-grid--stats">
    <article class="admin-stat-card"><span class="admin-stat-card__label">Utilisateurs</span><strong>{{ $stats['users'] }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Enseignants</span><strong>{{ $stats['teachers'] }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">TD publiés</span><strong>{{ $stats['td_published'] }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Questions TD ouvertes</span><strong>{{ $stats['td_questions_open'] }}</strong></article>
</div>

<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section admin-section--hero">
        <div class="admin-section__head">
            <div>
                <h2>{{ $dashboardText['modules_title'] ?? 'Modules de pilotage' }}</h2>
                <p class="admin-muted">{{ $dashboardText['modules_text'] ?? 'Contrôle rapide de l’administration pédagogique et opérationnelle.' }}</p>
            </div>
        </div>

        <div class="admin-module-grid">
            <a class="admin-module-card" href="{{ route('admin.teachers.index') }}">
                <strong>Enseignants</strong>
                <span>Gérer les comptes, statuts et disponibilités.</span>
            </a>

            <a class="admin-module-card" href="{{ route('admin.assignments.index') }}">
                <strong>Affectations</strong>
                <span>Lier enseignant, classe et matière.</span>
            </a>

            <a class="admin-module-card" href="{{ route('admin.courses.index') }}">
                <strong>Cours</strong>
                <span>Superviser le contenu importé et publié.</span>
            </a>

            <a class="admin-module-card" href="{{ route('admin.td.index') }}">
                <strong>TD</strong>
                <span>Vérifier publication, qualité et accès.</span>
            </a>

            <a class="admin-module-card" href="{{ route('admin.homepage.edit') }}">
                <strong>Homepage</strong>
                <span>Modifier hero, sections, FAQ et messages visibles.</span>
            </a>

            <a class="admin-module-card" href="{{ route('admin.settings.edit') }}">
                <strong>Paramètres plateforme</strong>
                <span>Piloter les textes généraux et les dashboards depuis un seul centre.</span>
            </a>
        </div>
    </section>

    <section class="admin-section admin-section--side">
        <div class="admin-section__head">
            <div>
                <h2>{{ $dashboardText['decision_title'] ?? 'Colonne décisionnelle' }}</h2>
                <p class="admin-muted">{{ $dashboardText['decision_text'] ?? 'Actions à fort impact pour gagner du temps au quotidien.' }}</p>
            </div>
        </div>

        <div class="admin-quick-actions">
            <a href="{{ route('admin.users.index') }}" class="admin-quick-action">
                <strong>Utilisateurs</strong>
                <span>Gérer accès et rôles.</span>
            </a>

            <a href="{{ route('admin.plans.index') }}" class="admin-quick-action">
                <strong>Plans</strong>
                <span>Mettre à jour les formules.</span>
            </a>

            <a href="{{ route('admin.payments.index') }}" class="admin-quick-action">
                <strong>Paiements</strong>
                <span>Suivre les transactions.</span>
            </a>

            <a href="{{ route('admin.settings.edit') }}" class="admin-quick-action">
                <strong>Réglages</strong>
                <span>Modifier les textes principaux de la plateforme.</span>
            </a>
        </div>

        <div class="admin-section__head">
            <div>
                <h2>{{ $dashboardText['indicators_title'] ?? 'Indicateurs TD' }}</h2>
                <p class="admin-muted">{{ $dashboardText['indicators_text'] ?? 'État du module TD en temps réel.' }}</p>
            </div>
        </div>

        <div class="admin-info-list">
            <div class="admin-info-item"><strong>{{ $stats['td_total'] }}</strong><span>Total TD</span></div>
            <div class="admin-info-item"><strong>{{ $stats['td_draft'] }}</strong><span>Brouillons</span></div>
            <div class="admin-info-item"><strong>{{ $stats['td_published'] }}</strong><span>Publiés</span></div>
            <div class="admin-info-item"><strong>{{ $stats['td_questions_open'] }}</strong><span>Questions ouvertes</span></div>
        </div>
    </section>
</div>

<div class="admin-grid admin-grid--two">
    <section class="admin-panel">
        <div class="admin-panel__head">
            <h2>{{ $dashboardText['recent_td_title'] ?? 'Derniers TD' }}</h2>
        </div>

        <div class="admin-panel__body admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Auteur</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentTdSets as $td)
                    <tr>
                        <td>{{ $td->title }}</td>
                        <td>{{ $td->schoolClass->name ?? '-' }}</td>
                        <td>{{ $td->subject->name ?? '-' }}</td>
                        <td>{{ $td->author->full_name ?? $td->author->name ?? $td->author->username ?? '-' }}</td>
                        <td><span class="admin-badge">{{ $td->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="admin-empty">Aucun TD pour le moment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__head">
            <h2>{{ $dashboardText['recent_messages_title'] ?? 'Derniers messages enseignants' }}</h2>
        </div>

        <div class="admin-panel__body admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Enseignant</th>
                        <th>Matière</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentTeacherMessages as $message)
                    <tr>
                        <td>{{ $message->student->full_name ?? $message->student->name ?? $message->student->username ?? '-' }}</td>
                        <td>{{ $message->teacher->full_name ?? $message->teacher->name ?? $message->teacher->username ?? '-' }}</td>
                        <td>{{ $message->subject->name ?? '-' }}</td>
                        <td><span class="admin-badge">{{ $message->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="admin-empty">Aucun message pour le moment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
