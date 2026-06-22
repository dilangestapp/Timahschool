@extends('layouts.technical')

@section('title', 'Tableau de bord technique')
@section('alert_count', $alerts->count())

@section('content')
@php
    $currentUser = auth()->user();
    $userName = $currentUser->full_name ?? $currentUser->name ?? $currentUser->username ?? 'Responsable';
    $todayLabel = ucfirst(now()->translatedFormat('l d F Y'));
@endphp

<div class="resp-dashboard">
    <section class="resp-overview">
        <div class="resp-header">
            <div>
                <h1>Bonjour, {{ $userName }}</h1>
                <p>{{ $todayLabel }} — Section technique</p>
            </div>
            <a href="#technical-classes" class="resp-btn" data-resp-nav="classes"><span>＋</span> Action rapide</a>
        </div>

        <div class="resp-stats-main">
            <div class="resp-stat-main" style="border-left-color:#1a237e;"><div class="resp-stat-main__label">Classes</div><div class="resp-stat-main__value" style="color:#1a237e;">{{ $stats['classes'] ?? 0 }}</div><div class="resp-stat-main__hint">techniques</div></div>
            <div class="resp-stat-main" style="border-left-color:#26a69a;"><div class="resp-stat-main__label">Enseignants</div><div class="resp-stat-main__value" style="color:#26a69a;">{{ $stats['teachers'] ?? 0 }}</div><div class="resp-stat-main__hint">affectés</div></div>
            <div class="resp-stat-main" style="border-left-color:#5c6bc0;"><div class="resp-stat-main__label">Cours publiés</div><div class="resp-stat-main__value" style="color:#5c6bc0;">{{ $stats['published_courses'] ?? 0 }}</div><div class="resp-stat-main__hint">disponibles</div></div>
            <div class="resp-stat-main" style="border-left-color:#ef5350;"><div class="resp-stat-main__label">Alertes</div><div class="resp-stat-main__value" style="color:#ef5350;">{{ $alerts->count() }}</div><div class="resp-stat-main__hint">pédagogiques</div></div>
        </div>

        <div class="resp-stats-mini">
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e8eaf6;color:#5c6bc0;">▤</div><div><div class="resp-mini-value">{{ $stats['draft_courses'] ?? 0 }}</div><div class="resp-mini-label">en brouillon</div></div></div>
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e0f2f1;color:#26a69a;">☑</div><div><div class="resp-mini-value">{{ $stats['published_td'] ?? 0 }}</div><div class="resp-mini-label">TD publiés</div></div></div>
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#fce4ec;color:#e91e63;">⌁</div><div><div class="resp-mini-value">{{ $stats['submitted_attempts'] ?? 0 }}</div><div class="resp-mini-label">soumissions TD</div></div></div>
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#fff3e0;color:#fb8c00;">♙</div><div><div class="resp-mini-value">{{ $stats['students'] ?? 0 }}</div><div class="resp-mini-label">élèves suivis</div></div></div>
        </div>

        <div class="resp-bottom-grid">
            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Classes techniques</span><a href="#technical-classes" class="resp-card__link" data-resp-nav="classes">Voir tout</a></div>
                <div class="resp-list">
                    @forelse($classRows->take(3) as $class)
                        <div class="resp-list-row">
                            <div><strong>{{ $class['name'] }}</strong><small>{{ $class['students'] }} élèves · {{ $class['subjects']->count() }} matières</small></div>
                            <div class="resp-pills"><span class="resp-pill">{{ $class['published_courses'] }} cours</span><span class="resp-pill resp-pill--blue">{{ $class['td'] }} TD</span></div>
                        </div>
                    @empty
                        <div class="resp-empty">Aucune classe technique.</div>
                    @endforelse
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Contenus pédagogiques</span></div>
                <div class="resp-list">
                    <div class="resp-content-row"><span>▧ Cours publiés</span><strong>{{ $stats['published_courses'] ?? 0 }}</strong></div>
                    <div class="resp-content-row"><span>▤ En brouillon</span><strong>{{ $stats['draft_courses'] ?? 0 }}</strong></div>
                    <div class="resp-content-row"><span>☑ TD publiés</span><strong>{{ $stats['published_td'] ?? 0 }}</strong></div>
                    <div class="resp-content-row"><span>⌁ Soumissions</span><strong>{{ $stats['submitted_attempts'] ?? 0 }}</strong></div>
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Actions rapides</span></div>
                <div class="resp-action-list">
                    <a href="#technical-classes" data-resp-nav="classes" class="resp-action-card" style="background:#e8eaf6;color:#3949ab;">▱ Créer une classe</a>
                    <a href="#technical-classes" data-resp-nav="classes" class="resp-action-card" style="background:#e0f2f1;color:#00695c;">▭ Créer une matière</a>
                    <a href="#technical-teachers" data-resp-nav="teachers" class="resp-action-card" style="background:#fff3e0;color:#e65100;">♙ Créer un enseignant</a>
                    <a href="#technical-teachers" data-resp-nav="teachers" class="resp-action-card" style="background:#fce4ec;color:#880e4f;">🔗 Affecter</a>
                </div>
            </div>
        </div>
    </section>

    <section class="resp-panel" id="technical-classes" data-resp-panel="classes">
        <div class="resp-management-card">
            <h2>Classes et matières</h2>
            <p>Créer, modifier ou supprimer les classes techniques et les matières.</p>
            <div class="resp-form-grid">
                <form method="POST" action="{{ route('technical.classes.store') }}" class="resp-inline-form">
                    @csrf
                    <label>Nom de la classe<input name="name" placeholder="Ex : Première F3" required></label>
                    <label>Description<textarea name="description" rows="2" placeholder="Filière ou spécialité"></textarea></label>
                    <label>Ordre<input type="number" name="order" value="0"></label>
                    <label><span><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Classe active</span></label>
                    <button class="resp-btn">Créer la classe</button>
                </form>
                <form method="POST" action="{{ route('technical.subjects.store') }}" class="resp-inline-form">
                    @csrf
                    <label>Nom de la matière<input name="name" placeholder="Ex : Circuits électriques" required></label>
                    <label>Description<textarea name="description" rows="2"></textarea></label>
                    <label>Ordre<input type="number" name="order" value="0"></label>
                    <label>Couleur<input name="color" value="#2563eb"></label>
                    <button class="resp-btn">Créer la matière</button>
                </form>
            </div>
        </div>

        <div class="resp-bottom-grid" style="grid-template-columns:1fr 1fr;">
            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Classes techniques</span></div>
                <div class="resp-list">
                    @forelse($classRows as $class)
                        <div class="resp-list-row">
                            <div>
                                <strong>{{ $class['name'] }}</strong>
                                <small>{{ $class['students'] }} élèves · {{ $class['teachers'] }} enseignants · {{ $class['subjects']->count() }} matières</small>
                                <details class="resp-details"><summary>Modifier</summary><form method="POST" action="{{ route('technical.classes.update', $class['id']) }}" class="resp-inline-form">@csrf<label>Nom<input name="name" value="{{ $class['name'] }}" required></label><label>Description<textarea name="description" rows="2">{{ $class['description'] }}</textarea></label><label>Ordre<input type="number" name="order" value="{{ $class['order'] ?? 0 }}"></label><input type="hidden" name="is_active" value="0"><label><span><input type="checkbox" name="is_active" value="1" @checked($class['is_active']) style="width:auto;"> Classe active</span></label><button class="resp-btn">Enregistrer</button></form></details>
                            </div>
                            <div class="resp-row-actions"><span class="resp-pill">{{ $class['published_courses'] }} cours</span><span class="resp-pill resp-pill--blue">{{ $class['td'] }} TD</span><form method="POST" action="{{ route('technical.classes.delete', $class['id']) }}" onsubmit="return confirm('Supprimer cette classe ?');">@csrf<button class="resp-btn resp-btn--danger">Supprimer</button></form></div>
                        </div>
                    @empty
                        <div class="resp-empty">Aucune classe technique.</div>
                    @endforelse
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Matières</span></div>
                <div class="resp-list">
                    @forelse($subjects as $subject)
                        <div class="resp-list-row">
                            <div><strong>{{ $subject->name }}</strong><small>{{ $subject->description ?: 'Aucune description' }}</small><details class="resp-details"><summary>Modifier</summary><form method="POST" action="{{ route('technical.subjects.update', $subject->id) }}" class="resp-inline-form">@csrf<label>Nom<input name="name" value="{{ $subject->name }}" required></label><label>Description<textarea name="description" rows="2">{{ $subject->description }}</textarea></label><label>Ordre<input type="number" name="order" value="{{ $subject->order ?? 0 }}"></label><label>Couleur<input name="color" value="{{ $subject->color }}"></label><input type="hidden" name="is_active" value="0"><label><span><input type="checkbox" name="is_active" value="1" @checked($subject->is_active ?? true) style="width:auto;"> Matière active</span></label><button class="resp-btn">Enregistrer</button></form></details></div>
                            <div class="resp-row-actions"><span class="resp-pill">{{ ($subject->is_active ?? true) ? 'active' : 'inactive' }}</span><form method="POST" action="{{ route('technical.subjects.delete', $subject->id) }}" onsubmit="return confirm('Supprimer cette matière ?');">@csrf<button class="resp-btn resp-btn--danger">Supprimer</button></form></div>
                        </div>
                    @empty
                        <div class="resp-empty">Aucune matière.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="resp-panel" id="technical-teachers" data-resp-panel="teachers">
        <div class="resp-management-card">
            <h2>Enseignants et affectations</h2>
            <p>Créer un enseignant, l’affecter, suspendre ou supprimer les liens inutiles.</p>
            <div class="resp-form-grid">
                <form method="POST" action="{{ route('technical.teachers.store') }}" class="resp-inline-form">
                    @csrf
                    <label>Nom complet<input name="full_name" required></label><label>Nom d'utilisateur<input name="username" required></label><label>Email<input type="email" name="email" placeholder="Facultatif"></label><label>Téléphone<input name="phone" placeholder="Facultatif"></label><label>Mot de passe<input type="text" name="password" required></label><button class="resp-btn">Créer l'enseignant</button>
                </form>
                <form method="POST" action="{{ route('technical.assignments.store') }}" class="resp-inline-form">
                    @csrf
                    <label>Enseignant<select name="teacher_id" required><option value="">Choisir</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name ?? $teacher->name ?? $teacher->username }}</option>@endforeach</select></label>
                    <label>Classe<select name="school_class_id" required><option value="">Choisir</option>@foreach($technicalClasses as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
                    <label>Matière<select name="subject_id" required><option value="">Choisir</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></label>
                    <label>Note<input name="notes" placeholder="Facultatif"></label><button class="resp-btn">Affecter</button>
                </form>
            </div>
        </div>

        <div class="resp-bottom-grid" style="grid-template-columns:1fr 1fr;">
            <div class="resp-card"><div class="resp-card__head"><span class="resp-card__title">Enseignants techniques</span></div><div class="resp-list">
                @forelse($teacherRows as $teacher)
                    <div class="resp-list-row"><div><strong>{{ $teacher['name'] }}</strong><small>{{ $teacher['subjects']->take(3)->implode(', ') ?: 'Matière non définie' }} · {{ $teacher['classes']->count() }} classe(s)</small></div><div class="resp-row-actions"><span class="resp-pill">{{ $teacher['courses'] }} cours</span><span class="resp-pill resp-pill--blue">{{ $teacher['td'] }} TD</span><form method="POST" action="{{ route('technical.teachers.toggle', $teacher['id']) }}">@csrf<button class="resp-btn resp-btn--light">{{ $teacher['status'] === 'active' ? 'Désactiver' : 'Réactiver' }}</button></form><form method="POST" action="{{ route('technical.teachers.delete', $teacher['id']) }}" onsubmit="return confirm('Supprimer cet enseignant ?');">@csrf<button class="resp-btn resp-btn--danger">Supprimer</button></form></div></div>
                @empty
                    <div class="resp-empty">Aucun enseignant affecté.</div>
                @endforelse
            </div></div>
            <div class="resp-card"><div class="resp-card__head"><span class="resp-card__title">Affectations</span></div><div class="resp-list">
                @forelse($assignments as $assignment)
                    <div class="resp-list-row"><div><strong>{{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? 'Enseignant' }}</strong><small>{{ $assignment->schoolClass->name ?? 'Classe' }} · {{ $assignment->subject->name ?? 'Matière' }} · {{ $assignment->notes ?: 'Aucune note' }}</small></div><div class="resp-row-actions"><span class="resp-pill">{{ $assignment->is_active ? 'active' : 'inactive' }}</span><form method="POST" action="{{ route('technical.assignments.toggle', $assignment->id) }}">@csrf<button class="resp-btn resp-btn--light">{{ $assignment->is_active ? 'Suspendre' : 'Réactiver' }}</button></form><form method="POST" action="{{ route('technical.assignments.delete', $assignment->id) }}" onsubmit="return confirm('Retirer cette affectation ?');">@csrf<button class="resp-btn resp-btn--danger">Retirer</button></form></div></div>
                @empty
                    <div class="resp-empty">Aucune affectation.</div>
                @endforelse
            </div></div>
        </div>
    </section>

    <section class="resp-panel" id="technical-courses" data-resp-panel="courses">
        <div class="resp-card"><div class="resp-card__head"><span class="resp-card__title">Cours techniques</span></div><div class="resp-list">
            @forelse($recentCourses as $course)
                <div class="resp-list-row"><div><strong>{{ $course->title }}</strong><small>{{ $course->schoolClass->name ?? 'Classe non définie' }} · {{ $course->subject->name ?? 'Matière non définie' }} · {{ $course->creator->full_name ?? $course->creator->name ?? 'Auteur non défini' }}</small></div><div class="resp-row-actions"><span class="resp-pill">{{ $course->status ?? 'draft' }}</span>@if(($course->status ?? 'draft') !== \App\Models\Course::STATUS_PUBLISHED)<form method="POST" action="{{ route('technical.courses.publish', $course->id) }}">@csrf<button class="resp-btn">Publier</button></form>@endif @if(($course->status ?? '') !== \App\Models\Course::STATUS_ARCHIVED)<form method="POST" action="{{ route('technical.courses.archive', $course->id) }}">@csrf<button class="resp-btn resp-btn--light">Archiver</button></form>@endif<form method="POST" action="{{ route('technical.courses.delete', $course->id) }}" onsubmit="return confirm('Supprimer ce cours ?');">@csrf<button class="resp-btn resp-btn--danger">Supprimer</button></form></div></div>
            @empty
                <div class="resp-empty">Aucun cours technique.</div>
            @endforelse
        </div></div>
    </section>

    <section class="resp-panel" id="technical-td" data-resp-panel="td">
        <div class="resp-card"><div class="resp-card__head"><span class="resp-card__title">TD techniques</span></div><div class="resp-list">
            @forelse($recentTds as $td)
                <div class="resp-list-row"><div><strong>{{ $td->title }}</strong><small>{{ $td->schoolClass->name ?? 'Classe non définie' }} · {{ $td->subject->name ?? 'Matière non définie' }}</small></div><div class="resp-row-actions"><span class="resp-pill">{{ $td->status ?? 'draft' }}</span>@if(($td->status ?? 'draft') !== \App\Models\TdSet::STATUS_PUBLISHED)<form method="POST" action="{{ route('technical.td.publish', $td->id) }}">@csrf<button class="resp-btn">Publier</button></form>@endif @if(($td->status ?? '') !== \App\Models\TdSet::STATUS_ARCHIVED)<form method="POST" action="{{ route('technical.td.archive', $td->id) }}">@csrf<button class="resp-btn resp-btn--light">Archiver</button></form>@endif<form method="POST" action="{{ route('technical.td.delete', $td->id) }}" onsubmit="return confirm('Supprimer ce TD ?');">@csrf<button class="resp-btn resp-btn--danger">Supprimer</button></form></div></div>
            @empty
                <div class="resp-empty">Aucun TD technique.</div>
            @endforelse
        </div></div>
    </section>

    <section class="resp-panel" id="technical-alerts" data-resp-panel="alerts">
        <div class="resp-card"><div class="resp-card__head"><span class="resp-card__title">Alertes prioritaires</span></div><div class="resp-list">
            @forelse($alerts as $alert)
                <div class="resp-alert-row"><strong>{{ $alert['title'] }}</strong><span>{{ $alert['message'] }}</span></div>
            @empty
                <div class="resp-empty">Aucune alerte critique.</div>
            @endforelse
        </div></div>
    </section>
</div>
@endsection
