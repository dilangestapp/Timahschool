@extends('layouts.teacher')

@section('title', 'TB Secrétaire général')
@section('page_title', 'TB Secrétaire général')
@section('page_subtitle', 'Pilotage global de TIMAH ACADEMY : cours, TD, questions, enseignants, départements et relances.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');
    $authorized = false;
    $responsibility = null;
    $stats = ['teachers' => 0, 'students' => 0, 'courses_published' => 0, 'courses_draft' => 0, 'td_published' => 0, 'questions_open' => 0, 'notes_open' => 0, 'departments' => 0];
    $teachers = collect();
    $courses = collect();
    $tdSets = collect();
    $questions = collect();
    $departments = collect();
    $notes = collect();

    if ($schemaReady) {
        $responsibility = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('scope_type', 'platform')
            ->where(function ($query) {
                $query->where('role_title', 'like', '%Secrétaire général%')
                    ->orWhere('role_title', 'like', '%Coordinateur général%');
            })
            ->first();

        $authorized = (bool) $responsibility;

        if ($authorized) {
            $stats = [
                'teachers' => \Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') ? \Illuminate\Support\Facades\DB::table('teacher_assignments')->where('is_active', true)->distinct()->count('teacher_id') : 0,
                'students' => \Illuminate\Support\Facades\Schema::hasTable('student_profiles') ? \Illuminate\Support\Facades\DB::table('student_profiles')->count() : 0,
                'courses_published' => \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'published')->count() : 0,
                'courses_draft' => \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'draft')->count() : 0,
                'td_published' => \Illuminate\Support\Facades\Schema::hasTable('td_sets') ? \Illuminate\Support\Facades\DB::table('td_sets')->where('status', 'published')->count() : 0,
                'questions_open' => \Illuminate\Support\Facades\Schema::hasTable('td_question_threads') ? \Illuminate\Support\Facades\DB::table('td_question_threads')->where('status', 'open')->count() : 0,
                'notes_open' => \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes')->where('status', 'open')->count(),
                'departments' => \Illuminate\Support\Facades\Schema::hasTable('teaching_departments') ? \Illuminate\Support\Facades\DB::table('teaching_departments')->where('is_active', true)->count() : 0,
            ];

            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                $teachers = \Illuminate\Support\Facades\DB::table('teacher_assignments as ta')
                    ->join('users as u', 'u.id', '=', 'ta.teacher_id')
                    ->leftJoin('school_classes as c', 'c.id', '=', 'ta.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')
                    ->where('ta.is_active', true)
                    ->select('u.id', 'u.full_name', 'u.name', 'u.username', 'u.phone', 'c.name as class_name', 's.name as subject_name')
                    ->orderByDesc('ta.id')
                    ->limit(10)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
                $courses = \Illuminate\Support\Facades\DB::table('courses as c')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'c.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'c.subject_id')
                    ->select('c.id', 'c.title', 'c.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('c.id')
                    ->limit(8)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_sets')) {
                $tdSets = \Illuminate\Support\Facades\DB::table('td_sets as td')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'td.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'td.subject_id')
                    ->select('td.id', 'td.title', 'td.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('td.id')
                    ->limit(8)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_question_threads')) {
                $questions = \Illuminate\Support\Facades\DB::table('td_question_threads as q')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'q.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id')
                    ->select('q.id', 'q.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('q.id')
                    ->limit(8)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('teaching_departments')) {
                $departments = \Illuminate\Support\Facades\DB::table('teaching_departments as dep')
                    ->leftJoin('teaching_divisions as div', 'div.id', '=', 'dep.teaching_division_id')
                    ->select('dep.id', 'dep.name', 'dep.code', 'div.name as division_name')
                    ->where('dep.is_active', true)
                    ->orderBy('dep.name')
                    ->limit(10)
                    ->get();
            }

            $notes = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')
                ->select('n.*', 'u.full_name', 'u.name', 'u.username')
                ->orderByRaw("CASE n.severity WHEN 'urgent' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                ->orderByDesc('n.id')
                ->limit(10)
                ->get();
        }
    }
@endphp

<style>
    .sg-wrap{display:grid;gap:18px}.sg-hero{border-radius:30px;padding:24px;color:#fff;background:linear-gradient(135deg,#020617,#1d4ed8,#0f766e);box-shadow:0 24px 60px rgba(15,23,42,.22)}.sg-hero h2{margin:8px 0;font-size:clamp(2rem,5vw,3.4rem)}.sg-hero p{color:#dbeafe;max-width:900px}.sg-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.sg-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;background:#fff;color:#0f172a;font-weight:900;text-decoration:none}.sg-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.sg-card,.sg-panel{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.sg-card span{display:block;color:#64748b;font-weight:800}.sg-card strong{display:block;font-size:2rem}.sg-panels{display:grid;grid-template-columns:1fr 1fr;gap:16px}.sg-list{display:grid;gap:10px}.sg-row{border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc}.sg-row strong{display:block}.sg-row span,.sg-row small{color:#64748b}.sg-empty{padding:18px;border-radius:18px;background:#f8fafc;color:#64748b;text-align:center}@media(max-width:1000px){.sg-grid,.sg-panels{grid-template-columns:1fr 1fr}}@media(max-width:680px){.sg-grid,.sg-panels{grid-template-columns:1fr}}
</style>

<div class="sg-wrap">
    @if(!$schemaReady)
        <section class="sg-hero"><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore installées.</p></section>
    @elseif(!$authorized)
        <section class="sg-hero"><h2>Accès réservé</h2><p>Ce TB est réservé au Secrétaire général ou Coordinateur général nommé sur la portée Plateforme entière.</p><div class="sg-actions"><a class="sg-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="sg-hero">
            <span>Secrétariat général TIMAH ACADEMY</span>
            <h2>Pilotage global de la plateforme</h2>
            <p>Le Secrétaire général suit l’activité pédagogique, les retards, les questions ouvertes, les cours, les TD, les responsables et les départements. Il relance et signale sans toucher aux paiements ni aux réglages sensibles.</p>
            <div class="sg-actions">
                <a class="sg-btn" href="{{ route('teacher.dashboard') }}">Retour enseignant</a>
                @if(\Illuminate\Support\Facades\Route::has('admin.organization.index'))<a class="sg-btn" href="{{ route('admin.organization.index') }}">Administration pédagogique</a>@endif
            </div>
        </section>

        <section class="sg-grid">
            <article class="sg-card"><span>Enseignants suivis</span><strong>{{ $stats['teachers'] }}</strong></article>
            <article class="sg-card"><span>Élèves suivis</span><strong>{{ $stats['students'] }}</strong></article>
            <article class="sg-card"><span>Cours publiés</span><strong>{{ $stats['courses_published'] }}</strong></article>
            <article class="sg-card"><span>Questions ouvertes</span><strong>{{ $stats['questions_open'] }}</strong></article>
            <article class="sg-card"><span>TD publiés</span><strong>{{ $stats['td_published'] }}</strong></article>
            <article class="sg-card"><span>Cours brouillons</span><strong>{{ $stats['courses_draft'] }}</strong></article>
            <article class="sg-card"><span>Notes ouvertes</span><strong>{{ $stats['notes_open'] }}</strong></article>
            <article class="sg-card"><span>Départements</span><strong>{{ $stats['departments'] }}</strong></article>
        </section>

        <div class="sg-panels">
            <section class="sg-panel"><h3>Enseignants à suivre</h3><div class="sg-list">@forelse($teachers as $teacher)<div class="sg-row"><strong>{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</strong><span>{{ $teacher->class_name ?? '-' }} · {{ $teacher->subject_name ?? '-' }}</span></div>@empty<div class="sg-empty">Aucun enseignant affecté.</div>@endforelse</div></section>
            <section class="sg-panel"><h3>Départements / filières</h3><div class="sg-list">@forelse($departments as $department)<div class="sg-row"><strong>{{ $department->name }}</strong><span>{{ $department->division_name ?? 'Non classé' }} · {{ $department->code ?? '' }}</span></div>@empty<div class="sg-empty">Aucun département.</div>@endforelse</div></section>
            <section class="sg-panel"><h3>Cours récents</h3><div class="sg-list">@forelse($courses as $course)<div class="sg-row"><strong>{{ $course->title }}</strong><span>{{ $course->class_name ?? '-' }} · {{ $course->subject_name ?? '-' }}</span><small>{{ $course->status }}</small></div>@empty<div class="sg-empty">Aucun cours.</div>@endforelse</div></section>
            <section class="sg-panel"><h3>TD récents</h3><div class="sg-list">@forelse($tdSets as $td)<div class="sg-row"><strong>{{ $td->title }}</strong><span>{{ $td->class_name ?? '-' }} · {{ $td->subject_name ?? '-' }}</span><small>{{ $td->status }}</small></div>@empty<div class="sg-empty">Aucun TD.</div>@endforelse</div></section>
            <section class="sg-panel"><h3>Questions ouvertes</h3><div class="sg-list">@forelse($questions as $question)<div class="sg-row"><strong>{{ $question->subject_name ?: 'Question élève' }}</strong><span>{{ $question->class_name ?? '-' }}</span><small>{{ $question->status }}</small></div>@empty<div class="sg-empty">Aucune question ouverte.</div>@endforelse</div></section>
            <section class="sg-panel"><h3>Notes / relances</h3><div class="sg-list">@forelse($notes as $note)<div class="sg-row"><strong>{{ $note->title }}</strong><span>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone générale')) }}</span><small>{{ $note->severity }} · {{ $note->status }}</small></div>@empty<div class="sg-empty">Aucune note.</div>@endforelse</div></section>
        </div>
    @endif
</div>
@endsection
