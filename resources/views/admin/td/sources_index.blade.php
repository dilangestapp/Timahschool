@extends('layouts.admin')

@section('title', 'Préparer pour ChatGPT')
@section('page_title', 'Préparer pour ChatGPT')
@section('page_subtitle', 'Source brute → texte nettoyé → visuels conservés → prompt prêt à copier manuellement dans ChatGPT.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head"><h2>Filtres</h2><a href="{{ route('admin.td.sources.create') }}" class="btn btn--primary">+ Nouvelle préparation</a></div>
    <form method="GET" class="admin-filters-grid">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Titre, référence, analyse...">
        <select name="status"><option value="">Tous les statuts</option>@foreach(['imported'=>'Importée','prepared'=>'Prête','transformed'=>'Réinjectée','archived'=>'Archivée'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
        <select name="source_kind"><option value="">Tous les types</option>@foreach(['url','text','prompt','pdf','image','document','legacy_td'] as $kind)<option value="{{ $kind }}" @selected(($filters['source_kind'] ?? '') === $kind)>{{ $kind }}</option>@endforeach</select>
        <select name="subject_id"><option value="">Toutes les matières</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string)($filters['subject_id'] ?? '') === (string)$subject->id)>{{ $subject->name }}</option>@endforeach</select>
        <button type="submit" class="btn btn--ghost">Filtrer</button>
    </form>
</section>

<section class="admin-section">
    <div class="admin-section__head"><h2>Sources réservées à l’administrateur</h2></div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Source</th><th>Classe / matière</th><th>Visuels</th><th>Statut</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($sources as $source)
                <tr>
                    <td><strong>{{ $source->title ?: 'Source sans titre' }}</strong><div class="admin-muted">{{ $source->source_kind }} • {{ $source->source_label ?: ($source->source_url ?: 'interne') }}</div></td>
                    <td>{{ $source->detectedSchoolClass->name ?? 'Classe ?' }} / {{ $source->detectedSubject->name ?? 'Matière ?' }}</td>
                    <td>{{ $source->visuals->count() }}</td>
                    <td><span class="admin-badge admin-badge--{{ $source->status }}">{{ $source->status }}</span></td>
                    <td><a href="{{ route('admin.td.sources.show', $source) }}" class="btn btn--ghost">Ouvrir</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Aucune source enregistrée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">{{ $sources->links() }}</div>
</section>
@endsection
