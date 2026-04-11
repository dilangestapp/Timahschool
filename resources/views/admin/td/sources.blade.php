@extends('layouts.admin')

@section('title', 'Sources TD')
@section('page_title', 'Sources TD')
@section('page_subtitle', 'Supervision des sources brutes importées par les enseignants avant transformation en nouveaux TD.')

@section('content')
<section class="admin-panel">
    <div class="admin-panel__head"><h2>Filtres</h2></div>
    <div class="admin-panel__body">
        <form method="GET" class="admin-filters-grid">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Titre, référence, analyse...">
            <select name="status">
                <option value="">Tous les statuts</option>
                @foreach(['imported' => 'Importée', 'analyzed' => 'Analysée', 'transformed' => 'Transformée', 'review' => 'En revue', 'archived' => 'Archivée'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="source_kind">
                <option value="">Tous les types</option>
                @foreach(['url','text','prompt','pdf','image','document','legacy_td'] as $kind)
                    <option value="{{ $kind }}" @selected(($filters['source_kind'] ?? '') === $kind)>{{ $kind }}</option>
                @endforeach
            </select>
            <select name="subject_id">
                <option value="">Toutes les matières</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string)($filters['subject_id'] ?? '') === (string)$subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn--primary">Filtrer</button>
        </form>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__head"><h2>Banque des sources brutes</h2></div>
    <div class="admin-panel__body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Enseignant</th>
                    <th>Détection</th>
                    <th>Statut</th>
                    <th>Transformations</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sources as $source)
                    <tr>
                        <td>
                            <strong>{{ $source->title ?: 'Source sans titre' }}</strong>
                            <div class="admin-muted">{{ $source->source_kind }} • {{ $source->source_label ?: ($source->source_url ?: 'Source interne') }}</div>
                        </td>
                        <td>{{ $source->uploader->full_name ?? $source->uploader->name ?? '-' }}</td>
                        <td>{{ $source->detectedSubject->name ?? 'Matière ?' }} / {{ $source->detectedSchoolClass->name ?? 'Classe ?' }}</td>
                        <td><span class="admin-badge admin-badge--{{ $source->status }}">{{ $source->status }}</span></td>
                        <td>{{ $source->transformations->count() }}</td>
                        <td><a href="{{ route('admin.td.sources.show', $source) }}" class="btn btn--ghost">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucune source importée pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-panel__body">{{ $sources->links() }}</div>
</section>
@endsection
