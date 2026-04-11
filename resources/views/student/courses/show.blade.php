@extends('layouts.student')

@section('title', $course->title)

@section('content')
<section class="panel student-course-reader">
    <div class="panel__head student-course-reader__head">
        <div>
            <div class="student-course-reader__meta muted">{{ $course->schoolClass->name ?? '-' }} • {{ $course->subject->name ?? '-' }}</div>
            <h2>{{ $course->title }}</h2>
            <div class="student-course-reader__submeta muted">
                @if($course->creator)
                    <span>Par {{ $course->creator->full_name ?? $course->creator->name ?? $course->creator->username }}</span>
                @endif
                @if($course->published_at)
                    <span>Publié le {{ $course->published_at->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
        <div class="student-course-reader__actions">
            <a href="{{ route('student.courses.index') }}" class="btn btn--ghost">Retour</a>
            @if($course->hasDocument())
                <a href="{{ route('student.courses.document', $course) }}" target="_blank" class="btn btn--primary">Ouvrir le document</a>
            @endif
        </div>
    </div>

    <div class="panel__body student-course-reader__body">
        @if($course->description)
            <section class="student-course-block">
                <h3>Résumé</h3>
                <p>{{ $course->description }}</p>
            </section>
        @endif

        @if($course->objectives)
            <section class="student-course-block">
                <h3>Objectifs pédagogiques</h3>
                <div class="student-course-richtext">{!! nl2br(e($course->objectives)) !!}</div>
            </section>
        @endif

        @if($course->hasRichContent())
            <section class="student-course-block">
                <h3>Contenu du cours</h3>
                <div class="student-course-richtext student-course-richtext--html">{!! $course->content_html !!}</div>
            </section>
        @endif

        @if($course->hasDocument())
            <section class="student-course-block">
                <h3>Document joint</h3>
                <div class="student-course-document-box">
                    <div>
                        <strong>{{ $course->document_name }}</strong>
                        <div class="muted">{{ strtoupper(pathinfo($course->document_name, PATHINFO_EXTENSION)) ?: 'DOC' }} • {{ $course->humanDocumentSize() }}</div>
                    </div>
                    <div class="student-course-document-box__actions">
                        <a href="{{ route('student.courses.document', $course) }}" target="_blank" class="btn btn--primary">Ouvrir</a>
                        <a href="{{ route('student.courses.document.download', $course) }}" class="btn btn--ghost">Télécharger</a>
                    </div>
                </div>
            </section>
        @endif
    </div>
</section>
@endsection
