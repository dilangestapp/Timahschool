@extends('layouts.teacher')

@section('title', 'Mes cours')
@section('page_title', 'Mes cours')
@section('page_subtitle', 'Rédigez directement, importez un fichier et gérez vos publications.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/course-writer.css') }}">
@endpush

@section('content')
@include('teacher.courses.partials.quick_create')

<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Bibliothèque de cours</h2>
            <p class="teacher-muted">Cours déjà créés, fichiers importés et publications.</p>
        </div>
        <a href="{{ route('teacher.courses.create') }}" class="teacher-btn teacher-btn--primary">Page complète</a>
    </div>

    <form method="GET" class="teacher-toolbar">
        <div class="teacher-toolbar__group">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="teacher-input" placeholder="Rechercher un titre, un texte ou un document...">
            <select name="status" class="teacher-select">
                <option value="">Tous les statuts</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Brouillon</option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>Publié</option>
                <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Archivé</option>
            </select>
        </div>
        <div class="teacher-toolbar__group teacher-toolbar__group--right">
            <button type="submit" class="teacher-btn teacher-btn--primary">Filtrer</button>
            <a href="{{ route('teacher.courses.index') }}" class="teacher-btn teacher-btn--ghost">Réinitialiser</a>
        </div>
    </form>

    @if($assignments->isEmpty())
        <div class="teacher-empty-state" style="margin-top:18px;"><strong>Aucune affectation active.</strong><p>Demandez à l'administrateur de vous affecter une classe et une matière.</p></div>
    @endif

    <div class="teacher-course-grid" style="margin-top:18px;">
        @forelse($courses as $course)
            @php
                $extension = strtolower(pathinfo($course->document_name ?: $course->document_path ?: '', PATHINFO_EXTENSION));
                $canEditFile = in_array($extension, ['docx','doc','odt','rtf','txt'], true);
            @endphp
            <article class="teacher-course-card">
                <div class="teacher-course-card__top"><div><div class="teacher-course-card__badges"><span class="teacher-status teacher-status--{{ $course->status }}">{{ $course->status === 'published' ? 'Publié' : ($course->status === 'archived' ? 'Archivé' : 'Brouillon') }}</span>@if($course->hasRichContent())<span class="teacher-pill">Contenu rédigé</span>@endif @if($course->hasDocument())<span class="teacher-pill teacher-pill--alt">Fichier joint</span>@endif @if($canEditFile)<span class="teacher-pill">Éditable</span>@endif</div><h3>{{ $course->title }}</h3><p class="teacher-muted">{{ $course->schoolClass->name ?? '-' }} — {{ $course->subject->name ?? '-' }}</p></div></div>
                @if($course->excerpt())<p class="teacher-course-card__excerpt">{{ $course->excerpt(180) }}</p>@endif
                @if($course->hasDocument())<div class="teacher-doc-chip"><strong>{{ $course->document_name }}</strong><span>{{ strtoupper($extension) ?: 'DOC' }} • {{ $course->humanDocumentSize() }}</span></div>@endif
                <div class="teacher-course-card__meta"><span>Créé le {{ optional($course->created_at)->format('d/m/Y H:i') }}</span>@if($course->published_at)<span>Publié le {{ $course->published_at->format('d/m/Y') }}</span>@endif</div>
                <div class="teacher-course-card__actions">
                    <a href="{{ route('teacher.courses.edit', $course) }}" class="teacher-btn teacher-btn--primary">Modifier / rédiger</a>
                    @if($canEditFile)<a href="{{ route('teacher.courses.office', $course) }}" class="teacher-btn teacher-btn--ghost">Éditer le fichier</a>@endif
                    @if($course->hasDocument())<a href="{{ route('teacher.courses.document', $course) }}" target="_blank" class="teacher-btn teacher-btn--ghost">Ouvrir fichier</a>@endif
                    @if($course->status !== 'published')<form method="POST" action="{{ route('teacher.courses.publish', $course) }}">@csrf<button type="submit" class="teacher-btn teacher-btn--primary">Publier</button></form>@endif
                    @if($course->status !== 'archived')<form method="POST" action="{{ route('teacher.courses.archive', $course) }}">@csrf<button type="submit" class="teacher-btn teacher-btn--ghost">Archiver</button></form>@endif
                    <form method="POST" action="{{ route('teacher.courses.delete', $course) }}">@csrf<button type="submit" class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                </div>
            </article>
        @empty
            <div class="teacher-empty-state"><strong>Aucun cours trouvé.</strong><p>Utilisez l’éditeur ci-dessus pour rédiger le premier cours.</p></div>
        @endforelse
    </div>

    @if(method_exists($courses, 'links'))<div style="margin-top:20px;">{{ $courses->links() }}</div>@endif
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/course-writer.js') }}"></script>
@endpush
