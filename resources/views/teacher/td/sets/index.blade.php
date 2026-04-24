@extends('layouts.teacher')

@section('title', 'Mes TD')
@section('page_title', 'Mes TD')
@section('page_subtitle', 'Consultez vos sujets, ouvrez les corrigés, fixez le temps de traitement et modifiez vos TD depuis l’éditeur intégré.')

@push('styles')
<style>
    .td-manager-head {
        display: grid;
        gap: 16px;
    }
    .td-manager-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .td-manager-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
    }
    .td-manager-stat {
        border: 1px solid var(--teacher-border, rgba(148,163,184,.28));
        border-radius: 18px;
        padding: 14px;
        background: rgba(255,255,255,.72);
    }
    .td-manager-stat strong {
        display: block;
        font-size: 1.45rem;
        color: var(--teacher-text, #0f172a);
        letter-spacing: -.04em;
    }
    .td-manager-stat span {
        color: var(--teacher-muted, #64748b);
        font-size: .82rem;
        font-weight: 800;
    }
    .td-manager-filter {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 190px auto;
        gap: 10px;
        align-items: center;
        margin-top: 16px;
    }
    .td-teacher-list {
        display: grid;
        gap: 16px;
        margin-top: 20px;
    }
    .td-teacher-card {
        border: 1px solid var(--teacher-border, rgba(148,163,184,.28));
        border-radius: 24px;
        background: rgba(255,255,255,.82);
        box-shadow: 0 18px 38px rgba(15,23,42,.08);
        padding: 18px;
        display: grid;
        gap: 16px;
    }
    html[data-theme='dark'] .td-teacher-card,
    html[data-theme='dark'] .td-manager-stat {
        background: rgba(15,23,42,.72);
    }
    .td-teacher-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .td-teacher-card__title h3 {
        margin: 0 0 6px;
        font-size: 1.15rem;
        letter-spacing: -.03em;
    }
    .td-meta-line {
        color: var(--teacher-muted, #64748b);
        font-weight: 750;
        line-height: 1.45;
    }
    .td-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .td-teacher-grid {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 14px;
    }
    .td-info-box {
        border: 1px solid var(--teacher-border, rgba(148,163,184,.28));
        border-radius: 18px;
        padding: 14px;
        background: rgba(248,250,252,.74);
        display: grid;
        gap: 10px;
    }
    html[data-theme='dark'] .td-info-box {
        background: rgba(2,6,23,.32);
    }
    .td-info-box__label {
        color: var(--teacher-muted, #64748b);
        font-size: .75rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .td-doc-actions,
    .td-main-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .td-delay-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: end;
    }
    .td-delay-form input {
        min-height: 44px;
        border-radius: 14px;
    }
    .td-main-actions .teacher-btn,
    .td-doc-actions .teacher-btn {
        min-width: 150px;
    }
    .td-action-danger-zone {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }
    @media (max-width: 760px) {
        .td-manager-stats,
        .td-teacher-grid,
        .td-manager-filter {
            grid-template-columns: 1fr;
        }
        .td-teacher-card {
            padding: 14px;
            border-radius: 20px;
        }
        .td-doc-actions,
        .td-main-actions,
        .td-action-danger-zone,
        .td-delay-form {
            grid-template-columns: 1fr;
            display: grid;
            width: 100%;
        }
        .td-main-actions .teacher-btn,
        .td-doc-actions .teacher-btn,
        .td-action-danger-zone .teacher-btn,
        .td-action-danger-zone form,
        .td-action-danger-zone button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $collection = collect($sets->items());
    $publishedCount = $collection->where('status', 'published')->count();
    $draftCount = $collection->where('status', 'draft')->count();
    $withCorrectionCount = $collection->filter(fn($td) => $td->hasCorrectionContent())->count();
@endphp

<section class="teacher-section">
    <div class="td-manager-head">
        <div class="td-manager-title-row">
            <div>
                <h2>Bibliothèque de TD</h2>
                <p class="teacher-muted">Chaque carte vous permet de consulter le sujet, consulter le corrigé, régler le temps minimum et modifier le contenu dans l’éditeur.</p>
            </div>
            <a href="{{ route('teacher.td.sets.create') }}" class="teacher-btn teacher-btn--primary">+ Nouveau TD</a>
        </div>

        <div class="td-manager-stats">
            <div class="td-manager-stat"><strong>{{ $sets->total() }}</strong><span>TD au total</span></div>
            <div class="td-manager-stat"><strong>{{ $publishedCount }}</strong><span>publiés sur cette page</span></div>
            <div class="td-manager-stat"><strong>{{ $draftCount }}</strong><span>brouillons sur cette page</span></div>
            <div class="td-manager-stat"><strong>{{ $withCorrectionCount }}</strong><span>avec corrigé sur cette page</span></div>
        </div>

        <form method="GET" class="td-manager-filter">
            <input type="text" name="q" placeholder="Rechercher un TD, un chapitre ou un fichier..." value="{{ $filters['q'] ?? '' }}">
            <select name="status">
                <option value="">Tous les statuts</option>
                <option value="draft" @selected(($filters['status'] ?? '')==='draft')>Brouillon</option>
                <option value="published" @selected(($filters['status'] ?? '')==='published')>Publié</option>
                <option value="archived" @selected(($filters['status'] ?? '')==='archived')>Archivé</option>
            </select>
            <button class="teacher-btn teacher-btn--ghost">Filtrer</button>
        </form>
    </div>

    <div class="td-teacher-list">
        @forelse($sets as $td)
            <article class="td-teacher-card">
                <div class="td-teacher-card__top">
                    <div class="td-teacher-card__title">
                        <h3>{{ $td->title }}</h3>
                        <div class="td-meta-line">
                            {{ $td->schoolClass->name ?? '-' }} · {{ $td->subject->name ?? '-' }} · {{ $td->chapter_label ?: 'Sans chapitre' }}
                        </div>
                    </div>
                    <div class="td-badge-row">
                        <span class="teacher-badge teacher-badge--{{ $td->access_level }}">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span>
                        <span class="teacher-badge teacher-badge--{{ $td->status }}">{{ $td->status === 'published' ? 'Publié' : ($td->status === 'draft' ? 'Brouillon' : 'Archivé') }}</span>
                    </div>
                </div>

                <div class="td-teacher-grid">
                    <div class="td-info-box">
                        <span class="td-info-box__label">Consultation enseignant</span>
                        <div class="td-doc-actions">
                            @if($td->document_path)
                                <a href="{{ route('teacher.td.sets.document', $td) }}" class="teacher-btn teacher-btn--ghost">Ouvrir le sujet</a>
                            @else
                                <span class="teacher-muted">Aucun document sujet.</span>
                            @endif

                            @if($td->hasCorrectionContent())
                                @if($td->correction_document_path)
                                    <a href="{{ route('teacher.td.sets.correction_document', $td) }}" class="teacher-btn teacher-btn--ghost">Ouvrir le corrigé</a>
                                @else
                                    <a href="{{ route('teacher.td.sets.edit', $td) }}#correction-zone" class="teacher-btn teacher-btn--ghost">Voir le corrigé texte</a>
                                @endif
                            @else
                                <span class="teacher-muted">Aucun corrigé lié.</span>
                            @endif
                        </div>
                        <div class="teacher-muted">
                            Sujet : {{ $td->document_name ?: 'non joint' }} @if($td->document_path) · {{ $td->humanDocumentSize() }} @endif
                        </div>
                    </div>

                    <div class="td-info-box">
                        <span class="td-info-box__label">Temps avant corrigé</span>
                        <form method="POST" action="{{ route('teacher.td.sets.correction_delay.update', $td) }}" class="td-delay-form">
                            @csrf
                            <div>
                                <label class="teacher-muted" style="display:block;font-size:.78rem;font-weight:900;margin-bottom:5px;">Minutes obligatoires</label>
                                <input type="number" name="correction_delay_minutes" min="0" max="1440" value="{{ old('correction_delay_minutes', $td->correction_delay_minutes ?? 30) }}">
                            </div>
                            <button class="teacher-btn teacher-btn--primary">Enregistrer</button>
                        </form>
                        <div class="teacher-muted">Le corrigé reste bloqué jusqu’à la fin de ce délai et après le clic élève sur « J’ai terminé ce TD ».</div>
                    </div>
                </div>

                <div class="td-info-box">
                    <span class="td-info-box__label">Modification du TD</span>
                    <div class="td-main-actions">
                        <a href="{{ route('teacher.td.sets.edit', $td) }}?mode=editor#editor-zone" class="teacher-btn teacher-btn--primary">Modifier dans l’éditeur</a>
                        <a href="{{ route('teacher.td.sets.edit', $td) }}" class="teacher-btn teacher-btn--ghost">Modifier les infos</a>
                    </div>
                    <div class="teacher-muted">L’éditeur intégré permet de corriger le contenu, reformuler le sujet, ajouter le corrigé texte et remplacer les documents joints.</div>
                </div>

                <div class="td-action-danger-zone">
                    @if($td->status !== 'published')
                        <form method="POST" action="{{ route('teacher.td.sets.publish', $td) }}">@csrf<button class="teacher-btn teacher-btn--primary">Publier</button></form>
                    @endif
                    @if($td->status !== 'archived')
                        <form method="POST" action="{{ route('teacher.td.sets.archive', $td) }}">@csrf<button class="teacher-btn teacher-btn--ghost">Archiver</button></form>
                    @endif
                    <form method="POST" action="{{ route('teacher.td.sets.delete', $td) }}" onsubmit="return confirm('Supprimer ce TD ?')">@csrf<button class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                </div>
            </article>
        @empty
            <div class="teacher-empty-row">Aucun TD pour le moment.</div>
        @endforelse
    </div>

    <div style="margin-top:16px;">{{ $sets->links() }}</div>
</section>
@endsection
