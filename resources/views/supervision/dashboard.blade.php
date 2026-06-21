@extends('layouts.teacher')

@section('title', 'TB responsable pédagogique')
@section('page_title', 'TB responsable pédagogique')
@section('page_subtitle', 'Suivi des cours, TD, questions, enseignants et relances selon votre responsabilité.')

@section('content')
<style>
    .supervision-workspace{display:grid;gap:18px}.supervision-hero{border-radius:28px;padding:22px;color:#fff;background:linear-gradient(135deg,#0f172a,#1d4ed8,#0f766e);box-shadow:0 22px 54px rgba(15,23,42,.18)}.supervision-hero h2{font-size:clamp(1.8rem,5vw,3rem);margin:8px 0}.supervision-hero p{color:#dbeafe}.supervision-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.supervision-card{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:16px;box-shadow:0 12px 30px rgba(15,23,42,.06)}.supervision-card span{display:block;color:#64748b;font-weight:800}.supervision-card strong{display:block;font-size:2rem;margin-top:6px}.supervision-panels{display:grid;grid-template-columns:1fr 1fr;gap:16px}.supervision-panel{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:16px}.supervision-panel h3{margin:0 0 10px}.supervision-list{display:grid;gap:10px}.supervision-row{border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc}.supervision-row strong{display:block}.supervision-row span,.supervision-row small{color:#64748b}.supervision-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.supervision-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;background:#fff;color:#0f172a;font-weight:900;text-decoration:none}.supervision-btn--ghost{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.28)}.supervision-form{display:grid;gap:10px}.supervision-form input,.supervision-form select,.supervision-form textarea{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:12px;padding:10px}.supervision-form button{border:0;border-radius:14px;padding:12px 14px;background:#2563eb;color:#fff;font-weight:900}.supervision-empty{padding:18px;border-radius:18px;background:#f8fafc;color:#64748b;text-align:center}@media(max-width:980px){.supervision-grid,.supervision-panels{grid-template-columns:1fr 1fr}}@media(max-width:640px){.supervision-grid,.supervision-panels{grid-template-columns:1fr}}
</style>

<div class="supervision-workspace">
    @if(!$schemaReady)
        <section class="supervision-hero">
            <h2>Migration nécessaire</h2>
            <p>Les tables de supervision ne sont pas encore installées sur le serveur Contabo. Déploie la dernière version puis lance les migrations Laravel.</p>
            <div class="supervision-actions"><a href="{{ route('teacher.dashboard') }}" class="supervision-btn">Retour enseignant</a></div>
        </section>
    @else
        <section class="supervision-hero">
            <span>Administration pédagogique</span>
            <h2>{{ $areaTitle }}</h2>
            <p>Ce tableau de bord permet au responsable de suivre sa zone, de repérer les retards et de créer des notes de relance sans avoir un accès admin complet.</p>
            <div class="supervision-actions">
                <a href="{{ route('teacher.dashboard') }}" class="supervision-btn">Retour enseignant</a>
                @if(Route::has('admin.organization.index'))
                    <a href="{{ route('admin.organization.index') }}" class="supervision-btn supervision-btn--ghost">Administration centrale</a>
                @endif
            </div>
        </section>

        @if($responsibilities->count() > 1)
            <section class="supervision-panel">
                <h3>Changer de responsabilité</h3>
                <form method="GET" action="{{ route('supervision.dashboard') }}" class="supervision-form">
                    <select name="responsibility" onchange="this.form.submit()">
                        @foreach($responsibilities as $responsibility)
                            <option value="{{ $responsibility->id }}" @selected($activeResponsibility && $activeResponsibility->id === $responsibility->id)>{{ $responsibility->role_title }} — {{ $responsibility->department_name ?: ($responsibility->division_name ?: 'Plateforme entière') }}</option>
                        @endforeach
                    </select>
                </form>
            </section>
        @endif

        <section class="supervision-grid">
            <article class="supervision-card"><span>Enseignants suivis</span><strong>{{ $stats['teachers'] ?? 0 }}</strong></article>
            <article class="supervision-card"><span>Cours publiés</span><strong>{{ $stats['courses_published'] ?? 0 }}</strong></article>
            <article class="supervision-card"><span>TD publiés</span><strong>{{ $stats['td_published'] ?? 0 }}</strong></article>
            <article class="supervision-card"><span>Questions ouvertes</span><strong>{{ $stats['questions_open'] ?? 0 }}</strong></article>
        </section>

        <div class="supervision-panels">
            <section class="supervision-panel">
                <h3>Enseignants concernés</h3>
                <div class="supervision-list">
                    @forelse($teachers as $teacher)
                        <div class="supervision-row"><strong>{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</strong><span>{{ $teacher->class_name ?? '-' }} · {{ $teacher->subject_name ?? '-' }}</span></div>
                    @empty
                        <div class="supervision-empty">Aucun enseignant trouvé pour cette zone.</div>
                    @endforelse
                </div>
            </section>

            <section class="supervision-panel">
                <h3>Cours récents</h3>
                <div class="supervision-list">
                    @forelse($courses as $course)
                        <div class="supervision-row"><strong>{{ $course->title }}</strong><span>{{ $course->class_name ?? '-' }} · {{ $course->subject_name ?? '-' }}</span><small>{{ $course->status }}</small></div>
                    @empty
                        <div class="supervision-empty">Aucun cours trouvé.</div>
                    @endforelse
                </div>
            </section>

            <section class="supervision-panel">
                <h3>TD récents</h3>
                <div class="supervision-list">
                    @forelse($tdSets as $td)
                        <div class="supervision-row"><strong>{{ $td->title }}</strong><span>{{ $td->class_name ?? '-' }} · {{ $td->subject_name ?? '-' }}</span><small>{{ $td->status }}</small></div>
                    @empty
                        <div class="supervision-empty">Aucun TD trouvé.</div>
                    @endforelse
                </div>
            </section>

            <section class="supervision-panel">
                <h3>Questions ouvertes</h3>
                <div class="supervision-list">
                    @forelse($questions as $question)
                        <div class="supervision-row"><strong>{{ $question->subject ?: 'Question élève' }}</strong><span>{{ $question->class_name ?? '-' }} · {{ $question->subject_name ?? '-' }}</span><small>{{ $question->status }}</small></div>
                    @empty
                        <div class="supervision-empty">Aucune question ouverte trouvée.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="supervision-panel">
            <h3>Créer une note de suivi / relance</h3>
            <form method="POST" action="{{ route('supervision.notes.store') }}" class="supervision-form">
                @csrf
                <input type="hidden" name="responsibility_id" value="{{ $activeResponsibility->id }}">
                <select name="target_user_id">
                    <option value="">Aucune personne ciblée</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->full_name ?: ($teacher->name ?: $teacher->username) }}</option>
                    @endforeach
                </select>
                <input name="title" required placeholder="Ex: Retard sur les TD de la semaine">
                <select name="severity"><option value="info">Info</option><option value="warning">Attention</option><option value="urgent">Urgent</option></select>
                <textarea name="message" rows="4" placeholder="Message de suivi..."></textarea>
                <button type="submit">Enregistrer la note</button>
            </form>
        </section>

        <section class="supervision-panel">
            <h3>Notes de suivi</h3>
            <div class="supervision-list">
                @forelse($notes as $note)
                    <div class="supervision-row"><strong>{{ $note->title }}</strong><span>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone générale')) }}</span><small>{{ $note->severity }} · {{ $note->status }}</small></div>
                @empty
                    <div class="supervision-empty">Aucune note de suivi pour le moment.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
