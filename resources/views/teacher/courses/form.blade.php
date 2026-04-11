@extends('layouts.teacher')

@section('title', $mode === 'create' ? 'Nouveau cours' : 'Modifier le cours')
@section('page_title', $mode === 'create' ? 'Nouveau cours' : 'Modifier le cours')
@section('page_subtitle', 'Renseignez les informations pédagogiques essentielles du cours.')

@section('content')
<section class="teacher-panel">
    <div class="teacher-panel__body">
        <form method="POST" action="{{ $mode === 'create' ? route('teacher.courses.store') : route('teacher.courses.update', $course) }}" class="teacher-form-grid">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="teacher-form-row">
                <div class="teacher-form-group">
                    <label>Matière</label>
                    <select name="subject_id" required>
                        <option value="">Choisir</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id', $course->subject_id) == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')<small class="teacher-error">{{ $message }}</small>@enderror
                </div>
                <div class="teacher-form-group">
                    <label>Classe</label>
                    <select name="school_class_id" required>
                        <option value="">Choisir</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id', $course->school_class_id) == $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('school_class_id')<small class="teacher-error">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="teacher-form-group">
                <label>Titre du cours</label>
                <input type="text" name="title" value="{{ old('title', $course->title) }}" required>
                @error('title')<small class="teacher-error">{{ $message }}</small>@enderror
            </div>

            <div class="teacher-form-group">
                <label>Description</label>
                <textarea name="description" rows="5">{{ old('description', $course->description) }}</textarea>
                @error('description')<small class="teacher-error">{{ $message }}</small>@enderror
            </div>

            <div class="teacher-form-group">
                <label>Objectifs</label>
                <textarea name="objectives" rows="5">{{ old('objectives', $course->objectives) }}</textarea>
                @error('objectives')<small class="teacher-error">{{ $message }}</small>@enderror
            </div>

            <div class="teacher-form-row">
                <div class="teacher-form-group">
                    <label>Niveau / mention</label>
                    <input type="text" name="level" value="{{ old('level', $course->level) }}">
                    @error('level')<small class="teacher-error">{{ $message }}</small>@enderror
                </div>
                <div class="teacher-form-group">
                    <label>Ordre</label>
                    <input type="number" min="0" name="order" value="{{ old('order', $course->order ?? 0) }}">
                    @error('order')<small class="teacher-error">{{ $message }}</small>@enderror
                </div>
                <div class="teacher-form-group">
                    <label>Statut</label>
                    <select name="status" required>
                        <option value="draft" @selected(old('status', $course->status ?: 'draft') === 'draft')>Brouillon</option>
                        <option value="published" @selected(old('status', $course->status) === 'published')>Publié</option>
                        <option value="archived" @selected(old('status', $course->status) === 'archived')>Archivé</option>
                    </select>
                    @error('status')<small class="teacher-error">{{ $message }}</small>@enderror
                </div>
            </div>

            <div class="teacher-actions-inline">
                <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn--ghost">Retour</a>
                <button type="submit" class="teacher-btn teacher-btn--primary">{{ $mode === 'create' ? 'Enregistrer le cours' : 'Mettre à jour' }}</button>
            </div>
        </form>
    </div>
</section>
@endsection
