@extends('layouts.student')

@section('title', 'Mes cours')

@section('content')
<section class="panel student-course-panel">
    <div class="panel__head student-course-panel__head">
        <div>
            <h2>Mes cours</h2>
            <span class="muted">
                @if($studentProfile && $studentProfile->schoolClass)
                    Classe : {{ $studentProfile->schoolClass->name }}
                @else
                    Classe non configurée
                @endif
            </span>
        </div>
    </div>

    <div class="panel__body" style="padding:20px 24px;">
        <form method="GET" class="student-course-filters">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="teacher-input" placeholder="Rechercher un cours...">
            <select name="subject_id" class="teacher-select">
                <option value="">Toutes les matières</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string)($filters['subject_id'] ?? '') === (string)$subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <button class="btn btn--primary" type="submit">Filtrer</button>
            <a href="{{ route('student.courses.index') }}" class="btn btn--ghost">Réinitialiser</a>
        </form>

        <div class="student-course-grid">
            @forelse($courses as $course)
                <article class="student-course-card">
                    <div class="student-course-card__head">
                        <div class="subject-mark" style="background-color: {{ $course->subject->color ?? '#4F46E5' }};">
                            {{ $course->subject->initials ?? 'C' }}
                        </div>
                        <div>
                            <h3>{{ $course->title }}</h3>
                            <div class="muted">{{ $course->subject->name ?? 'Matière' }}</div>
                        </div>
                    </div>

                    @if($course->excerpt())
                        <p class="student-course-card__excerpt">{{ $course->excerpt(170) }}</p>
                    @endif

                    <div class="student-course-card__badges">
                        @if($course->hasRichContent())
                            <span class="student-course-badge">Lecture en ligne</span>
                        @endif
                        @if($course->hasDocument())
                            <span class="student-course-badge student-course-badge--alt">Document joint</span>
                        @endif
                    </div>

                    <div class="student-course-card__actions">
                        <a href="{{ route('student.courses.show', $course) }}" class="btn btn--primary">Ouvrir</a>
                        @if($course->hasDocument())
                            <a href="{{ route('student.courses.document', $course) }}" target="_blank" class="btn btn--ghost">Document</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state" style="grid-column:1 / -1;">Aucun cours publié n'est disponible pour le moment.</div>
            @endforelse
        </div>

        @if(method_exists($courses, 'links'))
            <div style="margin-top:20px;">{{ $courses->links() }}</div>
        @endif
    </div>
</section>
@endsection
