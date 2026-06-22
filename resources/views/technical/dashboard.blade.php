@extends('layouts.technical')

@section('title', 'Tableau de bord technique')
@section('page_title', 'Espace Responsable Enseignement Technique')
@section('page_subtitle', 'Gestion directe des classes, matieres, enseignants, affectations, cours, TD et alertes de la section technique.')

@section('content')
<style>
    .tech-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.tech-form-grid .full{grid-column:1/-1}.tech-mini-form{margin-top:14px;padding:14px;border-radius:18px;background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.18)}.tech-mini-form label{display:block;font-size:12px;font-weight:800;margin-bottom:5px;color:var(--admin-muted,#64748b)}.tech-mini-form input,.tech-mini-form select,.tech-mini-form textarea{width:100%;box-sizing:border-box;border:1px solid rgba(148,163,184,.35);border-radius:12px;padding:10px;background:var(--admin-card-bg,#fff);color:inherit}.tech-action-row{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}.tech-section-title{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.tech-section-title a{text-decoration:none}.tech-management-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:18px 0}.tech-small{font-size:12px;color:var(--admin-muted,#64748b)}.tech-divider{height:1px;background:rgba(148,163,184,.25);margin:14px 0}.tech-details summary{cursor:pointer;font-weight:900}.tech-actions-panel{display:grid;gap:10px;margin-top:12px}@media(max-width:900px){.tech-management-grid,.tech-form-grid{grid-template-columns:1fr}.tech-section-title{display:block}.tech-action-row{justify-content:flex-start;margin-top:10px}}
</style>

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

<div class="tech-management-grid" id="technical-management">
    <section class="technical-panel">
        <div class="tech-section-title"><div><h2>Créer une classe technique</h2><p>Le niveau est automatiquement fixe sur Enseignement technique.</p></div></div>
        <form method="POST" action="{{ route('technical.classes.store') }}" class="tech-mini-form">
            @csrf
            <div class="tech-form-grid">
                <div><label>Nom de la classe</label><input name="name" placeholder="Ex: Premiere F3" required></div>
                <div><label>Ordre</label><input type="number" name="order" value="0"></div>
                <div class="full"><label>Description</label><textarea name="description" rows="2" placeholder="Filiere, specialite ou remarque"></textarea></div>
                <div class="full"><label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Classe active</label></div>
            </div>
            <div class="admin-actions" style="margin-top:12px;"><button class="btn btn--primary">Ajouter la classe technique</button></div>
        </form>
    </section>

    <section class="technical-panel">
        <div class="tech-section-title"><div><h2>Créer une matière</h2><p>La matière pourra ensuite être affectée aux classes techniques.</p></div></div>
        <form method="POST" action="{{ route('technical.subjects.store') }}" class="tech-mini-form">
            @csrf
            <div class="tech-form-grid">
                <div><label>Nom de la matière</label><input name="name" placeholder="Ex: Installations electriques" required></div>
                <div><label>Ordre</label><input type="number" name="order" value="0"></div>
                <div><label>Icône</label><input name="icon" placeholder="⚙️"></div>
                <div><label>Couleur</label><input name="color" placeholder="#2563eb"></div>
                <div class="full"><label>Description</label><textarea name="description" rows="2"></textarea></div>
                <div class="full"><label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Matière active</label></div>
            </div>
            <div class="admin-actions" style="margin-top:12px;"><button class="btn btn--primary">Ajouter la matière</button></div>
        </form>
    </section>

    <section class="technical-panel">
        <div class="tech-section-title"><div><h2>Créer un enseignant technique</h2><p>Le compte est créé comme enseignant, puis vous l'affectez à une classe et une matière.</p></div></div>
        <form method="POST" action="{{ route('technical.teachers.store') }}" class="tech-mini-form">
            @csrf
            <div class="tech-form-grid">
                <div><label>Nom complet</label><input name="full_name" required></div>
                <div><label>Nom d'utilisateur</label><input name="username" required></div>
                <div><label>Email</label><input type="email" name="email" placeholder="Facultatif"></div>
                <div><label>Téléphone</label><input name="phone" placeholder="Facultatif"></div>
                <div class="full"><label>Mot de passe initial</label><input type="text" name="password" required></div>
            </div>
            <div class="admin-actions" style="margin-top:12px;"><button class="btn btn--primary">Créer l'enseignant</button></div>
        </form>
    </section>

    <section class="technical-panel">
        <div class="tech-section-title"><div><h2>Affecter enseignant / classe / matière</h2><p>C'est cette action qui rattache un enseignant à la section technique.</p></div></div>
        <form method="POST" action="{{ route('technical.assignments.store') }}" class="tech-mini-form">
            @csrf
            <div class="tech-form-grid">
                <div>
                    <label>Enseignant</label>
                    <select name="teacher_id" required>
                        <option value="">Choisir</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name ?? $teacher->name ?? $teacher->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Classe technique</label>
                    <select name="school_class_id" required>
                        <option value="">Choisir</option>
                        @foreach($technicalClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="full">
                    <label>Matière</label>
                    <select name="subject_id" required>
                        <option value="">Choisir</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="full"><label>Note</label><input name="notes" placeholder="Ex: Responsable du module, remplaçant, priorité..."></div>
            </div>
            <div class="admin-actions" style="margin-top:12px;"><button class="btn btn--primary">Enregistrer l'affectation</button></div>
        </form>
    </section>
</div>

<div class="technical-grid">
    <section class="technical-panel" id="technical-alerts">
        <h2>Alertes prioritaires</h2>
        <p>Chaque alerte correspond à une action possible plus bas : affecter un enseignant, publier un cours, publier un TD ou réactiver un compte.</p>
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
        <p>Vue immédiate des contenus publiés et en attente.</p>
        <div class="technical-list">
            <div class="technical-row">
                <div><strong>Cours</strong><span>{{ $stats['courses'] ?? 0 }} contenu(s) au total</span></div>
                <div class="technical-badges">
                    @forelse($courseStatusCounts as $status => $count)<span class="admin-badge">{{ $status }} : {{ $count }}</span>@empty<span class="admin-badge">aucun cours</span>@endforelse
                </div>
            </div>
            <div class="technical-row">
                <div><strong>TD / controles</strong><span>{{ $stats['td'] ?? 0 }} TD au total</span></div>
                <div class="technical-badges">
                    @forelse($tdStatusCounts as $status => $count)<span class="admin-badge">{{ $status }} : {{ $count }}</span>@empty<span class="admin-badge">aucun TD</span>@endforelse
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
        <h2>Gérer les classes techniques</h2>
        <p>Modifier le nom, la description, l'ordre et le statut de chaque classe technique.</p>
        <div class="technical-list">
            @forelse($classRows as $class)
                <div class="technical-row">
                    <div>
                        <strong>{{ $class['name'] }}</strong>
                        <span>{{ $class['students'] }} eleve(s) · {{ $class['teachers'] }} enseignant(s) · {{ $class['subjects']->count() }} matiere(s)</span>
                        <span>{{ $class['subjects']->take(4)->implode(', ') ?: 'Aucune matiere affectee' }}</span>
                        <details class="tech-details tech-mini-form">
                            <summary>Modifier cette classe</summary>
                            <form method="POST" action="{{ route('technical.classes.update', $class['id']) }}" class="tech-actions-panel">
                                @csrf
                                <input name="name" value="{{ $class['name'] }}" required>
                                <textarea name="description" rows="2">{{ $class['description'] }}</textarea>
                                <input type="number" name="order" value="{{ $class['order'] ?? 0 }}">
                                <input type="hidden" name="is_active" value="0">
                                <label><input type="checkbox" name="is_active" value="1" @checked($class['is_active']) style="width:auto;"> Classe active</label>
                                <button class="btn btn--primary">Enregistrer</button>
                            </form>
                        </details>
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

    <section class="technical-panel" id="technical-subjects">
        <h2>Gérer les matières</h2>
        <p>Liste des matières disponibles pour les affectations techniques.</p>
        <div class="technical-list">
            @forelse($subjects as $subject)
                <div class="technical-row">
                    <div>
                        <strong>{{ $subject->name }}</strong>
                        <span>{{ $subject->description ?: 'Aucune description' }}</span>
                        <details class="tech-details tech-mini-form">
                            <summary>Modifier cette matière</summary>
                            <form method="POST" action="{{ route('technical.subjects.update', $subject->id) }}" class="tech-actions-panel">
                                @csrf
                                <input name="name" value="{{ $subject->name }}" required>
                                <textarea name="description" rows="2">{{ $subject->description }}</textarea>
                                <input name="icon" value="{{ $subject->icon }}" placeholder="Icône">
                                <input name="color" value="{{ $subject->color }}" placeholder="Couleur">
                                <input type="number" name="order" value="{{ $subject->order ?? 0 }}">
                                <input type="hidden" name="is_active" value="0">
                                <label><input type="checkbox" name="is_active" value="1" @checked($subject->is_active ?? true) style="width:auto;"> Matière active</label>
                                <button class="btn btn--primary">Enregistrer</button>
                            </form>
                        </details>
                    </div>
                    <div class="technical-badges"><span class="admin-badge">{{ ($subject->is_active ?? true) ? 'active' : 'inactive' }}</span></div>
                </div>
            @empty
                <div class="admin-empty-box">Aucune matière enregistrée.</div>
            @endforelse
        </div>
    </section>
</div>

<div class="technical-grid" style="margin-top:18px;">
    <section class="technical-panel" id="technical-teachers">
        <h2>Gérer les enseignants techniques</h2>
        <p>Activer, désactiver et suivre les enseignants rattachés à la section technique.</p>
        <div class="technical-list">
            @forelse($teacherRows as $teacher)
                <div class="technical-row">
                    <div>
                        <strong>{{ $teacher['name'] }}</strong>
                        <span>{{ $teacher['subjects']->take(3)->implode(', ') ?: 'Matiere non definie' }}</span>
                        <span>{{ $teacher['classes']->count() }} classe(s) · {{ $teacher['active_assignments'] }} affectation(s) active(s) · statut : {{ $teacher['status'] }}</span>
                    </div>
                    <div class="tech-action-row">
                        <span class="admin-badge">{{ $teacher['courses'] }} cours</span>
                        <span class="admin-badge">{{ $teacher['td'] }} TD</span>
                        <form method="POST" action="{{ route('technical.teachers.toggle', $teacher['id']) }}">@csrf<button class="btn btn--ghost">{{ $teacher['status'] === 'active' ? 'Désactiver' : 'Réactiver' }}</button></form>
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun enseignant affecte aux classes techniques.</div>
            @endforelse
        </div>
    </section>

    <section class="technical-panel" id="technical-assignments">
        <h2>Gérer les affectations</h2>
        <p>Retirer ou suspendre une affectation enseignant / classe / matière.</p>
        <div class="technical-list">
            @forelse($assignments as $assignment)
                <div class="technical-row">
                    <div>
                        <strong>{{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? 'Enseignant' }}</strong>
                        <span>{{ $assignment->schoolClass->name ?? 'Classe' }} · {{ $assignment->subject->name ?? 'Matiere' }}</span>
                        <span>{{ $assignment->notes ?: 'Aucune note' }}</span>
                    </div>
                    <div class="tech-action-row">
                        <span class="admin-badge">{{ $assignment->is_active ? 'active' : 'inactive' }}</span>
                        <form method="POST" action="{{ route('technical.assignments.toggle', $assignment->id) }}">@csrf<button class="btn btn--ghost">{{ $assignment->is_active ? 'Suspendre' : 'Réactiver' }}</button></form>
                        <form method="POST" action="{{ route('technical.assignments.delete', $assignment->id) }}" onsubmit="return confirm('Retirer cette affectation ?');">@csrf<button class="btn btn--ghost admin-btn-danger">Retirer</button></form>
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucune affectation technique enregistrée.</div>
            @endforelse
        </div>
    </section>
</div>

<div class="technical-grid" style="margin-top:18px;">
    <section class="technical-panel" id="technical-courses">
        <h2>Gérer les cours techniques</h2>
        <p>Publier ou archiver les cours de la section technique.</p>
        <div class="technical-list">
            @forelse($recentCourses as $course)
                <div class="technical-row">
                    <div>
                        <strong>{{ $course->title }}</strong>
                        <span>{{ $course->schoolClass->name ?? 'Classe non definie' }} · {{ $course->subject->name ?? 'Matiere non definie' }}</span>
                        <span>Par {{ $course->creator->full_name ?? $course->creator->name ?? 'Auteur non defini' }}</span>
                    </div>
                    <div class="tech-action-row">
                        <span class="admin-badge">{{ $course->status ?? 'draft' }}</span>
                        @if(($course->status ?? 'draft') !== \App\Models\Course::STATUS_PUBLISHED)
                            <form method="POST" action="{{ route('technical.courses.publish', $course->id) }}">@csrf<button class="btn btn--primary">Publier</button></form>
                        @endif
                        @if(($course->status ?? '') !== \App\Models\Course::STATUS_ARCHIVED)
                            <form method="POST" action="{{ route('technical.courses.archive', $course->id) }}">@csrf<button class="btn btn--ghost">Archiver</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun cours technique enregistre.</div>
            @endforelse
        </div>
    </section>

    <section class="technical-panel" id="technical-td">
        <h2>Gérer les TD techniques</h2>
        <p>Publier ou archiver les TD de la section technique.</p>
        <div class="technical-list">
            @forelse($recentTds as $td)
                <div class="technical-row">
                    <div>
                        <strong>{{ $td->title }}</strong>
                        <span>{{ $td->schoolClass->name ?? 'Classe non definie' }} · {{ $td->subject->name ?? 'Matiere non definie' }}</span>
                        <span>@if($td->opens_at) Ouverture : {{ $td->opens_at->format('d/m/Y H:i') }} @endif @if($td->closes_at) · Fermeture : {{ $td->closes_at->format('d/m/Y H:i') }} @endif</span>
                    </div>
                    <div class="tech-action-row">
                        <span class="admin-badge">{{ $td->status ?? 'draft' }}</span>
                        @if(($td->status ?? 'draft') !== \App\Models\TdSet::STATUS_PUBLISHED)
                            <form method="POST" action="{{ route('technical.td.publish', $td->id) }}">@csrf<button class="btn btn--primary">Publier</button></form>
                        @endif
                        @if(($td->status ?? '') !== \App\Models\TdSet::STATUS_ARCHIVED)
                            <form method="POST" action="{{ route('technical.td.archive', $td->id) }}">@csrf<button class="btn btn--ghost">Archiver</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun TD technique enregistre.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
