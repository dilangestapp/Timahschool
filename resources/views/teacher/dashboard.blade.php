@extends('layouts.teacher')

@section('title', 'Tableau de bord enseignant')
@section('page_title', 'Bonjour et bon travail')
@section('page_subtitle', 'Pilotez vos cours, TD, questions et programme de la semaine.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Bonjour {{ $teacher->full_name ?? $teacher->name ?? $teacher->username }}</h2>
            <p class="teacher-muted">Vous gérez {{ $stats['classes'] ?? 0 }} classe(s), {{ $stats['subjects'] ?? 0 }} matière(s), {{ $stats['courses'] ?? 0 }} cours et {{ $stats['td_total'] ?? 0 }} TD.</p>
        </div>
        <div class="teacher-actions-inline">
            <a href="{{ route('teacher.courses.create') }}" class="teacher-btn teacher-btn--primary">Nouveau cours</a>
            <a href="{{ route('teacher.td.sets.create') }}" class="teacher-btn teacher-btn--primary">Nouveau TD</a>
            <a href="{{ route('teacher.weekly-program.index') }}" class="teacher-btn teacher-btn--ghost">Programme semaine</a>
        </div>
    </div>

    <div class="teacher-grid teacher-grid--stats">
        <article class="teacher-stat-card"><span>Classes</span><strong>{{ $stats['classes'] ?? 0 }}</strong></article>
        <article class="teacher-stat-card"><span>Cours</span><strong>{{ $stats['courses'] ?? 0 }}</strong></article>
        <article class="teacher-stat-card"><span>TD</span><strong>{{ $stats['td_total'] ?? 0 }}</strong></article>
        <article class="teacher-stat-card"><span>Questions</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong></article>
    </div>
</section>
@endsection
