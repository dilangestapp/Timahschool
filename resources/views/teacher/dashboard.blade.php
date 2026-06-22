@extends('layouts.teacher-dashboard')

@section('title', 'Tableau de bord enseignant')

@section('content')
@php
    $teacherName = $teacher->full_name ?? $teacher->name ?? $teacher->username;
    $todayLabel = ucfirst(now()->translatedFormat('l d F Y'));
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

<div class="resp-dashboard teacher-dashboard-clean">
    <section class="resp-overview">
        <div class="resp-header">
            <div>
                <h1>Bonjour, {{ $teacherName }}</h1>
                <p>{{ $todayLabel }} — Vos classes, cours, TD, messages et suivis pédagogiques.</p>
            </div>
            <a href="{{ $coursesCreate }}" class="resp-btn"><span>＋</span> Ajouter un cours</a>
        </div>

        <div class="resp-stats-main">
            <div class="resp-stat-main" style="border-left-color:#1a237e;"><div class="resp-stat-main__label">Classes</div><div class="resp-stat-main__value" style="color:#1a237e;">{{ $stats['classes'] ?? 0 }}</div><div class="resp-stat-main__hint">affectées</div></div>
            <div class="resp-stat-main" style="border-left-color:#26a69a;"><div class="resp-stat-main__label">Élèves</div><div class="resp-stat-main__value" style="color:#26a69a;">{{ $stats['students'] ?? 0 }}</div><div class="resp-stat-main__hint">suivis</div></div>
            <div class="resp-stat-main" style="border-left-color:#5c6bc0;"><div class="resp-stat-main__label">Cours publiés</div><div class="resp-stat-main__value" style="color:#5c6bc0;">{{ $stats['published_courses'] ?? 0 }}</div><div class="resp-stat-main__hint">visibles aux élèves</div></div>
            <div class="resp-stat-main" style="border-left-color:#ef5350;"><div class="resp-stat-main__label">Questions</div><div class="resp-stat-main__value" style="color:#ef5350;">{{ $stats['td_questions_open'] ?? 0 }}</div><div class="resp-stat-main__hint">à traiter</div></div>
        </div>

        <div class="resp-stats-mini">
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e8eaf6;color:#5c6bc0;">▤</div><div><div class="resp-mini-value">{{ $stats['draft_courses'] ?? 0 }}</div><div class="resp-mini-label">cours en brouillon</div></div></div>
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e0f2f1;color:#26a69a;">☑</div><div><div class="resp-mini-value">{{ $stats['td_published'] ?? 0 }}</div><div class="resp-mini-label">TD publiés</div></div></div>
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#fce4ec;color:#e91e63;">◌</div><div><div class="resp-mini-value">{{ $stats['unread_messages'] ?? 0 }}</div><div class="resp-mini-label">messages non lus</div></div></div>
            <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#fff3e0;color:#fb8c00;">▣</div><div><div class="resp-mini-value">{{ $stats['week_program'] ?? 0 }}</div><div class="resp-mini-label">activités semaine</div></div></div>
        </div>

        <div class="resp-bottom-grid">
            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Mes classes et matières</span><a href="{{ $classesIndex }}" class="resp-card__link">Voir tout</a></div>
                <div class="resp-list resp-list--limited">
                    @forelse($assignmentCards as $card)
                        <div class="resp-list-row">
                            <div>
                                <strong>{{ $card['assignment']->schoolClass->name ?? '-' }}</strong>
                                <small>{{ $card['assignment']->subject->name ?? '-' }} · {{ $card['students'] }} élèves</small>
                            </div>
                            <div class="resp-pills">
                                <span class="resp-pill">{{ $card['course_count'] }} cours</span>
                                <span class="resp-pill resp-pill--blue">{{ $card['td_count'] }} TD</span>
                                @if(($card['open_questions'] ?? 0) > 0)
                                    <span class="resp-pill" style="background:#ffebee;color:#c62828;">{{ $card['open_questions'] }} question(s)</span>
                                @else
                                    <span class="resp-pill" style="background:#e8f5e9;color:#2e7d32;">à jour</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="resp-empty">Aucune affectation active. L’administration doit vous affecter une classe et une matière.</div>
                    @endforelse
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Travail prioritaire</span></div>
                <div class="resp-list">
                    <a href="{{ $questionsIndex }}" class="resp-content-row"><span>Questions ouvertes</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong></a>
                    <a href="{{ $messagesIndex }}" class="resp-content-row"><span>Messages non lus</span><strong>{{ $stats['unread_messages'] ?? 0 }}</strong></a>
                    <a href="{{ $coursesIndex }}" class="resp-content-row"><span>Cours brouillons</span><strong>{{ $stats['draft_courses'] ?? 0 }}</strong></a>
                    <a href="{{ $tdIndex }}" class="resp-content-row"><span>TD publiés</span><strong>{{ $stats['td_published'] ?? 0 }}</strong></a>
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Actions rapides</span></div>
                <div class="resp-action-list">
                    <a href="{{ $coursesCreate }}" class="resp-action-card" style="background:#e8eaf6;color:#3949ab;">▭ Créer un cours</a>
                    <a href="{{ $coursesIndex }}" class="resp-action-card" style="background:#e3f2fd;color:#1565c0;">▤ Mes cours</a>
                    <a href="{{ $tdCreate }}" class="resp-action-card" style="background:#e0f2f1;color:#00695c;">☑ Créer un TD</a>
                    <a href="{{ $questionsIndex }}" class="resp-action-card" style="background:#fff3e0;color:#e65100;">? Questions élèves</a>
                    <a href="{{ $messagesIndex }}" class="resp-action-card" style="background:#fce4ec;color:#880e4f;">◌ Messagerie</a>
                    <a href="{{ $studentsActivity }}" class="resp-action-card" style="background:#f5f7ff;color:#1a237e;">◉ Suivi élèves</a>
                </div>
            </div>
        </div>

        <div class="resp-bottom-grid" style="grid-template-columns:1fr 1fr;">
            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Cours récents</span><a href="{{ $coursesIndex }}" class="resp-card__link">Tous les cours</a></div>
                <div class="resp-list resp-list--limited">
                    @forelse($recentCourses as $course)
                        <a class="resp-list-row" href="{{ route('teacher.courses.edit', $course) }}">
                            <div><strong>{{ $course->title }}</strong><small>{{ $course->schoolClass->name ?? '-' }} · {{ $course->subject->name ?? '-' }}</small></div>
                            <div class="resp-pills"><span class="resp-pill {{ $course->status === \App\Models\Course::STATUS_PUBLISHED ? 'resp-pill--blue' : '' }}">{{ $course->status }}</span></div>
                        </a>
                    @empty
                        <div class="resp-empty">Aucun cours créé pour le moment.</div>
                    @endforelse
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">TD et corrections</span><a href="{{ $tdIndex }}" class="resp-card__link">Gérer</a></div>
                <div class="resp-list">
                    <a class="resp-content-row" href="{{ $tdIndex }}"><span>TD au total</span><strong>{{ $stats['td_total'] ?? 0 }}</strong></a>
                    <a class="resp-content-row" href="{{ $tdIndex }}"><span>TD publiés</span><strong>{{ $stats['td_published'] ?? 0 }}</strong></a>
                    <a class="resp-content-row" href="{{ $questionsIndex }}"><span>Questions ouvertes</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong></a>
                    <a class="resp-action-card" href="{{ $tdCreate }}" style="background:#e8eaf6;color:#3949ab;">＋ Nouveau TD</a>
                </div>
            </div>
        </div>

        <div class="resp-bottom-grid" style="grid-template-columns:1fr 1fr;">
            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Messages et questions récents</span><a href="{{ $messagesIndex }}" class="resp-card__link">Messagerie</a></div>
                <div class="resp-list resp-list--limited">
                    @forelse($recentTdQuestions as $thread)
                        <a class="resp-list-row" href="{{ route('teacher.td.questions.show', $thread) }}">
                            <div><strong>{{ $thread->student->full_name ?? $thread->student->name ?? 'Élève' }}</strong><small>{{ $thread->tdSet->title ?? 'TD' }} · {{ $thread->subject->name ?? '-' }}</small></div>
                            <div class="resp-pills"><span class="resp-pill" style="{{ $thread->status === \App\Models\TdQuestionThread::STATUS_OPEN ? 'background:#ffebee;color:#c62828;' : 'background:#e8f5e9;color:#2e7d32;' }}">{{ $thread->status }}</span></div>
                        </a>
                    @empty
                        @forelse($recentMessages->take(4) as $message)
                            <a class="resp-list-row" href="{{ route('teacher.messages.show', $message) }}">
                                <div><strong>{{ $message->student->full_name ?? $message->student->name ?? 'Élève' }}</strong><small>{{ $message->subject->name ?? 'Message' }} · {{ $message->schoolClass->name ?? '-' }}</small></div>
                                <div class="resp-pills"><span class="resp-pill" style="{{ $message->status === \App\Models\TeacherMessage::STATUS_UNREAD ? 'background:#ffebee;color:#c62828;' : 'background:#e8f5e9;color:#2e7d32;' }}">{{ $message->status }}</span></div>
                            </a>
                        @empty
                            <div class="resp-empty">Aucune question ou message récent.</div>
                        @endforelse
                    @endforelse
                </div>
            </div>

            <div class="resp-card">
                <div class="resp-card__head"><span class="resp-card__title">Programme et rappels</span><a href="{{ $weeklyIndex }}" class="resp-card__link">Ouvrir</a></div>
                <div class="resp-list resp-list--limited">
                    @forelse($weeklyProgram as $program)
                        <div class="resp-list-row">
                            <div><strong>{{ $program->title }}</strong><small>{{ optional($program->program_date)->format('d/m') }} à {{ $program->start_time ?: '--:--' }} · {{ $program->schoolClass->name ?? '-' }} · {{ $program->subject->name ?? '-' }}</small></div>
                            <div class="resp-pills"><span class="resp-pill resp-pill--blue">Semaine</span></div>
                        </div>
                    @empty
                        <div class="resp-empty">Aucune activité programmée cette semaine.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
