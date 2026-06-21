@extends('layouts.teacher')

@section('title', 'Modifier un cours')
@section('page_title', 'Modifier le cours')
@section('page_subtitle', 'Modifier le document réel ou récupérer son texte dans le contenu du cours.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/course-writer.css') }}">
@endpush

@section('content')
@php
    $extension = strtolower(pathinfo($course->document_name ?: $course->document_path ?: '', PATHINFO_EXTENSION));
    $canEditFile = in_array($extension, ['docx','doc','odt','rtf','txt'], true);
    $canConvertFile = in_array($extension, ['pdf','docx','doc','odt','rtf','txt'], true);
@endphp
<section class="course-writer-card">
    <div class="course-writer-head"><h2>{{ $course->title }}</h2><p>{{ $course->schoolClass->name ?? '-' }} — {{ $course->subject->name ?? '-' }}</p></div>

    @if($course->hasDocument())
        <div class="course-writer-form" style="padding-bottom:0;">
            <div class="course-doc-current">
                <div><strong>{{ $course->document_name }}</strong><div class="teacher-muted">{{ strtoupper($extension) ?: 'FICHIER' }} • {{ $course->humanDocumentSize() }}</div></div>
                <div class="course-doc-actions">
                    @if($canEditFile)<a href="{{ route('teacher.courses.office', $course) }}">Éditer dans Word</a>@endif
                    @if($canConvertFile)<form method="POST" action="{{ route('teacher.courses.convert', $course) }}" style="display:inline;">@csrf<button type="submit" class="course-doc-button">Récupérer le texte</button></form>@endif
                    <a href="{{ route('teacher.courses.document', $course) }}" target="_blank">Ouvrir</a><a href="{{ route('teacher.courses.document.download', $course) }}">Télécharger</a>
                </div>
            </div>
            <small class="course-writer-help">Word se modifie avec ONLYOFFICE. PDF texte peut être récupéré dans l’éditeur. PDF scanné demandera l’OCR.</small>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.courses.update', $course) }}" enctype="multipart/form-data" class="course-writer-form">
        @csrf
        <div class="course-writer-grid">
            <div class="course-writer-full"><label class="course-writer-label" for="title">Titre du cours</label><input id="title" type="text" name="title" class="course-writer-input" value="{{ old('title', $course->title) }}" required></div>
            <div class="course-writer-full"><label class="course-writer-label" for="description">Résumé</label><textarea id="description" name="description" class="course-writer-textarea">{{ old('description', $course->description) }}</textarea></div>
            <div class="course-writer-full"><label class="course-writer-label" for="objectives">Objectifs</label><textarea id="objectives" name="objectives" class="course-writer-textarea">{{ old('objectives', $course->objectives) }}</textarea></div>
            <div><label class="course-writer-label" for="order">Ordre</label><input id="order" type="number" name="order" min="0" class="course-writer-input" value="{{ old('order', $course->order ?? 0) }}"></div>
            <div><label class="course-writer-label" for="status">Statut</label><select id="status" name="status" class="course-writer-select"><option value="draft" @selected(old('status', $course->status) === 'draft')>Brouillon</option><option value="published" @selected(old('status', $course->status) === 'published')>Publié</option><option value="archived" @selected(old('status', $course->status) === 'archived')>Archivé</option></select></div>
            <div class="course-writer-full"><label class="course-writer-label" for="document">Remplacer le fichier</label><input id="document" type="file" name="document" class="course-writer-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.rtf,.odt">@if($course->hasDocument())<label style="display:flex;gap:8px;margin-top:10px;font-weight:800;color:#64748b;"><input type="checkbox" name="remove_document" value="1"> Supprimer le fichier actuel</label>@endif</div>
            <div class="course-writer-full"><label class="course-writer-label">Contenu éditable du cours</label><small class="course-writer-help">Le texte récupéré depuis un fichier apparaîtra ici pour correction et publication mobile.</small>@include('teacher.courses.partials.writer', ['field'=>'content_html','target'=>'#content_html','value'=>old('content_html', $course->content_html ?? '')])</div>
        </div>
        <div class="course-word-actions"><button type="submit" class="course-writer-primary">Enregistrer les modifications</button><a href="{{ route('teacher.courses.index') }}" class="course-writer-secondary" style="display:inline-flex;align-items:center;text-decoration:none;">Retour</a></div>
    </form>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/course-writer.js') }}"></script>
@endpush
