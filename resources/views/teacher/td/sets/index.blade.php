@extends('layouts.teacher')

@section('title', 'Mes TD')
@section('page_title', 'Mes TD')
@section('page_subtitle', 'Importez vos sujets, convertissez-les dans l’éditeur si nécessaire, puis publiez-les pour vos élèves.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Bibliothèque de TD</h2>
            <p class="teacher-muted">Version simple : titre, document source, version éditable, corrigé et publication.</p>
        </div>
        <div class="teacher-actions">
            <form method="GET" class="teacher-filter-inline">
                <input type="text" name="q" placeholder="Rechercher" value="{{ $filters['q'] ?? '' }}">
                <select name="status">
                    <option value="">Tous statuts</option>
                    <option value="draft" @selected(($filters['status'] ?? '')==='draft')>Brouillon</option>
                    <option value="published" @selected(($filters['status'] ?? '')==='published')>Publié</option>
                    <option value="archived" @selected(($filters['status'] ?? '')==='archived')>Archivé</option>
                </select>
                <button class="teacher-btn teacher-btn--ghost">Filtrer</button>
            </form>
            <a href="{{ route('teacher.td.sets.create') }}" class="teacher-btn teacher-btn--primary">+ Nouveau TD</a>
        </div>
    </div>

    <div class="teacher-table-wrap">
        <table class="teacher-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Classe</th>
                    <th>Matière</th>
                    <th>Document</th>
                    <th>Éditable</th>
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
                        <div class="teacher-muted">{{ $td->chapter_label ?: 'Sans chapitre' }}</div>
                    </td>
                    <td>{{ $td->schoolClass->name ?? '-' }}</td>
                    <td>{{ $td->subject->name ?? '-' }}</td>
                    <td>
                        @if($td->document_path)
                            <a href="{{ route('teacher.td.sets.document', $td) }}">{{ $td->document_name ?: 'Ouvrir' }}</a>
                            <div class="teacher-muted">{{ $td->humanDocumentSize() }}</div>
                        @else
                            <span class="teacher-muted">Aucun</span>
                        @endif
                    </td>
                    <td>{{ $td->has_editable_version ? 'Oui' : 'Non' }}</td>
                    <td><span class="teacher-badge teacher-badge--{{ $td->access_level }}">{{ $td->access_level }}</span></td>
                    <td><span class="teacher-badge teacher-badge--{{ $td->status }}">{{ $td->status }}</span></td>
                    <td>
                        <div class="teacher-actions teacher-actions--stack">
                            <a href="{{ route('teacher.td.sets.edit', $td) }}?mode=editor#editor-zone" class="teacher-btn teacher-btn--primary">Modifier dans l’éditeur</a>
                            <a href="{{ route('teacher.td.sets.edit', $td) }}" class="teacher-btn teacher-btn--ghost">Modifier les infos</a>
                            @if($td->status !== 'published')
                                <form method="POST" action="{{ route('teacher.td.sets.publish', $td) }}">@csrf<button class="teacher-btn teacher-btn--primary">Publier</button></form>
                            @endif
                            @if($td->status !== 'archived')
                                <form method="POST" action="{{ route('teacher.td.sets.archive', $td) }}">@csrf<button class="teacher-btn teacher-btn--ghost">Archiver</button></form>
                            @endif
                            <form method="POST" action="{{ route('teacher.td.sets.delete', $td) }}" onsubmit="return confirm('Supprimer ce TD ?')">@csrf<button class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="teacher-empty-row">Aucun TD pour le moment.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $sets->links() }}</div>
</section>
@endsection
