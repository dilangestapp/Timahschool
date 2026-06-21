@extends('layouts.teacher')

@php
    $scope = $requiredScope ?? 'division';
    $isDivision = $scope === 'division';
    $pageTitle = $dashboardTitle ?? ($isDivision ? 'TB Responsable type d’enseignement' : 'TB Responsable département / filière');
@endphp

@section('title', $pageTitle)
@section('page_title', $pageTitle)
@section('page_subtitle', $isDivision ? 'Suivi des départements, enseignants, cours, TD et questions rattachés à un type d’enseignement.' : 'Suivi des enseignants, classes, cours, TD et questions rattachés à un département ou une filière.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities')
        && \Illuminate\Support\Facades\Schema::hasTable('teaching_divisions')
        && \Illuminate\Support\Facades\Schema::hasTable('teaching_departments')
        && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');

    $responsibilities = collect();
    $responsibility = null;
    $area = null;
    $division = null;
    $departments = collect();
    $subjectIds = collect();
    $classIds = collect();
    $stats = ['teachers' => 0, 'students' => 0, 'departments' => 0, 'classes' => 0, 'courses_published' => 0, 'courses_draft' => 0, 'td_published' => 0, 'questions_open' => 0, 'notes_open' => 0];
    $teachers = collect();
    $classes = collect();
    $courses = collect();
    $tdSets = collect();
    $questions = collect();
    $notes = collect();

    $statusClass = function ($status) {
        return match ($status) {
            'published', 'active', 'open' => 'rz-badge--success',
            'draft', 'pending', 'programmed' => 'rz-badge--warning',
            'urgent' => 'rz-badge--danger',
            default => 'rz-badge--neutral',
        };
    };

    $labelStatus = function ($status) {
        return match ($status) {
            'published' => 'Publié',
            'draft' => 'Brouillon',
            'open' => 'Ouverte',
            'active' => 'Actif',
            'urgent' => 'Urgent',
            'warning' => 'Attention',
            'info' => 'Info',
            default => $status ?: '—',
        };
    };

    if ($schemaReady) {
        $responsibilities = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities as pr')
            ->leftJoin('teaching_divisions as div', 'div.id', '=', 'pr.teaching_division_id')
            ->leftJoin('teaching_departments as dep', 'dep.id', '=', 'pr.teaching_department_id')
            ->where('pr.user_id', auth()->id())
            ->where('pr.is_active', true)
            ->where('pr.scope_type', $scope)
            ->select('pr.*', 'div.name as division_name', 'dep.name as department_name')
            ->orderByDesc('pr.id')
            ->get();

        $responsibility = $responsibilities->firstWhere('id', (int) request('responsibility')) ?: $responsibilities->first();

        if ($responsibility) {
            if ($isDivision) {
                $division = \Illuminate\Support\Facades\DB::table('teaching_divisions')->where('id', $responsibility->teaching_division_id)->first();
                $area = $division;
                $departments = \Illuminate\Support\Facades\DB::table('teaching_departments')
                    ->where('teaching_division_id', $responsibility->teaching_division_id)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->orderBy('name')
                    ->get();
            } else {
                $department = \Illuminate\Support\Facades\DB::table('teaching_departments')->where('id', $responsibility->teaching_department_id)->first();
                $area = $department;
                $departments = $department ? collect([$department]) : collect();
                if ($department && $department->teaching_division_id) {
                    $division = \Illuminate\Support\Facades\DB::table('teaching_divisions')->where('id', $department->teaching_division_id)->first();
                }
            }

            $subjectIds = $departments->pluck('subject_id')->filter()->unique()->values();
            $classIds = $departments->pluck('school_class_id')->filter()->unique()->values();

            $applyScope = function ($query, string $table, string $alias = '') use ($subjectIds, $classIds) {
                $prefix = $alias !== '' ? $alias . '.' : '';
                $hasSubject = $subjectIds->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasColumn($table, 'subject_id');
                $hasClass = $classIds->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasColumn($table, 'school_class_id');
                if (!$hasSubject && !$hasClass) {
                    return $query;
                }
                return $query->where(function ($sub) use ($hasSubject, $hasClass, $prefix, $subjectIds, $classIds) {
                    if ($hasSubject) {
                        $sub->orWhereIn($prefix . 'subject_id', $subjectIds->all());
                    }
                    if ($hasClass) {
                        $sub->orWhereIn($prefix . 'school_class_id', $classIds->all());
                    }
                });
            };

            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_assignments')) {
                $teacherCount = \Illuminate\Support\Facades\DB::table('teacher_assignments as ta')->where('ta.is_active', true);
                $teacherCount = $applyScope($teacherCount, 'teacher_assignments', 'ta');
                $stats['teachers'] = $teacherCount->distinct()->count('ta.teacher_id');
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('student_profiles')) {
                $studentQuery = \Illuminate\Support\Facades\DB::table('student_profiles');
                if ($classIds->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasColumn('student_profiles', 'school_class_id')) {
                    $studentQuery->whereIn('school_class_id', $classIds->all());
                }
                $stats['students'] = $studentQuery->count();
            }

            $stats['departments'] = $departments->count();
            $stats['classes'] = $classIds->count();

            if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
                $courseBase = \Illuminate\Support\Facades\DB::table('courses');
                $courseBase = $applyScope($courseBase, 'courses');
                $stats['courses_published'] = (clone $courseBase)->where('status', 'published')->count();
                $stats['courses_draft'] = (clone $courseBase)->where('status', 'draft')->count();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_sets')) {
                $tdBase = \Illuminate\Support\Facades\DB::table('td_sets');
                $tdBase = $applyScope($tdBase, 'td_sets');
                $stats['td_published'] = (clone $tdBase)->where('status', 'published')->count();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_question_threads')) {
                $questionBase = \Illuminate\Support\Facades\DB::table('td_question_threads');
                $questionBase = $applyScope($questionBase, 'td_question_threads');
                $stats['questions_open'] = (clone $questionBase)->where('status', 'open')->count();
            }

            $noteQuery = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes')->where('status', 'open');
            if ($isDivision) {
                $noteQuery->where('teaching_division_id', $responsibility->teaching_division_id);
            } else {
                $noteQuery->where('teaching_department_id', $responsibility->teaching_department_id);
            }
            $stats['notes_open'] = $noteQuery->count();

            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                $teacherQuery = \Illuminate\Support\Facades\DB::table('teacher_assignments as ta')
                    ->join('users as u', 'u.id', '=', 'ta.teacher_id')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'ta.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')
                    ->where('ta.is_active', true)
                    ->select('u.id', 'u.full_name', 'u.name', 'u.username', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('ta.id')
                    ->limit(8);
                $teachers = $applyScope($teacherQuery, 'teacher_assignments', 'ta')->get();
            }

            if ($classIds->isNotEmpty() && \Illuminate\Support\Facades\Schema::hasTable('school_classes')) {
                $classes = \Illuminate\Support\Facades\DB::table('school_classes')->whereIn('id', $classIds->all())->orderBy('name')->limit(8)->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
                $courseQuery = \Illuminate\Support\Facades\DB::table('courses as item')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'item.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')
                    ->select('item.id', 'item.title', 'item.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('item.id')
                    ->limit(8);
                $courses = $applyScope($courseQuery, 'courses', 'item')->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_sets')) {
                $tdQuery = \Illuminate\Support\Facades\DB::table('td_sets as item')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'item.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')
                    ->select('item.id', 'item.title', 'item.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('item.id')
                    ->limit(8);
                $tdSets = $applyScope($tdQuery, 'td_sets', 'item')->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_question_threads')) {
                $questionQuery = \Illuminate\Support\Facades\DB::table('td_question_threads as q')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'q.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id')
                    ->select('q.id', 'q.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('q.id')
                    ->limit(8);
                $questions = $applyScope($questionQuery, 'td_question_threads', 'q')->get();
            }

            $notes = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')
                ->when($isDivision, fn($q) => $q->where('n.teaching_division_id', $responsibility->teaching_division_id))
                ->when(!$isDivision, fn($q) => $q->where('n.teaching_department_id', $responsibility->teaching_department_id))
                ->select('n.*', 'u.full_name', 'u.name', 'u.username')
                ->orderByRaw("CASE n.severity WHEN 'urgent' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                ->orderByDesc('n.id')
                ->limit(8)
                ->get();
        }
    }
@endphp

<style>
    .rz-wrap{display:grid;gap:18px}.rz-top{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:18px;box-shadow:0 16px 40px rgba(15,23,42,.06)}.rz-top h2{margin:0;color:#0f172a;font-size:clamp(1.8rem,4vw,2.8rem)}.rz-top h3{margin:4px 0 8px;color:#075ee8;font-size:1.35rem}.rz-top p{margin:0;color:#64748b}.rz-actions{display:flex;gap:10px;flex-wrap:wrap}.rz-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;border:1px solid #dbeafe;background:#fff;color:#0f172a;font-weight:900;text-decoration:none}.rz-btn--primary{background:#0f2a69;color:#fff;border-color:#0f2a69}.rz-btn--success{background:#16a34a;color:#fff;border-color:#16a34a}.rz-reserved{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:14px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-weight:900;margin-bottom:10px}.rz-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.rz-card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:16px;display:flex;gap:12px;align-items:center;box-shadow:0 12px 28px rgba(15,23,42,.05)}.rz-ico{width:50px;height:50px;border-radius:16px;display:grid;place-items:center;font-size:24px;background:#eef2ff;color:#1d4ed8}.rz-card span{display:block;color:#64748b;font-weight:800}.rz-card strong{font-size:1.9rem;color:#0f172a}.rz-card small{display:block;color:#2563eb;font-weight:900}.rz-panels{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.rz-panel{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:15px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.rz-panel__head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.rz-panel__head h3{margin:0;color:#0f172a}.rz-panel__head a{color:#2563eb;font-weight:900;font-size:13px;text-decoration:none}.rz-list{display:grid;gap:9px}.rz-row{border:1px solid #e5e7eb;background:#f8fafc;border-radius:14px;padding:10px;display:grid;gap:4px}.rz-row__line{display:flex;justify-content:space-between;gap:8px;align-items:center}.rz-row strong{color:#0f172a}.rz-row span,.rz-row small{color:#64748b}.rz-badge{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:900}.rz-badge--success{background:#dcfce7;color:#166534}.rz-badge--warning{background:#fff7ed;color:#c2410c}.rz-badge--danger{background:#ffe4e6;color:#be123c}.rz-badge--neutral{background:#eef2ff;color:#3730a3}.rz-alerts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:16px}.rz-alert{display:flex;gap:12px;align-items:center;border-right:1px solid #e5e7eb}.rz-alert:last-child{border-right:0}.rz-alert b{font-size:1.4rem;color:#0f172a}.rz-empty{padding:16px;border-radius:16px;background:#f8fafc;color:#64748b;text-align:center}.rz-note-form{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:16px}.rz-note-form form{display:grid;grid-template-columns:2fr 1fr auto;gap:10px}.rz-note-form input,.rz-note-form select{border:1px solid #cbd5e1;border-radius:12px;padding:10px}.rz-footer{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#64748b;font-weight:800}@media(max-width:1100px){.rz-grid,.rz-panels{grid-template-columns:1fr 1fr}.rz-alerts{grid-template-columns:1fr 1fr}.rz-top{display:grid}}@media(max-width:720px){.rz-grid,.rz-panels,.rz-alerts{grid-template-columns:1fr}.rz-actions,.rz-btn{width:100%}.rz-alert{border-right:0;border-bottom:1px solid #e5e7eb;padding-bottom:10px}.rz-note-form form{grid-template-columns:1fr}}
</style>

<div class="rz-wrap">
    @if(!$schemaReady)
        <section class="rz-top"><div><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore installées.</p></div></section>
    @elseif(!$responsibility)
        <section class="rz-top"><div><h2>Accès réservé</h2><p>Ce tableau de bord est réservé aux responsables ayant une responsabilité active de type {{ $isDivision ? 'type d’enseignement' : 'département / filière' }}.</p></div><div class="rz-actions"><a class="rz-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="rz-top">
            <div>
                <div class="rz-reserved">🔒 Espace réservé : {{ $responsibility->role_title }}</div>
                <h2>{{ $pageTitle }}</h2>
                <h3>{{ $area->name ?? ($isDivision ? 'Type d’enseignement' : 'Département / filière') }}</h3>
                <p>{{ $isDivision ? 'Vous supervisez les départements, enseignants, cours, TD, questions et relances rattachés à ce type d’enseignement.' : 'Vous suivez les enseignants, classes, cours, TD, questions et retards liés à ce département ou cette filière.' }}</p>
            </div>
            <div class="rz-actions">
                <a class="rz-btn" href="{{ route('teacher.dashboard') }}">← Retour</a>
                @if(\Illuminate\Support\Facades\Route::has('secretariat.dashboard'))<a class="rz-btn rz-btn--primary" href="{{ route('secretariat.dashboard') }}">TB Secrétaire</a>@endif
                <a class="rz-btn rz-btn--success" href="#note-suivi">+ Créer une note</a>
            </div>
        </section>

        <section class="rz-grid">
            <article class="rz-card"><div class="rz-ico">👥</div><div><span>Enseignants</span><strong>{{ $stats['teachers'] }}</strong><small>Voir la liste</small></div></article>
            <article class="rz-card"><div class="rz-ico">🎓</div><div><span>Élèves concernés</span><strong>{{ $stats['students'] }}</strong><small>Voir le détail</small></div></article>
            <article class="rz-card"><div class="rz-ico">{{ $isDivision ? '🏛️' : '🏫' }}</div><div><span>{{ $isDivision ? 'Départements / filières' : 'Classes liées' }}</span><strong>{{ $isDivision ? $stats['departments'] : $stats['classes'] }}</strong><small>Voir le détail</small></div></article>
            <article class="rz-card"><div class="rz-ico">📘</div><div><span>Cours publiés</span><strong>{{ $stats['courses_published'] }}</strong><small>Voir les cours</small></div></article>
            <article class="rz-card"><div class="rz-ico">📝</div><div><span>Cours brouillons</span><strong>{{ $stats['courses_draft'] }}</strong><small>À finaliser</small></div></article>
            <article class="rz-card"><div class="rz-ico">📄</div><div><span>TD publiés</span><strong>{{ $stats['td_published'] }}</strong><small>Voir les TD</small></div></article>
            <article class="rz-card"><div class="rz-ico">❓</div><div><span>Questions ouvertes</span><strong>{{ $stats['questions_open'] }}</strong><small>À suivre</small></div></article>
            <article class="rz-card"><div class="rz-ico">📋</div><div><span>Notes ouvertes</span><strong>{{ $stats['notes_open'] }}</strong><small>Relances</small></div></article>
        </section>

        <div class="rz-panels">
            <section class="rz-panel"><div class="rz-panel__head"><h3>{{ $isDivision ? 'Enseignants à suivre' : 'Enseignants du département' }}</h3><a href="#">Voir tout</a></div><div class="rz-list">@forelse($teachers as $teacher)<div class="rz-row"><div class="rz-row__line"><strong>{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</strong><span class="rz-badge rz-badge--success">Actif</span></div><span>{{ $teacher->class_name ?? '-' }} · {{ $teacher->subject_name ?? '-' }}</span></div>@empty<div class="rz-empty">Aucun enseignant trouvé.</div>@endforelse</div></section>
            <section class="rz-panel"><div class="rz-panel__head"><h3>{{ $isDivision ? 'Départements / filières concernés' : 'Classes concernées' }}</h3><a href="#">Voir tout</a></div><div class="rz-list">@if($isDivision) @forelse($departments as $department)<div class="rz-row"><div class="rz-row__line"><strong>{{ $department->name }}</strong><span class="rz-badge rz-badge--success">Actif</span></div><span>{{ $department->code ?: 'Sans code' }}</span></div>@empty<div class="rz-empty">Aucun département.</div>@endforelse @else @forelse($classes as $class)<div class="rz-row"><div class="rz-row__line"><strong>{{ $class->name }}</strong><span class="rz-badge rz-badge--neutral">Classe</span></div><span>{{ $class->level ?? $class->niveau ?? 'Niveau non précisé' }}</span></div>@empty<div class="rz-empty">Aucune classe liée directement.</div>@endforelse @endif</div></section>
            <section class="rz-panel"><div class="rz-panel__head"><h3>Cours récents</h3><a href="#">Voir tout</a></div><div class="rz-list">@forelse($courses as $course)<div class="rz-row"><div class="rz-row__line"><strong>{{ $course->title }}</strong><span class="rz-badge {{ $statusClass($course->status) }}">{{ $labelStatus($course->status) }}</span></div><span>{{ $course->class_name ?? '-' }} · {{ $course->subject_name ?? '-' }}</span></div>@empty<div class="rz-empty">Aucun cours.</div>@endforelse</div></section>
            <section class="rz-panel"><div class="rz-panel__head"><h3>TD récents</h3><a href="#">Voir tout</a></div><div class="rz-list">@forelse($tdSets as $td)<div class="rz-row"><div class="rz-row__line"><strong>{{ $td->title }}</strong><span class="rz-badge {{ $statusClass($td->status) }}">{{ $labelStatus($td->status) }}</span></div><span>{{ $td->class_name ?? '-' }} · {{ $td->subject_name ?? '-' }}</span></div>@empty<div class="rz-empty">Aucun TD.</div>@endforelse</div></section>
            <section class="rz-panel"><div class="rz-panel__head"><h3>Questions élèves ouvertes</h3><a href="#">Voir tout</a></div><div class="rz-list">@forelse($questions as $question)<div class="rz-row"><div class="rz-row__line"><strong>{{ $question->subject_name ?: 'Question élève' }}</strong><span class="rz-badge {{ $statusClass($question->status) }}">{{ $labelStatus($question->status) }}</span></div><span>{{ $question->class_name ?? '-' }}</span></div>@empty<div class="rz-empty">Aucune question ouverte.</div>@endforelse</div></section>
            <section class="rz-panel"><div class="rz-panel__head"><h3>Notes / relances</h3><a href="#">Voir tout</a></div><div class="rz-list">@forelse($notes as $note)<div class="rz-row"><div class="rz-row__line"><strong>{{ $note->title }}</strong><span class="rz-badge {{ $statusClass($note->severity) }}">{{ $labelStatus($note->severity) }}</span></div><span>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone suivie')) }}</span><small>{{ $labelStatus($note->status) }}</small></div>@empty<div class="rz-empty">Aucune note.</div>@endforelse</div></section>
        </div>

        <section class="rz-alerts">
            <div class="rz-alert"><div class="rz-ico">⏰</div><div><b>{{ $stats['courses_draft'] }}</b><br><span>cours à finaliser</span></div></div>
            <div class="rz-alert"><div class="rz-ico">📄</div><div><b>{{ max(0, $stats['teachers'] - $stats['td_published']) }}</b><br><span>points TD à surveiller</span></div></div>
            <div class="rz-alert"><div class="rz-ico">❓</div><div><b>{{ $stats['questions_open'] }}</b><br><span>questions sans réponse</span></div></div>
            <div class="rz-alert"><div class="rz-ico">📋</div><div><b>{{ $stats['notes_open'] }}</b><br><span>notes de suivi ouvertes</span></div></div>
        </section>

        <section class="rz-note-form" id="note-suivi">
            <h3>Créer une note de suivi rapide</h3>
            <form method="POST" action="{{ route('supervision.notes.store') }}">
                @csrf
                <input type="hidden" name="responsibility_id" value="{{ $responsibility->id }}">
                <input name="title" required placeholder="Exemple : TD en attente de publication">
                <select name="severity"><option value="info">Info</option><option value="warning">Attention</option><option value="urgent">Urgent</option></select>
                <button type="submit" class="rz-btn rz-btn--success">Enregistrer</button>
            </form>
        </section>

        <div class="rz-footer"><span>Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</span><span>Une solution Cabrel Tech.</span></div>
    @endif
</div>
@endsection
