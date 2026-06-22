@extends('layouts.technical')

@section('title', 'Tableau de bord technique')
@section('page_title', 'Espace Responsable Enseignement Technique')
@section('page_subtitle', 'Vue de controle des filieres, classes, enseignants, cours, TD et alertes de la section technique.')

@section('content')
<div class="technical-kpi-grid">
    <div class="technical-kpi"><strong>{{ $stats['classes'] ?? 0 }}</strong><span>classes techniques</span></div>
    <div class="technical-kpi"><strong>{{ $stats['teachers'] ?? 0 }}</strong><span>enseignants affectes</span></div>
    <div class="technical-kpi"><strong>{{ $stats['students'] ?? 0 }}</strong><span>eleves suivis</span></div>
    <div class="technical-kpi"><strong>{{ $alerts->count() }}</strong><span>alertes pedagogiques</span></div>
    <div class="technical-kpi"><strong>{{ $stats['published_courses'] ?? 0 }}</strong><span>cours publies</span></div>
    <div class="technical-kpi"><strong>{{ $stats['draft_courses'] ?? 0 }}</strong><span>cours en brouillon</span></div>
    <div class="technical-kpi"><strong>{{ $stats['published_td'] ?? 0 }}</strong><span>TD publies</span></div>
    <div class="technical-kpi"><strong>{{ $stats['submitted_attempts'] ?? 0 }}</strong><span>soumissions TD</span></div>
</div>

<div class="technical-grid">
    <section class="technical-panel" id="technical-alerts">
        <h2>Alertes prioritaires</h2>
        <p>Points a verifier rapidement pour garder la section technique active.</p>
        <div class="technical-list">
            @forelse($alerts as $alert)
                <div class="technical-alert technical-alert--{{ $alert['level'] ?? 'info' }}">
                    <strong>{{ $alert['title'] }}</strong>
                    <span>{{ $alert['message'] }}</span>
                </div>
            @empty
                <div class="technical-alert technical-alert--info">
                    <strong>Aucune alerte critique</strong>
                    <span>Les contenus techniques actuellement configures ne presentent pas de blocage visible.</span>
                </div>
            @endforelse
        </div>
    </section>

    <section class="technical-panel">
        <h2>Statuts rapides</h2>
        <p>Lecture immediate des contenus publies et en attente.</p>
        <div class="technical-list">
            <div class="technical-row">
                <div><strong>Cours</strong><span>{{ $stats['courses'] ?? 0 }} contenu(s) au total</span></div>
                <div class="technical-badges">
                    @forelse($courseStatusCounts as $status => $count)
                        <span class="admin-badge">{{ $status }} : {{ $count }}</span>
                    @empty
                        <span class="admin-badge">aucun cours</span>
                    @endforelse
                </div>
            </div>
            <div class="technical-row">
                <div><strong>TD / controles</strong><span>{{ $stats['td'] ?? 0 }} TD au total</span></div>
                <div class="technical-badges">
                    @forelse($tdStatusCounts as $status => $count)
                        <span class="admin-badge">{{ $status }} : {{ $count }}</span>
                    @empty
                        <span class="admin-badge">aucun TD</span>
                    @endforelse
                </div>
            </div>
            <div class="technical-row">
                <div><strong>Participation</strong><span>{{ $stats['attempts'] ?? 0 }} ouverture(s) / tentative(s) TD</span></div>
                <div class="technical-badges"><span class="admin-badge">{{ $stats['submitted_attempts'] ?? 0 }} soumis</span></div>
            </div>
        </div>
    </section>
</div>

<div class="technical-grid" style="margin-top:18px;">
    <section class="technical-panel" id="technical-classes">
        <h2>Classes techniques</h2>
        <p>Suivi par classe : eleves, enseignants, matieres, cours et TD.</p>
        <div class="technical-list">
            @forelse($classRows as $class)
                <div class="technical-row">
                    <div>
                        <strong>{{ $class['name'] }}</strong>
                        <span>{{ $class['students'] }} eleve(s) · {{ $class['teachers'] }} enseignant(s) · {{ $class['subjects']->count() }} matiere(s)</span>
                        <span>{{ $class['subjects']->take(4)->implode(', ') ?: 'Aucune matiere affectee' }}</span>
                    </div>
                    <div class="technical-badges">
                        <span class="admin-badge">{{ $class['published_courses'] }} cours</span>
                        <span class="admin-badge">{{ $class['td'] }} TD</span>
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucune classe marquee Enseignement technique pour le moment.</div>
            @endforelse
        </div>
    </section>

    <section class="technical-panel" id="technical-teachers">
        <h2>Enseignants techniques</h2>
        <p>Controle de l'activite des enseignants rattaches aux classes techniques.</p>
        <div class="technical-list">
            @forelse($teacherRows as $teacher)
                <div class="technical-row">
                    <div>
                        <strong>{{ $teacher['name'] }}</strong>
                        <span>{{ $teacher['subjects']->take(3)->implode(', ') ?: 'Matiere non definie' }}</span>
                        <span>{{ $teacher['classes']->count() }} classe(s) · statut : {{ $teacher['status'] }}</span>
                    </div>
                    <div class="technical-badges">
                        <span class="admin-badge">{{ $teacher['courses'] }} cours</span>
                        <span class="admin-badge">{{ $teacher['td'] }} TD</span>
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun enseignant affecte aux classes techniques.</div>
            @endforelse
        </div>
    </section>
</div>

<div class="technical-grid" style="margin-top:18px;">
    <section class="technical-panel" id="technical-courses">
        <h2>Derniers cours techniques</h2>
        <p>Contenus recents a suivre, publier, relancer ou verifier.</p>
        <div class="technical-list">
            @forelse($recentCourses as $course)
                <div class="technical-row">
                    <div>
                        <strong>{{ $course->title }}</strong>
                        <span>{{ $course->schoolClass->name ?? 'Classe non definie' }} · {{ $course->subject->name ?? 'Matiere non definie' }}</span>
                        <span>Par {{ $course->creator->full_name ?? $course->creator->name ?? 'Auteur non defini' }}</span>
                    </div>
                    <div class="technical-badges"><span class="admin-badge">{{ $course->status ?? 'draft' }}</span></div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun cours technique enregistre.</div>
            @endforelse
        </div>
    </section>

    <section class="technical-panel" id="technical-td">
        <h2>Derniers TD techniques</h2>
        <p>Controle des TD programmes, publies, fermes ou a corriger.</p>
        <div class="technical-list">
            @forelse($recentTds as $td)
                <div class="technical-row">
                    <div>
                        <strong>{{ $td->title }}</strong>
                        <span>{{ $td->schoolClass->name ?? 'Classe non definie' }} · {{ $td->subject->name ?? 'Matiere non definie' }}</span>
                        <span>
                            @if($td->opens_at) Ouverture : {{ $td->opens_at->format('d/m/Y H:i') }} @endif
                            @if($td->closes_at) · Fermeture : {{ $td->closes_at->format('d/m/Y H:i') }} @endif
                        </span>
                    </div>
                    <div class="technical-badges"><span class="admin-badge">{{ $td->status ?? 'draft' }}</span></div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun TD technique enregistre.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
