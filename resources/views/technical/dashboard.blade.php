@extends('layouts.technical')

@section('title', 'Tableau de bord technique')
@section('page_title', 'Espace Responsable Enseignement Technique')
@section('page_subtitle', 'Gestion claire des classes, matières, enseignants, affectations, cours et TD de la section technique.')

@section('content')
<style>
    :root{--ts-blue:#2563eb;--ts-blue-dark:#1e40af;--ts-blue-soft:#eff6ff;--ts-bg:#f5f8ff;--ts-card:#ffffff;--ts-border:#dbeafe;--ts-border-2:#e5edf8;--ts-text:#0f172a;--ts-muted:#64748b;--ts-danger:#b91c1c;--ts-warning:#b45309}.admin-content{background:var(--ts-bg)!important}.technical-page{display:grid;gap:16px}.technical-kpi-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:12px!important;margin:0!important}.technical-kpi{background:var(--ts-card)!important;border:1px solid var(--ts-border)!important;border-radius:18px!important;padding:16px 18px!important;box-shadow:0 8px 22px rgba(37,99,235,.06)!important}.technical-kpi strong{display:block!important;font-size:26px!important;line-height:1!important;color:var(--ts-blue-dark)!important;font-weight:900!important}.technical-kpi span{display:block!important;margin-top:7px!important;color:var(--ts-muted)!important;font-size:13px!important}.ts-actions{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.ts-action{background:var(--ts-card);border:1px solid var(--ts-border);border-radius:18px;overflow:hidden;box-shadow:0 8px 22px rgba(37,99,235,.05)}.ts-action summary{list-style:none;cursor:pointer;padding:15px 16px;color:var(--ts-text);font-weight:900}.ts-action summary::-webkit-details-marker{display:none}.ts-action summary:after{content:'+';float:right;color:var(--ts-blue);font-weight:900}.ts-action[open] summary:after{content:'−'}.ts-action summary span{display:block;font-size:12px;font-weight:700;color:var(--ts-muted);margin-top:4px}.ts-action[open] summary{background:var(--ts-blue-soft);border-bottom:1px solid var(--ts-border)}.ts-form{padding:15px 16px;display:grid;gap:10px}.ts-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.ts-form-grid .full{grid-column:1/-1}.ts-form label{font-size:12px;font-weight:800;color:#475569;display:grid;gap:5px}.ts-form input,.ts-form select,.ts-form textarea,.ts-inline-form input,.ts-inline-form textarea{width:100%;box-sizing:border-box;border:1px solid #cfe0f7;border-radius:12px;background:#fff;color:var(--ts-text);padding:10px 11px;font-size:14px;outline:none}.ts-form input:focus,.ts-form select:focus,.ts-form textarea:focus,.ts-inline-form input:focus,.ts-inline-form textarea:focus{border-color:var(--ts-blue);box-shadow:0 0 0 3px rgba(37,99,235,.10)}.ts-btn{border:0;border-radius:12px;padding:10px 14px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:13px}.ts-btn-primary{background:var(--ts-blue);color:#fff}.ts-btn-light{background:#fff;color:var(--ts-blue-dark);border:1px solid var(--ts-border)}.ts-btn-danger{background:#fff;color:var(--ts-danger);border:1px solid #fecaca}.ts-btn-muted{background:#f8fbff;color:#334155;border:1px solid var(--ts-border-2)}.ts-section{background:var(--ts-card);border:1px solid var(--ts-border);border-radius:20px;overflow:hidden;box-shadow:0 10px 28px rgba(37,99,235,.05)}.ts-section>summary{list-style:none;cursor:pointer;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:14px}.ts-section>summary::-webkit-details-marker{display:none}.ts-section-title{display:grid;gap:3px}.ts-section-title strong{font-size:18px;color:var(--ts-text);letter-spacing:-.02em}.ts-section-title span{font-size:13px;color:var(--ts-muted)}.ts-section-count{background:var(--ts-blue-soft);border:1px solid var(--ts-border);color:var(--ts-blue-dark);border-radius:999px;padding:6px 10px;font-size:12px;font-weight:900;white-space:nowrap}.ts-section[open]>summary{border-bottom:1px solid var(--ts-border);background:linear-gradient(90deg,#ffffff,#f8fbff)}.ts-section-body{padding:16px 20px}.ts-two{display:grid;grid-template-columns:1fr 1fr;gap:14px}.ts-list{display:grid;gap:9px}.ts-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px;align-items:center;background:#fbfdff;border:1px solid var(--ts-border-2);border-radius:15px;padding:13px 14px}.ts-row-main{min-width:0}.ts-row-main strong{display:block;font-size:15px;color:var(--ts-text);line-height:1.25}.ts-row-main span{display:block;font-size:13px;color:var(--ts-muted);margin-top:4px;line-height:1.35}.ts-row-actions{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap}.ts-pill{background:var(--ts-blue-soft);border:1px solid var(--ts-border);color:var(--ts-blue-dark);border-radius:999px;padding:5px 9px;font-size:12px;font-weight:900}.ts-pill-gray{background:#f8fafc;border-color:#e2e8f0;color:#334155}.ts-pill-danger{background:#fff1f2;border-color:#fecdd3;color:var(--ts-danger)}.ts-alert{border:1px solid var(--ts-border-2);border-left:4px solid var(--ts-blue);background:#fbfdff;border-radius:14px;padding:12px 14px}.ts-alert strong{display:block;color:var(--ts-text);font-size:14px}.ts-alert span{display:block;color:var(--ts-muted);font-size:13px;margin-top:3px}.ts-alert-danger{border-left-color:var(--ts-danger)}.ts-alert-warning{border-left-color:var(--ts-warning)}.ts-edit{margin-top:8px}.ts-edit summary{display:inline-flex;cursor:pointer;padding:7px 10px;border-radius:999px;background:#fff;border:1px solid var(--ts-border);color:var(--ts-blue-dark);font-size:12px;font-weight:900;list-style:none}.ts-edit summary::-webkit-details-marker{display:none}.ts-inline-form{margin-top:10px;padding:12px;border-radius:14px;background:#fff;border:1px solid var(--ts-border);display:grid;gap:8px}.ts-empty{border:1px dashed var(--ts-border);border-radius:15px;background:#fbfdff;color:var(--ts-muted);padding:15px;font-size:14px}.ts-subtitle{font-size:14px;color:var(--ts-muted);margin:0 0 12px}.admin-badge{width:auto!important;height:auto!important;min-width:0!important;min-height:0!important;display:inline-flex!important;padding:5px 9px!important;border-radius:999px!important;background:var(--ts-blue-soft)!important;border:1px solid var(--ts-border)!important;color:var(--ts-blue-dark)!important;font-size:12px!important;font-weight:900!important}.technical-panel,.technical-row,.technical-alert{box-shadow:none!important}.btn{box-shadow:none!important}@media(max-width:1180px){.ts-actions{grid-template-columns:repeat(2,minmax(0,1fr))}.technical-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}@media(max-width:980px){.ts-two{grid-template-columns:1fr}}@media(max-width:640px){.technical-kpi-grid,.ts-actions,.ts-form-grid{grid-template-columns:1fr!important}.ts-section>summary{align-items:flex-start}.ts-row{grid-template-columns:1fr}.ts-row-actions{justify-content:flex-start}.ts-section-body{padding:14px}.admin-topbar{gap:14px!important}}
</style>

<div class="technical-page">
    <div class="technical-kpi-grid">
        <div class="technical-kpi"><strong>{{ $stats['classes'] ?? 0 }}</strong><span>classes techniques</span></div>
        <div class="technical-kpi"><strong>{{ $stats['teachers'] ?? 0 }}</strong><span>enseignants affectés</span></div>
        <div class="technical-kpi"><strong>{{ $stats['students'] ?? 0 }}</strong><span>élèves suivis</span></div>
        <div class="technical-kpi"><strong>{{ $alerts->count() }}</strong><span>alertes pédagogiques</span></div>
        <div class="technical-kpi"><strong>{{ $stats['published_courses'] ?? 0 }}</strong><span>cours publiés</span></div>
        <div class="technical-kpi"><strong>{{ $stats['draft_courses'] ?? 0 }}</strong><span>cours en brouillon</span></div>
        <div class="technical-kpi"><strong>{{ $stats['published_td'] ?? 0 }}</strong><span>TD publiés</span></div>
        <div class="technical-kpi"><strong>{{ $stats['submitted_attempts'] ?? 0 }}</strong><span>soumissions TD</span></div>
    </div>

    <details class="ts-section" open>
        <summary><div class="ts-section-title"><strong>Actions rapides</strong><span>Créer et organiser les éléments principaux de la section technique.</span></div><span class="ts-section-count">4 actions</span></summary>
        <div class="ts-section-body">
            <div class="ts-actions" id="technical-management">
                <details class="ts-action">
                    <summary>Créer une classe<span>Ajout automatique en enseignement technique</span></summary>
                    <form method="POST" action="{{ route('technical.classes.store') }}" class="ts-form">
                        @csrf
                        <div class="ts-form-grid">
                            <label>Nom de la classe<input name="name" placeholder="Ex : Première F3" required></label>
                            <label>Ordre<input type="number" name="order" value="0"></label>
                            <label class="full">Description<textarea name="description" rows="2" placeholder="Filière ou spécialité"></textarea></label>
                            <label class="full"><span><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Classe active</span></label>
                        </div>
                        <button class="ts-btn ts-btn-primary">Ajouter</button>
                    </form>
                </details>

                <details class="ts-action">
                    <summary>Créer une matière<span>Matière utilisable dans les affectations</span></summary>
                    <form method="POST" action="{{ route('technical.subjects.store') }}" class="ts-form">
                        @csrf
                        <div class="ts-form-grid">
                            <label>Nom de la matière<input name="name" placeholder="Ex : Circuits électriques" required></label>
                            <label>Ordre<input type="number" name="order" value="0"></label>
                            <label>Icône<input name="icon" placeholder="⚙️"></label>
                            <label>Couleur<input name="color" placeholder="#2563eb"></label>
                            <label class="full">Description<textarea name="description" rows="2"></textarea></label>
                            <label class="full"><span><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Matière active</span></label>
                        </div>
                        <button class="ts-btn ts-btn-primary">Ajouter</button>
                    </form>
                </details>

                <details class="ts-action">
                    <summary>Créer un enseignant<span>Compte enseignant à affecter ensuite</span></summary>
                    <form method="POST" action="{{ route('technical.teachers.store') }}" class="ts-form">
                        @csrf
                        <div class="ts-form-grid">
                            <label>Nom complet<input name="full_name" required></label>
                            <label>Nom d'utilisateur<input name="username" required></label>
                            <label>Email<input type="email" name="email" placeholder="Facultatif"></label>
                            <label>Téléphone<input name="phone" placeholder="Facultatif"></label>
                            <label class="full">Mot de passe initial<input type="text" name="password" required></label>
                        </div>
                        <button class="ts-btn ts-btn-primary">Créer</button>
                    </form>
                </details>

                <details class="ts-action">
                    <summary>Affecter<span>Lier enseignant, classe et matière</span></summary>
                    <form method="POST" action="{{ route('technical.assignments.store') }}" class="ts-form">
                        @csrf
                        <div class="ts-form-grid">
                            <label>Enseignant<select name="teacher_id" required><option value="">Choisir</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name ?? $teacher->name ?? $teacher->username }}</option>@endforeach</select></label>
                            <label>Classe technique<select name="school_class_id" required><option value="">Choisir</option>@foreach($technicalClasses as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
                            <label class="full">Matière<select name="subject_id" required><option value="">Choisir</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></label>
                            <label class="full">Note<input name="notes" placeholder="Facultatif"></label>
                        </div>
                        <button class="ts-btn ts-btn-primary">Affecter</button>
                    </form>
                </details>
            </div>
        </div>
    </details>

    <div class="ts-two">
        <details class="ts-section" open id="technical-alerts">
            <summary><div class="ts-section-title"><strong>Alertes prioritaires</strong><span>Ce qui bloque la section technique.</span></div><span class="ts-section-count">{{ $alerts->count() }}</span></summary>
            <div class="ts-section-body"><div class="ts-list">
                @forelse($alerts as $alert)
                    <div class="ts-alert ts-alert-{{ $alert['level'] ?? 'info' }}"><strong>{{ $alert['title'] }}</strong><span>{{ $alert['message'] }}</span></div>
                @empty
                    <div class="ts-empty">Aucune alerte critique.</div>
                @endforelse
            </div></div>
        </details>

        <details class="ts-section" open>
            <summary><div class="ts-section-title"><strong>État des contenus</strong><span>Cours, TD et participation.</span></div><span class="ts-section-count">résumé</span></summary>
            <div class="ts-section-body"><div class="ts-list">
                <div class="ts-row"><div class="ts-row-main"><strong>Cours</strong><span>{{ $stats['courses'] ?? 0 }} contenu(s) au total</span></div><div class="ts-row-actions">@forelse($courseStatusCounts as $status => $count)<span class="ts-pill">{{ $status }} : {{ $count }}</span>@empty<span class="ts-pill ts-pill-gray">aucun cours</span>@endforelse</div></div>
                <div class="ts-row"><div class="ts-row-main"><strong>TD / contrôles</strong><span>{{ $stats['td'] ?? 0 }} TD au total</span></div><div class="ts-row-actions">@forelse($tdStatusCounts as $status => $count)<span class="ts-pill">{{ $status }} : {{ $count }}</span>@empty<span class="ts-pill ts-pill-gray">aucun TD</span>@endforelse</div></div>
                <div class="ts-row"><div class="ts-row-main"><strong>Participation</strong><span>{{ $stats['attempts'] ?? 0 }} ouverture(s) / tentative(s)</span></div><div class="ts-row-actions"><span class="ts-pill">{{ $stats['submitted_attempts'] ?? 0 }} soumis</span></div></div>
            </div></div>
        </details>
    </div>

    <details class="ts-section" open id="technical-classes">
        <summary><div class="ts-section-title"><strong>Classes et matières</strong><span>Modifier les classes techniques et les matières disponibles.</span></div><span class="ts-section-count">{{ $classRows->count() }} classes · {{ $subjects->count() }} matières</span></summary>
        <div class="ts-section-body"><div class="ts-two">
            <div><p class="ts-subtitle">Classes techniques</p><div class="ts-list">
                @forelse($classRows as $class)
                    <div class="ts-row"><div class="ts-row-main"><strong>{{ $class['name'] }}</strong><span>{{ $class['students'] }} élève(s) · {{ $class['teachers'] }} enseignant(s) · {{ $class['subjects']->count() }} matière(s)</span><span>{{ $class['subjects']->take(4)->implode(', ') ?: 'Aucune matière affectée' }}</span><details class="ts-edit"><summary>Modifier</summary><form method="POST" action="{{ route('technical.classes.update', $class['id']) }}" class="ts-inline-form">@csrf<input name="name" value="{{ $class['name'] }}" required><textarea name="description" rows="2">{{ $class['description'] }}</textarea><input type="number" name="order" value="{{ $class['order'] ?? 0 }}"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked($class['is_active']) style="width:auto;"> Classe active</label><button class="ts-btn ts-btn-primary">Enregistrer</button></form></details></div><div class="ts-row-actions"><span class="ts-pill">{{ $class['published_courses'] }} cours</span><span class="ts-pill">{{ $class['td'] }} TD</span></div></div>
                @empty
                    <div class="ts-empty">Aucune classe technique.</div>
                @endforelse
            </div></div>
            <div id="technical-subjects"><p class="ts-subtitle">Matières</p><div class="ts-list">
                @forelse($subjects as $subject)
                    <div class="ts-row"><div class="ts-row-main"><strong>{{ $subject->name }}</strong><span>{{ $subject->description ?: 'Aucune description' }}</span><details class="ts-edit"><summary>Modifier</summary><form method="POST" action="{{ route('technical.subjects.update', $subject->id) }}" class="ts-inline-form">@csrf<input name="name" value="{{ $subject->name }}" required><textarea name="description" rows="2">{{ $subject->description }}</textarea><input name="icon" value="{{ $subject->icon }}" placeholder="Icône"><input name="color" value="{{ $subject->color }}" placeholder="Couleur"><input type="number" name="order" value="{{ $subject->order ?? 0 }}"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked($subject->is_active ?? true) style="width:auto;"> Matière active</label><button class="ts-btn ts-btn-primary">Enregistrer</button></form></details></div><div class="ts-row-actions"><span class="ts-pill {{ ($subject->is_active ?? true) ? '' : 'ts-pill-danger' }}">{{ ($subject->is_active ?? true) ? 'active' : 'inactive' }}</span></div></div>
                @empty
                    <div class="ts-empty">Aucune matière enregistrée.</div>
                @endforelse
            </div></div>
        </div></div>
    </details>

    <details class="ts-section" id="technical-teachers">
        <summary><div class="ts-section-title"><strong>Enseignants et affectations</strong><span>Suivre les enseignants et leurs rattachements à la section technique.</span></div><span class="ts-section-count">{{ $teacherRows->count() }} enseignants · {{ $assignments->count() }} affectations</span></summary>
        <div class="ts-section-body"><div class="ts-two">
            <div><p class="ts-subtitle">Enseignants techniques</p><div class="ts-list">
                @forelse($teacherRows as $teacher)
                    <div class="ts-row"><div class="ts-row-main"><strong>{{ $teacher['name'] }}</strong><span>{{ $teacher['subjects']->take(3)->implode(', ') ?: 'Matière non définie' }}</span><span>{{ $teacher['classes']->count() }} classe(s) · {{ $teacher['active_assignments'] }} affectation(s) active(s) · {{ $teacher['status'] }}</span></div><div class="ts-row-actions"><span class="ts-pill">{{ $teacher['courses'] }} cours</span><span class="ts-pill">{{ $teacher['td'] }} TD</span><form method="POST" action="{{ route('technical.teachers.toggle', $teacher['id']) }}">@csrf<button class="ts-btn ts-btn-light">{{ $teacher['status'] === 'active' ? 'Désactiver' : 'Réactiver' }}</button></form></div></div>
                @empty
                    <div class="ts-empty">Aucun enseignant affecté aux classes techniques.</div>
                @endforelse
            </div></div>
            <div id="technical-assignments"><p class="ts-subtitle">Affectations</p><div class="ts-list">
                @forelse($assignments as $assignment)
                    <div class="ts-row"><div class="ts-row-main"><strong>{{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? 'Enseignant' }}</strong><span>{{ $assignment->schoolClass->name ?? 'Classe' }} · {{ $assignment->subject->name ?? 'Matière' }}</span><span>{{ $assignment->notes ?: 'Aucune note' }}</span></div><div class="ts-row-actions"><span class="ts-pill {{ $assignment->is_active ? '' : 'ts-pill-danger' }}">{{ $assignment->is_active ? 'active' : 'inactive' }}</span><form method="POST" action="{{ route('technical.assignments.toggle', $assignment->id) }}">@csrf<button class="ts-btn ts-btn-light">{{ $assignment->is_active ? 'Suspendre' : 'Réactiver' }}</button></form><form method="POST" action="{{ route('technical.assignments.delete', $assignment->id) }}" onsubmit="return confirm('Retirer cette affectation ?');">@csrf<button class="ts-btn ts-btn-danger">Retirer</button></form></div></div>
                @empty
                    <div class="ts-empty">Aucune affectation technique enregistrée.</div>
                @endforelse
            </div></div>
        </div></div>
    </details>

    <details class="ts-section" id="technical-courses">
        <summary><div class="ts-section-title"><strong>Cours et TD techniques</strong><span>Publier ou archiver les contenus de la section technique.</span></div><span class="ts-section-count">{{ $recentCourses->count() }} cours · {{ $recentTds->count() }} TD</span></summary>
        <div class="ts-section-body"><div class="ts-two">
            <div><p class="ts-subtitle">Cours techniques</p><div class="ts-list">
                @forelse($recentCourses as $course)
                    <div class="ts-row"><div class="ts-row-main"><strong>{{ $course->title }}</strong><span>{{ $course->schoolClass->name ?? 'Classe non définie' }} · {{ $course->subject->name ?? 'Matière non définie' }}</span><span>Par {{ $course->creator->full_name ?? $course->creator->name ?? 'Auteur non défini' }}</span></div><div class="ts-row-actions"><span class="ts-pill">{{ $course->status ?? 'draft' }}</span>@if(($course->status ?? 'draft') !== \App\Models\Course::STATUS_PUBLISHED)<form method="POST" action="{{ route('technical.courses.publish', $course->id) }}">@csrf<button class="ts-btn ts-btn-primary">Publier</button></form>@endif @if(($course->status ?? '') !== \App\Models\Course::STATUS_ARCHIVED)<form method="POST" action="{{ route('technical.courses.archive', $course->id) }}">@csrf<button class="ts-btn ts-btn-light">Archiver</button></form>@endif</div></div>
                @empty
                    <div class="ts-empty">Aucun cours technique enregistré.</div>
                @endforelse
            </div></div>
            <div id="technical-td"><p class="ts-subtitle">TD techniques</p><div class="ts-list">
                @forelse($recentTds as $td)
                    <div class="ts-row"><div class="ts-row-main"><strong>{{ $td->title }}</strong><span>{{ $td->schoolClass->name ?? 'Classe non définie' }} · {{ $td->subject->name ?? 'Matière non définie' }}</span><span>@if($td->opens_at) Ouverture : {{ $td->opens_at->format('d/m/Y H:i') }} @endif @if($td->closes_at) · Fermeture : {{ $td->closes_at->format('d/m/Y H:i') }} @endif</span></div><div class="ts-row-actions"><span class="ts-pill">{{ $td->status ?? 'draft' }}</span>@if(($td->status ?? 'draft') !== \App\Models\TdSet::STATUS_PUBLISHED)<form method="POST" action="{{ route('technical.td.publish', $td->id) }}">@csrf<button class="ts-btn ts-btn-primary">Publier</button></form>@endif @if(($td->status ?? '') !== \App\Models\TdSet::STATUS_ARCHIVED)<form method="POST" action="{{ route('technical.td.archive', $td->id) }}">@csrf<button class="ts-btn ts-btn-light">Archiver</button></form>@endif</div></div>
                @empty
                    <div class="ts-empty">Aucun TD technique enregistré.</div>
                @endforelse
            </div></div>
        </div></div>
    </details>
</div>
@endsection
