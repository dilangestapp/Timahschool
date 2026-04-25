@extends('layouts.admin')

@section('title', 'TD')
@section('page_title', 'Supervision des TD')
@section('page_subtitle', 'Consultez, ouvrez, modifiez, publiez et paramétrez les TD directement depuis cette page.')

@section('content')
<div class="admin-compact-page">
    <div class="admin-summary-strip">
        <div class="admin-summary-card"><strong>{{ $sets->total() }}</strong><span>TD enregistrés</span></div>
        <div class="admin-summary-card"><strong>{{ $sets->where('status', 'published')->count() }}</strong><span>publiés ici</span></div>
        <div class="admin-summary-card"><strong>{{ $sets->where('access_level', 'free')->count() }}</strong><span>gratuits ici</span></div>
        <div class="admin-summary-card"><strong>{{ $openQuestionCount }}</strong><span>questions ouvertes</span></div>
    </div>

    <details class="admin-collapse-box" {{ !empty($filters['q']) || !empty($filters['status']) || !empty($filters['access_level']) ? 'open' : '' }}>
        <summary>Rechercher et filtrer les TD</summary>
        <div class="admin-collapse-box__body">
            <form method="GET" class="admin-search-form admin-search-form--stack">
                <input type="text" name="q" placeholder="Rechercher un TD" value="{{ $filters['q'] ?? '' }}">
                <select name="status">
                    <option value="">Tous statuts</option>
                    @foreach(['draft' => 'Brouillon', 'published' => 'Publié', 'archived' => 'Désactivé'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="access_level">
                    <option value="">Tous accès</option>
                    <option value="free" @selected(($filters['access_level'] ?? '') === 'free')>Gratuit</option>
                    <option value="premium" @selected(($filters['access_level'] ?? '') === 'premium')>Premium</option>
                </select>
                <button class="btn btn--primary">Filtrer</button>
                <a href="{{ route('admin.td.index') }}" class="btn btn--ghost">Réinitialiser</a>
                <a href="{{ route('admin.td.create') }}" class="btn btn--primary">+ Nouveau TD</a>
            </form>
        </div>
    </details>

    <section class="admin-list-panel">
        <div class="admin-list-panel__head">
            <div>
                <h2>Liste des TD</h2>
                <p>{{ $questionCount }} discussion(s) TD, dont {{ $openQuestionCount }} ouverte(s). L’admin peut gérer entièrement chaque TD depuis la fiche.</p>
            </div>
            <a href="{{ route('admin.td.create') }}" class="btn btn--primary">+ Nouveau TD</a>
        </div>

        <div class="admin-clean-list">
            @forelse($sets as $td)
                <article class="admin-td-card">
                    <div class="admin-td-card__main">
                        <div class="admin-title-with-avatar">
                            <div class="admin-subscription-avatar">TD</div>
                            <div class="admin-clean-title">
                                <strong>{{ $td->title }}</strong>
                                <span>{{ $td->chapter_label ?: 'Sans chapitre' }} · Auteur : {{ $td->author->full_name ?? $td->author->name ?? $td->author->username ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="admin-clean-meta">
                            <strong>{{ $td->schoolClass->name ?? '-' }} · {{ $td->subject->name ?? '-' }}</strong><br>
                            <span class="admin-badge">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span>
                            <span class="admin-badge {{ $td->status === 'published' ? 'admin-badge--success' : ($td->status === 'archived' ? 'admin-badge--warning' : '') }}">
                                {{ $td->status === 'archived' ? 'désactivé' : $td->status }}
                            </span>
                            <span class="admin-badge">{{ $td->correctionDelayMinutes() }} min corrigé</span>
                        </div>

                        <div class="admin-td-docs">
                            @if($td->document_path)
                                <a href="{{ route('admin.td.document', $td) }}" target="_blank" class="btn btn--ghost">Ouvrir sujet</a>
                            @endif
                            @if($td->correction_document_path)
                                <a href="{{ route('admin.td.correction_document', $td) }}" target="_blank" class="btn btn--ghost">Ouvrir corrigé</a>
                            @elseif(!empty($td->correction_html))
                                <a href="#td-manage-{{ $td->id }}" onclick="document.getElementById('td-manage-{{ $td->id }}').open = true;" class="btn btn--ghost">Corrigé texte</a>
                            @endif
                        </div>

                        <div class="admin-td-actions">
                            <a href="#td-manage-{{ $td->id }}" onclick="document.getElementById('td-manage-{{ $td->id }}').open = true;" class="btn btn--primary">Gérer</a>
                            <a href="{{ route('admin.td.edit', $td) }}?mode=editor#editor-zone" class="btn btn--ghost">Éditeur</a>
                            @if($td->status !== 'published')
                                <form method="POST" action="{{ route('admin.td.publish', $td) }}">@csrf<button class="btn btn--primary">Publier</button></form>
                            @endif
                            @if($td->status !== 'archived')
                                <form method="POST" action="{{ route('admin.td.archive', $td) }}" onsubmit="return confirm('Désactiver temporairement ce TD ?');">@csrf<button class="btn btn--ghost">Désactiver</button></form>
                            @else
                                <form method="POST" action="{{ route('admin.td.publish', $td) }}" onsubmit="return confirm('Réactiver et republier ce TD ?');">@csrf<button class="btn btn--primary">Réactiver</button></form>
                            @endif
                            <form method="POST" action="{{ route('admin.td.delete', $td) }}" onsubmit="return confirm('Supprimer définitivement ce TD ?');">@csrf<button class="btn btn--danger">Supprimer</button></form>
                        </div>
                    </div>

                    <details class="admin-subscription-manage admin-td-manage" id="td-manage-{{ $td->id }}">
                        <summary>Gestion complète du TD : {{ $td->title }}</summary>
                        <form method="POST" action="{{ route('admin.td.update', $td) }}" enctype="multipart/form-data" class="admin-form admin-td-form">
                            @csrf
                            <div class="admin-form-grid">
                                <div class="form-group admin-form-grid__full">
                                    <label>Affectation enseignant / classe / matière</label>
                                    <select name="teacher_assignment_id" required>
                                        @foreach($assignments as $assignment)
                                            <option value="{{ $assignment->id }}" @selected((int)($td->teacher_assignment_id ?? 0) === (int)$assignment->id)>
                                                {{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? 'Enseignant' }} — {{ $assignment->schoolClass->name ?? 'Classe' }} — {{ $assignment->subject->name ?? 'Matière' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Titre du TD</label>
                                    <input type="text" name="title" value="{{ $td->title }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Chapitre / thème</label>
                                    <input type="text" name="chapter_label" value="{{ $td->chapter_label }}" placeholder="Sans chapitre">
                                </div>
                                <div class="form-group">
                                    <label>Difficulté</label>
                                    <select name="difficulty" required>
                                        @foreach(['easy' => 'Facile', 'medium' => 'Moyen', 'hard' => 'Difficile', 'exam' => 'Type examen'] as $value => $label)
                                            <option value="{{ $value }}" @selected(($td->difficulty ?? 'medium') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Accès</label>
                                    <select name="access_level" required>
                                        <option value="free" @selected($td->access_level === 'free')>Gratuit</option>
                                        <option value="premium" @selected($td->access_level === 'premium')>Premium</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="status" required>
                                        <option value="draft" @selected($td->status === 'draft')>Brouillon</option>
                                        <option value="published" @selected($td->status === 'published')>Publié</option>
                                        <option value="archived" @selected($td->status === 'archived')>Archivé</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Délai corrigé en minutes</label>
                                    <input type="number" name="correction_delay_minutes" min="0" max="1440" value="{{ $td->correction_delay_minutes ?? 30 }}">
                                </div>
                                <div class="form-group">
                                    <label>Remplacer le document TD</label>
                                    <input type="file" name="document" accept=".pdf,.doc,.docx,.txt,.odt,.rtf,.html,.htm">
                                    @if($td->document_name)<small>Actuel : {{ $td->document_name }}</small>@endif
                                </div>
                                <div class="form-group">
                                    <label>Remplacer le corrigé</label>
                                    <input type="file" name="correction_document" accept=".pdf,.doc,.docx,.txt,.odt,.rtf,.html,.htm">
                                    @if($td->correction_document_name)<small>Actuel : {{ $td->correction_document_name }}</small>@endif
                                </div>
                                <div class="form-group form-group--check">
                                    <label><input type="checkbox" name="remove_document" value="1"> Supprimer le document TD actuel</label>
                                </div>
                                <div class="form-group form-group--check">
                                    <label><input type="checkbox" name="remove_correction_document" value="1"> Supprimer le corrigé actuel</label>
                                </div>
                                <div class="form-group admin-form-grid__full">
                                    <label>Texte éditable du sujet</label>
                                    <textarea name="editable_html" rows="8" placeholder="Contenu du TD à éditer...">{{ $td->editable_html }}</textarea>
                                </div>
                                <div class="form-group admin-form-grid__full">
                                    <label>Corrigé texte</label>
                                    <textarea name="correction_html" rows="8" placeholder="Corrigé du TD...">{{ $td->correction_html }}</textarea>
                                </div>
                            </div>
                            <input type="hidden" name="editable_text" value="{{ $td->editable_text }}">
                            <div class="admin-actions">
                                <button type="submit" class="btn btn--primary">Enregistrer toutes les modifications</button>
                                <a href="{{ route('admin.td.edit', $td) }}?mode=editor#editor-zone" class="btn btn--ghost">Ouvrir l’éditeur complet</a>
                            </div>
                        </form>
                    </details>
                </article>
            @empty
                <div class="admin-empty-box">Aucun TD enregistré pour le moment.</div>
            @endforelse
        </div>
    </section>

    <div style="margin-top:8px;">{{ $sets->links() }}</div>
</div>
@endsection
