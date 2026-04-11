@extends('layouts.teacher')

@section('title', 'Détail de la source TD')
@section('page_title', 'Détail de la source TD')
@section('page_subtitle', 'Analyse pédagogique, détection automatique et transformation vers un nouveau brouillon TD.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head teacher-head-split">
        <div>
            <h2>{{ $source->title ?: 'Source sans titre' }}</h2>
            <p class="teacher-muted">{{ $source->teacherAssignment->schoolClass->name ?? '-' }} • {{ $source->teacherAssignment->subject->name ?? '-' }}</p>
        </div>
        <div class="teacher-form-actions teacher-form-actions--inline">
            <form method="POST" action="{{ route('teacher.td.sources.analyze', $source) }}">@csrf<button type="submit" class="teacher-btn teacher-btn--primary">Analyser la source</button></form>
            @if($source->source_file_path)
                <a href="{{ route('teacher.td.sources.file', $source) }}" class="teacher-btn teacher-btn--ghost">Ouvrir le fichier</a>
            @endif
        </div>
    </div>

    <div class="teacher-form-grid">
        <div class="teacher-card"><strong>Statut</strong><p><span class="teacher-badge teacher-badge--{{ $source->status }}">{{ $source->status }}</span></p></div>
        <div class="teacher-card"><strong>Type</strong><p>{{ $source->source_kind }}</p></div>
        <div class="teacher-card"><strong>Classe détectée</strong><p>{{ $source->detectedSchoolClass->name ?? 'Non détectée' }}</p></div>
        <div class="teacher-card"><strong>Matière détectée</strong><p>{{ $source->detectedSubject->name ?? 'Non détectée' }}</p></div>
        <div class="teacher-card"><strong>Chapitre</strong><p>{{ $source->detected_chapter_label ?: 'Non détecté' }}</p></div>
        <div class="teacher-card"><strong>Difficulté</strong><p>{{ $source->detected_difficulty ?: '-' }}</p></div>
    </div>

    @if($source->analysis_notes)
        <div class="teacher-rich-card">
            <h3>Analyse détectée</h3>
            <pre class="teacher-pre">{{ $source->analysis_notes }}</pre>
        </div>
    @endif

    @if($source->working_text)
        <div class="teacher-rich-card">
            <h3>Contenu source exploitable</h3>
            <pre class="teacher-pre">{{ $source->working_text }}</pre>
        </div>
    @endif
</section>

<section class="teacher-section">
    <div class="teacher-section__head"><h2>Générer une nouvelle version</h2></div>
    <form method="POST" action="{{ route('teacher.td.sources.generate', $source) }}" class="teacher-form-grid">
        @csrf
        <div class="teacher-form-group">
            <label>Variante</label>
            <select name="variant_type" required>
                <option value="similar">TD similaire mais reformulé</option>
                <option value="easier">Version plus facile</option>
                <option value="harder">Version plus difficile</option>
                <option value="inverted">Version inversée</option>
                <option value="fresh">Nouvelle évaluation</option>
            </select>
        </div>
        <div class="teacher-form-group">
            <label>Affectation cible</label>
            <select name="teacher_assignment_id">
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}" @selected((int) $assignment->id === (int) $source->teacher_assignment_id)>{{ $assignment->schoolClass->name ?? '-' }} — {{ $assignment->subject->name ?? '-' }}</option>
                @endforeach
            </select>
        </div>
        <div class="teacher-form-group">
            <label>Accès</label>
            <select name="access_level">
                <option value="free">Gratuit</option>
                <option value="premium">Premium</option>
            </select>
        </div>
        <div class="teacher-form-group">
            <label>Statut cible</label>
            <select name="target_status">
                <option value="draft">Brouillon</option>
                <option value="submitted">Soumettre à validation</option>
            </select>
        </div>
        <div class="teacher-form-group teacher-form-group--full">
            <label>Consignes complémentaires</label>
            <textarea name="generation_notes" rows="5" placeholder="Ex. Ajouter plus d’applications numériques, changer totalement le contexte, viser un niveau terminale C, produire un corrigé très détaillé."></textarea>
        </div>
        <div class="teacher-form-actions">
            <button type="submit" class="teacher-btn teacher-btn--primary">Générer un brouillon TD</button>
        </div>
    </form>
</section>

<section class="teacher-panel">
    <div class="teacher-panel__head"><h2>Transformations déjà générées</h2></div>
    <div class="teacher-table-wrap">
        <table class="teacher-table">
            <thead><tr><th>Variante</th><th>Titre généré</th><th>Statut</th><th>Brouillon TD</th></tr></thead>
            <tbody>
            @forelse($source->transformations as $transformation)
                <tr>
                    <td>{{ $transformation->variant_type }}</td>
                    <td>{{ $transformation->transformed_title }}</td>
                    <td><span class="teacher-badge teacher-badge--{{ $transformation->status }}">{{ $transformation->status }}</span></td>
                    <td>
                        @if($transformation->tdSet)
                            <a href="{{ route('teacher.td.sets.edit', $transformation->tdSet) }}">Ouvrir le brouillon</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Aucune transformation générée pour le moment.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
