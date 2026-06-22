@extends('layouts.student')

@section('title', 'Tableau de bord')

@php
    use Illuminate\Support\Facades\Route;

    $studentName = $user->full_name ?? $user->name ?? $user->username ?? auth()->user()->full_name ?? auth()->user()->name ?? 'Élève';
    $className = $studentProfile->schoolClass->name ?? 'Classe non définie';
    $subscriptionName = $subscription->plan_name ?? 'Aucun abonnement';
    $subscriptionState = ($subscription && method_exists($subscription, 'isActive') && $subscription->isActive()) ? 'Actif' : 'Inactif';
    $subscriptionTone = $subscriptionState === 'Actif' ? 'ok' : 'warn';
    $progress = (int) ($progressPercent ?? 0);
    $allCoursesCount = (int) ($allCoursesCount ?? 0);
    $allTdCount = (int) ($allTdCount ?? 0);
    $tdOpenedCount = (int) ($tdOpenedCount ?? 0);
    $tdCompletedCount = (int) ($tdCompletedCount ?? 0);
    $pendingCount = (int) ($pendingCount ?? 0);

    $coursesUrl = Route::has('student.courses.index') ? route('student.courses.index') : '#';
    $tdUrl = Route::has('student.td.index') ? route('student.td.index') : '#';
    $messagesUrl = Route::has('student.messages.index') ? route('student.messages.index') : '#';
    $subscriptionUrl = Route::has('subscription.required') ? route('subscription.required') : '#';

    $items = collect();
    foreach (($recentCourses ?? collect()) as $course) {
        $items->push([
            'type' => 'Cours',
            'title' => $course->title ?? 'Cours',
            'subject' => $course->subject->name ?? 'Matière',
            'date' => $course->published_at ?? $course->created_at ?? null,
            'url' => Route::has('student.courses.show') ? route('student.courses.show', $course) : '#',
        ]);
    }
    foreach (($recentTdSets ?? collect()) as $td) {
        $items->push([
            'type' => 'TD',
            'title' => $td->title ?? 'TD',
            'subject' => $td->subject->name ?? 'Matière',
            'date' => $td->published_at ?? $td->created_at ?? null,
            'url' => Route::has('student.td.show') ? route('student.td.show', $td) : '#',
        ]);
    }
    $items = $items->sortByDesc(fn($item) => optional($item['date'])->timestamp ?? 0)->take(6)->values();
@endphp

@push('styles')
<style>
    .student-clean-dashboard {
        display: grid;
        gap: 18px;
        color: #0f172a;
    }

    .student-clean-dashboard * {
        box-sizing: border-box;
    }

    .student-clean-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(280px, .88fr);
        gap: 18px;
        align-items: stretch;
        padding: 24px;
        border-radius: 28px;
        background: linear-gradient(135deg, #ffffff 0%, #eef6ff 48%, #e8f1ff 100%);
        border: 1px solid #d8e7ff;
        box-shadow: 0 18px 45px rgba(37, 99, 235, .10);
        position: relative;
        overflow: hidden;
    }

    .student-clean-hero::after {
        content: "";
        position: absolute;
        width: 320px;
        height: 320px;
        right: -120px;
        top: -150px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .10);
        pointer-events: none;
    }

    .student-clean-hero__content,
    .student-clean-hero__side {
        position: relative;
        z-index: 1;
    }

    .student-clean-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: #e0edff;
        color: #1d4ed8;
        font-weight: 900;
        font-size: .84rem;
        border: 1px solid #c7dcff;
    }

    .student-clean-hero h1 {
        margin: 16px 0 10px;
        max-width: 760px;
        font-size: clamp(2rem, 4vw, 3.4rem);
        line-height: 1.02;
        letter-spacing: -.055em;
        color: #0f172a;
    }

    .student-clean-hero h1 span {
        color: #1d4ed8;
    }

    .student-clean-hero p {
        margin: 0;
        max-width: 680px;
        color: #52657f;
        font-size: 1.02rem;
        line-height: 1.7;
    }

    .student-clean-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .student-clean-pill {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 0 13px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #d8e7ff;
        color: #18345f;
        font-weight: 850;
        box-shadow: 0 8px 18px rgba(37, 99, 235, .06);
    }

    .student-clean-hero__side {
        display: grid;
        gap: 12px;
    }

    .student-clean-side-card {
        border-radius: 22px;
        background: #ffffff;
        border: 1px solid #d8e7ff;
        padding: 18px;
        box-shadow: 0 12px 26px rgba(37, 99, 235, .08);
    }

    .student-clean-side-card small {
        display: block;
        color: #64748b;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 8px;
    }

    .student-clean-side-card strong {
        display: block;
        color: #0f172a;
        font-size: 1.25rem;
        letter-spacing: -.03em;
    }

    .student-clean-side-card.is-ok strong { color: #047857; }
    .student-clean-side-card.is-warn strong { color: #dc2626; }

    .student-clean-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .student-clean-kpi {
        min-height: 132px;
        padding: 18px;
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid #dbe8fb;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
        display: grid;
        align-content: space-between;
    }

    .student-clean-kpi span {
        color: #60758d;
        font-size: .8rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .045em;
    }

    .student-clean-kpi strong {
        display: block;
        margin-top: 10px;
        font-size: 2.25rem;
        line-height: 1;
        color: #0f172a;
        letter-spacing: -.06em;
    }

    .student-clean-kpi small {
        display: block;
        color: #64748b;
        margin-top: 8px;
        font-weight: 700;
    }

    .student-clean-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 18px;
        align-items: start;
    }

    .student-clean-panel,
    .student-clean-action {
        background: #ffffff;
        border: 1px solid #dbe8fb;
        border-radius: 24px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .student-clean-panel__head {
        padding: 18px 20px;
        border-bottom: 1px solid #edf3fc;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .student-clean-panel__head h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.18rem;
        letter-spacing: -.035em;
    }

    .student-clean-panel__head a {
        text-decoration: none;
        color: #1d4ed8;
        font-weight: 900;
    }

    .student-clean-list {
        display: grid;
        gap: 10px;
        padding: 16px;
    }

    .student-clean-row {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 13px;
        border-radius: 18px;
        background: #f8fbff;
        border: 1px solid #edf3fc;
    }

    .student-clean-row__icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #e0edff;
        color: #1d4ed8;
        font-weight: 950;
    }

    .student-clean-row strong {
        color: #0f172a;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .student-clean-row small {
        display: block;
        color: #64748b;
        margin-top: 3px;
    }

    .student-clean-row a {
        text-decoration: none;
        color: #ffffff;
        background: #1d4ed8;
        padding: 9px 12px;
        border-radius: 999px;
        font-weight: 900;
        white-space: nowrap;
    }

    .student-clean-actions {
        display: grid;
        gap: 12px;
    }

    .student-clean-action {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 16px;
        text-decoration: none;
        color: inherit;
    }

    .student-clean-action__icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: #e0edff;
        color: #1d4ed8;
        font-size: 1.2rem;
        flex: 0 0 48px;
    }

    .student-clean-action strong {
        display: block;
        color: #0f172a;
        font-size: 1rem;
        margin-bottom: 4px;
    }

    .student-clean-action span {
        color: #64748b;
        line-height: 1.45;
    }

    .student-clean-progress {
        height: 12px;
        border-radius: 999px;
        background: #e8f0ff;
        overflow: hidden;
        margin-top: 12px;
    }

    .student-clean-progress div {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #1d4ed8, #38bdf8);
    }

    .student-clean-empty {
        padding: 24px;
        text-align: center;
        color: #64748b;
        border: 1px dashed #dbe8fb;
        border-radius: 18px;
        background: #f8fbff;
    }

    html[data-theme='dark'] .student-clean-dashboard,
    html[data-theme='dark'] .student-clean-hero h1,
    html[data-theme='dark'] .student-clean-kpi strong,
    html[data-theme='dark'] .student-clean-panel__head h2,
    html[data-theme='dark'] .student-clean-row strong,
    html[data-theme='dark'] .student-clean-action strong,
    html[data-theme='dark'] .student-clean-side-card strong {
        color: #eaf2ff;
    }

    html[data-theme='dark'] .student-clean-hero,
    html[data-theme='dark'] .student-clean-kpi,
    html[data-theme='dark'] .student-clean-panel,
    html[data-theme='dark'] .student-clean-action,
    html[data-theme='dark'] .student-clean-side-card {
        background: #0f172a;
        border-color: rgba(148, 163, 184, .22);
    }

    html[data-theme='dark'] .student-clean-row,
    html[data-theme='dark'] .student-clean-empty {
        background: rgba(15, 23, 42, .55);
        border-color: rgba(148, 163, 184, .18);
    }

    @media (max-width: 1100px) {
        .student-clean-hero,
        .student-clean-grid {
            grid-template-columns: 1fr;
        }
        .student-clean-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .student-clean-hero { padding: 18px; border-radius: 22px; }
        .student-clean-kpis { grid-template-columns: 1fr; }
        .student-clean-row { grid-template-columns: 42px minmax(0,1fr); }
        .student-clean-row a { grid-column: 1 / -1; text-align: center; }
    }
</style>
@endpush

@section('content')
<div class="student-clean-dashboard">
    <section class="student-clean-hero">
        <div class="student-clean-hero__content">
            <span class="student-clean-badge">Tableau de bord élève</span>
            <h1>Bonjour, <span>{{ $studentName }}</span></h1>
            <p>Accédez rapidement à vos cours, vos TD, votre messagerie et votre progression sans fatigue visuelle.</p>
            <div class="student-clean-pills">
                <span class="student-clean-pill">Classe : {{ $className }}</span>
                <span class="student-clean-pill">{{ $tdOpenedCount }} TD ouverts</span>
                <span class="student-clean-pill">Progression : {{ $progress }}%</span>
            </div>
        </div>

        <div class="student-clean-hero__side">
            <div class="student-clean-side-card is-{{ $subscriptionTone }}">
                <small>État de l’abonnement</small>
                <strong>{{ $subscriptionState }}</strong>
                <div class="student-clean-progress"><div style="width: {{ $subscriptionState === 'Actif' ? 100 : 12 }}%"></div></div>
            </div>
            <div class="student-clean-side-card">
                <small>Objectif du moment</small>
                <strong>{{ $pendingCount > 0 ? $pendingCount.' élément(s) à consulter' : 'Vous êtes à jour' }}</strong>
            </div>
        </div>
    </section>

    @if(!empty($studentExamCountdown))
        @include('components.exam-countdowns', ['examCountdowns' => [$studentExamCountdown], 'compact' => true])
    @endif

    <section class="student-clean-kpis">
        <div class="student-clean-kpi"><span>Cours disponibles</span><strong>{{ $allCoursesCount }}</strong><small>Dans votre classe</small></div>
        <div class="student-clean-kpi"><span>TD disponibles</span><strong>{{ $allTdCount }}</strong><small>{{ $tdOpenedCount }} déjà ouvert(s)</small></div>
        <div class="student-clean-kpi"><span>TD terminés</span><strong>{{ $tdCompletedCount }}</strong><small>Travaux validés</small></div>
        <div class="student-clean-kpi"><span>Rappels</span><strong>{{ $pendingCount }}</strong><small>À consulter</small></div>
    </section>

    <section class="student-clean-grid">
        <div class="student-clean-panel">
            <div class="student-clean-panel__head">
                <h2>Derniers cours et TD</h2>
                <a href="{{ $coursesUrl }}">Voir les cours</a>
            </div>
            <div class="student-clean-list">
                @forelse($items as $item)
                    <div class="student-clean-row">
                        <div class="student-clean-row__icon">{{ $item['type'] === 'TD' ? 'TD' : 'C' }}</div>
                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            <small>{{ $item['type'] }} · {{ $item['subject'] }} @if($item['date']) · {{ optional($item['date'])->diffForHumans() }} @endif</small>
                        </div>
                        <a href="{{ $item['url'] }}">Ouvrir</a>
                    </div>
                @empty
                    <div class="student-clean-empty">Aucune publication récente pour votre classe.</div>
                @endforelse
            </div>
        </div>

        <aside class="student-clean-actions">
            <a href="{{ $coursesUrl }}" class="student-clean-action"><div class="student-clean-action__icon">📘</div><div><strong>Mes cours</strong><span>Consulter les cours publiés avec version officielle TIMAH.</span></div></a>
            <a href="{{ $tdUrl }}" class="student-clean-action"><div class="student-clean-action__icon">✅</div><div><strong>Mes TD</strong><span>Ouvrir les TD et les corrigés disponibles.</span></div></a>
            <a href="{{ $messagesUrl }}" class="student-clean-action"><div class="student-clean-action__icon">💬</div><div><strong>Messagerie</strong><span>Poser une question ou répondre à un enseignant.</span></div></a>
            <a href="{{ $subscriptionUrl }}" class="student-clean-action"><div class="student-clean-action__icon">⭐</div><div><strong>Abonnement</strong><span>Voir l’état de votre accès aux contenus.</span></div></a>
        </aside>
    </section>
</div>
@endsection
