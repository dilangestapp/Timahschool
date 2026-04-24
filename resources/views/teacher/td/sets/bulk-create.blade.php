@extends('layouts.teacher')

@section('title', 'Importer plusieurs TD')
@section('page_title', 'Importer plusieurs TD')
@section('page_subtitle', 'Ajoutez chaque TD avec son corrigé juste en dessous pour éviter toute mauvaise association.')

@push('styles')
<style>
    .bulk-td-page { display:grid; gap:18px; }
    .bulk-td-panel,
    .bulk-td-row {
        background: rgba(255,255,255,.88);
        border: 1px solid rgba(148,163,184,.24);
        border-radius: 24px;
        box-shadow: 0 18px 42px rgba(15,23,42,.08);
        padding: 18px;
    }
    html[data-theme='dark'] .bulk-td-panel,
    html[data-theme='dark'] .bulk-td-row { background: rgba(15,23,42,.78); }
    .bulk-td-head { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; }
    .bulk-td-head h2 { margin:0 0 8px; letter-spacing:-.04em; }
    .bulk-td-muted { color: var(--teacher-muted, #64748b); font-weight: 750; line-height:1.45; }
    .bulk-td-settings { display:grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap:12px; margin-top:16px; }
    .bulk-td-field { display:grid; gap:6px; }
    .bulk-td-field label { font-weight:900; font-size:.82rem; color:var(--teacher-text, #0f172a); }
    .bulk-td-field input,
    .bulk-td-field select {
        min-height:46px;
        border-radius:14px;
        border:1px solid rgba(148,163,184,.30);
        padding:10px 12px;
        background:rgba(255,255,255,.9);
        color:var(--teacher-text,#0f172a);
    }
    html[data-theme='dark'] .bulk-td-field input,
    html[data-theme='dark'] .bulk-td-field select { background:rgba(2,6,23,.42); color:#f8fafc; }
    .bulk-td-list { display:grid; gap:14px; }
    .bulk-td-row { display:grid; gap:14px; }
    .bulk-td-row-title { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; }
    .bulk-td-row-title strong { font-size:1.05rem; letter-spacing:-.03em; }
    .bulk-td-pair { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
    .bulk-upload-box {
        border:1px dashed rgba(15,118,110,.38);
        border-radius:18px;
        padding:14px;
        background:rgba(15,118,110,.05);
        display:grid;
        gap:8px;
    }
    .bulk-upload-box strong { color:#115e59; }
    html[data-theme='dark'] .bulk-upload-box strong { color:#99f6e4; }
    .bulk-td-actions { display:flex; gap:10px; justify-content:space-between; flex-wrap:wrap; align-items:center; }
    .bulk-submit-bar {
        position:sticky;
        bottom:12px;
        z-index:20;
        display:flex;
        justify-content:flex-end;
        gap:10px;
        padding:12px;
        border-radius:20px;
        background:rgba(255,255,255,.9);
        border:1px solid rgba(148,163,184,.24);
        box-shadow:0 18px 42px rgba(15,23,42,.12);
        backdrop-filter:blur(14px);
    }
    html[data-theme='dark'] .bulk-submit-bar { background:rgba(15,23,42,.88); }
    @media (max-width: 980px) {
        .bulk-td-settings { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 720px) {
        .bulk-td-settings,
        .bulk-td-pair { grid-template-columns:1fr; }
        .bulk-submit-bar,
        .bulk-submit-bar .teacher-btn,
        .bulk-td-actions .teacher-btn { width:100%; }
        .bulk-submit-bar { display:grid; grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
<section class="bulk-td-page">
    <div class="bulk-td-panel">
        <div class="bulk-td-head">
            <div>
                <h2>Importation par paires TD + corrigé</h2>
                <p class="bulk-td-muted">Chaque ligne représente un seul TD. Vous chargez le sujet, puis juste en dessous son corrigé. Le système associe automatiquement le corrigé au TD de la même ligne.</p>
            </div>
            <a href="{{ route('teacher.td.sets.index') }}" class="teacher-btn teacher-btn--ghost">Retour aux TD</a>
        </div>

        <form method="POST" action="{{ route('teacher.td.sets.bulk_store') }}" enctype="multipart/form-data" class="bulk-td-page" id="bulkTdForm">
            @csrf
            <div class="bulk-td-settings">
                <div class="bulk-td-field">
                    <label>Classe / matière</label>
                    <select name="teacher_assignment_id" required>
                        <option value="">Choisir</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}" @selected(old('teacher_assignment_id') == $assignment->id)>
                                {{ $assignment->schoolClass->name ?? '-' }} — {{ $assignment->subject->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="bulk-td-field">
                    <label>Chapitre / thème</label>
                    <input type="text" name="chapter_label" value="{{ old('chapter_label') }}" placeholder="Facultatif">
                </div>
                <div class="bulk-td-field">
                    <label>Difficulté</label>
                    <select name="difficulty" required>
                        <option value="easy">Facile</option>
                        <option value="medium" selected>Moyen</option>
                        <option value="hard">Difficile</option>
                        <option value="exam">Type examen</option>
                    </select>
                </div>
                <div class="bulk-td-field">
                    <label>Accès</label>
                    <select name="access_level" required>
                        <option value="free">Gratuit</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="bulk-td-field">
                    <label>Statut</label>
                    <select name="status" required>
                        <option value="draft">Brouillon</option>
                        <option value="published" selected>Publié directement</option>
                    </select>
                </div>
                <div class="bulk-td-field">
                    <label>Temps avant corrigé</label>
                    <input type="number" name="correction_delay_minutes" min="0" max="1440" value="30" required>
                </div>
            </div>

            <div class="bulk-td-actions">
                <p class="bulk-td-muted">Ajoutez autant de lignes que nécessaire : TD 1 + corrigé 1, TD 2 + corrigé 2, etc.</p>
                <button type="button" class="teacher-btn teacher-btn--primary" id="addBulkRow">+ Ajouter une ligne TD</button>
            </div>

            <div class="bulk-td-list" id="bulkRows"></div>

            <div class="bulk-submit-bar">
                <a href="{{ route('teacher.td.sets.index') }}" class="teacher-btn teacher-btn--ghost">Annuler</a>
                <button type="submit" class="teacher-btn teacher-btn--primary">Créer tous les TD</button>
            </div>
        </form>
    </div>
</section>

<template id="bulkRowTemplate">
    <div class="bulk-td-row" data-row>
        <div class="bulk-td-row-title">
            <strong data-row-title>TD</strong>
            <button type="button" class="teacher-btn teacher-btn--danger" data-remove-row>Retirer cette ligne</button>
        </div>
        <div class="bulk-td-field">
            <label>Titre du TD</label>
            <input type="text" data-name="title" placeholder="Exemple : TD 1 - Nombres complexes">
        </div>
        <div class="bulk-td-pair">
            <div class="bulk-upload-box">
                <strong>Sujet TD</strong>
                <span class="bulk-td-muted">Chargez ici le fichier du TD.</span>
                <input type="file" data-name="document" accept=".pdf,.doc,.docx,.txt,.odt,.rtf,.html,.htm,.png,.jpg,.jpeg,.webp">
            </div>
            <div class="bulk-upload-box">
                <strong>Corrigé associé</strong>
                <span class="bulk-td-muted">Chargez ici le corrigé du TD de cette même ligne.</span>
                <input type="file" data-name="correction_document" accept=".pdf,.doc,.docx,.txt,.odt,.rtf,.html,.htm,.png,.jpg,.jpeg,.webp">
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('bulkRows');
    const template = document.getElementById('bulkRowTemplate');
    const addButton = document.getElementById('addBulkRow');

    function refreshRows() {
        list.querySelectorAll('[data-row]').forEach(function (row, index) {
            row.querySelector('[data-row-title]').textContent = 'TD ' + (index + 1) + ' + corrigé ' + (index + 1);
            row.querySelectorAll('[data-name]').forEach(function (field) {
                field.name = 'td_rows[' + index + '][' + field.dataset.name + ']';
            });
        });
    }

    function addRow() {
        const fragment = template.content.cloneNode(true);
        list.appendChild(fragment);
        refreshRows();
    }

    addButton.addEventListener('click', addRow);
    list.addEventListener('click', function (event) {
        const button = event.target.closest('[data-remove-row]');
        if (!button) return;
        const row = button.closest('[data-row]');
        if (row) row.remove();
        if (list.querySelectorAll('[data-row]').length === 0) addRow();
        refreshRows();
    });

    for (let i = 0; i < 5; i++) addRow();
});
</script>
@endsection
