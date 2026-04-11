@extends('layouts.admin')

@section('title', 'Cours')
@section('page_title', 'Pilotage des cours')
@section('page_subtitle', 'Contrôlez les brouillons, publications et archives produits par les enseignants.')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head admin-section__head--stack">
            <div>
                <h2>Filtres de contrôle</h2>
                <p class="admin-muted">Rechercher un cours par titre, statut, classe ou matière.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.courses.index') }}" class="admin-form">
            <div class="admin-form-grid">
                <div class="form-group"><label>Recherche</label><input type="text" name="q" value="{{ $search }}" placeholder="Titre, description..."></div>
                <div class="form-group"><label>Statut</label><select name="status"><option value="">Tous</option><option value="draft" @selected($status==='draft')>Brouillon</option><option value="published" @selected($status==='published')>Publié</option><option value="archived" @selected($status==='archived')>Archivé</option></select></div>
                <div class="form-group"><label>Classe</label><select name="class_id"><option value="0">Toutes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>@endforeach</select></div>
                <div class="form-group"><label>Matière</label><select name="subject_id"><option value="0">Toutes</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($subjectId == $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
            </div>
            <div class="admin-actions"><button type="submit" class="btn btn--primary">Filtrer les cours</button></div>
        </form>
    </section>
    <section class="admin-section">
        <div class="admin-section__head"><h2>Résumé</h2></div>
        <div class="admin-info-list">
            <div class="admin-info-item"><strong>{{ $summary['total'] }}</strong><span>cours affichés</span></div>
            <div class="admin-info-item"><strong>{{ $summary['published'] }}</strong><span>publiés</span></div>
            <div class="admin-info-item"><strong>{{ $summary['draft'] }}</strong><span>brouillons</span></div>
            <div class="admin-info-item"><strong>{{ $summary['archived'] }}</strong><span>archivés</span></div>
        </div>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Liste des cours</h2></div>
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>courses</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-card-grid">
            @forelse($courses as $course)
                <div class="admin-manage-card">
                    <form method="POST" action="{{ route('admin.courses.update', $course->id) }}" class="admin-form">
                        @csrf
                        <div class="admin-form-grid">
                            <div class="form-group admin-form-grid__full"><label>Titre</label><input type="text" name="title" value="{{ $course->title }}" required></div>
                            <div class="form-group"><label>Classe</label><select name="school_class_id">@foreach($classes as $class)<option value="{{ $class->id }}" @selected(($course->school_class_id ?? 0) == $class->id)>{{ $class->name }}</option>@endforeach</select></div>
                            <div class="form-group"><label>Matière</label><select name="subject_id">@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(($course->subject_id ?? 0) == $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
                            <div class="form-group"><label>Ordre</label><input type="number" name="order" value="{{ $course->order ?? 0 }}" min="0"></div>
                            <div class="form-group"><label>Statut</label><select name="status"><option value="draft" @selected(($course->status ?? '')==='draft')>Brouillon</option><option value="published" @selected(($course->status ?? '')==='published')>Publié</option><option value="archived" @selected(($course->status ?? '')==='archived')>Archivé</option></select></div>
                            <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3">{{ $course->description ?? '' }}</textarea></div>
                            <div class="form-group admin-form-grid__full"><label>Objectifs</label><textarea name="objectives" rows="3">{{ $course->objectives ?? '' }}</textarea></div>
                        </div>
                        <div class="admin-actions admin-actions--spread">
                            <span class="admin-badge">{{ $course->schoolClass->name ?? '—' }} · {{ $course->subject->name ?? '—' }}</span>
                            <div class="admin-actions">
                                <button type="submit" class="btn btn--primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                    <div class="admin-actions admin-actions--spread admin-actions--wrap">
                        <form method="POST" action="{{ route('admin.courses.publish', $course->id) }}">@csrf<button type="submit" class="btn btn--ghost">Publier</button></form>
                        <form method="POST" action="{{ route('admin.courses.archive', $course->id) }}">@csrf<button type="submit" class="btn btn--ghost">Archiver</button></form>
                        <form method="POST" action="{{ route('admin.courses.delete', $course->id) }}" onsubmit="return confirm('Supprimer ce cours ?');">@csrf<button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button></form>
                    </div>
                </div>
            @empty
                <div class="admin-empty-box">Aucun cours trouvé.</div>
            @endforelse
        </div>
    @endif
</section>
@endsection
