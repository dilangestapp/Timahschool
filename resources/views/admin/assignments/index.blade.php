@extends('layouts.admin')

@section('title', 'Affectations enseignants')
@section('page_title', 'Affectation des enseignants')
@section('page_subtitle', 'Attribuez les enseignants aux classes et matières avec une page plus lisible.')

@section('content')
<div class="admin-compact-page">
    <div class="admin-summary-strip">
        <div class="admin-summary-card"><strong>{{ $assignments->count() }}</strong><span>affectations</span></div>
        <div class="admin-summary-card"><strong>{{ $assignments->where('is_active', 1)->count() }}</strong><span>actives</span></div>
        <div class="admin-summary-card"><strong>{{ $teachers->count() }}</strong><span>enseignants</span></div>
        <div class="admin-summary-card"><strong>{{ $classes->count() }}</strong><span>classes</span></div>
    </div>

    <details class="admin-collapse-box">
        <summary>Nouvelle affectation</summary>
        <div class="admin-collapse-box__body">
            <form method="POST" action="{{ route('admin.assignments.store') }}" class="admin-form">
                @csrf
                <div class="admin-form-grid">
                    <div class="form-group"><label>Enseignant</label><select name="teacher_id" required><option value="">Choisir...</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name ?? $teacher->name ?? $teacher->username }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Classe</label><select name="school_class_id" required><option value="">Choisir...</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Matière</label><select name="subject_id" required><option value="">Choisir...</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
                    <div class="form-group admin-form-grid__full"><label>Notes</label><textarea name="notes" rows="3" placeholder="Ex: Responsable des cours et du suivi de devoirs."></textarea></div>
                </div>
                <div class="admin-actions"><button type="submit" class="btn btn--primary">Enregistrer l'affectation</button></div>
            </form>
        </div>
    </details>

    <section class="admin-list-panel">
        <div class="admin-list-panel__head"><div><h2>Affectations existantes</h2><p>L’enseignant ne voit ensuite que les contenus liés à ses affectations actives.</p></div></div>
        <div class="admin-clean-list">
            @forelse($assignments as $assignment)
                <article class="admin-clean-row">
                    <div class="admin-clean-title"><strong>{{ $assignment->teacher_full_name ?? $assignment->teacher_name ?? $assignment->teacher_username ?? '-' }}</strong><span>{{ $assignment->notes ?: 'Aucune note' }}</span></div>
                    <div class="admin-clean-meta"><strong>{{ $assignment->class_name ?? '-' }}</strong><br><span>{{ $assignment->subject_name ?? '-' }} · {{ $assignment->is_active ? 'active' : 'inactive' }}</span></div>
                    <div class="admin-row-actions">
                        <form method="POST" action="{{ route('admin.assignments.toggle', $assignment->id) }}">@csrf<button type="submit" class="btn btn--ghost">{{ $assignment->is_active ? 'Désactiver' : 'Réactiver' }}</button></form>
                        <form method="POST" action="{{ route('admin.assignments.delete', $assignment->id) }}" onsubmit="return confirm('Supprimer cette affectation ?');">@csrf<button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button></form>
                    </div>
                </article>
            @empty
                <div class="admin-empty-box">Aucune affectation trouvée.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
