@extends('layouts.admin')

@section('title', 'Source TD')
@section('page_title', 'Détail de la source TD')
@section('page_subtitle', 'Lecture de la source brute, analyse pédagogique détectée et historique des transformations générées.')

@section('content')
<section class="admin-panel">
    <div class="admin-panel__head"><h2>{{ $source->title ?: 'Source sans titre' }}</h2></div>
    <div class="admin-panel__body">
        <div class="admin-detail-grid">
            <div><strong>Type</strong><span>{{ $source->source_kind }}</span></div>
            <div><strong>Statut</strong><span class="admin-badge admin-badge--{{ $source->status }}">{{ $source->status }}</span></div>
            <div><strong>Classe détectée</strong><span>{{ $source->detectedSchoolClass->name ?? 'Non détectée' }}</span></div>
            <div><strong>Matière détectée</strong><span>{{ $source->detectedSubject->name ?? 'Non détectée' }}</span></div>
            <div><strong>Chapitre</strong><span>{{ $source->detected_chapter_label ?: 'Non détecté' }}</span></div>
            <div><strong>Difficulté</strong><span>{{ $source->detected_difficulty ?: '-' }}</span></div>
        </div>

        @if($source->source_url)
            <div class="admin-doc-card"><strong>URL source :</strong> {{ $source->source_url }}</div>
        @endif
        @if($source->source_file_path)
            <div class="admin-doc-card"><strong>Document source :</strong> {{ $source->source_file_name }} ({{ $source->humanFileSize() }})</div>
        @endif

        @if($source->analysis_notes)
            <div class="admin-rich-block">
                <h3>Analyse détectée</h3>
                <pre class="admin-pre">{{ $source->analysis_notes }}</pre>
            </div>
        @endif

        @if($source->working_text)
            <div class="admin-rich-block">
                <h3>Contenu source exploitable</h3>
                <pre class="admin-pre">{{ $source->working_text }}</pre>
            </div>
        @endif
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__head"><h2>Transformations générées</h2></div>
    <div class="admin-panel__body admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Variante</th><th>Titre</th><th>Statut</th><th>Brouillon TD</th></tr></thead>
            <tbody>
                @forelse($source->transformations as $transformation)
                    <tr>
                        <td>{{ $transformation->variant_type }}</td>
                        <td>{{ $transformation->transformed_title }}</td>
                        <td><span class="admin-badge admin-badge--{{ $transformation->status }}">{{ $transformation->status }}</span></td>
                        <td>
                            @if($transformation->tdSet)
                                <a href="{{ route('admin.td.sets.show', $transformation->tdSet) }}">{{ $transformation->tdSet->title }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aucune transformation générée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
