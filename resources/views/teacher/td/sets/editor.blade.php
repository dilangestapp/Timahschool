@extends('layouts.teacher')

@section('title', 'Modifier le TD')
@section('page_title', 'Modifier le TD')
@section('page_subtitle', 'Le sujet s’ouvre directement dans un éditeur visuel, prêt à être corrigé et mis en forme.')

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
    .td-editor-toolbar { display: flex; gap: 8px; flex-wrap: wrap; align-items:center; }
    .td-editor-tool,
    .td-editor-select {
        border: 1px solid rgba(148,163,184,.28);
        border-radius: 999px;
        background: rgba(255,255,255,.92);
        padding: 8px 12px;
        font-weight: 900;
        cursor: pointer;
        color: var(--teacher-text, #0f172a);
        min-height: 40px;
    }
    .td-editor-select { border-radius: 14px; }
    html[data-theme='dark'] .td-editor-tool,
    html[data-theme='dark'] .td-editor-select { background: rgba(15,23,42,.86); color: #f8fafc; }
    .td-rich-editor {
        width: 100%;
        min-height: 650px;
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.28);
        padding: 26px 30px;
        background: #ffffff;
        color: #111827;
        font: 500 16px/1.72 Inter, system-ui, sans-serif;
        outline: none;
        overflow: auto;
    }
    .td-rich-editor:focus { border-color: rgba(15,118,110,.45); box-shadow: 0 0 0 4px rgba(15,118,110,.12); }
    .td-rich-editor h1,
    .td-rich-editor h2,
    .td-rich-editor h3 { color:#0f172a; line-height:1.25; margin: 18px 0 10px; }
    .td-rich-editor h1 { font-size: 1.75rem; text-align:center; text-transform: uppercase; }
    .td-rich-editor h2 { font-size: 1.35rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
    .td-rich-editor h3 { font-size: 1.08rem; color:#115e59; }
    .td-rich-editor p { margin: 0 0 12px; }
    .td-rich-editor ul,
    .td-rich-editor ol { margin: 8px 0 14px 24px; }
    .td-rich-editor table { width:100%; border-collapse: collapse; margin: 14px 0; }
    .td-rich-editor td,
    .td-rich-editor th { border: 1px solid #d1d5db; padding: 8px; }
    .td-rich-editor .exam-title { text-align:center; font-weight:900; text-transform:uppercase; letter-spacing:.04em; }
    .td-rich-editor .exercise-title { font-weight:900; color:#115e59; margin-top:18px; }
    html[data-theme='dark'] .td-rich-editor { background: rgba(2,6,23,.72); color:#f8fafc; }
    html[data-theme='dark'] .td-rich-editor h1,
    html[data-theme='dark'] .td-rich-editor h2 { color:#f8fafc; }
    html[data-theme='dark'] .td-rich-editor h3,
    html[data-theme='dark'] .td-rich-editor .exercise-title { color:#5eead4; }
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
    .td-correction-open { grid-column: 1 / -1; }
    @media (max-width: 920px) {
        .td-editor-layout { grid-template-columns: 1fr; }
        .td-editor-actions,
        .td-editor-submit,
        .td-editor-submit .teacher-btn,
        .td-editor-side-card .teacher-btn { width: 100%; }
        .td-editor-submit { display: grid; grid-template-columns: 1fr; }
        .td-rich-editor { min-height: 460px; padding: 18px; font-size: 15px; }
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
        <input type="hidden" name="editable_html" id="tdEditableHtml" value="">
        <input type="hidden" name="correction_html" id="tdCorrectionHtml" value="">

        <div class="td-editor-layout" id="tdEditorLayout">
            <div class="td-editor-panel">
                <h3>Sujet à modifier</h3>
                <div class="td-editor-status" id="tdLoadStatus">Chargement du sujet dans l’éditeur...</div>
                <div class="td-editor-toolbar" data-target="tdSubjectEditor">
                    <select class="td-editor-select" data-command="formatBlock">
                        <option value="p">Paragraphe</option>
                        <option value="h1">Grand titre</option>
                        <option value="h2">Titre section</option>
                        <option value="h3">Titre exercice</option>
                    </select>
                    <button type="button" class="td-editor-tool" data-command="bold">Gras</button>
                    <button type="button" class="td-editor-tool" data-command="italic">Italique</button>
                    <button type="button" class="td-editor-tool" data-command="underline">Souligner</button>
                    <button type="button" class="td-editor-tool" data-command="insertUnorderedList">Liste</button>
                    <button type="button" class="td-editor-tool" data-command="insertOrderedList">Numéros</button>
                    <button type="button" class="td-editor-tool" data-action="clean">Nettoyer</button>
                </div>
                <div id="tdSubjectEditor" class="td-rich-editor" contenteditable="true">{!! $subjectInitial !!}</div>
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
                        <select class="td-editor-select" data-command="formatBlock">
                            <option value="p">Paragraphe</option>
                            <option value="h2">Titre</option>
                            <option value="h3">Sous-titre</option>
                        </select>
                        <button type="button" class="td-editor-tool" data-command="bold">Gras</button>
                        <button type="button" class="td-editor-tool" data-command="italic">Italique</button>
                        <button type="button" class="td-editor-tool" data-command="insertUnorderedList">Liste</button>
                        <button type="button" class="td-editor-tool" data-command="insertOrderedList">Numéros</button>
                    </div>
                    <div id="tdCorrectionEditor" class="td-rich-editor" contenteditable="true" style="min-height:360px;">{!! $correctionInitial !!}</div>
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
    const correctionEditor = document.getElementById('tdCorrectionEditor');
    const statusBox = document.getElementById('tdLoadStatus');

    function setStatus(message) { if (statusBox) statusBox.textContent = message; }

    function escapeHtml(value) {
        return (value || '').replace(/[&<>\"]/g, function (char) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;'}[char];
        });
    }

    function normalizeText(value) {
        return (value || '').replace(/\s+/g, ' ').trim();
    }

    function isExerciseLine(line) {
        return /^(exercice|partie|problème|probleme|question)\s*[\w\d-]*/i.test(line)
            || /^\d+[\).\-]\s+/.test(line);
    }

    function formatExtractedText(text) {
        let cleaned = (text || '')
            .replace(/<\/?p>/gi, '\n')
            .replace(/\r/g, '\n')
            .replace(/\s+([,.;:!?])/g, '$1')
            .replace(/([.!?])\s+(?=(Exercice|Partie|Problème|Probleme|Question)\b)/gi, '$1\n\n')
            .replace(/\s+(?=(Exercice\s+\d+|Partie\s+[A-Z]|Problème|Probleme)\b)/gi, '\n\n')
            .trim();

        const rawLines = cleaned.split(/\n+/).map(l => normalizeText(l)).filter(Boolean);
        const html = [];

        rawLines.forEach((line, index) => {
            const safe = escapeHtml(line);
            if (index === 0 && line.length < 140) {
                html.push('<h1>' + safe + '</h1>');
            } else if (isExerciseLine(line)) {
                html.push('<h3 class="exercise-title">' + safe + '</h3>');
            } else if (/^(code de suivi|durée|classe|matière|sujet)/i.test(line) && line.length < 180) {
                html.push('<p><strong>' + safe + '</strong></p>');
            } else {
                html.push('<p>' + safe + '</p>');
            }
        });

        return html.join('\n');
    }

    async function loadPdfText(arrayBuffer) {
        const pdfjsLib = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.min.mjs');
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.worker.min.mjs';
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        const pages = [];

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);
            const content = await page.getTextContent();
            const rows = new Map();

            content.items.forEach(item => {
                const str = normalizeText(item.str || '');
                if (!str) return;
                const y = Math.round((item.transform && item.transform[5] ? item.transform[5] : 0) / 3) * 3;
                const x = item.transform && item.transform[4] ? item.transform[4] : 0;
                if (!rows.has(y)) rows.set(y, []);
                rows.get(y).push({x, str});
            });

            const lines = Array.from(rows.keys()).sort((a, b) => b - a).map(y => {
                return rows.get(y).sort((a, b) => a.x - b.x).map(item => item.str).join(' ');
            });

            pages.push(lines.join('\n'));
        }
        return pages.join('\n\n').trim();
    }

    async function autoLoadSubject() {
        if (!editor) return;
        if (initialHasSubject || normalizeText(editor.textContent) !== '') {
            setStatus('Sujet chargé avec mise en forme. Vous pouvez modifier directement le contenu affiché.');
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
                editor.innerHTML = result.value || '';
                setStatus('Sujet Word chargé avec sa mise en forme. Vous pouvez modifier directement.');
                return;
            }

            if (lowerName.endsWith('.pdf')) {
                const text = await loadPdfText(buffer);
                if (text.length > 30) {
                    editor.innerHTML = formatExtractedText(text);
                    setStatus('Sujet PDF texte chargé avec une mise en page automatique. Vérifiez puis enregistrez.');
                } else {
                    setStatus('Ce PDF semble scanné. Ouvrez l’original, puis copiez le contenu à modifier dans l’éditeur.');
                }
                return;
            }

            if (lowerName.endsWith('.txt') || lowerName.endsWith('.rtf') || lowerName.endsWith('.html') || lowerName.endsWith('.htm')) {
                const text = new TextDecoder('utf-8').decode(buffer);
                editor.innerHTML = (lowerName.endsWith('.html') || lowerName.endsWith('.htm')) ? text : formatExtractedText(text);
                setStatus('Sujet chargé avec mise en forme. Vous pouvez modifier directement.');
                return;
            }

            setStatus('Format non convertible automatiquement. Ouvrez l’original, puis copiez le contenu à modifier dans l’éditeur.');
        } catch (error) {
            setStatus('Chargement automatique impossible. Ouvrez l’original, puis copiez le contenu à modifier dans l’éditeur.');
        }
    }

    function focusTarget(toolbar) {
        const target = document.getElementById(toolbar.dataset.target);
        if (target) target.focus();
        return target;
    }

    document.querySelectorAll('.td-editor-toolbar').forEach(function (toolbar) {
        toolbar.querySelectorAll('[data-command]').forEach(function (control) {
            control.addEventListener('click', function () {
                const target = focusTarget(toolbar);
                if (!target) return;
                const command = control.dataset.command;
                const value = control.tagName === 'SELECT' ? control.value : null;
                if (command === 'formatBlock') {
                    document.execCommand(command, false, value);
                } else {
                    document.execCommand(command, false, null);
                }
            });
            control.addEventListener('change', function () {
                const target = focusTarget(toolbar);
                if (!target) return;
                if (control.dataset.command === 'formatBlock') document.execCommand('formatBlock', false, control.value);
            });
        });
        toolbar.querySelectorAll('[data-action="clean"]').forEach(function (button) {
            button.addEventListener('click', function () {
                const target = focusTarget(toolbar);
                if (!target) return;
                target.innerHTML = formatExtractedText(target.innerText || target.textContent || '');
            });
        });
    });

    const openCorrectionEditor = document.getElementById('openCorrectionEditor');
    const side = document.getElementById('tdCorrectionSide');
    if (openCorrectionEditor && side) {
        openCorrectionEditor.addEventListener('click', function () {
            side.classList.add('td-correction-open');
            if (correctionEditor) correctionEditor.focus();
        });
    }

    const form = document.getElementById('tdEditorForm');
    const hiddenText = document.getElementById('tdEditableText');
    const hiddenHtml = document.getElementById('tdEditableHtml');
    const hiddenCorrection = document.getElementById('tdCorrectionHtml');
    if (form && editor && hiddenText && hiddenHtml && hiddenCorrection) {
        form.addEventListener('submit', function () {
            hiddenHtml.value = editor.innerHTML;
            hiddenText.value = normalizeText(editor.innerText || editor.textContent || '');
            hiddenCorrection.value = correctionEditor ? correctionEditor.innerHTML : '';
        });
    }

    autoLoadSubject();
});
</script>
@endsection
