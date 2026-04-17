@php($isEdit = $isEdit ?? false)
@php($existingDocumentFetchUrl = $isEdit && !empty($td->document_path) ? route('teacher.td.sets.document', $td) : null)
@php($existingDocumentName = $isEdit && !empty($td->document_name) ? $td->document_name : ($isEdit && !empty($td->document_path) ? basename($td->document_path) : null))
<section class="teacher-section">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="teacher-form-stack">
        @csrf

        <div class="teacher-card-block">
            <h2>{{ $isEdit ? 'Modifier le TD' : 'Créer un nouveau TD' }}</h2>
            <div class="teacher-form-grid">
                <div class="teacher-form-group">
                    <label>Affectation enseignant / classe / matière</label>
                    <select name="teacher_assignment_id" required>
                        <option value="">Choisir...</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}" @selected((int) old('teacher_assignment_id', $td->teacher_assignment_id) === (int) $assignment->id)>
                                {{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? 'Enseignant' }} — {{ $assignment->schoolClass->name ?? '-' }} — {{ $assignment->subject->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="teacher-form-group">
                    <label>Titre du TD</label>
                    <input type="text" name="title" value="{{ old('title', $td->title) }}" required>
                </div>
                <div class="teacher-form-group">
                    <label>Chapitre / thème</label>
                    <input type="text" name="chapter_label" value="{{ old('chapter_label', $td->chapter_label) }}">
                </div>
                <div class="teacher-form-group">
                    <label>Difficulté</label>
                    <select name="difficulty">
                        @foreach(['easy' => 'Facile', 'medium' => 'Moyen', 'hard' => 'Difficile', 'exam' => 'Examen'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('difficulty', $td->difficulty ?? 'medium') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="teacher-form-group">
                    <label>Accès</label>
                    <select name="access_level">
                        <option value="free" @selected(old('access_level', $td->access_level ?? 'free') === 'free')>Gratuit</option>
                        <option value="premium" @selected(old('access_level', $td->access_level ?? 'free') === 'premium')>Premium</option>
                    </select>
                </div>
                <div class="teacher-form-group">
                    <label>Statut</label>
                    <select name="status">
                        <option value="draft" @selected(old('status', $td->status ?? 'draft') === 'draft')>Brouillon</option>
                        <option value="published" @selected(old('status', $td->status ?? 'draft') === 'published')>Publié</option>
                        <option value="archived" @selected(old('status', $td->status ?? 'draft') === 'archived')>Désactivé</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="teacher-card-block">
            <h2>Document TD</h2>
            <div class="teacher-form-grid">
                <div class="teacher-form-group teacher-form-group--full">
                    <label>Document source</label>
                    <input type="file" name="document" id="teacher-td-source-file" accept=".pdf,.doc,.docx,.txt,.odt,.rtf,.html,.htm,.png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                    <small>Formats autorisés : PDF, DOC, DOCX, TXT, ODT, RTF, HTML, PNG, JPG, JPEG, WEBP.</small>
                </div>
                @if($isEdit && $td->document_path)
                    <div class="teacher-doc-box teacher-form-group--full">
                        <strong>Document actuel :</strong>
                        <a href="{{ route('teacher.td.sets.document', $td) }}">{{ $td->document_name ?: 'Ouvrir le document' }}</a>
                        <label class="teacher-inline-check"><input type="checkbox" name="remove_document" value="1"> Supprimer le document actuel</label>
                    </div>
                @endif
            </div>
            <div class="teacher-actions teacher-actions--wrap" style="margin-top:14px;">
                <button type="button" class="teacher-btn teacher-btn--ghost" id="teacher-convert-new-file">Importer et convertir le nouveau fichier</button>
                @if($isEdit && $td->document_path)
                    <button type="button" class="teacher-btn teacher-btn--ghost" id="teacher-convert-current-file">Convertir le document actuel dans l’éditeur</button>
                @endif
                <span class="teacher-muted" id="teacher-conversion-status">Choisis un nouveau fichier ou convertis le document actuel pour remplir l’éditeur.</span>
            </div>
        </div>

        <div class="teacher-card-block" id="editor-zone">
            <h2>Version éditable</h2>
            <p class="teacher-muted" style="margin:0 0 12px;">Ici tu peux corriger, reformuler et gérer le TD directement depuis la plateforme.</p>
            <textarea id="teacher-td-editor" name="editable_html" rows="12">{{ old('editable_html', $td->editable_html) }}</textarea>
            <input type="hidden" id="teacher-td-editor-text" name="editable_text" value="{{ old('editable_text', $td->editable_text) }}">
            <small>Si tu veux modifier le TD depuis la plateforme, le document doit être converti ici. Après conversion, vérifie toujours la mise en forme.</small>
        </div>

        <div class="teacher-card-block">
            <h2>Corrigé</h2>
            <div class="teacher-form-grid">
                <div class="teacher-form-group teacher-form-group--full">
                    <label>Corrigé texte</label>
                    <textarea id="teacher-correction-editor" name="correction_html" rows="8">{{ old('correction_html', $td->correction_html) }}</textarea>
                </div>
                <div class="teacher-form-group teacher-form-group--full">
                    <label>Document corrigé</label>
                    <input type="file" name="correction_document" accept=".pdf,.doc,.docx,.txt,.odt,.rtf,.html,.htm,.png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                </div>
                @if($isEdit && $td->correction_document_path)
                    <div class="teacher-doc-box teacher-form-group--full">
                        <strong>Corrigé actuel :</strong>
                        <a href="{{ route('teacher.td.sets.correction_document', $td) }}">{{ $td->correction_document_name ?: 'Ouvrir le corrigé' }}</a>
                        <label class="teacher-inline-check"><input type="checkbox" name="remove_correction_document" value="1"> Supprimer le document corrigé actuel</label>
                    </div>
                @endif
            </div>
        </div>

        <div class="teacher-actions teacher-actions--end">
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-btn teacher-btn--ghost">Annuler</a>
            <button type="submit" class="teacher-btn teacher-btn--primary">{{ $isEdit ? 'Enregistrer les modifications' : 'Créer le TD' }}</button>
        </div>
    </form>
</section>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tinymce@6/skins/ui/oxide/skin.min.css">
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script src="https://unpkg.com/mammoth@1.7.2/mammoth.browser.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === TD Converter (merge-safe section): shared conversion pipeline for DOCX/PDF/OCR ===
    const prefix = 'teacher';
    const shouldFocusEditor = new URLSearchParams(window.location.search).get('mode') === 'editor';
    const status = document.getElementById(prefix + '-conversion-status');
    const fileInput = document.getElementById(prefix + '-td-source-file');
    const newBtn = document.getElementById(prefix + '-convert-new-file');
    const currentBtn = document.getElementById(prefix + '-convert-current-file');
    const currentDocFetchUrl = @json($existingDocumentFetchUrl ?? null);
    const currentDocName = @json($existingDocumentName ?? null);

    function updateEditableText(editorId, hiddenId) {
        const editor = tinymce.get(editorId);
        const hidden = document.getElementById(hiddenId);
        if (editor && hidden) {
            hidden.value = editor.getContent({ format: 'text' });
        }
    }

    function initEditor(selector, height, hiddenId) {
        tinymce.init({
            selector,
            height,
            menubar: 'file edit view insert format tools table help',
            plugins: 'lists link image table code fullscreen wordcount searchreplace autoresize',
            toolbar: 'undo redo | styles fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | removeformat code fullscreen',
            branding: false,
            promotion: false,
            convert_urls: false,
            setup: function(editor) {
                editor.on('change keyup', function() {
                    if (hiddenId) {
                        updateEditableText(editor.id, hiddenId);
                    }
                });
                editor.on('init', function() {
                    if (shouldFocusEditor && editor.id === prefix + '-td-editor') {
                        const zone = document.getElementById('editor-zone');
                        if (zone) zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        setTimeout(() => editor.focus(), 250);
                    }
                });
            }
        });
    }

    initEditor('#' + prefix + '-td-editor', 520, prefix + '-td-editor-text');
    initEditor('#' + prefix + '-correction-editor', 320, null);

    function setEditorContent(html, text) {
        const editor = tinymce.get(prefix + '-td-editor');
        const hidden = document.getElementById(prefix + '-td-editor-text');
        if (editor) editor.setContent(html || '');
        if (hidden) hidden.value = text || (html ? html.replace(/<[^>]+>/g, ' ') : '');
        const zone = document.getElementById('editor-zone');
        if (zone) zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"]/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function convertTextToHtml(text) {
        return (text || '')
            .split(/\n{2,}/)
            .map(function(p) {
                return '<p>' + escapeHtml(p).replace(/\n/g, '<br>') + '</p>';
            })
            .join('');
    }

    function normalizeLine(rawLine) {
        let line = (rawLine || '').replace(/\s+/g, ' ').trim();
        line = line.replace(/\s+([,.;:!?])/g, '$1');
        return line;
    }

    function isHeadingCandidate(line) {
        const text = normalizeLine(line);
        if (!text) return false;
        if (text.length > 95) return false;
        if (/^(chapitre|th[eè]me|theme|partie|section|introduction|conclusion)\b/i.test(text)) return true;
        if (/^(exercice|question)\s+\d+/i.test(text)) return true;
        const letters = text.replace(/[^A-Za-zÀ-ÿ]/g, '');
        if (letters.length >= 4) {
            const upperRatio = letters.replace(/[^A-ZÀ-Ý]/g, '').length / letters.length;
            if (upperRatio > 0.75) return true;
        }
        return false;
    }

    function isListItem(line) {
        return /^(\d+[\.\)]|[a-zA-Z][\.\)]|[-•*])\s+/.test(normalizeLine(line));
    }

    function toTitleCase(text) {
        return text.replace(/\s+/g, ' ').trim();
    }

    function buildPdfHtmlFromLines(lines) {
        const blocks = [];
        let paragraph = [];
        let listItems = [];
        let listType = 'ol';

        function flushParagraph() {
            if (!paragraph.length) return;
            const p = paragraph.join(' ').replace(/\s+/g, ' ').trim();
            if (p) blocks.push('<p>' + escapeHtml(p) + '</p>');
            paragraph = [];
        }

        function flushList() {
            if (!listItems.length) return;
            const tag = listType === 'ul' ? 'ul' : 'ol';
            blocks.push('<' + tag + '>' + listItems.map(function(item) { return '<li>' + escapeHtml(item) + '</li>'; }).join('') + '</' + tag + '>');
            listItems = [];
            listType = 'ol';
        }

        lines.forEach(function(rawLine) {
            const line = normalizeLine(rawLine);
            if (!line) {
                flushParagraph();
                flushList();
                return;
            }

            if (isHeadingCandidate(line)) {
                flushParagraph();
                flushList();
                blocks.push('<h3>' + escapeHtml(toTitleCase(line)) + '</h3>');
                return;
            }

            if (isListItem(line)) {
                flushParagraph();
                listType = /^[-•*]\s+/.test(line) ? 'ul' : 'ol';
                listItems.push(line.replace(/^(\d+[\.\)]|[a-zA-Z][\.\)]|[-•*])\s+/, '').trim());
                return;
            }

            flushList();

            if (!paragraph.length) {
                paragraph.push(line);
                return;
            }

            const prev = paragraph[paragraph.length - 1];
            const shouldBreakParagraph = /[.:;!?]$/.test(prev) && /^[A-ZÀ-Ý]/.test(line) && line.length > 40;
            if (shouldBreakParagraph) {
                flushParagraph();
                paragraph.push(line);
                return;
            }

            if (/-$/.test(prev)) {
                paragraph[paragraph.length - 1] = prev.slice(0, -1) + line;
            } else {
                paragraph[paragraph.length - 1] = prev + ' ' + line;
            }
        });

        flushParagraph();
        flushList();

        return blocks.join('');
    }

    function lineLooksCorrupted(line) {
        const text = normalizeLine(line);
        if (!text) return false;
        const weirdChars = (text.match(/[^A-Za-zÀ-ÿ0-9\s.,;:!?()%+\-–—'"«»/]/g) || []).length;
        return weirdChars > Math.max(5, text.length * 0.25);
    }

    async function convertPdfBuffer(arrayBuffer) {
        const pdfjsLib = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.min.mjs');
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.worker.min.mjs';
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        const lines = [];
        let textItemCount = 0;

        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const content = await page.getTextContent();
            const rowsByY = new Map();

            content.items.forEach(function(item) {
                const str = (item.str || '').trim();
                if (!str) return;
                textItemCount += 1;
                const y = Math.round((item.transform && item.transform[5] ? item.transform[5] : 0) * 10) / 10;
                if (!rowsByY.has(y)) rowsByY.set(y, []);
                rowsByY.get(y).push({
                    x: item.transform && item.transform[4] ? item.transform[4] : 0,
                    str: str,
                });
            });

            Array.from(rowsByY.keys())
                .sort(function(a, b) { return b - a; })
                .forEach(function(y) {
                    const row = rowsByY.get(y)
                        .sort(function(a, b) { return a.x - b.x; })
                        .map(function(piece) { return piece.str; })
                        .join(' ');

                    const normalized = normalizeLine(row);
                    if (normalized) {
                        lines.push(normalized);
                    }
                });

            lines.push('');
        }

        const cleanedLines = lines
            .map(normalizeLine)
            .filter(function(line) { return !lineLooksCorrupted(line); });

        const plainText = cleanedLines.join('\n').trim();
        const html = buildPdfHtmlFromLines(cleanedLines);
        const averageItemsPerPage = pdf.numPages > 0 ? textItemCount / pdf.numPages : 0;
        const scannedLikely = averageItemsPerPage < 12 || plainText.length < 120;

        return {
            text: plainText,
            html: html,
            scannedLikely: scannedLikely,
            averageItemsPerPage: averageItemsPerPage,
        };
    }

    async function extractTextFromImage(file) {
        if (!window.Tesseract || typeof window.Tesseract.recognize !== 'function') {
            throw new Error('Tesseract.js indisponible');
        }

        const result = await window.Tesseract.recognize(file, 'fra+eng', {
            logger: function(message) {
                if (message && message.status === 'recognizing text' && typeof message.progress === 'number') {
                    status.textContent = 'OCR image en cours... ' + Math.round(message.progress * 100) + '%';
                }
            }
        });

        return {
            text: (result && result.data && result.data.text ? result.data.text : '').trim(),
            confidence: (result && result.data && typeof result.data.confidence === 'number') ? result.data.confidence : null,
        };
    }

    async function handleFile(file, sourceLabel) {
        if (!file) {
            status.textContent = 'Aucun document à convertir.';
            return;
        }
        const name = (file.name || '').toLowerCase().trim();
        status.textContent = 'Conversion en cours...';
        try {
            if (name.endsWith('.docx')) {
                const buffer = await file.arrayBuffer();
                const result = await window.mammoth.convertToHtml({ arrayBuffer: buffer });
                const plain = (result.value || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                setEditorContent(result.value, plain);
                status.textContent = sourceLabel + ' converti dans l’éditeur. Vérifie et corrige la mise en forme si besoin.';
                return;
            }
            if (name.endsWith('.txt') || name.endsWith('.rtf') || name.endsWith('.html') || name.endsWith('.htm') || name.endsWith('.odt')) {
                const text = await file.text();
                setEditorContent(convertTextToHtml(text), text);
                status.textContent = sourceLabel + ' importé dans l’éditeur. Vérifie le rendu avant publication.';
                return;
            }
            if (name.endsWith('.pdf')) {
                const buffer = await file.arrayBuffer();
                const pdfResult = await convertPdfBuffer(buffer);
                if (pdfResult.scannedLikely || !pdfResult.text || !pdfResult.html) {
                    status.textContent = 'PDF scanné ou texte non exploitable automatiquement. Utilise un DOCX, active l’OCR image, ou complète l’éditeur manuellement.';
                    return;
                }
                setEditorContent(pdfResult.html, pdfResult.text);
                status.textContent = sourceLabel + ' converti depuis PDF texte avec nettoyage avancé (paragraphes, titres, listes). Vérifie le rendu final.';
                return;
            }
            if (name.endsWith('.doc')) {
                status.textContent = 'Le format DOC (ancien Word) n’est pas convertible de façon fiable dans le navigateur. Convertis d’abord en DOCX pour une édition fidèle.';
                return;
            }
            if (name.endsWith('.png') || name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.webp')) {
                const ocr = await extractTextFromImage(file);
                if (!ocr.text) {
                    status.textContent = 'OCR terminé mais aucun texte détecté sur l’image. Vérifie la qualité (netteté, contraste) puis réessaie.';
                    return;
                }
                setEditorContent(convertTextToHtml(ocr.text), ocr.text);
                if (ocr.confidence !== null && ocr.confidence < 55) {
                    status.textContent = sourceLabel + ' converti par OCR avec une confiance partielle (' + Math.round(ocr.confidence) + '%). Vérifie et corrige le texte extrait.';
                } else {
                    status.textContent = sourceLabel + ' converti par OCR image dans l’éditeur. Vérifie la ponctuation et les sauts de ligne.';
                }
                return;
            }
            status.textContent = 'Ce format peut être enregistré comme document source, mais sa conversion automatique reste limitée. Utilise DOCX, PDF texte, TXT ou HTML pour une meilleure conversion.';
        } catch (error) {
            console.error(error);
            const message = (error && error.message) ? error.message : 'Erreur inconnue';
            status.textContent = 'Conversion échouée : ' + message + '. Vérifie le type de fichier (DOCX prioritaire, PDF texte, image OCR).';
        }
    }

    if (newBtn) {
        newBtn.addEventListener('click', async function() {
            const file = fileInput && fileInput.files ? fileInput.files[0] : null;
            if (!file) {
                status.textContent = 'Choisis d’abord un nouveau document TD.';
                return;
            }
            await handleFile(file, 'Le nouveau document');
        });
    }

    if (currentBtn) {
        currentBtn.addEventListener('click', async function() {
            if (!currentDocFetchUrl || !currentDocName) {
                status.textContent = 'Aucun document actuel à convertir.';
                return;
            }
            status.textContent = 'Chargement sécurisé du document actuel...';
            try {
                const response = await fetch(currentDocFetchUrl, {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/octet-stream,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain,text/html,image/*,*/*',
                    }
                });

                if (!response.ok) {
                    throw new Error('Impossible de relire le document actuel (HTTP ' + response.status + ')');
                }

                const blob = await response.blob();
                const inferredName = (currentDocName || '').trim() || 'document-source';
                const file = new File([blob], inferredName, { type: blob.type || 'application/octet-stream' });
                await handleFile(file, 'Le document actuel');
            } catch (error) {
                console.error(error);
                const message = (error && error.message) ? error.message : 'erreur inconnue';
                status.textContent = 'Le document actuel n’a pas pu être relu automatiquement : ' + message + '. Recharge-le dans le champ pour conversion locale.';
            }
        });
    }

    if (shouldFocusEditor) {
        const zone = document.getElementById('editor-zone');
        if (zone) setTimeout(() => zone.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
    }
    // === End TD Converter section ===
});
</script>
