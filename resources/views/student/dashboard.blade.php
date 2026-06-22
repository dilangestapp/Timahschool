@extends('layouts.student')

@section('title', 'Tableau de bord')

@php
    use Illuminate\Support\Facades\Route;

    $studentName = $user->full_name ?? $user->name ?? $user->username ?? auth()->user()->full_name ?? auth()->user()->name ?? 'Élève';
    $className = $studentProfile->schoolClass->name ?? 'Classe non définie';
    $subscriptionName = $subscription->plan_name ?? 'Aucun abonnement';
    $subscriptionState = ($subscription && method_exists($subscription, 'isActive') && $subscription->isActive()) ? 'Actif' : 'Inactif';
    $subscriptionTone = $subscriptionState === 'Actif' ? 'good' : 'danger';
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
            'icon' => '▭',
            'title' => $course->title ?? 'Cours',
            'subject' => $course->subject->name ?? 'Matière',
            'date' => $course->published_at ?? $course->created_at ?? null,
            'url' => Route::has('student.courses.show') ? route('student.courses.show', $course) : '#',
        ]);
    }
    foreach (($recentTdSets ?? collect()) as $td) {
        $items->push([
            'type' => 'TD',
            'icon' => '☑',
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
    .student-resp-dashboard {
        --sr-blue: #1d4ed8;
        --sr-navy: #18227a;
        --sr-purple: #7c3aed;
        --sr-cyan: #0891b2;
        --sr-green: #059669;
        --sr-orange: #ea580c;
        --sr-pink: #db2777;
        --sr-red: #dc2626;
        --sr-soft: #eef4ff;
        --sr-line: #dbe7ff;
        --sr-text: #0f172a;
        --sr-muted: #64748b;
        display: grid;
        gap: 18px;
        color: var(--sr-text);
    }

    .student-resp-dashboard * { box-sizing: border-box; }

    .sr-topbar {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        background: linear-gradient(135deg, #ffffff 0%, #f2f7ff 100%);
        border: 1px solid var(--sr-line);
        border-radius: 26px;
        padding: 20px 22px;
        box-shadow: 0 16px 38px rgba(29, 78, 216, .08);
    }

    .sr-title h1 {
        margin: 0;
        color: var(--sr-navy);
        font-size: clamp(1.8rem, 3.2vw, 3rem);
        line-height: 1.02;
        letter-spacing: -.055em;
    }

    .sr-title p {
        margin: 8px 0 0;
        color: var(--sr-muted);
        font-size: 1rem;
        line-height: 1.55;
    }

    .sr-profile-card {
        min-width: 260px;
        border-radius: 22px;
        padding: 16px 18px;
        background: #ffffff;
        border: 1px solid var(--sr-line);
        border-top: 4px solid var(--sr-purple);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
    }

    .sr-profile-card strong { display:block; color:var(--sr-text); font-size:1.05rem; }
    .sr-profile-card span { display:block; color:var(--sr-muted); margin-top:4px; font-weight:700; }

    .sr-main-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .sr-stat-card {
        min-height: 126px;
        padding: 18px;
        border-radius: 22px;
        background: #ffffff;
        border: 1px solid var(--sr-line);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
        position: relative;
        overflow: hidden;
    }

    .sr-stat-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--accent, var(--sr-blue));
    }

    .sr-stat-card::after {
        content: "";
        position: absolute;
        width: 74px;
        height: 74px;
        border-radius: 999px;
        background: color-mix(in srgb, var(--accent, var(--sr-blue)) 12%, transparent);
        right: -16px;
        top: -18px;
    }

    .sr-stat-card span {
        position: relative;
        z-index: 1;
        color: #8a9ab0;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .055em;
        font-weight: 950;
    }

    .sr-stat-card strong {
        position: relative;
        z-index: 1;
        display: block;
        margin-top: 10px;
        font-size: 2.25rem;
        line-height: 1;
        letter-spacing: -.06em;
        color: var(--accent, var(--sr-blue));
    }

    .sr-stat-card small {
        position: relative;
        z-index: 1;
        display: block;
        color: var(--sr-muted);
        margin-top: 8px;
        font-weight: 700;
    }

    .sr-stat-card--blue { --accent: var(--sr-blue); }
    .sr-stat-card--green { --accent: var(--sr-green); }
    .sr-stat-card--orange { --accent: var(--sr-orange); }
    .sr-stat-card--pink { --accent: var(--sr-pink); }

    .sr-mini-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .sr-mini-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid var(--sr-line);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .05);
    }

    .sr-mini-icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-weight: 950;
        background: var(--bg, #e0edff);
        color: var(--color, var(--sr-blue));
        flex: 0 0 46px;
    }

    .sr-mini-card strong { display:block; color:var(--sr-text); font-size:1.35rem; line-height:1; }
    .sr-mini-card span { display:block; margin-top:4px; color:var(--sr-muted); font-weight:700; }

    .sr-bottom-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 18px;
        align-items: start;
    }

    .sr-panel {
        background: #ffffff;
        border: 1px solid var(--sr-line);
        border-radius: 24px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .sr-panel__head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 18px 20px;
        border-bottom: 1px solid #eef4ff;
        background: #fbfdff;
    }

    .sr-panel__head h2 { margin:0; color:var(--sr-navy); font-size:1.2rem; letter-spacing:-.035em; }
    .sr-panel__head a { text-decoration:none; color:var(--sr-blue); font-weight:950; }

    .sr-list {
        display: grid;
        gap: 10px;
        padding: 16px;
    }

    .sr-list-row {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 14px;
        border-radius: 18px;
        background: #f8fbff;
        border: 1px solid #edf3fc;
    }

    .sr-row-icon {
        width: 44px;
        height: 44px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: #e0f2fe;
        color: var(--sr-cyan);
        font-weight: 950;
    }

    .sr-list-row strong {
        display: block;
        color: var(--sr-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sr-list-row small { display:block; color:var(--sr-muted); margin-top:4px; font-weight:700; }

    .sr-list-row a {
        text-decoration: none;
        color: #ffffff;
        background: var(--sr-navy);
        padding: 9px 13px;
        border-radius: 999px;
        font-weight: 950;
        white-space: nowrap;
    }

    .sr-actions { display:grid; gap:12px; }

    .sr-action {
        display: flex;
        gap: 14px;
        align-items: center;
        text-decoration: none;
        padding: 15px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid var(--sr-line);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .05);
    }

    .sr-action-icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        flex: 0 0 46px;
        background: var(--bg, #e0edff);
        color: var(--color, var(--sr-blue));
        font-weight: 950;
    }

    .sr-action strong { display:block; color:var(--sr-text); font-size:1rem; margin-bottom:4px; }
    .sr-action span { color:var(--sr-muted); font-weight:700; line-height:1.45; }

    .sr-empty {
        padding: 26px;
        border: 1px dashed var(--sr-line);
        border-radius: 18px;
        background: #f8fbff;
        color: var(--sr-muted);
        text-align: center;
        font-weight: 700;
    }

    html[data-theme='dark'] .student-resp-dashboard { --sr-text:#eaf2ff; --sr-muted:#9fb2ca; --sr-line:rgba(148,163,184,.22); }
    html[data-theme='dark'] .sr-topbar,
    html[data-theme='dark'] .sr-stat-card,
    html[data-theme='dark'] .sr-mini-card,
    html[data-theme='dark'] .sr-panel,
    html[data-theme='dark'] .sr-action,
    html[data-theme='dark'] .sr-profile-card { background:#0f172a; border-color:rgba(148,163,184,.22); }
    html[data-theme='dark'] .sr-panel__head,
    html[data-theme='dark'] .sr-list-row,
    html[data-theme='dark'] .sr-empty { background:rgba(15,23,42,.55); border-color:rgba(148,163,184,.18); }
    html[data-theme='dark'] .sr-title h1,
    html[data-theme='dark'] .sr-panel__head h2,
    html[data-theme='dark'] .sr-mini-card strong,
    html[data-theme='dark'] .sr-list-row strong,
    html[data-theme='dark'] .sr-action strong,
    html[data-theme='dark'] .sr-profile-card strong { color:#eaf2ff; }

    @media (max-width: 1100px) {
        .sr-main-stats, .sr-mini-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sr-bottom-grid { grid-template-columns: 1fr; }
        .sr-topbar { align-items:flex-start; flex-direction:column; }
        .sr-profile-card { min-width:0; width:100%; }
    }

    @media (max-width: 680px) {
        .sr-main-stats, .sr-mini-stats { grid-template-columns: 1fr; }
        .sr-list-row { grid-template-columns:44px minmax(0,1fr); }
        .sr-list-row a { grid-column: 1 / -1; text-align:center; }
    }
</style>
@endpush

@section('content')
<div class="student-resp-dashboard">
    <section class="sr-topbar">
        <div class="sr-title">
            <h1>Bonjour, {{ $studentName }}</h1>
            <p>Tableau de bord élève organisé comme les espaces responsables, avec un suivi rapide des cours, TD, rappels et messages.</p>
        </div>
        <div class="sr-profile-card">
            <strong>{{ $className }}</strong>
            <span>{{ $subscriptionName }} · {{ $subscriptionState }}</span>
        </div>
    </section>

    @if(!empty($studentExamCountdown))
        @include('components.exam-countdowns', ['examCountdowns' => [$studentExamCountdown], 'compact' => true])
    @endif

    <section class="sr-main-stats">
        <div class="sr-stat-card sr-stat-card--blue"><span>Cours</span><strong>{{ $allCoursesCount }}</strong><small>disponibles</small></div>
        <div class="sr-stat-card sr-stat-card--green"><span>TD ouverts</span><strong>{{ $tdOpenedCount }}</strong><small>sur {{ $allTdCount }} TD</small></div>
        <div class="sr-stat-card sr-stat-card--orange"><span>Progression</span><strong>{{ $progress }}%</strong><small>estimation actuelle</small></div>
        <div class="sr-stat-card sr-stat-card--pink"><span>Rappels</span><strong>{{ $pendingCount }}</strong><small>à consulter</small></div>
    </section>

    <section class="sr-mini-stats">
        <div class="sr-mini-card"><div class="sr-mini-icon" style="--bg:#e0edff;--color:#1d4ed8;">▭</div><div><strong>{{ $allCoursesCount }}</strong><span>cours publiés</span></div></div>
        <div class="sr-mini-card"><div class="sr-mini-icon" style="--bg:#dcfce7;--color:#059669;">✓</div><div><strong>{{ $tdCompletedCount }}</strong><span>TD terminés</span></div></div>
        <div class="sr-mini-card"><div class="sr-mini-icon" style="--bg:#ffedd5;--color:#ea580c;">!</div><div><strong>{{ max(0, $allTdCount - $tdOpenedCount) }}</strong><span>TD non ouverts</span></div></div>
        <div class="sr-mini-card"><div class="sr-mini-icon" style="--bg:#fce7f3;--color:#db2777;">◉</div><div><strong>{{ $subscriptionState }}</strong><span>abonnement</span></div></div>
    </section>

    <section class="sr-bottom-grid">
        <div class="sr-panel">
            <div class="sr-panel__head">
                <h2>Derniers contenus</h2>
                <a href="{{ $coursesUrl }}">Voir tout</a>
            </div>
            <div class="sr-list">
                @forelse($items as $item)
                    <div class="sr-list-row">
                        <div class="sr-row-icon">{{ $item['icon'] }}</div>
                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            <small>{{ $item['type'] }} · {{ $item['subject'] }} @if($item['date']) · {{ optional($item['date'])->diffForHumans() }} @endif</small>
                        </div>
                        <a href="{{ $item['url'] }}">Ouvrir</a>
                    </div>
                @empty
                    <div class="sr-empty">Aucun cours ou TD récent pour votre classe.</div>
                @endforelse
            </div>
        </div>

        <aside class="sr-actions">
            <a href="{{ $coursesUrl }}" class="sr-action"><div class="sr-action-icon" style="--bg:#e0edff;--color:#1d4ed8;">▭</div><div><strong>Mes cours</strong><span>Consulter les cours publiés.</span></div></a>
            <a href="{{ $tdUrl }}" class="sr-action"><div class="sr-action-icon" style="--bg:#dcfce7;--color:#059669;">☑</div><div><strong>Mes TD</strong><span>Traiter les TD et voir les corrigés disponibles.</span></div></a>
            <a href="{{ $messagesUrl }}" class="sr-action"><div class="sr-action-icon" style="--bg:#fce7f3;--color:#db2777;">◌</div><div><strong>Messagerie</strong><span>Poser une question à un enseignant.</span></div></a>
            <a href="{{ $subscriptionUrl }}" class="sr-action"><div class="sr-action-icon" style="--bg:#ffedd5;--color:#ea580c;">★</div><div><strong>Abonnement</strong><span>Vérifier l’accès aux contenus.</span></div></a>
        </aside>
    </section>
</div>
@endsection
