@extends('layouts.admin')

@section('title', 'Source TD')
@section('page_title', 'Préparation de la source')
@section('page_subtitle', 'Texte et visuels sont traités séparément. Le bouton spécial ci-dessous reste réservé à l’administrateur uniquement.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head">
        <h2>{{ $source->title ?: 'Source sans titre' }}</h2>
        <div class="admin-actions">
            @if($source->source_file_path)
                <a href="{{ route('admin.td.sources.file', $source) }}" class="btn btn--ghost">Ouvrir le document source</a>
            @endif
            <form method="POST" action="{{ route('admin.td.sources.prepare', $source) }}">
                @csrf
                <button type="submit" class="btn btn--primary">Préparer pour ChatGPT</button>
            </form>
        </div>
    </div>

    <div class="admin-detail-grid">
        <div><strong>Type</strong><span>{{ $source->source_kind }}</span></div>
        <div><strong>Statut</strong><span class="admin-badge admin-badge--{{ $source->status }}">{{ $source->status }}</span></div>
        <div><strong>Classe</strong><span>{{ $source->detectedSchoolClass->name ?? 'À préciser' }}</span></div>
        <div><strong>Matière</strong><span>{{ $source->detectedSubject->name ?? 'À préciser' }}</span></div>
        <div><strong>Chapitre</strong><span>{{ $source->detected_chapter_label ?: 'À préciser' }}</span></div>
        <div><strong>Visuels extraits</strong><span>{{ $source->visuals->count() }}</span></div>
    </div>

    @if($source->analysis_notes)
        <div class="admin-rich-block">
            <h3>Analyse / notes</h3>
            <pre class="admin-pre">{{ $source->analysis_notes }}</pre>
        </div>
    @endif

    @if($source->working_text)
        <div class="admin-rich-block">
            <h3>Texte exploitable</h3>
            <pre class="admin-pre">{{ $source->workingText }}</pre>
        </div>
    @endif
</section>

<section class="admin-section">
    <div class="admin-section__head"><h2>Visuels pédagogiques</h2></div>
    <div class="admin-visual-grid">
        @forelse($source->visuals as $visual)
            <article class="admin-visual-card">
                <a href="{{ route('admin.td.sources.visual', $visual) }}" target="_blank"><img src="{{ route('admin.td.sources.visual', $visual) }}" alt="{{ $visual->file_name }}"></a>
                <form method="POST" action="{{ route('admin.td.sources.visuals.update', $visual) }}" class="admin-form">
                    @csrf
                    <div class="form-group"><label>Rôle du visuel</label><select name="visual_role"><option value="essential" @selected($visual->visual_role === 'essential')>Essentiel</option><option value="useful" @selected($visual->visual_role === 'useful')>Utile</option><option value="optional" @selected($visual->visual_role === 'optional')>Non nécessaire</option></select></div>
                    <div class="form-group"><label>Exercice concerné</label><input type="text" name="exercise_label" value="{{ $visual->exercise_label }}" placeholder="Exercice 2"></div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" rows="2">{{ $visual->notes }}</textarea></div>
                    <button type="submit" class="btn btn--ghost">Enregistrer le visuel</button>
                </form>
            </article>
        @empty
            <div class="admin-empty-box">Aucun visuel enregistré pour cette source.</div>
        @endforelse
    </div>
</section>

@if($source->prompt_ready_text)
<section class="admin-section">
    <div class="admin-section__head"><h2>Dossier de génération prêt</h2></div>
    <div class="admin-rich-block">
        <h3>Prompt prêt à copier dans ChatGPT</h3>
        <textarea class="admin-prompt-box" rows="18">{{ $source->prompt_ready_text }}</textarea>
        <small>Le logiciel ne se connecte pas à ChatGPT. Tu copies ce prompt, tu l’utilises dans ton propre compte, puis tu colles ici la réponse générée.</small>
    </div>

    <form method="POST" action="{{ route('admin.td.sources.store_result', $source) }}" class="admin-form">
        @csrf
        <div class="admin-form-grid">
            <div class="form-group admin-form-grid__full"><label>Titre final généré</label><input type="text" name="generated_title" required></div>
            <div class="form-group admin-form-grid__full"><label>Résumé final</label><textarea name="generated_summary" rows="3"></textarea></div>
            <div class="form-group admin-form-grid__full"><label>TD HTML généré</label><textarea name="generated_instructions_html" rows="12" required></textarea></div>
            <div class="form-group admin-form-grid__full"><label>Corrigé HTML généré</label><textarea name="generated_correction_html" rows="12"></textarea></div>
            <div class="form-group"><label>Variante</label><input type="text" name="variant_type" value="chatgpt_reworked"></div>
            <div class="form-group"><label>Durée estimée</label><input type="number" name="estimated_minutes" min="5" max="360"></div>
            <div class="form-group"><label>Accès</label><select name="access_level"><option value="free">Gratuit</option><option value="premium">Premium</option></select></div>
            <div class="form-group"><label>Type de TD</label><select name="td_type"><option value="training">Entraînement</option><option value="past_exam">Ancien sujet</option><option value="simulation">Simulation</option><option value="remedial">Remédiation</option></select></div>
            <div class="form-group"><label>Statut final</label><select name="status"><option value="draft">Brouillon</option><option value="submitted">Soumis pour validation</option><option value="published">Publié</option></select></div>
            <div class="form-group"><label>Règle corrigé</label><select name="correction_mode"><option value="after_submit">Après soumission</option><option value="immediate">Immédiat</option><option value="scheduled">Planifié</option><option value="premium_only">Premium uniquement</option></select></div>
            <div class="form-group admin-form-grid__full"><label>Notes de génération</label><textarea name="generation_notes" rows="4" placeholder="Expliquer ce qui a été conservé, inversé, allégé, renforcé, etc."></textarea></div>
        </div>
        <div class="admin-actions"><button type="submit" class="btn btn--primary">Réinjecter la réponse et créer le brouillon TD</button></div>
    </form>
</section>
@endif

@if($source->transformations->count())
<section class="admin-section">
    <div class="admin-section__head"><h2>Réinjections déjà créées</h2></div>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Variante</th><th>Titre</th><th>Statut</th><th>TD créé</th></tr></thead><tbody>
    @foreach($source->transformations as $transformation)
        <tr>
            <td>{{ $transformation->variant_type }}</td>
            <td>{{ $transformation->generated_title }}</td>
            <td><span class="admin-badge admin-badge--{{ $transformation->status }}">{{ $transformation->status }}</span></td>
            <td>@if($transformation->tdSet)<a href="{{ route('admin.td.sets.show', $transformation->tdSet) }}">{{ $transformation->tdSet->title }}</a>@else - @endif</td>
        </tr>
    @endforeach
    </tbody></table></div>
</section>
@endif
@endsection
