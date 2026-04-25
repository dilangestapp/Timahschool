@extends('layouts.admin')

@section('title', 'TD')
@section('page_title', 'Supervision des TD')
@section('page_subtitle', 'Consultez, filtrez et gérez les TD avec une liste claire et des actions rapides.')

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
                <p>{{ $questionCount }} discussion(s) TD, dont {{ $openQuestionCount }} ouverte(s).</p>
            </div>
            <a href="{{ route('admin.td.create') }}" class="btn btn--primary">+ Nouveau TD</a>
        </div>

        <div class="admin-clean-list">
            @forelse($sets as $td)
                <article class="admin-clean-row">
                    <div class="admin-clean-title">
                        <strong>{{ $td->title }}</strong>
                        <span>{{ $td->chapter_label ?: 'Sans chapitre' }} · Auteur : {{ $td->author->full_name ?? $td->author->name ?? $td->author->username ?? '-' }}</span>
                    </div>

                    <div class="admin-clean-meta">
                        <strong>{{ $td->schoolClass->name ?? '-' }} · {{ $td->subject->name ?? '-' }}</strong><br>
                        <span class="admin-badge">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span>
                        <span class="admin-badge {{ $td->status === 'published' ? 'admin-badge--success' : ($td->status === 'archived' ? 'admin-badge--warning' : '') }}">
                            {{ $td->status === 'archived' ? 'désactivé' : $td->status }}
                        </span>
                    </div>

                    <div class="admin-row-actions">
                        <a href="{{ route('admin.td.edit', $td) }}?mode=editor#editor-zone" class="btn btn--primary">Éditeur</a>
                        <a href="{{ route('admin.td.edit', $td) }}" class="btn btn--ghost">Infos</a>
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
                </article>
            @empty
                <div class="admin-empty-box">Aucun TD enregistré pour le moment.</div>
            @endforelse
        </div>
    </section>

    <div style="margin-top:8px;">{{ $sets->links() }}</div>
</div>
@endsection
