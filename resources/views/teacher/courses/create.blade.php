@extends('layouts.teacher')

@section('title', 'Nouveau cours')
@section('page_title', 'Nouveau cours enseignant')
@section('page_subtitle', 'Rédigez un cours dans un vrai éditeur riche, joignez un document et publiez quand vous êtes prêt.')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
<section class="teacher-section teacher-form-section">
    <div class="teacher-section__head">
        <div>
            <h2>Créer un cours</h2>
            <p class="teacher-muted">Vous pouvez enregistrer un cours rédigé, un document joint, ou les deux en même temps.</p>
        </div>
        <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn--ghost">Retour à mes cours</a>
    </div>

    @if($assignments->isEmpty())
        <div class="teacher-empty-state">
            <strong>Aucune affectation active.</strong>
            <p>Demandez à l'administrateur de vous affecter à une classe et à une matière avant de créer un cours.</p>
        </div>
    @else
        <form method="POST" action="{{ route('teacher.courses.store') }}" enctype="multipart/form-data" class="teacher-form-card teacher-course-editor-card" id="teacher-course-form">
            @csrf
            <div class="teacher-form-grid teacher-form-grid--two">
                <div class="teacher-form-group teacher-form-group--full">
                    <label for="teacher_assignment_id">Affectation</label>
                    <select id="teacher_assignment_id" name="teacher_assignment_id" class="teacher-select" required>
                        <option value="">Choisir...</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}" @selected(old('teacher_assignment_id') == $assignment->id)>
                                {{ $assignment->schoolClass->name ?? 'Classe' }} — {{ $assignment->subject->name ?? 'Matière' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="teacher-form-group teacher-form-group--full">
                    <label for="title">Titre du cours</label>
                    <input id="title" type="text" name="title" class="teacher-input" value="{{ old('title') }}" required>
                </div>

                <div class="teacher-form-group teacher-form-group--full">
                    <label for="description">Résumé du cours</label>
                    <textarea id="description" name="description" class="teacher-textarea" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="teacher-form-group teacher-form-group--full">
                    <label for="objectives">Objectifs pédagogiques</label>
                    <textarea id="objectives" name="objectives" class="teacher-textarea" rows="4">{{ old('objectives') }}</textarea>
                </div>

                <div class="teacher-form-group">
                    <label for="order">Ordre d'affichage</label>
                    <input id="order" type="number" min="0" name="order" class="teacher-input" value="{{ old('order', 0) }}">
                </div>

                <div class="teacher-form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="teacher-select" required>
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Brouillon</option>
                        <option value="published" @selected(old('status') === 'published')>Publié</option>
                        <option value="archived" @selected(old('status') === 'archived')>Archivé</option>
                    </select>
                </div>

                <div class="teacher-form-group teacher-form-group--full">
                    <label for="document">Document joint du cours</label>
                    <input id="document" type="file" name="document" class="teacher-file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.rtf,.odt">
                    <small class="teacher-help">Formats acceptés : PDF, DOC, DOCX, PPT, PPTX, TXT, RTF, ODT. Taille maximale : 20 Mo.</small>
                </div>

                <div class="teacher-form-group teacher-form-group--full">
                    <label for="content_html">Contenu rédigé du cours</label>
                    <div class="teacher-editor-note">Interface de rédaction enrichie type traitement de texte : titres, couleurs, tableaux, images, listes, liens, citations, plein écran.</div>
                    <textarea id="content_html" name="content_html" class="teacher-editor-source">{!! old('content_html') !!}</textarea>
                </div>
            </div>

            <div class="teacher-form-actions">
                <button type="submit" class="teacher-btn teacher-btn--primary">Enregistrer le cours</button>
                <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn--ghost">Annuler</a>
            </div>
        </form>
    @endif
</section>
@endsection

@push('scripts')
<script>
(function () {
    if (typeof tinymce === 'undefined') {
        console.warn('TinyMCE indisponible');
        return;
    }

    tinymce.init({
        selector: '#content_html',
        menubar: 'file edit view insert format tools table help',
        height: 620,
        branding: false,
        promotion: false,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount quickbars help pagebreak',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table blockquote pagebreak | removeformat code preview fullscreen',
        toolbar_sticky: true,
        browser_spellcheck: true,
        contextmenu: 'link image table',
        quickbars_selection_toolbar: 'bold italic underline | quicklink h2 h3 blockquote',
        quickbars_insert_toolbar: 'quickimage quicktable',
        image_caption: true,
        image_title: true,
        automatic_uploads: false,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        pagebreak_separator: '<!-- pagebreak -->',
        content_style: 'body { font-family: Inter, Segoe UI, Arial, sans-serif; font-size: 16px; line-height: 1.7; }',
        setup: function (editor) {
            editor.on('change input undo redo', function () {
                editor.save();
            });
        }
    });
})();
</script>
@endpush
