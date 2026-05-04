@extends('layouts.admin')

@section('title', 'TIMAH ACADEMY Mobile')
@section('page_title', 'TIMAH ACADEMY Mobile')
@section('page_subtitle', 'Centre de pilotage mobile : programme, babillard, quiz, évaluations, rapports et notifications.')

@section('content')
@php
    $days = [1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi',7=>'Dimanche'];
@endphp

<div class="admin-compact-page">
    @if($missingTables->isNotEmpty())
        <div class="admin-alert admin-alert--warning">Tables manquantes : {{ $missingTables->implode(', ') }}</div>
    @endif

    <div class="admin-summary-strip">
        <div class="admin-summary-card"><strong>{{ $programs->count() }}</strong><span>activités</span></div>
        <div class="admin-summary-card"><strong>{{ $posts->count() }}</strong><span>annonces</span></div>
        <div class="admin-summary-card"><strong>{{ $quizzes->count() }}</strong><span>quiz</span></div>
        <div class="admin-summary-card"><strong>{{ $evaluations->count() }}</strong><span>évaluations</span></div>
        <div class="admin-summary-card"><strong>{{ $reports->count() }}</strong><span>rapports</span></div>
        <div class="admin-summary-card"><strong>{{ $notifications->count() }}</strong><span>notifications</span></div>
    </div>

    <section class="admin-list-panel">
        <div class="admin-list-panel__head"><div><h2>1. Programme de répétition</h2><p>Ajoutez les cours, TD, quiz et révisions visibles dans Flutter.</p></div></div>
        <details class="admin-collapse-box" open><summary>Ajouter une activité</summary><div class="admin-collapse-box__body">
            <form method="POST" action="{{ route('admin.mobile-academy.program.store') }}" class="admin-form">@csrf
                <div class="admin-form-grid">
                    <div class="form-group"><label>Titre</label><input name="title" required></div>
                    <div class="form-group"><label>Type</label><select name="activity_type"><option value="course">Cours</option><option value="td">TD</option><option value="quiz">Quiz</option><option value="revision">Révision</option><option value="evaluation">Évaluation</option></select></div>
                    <div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Tous</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Matière</label><select name="subject_id"><option value="">Toutes</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Semaine</label><input type="number" name="week_number" value="1" min="1"></div>
                    <div class="form-group"><label>Jour</label><select name="weekday">@foreach($days as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Heure</label><input type="time" name="unlock_time" value="18:00"></div>
                    <div class="form-group"><label>Déblocage exact</label><input type="datetime-local" name="unlocks_at"></div>
                    <div class="form-group"><label>Clôture</label><input type="datetime-local" name="closes_at"></div>
                    <div class="form-group"><label>Durée</label><input type="number" name="duration_minutes" placeholder="60"></div>
                    <div class="form-group"><label>Statut</label><select name="status"><option value="published">Publié</option><option value="scheduled">Programmé</option><option value="draft">Brouillon</option></select></div>
                    <div class="form-group form-group--check"><label><input type="checkbox" name="requires_subscription" value="1" checked> Abonnement requis</label></div>
                    <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="4"></textarea></div>
                </div><button class="btn btn--primary">Ajouter</button>
            </form>
        </div></details>
        <div class="admin-clean-list">@forelse($programs as $item)<article class="admin-clean-row"><div class="admin-clean-title"><strong>{{ $item->title ?? 'Activité' }}</strong><span>{{ $item->activity_type ?? 'activité' }} · {{ $days[(int)($item->weekday ?? 0)] ?? 'Jour' }} · {{ $item->unlock_time ?? '—' }}</span><p style="color:var(--muted)">{{ Str::limit($item->description ?? '', 140) }}</p></div><div class="admin-clean-meta"><span class="admin-badge">{{ $item->status ?? '—' }}</span></div></article>@empty<div class="admin-empty-box">Aucune activité.</div>@endforelse</div>
    </section>

    <section class="admin-list-panel">
        <div class="admin-list-panel__head"><div><h2>2. Babillard numérique</h2><p>Publiez annonces, rappels et messages.</p></div></div>
        <details class="admin-collapse-box"><summary>Ajouter une publication</summary><div class="admin-collapse-box__body">
            <form method="POST" action="{{ route('admin.mobile-academy.board.store') }}" class="admin-form">@csrf
                <div class="admin-form-grid"><div class="form-group"><label>Titre</label><input name="title" required></div><div class="form-group"><label>Type</label><select name="type"><option value="announcement">Annonce</option><option value="report">Rapport</option><option value="evaluation">Évaluation</option></select></div><div class="form-group"><label>Public</label><select name="audience"><option value="all">Tous</option><option value="student">Élèves</option><option value="parent">Parents</option></select></div><div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Tous</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div><div class="form-group"><label>Statut</label><select name="status"><option value="published">Publié</option><option value="draft">Brouillon</option></select></div><div class="form-group admin-form-grid__full"><label>Message</label><textarea name="content" rows="4" required></textarea></div></div><button class="btn btn--primary">Publier</button>
            </form>
        </div></details>
        <div class="admin-clean-list">@forelse($posts as $post)<article class="admin-clean-row"><div class="admin-clean-title"><strong>{{ $post->title ?? 'Publication' }}</strong><span>{{ $post->type ?? 'info' }} · {{ $post->audience ?? 'all' }}</span><p style="color:var(--muted)">{{ Str::limit($post->content ?? '', 160) }}</p></div><div class="admin-clean-meta"><span class="admin-badge">{{ $post->status ?? '—' }}</span></div></article>@empty<div class="admin-empty-box">Aucune publication.</div>@endforelse</div>
    </section>

    <section class="admin-list-panel">
        <div class="admin-list-panel__head"><div><h2>3. Quiz natifs</h2><p>Créez des QCM avec correction automatique.</p></div></div>
        <details class="admin-collapse-box"><summary>Créer un quiz</summary><div class="admin-collapse-box__body">
            <form method="POST" action="{{ route('admin.mobile-academy.quizzes.store') }}" class="admin-form">@csrf
                <div class="admin-form-grid"><div class="form-group"><label>Titre</label><input name="title" required></div><div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Tous</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div><div class="form-group"><label>Matière</label><select name="subject_id"><option value="">Toutes</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div><div class="form-group"><label>Durée</label><input type="number" name="duration_minutes" value="15"></div><div class="form-group"><label>Note minimale</label><input type="number" name="pass_mark" value="10"></div><div class="form-group"><label>Statut</label><select name="status"><option value="published">Publié</option><option value="draft">Brouillon</option></select></div><div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3"></textarea></div></div><button class="btn btn--primary">Créer</button>
            </form>
        </div></details>
        <div class="admin-clean-list">@forelse($quizzes as $quiz)<article class="admin-clean-row"><div class="admin-clean-title"><strong>{{ $quiz->title ?? 'Quiz' }}</strong><span>{{ $quiz->status ?? '—' }}</span><p style="color:var(--muted)">{{ Str::limit($quiz->description ?? '', 130) }}</p><details class="admin-subscription-manage"><summary>Ajouter une question</summary><form method="POST" action="{{ route('admin.mobile-academy.quizzes.questions.store', ['quiz' => $quiz->id]) }}" class="admin-form">@csrf<input name="question" placeholder="Question" required><textarea name="choices_text" rows="4" placeholder="Un choix par ligne" required></textarea><input name="correct_answer" placeholder="Réponse exacte" required><textarea name="explanation" rows="2" placeholder="Explication"></textarea><input type="number" name="points" value="1"><button class="btn btn--primary">Ajouter</button></form></details></div></article>@empty<div class="admin-empty-box">Aucun quiz.</div>@endforelse</div>
    </section>

    <section class="admin-list-panel"><div class="admin-list-panel__head"><div><h2>4. Évaluations bimensuelles</h2><p>Planifiez les évaluations de progression.</p></div></div><details class="admin-collapse-box"><summary>Ajouter une évaluation</summary><div class="admin-collapse-box__body"><form method="POST" action="{{ route('admin.mobile-academy.evaluations.store') }}" class="admin-form">@csrf<div class="admin-form-grid"><div class="form-group"><label>Titre</label><input name="title" required></div><div class="form-group"><label>Statut</label><select name="status"><option value="published">Publié</option><option value="scheduled">Programmé</option><option value="draft">Brouillon</option></select></div><div class="form-group"><label>Ouverture</label><input type="datetime-local" name="opens_at"></div><div class="form-group"><label>Clôture</label><input type="datetime-local" name="closes_at"></div><div class="form-group"><label>Durée</label><input type="number" name="duration_minutes" value="120"></div><div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3"></textarea></div></div><button class="btn btn--primary">Ajouter</button></form></div></details><div class="admin-clean-list">@forelse($evaluations as $evaluation)<article class="admin-clean-row"><div class="admin-clean-title"><strong>{{ $evaluation->title ?? 'Évaluation' }}</strong><span>{{ $evaluation->status ?? '—' }}</span><p style="color:var(--muted)">{{ Str::limit($evaluation->description ?? '', 140) }}</p></div></article>@empty<div class="admin-empty-box">Aucune évaluation.</div>@endforelse</div></section>

    <section class="admin-list-panel"><div class="admin-list-panel__head"><div><h2>5. Rapports de progression</h2><p>Publiez les rapports visibles dans le suivi parent.</p></div></div><details class="admin-collapse-box"><summary>Créer un rapport</summary><div class="admin-collapse-box__body"><form method="POST" action="{{ route('admin.mobile-academy.reports.store') }}" class="admin-form">@csrf<div class="admin-form-grid"><div class="form-group"><label>Élève</label><select name="student_id" required>@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->full_name ?: $student->name ?: $student->username ?: ('Utilisateur #' . $student->id) }}</option>@endforeach</select></div><div class="form-group"><label>Participation %</label><input type="number" name="participation_rate" value="0"></div><div class="form-group"><label>Score</label><input type="number" step="0.01" name="evaluation_score"></div><div class="form-group"><label>Cours faits</label><input type="number" name="courses_done" value="0"></div><div class="form-group"><label>TD faits</label><input type="number" name="td_done" value="0"></div><div class="form-group"><label>Quiz faits</label><input type="number" name="quizzes_done" value="0"></div><div class="form-group"><label>Statut</label><select name="status"><option value="published">Publié</option><option value="draft">Brouillon</option></select></div><div class="form-group admin-form-grid__full"><label>Points forts</label><textarea name="strengths" rows="2"></textarea></div><div class="form-group admin-form-grid__full"><label>Difficultés</label><textarea name="weaknesses" rows="2"></textarea></div><div class="form-group admin-form-grid__full"><label>Recommandations</label><textarea name="recommendations" rows="2"></textarea></div></div><button class="btn btn--primary">Publier</button></form></div></details><div class="admin-clean-list">@forelse($reports as $report)<article class="admin-clean-row"><div class="admin-clean-title"><strong>Élève #{{ $report->student_id ?? '—' }}</strong><span>Participation {{ $report->participation_rate ?? 0 }}% · Score {{ $report->evaluation_score ?? '—' }}</span><p style="color:var(--muted)">{{ Str::limit($report->recommendations ?? '', 140) }}</p></div></article>@empty<div class="admin-empty-box">Aucun rapport.</div>@endforelse</div></section>

    <section class="admin-list-panel"><div class="admin-list-panel__head"><div><h2>6. Notifications internes gratuites</h2><p>Messages vus dans l’application sans API WhatsApp payante.</p></div></div><details class="admin-collapse-box"><summary>Créer une notification</summary><div class="admin-collapse-box__body"><form method="POST" action="{{ route('admin.mobile-academy.notifications.store') }}" class="admin-form">@csrf<div class="admin-form-grid"><div class="form-group"><label>Titre</label><input name="title" required></div><div class="form-group"><label>Type</label><select name="type"><option value="info">Info</option><option value="course">Cours</option><option value="td">TD</option><option value="quiz">Quiz</option><option value="evaluation">Évaluation</option><option value="report">Rapport</option><option value="subscription">Abonnement</option></select></div><div class="form-group"><label>Public</label><select name="audience"><option value="all">Tous</option><option value="student">Élèves</option><option value="parent">Parents</option></select></div><div class="form-group admin-form-grid__full"><label>Message</label><textarea name="message" rows="3" required></textarea></div></div><button class="btn btn--primary">Publier</button></form></div></details><div class="admin-clean-list">@forelse($notifications as $notification)<article class="admin-clean-row"><div class="admin-clean-title"><strong>{{ $notification->title ?? 'Notification' }}</strong><span>{{ $notification->type ?? 'info' }} · {{ $notification->audience ?? 'all' }}</span><p style="color:var(--muted)">{{ Str::limit($notification->message ?? '', 150) }}</p></div></article>@empty<div class="admin-empty-box">Aucune notification.</div>@endforelse</div></section>
</div>
@endsection
