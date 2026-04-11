@extends('layouts.teacher')

@section('title', 'Modifier un cours')
@section('page_title', 'Modifier le cours')
@section('page_subtitle', 'Mettez à jour le contenu rédigé, le document et le statut de ce cours.')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
<section class="teacher-section teacher-form-section">
    <div class="teacher-section__head">
        <div>
            <h2>{{ $course->title }}</h2>
            <p class="teacher-muted">{{ $course->schoolClass->name ?? '-' }} — {{ $course->subject->name ?? '-' }}</p>
        </div>
        <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn--ghost">Retour à mes cours</a>
    </div>

    <form method="POST" action="{{ route('teacher.courses.update', $course) }}" enctype="multipart/form-data" class="teacher-form-card teacher-course-editor-card" id="teacher-course-form">
        @csrf
        <div class="teacher-form-grid teacher-form-grid--two">
            <div class="teacher-form-group teacher-form-group--full">
                <label for="title">Titre du cours</label>
                <input id="title" type="text" name="title" class="teacher-input" value="{{ old('title', $course->title) }}" required>
            </div>

            <div class="teacher-form-group teacher-form-group--full">
                <label for="description">Résumé du cours</label>
                <textarea id="description" name="description" class="teacher-textarea" rows="4">{{ old('description', $course->description) }}</textarea>
            </div>

            <div class="teacher-form-group teacher-form-group--full">
                <label for="objectives">Objectifs pédagogiques</label>
                <textarea id="objectives" name="objectives" class="teacher-textarea" rows="4">{{ old('objectives', $course->objectives) }}</textarea>
            </div>

            <div class="teacher-form-group">
                <label for="order">Ordre d'affichage</label>
                <input id="order" type="number" min="0" name="order" class="teacher-input" value="{{ old('order', $course->order ?? 0) }}">
            </div>

            <div class="teacher-form-group">
                <label for="status">Statut</label>
                <select id="status" name="status" class="teacher-select" required>
                    <option value="draft" @selected(old('status', $course->status) === 'draft')>Brouillon</option>
                    <option value="published" @selected(old('status', $course->status) === 'published')>Publié</option>
                    <option value="archived" @selected(old('status', $course->status) === 'archived')>Archivé</option>
                </select>
            </div>

            <div class="teacher-form-group teacher-form-group--full">
                <label for="document">Remplacer le document</label>
                <input id="document" type="file" name="document" class="teacher-file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.rtf,.odt">
                <small class="teacher-help">Formats acceptés : PDF, DOC, DOCX, PPT, PPTX, TXT, RTF, ODT.</small>
            </div>

            <div class="teacher-form-group teacher-form-group--full">
                <label>Document actuel</label>
                @if($course->hasDocument())
                    <div class="teacher-current-doc">
                        <div>
                            <strong>{{ $course->document_name }}</strong>
                            <div class="teacher-muted">{{ strtoupper(pathinfo($course->document_name, PATHINFO_EXTENSION)) ?: 'DOC' }} • {{ $course->humanDocumentSize() }}</div>
                        </div>
                        <div class="teacher-current-doc__actions">
                            <a href="{{ route('teacher.courses.document', $course) }}" target="_blank" class="teacher-mini-link">Ouvrir</a>
                            <a href="{{ route('teacher.courses.document.download', $course) }}" class="teacher-mini-link">Télécharger</a>
                        </div>
                    </div>
                    <label class="teacher-inline-check">
                        <input type="checkbox" name="remove_document" value="1"> Supprimer le document actuel
                    </label>
                @else
                    <div class="teacher-empty-inline">Aucun document importé pour ce cours.</div>
                @endif
            </div>

            <div class="teacher-form-group teacher-form-group--full">
                <label for="content_html">Contenu rédigé du cours</label>
                <div class="teacher-editor-note">Vous pouvez modifier librement le contenu avec une interface riche type traitement de texte.</div>
                <textarea id="content_html" name="content_html" class="teacher-editor-source">{!! old('content_html', $course->content_html ?? '') !!}</textarea>
            </div>
        </div>

        <div class="teacher-form-actions">
            <button type="submit" class="teacher-btn teacher-btn--primary">Enregistrer les modifications</button>
            <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn--ghost">Annuler</a>
        </div>
    </form>
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
