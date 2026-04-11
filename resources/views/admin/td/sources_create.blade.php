@extends('layouts.admin')

@section('title', 'Nouvelle préparation TD')
@section('page_title', 'Nouvelle préparation TD')
@section('page_subtitle', 'Importer une source, conserver les visuels utiles et générer un prompt propre à copier dans ChatGPT avec ton propre compte.')

@section('content')
<form method="POST" action="{{ route('admin.td.sources.store') }}" enctype="multipart/form-data" class="admin-section">
    @csrf
    <div class="admin-form-grid">
        <div class="form-group">
            <label>Affectation cible (facultatif)</label>
            <select name="teacher_assignment_id">
                <option value="">Aucune affectation directe</option>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}">{{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? '-' }} — {{ $assignment->schoolClass->name ?? '-' }} / {{ $assignment->subject->name ?? '-' }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Type de source</label>
            <select name="source_kind" required>
                <option value="url">Lien web</option><option value="text">Texte</option><option value="prompt">Consigne libre</option><option value="pdf">PDF</option><option value="image">Image</option><option value="document">Document</option><option value="legacy_td">Ancien TD</option>
            </select>
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Titre de la source</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex. Sujet type probatoire – dérivées et étude de fonctions">
        </div>
        <div class="form-group admin-form-grid__full">
            <label>URL source</label>
            <input type="url" name="source_url" value="{{ old('source_url') }}" placeholder="https://...">
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Référence / mention source</label>
            <input type="text" name="source_label" value="{{ old('source_label') }}" placeholder="Session, séquence, fascicule, établissement...">
        </div>
        <div class="form-group">
            <label>Classe</label>
            <select name="detected_school_class_id"><option value="">Détection / plus tard</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select>
        </div>
        <div class="form-group">
            <label>Matière</label>
            <select name="detected_subject_id"><option value="">Détection / plus tard</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select>
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Chapitre / thème si connu</label>
            <input type="text" name="detected_chapter_label" value="{{ old('detected_chapter_label') }}">
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Texte source brut</label>
            <textarea name="raw_text" rows="10" placeholder="Colle ici le texte si tu l’as déjà extrait ou si la source est textuelle.">{{ old('raw_text') }}</textarea>
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Texte extrait manuel (facultatif)</label>
            <textarea name="extracted_text" rows="8" placeholder="Si le PDF/image n’est pas lisible automatiquement, tu peux déjà coller ici le texte extrait à la main.">{{ old('extracted_text') }}</textarea>
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Document source</label>
            <input type="file" name="source_file">
        </div>
        <div class="form-group admin-form-grid__full">
            <label>Visuels pédagogiques à conserver (images, schémas, tableaux capturés)</label>
            <input type="file" name="visual_files[]" multiple accept="image/*">
            <small>Ces visuels seront rattachés ensuite aux exercices concernés avant préparation du prompt.</small>
        </div>
        <div class="form-group form-group--check admin-form-grid__full">
            <label><input type="checkbox" name="rights_confirmed" value="1"> Je confirme que cette source peut être utilisée comme base d’inspiration pédagogique, sans republication brute.</label>
        </div>
    </div>
    <div class="admin-actions">
        <button type="submit" class="btn btn--primary">Enregistrer la source</button>
        <a href="{{ route('admin.td.sources.index') }}" class="btn btn--ghost">Retour</a>
    </div>
</form>
@endsection
