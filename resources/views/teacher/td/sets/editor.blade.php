@extends('layouts.teacher')

@section('title', 'Éditeur du TD')
@section('page_title', 'Éditeur du TD')
@section('page_subtitle', 'Modifiez directement le contenu du sujet et du corrigé sans passer par le formulaire complet.')

@push('styles')
<style>
    .quick-editor-shell {
        display: grid;
        gap: 18px;
    }
    .quick-editor-hero,
    .quick-editor-card {
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(148,163,184,.25);
        border-radius: 24px;
        box-shadow: 0 18px 42px rgba(15,23,42,.08);
        padding: 18px;
    }
    html[data-theme='dark'] .quick-editor-hero,
    html[data-theme='dark'] .quick-editor-card {
        background: rgba(15,23,42,.78);
    }
    .quick-editor-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .quick-editor-hero h2 {
        margin: 0 0 8px;
        letter-spacing: -.04em;
    }
    .quick-editor-meta {
        color: var(--teacher-muted, #64748b);
        font-weight: 800;
        line-height: 1.45;
    }
    .quick-editor-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .quick-editor-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .quick-editor-card h3 {
        margin: 0 0 8px;
        letter-spacing: -.03em;
    }
    .quick-editor-card textarea {
        width: 100%;
        min-height: 460px;
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.28);
        padding: 16px;
        background: rgba(248,250,252,.86);
        color: var(--teacher-text, #0f172a);
        font: 500 15px/1.6 Inter, system-ui, sans-serif;
        resize: vertical;
    }
    html[data-theme='dark'] .quick-editor-card textarea {
        background: rgba(2,6,23,.44);
        color: #f8fafc;
    }
    .quick-editor-toolbar {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .quick-editor-tool {
        border: 1px solid rgba(148,163,184,.28);
        border-radius: 999px;
        background: rgba(255,255,255,.9);
        padding: 8px 12px;
        font-weight: 900;
        cursor: pointer;
    }
    html[data-theme='dark'] .quick-editor-tool {
        background: rgba(15,23,42,.86);
        color: #f8fafc;
    }
    .quick-editor-preview {
        min-height: 220px;
        border-radius: 18px;
        border: 1px dashed rgba(15,118,110,.35);
        background: rgba(15,118,110,.05);
        padding: 16px;
        overflow: auto;
    }
    .quick-editor-submit {
        position: sticky;
        bottom: 12px;
        z-index: 10;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px;
        border-radius: 20px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(148,163,184,.24);
        backdrop-filter: blur(14px);
        box-shadow: 0 18px 40px rgba(15,23,42,.10);
    }
    html[data-theme='dark'] .quick-editor-submit {
        background: rgba(15,23,42,.86);
    }
    @media (max-width: 900px) {
        .quick-editor-grid {
            grid-template-columns: 1fr;
        }
        .quick-editor-card textarea {
            min-height: 360px;
        }
        .quick-editor-actions,
        .quick-editor-submit,
        .quick-editor-submit .teacher-btn {
            width: 100%;
        }
        .quick-editor-submit {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="quick-editor-shell">
    <div class="quick-editor-hero">
        <div>
            <h2>{{ $td->title }}</h2>
            <div class="quick-editor-meta">
                {{ $td->schoolClass->name ?? '-' }} · {{ $td->subject->name ?? '-' }} · {{ $td->chapter_label ?: 'Sans chapitre' }}
            </div>
            <div class="quick-editor-meta">Temps avant corrigé : {{ $td->correction_delay_minutes ?? 30 }} minute(s)</div>
        </div>
        <div class="quick-editor-actions">
            @if($td->document_path)
                <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.document', $td) }}">Ouvrir le sujet original</a>
            @endif
            @if($td->correction_document_path)
                <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.correction_document', $td) }}">Ouvrir le corrigé original</a>
            @endif
            <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.td.sets.edit', $td) }}">Formulaire complet</a>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.td.sets.update', $td) }}" enctype="multipart/form-data" class="quick-editor-shell" id="quickTdEditorForm">
        @csrf
        <input type="hidden" name="title" value="{{ $td->title }}">
        <input type="hidden" name="chapter_label" value="{{ $td->chapter_label }}">
        <input type="hidden" name="difficulty" value="{{ $td->difficulty ?? 'medium' }}">
        <input type="hidden" name="access_level" value="{{ $td->access_level ?? 'free' }}">
        <input type="hidden" name="correction_delay_minutes" value="{{ $td->correction_delay_minutes ?? 30 }}">
        <input type="hidden" name="status" value="{{ $td->status ?? 'draft' }}">
        <input type="hidden" name="editable_text" id="quickEditableText" value="{{ $td->editable_text }}">

        <div class="quick-editor-grid">
            <div class="quick-editor-card">
                <h3>Sujet du TD</h3>
                <p class="teacher-muted">Modifiez ici la version texte du TD. Si le document original n’a pas encore été converti, collez ou rédigez le contenu ici.</p>
                <div class="quick-editor-toolbar" data-target="quickSubjectEditor">
                    <button type="button" class="quick-editor-tool" data-format="bold">Gras</button>
                    <button type="button" class="quick-editor-tool" data-format="italic">Italique</button>
                    <button type="button" class="quick-editor-tool" data-format="h3">Titre</button>
                    <button type="button" class="quick-editor-tool" data-format="ul">Liste</button>
                    <button type="button" class="quick-editor-tool" data-preview="quickSubjectPreview">Aperçu</button>
                </div>
                <textarea name="editable_html" id="quickSubjectEditor" placeholder="Rédigez ou collez le contenu du TD ici...">{{ old('editable_html', $td->editable_html) }}</textarea>
                <div class="quick-editor-preview" id="quickSubjectPreview" style="display:none;"></div>
            </div>

            <div class="quick-editor-card" id="correction-zone">
                <h3>Corrigé du TD</h3>
                <p class="teacher-muted">Rédigez ou améliorez le corrigé. Il sera visible par l’élève uniquement après le délai et après validation du TD.</p>
                <div class="quick-editor-toolbar" data-target="quickCorrectionEditor">
                    <button type="button" class="quick-editor-tool" data-format="bold">Gras</button>
                    <button type="button" class="quick-editor-tool" data-format="italic">Italique</button>
                    <button type="button" class="quick-editor-tool" data-format="h3">Titre</button>
                    <button type="button" class="quick-editor-tool" data-format="ul">Liste</button>
                    <button type="button" class="quick-editor-tool" data-preview="quickCorrectionPreview">Aperçu</button>
                </div>
                <textarea name="correction_html" id="quickCorrectionEditor" placeholder="Rédigez ou collez le corrigé ici...">{{ old('correction_html', $td->correction_html) }}</textarea>
                <div class="quick-editor-preview" id="quickCorrectionPreview" style="display:none;"></div>
            </div>
        </div>

        <div class="quick-editor-submit">
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-btn teacher-btn--ghost">Retour aux TD</a>
            <button type="submit" class="teacher-btn teacher-btn--primary">Enregistrer le TD</button>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function wrapSelection(textarea, before, after) {
        const start = textarea.selectionStart || 0;
        const end = textarea.selectionEnd || 0;
        const selected = textarea.value.substring(start, end) || 'texte';
        textarea.value = textarea.value.substring(0, start) + before + selected + after + textarea.value.substring(end);
        textarea.focus();
        textarea.selectionStart = start + before.length;
        textarea.selectionEnd = start + before.length + selected.length;
    }

    document.querySelectorAll('.quick-editor-toolbar').forEach(function (toolbar) {
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
                preview.style.display = preview.style.display === 'none' ? 'block' : 'none';
            });
        });
    });

    const form = document.getElementById('quickTdEditorForm');
    const subject = document.getElementById('quickSubjectEditor');
    const hiddenText = document.getElementById('quickEditableText');
    if (form && subject && hiddenText) {
        form.addEventListener('submit', function () {
            hiddenText.value = subject.value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        });
    }
});
</script>
@endsection
