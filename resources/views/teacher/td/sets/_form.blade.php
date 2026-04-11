@php
    $assignmentValue = old('teacher_assignment_id', $tdSet->teacher_assignment_id ?? ($assignments->first()->id ?? null));
@endphp
<div class="teacher-form-grid">
    <div class="teacher-form-group">
        <label for="teacher_assignment_id">Affectation</label>
        <select name="teacher_assignment_id" id="teacher_assignment_id" required>
            @foreach($assignments as $assignment)
                <option value="{{ $assignment->id }}" @selected((string)$assignmentValue === (string)$assignment->id)>
                    {{ $assignment->schoolClass->name ?? '-' }} — {{ $assignment->subject->name ?? '-' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="teacher-form-group">
        <label for="title">Titre du TD</label>
        <input type="text" name="title" id="title" value="{{ old('title', $tdSet->title) }}" required>
    </div>
    <div class="teacher-form-group">
        <label for="chapter_label">Chapitre / thème</label>
        <input type="text" name="chapter_label" id="chapter_label" value="{{ old('chapter_label', $tdSet->chapter_label) }}">
    </div>
    <div class="teacher-form-group">
        <label for="estimated_minutes">Durée estimée (minutes)</label>
        <input type="number" name="estimated_minutes" id="estimated_minutes" min="5" max="360" value="{{ old('estimated_minutes', $tdSet->estimated_minutes) }}">
    </div>
    <div class="teacher-form-group teacher-form-group--full">
        <label for="summary">Résumé court</label>
        <textarea name="summary" id="summary" rows="3">{{ old('summary', $tdSet->summary) }}</textarea>
    </div>
    <div class="teacher-form-group">
        <label for="difficulty">Niveau</label>
        <select name="difficulty" id="difficulty">
            @foreach(['easy' => 'Facile', 'medium' => 'Moyen', 'exam' => 'Niveau examen'] as $value => $label)
                <option value="{{ $value }}" @selected(old('difficulty', $tdSet->difficulty) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="teacher-form-group">
        <label for="td_type">Type de TD</label>
        <select name="td_type" id="td_type">
            @foreach(['training' => 'Entraînement', 'past_exam' => 'Ancien sujet', 'simulation' => 'Simulation', 'remedial' => 'Remédiation'] as $value => $label)
                <option value="{{ $value }}" @selected(old('td_type', $tdSet->td_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="teacher-form-group">
        <label for="access_level">Accès</label>
        <select name="access_level" id="access_level">
            <option value="free" @selected(old('access_level', $tdSet->access_level) === 'free')>Gratuit</option>
            <option value="premium" @selected(old('access_level', $tdSet->access_level) === 'premium')>Premium</option>
        </select>
        <small>Conseil business : un TD gratuit par matière, le reste en premium.</small>
    </div>
    <div class="teacher-form-group">
        <label for="status">Statut</label>
        <select name="status" id="status">
            @foreach(['draft' => 'Brouillon', 'submitted' => 'Soumis pour validation', 'published' => 'Publié', 'archived' => 'Archivé'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $tdSet->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="teacher-form-group">
        <label for="correction_mode">Règle d’accès au corrigé</label>
        <select name="correction_mode" id="correction_mode">
            @foreach(['immediate' => 'Immédiat', 'after_submit' => 'Après soumission', 'scheduled' => 'À une date donnée', 'premium_only' => 'Premium uniquement'] as $value => $label)
                <option value="{{ $value }}" @selected(old('correction_mode', $tdSet->correction_mode) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="teacher-form-group">
        <label for="correction_release_at">Date d’ouverture du corrigé</label>
        <input type="datetime-local" name="correction_release_at" id="correction_release_at" value="{{ old('correction_release_at', optional($tdSet->correction_release_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="teacher-form-group">
        <label for="source_type">Source</label>
        <select name="source_type" id="source_type">
            @foreach(['original' => 'Original', 'curated' => 'Sélectionné', 'past_paper' => 'Ancien sujet', 'imported' => 'Importé'] as $value => $label)
                <option value="{{ $value }}" @selected(old('source_type', $tdSet->source_type ?? 'original') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="teacher-form-group">
        <label for="source_label">Référence / source</label>
        <input type="text" name="source_label" id="source_label" value="{{ old('source_label', $tdSet->source_label) }}">
    </div>
    <div class="teacher-form-group">
        <label for="license_type">Licence / droit d’utilisation</label>
        <input type="text" name="license_type" id="license_type" value="{{ old('license_type', $tdSet->license_type) }}">
    </div>
    <div class="teacher-form-group teacher-form-group--checkbox">
        <label><input type="checkbox" name="rights_confirmed" value="1" @checked(old('rights_confirmed', $tdSet->rights_confirmed))> Je confirme disposer des droits nécessaires pour publier ce contenu.</label>
    </div>

    <div class="teacher-form-group teacher-form-group--full">
        <label for="instructions_html">Énoncé du TD</label>
        <textarea class="js-td-editor" name="instructions_html" id="instructions_html" rows="12">{{ old('instructions_html', $tdSet->instructions_html) }}</textarea>
    </div>

    <div class="teacher-form-group teacher-form-group--full">
        <label for="correction_html">Corrigé rédigé</label>
        <textarea class="js-td-editor" name="correction_html" id="correction_html" rows="12">{{ old('correction_html', $tdSet->correction_html) }}</textarea>
    </div>

    <div class="teacher-form-group">
        <label for="document">Document TD (PDF, Word, image...)</label>
        <input type="file" name="document" id="document">
        @if(!empty($tdSet->document_name))
            <small>Document actuel : {{ $tdSet->document_name }}</small>
            <label class="teacher-inline-check"><input type="checkbox" name="remove_document" value="1"> Supprimer le document actuel</label>
        @endif
    </div>

    <div class="teacher-form-group">
        <label for="correction_document">Document corrigé</label>
        <input type="file" name="correction_document" id="correction_document">
        @if(!empty($tdSet->correction_document_name))
            <small>Document actuel : {{ $tdSet->correction_document_name }}</small>
            <label class="teacher-inline-check"><input type="checkbox" name="remove_correction_document" value="1"> Supprimer le document corrigé actuel</label>
        @endif
    </div>
</div>
