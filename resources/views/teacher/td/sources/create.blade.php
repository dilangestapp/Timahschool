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
            <input type="file" name="source_file" id="td-source-file-input" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,.txt,.rtf,.odt,.html,.htm,image/png,image/jpeg,image/webp">
            <input type="hidden" name="ocr_extracted_text" id="td-source-ocr-text" value="{{ old('ocr_extracted_text') }}">
            <small>Formats autorisés : PDF, DOC, DOCX, PNG, JPG, JPEG, WEBP, TXT, RTF, ODT, HTML.</small>
            <small id="td-source-ocr-status" style="display:block; margin-top:6px;">Si vous chargez une image, un OCR automatique tentera d’extraire le texte vers le champ “Texte source”.</small>
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

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('td-source-file-input');
    const rawTextField = document.querySelector('textarea[name="raw_text"]');
    const ocrHidden = document.getElementById('td-source-ocr-text');
    const status = document.getElementById('td-source-ocr-status');

    if (!fileInput || !rawTextField || !ocrHidden || !status) {
        return;
    }

    const isImageFile = (file) => !!file && /(\.png|\.jpe?g|\.webp)$/i.test(file.name || '');

    fileInput.addEventListener('change', async function () {
        const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

        if (!file) {
            ocrHidden.value = '';
            status.textContent = 'Aucun fichier sélectionné.';
            return;
        }

        if (!isImageFile(file)) {
            ocrHidden.value = '';
            status.textContent = 'Fichier non-image : OCR non requis. Les autres conversions restent inchangées.';
            return;
        }

        if (!window.Tesseract || typeof window.Tesseract.recognize !== 'function') {
            ocrHidden.value = '';
            status.textContent = 'OCR image indisponible dans ce navigateur. Vous pouvez quand même coller le texte manuellement.';
            return;
        }

        try {
            status.textContent = 'OCR image en cours...';
            const result = await window.Tesseract.recognize(file, 'fra+eng', {
                logger: function (message) {
                    if (message && message.status === 'recognizing text' && typeof message.progress === 'number') {
                        status.textContent = 'OCR image en cours... ' + Math.round(message.progress * 100) + '%';
                    }
                }
            });

            const text = ((result && result.data && result.data.text) ? result.data.text : '').trim();
            const confidence = (result && result.data && typeof result.data.confidence === 'number') ? result.data.confidence : null;
            ocrHidden.value = text;

            if (!text) {
                status.textContent = 'OCR terminé mais aucun texte lisible détecté. Vérifiez la qualité de la photo (netteté, lumière, contraste).';
                return;
            }

            if (!rawTextField.value || !rawTextField.value.trim()) {
                rawTextField.value = text;
            }

            if (confidence !== null && confidence < 55) {
                status.textContent = 'OCR partiel (' + Math.round(confidence) + '%). Le texte a été injecté, merci de le relire et corriger.';
            } else {
                status.textContent = 'OCR image terminé. Le texte extrait est prêt pour l’analyse/transformation.';
            }
        } catch (error) {
            console.error(error);
            ocrHidden.value = '';
            status.textContent = 'Échec OCR image. Le fichier est conservé, mais ajoutez le texte manuellement si nécessaire.';
        }
    });
});
</script>
@endsection
