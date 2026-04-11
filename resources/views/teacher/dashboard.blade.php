@extends('layouts.teacher')

@section('title', 'Tableau de bord enseignant')
@section('page_title', 'Tableau de bord enseignant')
@section('page_subtitle', 'Gérez vos classes, vos TD, vos corrigés et les questions liées à vos affectations.')

@section('content')
<div class="teacher-grid teacher-grid--stats">
    <div class="teacher-stat-card"><span>Classes affectées</span><strong>{{ $stats['classes'] ?? 0 }}</strong></div>
    <div class="teacher-stat-card"><span>Matières affectées</span><strong>{{ $stats['subjects'] ?? 0 }}</strong></div>
    <div class="teacher-stat-card"><span>Mes TD</span><strong>{{ $stats['td_total'] ?? 0 }}</strong></div>
    <div class="teacher-stat-card"><span>Questions TD ouvertes</span><strong>{{ $stats['td_questions_open'] ?? 0 }}</strong></div>
</div>

<section class="teacher-section">
    <div class="teacher-section__head">
        <h2>Mes affectations</h2>
        <a href="{{ route('teacher.classes.index') }}" class="teacher-btn teacher-btn--ghost">Voir toutes mes classes</a>
    </div>
    <div class="teacher-cards">
        @forelse($assignments as $assignment)
            <article class="teacher-card">
                <strong>{{ $assignment->schoolClass->name ?? '-' }}</strong>
                <p>{{ $assignment->subject->name ?? '-' }}</p>
            </article>
        @empty
            <div class="teacher-empty">Aucune affectation active.</div>
        @endforelse
    </div>
</section>

<div class="teacher-grid teacher-grid--two">
    <section class="teacher-panel">
        <div class="teacher-panel__head"><h2>Derniers TD</h2></div>
        <div class="teacher-table-wrap">
            <table class="teacher-table">
                <thead><tr><th>Titre</th><th>Classe</th><th>Matière</th><th>Statut</th></tr></thead>
                <tbody>
                @forelse($recentTdSets as $td)
                    <tr>
                        <td><a href="{{ route('teacher.td.sets.edit', $td) }}">{{ $td->title }}</a></td>
                        <td>{{ $td->schoolClass->name ?? '-' }}</td>
                        <td>{{ $td->subject->name ?? '-' }}</td>
                        <td>{{ $td->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="teacher-empty-row">Aucun TD pour le moment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="teacher-panel">
        <div class="teacher-panel__head"><h2>Dernières questions TD</h2></div>
        <div class="teacher-table-wrap">
            <table class="teacher-table">
                <thead><tr><th>Élève</th><th>TD</th><th>Matière</th><th>Statut</th></tr></thead>
                <tbody>
                @forelse($recentTdQuestions as $thread)
                    <tr>
                        <td><a href="{{ route('teacher.td.questions.show', $thread) }}">{{ $thread->student->full_name ?? $thread->student->name ?? $thread->student->username ?? '-' }}</a></td>
                        <td>{{ $thread->tdSet->title ?? '-' }}</td>
                        <td>{{ $thread->subject->name ?? '-' }}</td>
                        <td>{{ $thread->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="teacher-empty-row">Aucune question TD pour le moment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
