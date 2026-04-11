@extends('layouts.admin')

@section('title', 'TD')
@section('page_title', 'Supervision des TD')
@section('page_subtitle', 'Vue globale des TD créés par les enseignants et gestion complète des accès, statuts et publications.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head">
        <div>
            <h2>Liste des TD</h2>
            <p class="admin-muted">{{ $questionCount }} discussion(s) TD, dont {{ $openQuestionCount }} ouverte(s).</p>
        </div>
        <div class="admin-actions admin-actions--wrap">
            <a href="{{ route('admin.td.create') }}" class="btn btn--primary">+ Nouveau TD</a>
            <form method="GET" class="admin-actions admin-actions--wrap">
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
                <button class="btn btn--ghost">Filtrer</button>
            </form>
        </div>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Classe</th>
                    <th>Matière</th>
                    <th>Auteur</th>
                    <th>Accès</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($sets as $td)
                <tr>
                    <td>
                        <strong>{{ $td->title }}</strong>
                        <div class="admin-muted">{{ $td->chapter_label ?: 'Sans chapitre' }}</div>
                    </td>
                    <td>{{ $td->schoolClass->name ?? '-' }}</td>
                    <td>{{ $td->subject->name ?? '-' }}</td>
                    <td>{{ $td->author->full_name ?? $td->author->name ?? $td->author->username ?? '-' }}</td>
                    <td><span class="admin-badge">{{ $td->access_level }}</span></td>
                    <td>
                        <span class="admin-badge {{ $td->status === 'published' ? 'admin-badge--success' : ($td->status === 'archived' ? 'admin-badge--warning' : '') }}">
                            {{ $td->status === 'archived' ? 'inactive' : $td->status }}
                        </span>
                    </td>
                    <td>
                        <div class="admin-actions admin-actions--wrap">
                            <a href="{{ route('admin.td.edit', $td) }}?mode=editor#editor-zone" class="btn btn--primary">Modifier dans l’éditeur</a>
                            <a href="{{ route('admin.td.edit', $td) }}" class="btn btn--ghost">Modifier les infos</a>
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
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="admin-empty">Aucun TD enregistré pour le moment.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $sets->links() }}</div>
</section>
@endsection
