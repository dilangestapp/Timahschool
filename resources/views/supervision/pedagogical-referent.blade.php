@extends('layouts.teacher')

@section('title', 'TB Référent pédagogique')
@section('page_title', 'TB Référent pédagogique')
@section('page_subtitle', 'Contrôle qualité des cours, TD, corrigés, contenus pédagogiques et remarques à traiter.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities')
        && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');

    $responsibility = null;
    $authorized = false;
    $stats = ['courses_to_review' => 0, 'td_to_review' => 0, 'published_courses' => 0, 'published_td' => 0, 'open_notes' => 0, 'urgent_notes' => 0, 'questions_open' => 0, 'teachers' => 0];
    $courses = collect();
    $tdSets = collect();
    $questions = collect();
    $notes = collect();
    $teachers = collect();

    if ($schemaReady) {
        $responsibility = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('role_title', 'like', '%Référent pédagogique%')
            ->first();
        $authorized = (bool) $responsibility;

        if ($authorized) {
            $stats['courses_to_review'] = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->whereIn('status', ['draft', 'pending', 'review'])->count() : 0;
            $stats['td_to_review'] = \Illuminate\Support\Facades\Schema::hasTable('td_sets') ? \Illuminate\Support\Facades\DB::table('td_sets')->whereIn('status', ['draft', 'pending', 'review', 'programmed'])->count() : 0;
            $stats['published_courses'] = \Illuminate\Support\Facades\Schema::hasTable('courses') ? \Illuminate\Support\Facades\DB::table('courses')->where('status', 'published')->count() : 0;
            $stats['published_td'] = \Illuminate\Support\Facades\Schema::hasTable('td_sets') ? \Illuminate\Support\Facades\DB::table('td_sets')->where('status', 'published')->count() : 0;
            $stats['open_notes'] = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes')->where('status', 'open')->count();
            $stats['urgent_notes'] = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes')->where('status', 'open')->where('severity', 'urgent')->count();
            $stats['questions_open'] = \Illuminate\Support\Facades\Schema::hasTable('td_question_threads') ? \Illuminate\Support\Facades\DB::table('td_question_threads')->where('status', 'open')->count() : 0;
            $stats['teachers'] = \Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') ? \Illuminate\Support\Facades\DB::table('teacher_assignments')->where('is_active', true)->distinct()->count('teacher_id') : 0;

            if (\Illuminate\Support\Facades\Schema::hasTable('courses')) {
                $courses = \Illuminate\Support\Facades\DB::table('courses as c')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'c.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'c.subject_id')
                    ->select('c.id', 'c.title', 'c.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByRaw("CASE c.status WHEN 'draft' THEN 1 WHEN 'pending' THEN 2 WHEN 'review' THEN 3 ELSE 4 END")
                    ->orderByDesc('c.id')
                    ->limit(10)
                    ->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('td_sets')) {
                $tdSets = \Illuminate\Support\Facades\DB::table('td_sets as td')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'td.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'td.subject_id')
                    ->select('td.id', 'td.title', 'td.status', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByRaw("CASE td.status WHEN 'draft' THEN 1 WHEN 'pending' THEN 2 WHEN 'review' THEN 3 ELSE 4 END")
                    ->orderByDesc('td.id')
                    ->limit(10)
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

            $notes = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')
                ->select('n.*', 'u.full_name', 'u.name', 'u.username')
                ->orderByRaw("CASE n.severity WHEN 'urgent' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                ->orderByDesc('n.id')
                ->limit(10)
                ->get();

            if (\Illuminate\Support\Facades\Schema::hasTable('teacher_assignments') && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                $teachers = \Illuminate\Support\Facades\DB::table('teacher_assignments as ta')
                    ->join('users as u', 'u.id', '=', 'ta.teacher_id')
                    ->leftJoin('school_classes as cl', 'cl.id', '=', 'ta.school_class_id')
                    ->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')
                    ->where('ta.is_active', true)
                    ->select('u.id', 'u.full_name', 'u.name', 'u.username', 'cl.name as class_name', 's.name as subject_name')
                    ->orderByDesc('ta.id')
                    ->limit(8)
                    ->get();
            }
        }
    }

    $statusClass = function ($status) {
        return match ($status) {
            'published', 'active', 'open' => 'ref-badge--success',
            'draft', 'pending', 'review', 'programmed' => 'ref-badge--warning',
            'urgent' => 'ref-badge--danger',
            default => 'ref-badge--neutral',
        };
    };

    $statusLabel = function ($status) {
        return match ($status) {
            'published' => 'Publié',
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'review' => 'À relire',
            'programmed' => 'Programmé',
            'open' => 'Ouverte',
            'urgent' => 'Urgent',
            'warning' => 'Attention',
            'info' => 'Info',
            default => $status ?: '—',
        };
    };
@endphp

<style>
    .ref-wrap{display:grid;gap:18px}.ref-hero{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;background:linear-gradient(135deg,#0f172a,#1d4ed8,#7c3aed);border-radius:26px;padding:22px;color:#fff;box-shadow:0 22px 55px rgba(15,23,42,.22)}.ref-hero h2{margin:6px 0;font-size:clamp(2rem,5vw,3.2rem)}.ref-hero p{color:#dbeafe;max-width:850px}.ref-lock{display:inline-flex;padding:9px 13px;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);font-weight:900}.ref-actions{display:flex;gap:10px;flex-wrap:wrap}.ref-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;background:#fff;color:#0f172a;text-decoration:none;font-weight:900}.ref-btn--green{background:#16a34a;color:#fff}.ref-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.ref-card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:15px;display:flex;align-items:center;gap:12px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.ref-ico{width:50px;height:50px;border-radius:16px;display:grid;place-items:center;background:#eef2ff;color:#1d4ed8;font-size:24px}.ref-card span{display:block;color:#64748b;font-weight:800}.ref-card strong{font-size:1.85rem;color:#0f172a}.ref-card small{display:block;color:#2563eb;font-weight:900}.ref-panels{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.ref-panel{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:15px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.ref-panel__head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.ref-panel__head h3{margin:0}.ref-panel__head a{font-size:13px;color:#2563eb;font-weight:900;text-decoration:none}.ref-list{display:grid;gap:9px}.ref-row{border:1px solid #e5e7eb;background:#f8fafc;border-radius:14px;padding:10px;display:grid;gap:4px}.ref-row__line{display:flex;align-items:center;justify-content:space-between;gap:8px}.ref-row strong{color:#0f172a}.ref-row span,.ref-row small{color:#64748b}.ref-badge{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:900}.ref-badge--success{background:#dcfce7;color:#166534}.ref-badge--warning{background:#fff7ed;color:#c2410c}.ref-badge--danger{background:#ffe4e6;color:#be123c}.ref-badge--neutral{background:#eef2ff;color:#3730a3}.ref-form{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:16px}.ref-form form{display:grid;grid-template-columns:2fr 1fr auto;gap:10px}.ref-form input,.ref-form select{border:1px solid #cbd5e1;border-radius:12px;padding:10px}.ref-empty{padding:16px;border-radius:16px;background:#f8fafc;color:#64748b;text-align:center}.ref-footer{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#64748b;font-weight:800}@media(max-width:1100px){.ref-grid,.ref-panels{grid-template-columns:1fr 1fr}.ref-hero{display:grid}}@media(max-width:720px){.ref-grid,.ref-panels{grid-template-columns:1fr}.ref-actions,.ref-btn{width:100%}.ref-form form{grid-template-columns:1fr}}
</style>

<div class="ref-wrap">
    @if(!$schemaReady)
        <section class="ref-hero"><div><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore installées.</p></div></section>
    @elseif(!$authorized)
        <section class="ref-hero"><div><h2>Accès réservé</h2><p>Ce tableau de bord est réservé au compte nommé comme Référent pédagogique.</p></div><div class="ref-actions"><a class="ref-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="ref-hero">
            <div>
                <span class="ref-lock">🔎 Espace réservé : Référent pédagogique</span>
                <h2>Contrôle qualité pédagogique</h2>
                <p>Le Référent pédagogique vérifie la qualité des cours, TD, corrigés et contenus publiés ou en attente. Il signale les contenus faibles, demande des améliorations et veille à la cohérence pédagogique.</p>
            </div>
            <div class="ref-actions">
                <a class="ref-btn" href="{{ route('teacher.dashboard') }}">← Retour enseignant</a>
                <a class="ref-btn ref-btn--green" href="#note-qualite">+ Note qualité</a>
            </div>
        </section>

        <section class="ref-grid">
            <article class="ref-card"><div class="ref-ico">📘</div><div><span>Cours à vérifier</span><strong>{{ $stats['courses_to_review'] }}</strong><small>À relire</small></div></article>
            <article class="ref-card"><div class="ref-ico">📄</div><div><span>TD à vérifier</span><strong>{{ $stats['td_to_review'] }}</strong><small>À contrôler</small></div></article>
            <article class="ref-card"><div class="ref-ico">✅</div><div><span>Cours publiés</span><strong>{{ $stats['published_courses'] }}</strong><small>Disponibles</small></div></article>
            <article class="ref-card"><div class="ref-ico">🧪</div><div><span>TD publiés</span><strong>{{ $stats['published_td'] }}</strong><small>Actifs</small></div></article>
            <article class="ref-card"><div class="ref-ico">📋</div><div><span>Notes ouvertes</span><strong>{{ $stats['open_notes'] }}</strong><small>À suivre</small></div></article>
            <article class="ref-card"><div class="ref-ico">🚨</div><div><span>Notes urgentes</span><strong>{{ $stats['urgent_notes'] }}</strong><small>Prioritaires</small></div></article>
            <article class="ref-card"><div class="ref-ico">❓</div><div><span>Questions ouvertes</span><strong>{{ $stats['questions_open'] }}</strong><small>À surveiller</small></div></article>
            <article class="ref-card"><div class="ref-ico">👥</div><div><span>Enseignants suivis</span><strong>{{ $stats['teachers'] }}</strong><small>Actifs</small></div></article>
        </section>

        <div class="ref-panels">
            <section class="ref-panel"><div class="ref-panel__head"><h3>Cours à relire</h3><a href="#">Voir tout</a></div><div class="ref-list">@forelse($courses as $course)<div class="ref-row"><div class="ref-row__line"><strong>{{ $course->title }}</strong><span class="ref-badge {{ $statusClass($course->status) }}">{{ $statusLabel($course->status) }}</span></div><span>{{ $course->class_name ?? '-' }} · {{ $course->subject_name ?? '-' }}</span></div>@empty<div class="ref-empty">Aucun cours à afficher.</div>@endforelse</div></section>
            <section class="ref-panel"><div class="ref-panel__head"><h3>TD à relire</h3><a href="#">Voir tout</a></div><div class="ref-list">@forelse($tdSets as $td)<div class="ref-row"><div class="ref-row__line"><strong>{{ $td->title }}</strong><span class="ref-badge {{ $statusClass($td->status) }}">{{ $statusLabel($td->status) }}</span></div><span>{{ $td->class_name ?? '-' }} · {{ $td->subject_name ?? '-' }}</span></div>@empty<div class="ref-empty">Aucun TD à afficher.</div>@endforelse</div></section>
            <section class="ref-panel"><div class="ref-panel__head"><h3>Questions à surveiller</h3><a href="#">Voir tout</a></div><div class="ref-list">@forelse($questions as $question)<div class="ref-row"><div class="ref-row__line"><strong>{{ $question->subject_name ?: 'Question élève' }}</strong><span class="ref-badge {{ $statusClass($question->status) }}">{{ $statusLabel($question->status) }}</span></div><span>{{ $question->class_name ?? '-' }}</span></div>@empty<div class="ref-empty">Aucune question ouverte.</div>@endforelse</div></section>
            <section class="ref-panel"><div class="ref-panel__head"><h3>Enseignants concernés</h3><a href="#">Voir tout</a></div><div class="ref-list">@forelse($teachers as $teacher)<div class="ref-row"><div class="ref-row__line"><strong>{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</strong><span class="ref-badge ref-badge--neutral">Suivi</span></div><span>{{ $teacher->class_name ?? '-' }} · {{ $teacher->subject_name ?? '-' }}</span></div>@empty<div class="ref-empty">Aucun enseignant.</div>@endforelse</div></section>
            <section class="ref-panel"><div class="ref-panel__head"><h3>Notes qualité / relances</h3><a href="#">Voir tout</a></div><div class="ref-list">@forelse($notes as $note)<div class="ref-row"><div class="ref-row__line"><strong>{{ $note->title }}</strong><span class="ref-badge {{ $statusClass($note->severity) }}">{{ $statusLabel($note->severity) }}</span></div><span>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone générale')) }}</span><small>{{ $statusLabel($note->status) }}</small></div>@empty<div class="ref-empty">Aucune note.</div>@endforelse</div></section>
            <section class="ref-panel"><div class="ref-panel__head"><h3>Critères de contrôle</h3></div><div class="ref-list"><div class="ref-row"><strong>Clarté du cours</strong><span>Objectifs, explications, exemples.</span></div><div class="ref-row"><strong>Niveau adapté</strong><span>Contenu conforme au niveau des élèves.</span></div><div class="ref-row"><strong>TD cohérent</strong><span>Consignes, durée, correction possible.</span></div><div class="ref-row"><strong>Correction exploitable</strong><span>Étapes, méthode, lisibilité.</span></div></div></section>
        </div>

        <section class="ref-form" id="note-qualite">
            <h3>Ajouter une note qualité rapide</h3>
            <form method="POST" action="{{ route('supervision.notes.store') }}">
                @csrf
                <input type="hidden" name="responsibility_id" value="{{ $responsibility->id }}">
                <input name="title" required placeholder="Exemple : Revoir la progression du TD d’électrotechnique">
                <select name="severity"><option value="info">Info</option><option value="warning">Attention</option><option value="urgent">Urgent</option></select>
                <button type="submit" class="ref-btn ref-btn--green">Enregistrer</button>
            </form>
        </section>

        <div class="ref-footer"><span>Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</span><span>Une solution Cabrel Tech.</span></div>
    @endif
</div>
@endsection
