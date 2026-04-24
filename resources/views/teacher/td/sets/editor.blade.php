@extends('layouts.teacher')

@section('title', 'Modifier le TD')
@section('page_title', 'Modifier le TD')
@section('page_subtitle', 'Le sujet est chargé directement dans l’éditeur. Vous pouvez aussi ouvrir l’original, consulter le corrigé et modifier le corrigé.')

@push('styles')
<style>
    .td-editor-page { display: grid; gap: 18px; }
    .td-editor-hero,
    .td-editor-panel,
    .td-editor-side-card {
        background: rgba(255,255,255,.88);
        border: 1px solid rgba(148,163,184,.24);
        border-radius: 24px;
        box-shadow: 0 18px 42px rgba(15,23,42,.08);
    }
    html[data-theme='dark'] .td-editor-hero,
    html[data-theme='dark'] .td-editor-panel,
    html[data-theme='dark'] .td-editor-side-card { background: rgba(15,23,42,.78); }
    .td-editor-hero { padding: 18px; display: flex; justify-content: space-between; gap: 14px; flex-wrap: wrap; align-items: flex-start; }
    .td-editor-hero h2 { margin: 0 0 8px; letter-spacing: -.04em; }
    .td-editor-meta { color: var(--teacher-muted, #64748b); font-weight: 800; line-height: 1.45; }
    .td-editor-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .td-editor-layout { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 16px; align-items: start; }
    .td-editor-panel { padding: 18px; display: grid; gap: 12px; }
    .td-editor-side { display: grid; gap: 14px; }
    .td-editor-side-card { padding: 16px; display: grid; gap: 12px; }
    .td-editor-side-card h3,
    .td-editor-panel h3 { margin: 0; letter-spacing: -.03em; }
    .td-editor-status {
        border-radius: 16px;
        padding: 12px 14px;
        background: rgba(15,118,110,.08);
        color: #115e59;
        font-weight: 850;
        line-height: 1.45;
    }
    html[data-theme='dark'] .td-editor-status { background: rgba(45,212,191,.12); color: #99f6e4; }
    .td-editor-toolbar { display: flex; gap: 8px; flex-wrap: wrap; }
    .td-editor-tool {
        border: 1px solid rgba(148,163,184,.28);
        border-radius: 999px;
        background: rgba(255,255,255,.92);
        padding: 8px 12px;
        font-weight: 900;
        cursor: pointer;
        color: var(--teacher-text, #0f172a);
    }
    html[data-theme='dark'] .td-editor-tool { background: rgba(15,23,42,.86); color: #f8fafc; }
    .td-editor-textarea {
        width: 100%;
        min-height: 620px;
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.28);
        padding: 18px;
        background: rgba(248,250,252,.92);
        color: var(--teacher-text, #0f172a);
        font: 500 15px/1.65 Inter, system-ui, sans-serif;
        resize: vertical;
        outline: none;
    }
    .td-editor-textarea:focus { border-color: rgba(15,118,110,.45); box-shadow: 0 0 0 4px rgba(15,118,110,.12); }
    html[data-theme='dark'] .td-editor-textarea { background: rgba(2,6,23,.44); color: #f8fafc; }
    .td-editor-preview {
        display: none;
        min-height: 260px;
        border-radius: 18px;
        border: 1px dashed rgba(15,118,110,.35);
        background: rgba(15,118,110,.05);
        padding: 16px;
        overflow: auto;
    }
    .td-editor-submit {
        position: sticky;
        bottom: 12px;
        z-index: 10;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px;
        border-radius: 20px;
        background: rgba(255,255,255,.88);
        border: 1px solid rgba(148,163,184,.24);
        backdrop-filter: blur(14px);
        box-shadow: 0 18px 40px rgba(15,23,42,.10);
    }
    html[data-theme='dark'] .td-editor-submit { background: rgba(15,23,42,.86); }
    .td-hidden-correction { display: none; }
    .td-correction-open .td-hidden-correction { display: grid; gap: 12px; }
    .td-correction-open .td-correction-button { display: none; }
    @media (max-width: 920px) {
        .td-editor-layout { grid-template-columns: 1fr; }
        .td-editor-actions,
        .td-editor-submit,
        .td-editor-submit .teacher-btn,
        .td-editor-side-card .teacher-btn { width: 100%; }
        .td-editor-submit { display: grid; grid-template-columns: 1fr; }
        .td-editor-textarea { min-height: 430px; }
    }
</style>
@endpush

@section('content')
@php
    $subjectInitial = old('editable_html', $td->editable_html ?: '');
    $correctionInitial = old('correction_html', $td->correction_html ?: '');
@endphp

<section class="td-editor-page">
    <div class="td-editor-hero">
        <div>
            <h2>{{ $td->title }}</h2>
            <div class="td-editor-meta">{{ $td->schoolClass->name ?? '-' }} · {{ $td->subject->name ?? '-' }} · {{ $td->chapter_label ?: 'Sans chapitre' }}</div>
            <div class="td-editor-meta">Temps avant corrigé : {{ $td->correction_delay_minutes ?? 30 }} minute(s)</div>
        </div>
        <div class="td-editor-actions">
            @if($td->document_path)
                <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.document', $td) }}">Ouvrir l’original</a>
            @endif
            @if($td->correction_document_path)
                <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.correction_document', $td) }}">Ouvrir le corrigé</a>
            @endif
            <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.edit', $td) }}">Paramètres / fichiers</a>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.td.sets.update', $td) }}" enctype="multipart/form-data" class="td-editor-page" id="tdEditorForm">
        @csrf
        <input type="hidden" name="title" value="{{ $td->title }}">
        <input type="hidden" name="chapter_label" value="{{ $td->chapter_label }}">
        <input type="hidden" name="difficulty" value="{{ $td->difficulty ?? 'medium' }}">
        <input type="hidden" name="access_level" value="{{ $td->access_level ?? 'free' }}">
        <input type="hidden" name="correction_delay_minutes" value="{{ $td->correction_delay_minutes ?? 30 }}">
        <input type="hidden" name="status" value="{{ $td->status ?? 'draft' }}">
        <input type="hidden" name="editable_text" id="tdEditableText" value="{{ $td->editable_text }}">

        <div class="td-editor-layout" id="tdEditorLayout">
            <div class="td-editor-panel">
                <h3>Sujet à modifier</h3>
                <div class="td-editor-status" id="tdLoadStatus">
                    Chargement du sujet dans l’éditeur...
                </div>
                <div class="td-editor-toolbar" data-target="tdSubjectEditor">
                    <button type="button" class="td-editor-tool" data-format="bold">Gras</button>
                    <button type="button" class="td-editor-tool" data-format="italic">Italique</button>
                    <button type="button" class="td-editor-tool" data-format="h3">Titre</button>
                    <button type="button" class="td-editor-tool" data-format="ul">Liste</button>
                    <button type="button" class="td-editor-tool" data-preview="tdSubjectPreview">Aperçu</button>
                </div>
                <textarea name="editable_html" id="tdSubjectEditor" class="td-editor-textarea" placeholder="Le sujet à modifier apparaît ici...">{{ $subjectInitial }}</textarea>
                <div class="td-editor-preview" id="tdSubjectPreview"></div>
            </div>

            <aside class="td-editor-side" id="tdCorrectionSide">
                <div class="td-editor-side-card">
                    <h3>Actions</h3>
                    @if($td->document_path)
                        <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.document', $td) }}">Ouvrir l’original</a>
                    @endif
                    @if($td->correction_document_path)
                        <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.correction_document', $td) }}">Ouvrir le corrigé</a>
                    @endif
                    <button type="button" class="teacher-btn teacher-btn--primary td-correction-button" id="openCorrectionEditor">Modifier le corrigé</button>
                    <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.index') }}">Retour aux TD</a>
                </div>

                <div class="td-editor-side-card td-hidden-correction" id="tdCorrectionCard">
                    <h3>Corrigé à modifier</h3>
                    <div class="td-editor-toolbar" data-target="tdCorrectionEditor">
                        <button type="button" class="td-editor-tool" data-format="bold">Gras</button>
                        <button type="button" class="td-editor-tool" data-format="italic">Italique</button>
                        <button type="button" class="td-editor-tool" data-format="h3">Titre</button>
                        <button type="button" class="td-editor-tool" data-format="ul">Liste</button>
                        <button type="button" class="td-editor-tool" data-preview="tdCorrectionPreview">Aperçu</button>
                    </div>
                    <textarea name="correction_html" id="tdCorrectionEditor" class="td-editor-textarea" style="min-height:360px;" placeholder="Rédigez ou modifiez le corrigé ici...">{{ $correctionInitial }}</textarea>
                    <div class="td-editor-preview" id="tdCorrectionPreview"></div>
                </div>
            </aside>
        </div>

        <div class="td-editor-submit">
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-btn teacher-btn--ghost">Annuler</a>
            <button type="submit" class="teacher-btn teacher-btn--primary">Enregistrer les modifications</button>
        </div>
    </form>
</section>

<script src="https://unpkg.com/mammoth@1.7.2/mammoth.browser.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.min.mjs" type="module"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const documentUrl = @json($td->document_path ? route('teacher.td.sets.document', $td) : null);
    const documentName = @json($td->document_name ?: 'document');
    const initialHasSubject = @json(trim(strip_tags((string) $subjectInitial)) !== '');
    const editor = document.getElementById('tdSubjectEditor');
    const statusBox = document.getElementById('tdLoadStatus');

    function setStatus(message) { if (statusBox) statusBox.textContent = message; }

    function wrapSelection(textarea, before, after) {
        const start = textarea.selectionStart || 0;
        const end = textarea.selectionEnd || 0;
        const selected = textarea.value.substring(start, end) || 'texte';
        textarea.value = textarea.value.substring(0, start) + before + selected + after + textarea.value.substring(end);
        textarea.focus();
        textarea.selectionStart = start + before.length;
        textarea.selectionEnd = start + before.length + selected.length;
    }

    function escapeHtml(value) {
        return (value || '').replace(/[&<>\"]/g, function (char) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;'}[char];
        });
    }

    function textToHtml(text) {
        return (text || '').split(/\n{2,}/).map(function (part) {
            return '<p>' + escapeHtml(part).replace(/\n/g, '<br>') + '</p>';
        }).join('\n');
    }

    async function loadPdfText(arrayBuffer) {
        const pdfjsLib = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.min.mjs');
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.worker.min.mjs';
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        let text = '';
        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);
            const content = await page.getTextContent();
            text += content.items.map(item => item.str || '').join(' ') + '\n\n';
        }
        return text.trim();
    }

    async function autoLoadSubject() {
        if (!editor) return;
        if (initialHasSubject || editor.value.trim() !== '') {
            setStatus('Sujet chargé dans l’éditeur. Vous pouvez modifier puis enregistrer.');
            return;
        }
        if (!documentUrl) {
            setStatus('Aucun sujet original n’est joint. Rédigez directement le sujet dans l’éditeur.');
            return;
        }

        const lowerName = (documentName || '').toLowerCase();
        try {
            const response = await fetch(documentUrl, { credentials: 'same-origin' });
            if (!response.ok) throw new Error('document inaccessible');
            const buffer = await response.arrayBuffer();

            if (lowerName.endsWith('.docx') && window.mammoth) {
                const result = await window.mammoth.convertToHtml({ arrayBuffer: buffer });
                editor.value = result.value || '';
                setStatus('Sujet Word chargé dans l’éditeur. Vérifiez la mise en forme avant d’enregistrer.');
                return;
            }

            if (lowerName.endsWith('.pdf')) {
                const text = await loadPdfText(buffer);
                if (text.length > 30) {
                    editor.value = textToHtml(text);
                    setStatus('Sujet PDF texte chargé dans l’éditeur. Vérifiez la mise en forme avant d’enregistrer.');
                } else {
                    setStatus('Ce PDF semble scanné ou sans texte récupérable. Ouvrez l’original, puis copiez le contenu à modifier dans l’éditeur.');
                }
                return;
            }

            if (lowerName.endsWith('.txt') || lowerName.endsWith('.rtf') || lowerName.endsWith('.html') || lowerName.endsWith('.htm')) {
                const text = new TextDecoder('utf-8').decode(buffer);
                editor.value = lowerName.endsWith('.html') || lowerName.endsWith('.htm') ? text : textToHtml(text);
                setStatus('Sujet chargé dans l’éditeur. Vous pouvez modifier puis enregistrer.');
                return;
            }

            setStatus('Format non convertible automatiquement. Ouvrez l’original, puis copiez le contenu à modifier dans l’éditeur.');
        } catch (error) {
            setStatus('Chargement automatique impossible. Ouvrez l’original, puis copiez le contenu à modifier dans l’éditeur.');
        }
    }

    document.querySelectorAll('.td-editor-toolbar').forEach(function (toolbar) {
        const target = document.getElementById(toolbar.dataset.target);
        toolbar.querySelectorAll('[data-format]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!target) return;
                const format = button.dataset.format;
                if (format === 'bold') wrapSelection(target, '<strong>', '</strong>');
                if (format === 'italic') wrapSelection(target, '<em>', '</em>');
                if (format === 'h3') wrapSelection(target, '<h3>', '</h3>');
                if (format === 'ul') wrapSelection(target, '<ul><li>', '</li></ul>');
            });
        });
        toolbar.querySelectorAll('[data-preview]').forEach(function (button) {
            button.addEventListener('click', function () {
                const preview = document.getElementById(button.dataset.preview);
                if (!target || !preview) return;
                preview.innerHTML = target.value || '<p class="teacher-muted">Aucun contenu.</p>';
                preview.style.display = preview.style.display === 'block' ? 'none' : 'block';
            });
        });
    });

    const openCorrectionEditor = document.getElementById('openCorrectionEditor');
    const side = document.getElementById('tdCorrectionSide');
    if (openCorrectionEditor && side) {
        openCorrectionEditor.addEventListener('click', function () {
            side.classList.add('td-correction-open');
            const correction = document.getElementById('tdCorrectionEditor');
            if (correction) correction.focus();
        });
    }

    const form = document.getElementById('tdEditorForm');
    const hiddenText = document.getElementById('tdEditableText');
    if (form && editor && hiddenText) {
        form.addEventListener('submit', function () {
            hiddenText.value = editor.value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        });
    }

    autoLoadSubject();
});
</script>
@endsection
