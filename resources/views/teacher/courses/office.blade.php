@extends('layouts.teacher')

@section('title', 'Éditeur Word')
@section('page_title', 'Éditeur Word du cours')
@section('page_subtitle', 'Modifiez le document avec ONLYOFFICE, comme dans un traitement de texte professionnel.')

@push('styles')
<style>
    .office-shell { display: grid; gap: 14px; height: calc(100vh - 170px); min-height: 720px; }
    .office-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 22px; background: #fff; border: 1px solid var(--teacher-border); box-shadow: 0 12px 28px rgba(15,23,42,.06); }
    .office-toolbar h2 { margin: 0; font-size: 1.1rem; }
    .office-toolbar p { margin: 4px 0 0; color: var(--teacher-muted); }
    .office-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    #onlyoffice-editor { width: 100%; height: 100%; border-radius: 22px; overflow: hidden; background: #fff; border: 1px solid var(--teacher-border); }
    @media (max-width: 760px) { .office-shell { height: calc(100vh - 145px); min-height: 640px; } .office-toolbar { align-items: flex-start; flex-direction: column; } .office-actions { width: 100%; } .office-actions a { flex: 1; justify-content: center; } }
</style>
@endpush

@section('content')
<div class="office-shell">
    <div class="office-toolbar">
        <div>
            <h2>{{ $course->title }}</h2>
            <p>{{ $course->schoolClass->name ?? '-' }} — {{ $course->subject->name ?? '-' }} — {{ $course->document_name }}</p>
        </div>
        <div class="office-actions">
            <a href="{{ route('teacher.courses.edit', $course) }}" class="teacher-btn teacher-btn--ghost">Retour</a>
            <a href="{{ route('teacher.courses.document.download', $course) }}" class="teacher-btn teacher-btn--primary">Télécharger</a>
        </div>
    </div>

    <div id="onlyoffice-editor"></div>
</div>
@endsection

@push('scripts')
<script src="{{ $documentServerUrl }}/web-apps/apps/api/documents/api.js"></script>
<script>
    window.timahOnlyOfficeConfig = @json($editorConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    window.timahDocEditor = new DocsAPI.DocEditor('onlyoffice-editor', window.timahOnlyOfficeConfig);
</script>
@endpush
