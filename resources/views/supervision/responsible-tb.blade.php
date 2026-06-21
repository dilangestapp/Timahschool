@extends('layouts.teacher')

@section('title', 'TB responsable pédagogique')
@section('page_title', 'TB responsable pédagogique')
@section('page_subtitle', 'Tableau de bord réservé aux enseignants nommés responsables pédagogiques.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');
    $responsibilities = collect();
    $activeResponsibility = null;
    $areaTitle = 'Supervision pédagogique';
    $teachers = collect();
    $courses = collect();
    $tdSets = collect();
    $questions = collect();
    $notes = collect();
    $stats = ['teachers' => 0, 'courses_published' => 0, 'td_published' => 0, 'questions_open' => 0];

    if ($schemaReady) {
        $responsibilities = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities as pr')
            ->leftJoin('teaching_divisions as d', 'd.id', '=', 'pr.teaching_division_id')
            ->leftJoin('teaching_departments as dep', 'dep.id', '=', 'pr.teaching_department_id')
            ->where('pr.user_id', auth()->id())
            ->where('pr.is_active', true)
            ->select('pr.*', 'd.name as division_name', 'dep.name as department_name')
            ->get();

        if ($responsibilities->isEmpty()) {
            abort(403, 'Aucune responsabilité pédagogique active ne vous est attribuée.');
        }

        $activeResponsibility = $responsibilities->firstWhere('id', (int) request('responsibility')) ?: $responsibilities->first();
        $areaTitle = $activeResponsibility->department_name ?: ($activeResponsibility->division_name ?: 'Plateforme entière');

        if (\Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') && \Illuminate\Support\Facades\Schema::hasTable('users')) {
            $teachers = \Illuminate\Support\Facades\DB::table('teacher_assignments as ta')
                ->join('users as u', 'u.id', '=', 'ta.teacher_id')
                ->leftJoin('school_classes as c', 'c.id', '=', 'ta.school_class_id')
                ->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')
                ->where('ta.is_active', true)
                ->select('u.id', 'u.full_name', 'u.name', 'u.username', 'c.name as class_name', 's.name as subject_name')
                ->orderByDesc('ta.id')
                ->limit(12)
                ->get();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
            $courses = \Illuminate\Support\Facades\DB::table('courses as item')
                ->leftJoin('school_classes as c', 'c.id', '=', 'item.school_class_id')
                ->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')
                ->select('item.id', 'item.title', 'item.status', 'c.name as class_name', 's.name as subject_name')
                ->orderByDesc('item.id')
                ->limit(10)
                ->get();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('td_sets')) {
            $tdSets = \Illuminate\Support\Facades\DB::table('td_sets as item')
                ->leftJoin('school_classes as c', 'c.id', '=', 'item.school_class_id')
                ->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')
                ->select('item.id', 'item.title', 'item.status', 'c.name as class_name', 's.name as subject_name')
                ->orderByDesc('item.id')
                ->limit(10)
                ->get();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('td_question_threads')) {
            $questions = \Illuminate\Support\Facades\DB::table('td_question_threads as q')
                ->leftJoin('school_classes as c', 'c.id', '=', 'q.school_class_id')
                ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id')
                ->select('q.id', 'q.status', 'c.name as class_name', 's.name as subject_name')
                ->orderByDesc('q.id')
                ->limit(10)
                ->get();
        }

        $notes = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')
            ->where('n.responsibility_id', $activeResponsibility->id)
            ->select('n.*', 'u.full_name', 'u.name', 'u.username')
            ->orderByDesc('n.id')
            ->limit(12)
            ->get();

        $stats = [
            'teachers' => $teachers->pluck('id')->unique()->count(),
            'courses_published' => \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'published')->count() : 0,
            'td_published' => \Illuminate\Support\Facades\Schema::hasTable('td_sets') ? \Illuminate\Support\Facades\DB::table('td_sets')->where('status', 'published')->count() : 0,
            'questions_open' => \Illuminate\Support\Facades\Schema::hasTable('td_question_threads') ? \Illuminate\Support\Facades\DB::table('td_question_threads')->where('status', 'open')->count() : 0,
        ];
    }
@endphp

<style>
    .resp-tb{display:grid;gap:18px}.resp-hero{border-radius:28px;padding:22px;color:#fff;background:linear-gradient(135deg,#0f172a,#1d4ed8,#0f766e);box-shadow:0 22px 54px rgba(15,23,42,.18)}.resp-hero h2{font-size:clamp(1.8rem,5vw,3rem);margin:8px 0}.resp-hero p{color:#dbeafe}.resp-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.resp-card{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:16px}.resp-card span{display:block;color:#64748b;font-weight:800}.resp-card strong{display:block;font-size:2rem}.resp-panels{display:grid;grid-template-columns:1fr 1fr;gap:16px}.resp-panel{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:16px}.resp-list{display:grid;gap:10px}.resp-row{border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc}.resp-row strong{display:block}.resp-row span,.resp-row small{color:#64748b}.resp-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.resp-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;background:#fff;color:#0f172a;font-weight:900;text-decoration:none}.resp-btn--ghost{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.28)}.resp-empty{padding:18px;border-radius:18px;background:#f8fafc;color:#64748b;text-align:center}@media(max-width:900px){.resp-grid,.resp-panels{grid-template-columns:1fr}} 
</style>

<div class="resp-tb">
    @if(!$schemaReady)
        <section class="resp-hero">
            <h2>Migration nécessaire</h2>
            <p>Les tables de supervision ne sont pas encore installées sur le serveur Contabo. Déploie puis lance les migrations Laravel.</p>
            <div class="resp-actions"><a href="{{ route('teacher.dashboard') }}" class="resp-btn">Retour enseignant</a></div>
        </section>
    @else
        <section class="resp-hero">
            <span>Responsabilité pédagogique</span>
            <h2>{{ $areaTitle }}</h2>
            <p>Ce TB permet de suivre les enseignants, les cours, les TD, les questions ouvertes et les notes de suivi, sans donner un accès admin complet.</p>
            <div class="resp-actions"><a href="{{ route('teacher.dashboard') }}" class="resp-btn">Retour enseignant</a></div>
        </section>

        @if($responsibilities->count() > 1)
            <section class="resp-panel">
                <h3>Changer de responsabilité</h3>
                <form method="GET" action="{{ route('supervision.tb') }}">
                    <select name="responsibility" onchange="this.form.submit()">
                        @foreach($responsibilities as $responsibility)
                            <option value="{{ $responsibility->id }}" @selected($activeResponsibility && $activeResponsibility->id === $responsibility->id)>{{ $responsibility->role_title }} — {{ $responsibility->department_name ?: ($responsibility->division_name ?: 'Plateforme entière') }}</option>
                        @endforeach
                    </select>
                </form>
            </section>
        @endif

        <section class="resp-grid">
            <article class="resp-card"><span>Enseignants</span><strong>{{ $stats['teachers'] }}</strong></article>
            <article class="resp-card"><span>Cours publiés</span><strong>{{ $stats['courses_published'] }}</strong></article>
            <article class="resp-card"><span>TD publiés</span><strong>{{ $stats['td_published'] }}</strong></article>
            <article class="resp-card"><span>Questions ouvertes</span><strong>{{ $stats['questions_open'] }}</strong></article>
        </section>

        <div class="resp-panels">
            <section class="resp-panel"><h3>Enseignants concernés</h3><div class="resp-list">@forelse($teachers as $teacher)<div class="resp-row"><strong>{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</strong><span>{{ $teacher->class_name ?? '-' }} · {{ $teacher->subject_name ?? '-' }}</span></div>@empty<div class="resp-empty">Aucun enseignant trouvé.</div>@endforelse</div></section>
            <section class="resp-panel"><h3>Cours récents</h3><div class="resp-list">@forelse($courses as $course)<div class="resp-row"><strong>{{ $course->title }}</strong><span>{{ $course->class_name ?? '-' }} · {{ $course->subject_name ?? '-' }}</span><small>{{ $course->status }}</small></div>@empty<div class="resp-empty">Aucun cours trouvé.</div>@endforelse</div></section>
            <section class="resp-panel"><h3>TD récents</h3><div class="resp-list">@forelse($tdSets as $td)<div class="resp-row"><strong>{{ $td->title }}</strong><span>{{ $td->class_name ?? '-' }} · {{ $td->subject_name ?? '-' }}</span><small>{{ $td->status }}</small></div>@empty<div class="resp-empty">Aucun TD trouvé.</div>@endforelse</div></section>
            <section class="resp-panel"><h3>Questions ouvertes</h3><div class="resp-list">@forelse($questions as $question)<div class="resp-row"><strong>{{ $question->subject_name ?: 'Question élève' }}</strong><span>{{ $question->class_name ?? '-' }}</span><small>{{ $question->status }}</small></div>@empty<div class="resp-empty">Aucune question ouverte.</div>@endforelse</div></section>
        </div>

        <section class="resp-panel"><h3>Notes de suivi</h3><div class="resp-list">@forelse($notes as $note)<div class="resp-row"><strong>{{ $note->title }}</strong><span>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone générale')) }}</span><small>{{ $note->severity }} · {{ $note->status }}</small></div>@empty<div class="resp-empty">Aucune note de suivi.</div>@endforelse</div></section>
    @endif
</div>
@endsection
