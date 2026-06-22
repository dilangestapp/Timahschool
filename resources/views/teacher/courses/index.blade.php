@extends('layouts.teacher')

@section('title', 'Mes cours')
@section('page_title', 'Mes cours')
@section('page_subtitle', 'Votre bibliothèque personnelle : gardez vos cours en brouillon, traitez-les, puis publiez-les quand ils sont prêts.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/course-writer.css') }}">
@endpush

@section('content')
@php
    $statusCounters = $statusCounters ?? ['draft' => 0, 'published' => 0, 'archived' => 0, 'total' => 0];
@endphp

<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Mon espace de cours</h2>
            <p class="teacher-muted">Les brouillons restent privés dans votre espace enseignant. Seuls les cours publiés sont visibles par les élèves et l’application mobile.</p>
        </div>
        <a href="{{ route('teacher.courses.create') }}" class="teacher-btn teacher-btn--primary">+ Nouveau cours</a>
    </div>

    <div class="resp-stats-mini" style="margin-bottom:16px;">
        <a href="{{ route('teacher.courses.index') }}" class="resp-stat-mini" style="text-decoration:none;"><div class="resp-mini-icon" style="background:#e8eaf6;color:#3949ab;">▭</div><div><div class="resp-mini-value">{{ $statusCounters['total'] }}</div><div class="resp-mini-label">tous mes cours</div></div></a>
        <a href="{{ route('teacher.courses.index', ['status' => 'draft']) }}" class="resp-stat-mini" style="text-decoration:none;"><div class="resp-mini-icon" style="background:#fff3e0;color:#e65100;">✎</div><div><div class="resp-mini-value">{{ $statusCounters['draft'] }}</div><div class="resp-mini-label">brouillons privés</div></div></a>
        <a href="{{ route('teacher.courses.index', ['status' => 'published']) }}" class="resp-stat-mini" style="text-decoration:none;"><div class="resp-mini-icon" style="background:#e0f2f1;color:#00695c;">✓</div><div><div class="resp-mini-value">{{ $statusCounters['published'] }}</div><div class="resp-mini-label">publiés aux élèves</div></div></a>
        <a href="{{ route('teacher.courses.index', ['status' => 'archived']) }}" class="resp-stat-mini" style="text-decoration:none;"><div class="resp-mini-icon" style="background:#f5f7ff;color:#60758d;">▤</div><div><div class="resp-mini-value">{{ $statusCounters['archived'] }}</div><div class="resp-mini-label">archivés</div></div></a>
    </div>

    @include('teacher.courses.partials.quick_create')

    <form method="GET" class="teacher-toolbar" style="margin-top:18px;">
        <div class="teacher-toolbar__group">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="teacher-input" placeholder="Rechercher un titre, un texte ou un document...">
            <select name="status" class="teacher-select">
                <option value="">Tous les statuts</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Brouillon privé</option>
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
                $statusLabel = $course->status === 'published' ? 'Publié aux élèves' : ($course->status === 'archived' ? 'Archivé' : 'Brouillon privé');
            @endphp
            <article class="teacher-course-card">
                <div class="teacher-course-card__top">
                    <div>
                        <div class="teacher-course-card__badges">
                            <span class="teacher-status teacher-status--{{ $course->status }}">{{ $statusLabel }}</span>
                            @if($course->hasRichContent())<span class="teacher-pill">Contenu rédigé</span>@endif
                            @if($course->hasDocument())<span class="teacher-pill teacher-pill--alt">Fichier joint</span>@endif
                            @if($canEditFile)<span class="teacher-pill">Éditable</span>@endif
                        </div>
                        <h3>{{ $course->title }}</h3>
                        <p class="teacher-muted">{{ $course->schoolClass->name ?? '-' }} — {{ $course->subject->name ?? '-' }}</p>
                    </div>
                </div>
                @if($course->status === 'draft')
                    <p class="teacher-muted" style="background:#fff8e1;border:1px solid #ffe0a3;border-radius:12px;padding:10px 12px;margin-top:10px;">Ce cours est gardé dans votre espace. Les élèves ne le voient pas encore.</p>
                @endif
                @if($course->excerpt())<p class="teacher-course-card__excerpt">{{ $course->excerpt(180) }}</p>@endif
                @if($course->hasDocument())<div class="teacher-doc-chip"><strong>{{ $course->document_name }}</strong><span>{{ strtoupper($extension) ?: 'DOC' }} • {{ $course->humanDocumentSize() }}</span></div>@endif
                <div class="teacher-course-card__meta"><span>Créé le {{ optional($course->created_at)->format('d/m/Y H:i') }}</span>@if($course->published_at)<span>Publié le {{ $course->published_at->format('d/m/Y') }}</span>@endif</div>
                <div class="teacher-course-card__actions">
                    <a href="{{ route('teacher.courses.edit', $course) }}" class="teacher-btn teacher-btn--primary">Traiter / modifier</a>
                    @if($canEditFile)<a href="{{ route('teacher.courses.office', $course) }}" class="teacher-btn teacher-btn--ghost">Éditer le fichier</a>@endif
                    @if($course->hasDocument())<a href="{{ route('teacher.courses.document', $course) }}" target="_blank" class="teacher-btn teacher-btn--ghost">Ouvrir fichier</a>@endif
                    @if($course->status !== 'published')<form method="POST" action="{{ route('teacher.courses.publish', $course) }}">@csrf<button type="submit" class="teacher-btn teacher-btn--primary">Publier</button></form>@endif
                    @if($course->status !== 'archived')<form method="POST" action="{{ route('teacher.courses.archive', $course) }}">@csrf<button type="submit" class="teacher-btn teacher-btn--ghost">Archiver</button></form>@endif
                    <form method="POST" action="{{ route('teacher.courses.delete', $course) }}" onsubmit="return confirm('Supprimer définitivement ce cours de votre espace ?');">@csrf<button type="submit" class="teacher-btn teacher-btn--danger">Supprimer</button></form>
                </div>
            </article>
        @empty
            <div class="teacher-empty-state"><strong>Aucun cours trouvé.</strong><p>Ajoutez un cours dans votre espace. Il restera en brouillon privé jusqu’à publication.</p></div>
        @endforelse
    </div>

    @if(method_exists($courses, 'links'))<div style="margin-top:20px;">{{ $courses->links() }}</div>@endif
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/course-writer.js') }}"></script>
@endpush
