@extends('layouts.teacher')

@section('title', 'Sources TD')
@section('page_title', 'Sources TD')
@section('page_subtitle', 'Importez des sujets web, images, PDF, documents ou prompts, puis lancez une analyse pédagogique avant de générer un nouveau TD.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head"><h2>Filtres</h2></div>
    <form method="GET" class="teacher-filter-grid">
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
                <option value="{{ $kind }}" @selected(($filters['source_kind'] ?? '') === $kind)>{{ ucfirst($kind) }}</option>
            @endforeach
        </select>
        <button type="submit" class="teacher-btn teacher-btn--ghost">Filtrer</button>
        <a href="{{ route('teacher.td.sources.create') }}" class="teacher-btn teacher-btn--primary">+ Importer une source</a>
    </form>
</section>

<section class="teacher-panel">
    <div class="teacher-panel__head"><h2>Bibliothèque des sources</h2></div>
    <div class="teacher-table-wrap">
        <table class="teacher-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Affectation</th>
                    <th>Détection</th>
                    <th>Statut</th>
                    <th>Générations</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($sources as $source)
                <tr>
                    <td>
                        <strong>{{ $source->title ?: 'Source sans titre' }}</strong>
                        <div class="teacher-muted">{{ $source->source_kind }} • {{ $source->source_label ?: ($source->source_url ?: 'Source interne') }}</div>
                    </td>
                    <td>{{ $source->teacherAssignment->schoolClass->name ?? '-' }} — {{ $source->teacherAssignment->subject->name ?? '-' }}</td>
                    <td>{{ $source->detectedSubject->name ?? 'Matière ?' }} / {{ $source->detectedSchoolClass->name ?? 'Classe ?' }}</td>
                    <td><span class="teacher-badge teacher-badge--{{ $source->status }}">{{ $source->status }}</span></td>
                    <td>{{ $source->transformations->count() }}</td>
                    <td><a href="{{ route('teacher.td.sources.show', $source) }}" class="teacher-btn teacher-btn--ghost">Ouvrir</a></td>
                </tr>
            @empty
                <tr><td colspan="6">Aucune source TD importée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="teacher-panel__pagination">{{ $sources->links() }}</div>
</section>
@endsection
