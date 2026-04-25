@extends('layouts.teacher')

@section('title', 'Mes TD')
@section('page_title', 'Mes TD')
@section('page_subtitle', 'Gérez vos sujets, corrigés, délais et publications dans une interface plus simple.')

@push('styles')
<style>
    .td-library {
        display: grid;
        gap: 18px;
    }

    .td-library-hero {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 28px;
        padding: 20px;
        background:
            radial-gradient(circle at top right, rgba(20, 184, 166, .16), transparent 28%),
            linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .86));
        box-shadow: 0 18px 46px rgba(15, 23, 42, .08);
        display: grid;
        gap: 16px;
    }

    html[data-theme='dark'] .td-library-hero {
        background:
            radial-gradient(circle at top right, rgba(20, 184, 166, .15), transparent 28%),
            linear-gradient(135deg, rgba(15, 23, 42, .88), rgba(30, 41, 59, .72));
    }

    .td-library-hero__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .td-library-hero h2 {
        margin: 0 0 6px;
        font-size: clamp(1.45rem, 2.5vw, 2.05rem);
        letter-spacing: -.05em;
        color: var(--teacher-text, #0f172a);
    }

    .td-library-hero p {
        margin: 0;
        max-width: 780px;
        color: var(--teacher-muted, #64748b);
        line-height: 1.55;
    }

    .td-quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: flex-end;
    }

    .td-manager-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .td-manager-stat {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 20px;
        padding: 14px;
        background: rgba(255, 255, 255, .68);
        display: grid;
        gap: 4px;
    }

    html[data-theme='dark'] .td-manager-stat {
        background: rgba(15, 23, 42, .52);
    }

    .td-manager-stat strong {
        display: block;
        font-size: 1.45rem;
        line-height: 1;
        color: var(--teacher-text, #0f172a);
        letter-spacing: -.05em;
    }

    .td-manager-stat span {
        color: var(--teacher-muted, #64748b);
        font-size: .8rem;
        font-weight: 850;
    }

    .td-manager-filter {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 190px auto;
        gap: 10px;
        align-items: center;
    }

    .td-manager-filter input,
    .td-manager-filter select {
        min-height: 46px;
        border-radius: 16px;
    }

    .td-list {
        display: grid;
        gap: 12px;
    }

    .td-card {
        position: relative;
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 24px;
        background: rgba(255, 255, 255, .84);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .06);
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .td-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 46px rgba(15, 23, 42, .10);
        border-color: rgba(20, 184, 166, .30);
    }

    html[data-theme='dark'] .td-card {
        background: rgba(15, 23, 42, .72);
    }

    .td-card__main {
        padding: 16px;
        display: grid;
        grid-template-columns: minmax(240px, 1.2fr) minmax(320px, 1.35fr) minmax(250px, .85fr);
        gap: 14px;
        align-items: start;
    }

    .td-card__title h3 {
        margin: 0 0 8px;
        font-size: 1.2rem;
        letter-spacing: -.035em;
        color: var(--teacher-text, #0f172a);
    }

    .td-meta-line {
        color: var(--teacher-muted, #64748b);
        font-weight: 750;
        line-height: 1.45;
        font-size: .92rem;
    }

    .td-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-top: 12px;
    }

    .td-zone {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        background: rgba(248, 250, 252, .70);
        padding: 14px;
        display: grid;
        gap: 10px;
    }

    html[data-theme='dark'] .td-zone {
        background: rgba(2, 6, 23, .28);
    }

    .td-zone__label {
        color: var(--teacher-muted, #64748b);
        font-size: .72rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .td-doc-line {
        color: var(--teacher-muted, #64748b);
        font-size: .9rem;
        line-height: 1.45;
        word-break: break-word;
    }

    .td-action-row,
    .td-danger-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .td-card .teacher-btn {
        min-height: 42px;
        padding-inline: 15px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .td-card .teacher-btn--primary {
        box-shadow: 0 12px 24px rgba(15, 118, 110, .18);
    }

    .td-delay-card {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        background: rgba(248, 250, 252, .70);
        padding: 14px;
        display: grid;
        gap: 10px;
    }

    html[data-theme='dark'] .td-delay-card {
        background: rgba(2, 6, 23, .28);
    }

    .td-delay-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        align-items: center;
    }

    .td-delay-form input {
        min-height: 42px;
        border-radius: 15px;
        padding-inline: 14px;
    }

    .td-delay-hint {
        margin: 0;
        color: var(--teacher-muted, #64748b);
        font-size: .86rem;
        line-height: 1.45;
    }

    .td-card__footer {
        border-top: 1px solid rgba(148, 163, 184, .14);
        padding: 12px 16px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: rgba(248, 250, 252, .46);
    }

    html[data-theme='dark'] .td-card__footer {
        background: rgba(2, 6, 23, .18);
    }

    .td-footer-note {
        color: var(--teacher-muted, #64748b);
        font-size: .88rem;
        line-height: 1.45;
    }

    .td-empty {
        border: 1px dashed rgba(148, 163, 184, .35);
        border-radius: 24px;
        padding: 30px;
        text-align: center;
        color: var(--teacher-muted, #64748b);
        background: rgba(255, 255, 255, .62);
    }

    @media (max-width: 1160px) {
        .td-card__main {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .td-library-hero,
        .td-card__main {
            padding: 14px;
        }

        .td-manager-stats,
        .td-manager-filter,
        .td-delay-form {
            grid-template-columns: 1fr;
        }

        .td-quick-actions,
        .td-action-row,
        .td-danger-row,
        .td-card__footer {
            display: grid;
            width: 100%;
        }

        .td-card .teacher-btn,
        .td-card form,
        .td-card button,
        .td-quick-actions .teacher-btn,
        .td-manager-filter .teacher-btn {
            width: 100%;
        }

        .td-card__footer {
            padding: 12px 14px 14px;
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
    $bulkCreateRouteExists = Route::has('teacher.td.sets.bulk_create') || Route::has('teacher.td.sets.bulk.create');
@endphp

<section class="teacher-section td-library">
    <div class="td-library-hero">
        <div class="td-library-hero__top">
            <div>
                <h2>Bibliothèque de TD</h2>
                <p>Une vue plus légère pour ouvrir les sujets, consulter les corrigés, régler le délai et modifier rapidement chaque TD.</p>
            </div>
            <div class="td-quick-actions">
                @if(Route::has('teacher.td.sets.bulk.create'))
                    <a href="{{ route('teacher.td.sets.bulk.create') }}" class="teacher-btn teacher-btn--ghost">Importer plusieurs TD</a>
                @elseif(Route::has('teacher.td.sets.bulk_create'))
                    <a href="{{ route('teacher.td.sets.bulk_create') }}" class="teacher-btn teacher-btn--ghost">Importer plusieurs TD</a>
                @endif
                <a href="{{ route('teacher.td.sets.create') }}" class="teacher-btn teacher-btn--primary">+ Nouveau TD</a>
            </div>
        </div>

        <div class="td-manager-stats">
            <div class="td-manager-stat"><strong>{{ $sets->total() }}</strong><span>TD au total</span></div>
            <div class="td-manager-stat"><strong>{{ $publishedCount }}</strong><span>publiés ici</span></div>
            <div class="td-manager-stat"><strong>{{ $draftCount }}</strong><span>brouillons ici</span></div>
            <div class="td-manager-stat"><strong>{{ $withCorrectionCount }}</strong><span>avec corrigé ici</span></div>
        </div>

        <form method="GET" class="td-manager-filter">
            <input type="text" name="q" placeholder="Rechercher un titre, chapitre ou fichier..." value="{{ $filters['q'] ?? '' }}">
            <select name="status">
                <option value="">Tous les statuts</option>
                <option value="draft" @selected(($filters['status'] ?? '')==='draft')>Brouillon</option>
                <option value="published" @selected(($filters['status'] ?? '')==='published')>Publié</option>
                <option value="archived" @selected(($filters['status'] ?? '')==='archived')>Archivé</option>
            </select>
            <button class="teacher-btn teacher-btn--ghost">Filtrer</button>
        </form>
    </div>

    <div class="td-list">
        @forelse($sets as $td)
            <article class="td-card">
                <div class="td-card__main">
                    <div class="td-card__title">
                        <h3>{{ $td->title }}</h3>
                        <div class="td-meta-line">
                            {{ $td->schoolClass->name ?? '-' }} · {{ $td->subject->name ?? '-' }} · {{ $td->chapter_label ?: 'Sans chapitre' }}
                        </div>
                        <div class="td-badge-row">
                            <span class="teacher-badge teacher-badge--{{ $td->access_level }}">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span>
                            <span class="teacher-badge teacher-badge--{{ $td->status }}">{{ $td->status === 'published' ? 'Publié' : ($td->status === 'draft' ? 'Brouillon' : 'Archivé') }}</span>
                        </div>
                    </div>

                    <div class="td-zone">
                        <span class="td-zone__label">Documents</span>
                        <div class="td-action-row">
                            @if($td->document_path)
                                <a href="{{ route('teacher.td.sets.document', $td) }}" class="teacher-btn teacher-btn--ghost">Ouvrir le sujet</a>
                            @else
                                <span class="teacher-muted">Aucun sujet joint</span>
                            @endif

                            @if($td->hasCorrectionContent())
                                @if($td->correction_document_path)
                                    <a href="{{ route('teacher.td.sets.correction_document', $td) }}" class="teacher-btn teacher-btn--ghost">Ouvrir le corrigé</a>
                                @else
                                    <a href="{{ route('teacher.td.sets.editor', $td) }}#correction-zone" class="teacher-btn teacher-btn--ghost">Voir le corrigé texte</a>
                                @endif
                            @else
                                <span class="teacher-muted">Aucun corrigé lié</span>
                            @endif
                        </div>
                        <div class="td-doc-line">
                            Sujet : {{ $td->document_name ?: 'non joint' }} @if($td->document_path) · {{ $td->humanDocumentSize() }} @endif
                        </div>
                    </div>

                    <div class="td-delay-card">
                        <span class="td-zone__label">Délai corrigé</span>
                        <form method="POST" action="{{ route('teacher.td.sets.correction_delay.update', $td) }}" class="td-delay-form">
                            @csrf
                            <input type="number" name="correction_delay_minutes" min="0" max="1440" value="{{ old('correction_delay_minutes', $td->correction_delay_minutes ?? 30) }}" aria-label="Minutes obligatoires avant corrigé">
                            <button class="teacher-btn teacher-btn--primary">OK</button>
                        </form>
                        <p class="td-delay-hint">Le corrigé se débloque après ce délai et après le clic élève sur « J’ai terminé ».</p>
                    </div>
                </div>

                <div class="td-card__footer">
                    <div class="td-action-row">
                        <a href="{{ route('teacher.td.sets.editor', $td) }}" class="teacher-btn teacher-btn--primary">Modifier dans l’éditeur</a>
                        <a href="{{ route('teacher.td.sets.edit', $td) }}" class="teacher-btn teacher-btn--ghost">Modifier les infos</a>
                    </div>

                    <div class="td-danger-row">
                        @if($td->status !== 'published')
                            <form method="POST" action="{{ route('teacher.td.sets.publish', $td) }}">@csrf<button class="teacher-btn teacher-btn--primary">Publier</button></form>
                        @endif
                        @if($td->status !== 'archived')
                            <form method="POST" action="{{ route('teacher.td.sets.archive', $td) }}">@csrf<button class="teacher-btn teacher-btn--ghost">Archiver</button></form>
                        @endif
                        <form method="POST" action="{{ route('teacher.td.sets.delete', $td) }}" onsubmit="return confirm('Supprimer ce TD ?')">@csrf<button class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                    </div>
                </div>
            </article>
        @empty
            <div class="td-empty">Aucun TD pour le moment. Cliquez sur « Nouveau TD » pour publier votre premier sujet.</div>
        @endforelse
    </div>

    <div style="margin-top:8px;">{{ $sets->links() }}</div>
</section>
@endsection
