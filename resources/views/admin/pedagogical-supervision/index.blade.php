@extends('layouts.admin')

@section('title', 'Administration pédagogique')
@section('page_title', 'Administration pédagogique')
@section('page_subtitle', 'Suivi des enseignants, cours, TD, questions, départements et responsabilités sans hiérarchie lourde.')

@section('content')
@if(!$schemaReady)
    <section class="admin-section">
        <div class="admin-section__head">
            <div>
                <h2>Migration nécessaire</h2>
                <p class="admin-muted">Les tables de supervision ne sont pas encore installées sur le serveur Contabo. Déploie la dernière version depuis GitHub puis lance les migrations Laravel sur le VPS.</p>
            </div>
        </div>
        <div class="admin-alert admin-alert--warning" style="margin-top:12px;">
            Commandes serveur : cd /var/www/timahacademy ; git pull origin main ; php artisan migrate --force ; php artisan optimize:clear
        </div>
    </section>
@else
<style>
    .supervision-note{border:1px solid #bfdbfe;background:#eff6ff;border-radius:18px;padding:16px;margin-bottom:18px;color:#0f172a}
    .supervision-forms{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:18px}
    .supervision-form{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.06)}
    .supervision-form h3{margin-top:0}.supervision-form label{display:block;font-weight:800;font-size:13px;margin-top:10px;color:#334155}
    .supervision-form input,.supervision-form select,.supervision-form textarea{width:100%;box-sizing:border-box;margin-top:5px;border:1px solid #cbd5e1;border-radius:12px;padding:10px;background:#fff;color:#0f172a}.supervision-form textarea{min-height:78px}
    .supervision-list{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}.supervision-item{border-top:1px solid #e5e7eb;padding:12px 0}.supervision-item strong{display:block}.supervision-item small{display:inline-block;background:#eef2ff;color:#3730a3;border-radius:999px;padding:4px 8px;margin-top:4px}.severity-warning{background:#fff7ed!important;color:#c2410c!important}.severity-urgent{background:#fef2f2!important;color:#b91c1c!important}@media(max-width:980px){.supervision-forms,.supervision-list{grid-template-columns:1fr}}
</style>

<div class="admin-grid admin-grid--stats">
    <article class="admin-stat-card"><span class="admin-stat-card__label">Types d’enseignement</span><strong>{{ $stats['divisions'] ?? 0 }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Départements / filières</span><strong>{{ $stats['departments'] ?? 0 }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Responsables actifs</span><strong>{{ $stats['active_responsibilities'] ?? 0 }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Notes ouvertes</span><strong>{{ $stats['open_notes'] ?? 0 }}</strong></article>
</div>

<div class="admin-grid admin-grid--stats">
    <article class="admin-stat-card"><span class="admin-stat-card__label">Cours publiés</span><strong>{{ $stats['courses_published'] ?? 0 }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Cours brouillons</span><strong>{{ $stats['courses_draft'] ?? 0 }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">TD publiés</span><strong>{{ $stats['td_published'] ?? 0 }}</strong></article>
    <article class="admin-stat-card"><span class="admin-stat-card__label">Questions ouvertes</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong></article>
</div>

<div class="supervision-note">
    <strong>Logique retenue :</strong>
    un coordinateur général suit toute la plateforme, des responsables suivent chaque type d’enseignement, puis des responsables de département ou de filière vérifient les cours, TD, réponses aux élèves, programmes et retards. Leur rôle est de contrôler, relancer et signaler, sans créer une administration lourde.
</div>

<div class="supervision-forms">
    <form class="supervision-form" method="POST" action="{{ route('admin.organization.divisions.store') }}">
        @csrf
        <h3>Type d’enseignement</h3>
        <label>Nom<input name="name" required placeholder="Enseignement technique"></label>
        <label>Type
            <select name="type">
                <option value="general">Général</option><option value="technical">Technique</option><option value="anglophone">Anglophone</option><option value="primary">Primaire</option><option value="exam">Classes d’examen</option>
            </select>
        </label>
        <label>Ordre<input type="number" name="order" value="0"></label>
        <label>Description<textarea name="description"></textarea></label>
        <button type="submit" class="btn btn--primary">Ajouter</button>
    </form>

    <form class="supervision-form" method="POST" action="{{ route('admin.organization.departments.store') }}">
        @csrf
        <h3>Département / filière</h3>
        <label>Nom<input name="name" required placeholder="Département électrotechnique"></label>
        <label>Type d’enseignement<select name="teaching_division_id"><option value="">Non classé</option>@foreach($divisions as $division)<option value="{{ $division->id }}">{{ $division->name }}</option>@endforeach</select></label>
        <label>Matière liée<select name="subject_id"><option value="">Aucune</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></label>
        <label>Classe liée<select name="school_class_id"><option value="">Aucune</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></label>
        <label>Code<input name="code" placeholder="ELT"></label>
        <label>Description<textarea name="description"></textarea></label>
        <button type="submit" class="btn btn--primary">Ajouter</button>
    </form>

    <form class="supervision-form" method="POST" action="{{ route('admin.organization.responsibilities.store') }}">
        @csrf
        <h3>Attribuer une responsabilité</h3>
        <label>Personne<select name="user_id" required><option value="">Choisir</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->full_name ?: ($user->name ?: $user->username) }} {{ $user->phone }}</option>@endforeach</select></label>
        <label>Titre<input name="role_title" required placeholder="Coordinateur général"></label>
        <label>Portée<select name="scope_type"><option value="platform">Plateforme entière</option><option value="division">Type d’enseignement</option><option value="department">Département / filière</option></select></label>
        <label>Type d’enseignement<select name="teaching_division_id"><option value="">Aucun</option>@foreach($divisions as $division)<option value="{{ $division->id }}">{{ $division->name }}</option>@endforeach</select></label>
        <label>Département<select name="teaching_department_id"><option value="">Aucun</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></label>
        <label>Peut valider le contenu<select name="can_validate_content"><option value="0">Non</option><option value="1">Oui</option></select></label>
        <label>Notes<textarea name="notes"></textarea></label>
        <button type="submit" class="btn btn--primary">Attribuer</button>
    </form>
</div>

<section class="admin-section">
    <div class="admin-section__head"><div><h2>Note de suivi / relance</h2><p class="admin-muted">Permet d’attirer l’attention sur un retard, une question non traitée, un TD manquant ou un enseignant inactif.</p></div></div>
    <form class="supervision-form" method="POST" action="{{ route('admin.organization.notes.store') }}" style="box-shadow:none;max-width:none">
        @csrf
        <label>Responsabilité<select name="responsibility_id"><option value="">Aucune</option>@foreach($responsibilities as $responsibility)<option value="{{ $responsibility->id }}">{{ $responsibility->role_title }} — {{ $responsibility->full_name ?: ($responsibility->name ?: $responsibility->username) }}</option>@endforeach</select></label>
        <label>Personne ciblée<select name="target_user_id"><option value="">Aucune</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->full_name ?: ($user->name ?: $user->username) }}</option>@endforeach</select></label>
        <label>Type d’enseignement<select name="teaching_division_id"><option value="">Aucun</option>@foreach($divisions as $division)<option value="{{ $division->id }}">{{ $division->name }}</option>@endforeach</select></label>
        <label>Département<select name="teaching_department_id"><option value="">Aucun</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></label>
        <label>Titre<input name="title" required placeholder="Retard sur les TD d’électrotechnique"></label>
        <label>Niveau<select name="severity"><option value="info">Info</option><option value="warning">Attention</option><option value="urgent">Urgent</option></select></label>
        <label>Message<textarea name="message"></textarea></label>
        <button type="submit" class="btn btn--primary">Enregistrer la note</button>
    </form>
</section>

<div class="supervision-list">
    <section class="admin-panel"><div class="admin-panel__head"><h2>Types d’enseignement</h2></div><div class="admin-panel__body">@forelse($divisions as $division)<div class="supervision-item"><strong>{{ $division->name }}</strong><small>{{ $division->type }}</small><p class="admin-muted">{{ $division->description ?: 'Aucune description.' }}</p></div>@empty<p class="admin-empty">Aucun type d’enseignement.</p>@endforelse</div></section>
    <section class="admin-panel"><div class="admin-panel__head"><h2>Départements / filières</h2></div><div class="admin-panel__body">@forelse($departments as $department)<div class="supervision-item"><strong>{{ $department->name }}</strong><small>{{ $department->division_name ?? 'Non classé' }}</small><p class="admin-muted">{{ $department->description ?: 'Aucune description.' }}</p></div>@empty<p class="admin-empty">Aucun département.</p>@endforelse</div></section>
</div>

<div class="admin-grid admin-grid--two">
    <section class="admin-panel"><div class="admin-panel__head"><h2>Responsables affectés</h2></div><div class="admin-panel__body admin-table-wrap"><table class="admin-table"><thead><tr><th>Personne</th><th>Responsabilité</th><th>Zone</th><th>Statut</th></tr></thead><tbody>@forelse($responsibilities as $responsibility)<tr><td>{{ $responsibility->full_name ?: ($responsibility->name ?: $responsibility->username) }}</td><td>{{ $responsibility->role_title }}</td><td>{{ $responsibility->department_name ?: ($responsibility->division_name ?: 'Plateforme entière') }}</td><td><span class="admin-badge">{{ $responsibility->is_active ? 'Actif' : 'Inactif' }}</span></td></tr>@empty<tr><td colspan="4" class="admin-empty">Aucune responsabilité attribuée.</td></tr>@endforelse</tbody></table></div></section>
    <section class="admin-panel"><div class="admin-panel__head"><h2>Notes de suivi</h2></div><div class="admin-panel__body admin-table-wrap"><table class="admin-table"><thead><tr><th>Objet</th><th>Cible</th><th>Niveau</th><th>Statut</th></tr></thead><tbody>@forelse($notes as $note)<tr><td>{{ $note->title }}</td><td>{{ $note->target_name ?: ($note->department_name ?: ($note->division_name ?: 'Plateforme')) }}</td><td><span class="admin-badge severity-{{ $note->severity }}">{{ $note->severity }}</span></td><td>{{ $note->status }}</td></tr>@empty<tr><td colspan="4" class="admin-empty">Aucune note de suivi.</td></tr>@endforelse</tbody></table></div></section>
</div>

<section class="admin-panel" style="margin-top:18px">
    <div class="admin-panel__head"><h2>Rapport par département / filière</h2></div>
    <div class="admin-panel__body admin-table-wrap"><table class="admin-table"><thead><tr><th>Département</th><th>Cours</th><th>TD</th><th>Questions ouvertes</th></tr></thead><tbody>@forelse($departmentReports as $report)<tr><td>{{ $report['department']->name }}</td><td>{{ $report['courses'] }}</td><td>{{ $report['td'] }}</td><td>{{ $report['open_questions'] }}</td></tr>@empty<tr><td colspan="4" class="admin-empty">Aucun rapport disponible.</td></tr>@endforelse</tbody></table></div>
</section>
@endif
@endsection
