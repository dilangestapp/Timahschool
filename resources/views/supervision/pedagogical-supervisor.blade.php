@extends('layouts.teacher')

@section('title', 'TB Superviseur pédagogique')
@section('page_title', 'TB Superviseur pédagogique')
@section('page_subtitle', 'Suivi quotidien des enseignants, cours, TD, questions sans réponse, alertes et relances pédagogiques.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');
    $authorized = false;
    $responsibility = null;
    $stats = [
        'teachers' => 0,
        'courses_week' => 0,
        'td_week' => 0,
        'draft_courses' => 0,
        'open_questions' => 0,
        'open_notes' => 0,
        'urgent_notes' => 0,
        'published_td' => 0,
    ];
    $teachers = collect();
    $courses = collect();
    $tdSets = collect();
    $questions = collect();
    $notes = collect();

    if ($schemaReady) {
        $responsibility = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('role_title', 'like', '%Superviseur pédagogique%')
            ->first();
        $authorized = (bool) $responsibility;

        if ($authorized) {
            $weekStart = now()->startOfWeek();
            $stats['teachers'] = \Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') ? \Illuminate\Support\Facades\DB::table('teacher_assignments')->where('is_active', true)->distinct()->count('teacher_id') : 0;
            $stats['courses_week'] = \Illuminate\Support\Facades\Schema::hasTable('courses') && \Illuminate\Support\Facades\Schema::hasColumn('courses', 'created_at') ? \Illuminate\Support\Facades\DB::table('courses')->where('created_at', '>=', $weekStart)->count() : 0;
            $stats['td_week'] = \Illuminate\Support\Facades\Schema::hasTable('td_sets') && \Illuminate\Support\Facades\Schema::hasColumn('td_sets', 'created_at') ? \Illuminate\Support\Facades\DB::table('td_sets')->where('created_at', '>=', $weekStart)->count() : 0;
            $stats['draft_courses'] = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'draft')->count() : 0;
            $stats['open_questions'] = \Illuminate\Support\Facades\Schema::hasTable('td_question_threads') ? \Illuminate\Support\Facades\DB::table('td_question_threads')->where('status', 'open')->count() : 0;
            $stats['open_notes'] = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes')->where('status', 'open')->count();
            $stats['urgent_notes'] = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes')->where('status', 'open')->where('severity', 'urgent')->count();
            $stats['published_td'] = \Illuminate\Support\Facades\Schema::hasTable('td_sets') ? \Illuminate\Support\Facades\DB::table('td_sets')->where('status', 'published')->count() : 0;

            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                $teachers = \Illuminate\Support\Facades\DB::table('teacher_assignments as ta')
                    ->join('users as u', 'u.id', '=', 'ta.teacher_id')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'ta.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')
                    ->where('ta.is_active', true)
                    ->select('u.id', 'u.full_name', 'u.name', 'u.username', 'u.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('ta.id')
                    ->limit(10)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
                $courses = \Illuminate\Support\Facades\DB::table('courses as c')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'c.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'c.subject_id')
                    ->select('c.id', 'c.title', 'c.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByRaw("CASE c.status WHEN 'draft' THEN 1 WHEN 'pending' THEN 2 ELSE 3 END")
                    ->orderByDesc('c.id')
                    ->limit(10)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_sets')) {
                $tdSets = \Illuminate\Support\Facades\DB::table('td_sets as td')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'td.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'td.subject_id')
                    ->select('td.id', 'td.title', 'td.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByRaw("CASE td.status WHEN 'draft' THEN 1 WHEN 'pending' THEN 2 ELSE 3 END")
                    ->orderByDesc('td.id')
                    ->limit(10)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_question_threads')) {
                $questions = \Illuminate\Support\Facades\DB::table('td_question_threads as q')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'q.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id')
                    ->select('q.id', 'q.status', 'cl.name as class_name', 's.name as subject_name')
                    ->where('q.status', 'open')
                    ->orderByDesc('q.id')
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

    $badgeClass = fn($status) => match ($status) {
        'published', 'active', 'open' => 'sup-badge--success',
        'draft', 'pending', 'warning' => 'sup-badge--warning',
        'urgent' => 'sup-badge--danger',
        default => 'sup-badge--neutral',
    };
    $label = fn($status) => match ($status) {
        'published' => 'Publié', 'draft' => 'Brouillon', 'pending' => 'En attente', 'open' => 'Ouvert', 'active' => 'Actif', 'urgent' => 'Urgent', 'warning' => 'Attention', default => $status ?: '—',
    };
@endphp

<style>
    .sup-wrap{display:grid;gap:18px}.sup-hero{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;background:linear-gradient(135deg,#020617,#0f766e,#1d4ed8);color:#fff;border-radius:26px;padding:22px;box-shadow:0 22px 55px rgba(15,23,42,.22)}.sup-hero h2{margin:6px 0;font-size:clamp(2rem,5vw,3.2rem)}.sup-hero p{color:#dbeafe;max-width:880px}.sup-lock{display:inline-flex;padding:9px 13px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);font-weight:900}.sup-actions{display:flex;gap:10px;flex-wrap:wrap}.sup-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;background:#fff;color:#0f172a;text-decoration:none;font-weight:900}.sup-btn--green{background:#16a34a;color:#fff}.sup-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.sup-card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:15px;display:flex;align-items:center;gap:12px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.sup-ico{width:50px;height:50px;border-radius:16px;display:grid;place-items:center;background:#ecfeff;color:#0891b2;font-size:24px}.sup-card span{display:block;color:#64748b;font-weight:800}.sup-card strong{font-size:1.85rem;color:#0f172a}.sup-card small{display:block;color:#0f766e;font-weight:900}.sup-panels{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.sup-panel{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:15px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.sup-panel h3{margin-top:0}.sup-list{display:grid;gap:9px}.sup-row{border:1px solid #e5e7eb;background:#f8fafc;border-radius:14px;padding:10px;display:grid;gap:4px}.sup-row__line{display:flex;align-items:center;justify-content:space-between;gap:8px}.sup-row strong{color:#0f172a}.sup-row span,.sup-row small{color:#64748b}.sup-badge{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:900}.sup-badge--success{background:#dcfce7;color:#166534}.sup-badge--warning{background:#fff7ed;color:#c2410c}.sup-badge--danger{background:#ffe4e6;color:#be123c}.sup-badge--neutral{background:#eef2ff;color:#3730a3}.sup-form{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:16px}.sup-form form{display:grid;grid-template-columns:2fr 1fr auto;gap:10px}.sup-form input,.sup-form select{border:1px solid #cbd5e1;border-radius:12px;padding:10px}.sup-empty{padding:16px;border-radius:16px;background:#f8fafc;color:#64748b;text-align:center}.sup-footer{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#64748b;font-weight:800}@media(max-width:1100px){.sup-grid,.sup-panels{grid-template-columns:1fr 1fr}.sup-hero{display:grid}}@media(max-width:720px){.sup-grid,.sup-panels{grid-template-columns:1fr}.sup-actions,.sup-btn{width:100%}.sup-form form{grid-template-columns:1fr}}
</style>

<div class="sup-wrap">
    @if(!$schemaReady)
        <section class="sup-hero"><div><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore installées.</p></div></section>
    @elseif(!$authorized)
        <section class="sup-hero"><div><h2>Accès réservé</h2><p>Ce tableau de bord est réservé au compte nommé comme Superviseur pédagogique.</p></div><div class="sup-actions"><a class="sup-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="sup-hero"><div><span class="sup-lock">📡 Espace réservé : Superviseur pédagogique</span><h2>Supervision quotidienne</h2><p>Le Superviseur pédagogique surveille l’activité réelle : enseignants actifs, cours à finaliser, TD en retard, questions sans réponse et alertes à transmettre au Secrétariat général.</p></div><div class="sup-actions"><a class="sup-btn" href="{{ route('teacher.dashboard') }}">← Retour enseignant</a><a class="sup-btn sup-btn--green" href="#alerte-rapide">+ Alerte</a></div></section>
        <section class="sup-grid">
            <article class="sup-card"><div class="sup-ico">👥</div><div><span>Enseignants actifs</span><strong>{{ $stats['teachers'] }}</strong><small>Suivis</small></div></article>
            <article class="sup-card"><div class="sup-ico">📘</div><div><span>Cours cette semaine</span><strong>{{ $stats['courses_week'] }}</strong><small>Nouveaux</small></div></article>
            <article class="sup-card"><div class="sup-ico">📄</div><div><span>TD cette semaine</span><strong>{{ $stats['td_week'] }}</strong><small>Nouveaux</small></div></article>
            <article class="sup-card"><div class="sup-ico">❓</div><div><span>Questions ouvertes</span><strong>{{ $stats['open_questions'] }}</strong><small>À traiter</small></div></article>
            <article class="sup-card"><div class="sup-ico">📝</div><div><span>Cours brouillons</span><strong>{{ $stats['draft_courses'] }}</strong><small>À finaliser</small></div></article>
            <article class="sup-card"><div class="sup-ico">🧪</div><div><span>TD publiés</span><strong>{{ $stats['published_td'] }}</strong><small>Disponibles</small></div></article>
            <article class="sup-card"><div class="sup-ico">🚨</div><div><span>Notes urgentes</span><strong>{{ $stats['urgent_notes'] }}</strong><small>Priorité</small></div></article>
            <article class="sup-card"><div class="sup-ico">📋</div><div><span>Notes ouvertes</span><strong>{{ $stats['open_notes'] }}</strong><small>Suivi</small></div></article>
        </section>
        <div class="sup-panels">
            <section class="sup-panel"><h3>Enseignants à surveiller</h3><div class="sup-list">@forelse($teachers as $teacher)<div class="sup-row"><div class="sup-row__line"><strong>{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</strong><span class="sup-badge {{ $badgeClass($teacher->status ?: 'active') }}">{{ $label($teacher->status ?: 'active') }}</span></div><span>{{ $teacher->class_name ?? '-' }} · {{ $teacher->subject_name ?? '-' }}</span></div>@empty<div class="sup-empty">Aucun enseignant à afficher.</div>@endforelse</div></section>
            <section class="sup-panel"><h3>Cours en suivi</h3><div class="sup-list">@forelse($courses as $course)<div class="sup-row"><div class="sup-row__line"><strong>{{ $course->title }}</strong><span class="sup-badge {{ $badgeClass($course->status) }}">{{ $label($course->status) }}</span></div><span>{{ $course->class_name ?? '-' }} · {{ $course->subject_name ?? '-' }}</span></div>@empty<div class="sup-empty">Aucun cours.</div>@endforelse</div></section>
            <section class="sup-panel"><h3>TD en suivi</h3><div class="sup-list">@forelse($tdSets as $td)<div class="sup-row"><div class="sup-row__line"><strong>{{ $td->title }}</strong><span class="sup-badge {{ $badgeClass($td->status) }}">{{ $label($td->status) }}</span></div><span>{{ $td->class_name ?? '-' }} · {{ $td->subject_name ?? '-' }}</span></div>@empty<div class="sup-empty">Aucun TD.</div>@endforelse</div></section>
            <section class="sup-panel"><h3>Questions sans réponse</h3><div class="sup-list">@forelse($questions as $question)<div class="sup-row"><div class="sup-row__line"><strong>{{ $question->subject_name ?: 'Question élève' }}</strong><span class="sup-badge {{ $badgeClass($question->status) }}">{{ $label($question->status) }}</span></div><span>{{ $question->class_name ?? '-' }}</span></div>@empty<div class="sup-empty">Aucune question ouverte.</div>@endforelse</div></section>
            <section class="sup-panel"><h3>Notes / alertes</h3><div class="sup-list">@forelse($notes as $note)<div class="sup-row"><div class="sup-row__line"><strong>{{ $note->title }}</strong><span class="sup-badge {{ $badgeClass($note->severity) }}">{{ $label($note->severity) }}</span></div><span>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone générale')) }}</span><small>{{ $label($note->status) }}</small></div>@empty<div class="sup-empty">Aucune note.</div>@endforelse</div></section>
            <section class="sup-panel"><h3>Points à contrôler</h3><div class="sup-list"><div class="sup-row"><strong>Retards cours</strong><span>Cours brouillons ou non finalisés.</span></div><div class="sup-row"><strong>Retards TD</strong><span>TD manquants ou non publiés.</span></div><div class="sup-row"><strong>Questions élèves</strong><span>Questions sans réponse à relancer.</span></div><div class="sup-row"><strong>Notes urgentes</strong><span>Alertes à transmettre au Secrétariat général.</span></div></div></section>
        </div>
        <section class="sup-form" id="alerte-rapide"><h3>Créer une alerte rapide</h3><form method="POST" action="{{ route('supervision.notes.store') }}">@csrf<input type="hidden" name="responsibility_id" value="{{ $responsibility->id }}"><input name="title" required placeholder="Exemple : Questions sans réponse en terminale"><select name="severity"><option value="info">Info</option><option value="warning">Attention</option><option value="urgent">Urgent</option></select><button type="submit" class="sup-btn sup-btn--green">Enregistrer</button></form></section>
        <div class="sup-footer"><span>Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</span><span>Une solution Cabrel Tech.</span></div>
    @endif
</div>
@endsection
