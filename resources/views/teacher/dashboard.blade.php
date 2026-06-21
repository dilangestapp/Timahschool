@extends('layouts.teacher')

@section('title', 'Tableau de bord enseignant')
@section('page_title', 'Bonjour et bon travail')
@section('page_subtitle', 'Retrouvez vos priorités, vos classes, vos cours, vos TD et les questions à traiter.')

@push('styles')
<style>
    .teacher-workspace { display: grid; gap: 22px; }
    .teacher-hero {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
        gap: 20px;
        padding: 24px;
        border-radius: 30px;
        color: #fff;
        background:
            radial-gradient(circle at 15% 15%, rgba(59, 130, 246, .95), transparent 34%),
            radial-gradient(circle at 90% 20%, rgba(124, 58, 237, .85), transparent 34%),
            linear-gradient(135deg, #0f172a 0%, #173c6c 58%, #0f766e 100%);
        box-shadow: 0 22px 54px rgba(15, 23, 42, .18);
    }
    .teacher-hero::after {
        content: '';
        position: absolute;
        width: 260px;
        height: 260px;
        right: -90px;
        bottom: -110px;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
    }
    .teacher-hero > * { position: relative; z-index: 1; }
    .teacher-hero__eyebrow { display: inline-flex; gap: 8px; align-items: center; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.13); font-weight: 800; color: #e0f2fe; }
    .teacher-hero h2 { margin: 16px 0 8px; font-size: clamp(1.8rem, 5vw, 3.2rem); line-height: 1; letter-spacing: -.04em; }
    .teacher-hero p { margin: 0; color: #dbeafe; line-height: 1.55; font-size: 1.02rem; }
    .teacher-hero__actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .teacher-hero__button { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 16px; border-radius: 16px; font-weight: 900; background: #fff; color: #0f172a; }
    .teacher-hero__button--ghost { background: rgba(255,255,255,.14); color: #fff; border: 1px solid rgba(255,255,255,.24); }
    .teacher-today-card { border: 1px solid rgba(255,255,255,.16); background: rgba(255,255,255,.12); backdrop-filter: blur(14px); border-radius: 24px; padding: 18px; }
    .teacher-today-card h3 { margin: 0 0 12px; color: #fff; }
    .teacher-today-list { display: grid; gap: 10px; }
    .teacher-today-item { display: flex; justify-content: space-between; gap: 10px; padding: 11px 12px; border-radius: 16px; background: rgba(255,255,255,.12); color: #eef6ff; font-weight: 800; }

    .teacher-priority-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .teacher-priority-card { position: relative; overflow: hidden; padding: 18px; border-radius: 24px; background: #fff; border: 1px solid var(--teacher-border); box-shadow: 0 12px 30px rgba(15, 23, 42, .06); }
    .teacher-priority-card::before { content: ''; position: absolute; inset: 0 0 auto; height: 5px; background: linear-gradient(90deg, #2563eb, #10b981, #f59e0b, #a855f7); }
    .teacher-priority-card span { display: block; color: var(--teacher-muted); font-weight: 800; margin-bottom: 8px; }
    .teacher-priority-card strong { display: block; font-size: 2.1rem; letter-spacing: -.04em; }
    .teacher-priority-card small { color: var(--teacher-muted); font-weight: 700; }

    .teacher-action-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; }
    .teacher-action-card { min-height: 118px; padding: 16px; border-radius: 24px; background: #fff; border: 1px solid var(--teacher-border); box-shadow: 0 10px 24px rgba(15,23,42,.05); display: flex; flex-direction: column; gap: 10px; justify-content: space-between; transition: .2s ease; }
    .teacher-action-card:hover { transform: translateY(-2px); border-color: #bfdbfe; box-shadow: 0 16px 34px rgba(37,99,235,.10); }
    .teacher-action-card b { display: block; color: var(--teacher-ink); }
    .teacher-action-card small { color: var(--teacher-muted); line-height: 1.35; font-weight: 700; }
    .teacher-action-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 15px; background: #eff6ff; color: #1d4ed8; font-size: 1.25rem; }

    .teacher-dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(300px, .95fr); gap: 20px; }
    .teacher-stack { display: grid; gap: 16px; }
    .teacher-panel--rich { padding: 0; overflow: hidden; }
    .teacher-panel__body { padding: 18px; display: grid; gap: 12px; }
    .teacher-panel__head--rich { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px; border-bottom: 1px solid #edf2fa; }
    .teacher-panel__head--rich h2 { margin: 0; font-size: 1.18rem; }
    .teacher-panel__head--rich p { margin: 4px 0 0; color: var(--teacher-muted); }
    .teacher-pill-link { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 0 12px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-weight: 900; }

    .teacher-assignment-card { padding: 16px; border: 1px solid #e2e8f0; border-radius: 20px; background: linear-gradient(180deg, #fff, #f8fbff); }
    .teacher-assignment-card__top { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
    .teacher-assignment-card__top strong { display: block; font-size: 1.08rem; }
    .teacher-assignment-card__top span { color: var(--teacher-muted); font-weight: 700; }
    .teacher-assignment-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .teacher-assignment-stats div { padding: 10px; border-radius: 16px; background: #fff; border: 1px solid #edf2fa; }
    .teacher-assignment-stats strong { display: block; font-size: 1.15rem; }
    .teacher-assignment-stats span { display: block; margin-top: 2px; color: var(--teacher-muted); font-size: .78rem; font-weight: 800; }
    .teacher-assignment-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .teacher-mini-link { min-height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; border-radius: 999px; background: #fff; border: 1px solid #dbeafe; color: #1d4ed8; font-weight: 900; font-size: .85rem; }

    .teacher-list-card { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: center; padding: 14px; border: 1px solid #e2e8f0; border-radius: 18px; background: #fff; }
    .teacher-list-card strong { display: block; color: var(--teacher-ink); }
    .teacher-list-card p { margin: 5px 0 0; color: var(--teacher-muted); line-height: 1.35; }
    .teacher-status-badge { display: inline-flex; align-items: center; min-height: 30px; padding: 0 10px; border-radius: 999px; background: #f1f5f9; color: #334155; font-size: .78rem; font-weight: 900; white-space: nowrap; }
    .teacher-status-badge--danger { background: #fee2e2; color: #991b1b; }
    .teacher-status-badge--success { background: #dcfce7; color: #166534; }
    .teacher-status-badge--blue { background: #dbeafe; color: #1d4ed8; }

    @media (max-width: 1180px) {
        .teacher-hero, .teacher-dashboard-grid { grid-template-columns: 1fr; }
        .teacher-priority-grid { grid-template-columns: repeat(2, 1fr); }
        .teacher-action-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 680px) {
        .teacher-workspace { gap: 16px; }
        .teacher-hero { padding: 18px; border-radius: 24px; }
        .teacher-hero h2 { font-size: 2rem; }
        .teacher-hero__actions { display: grid; grid-template-columns: 1fr; }
        .teacher-priority-grid, .teacher-action-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
        .teacher-priority-card { padding: 14px; border-radius: 20px; }
        .teacher-priority-card strong { font-size: 1.75rem; }
        .teacher-action-card { min-height: 110px; padding: 13px; border-radius: 20px; }
        .teacher-dashboard-grid { gap: 14px; }
        .teacher-panel__head--rich { align-items: flex-start; flex-direction: column; }
        .teacher-assignment-stats { grid-template-columns: repeat(2, 1fr); }
        .teacher-list-card { grid-template-columns: 1fr; }
        .teacher-status-badge { justify-self: start; }
    }
</style>
@endpush

@section('content')
@php
    $teacherName = $teacher->full_name ?? $teacher->name ?? $teacher->username;
    $hasWeeklyProgram = \Illuminate\Support\Facades\Route::has('teacher.weekly-program.index');
    $coursesIndex = route('teacher.courses.index');
    $coursesCreate = route('teacher.courses.create');
    $tdIndex = route('teacher.td.sets.index');
    $tdCreate = route('teacher.td.sets.create');
    $questionsIndex = route('teacher.td.questions.index');
    $messagesIndex = route('teacher.messages.index');
    $classesIndex = route('teacher.classes.index');
    $studentsActivity = route('teacher.students.activity');
    $weeklyIndex = $hasWeeklyProgram ? route('teacher.weekly-program.index') : route('teacher.dashboard');
@endphp

<div class="teacher-workspace">
    <section class="teacher-hero">
        <div>
            <span class="teacher-hero__eyebrow">⚡ Espace enseignant</span>
            <h2>Bonjour {{ $teacherName }}</h2>
            <p>Votre tableau de bord regroupe vos classes, vos cours, vos TD, les questions des élèves et les messages importants. L’objectif est de vous faire gagner du temps chaque jour.</p>
            <div class="teacher-hero__actions">
                <a href="{{ $coursesCreate }}" class="teacher-hero__button">+ Ajouter un cours</a>
                <a href="{{ $tdCreate }}" class="teacher-hero__button teacher-hero__button--ghost">+ Créer un TD</a>
                <a href="{{ $questionsIndex }}" class="teacher-hero__button teacher-hero__button--ghost">Questions élèves</a>
            </div>
        </div>
        <aside class="teacher-today-card">
            <h3>Travail prioritaire</h3>
            <div class="teacher-today-list">
                <div class="teacher-today-item"><span>Questions ouvertes</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong></div>
                <div class="teacher-today-item"><span>Messages non lus</span><strong>{{ $stats['unread_messages'] ?? 0 }}</strong></div>
                <div class="teacher-today-item"><span>Cours brouillons</span><strong>{{ $stats['draft_courses'] ?? 0 }}</strong></div>
                <div class="teacher-today-item"><span>TD publiés</span><strong>{{ $stats['td_published'] ?? 0 }}</strong></div>
            </div>
        </aside>
    </section>

    <section class="teacher-priority-grid" aria-label="Indicateurs enseignant">
        <article class="teacher-priority-card"><span>Classes affectées</span><strong>{{ $stats['classes'] ?? 0 }}</strong><small>Classes à gérer</small></article>
        <article class="teacher-priority-card"><span>Élèves suivis</span><strong>{{ $stats['students'] ?? 0 }}</strong><small>Dans vos classes</small></article>
        <article class="teacher-priority-card"><span>Cours publiés</span><strong>{{ $stats['published_courses'] ?? 0 }}</strong><small>Disponibles pour élèves</small></article>
        <article class="teacher-priority-card"><span>Questions à traiter</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong><small>Priorité pédagogique</small></article>
    </section>

    <section class="teacher-action-grid" aria-label="Actions rapides">
        <a class="teacher-action-card" href="{{ $coursesCreate }}"><span class="teacher-action-icon">📘</span><span><b>Créer un cours</b><small>Rédiger ou joindre un document</small></span></a>
        <a class="teacher-action-card" href="{{ $coursesIndex }}"><span class="teacher-action-icon">📚</span><span><b>Mes cours</b><small>Brouillons, publiés, archivés</small></span></a>
        <a class="teacher-action-card" href="{{ $tdCreate }}"><span class="teacher-action-icon">📝</span><span><b>Créer un TD</b><small>Sujet, corrigé, délai</small></span></a>
        <a class="teacher-action-card" href="{{ $questionsIndex }}"><span class="teacher-action-icon">❓</span><span><b>Questions élèves</b><small>Répondre rapidement</small></span></a>
        <a class="teacher-action-card" href="{{ $messagesIndex }}"><span class="teacher-action-icon">💬</span><span><b>Messagerie</b><small>Élèves et classes</small></span></a>
        <a class="teacher-action-card" href="{{ $studentsActivity }}"><span class="teacher-action-icon">📊</span><span><b>Suivi élèves</b><small>Activité et progression</small></span></a>
    </section>

    <div class="teacher-dashboard-grid">
        <section class="teacher-panel teacher-panel--rich">
            <div class="teacher-panel__head--rich">
                <div>
                    <h2>Mes classes et matières</h2>
                    <p>Travaillez directement par classe : cours, TD, élèves et questions.</p>
                </div>
                <a href="{{ $classesIndex }}" class="teacher-pill-link">Voir tout</a>
            </div>
            <div class="teacher-panel__body">
                @forelse($assignmentCards as $card)
                    <article class="teacher-assignment-card">
                        <div class="teacher-assignment-card__top">
                            <div>
                                <strong>{{ $card['assignment']->schoolClass->name ?? '-' }}</strong>
                                <span>{{ $card['assignment']->subject->name ?? '-' }}</span>
                            </div>
                            @if(($card['open_questions'] ?? 0) > 0)
                                <span class="teacher-status-badge teacher-status-badge--danger">{{ $card['open_questions'] }} question(s)</span>
                            @else
                                <span class="teacher-status-badge teacher-status-badge--success">À jour</span>
                            @endif
                        </div>
                        <div class="teacher-assignment-stats">
                            <div><strong>{{ $card['students'] }}</strong><span>Élèves</span></div>
                            <div><strong>{{ $card['course_count'] }}</strong><span>Cours</span></div>
                            <div><strong>{{ $card['td_count'] }}</strong><span>TD</span></div>
                            <div><strong>{{ $card['unread_messages'] }}</strong><span>Messages</span></div>
                        </div>
                        <div class="teacher-assignment-actions">
                            <a class="teacher-mini-link" href="{{ $coursesCreate }}">Ajouter cours</a>
                            <a class="teacher-mini-link" href="{{ $tdCreate }}">Créer TD</a>
                            <a class="teacher-mini-link" href="{{ $studentsActivity }}">Voir élèves</a>
                            <a class="teacher-mini-link" href="{{ $messagesIndex }}">Message</a>
                        </div>
                    </article>
                @empty
                    <div class="teacher-empty">Aucune affectation active. L’administration doit vous affecter une classe et une matière.</div>
                @endforelse
            </div>
        </section>

        <div class="teacher-stack">
            <section class="teacher-panel teacher-panel--rich">
                <div class="teacher-panel__head--rich">
                    <div>
                        <h2>TD et corrections</h2>
                        <p>Suivez les TD publiés, les questions et les copies à traiter.</p>
                    </div>
                    <a href="{{ $tdIndex }}" class="teacher-pill-link">Gérer</a>
                </div>
                <div class="teacher-panel__body">
                    <article class="teacher-list-card"><div><strong>{{ $stats['td_total'] ?? 0 }} TD au total</strong><p>{{ $stats['td_published'] ?? 0 }} TD publiés pour vos classes.</p></div><span class="teacher-status-badge teacher-status-badge--blue">TD</span></article>
                    <a class="teacher-list-card" href="{{ $questionsIndex }}"><div><strong>{{ $stats['td_questions_open'] ?? 0 }} question(s) ouvertes</strong><p>Les élèves attendent une réponse sur leurs TD.</p></div><span class="teacher-status-badge {{ ($stats['td_questions_open'] ?? 0) > 0 ? 'teacher-status-badge--danger' : 'teacher-status-badge--success' }}">Questions</span></a>
                    <a class="teacher-list-card" href="{{ $tdCreate }}"><div><strong>Nouveau TD</strong><p>Créer un sujet, ajouter un corrigé et préparer la publication.</p></div><span class="teacher-status-badge teacher-status-badge--blue">Créer</span></a>
                </div>
            </section>

            <section class="teacher-panel teacher-panel--rich">
                <div class="teacher-panel__head--rich">
                    <div>
                        <h2>Programme et rappels</h2>
                        <p>Activités de la semaine et prochains travaux.</p>
                    </div>
                    <a href="{{ $weeklyIndex }}" class="teacher-pill-link">Ouvrir</a>
                </div>
                <div class="teacher-panel__body">
                    @forelse($weeklyProgram as $program)
                        <article class="teacher-list-card">
                            <div>
                                <strong>{{ $program->title }}</strong>
                                <p>{{ optional($program->program_date)->format('d/m') }} à {{ $program->start_time ?: '--:--' }} · {{ $program->schoolClass->name ?? '-' }} · {{ $program->subject->name ?? '-' }}</p>
                            </div>
                            <span class="teacher-status-badge teacher-status-badge--blue">Semaine</span>
                        </article>
                    @empty
                        <div class="teacher-empty">Aucune activité programmée cette semaine.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="teacher-dashboard-grid">
        <section class="teacher-panel teacher-panel--rich">
            <div class="teacher-panel__head--rich">
                <div><h2>Cours récents</h2><p>Derniers cours préparés ou publiés.</p></div>
                <a href="{{ $coursesIndex }}" class="teacher-pill-link">Tous les cours</a>
            </div>
            <div class="teacher-panel__body">
                @forelse($recentCourses as $course)
                    <a class="teacher-list-card" href="{{ route('teacher.courses.edit', $course) }}">
                        <div>
                            <strong>{{ $course->title }}</strong>
                            <p>{{ $course->schoolClass->name ?? '-' }} · {{ $course->subject->name ?? '-' }}</p>
                        </div>
                        <span class="teacher-status-badge {{ $course->status === \App\Models\Course::STATUS_PUBLISHED ? 'teacher-status-badge--success' : 'teacher-status-badge--blue' }}">{{ $course->status }}</span>
                    </a>
                @empty
                    <div class="teacher-empty">Aucun cours créé pour le moment.</div>
                @endforelse
            </div>
        </section>

        <section class="teacher-panel teacher-panel--rich">
            <div class="teacher-panel__head--rich">
                <div><h2>Messages et questions</h2><p>Échanges récents avec les élèves.</p></div>
                <a href="{{ $messagesIndex }}" class="teacher-pill-link">Messagerie</a>
            </div>
            <div class="teacher-panel__body">
                @forelse($recentTdQuestions as $thread)
                    <a class="teacher-list-card" href="{{ route('teacher.td.questions.show', $thread) }}">
                        <div>
                            <strong>{{ $thread->student->full_name ?? $thread->student->name ?? 'Élève' }}</strong>
                            <p>{{ $thread->tdSet->title ?? 'TD' }} · {{ $thread->subject->name ?? '-' }}</p>
                        </div>
                        <span class="teacher-status-badge {{ $thread->status === \App\Models\TdQuestionThread::STATUS_OPEN ? 'teacher-status-badge--danger' : 'teacher-status-badge--success' }}">{{ $thread->status }}</span>
                    </a>
                @empty
                    @forelse($recentMessages as $message)
                        <a class="teacher-list-card" href="{{ route('teacher.messages.show', $message) }}">
                            <div>
                                <strong>{{ $message->student->full_name ?? $message->student->name ?? 'Élève' }}</strong>
                                <p>{{ $message->subject->name ?? 'Message' }} · {{ $message->schoolClass->name ?? '-' }}</p>
                            </div>
                            <span class="teacher-status-badge {{ $message->status === \App\Models\TeacherMessage::STATUS_UNREAD ? 'teacher-status-badge--danger' : 'teacher-status-badge--success' }}">{{ $message->status }}</span>
                        </a>
                    @empty
                        <div class="teacher-empty">Aucune question ou message récent.</div>
                    @endforelse
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
