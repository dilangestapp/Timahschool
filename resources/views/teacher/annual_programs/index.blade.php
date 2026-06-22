@extends('layouts.teacher')

@section('title', 'Programme annuel')
@section('page_title', 'Programme annuel')
@section('page_subtitle', 'Planifiez les chapitres de l’année par classe et matière, puis rendez le programme visible aux élèves, parents et responsables.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Créer un programme annuel</h2>
            <p class="teacher-muted">Un programme publié sert de référence pour le suivi pédagogique annuel.</p>
        </div>
    </div>

    @if($migrationMissing ?? false)
        <div class="teacher-alert teacher-alert--error">Le module Programme annuel sera actif après lancement des migrations sur le serveur.</div>
    @endif

    @if($assignments->isEmpty())
        <div class="teacher-empty-state"><strong>Aucune affectation active.</strong><p>Demandez à l’administration de vous affecter une classe et une matière.</p></div>
    @else
        <form method="POST" action="{{ route('teacher.annual-programs.store') }}" class="teacher-form-card">
            @csrf
            <div class="teacher-form-grid teacher-form-grid--two">
                <div class="teacher-form-group">
                    <label>Affectation</label>
                    <select name="teacher_assignment_id" class="teacher-select" required>
                        <option value="">Choisir...</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}">{{ $assignment->schoolClass->name ?? 'Classe' }} — {{ $assignment->subject->name ?? 'Matière' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="teacher-form-group">
                    <label>Année scolaire</label>
                    <input name="school_year" class="teacher-input" value="{{ now()->year }}-{{ now()->addYear()->year }}" required>
                </div>
                <div class="teacher-form-group teacher-form-group--full">
                    <label>Titre</label>
                    <input name="title" class="teacher-input" placeholder="Programme annuel de Littérature - 3ème" required>
                </div>
                <div class="teacher-form-group teacher-form-group--full">
                    <label>Description</label>
                    <textarea name="description" class="teacher-textarea" rows="3"></textarea>
                </div>
                <div class="teacher-form-group">
                    <label>Statut</label>
                    <select name="status" class="teacher-select"><option value="draft">Brouillon</option><option value="published">Publié</option></select>
                </div>
            </div>
            <div class="teacher-form-actions"><button class="teacher-btn teacher-btn--primary">Créer le programme</button></div>
        </form>
    @endif
</section>

<section class="teacher-section">
    <div class="teacher-section__head"><div><h2>Mes programmes annuels</h2><p class="teacher-muted">Ajoutez les chapitres, liez des cours et TD, puis marquez l’avancement.</p></div></div>
    <div class="teacher-course-grid">
        @forelse($programs as $program)
            <article class="teacher-course-card">
                <div class="teacher-course-card__top">
                    <div>
                        <div class="teacher-course-card__badges"><span class="teacher-status teacher-status--{{ $program->status }}">{{ $program->status === 'published' ? 'Publié' : ($program->status === 'archived' ? 'Archivé' : 'Brouillon') }}</span><span class="teacher-pill">{{ $program->school_year }}</span></div>
                        <h3>{{ $program->title }}</h3>
                        <p class="teacher-muted">{{ $program->class_name ?? '-' }} — {{ $program->subject_name ?? '-' }}</p>
                    </div>
                </div>
                @if($program->description)<p class="teacher-course-card__excerpt">{{ $program->description }}</p>@endif

                <div class="teacher-form-card" style="margin:12px 0;">
                    <strong>Ajouter un chapitre</strong>
                    <form method="POST" action="{{ route('teacher.annual-programs.items.store', $program->id) }}" class="teacher-form-grid teacher-form-grid--two" style="margin-top:10px;">
                        @csrf
                        <div class="teacher-form-group"><label>Période</label><input name="period_label" class="teacher-input" placeholder="Trimestre 1 / Séquence 1"></div>
                        <div class="teacher-form-group"><label>Ordre</label><input type="number" name="order" class="teacher-input" value="0"></div>
                        <div class="teacher-form-group teacher-form-group--full"><label>Chapitre</label><input name="chapter_title" class="teacher-input" required></div>
                        <div class="teacher-form-group teacher-form-group--full"><label>Objectifs</label><textarea name="objectives" class="teacher-textarea" rows="2"></textarea></div>
                        <div class="teacher-form-group"><label>Cours lié</label><select name="course_id" class="teacher-select"><option value="">Aucun</option>@foreach($courses->where('school_class_id', $program->school_class_id)->where('subject_id', $program->subject_id) as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></div>
                        <div class="teacher-form-group"><label>TD lié</label><select name="td_set_id" class="teacher-select"><option value="">Aucun</option>@foreach($tdSets->where('school_class_id', $program->school_class_id)->where('subject_id', $program->subject_id) as $td)<option value="{{ $td->id }}">{{ $td->title }}</option>@endforeach</select></div>
                        <div class="teacher-form-group"><label>Début</label><input type="date" name="starts_on" class="teacher-input"></div>
                        <div class="teacher-form-group"><label>Fin</label><input type="date" name="ends_on" class="teacher-input"></div>
                        <div class="teacher-form-group"><label>Statut</label><select name="status" class="teacher-select"><option value="planned">Prévu</option><option value="in_progress">En cours</option><option value="completed">Terminé</option><option value="late">En retard</option></select></div>
                        <div class="teacher-form-actions"><button class="teacher-btn teacher-btn--primary">Ajouter</button></div>
                    </form>
                </div>

                <div class="teacher-course-list">
                    @forelse($program->items as $item)
                        <div class="teacher-course-row">
                            <div><strong>{{ $item->chapter_title }}</strong><small>{{ $item->period_label ?: 'Période non définie' }} · {{ $item->status }}</small></div>
                            <div class="teacher-row-actions">
                                @if($item->status !== 'completed')<form method="POST" action="{{ route('teacher.annual-programs.items.complete', $item->id) }}">@csrf<button class="teacher-btn teacher-btn--ghost">Terminer</button></form>@endif
                                <form method="POST" action="{{ route('teacher.annual-programs.items.delete', $item->id) }}" onsubmit="return confirm('Retirer ce chapitre ?');">@csrf<button class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                            </div>
                        </div>
                    @empty
                        <div class="teacher-empty-state"><strong>Aucun chapitre.</strong><p>Ajoutez les chapitres de votre progression annuelle.</p></div>
                    @endforelse
                </div>

                <div class="teacher-course-card__actions">
                    @if($program->status !== 'published')<form method="POST" action="{{ route('teacher.annual-programs.publish', $program->id) }}">@csrf<button class="teacher-btn teacher-btn--primary">Publier</button></form>@endif
                    @if($program->status !== 'archived')<form method="POST" action="{{ route('teacher.annual-programs.archive', $program->id) }}">@csrf<button class="teacher-btn teacher-btn--ghost">Archiver</button></form>@endif
                    <form method="POST" action="{{ route('teacher.annual-programs.delete', $program->id) }}" onsubmit="return confirm('Supprimer ce programme annuel ?');">@csrf<button class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                </div>
            </article>
        @empty
            <div class="teacher-empty-state"><strong>Aucun programme annuel.</strong><p>Créez le programme annuel de vos classes et matières.</p></div>
        @endforelse
    </div>
</section>
@endsection
