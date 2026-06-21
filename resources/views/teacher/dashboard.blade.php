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
        <article class="teacher-stat-card"><span>Cours brouillons</span><strong>{{ $stats['draft_courses'] ?? 0 }}</strong></article>
        <article class="teacher-stat-card"><span>Semaine</span><strong>{{ $stats['week_program'] ?? 0 }}</strong></article>
    </div>
</section>

<div class="teacher-grid teacher-grid--two" style="margin-top:22px;">
    <section class="teacher-panel">
        <div class="teacher-panel__head"><h2>Programme de la semaine</h2></div>
        <div class="teacher-cards">
            @forelse($weeklyProgram as $program)
                <article class="teacher-card">
                    <strong>{{ $program->title }}</strong>
                    <p>{{ optional($program->program_date)->format('d/m') }} à {{ $program->start_time ?: '--:--' }} · {{ $program->schoolClass->name ?? '-' }} · {{ $program->subject->name ?? '-' }}</p>
                </article>
            @empty
                <div class="teacher-empty">Aucune activité programmée cette semaine.</div>
            @endforelse
        </div>
    </section>

    <section class="teacher-panel">
        <div class="teacher-panel__head"><h2>Actions rapides</h2></div>
        <div class="teacher-cards">
            <a class="teacher-card" href="{{ route('teacher.courses.create') }}"><strong>Créer</strong><p>Nouveau cours</p></a>
            <a class="teacher-card" href="{{ route('teacher.td.sets.create') }}"><strong>Créer</strong><p>Nouveau TD</p></a>
            <a class="teacher-card" href="{{ route('teacher.td.questions.index') }}"><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong><p>Questions à traiter</p></a>
            <a class="teacher-card" href="{{ route('teacher.weekly-program.index') }}"><strong>{{ $stats['week_program'] ?? 0 }}</strong><p>Programme semaine</p></a>
        </div>
    </section>
</div>

<section class="teacher-section" style="margin-top:22px;">
    <div class="teacher-section__head"><h2>Mes classes et matières</h2><a href="{{ route('teacher.classes.index') }}" class="teacher-btn teacher-btn--ghost">Voir mes classes</a></div>
    <div class="teacher-cards">
        @forelse($assignmentCards as $card)
            <article class="teacher-card">
                <strong>{{ $card['assignment']->schoolClass->name ?? '-' }}</strong>
                <p>{{ $card['assignment']->subject->name ?? '-' }}</p>
                <div class="teacher-class-stats">
                    <div><strong>{{ $card['students'] }}</strong><span>élèves</span></div>
                    <div><strong>{{ $card['course_count'] }}</strong><span>cours</span></div>
                    <div><strong>{{ $card['td_count'] }}</strong><span>TD</span></div>
                    <div><strong>{{ $card['open_questions'] }}</strong><span>questions</span></div>
                </div>
            </article>
        @empty
            <div class="teacher-empty">Aucune affectation active.</div>
        @endforelse
    </div>
</section>
@endsection
