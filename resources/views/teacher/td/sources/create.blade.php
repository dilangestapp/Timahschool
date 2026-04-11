@extends('layouts.teacher')

@section('title', 'Importer une source TD')
@section('page_title', 'Importer une source TD')
@section('page_subtitle', 'Ajoutez une source brute par matière : sujet web, image, PDF, document, ancien TD ou simple prompt, puis laissez le moteur préparer une base de transformation.')

@section('content')
<form method="POST" action="{{ route('teacher.td.sources.store') }}" enctype="multipart/form-data" class="teacher-section">
    @csrf
    <div class="teacher-form-grid">
        <div class="teacher-form-group">
            <label>Affectation</label>
            <select name="teacher_assignment_id" required>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}">{{ $assignment->schoolClass->name ?? '-' }} — {{ $assignment->subject->name ?? '-' }}</option>
                @endforeach
            </select>
        </div>
        <div class="teacher-form-group">
            <label>Type de source</label>
            <select name="source_kind" required>
                <option value="url">Lien web</option>
                <option value="text">Texte / sujet saisi</option>
                <option value="prompt">Consigne libre / prompt</option>
                <option value="pdf">PDF</option>
                <option value="image">Image / capture</option>
                <option value="document">Document bureautique</option>
                <option value="legacy_td">Ancien TD</option>
            </select>
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>Titre de la source</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex. Sujet type probatoire - fonctions logarithmiques">
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>URL source</label>
            <input type="url" name="source_url" value="{{ old('source_url') }}" placeholder="https://...">
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>Référence / mention source</label>
            <input type="text" name="source_label" value="{{ old('source_label') }}" placeholder="Session 2022, établissement, séquence, etc.">
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>Texte source / notes extraites</label>
            <textarea name="raw_text" rows="10" placeholder="Collez ici l’énoncé, un résumé, des exercices ou le texte extrait d’un document.">{{ old('raw_text') }}</textarea>
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>Prompt / consigne de transformation</label>
            <textarea name="prompt_text" rows="6" placeholder="Ex. Générer une version plus difficile, centrée sur les applications numériques et avec un corrigé détaillé.">{{ old('prompt_text') }}</textarea>
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>Document source</label>
            <input type="file" name="source_file">
            <small>Formats autorisés : PDF, DOC, DOCX, PNG, JPG, TXT, RTF, ODT.</small>
        </div>
        <div class="teacher-form-group teacher-form-group--checkbox teacher-form-group--full">
            <label><input type="checkbox" name="rights_confirmed" value="1" @checked(old('rights_confirmed'))> Je confirme que cette source peut être utilisée pour construire un nouveau TD original dans la plateforme.</label>
        </div>
    </div>
    <div class="teacher-form-actions">
        <button type="submit" class="teacher-btn teacher-btn--primary">Importer la source</button>
        <a href="{{ route('teacher.td.sources.index') }}" class="teacher-btn teacher-btn--ghost">Retour</a>
    </div>
</form>
@endsection
