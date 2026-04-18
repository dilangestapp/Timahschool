@extends('layouts.student')

@section('title', 'Tableau de bord')

@php
    $studentName = auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username ?? 'Élève';
    $className = $studentProfile->schoolClass->name ?? 'Classe non définie';
    $isSubscriptionActive = $subscription && $subscription->isActive();
    $subscriptionState = $isSubscriptionActive ? 'Actif' : 'Inactif';
    $subscriptionSymbol = $isSubscriptionActive ? '✓' : '!';
    $courseCount = $recentCourses->count();
    $tdCount = $recentTdSets->count();
    $progressPercent = min(100, ($tdOpenedCount * 20));

    $subjectInitials = function ($value) {
        $text = trim((string) $value);

        if ($text === '') {
            return 'TD';
        }

        $parts = preg_split('/\s+/', $text) ?: [];
        $letters = collect($parts)
            ->filter()
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        return $letters !== '' ? $letters : mb_strtoupper(mb_substr($text, 0, 2));
    };

    $circumference = 2 * pi() * 42;
    $progressStroke = max(0, $circumference - (($progressPercent / 100) * $circumference));

    $weeklyBars = [
        ['label' => 'Lu', 'value' => max(12, min(92, 18 + ($tdOpenedCount * 8)))],
        ['label' => 'Ma', 'value' => max(16, min(94, 24 + ($courseCount * 11)))],
        ['label' => 'Me', 'value' => max(18, min(96, 28 + ($progressPercent * 0.55)))],
        ['label' => 'Je', 'value' => max(14, min(88, 20 + ($tdCount * 12)))],
        ['label' => 'Ve', 'value' => max(16, min(92, 22 + ($tdOpenedCount * 10)))],
        ['label' => 'Sa', 'value' => max(10, min(80, 14 + ($courseCount * 8)))],
        ['label' => 'Di', 'value' => max(8, min(72, 10 + ($tdCount * 7)))],
    ];

    $uniqueSubjects = collect($recentTdSets)
        ->map(function ($td, $index) {
            $name = $td->subject->name ?? 'Matière';
            return [
                'name' => $name,
                'color' => $td->subject->color ?? ['#2563eb', '#f59e0b', '#16a34a', '#7c3aed', '#ef4444'][$index % 5],
            ];
        })
        ->unique('name')
        ->values();

    $subjectInsights = $uniqueSubjects->take(4)->values()->map(function ($subject, $index) use ($tdCount, $courseCount, $progressPercent) {
        $value = match ($index) {
            0 => max(42, min(94, 48 + ($progressPercent * 0.42))),
            1 => max(34, min(88, 40 + ($tdCount * 12))),
            2 => max(30, min(82, 34 + ($courseCount * 10))),
            default => max(28, min(76, 30 + ($tdCount * 8))),
        };

        return [
            'name' => $subject['name'],
            'color' => $subject['color'],
            'value' => (int) round($value),
        ];
    });

    if ($subjectInsights->isEmpty()) {
        $subjectInsights = collect([
            ['name' => 'Mathématiques', 'color' => '#2563eb', 'value' => max(40, $progressPercent)],
            ['name' => 'Français', 'color' => '#16a34a', 'value' => max(34, min(78, $progressPercent + 8))],
            ['name' => 'Physique', 'color' => '#f59e0b', 'value' => max(28, min(72, $progressPercent + 2))],
        ]);
    }

    $trendCards = [
        [
            'title' => 'Rythme',
            'value' => max(1, $tdOpenedCount),
            'suffix' => 'TD ouverts',
            'tone' => 'blue',
            'icon' => '⚡',
        ],
        [
            'title' => 'Focus',
            'value' => max(1, $courseCount),
            'suffix' => 'cours utiles',
            'tone' => 'green',
            'icon' => '🎯',
        ],
        [
            'title' => 'Élan',
            'value' => max(1, $tdCount),
            'suffix' => 'TD disponibles',
            'tone' => 'violet',
            'icon' => '🚀',
        ],
    ];
@endphp

@push('styles')
<style>
    .student-dashboard-wow {
        display: grid;
        gap: 22px;
    }

    .student-dashboard-wow .hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        border: 1px solid rgba(255,255,255,.08);
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.20), transparent 28%),
            radial-gradient(circle at 15% 100%, rgba(56, 189, 248, 0.15), transparent 34%),
            linear-gradient(135deg, #0f172a 0%, #172554 44%, #1d4ed8 100%);
        color: #fff;
        padding: 28px;
        box-shadow: var(--shadow-lg);
    }

    .student-dashboard-wow .hero::before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        top: -100px;
        right: -70px;
    }

    .student-dashboard-wow .hero::after {
        content: "";
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        bottom: -40px;
        left: -20px;
    }

    .student-dashboard-wow .hero__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.06fr .94fr;
        gap: 20px;
        align-items: stretch;
    }

    .student-dashboard-wow .hero__left,
    .student-dashboard-wow .hero__right {
        display: grid;
        gap: 16px;
    }

    .student-dashboard-wow .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        width: fit-content;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.16);
        background: rgba(255,255,255,.10);
        font-size: .84rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .student-dashboard-wow .hero-title {
        margin: 0;
        font-size: clamp(2rem, 3.8vw, 3.8rem);
        line-height: .98;
        letter-spacing: -0.05em;
        max-width: 11ch;
    }

    .student-dashboard-wow .hero-title span {
        display: block;
        color: #dbeafe;
    }

    .student-dashboard-wow .hero-text {
        margin: 0;
        color: rgba(255,255,255,.84);
        line-height: 1.75;
        font-size: 1rem;
        max-width: 62ch;
    }

    .student-dashboard-wow .hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-dashboard-wow .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        color: #eef6ff;
        font-size: .84rem;
        font-weight: 800;
    }

    .student-dashboard-wow .hero-panels {
        display: grid;
        gap: 14px;
    }

    .student-dashboard-wow .hero-panel,
    .student-dashboard-wow .hero-status {
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        padding: 18px;
        backdrop-filter: blur(10px);
    }

    .student-dashboard-wow .hero-panel strong {
        display: block;
        margin-bottom: 8px;
        font-size: 1.05rem;
        letter-spacing: -0.02em;
    }

    .student-dashboard-wow .hero-panel p {
        margin: 0;
        color: rgba(255,255,255,.78);
        font-size: .94rem;
        line-height: 1.65;
    }

    .student-dashboard-wow .hero-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .student-dashboard-wow .hero-status__left {
        display: grid;
        gap: 4px;
    }

    .student-dashboard-wow .hero-status__left span {
        color: rgba(255,255,255,.76);
        font-size: .88rem;
    }

    .student-dashboard-wow .hero-status__left strong {
        font-size: 1.08rem;
        letter-spacing: -0.02em;
    }

    .student-dashboard-wow .hero-status__badge {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.14);
        color: #fff;
        font-size: 1.35rem;
        font-weight: 900;
        flex: 0 0 58px;
    }

    .student-dashboard-wow .wow-grid {
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 18px;
        align-items: stretch;
    }

    .student-dashboard-wow .wow-card,
    .student-dashboard-wow .action-card,
    .student-dashboard-wow .stat-card,
    .student-dashboard-wow .panel,
    .student-dashboard-wow .side-card,
    .student-dashboard-wow .shortcut-card {
        border: 1px solid var(--line);
        border-radius: 28px;
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-wow .wow-card:hover,
    .student-dashboard-wow .action-card:hover,
    .student-dashboard-wow .stat-card:hover,
    .student-dashboard-wow .panel:hover,
    .student-dashboard-wow .side-card:hover,
    .student-dashboard-wow .shortcut-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .student-dashboard-wow .wow-card {
        overflow: hidden;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-wow .wow-card--progress {
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.10), transparent 30%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-wow .wow-card--activity {
        background:
            radial-gradient(circle at top right, rgba(245,158,11,.12), transparent 30%),
            linear-gradient(180deg, #fffdf8, #fff9ee);
        border-color: #f4dfb4;
    }

    html[data-theme='dark'] .student-dashboard-wow .wow-card--activity {
        background:
            radial-gradient(circle at top right, rgba(245,158,11,.10), transparent 30%),
            linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245,158,11,.22);
    }

    .student-dashboard-wow .wow-card__head {
        padding: 22px 22px 16px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line);
    }

    .student-dashboard-wow .wow-card__head h2 {
        margin: 0;
        font-size: 1.26rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-wow .wow-card__head p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
        font-size: .92rem;
    }

    .student-dashboard-wow .wow-card__body {
        padding: 20px 22px 22px;
    }

    .student-dashboard-wow .progress-layout {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 20px;
        align-items: center;
    }

    .student-dashboard-wow .ring-wrap {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
    }

    .student-dashboard-wow .ring-svg {
        width: 160px;
        height: 160px;
        transform: rotate(-90deg);
    }

    .student-dashboard-wow .ring-bg {
        fill: none;
        stroke: rgba(37,99,235,.10);
        stroke-width: 12;
    }

    .student-dashboard-wow .ring-fill {
        fill: none;
        stroke: url(#ringGradient);
        stroke-width: 12;
        stroke-linecap: round;
        stroke-dasharray: {{ $circumference }};
        stroke-dashoffset: {{ $progressStroke }};
        transition: stroke-dashoffset .6s ease;
    }

    .student-dashboard-wow .ring-center {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 2px;
        text-align: center;
    }

    .student-dashboard-wow .ring-center strong {
        font-size: 2rem;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .student-dashboard-wow .ring-center span {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 700;
    }

    .student-dashboard-wow .insight-list {
        display: grid;
        gap: 12px;
    }

    .student-dashboard-wow .insight-row {
        display: grid;
        gap: 8px;
    }

    .student-dashboard-wow .insight-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .student-dashboard-wow .insight-top strong {
        font-size: .95rem;
        letter-spacing: -0.02em;
    }

    .student-dashboard-wow .insight-top span {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 700;
    }

    .student-dashboard-wow .bar-track {
        height: 10px;
        border-radius: 999px;
        background: rgba(37,99,235,.08);
        overflow: hidden;
    }

    .student-dashboard-wow .bar-fill {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .student-dashboard-wow .activity-layout {
        display: grid;
        gap: 18px;
    }

    .student-dashboard-wow .activity-bars {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 12px;
        align-items: end;
        min-height: 180px;
    }

    .student-dashboard-wow .activity-bar {
        display: grid;
        justify-items: center;
        gap: 10px;
    }

    .student-dashboard-wow .activity-bar__stick {
        width: 100%;
        max-width: 34px;
        height: 150px;
        display: flex;
        align-items: end;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    html[data-theme='dark'] .student-dashboard-wow .activity-bar__stick {
        background: rgba(255,255,255,.08);
    }

    .student-dashboard-wow .activity-bar__fill {
        width: 100%;
        border-radius: inherit;
        background: linear-gradient(180deg, #f59e0b, #f97316);
    }

    .student-dashboard-wow .activity-bar:nth-child(2) .activity-bar__fill,
    .student-dashboard-wow .activity-bar:nth-child(5) .activity-bar__fill {
        background: linear-gradient(180deg, #2563eb, #4f46e5);
    }

    .student-dashboard-wow .activity-bar:nth-child(3) .activity-bar__fill,
    .student-dashboard-wow .activity-bar:nth-child(7) .activity-bar__fill {
        background: linear-gradient(180deg, #16a34a, #22c55e);
    }

    .student-dashboard-wow .activity-bar__value {
        font-size: .78rem;
        font-weight: 800;
        color: var(--text);
    }

    .student-dashboard-wow .activity-bar__label {
        font-size: .78rem;
        font-weight: 800;
        color: var(--muted);
    }

    .student-dashboard-wow .trend-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .student-dashboard-wow .trend-card {
        padding: 16px;
        border-radius: 20px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.56);
        display: grid;
        gap: 8px;
    }

    html[data-theme='dark'] .student-dashboard-wow .trend-card {
        background: rgba(15, 23, 42, 0.18);
    }

    .student-dashboard-wow .trend-card__icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .student-dashboard-wow .trend-card--blue .trend-card__icon {
        background: rgba(37,99,235,.12);
    }

    .student-dashboard-wow .trend-card--green .trend-card__icon {
        background: rgba(22,163,74,.12);
    }

    .student-dashboard-wow .trend-card--violet .trend-card__icon {
        background: rgba(124,58,237,.12);
    }

    .student-dashboard-wow .trend-card strong {
        font-size: 1.35rem;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .student-dashboard-wow .trend-card span {
        color: var(--muted);
        font-size: .84rem;
        line-height: 1.5;
    }

    .student-dashboard-wow .actions-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .student-dashboard-wow .action-card {
        position: relative;
        overflow: hidden;
        min-height: 154px;
        padding: 18px;
        display: grid;
        gap: 12px;
    }

    .student-dashboard-wow .action-card::before {
        content: "";
        position: absolute;
        width: 96px;
        height: 96px;
        border-radius: 999px;
        top: -26px;
        right: -18px;
        opacity: .8;
    }

    .student-dashboard-wow .action-card--courses {
        background: linear-gradient(180deg, #ffffff, #f6faff);
        border-color: #d8e6fb;
    }

    .student-dashboard-wow .action-card--courses::before {
        background: rgba(37, 99, 235, 0.10);
    }

    .student-dashboard-wow .action-card--td {
        background: linear-gradient(180deg, #fffdf6, #fff7e8);
        border-color: #f6dfae;
    }

    .student-dashboard-wow .action-card--td::before {
        background: rgba(245, 158, 11, 0.16);
    }

    .student-dashboard-wow .action-card--messages {
        background: linear-gradient(180deg, #f7fcfb, #ebfaf5);
        border-color: #bde9d8;
    }

    .student-dashboard-wow .action-card--messages::before {
        background: rgba(22, 163, 74, 0.14);
    }

    .student-dashboard-wow .action-card--subscription {
        background: linear-gradient(180deg, #faf7ff, #f3ecff);
        border-color: #dac8ff;
    }

    .student-dashboard-wow .action-card--subscription::before {
        background: rgba(124, 58, 237, 0.14);
    }

    html[data-theme='dark'] .student-dashboard-wow .action-card--courses {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110, 161, 255, 0.18);
    }

    html[data-theme='dark'] .student-dashboard-wow .action-card--td {
        background: linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245, 158, 11, 0.22);
    }

    html[data-theme='dark'] .student-dashboard-wow .action-card--messages {
        background: linear-gradient(180deg, #11231f, #153029);
        border-color: rgba(22, 163, 74, 0.20);
    }

    html[data-theme='dark'] .student-dashboard-wow .action-card--subscription {
        background: linear-gradient(180deg, #1c1630, #251c3d);
        border-color: rgba(124, 58, 237, 0.22);
    }

    .student-dashboard-wow .action-card__icon {
        width: 50px;
        height: 50px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        position: relative;
        z-index: 1;
    }

    .student-dashboard-wow .action-card--courses .action-card__icon {
        background: rgba(37,99,235,.10);
    }

    .student-dashboard-wow .action-card--td .action-card__icon {
        background: rgba(245,158,11,.14);
    }

    .student-dashboard-wow .action-card--messages .action-card__icon {
        background: rgba(22,163,74,.12);
    }

    .student-dashboard-wow .action-card--subscription .action-card__icon {
        background: rgba(124,58,237,.12);
    }

    .student-dashboard-wow .action-card h3 {
        margin: 0;
        font-size: 1.04rem;
        letter-spacing: -0.02em;
        color: var(--text);
        position: relative;
        z-index: 1;
    }

    .student-dashboard-wow .action-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
        font-size: .9rem;
        position: relative;
        z-index: 1;
    }

    .student-dashboard-wow .action-card span {
        margin-top: auto;
        font-weight: 800;
        font-size: .92rem;
        position: relative;
        z-index: 1;
    }

    .student-dashboard-wow .action-card--courses span {
        color: #2563eb;
    }

    .student-dashboard-wow .action-card--td span {
        color: #d97706;
    }

    .student-dashboard-wow .action-card--messages span {
        color: #15803d;
    }

    .student-dashboard-wow .action-card--subscription span {
        color: #7c3aed;
    }

    .student-dashboard-wow .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .student-dashboard-wow .stat-card {
        padding: 20px;
        display: grid;
        gap: 14px;
    }

    .student-dashboard-wow .stat-card--neutral {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-wow .stat-card--blue {
        background: linear-gradient(180deg, #f6faff, #eef5ff);
        border-color: #d8e6fb;
    }

    .student-dashboard-wow .stat-card--amber {
        background: linear-gradient(180deg, #fffdf7, #fff8eb);
        border-color: #f5dfaf;
    }

    .student-dashboard-wow .stat-card--green {
        background: linear-gradient(180deg, #f7fcfa, #edf9f2);
        border-color: #c7ead7;
    }

    html[data-theme='dark'] .student-dashboard-wow .stat-card--neutral {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    html[data-theme='dark'] .student-dashboard-wow .stat-card--blue {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110, 161, 255, 0.18);
    }

    html[data-theme='dark'] .student-dashboard-wow .stat-card--amber {
        background: linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245, 158, 11, 0.22);
    }

    html[data-theme='dark'] .student-dashboard-wow .stat-card--green {
        background: linear-gradient(180deg, #11231f, #153029);
        border-color: rgba(22, 163, 74, 0.20);
    }

    .student-dashboard-wow .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .student-dashboard-wow .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex: 0 0 52px;
    }

    .student-dashboard-wow .stat-card--neutral .stat-icon {
        background: rgba(37,99,235,.10);
    }

    .student-dashboard-wow .stat-card--blue .stat-icon {
        background: rgba(37,99,235,.12);
    }

    .student-dashboard-wow .stat-card--amber .stat-icon {
        background: rgba(245,158,11,.14);
    }

    .student-dashboard-wow .stat-card--green .stat-icon {
        background: rgba(22,163,74,.12);
    }

    .student-dashboard-wow .stat-label {
        display: block;
        font-size: .94rem;
        font-weight: 800;
        color: var(--text);
    }

    .student-dashboard-wow .stat-value {
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: var(--text);
    }

    .student-dashboard-wow .stat-note {
        color: var(--muted);
        font-size: .88rem;
        line-height: 1.6;
    }

    .student-dashboard-wow .main-grid {
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 18px;
        align-items: start;
    }

    .student-dashboard-wow .panel {
        overflow: hidden;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-wow .panel__head {
        padding: 22px 22px 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line);
        background: rgba(37, 99, 235, 0.03);
    }

    .student-dashboard-wow .panel__head h2 {
        margin: 0;
        font-size: 1.45rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-wow .panel__head p,
    .student-dashboard-wow .panel__head span {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
    }

    .student-dashboard-wow .panel__list {
        display: grid;
    }

    .student-dashboard-wow .panel__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, transparent, transparent);
    }

    .student-dashboard-wow .panel__item:nth-child(odd) {
        background: rgba(37, 99, 235, 0.02);
    }

    .student-dashboard-wow .panel__item:last-child {
        border-bottom: 0;
    }

    .student-dashboard-wow .panel__item-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .student-dashboard-wow .subject-mark {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 900;
        flex: 0 0 48px;
        box-shadow: var(--shadow-xs);
    }

    .student-dashboard-wow .panel__item-text {
        min-width: 0;
        display: grid;
        gap: 2px;
    }

    .student-dashboard-wow .panel__item-text strong {
        font-size: 1rem;
        line-height: 1.35;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-dashboard-wow .panel__item-text strong a {
        color: inherit;
    }

    .student-dashboard-wow .panel__item-text span {
        color: var(--muted);
        font-size: .88rem;
    }

    .student-dashboard-wow .tag {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        font-size: .82rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .student-dashboard-wow .tag--premium {
        background: rgba(124,58,237,.10);
        color: #7c3aed;
        border-color: rgba(124,58,237,.18);
    }

    .student-dashboard-wow .tag--free {
        background: rgba(22,163,74,.10);
        color: #15803d;
        border-color: rgba(22,163,74,.18);
    }

    .student-dashboard-wow .side {
        display: grid;
        gap: 18px;
    }

    .student-dashboard-wow .side-card {
        padding: 22px;
        display: grid;
        gap: 14px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-wow .side-card--soft-blue {
        background: linear-gradient(180deg, #f6faff, #eef5ff);
        border-color: #d8e6fb;
    }

    .student-dashboard-wow .side-card--soft-violet {
        background: linear-gradient(180deg, #faf7ff, #f3ecff);
        border-color: #dac8ff;
    }

    html[data-theme='dark'] .student-dashboard-wow .side-card--soft-blue {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110, 161, 255, 0.18);
    }

    html[data-theme='dark'] .student-dashboard-wow .side-card--soft-violet {
        background: linear-gradient(180deg, #1c1630, #251c3d);
        border-color: rgba(124, 58, 237, 0.22);
    }

    .student-dashboard-wow .side-card h3 {
        margin: 0;
        font-size: 1.15rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-wow .side-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .student-dashboard-wow .side-list {
        display: grid;
        gap: 10px;
    }

    .student-dashboard-wow .side-list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.56);
    }

    html[data-theme='dark'] .student-dashboard-wow .side-list div {
        background: rgba(15, 23, 42, 0.22);
    }

    .student-dashboard-wow .side-list div strong {
        font-size: .95rem;
    }

    .student-dashboard-wow .side-list div span {
        color: var(--muted);
        font-size: .88rem;
    }

    .student-dashboard-wow .side-note {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.54);
        color: var(--muted);
        line-height: 1.7;
        font-size: .9rem;
    }

    html[data-theme='dark'] .student-dashboard-wow .side-note {
        background: rgba(15, 23, 42, 0.22);
    }

    .student-dashboard-wow .shortcuts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .student-dashboard-wow .shortcut-card {
        padding: 22px;
        display: grid;
        gap: 14px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-wow .shortcut-card--courses {
        background: linear-gradient(180deg, #f7fcfb, #ecfaf4);
        border-color: #c7ead7;
    }

    .student-dashboard-wow .shortcut-card--messages {
        background: linear-gradient(180deg, #fffdf7, #fff7eb);
        border-color: #f5dfaf;
    }

    html[data-theme='dark'] .student-dashboard-wow .shortcut-card--courses {
        background: linear-gradient(180deg, #11231f, #153029);
        border-color: rgba(22, 163, 74, 0.20);
    }

    html[data-theme='dark'] .student-dashboard-wow .shortcut-card--messages {
        background: linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245, 158, 11, 0.22);
    }

    .student-dashboard-wow .shortcut-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .student-dashboard-wow .shortcut-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.46);
        font-size: 1.35rem;
        flex: 0 0 54px;
    }

    html[data-theme='dark'] .student-dashboard-wow .shortcut-icon {
        background: rgba(255,255,255,.08);
    }

    .student-dashboard-wow .shortcut-card h3 {
        margin: 0;
        font-size: 1.2rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-wow .shortcut-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .student-dashboard-wow .shortcut-link {
        font-weight: 800;
    }

    .student-dashboard-wow .shortcut-card--courses .shortcut-link {
        color: #15803d;
    }

    .student-dashboard-wow .shortcut-card--messages .shortcut-link {
        color: #d97706;
    }

    .student-dashboard-wow .empty-state {
        padding: 20px 22px;
        color: var(--muted);
        line-height: 1.7;
    }

    @media (max-width: 1180px) {
        .student-dashboard-wow .hero__grid,
        .student-dashboard-wow .wow-grid,
        .student-dashboard-wow .main-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-wow .actions-grid,
        .student-dashboard-wow .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .student-dashboard-wow {
            gap: 18px;
        }

        .student-dashboard-wow .hero,
        .student-dashboard-wow .wow-card,
        .student-dashboard-wow .action-card,
        .student-dashboard-wow .stat-card,
        .student-dashboard-wow .side-card,
        .student-dashboard-wow .shortcut-card {
            border-radius: 22px;
        }

        .student-dashboard-wow .hero {
            padding: 18px 14px;
        }

        .student-dashboard-wow .hero__left {
            gap: 12px;
        }

        .student-dashboard-wow .hero__right {
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
        }

        .student-dashboard-wow .hero-status {
            grid-column: 1 / -1;
            padding: 14px 16px;
        }

        .student-dashboard-wow .hero-panel {
            padding: 14px;
        }

        .student-dashboard-wow .hero-panel strong {
            margin-bottom: 6px;
            font-size: .92rem;
        }

        .student-dashboard-wow .hero-panel p {
            font-size: .82rem;
            line-height: 1.45;
        }

        .student-dashboard-wow .hero-status__left span {
            font-size: .8rem;
        }

        .student-dashboard-wow .hero-status__left strong {
            font-size: .96rem;
        }

        .student-dashboard-wow .hero-status__badge {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            flex-basis: 48px;
            font-size: 1.15rem;
        }

        .student-dashboard-wow .hero-title {
            max-width: 8ch;
            font-size: clamp(1.7rem, 9vw, 2.45rem);
            line-height: 1;
        }

        .student-dashboard-wow .hero-text {
            font-size: .9rem;
            line-height: 1.6;
        }

        .student-dashboard-wow .hero-badge,
        .student-dashboard-wow .hero-pill {
            min-height: 32px;
            font-size: .76rem;
            padding: 0 10px;
        }

        .student-dashboard-wow .progress-layout {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .student-dashboard-wow .activity-bars {
            min-height: 150px;
            gap: 8px;
        }

        .student-dashboard-wow .trend-grid,
        .student-dashboard-wow .actions-grid,
        .student-dashboard-wow .stats-grid,
        .student-dashboard-wow .shortcuts-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-wow .panel__head,
        .student-dashboard-wow .wow-card__head,
        .student-dashboard-wow .wow-card__body,
        .student-dashboard-wow .panel__item,
        .student-dashboard-wow .side-card,
        .student-dashboard-wow .shortcut-card {
            padding-left: 16px;
            padding-right: 16px;
        }

        .student-dashboard-wow .panel__item {
            align-items: flex-start;
            flex-direction: column;
        }

        .student-dashboard-wow .panel__item-left {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .student-dashboard-wow .hero {
            padding: 16px 12px;
        }

        .student-dashboard-wow .hero__grid,
        .student-dashboard-wow .wow-grid {
            gap: 14px;
        }

        .student-dashboard-wow .hero__right {
            gap: 8px;
        }

        .student-dashboard-wow .hero-title {
            font-size: 1.62rem;
            max-width: 8ch;
        }

        .student-dashboard-wow .hero-text {
            font-size: .86rem;
            line-height: 1.55;
        }

        .student-dashboard-wow .hero-badge {
            min-height: 30px;
            padding: 0 10px;
            font-size: .72rem;
        }

        .student-dashboard-wow .hero-pill {
            min-height: 30px;
            padding: 0 9px;
            font-size: .72rem;
        }

        .student-dashboard-wow .hero-panel,
        .student-dashboard-wow .hero-status {
            border-radius: 18px;
        }

        .student-dashboard-wow .hero-panel {
            padding: 12px;
        }

        .student-dashboard-wow .hero-panel strong {
            font-size: .88rem;
        }

        .student-dashboard-wow .hero-panel p {
            font-size: .78rem;
            line-height: 1.4;
        }

        .student-dashboard-wow .hero-status {
            padding: 12px 14px;
        }

        .student-dashboard-wow .hero-status__badge {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            flex-basis: 42px;
            font-size: 1rem;
        }

        .student-dashboard-wow .ring-wrap,
        .student-dashboard-wow .ring-svg {
            width: 132px;
            height: 132px;
        }

        .student-dashboard-wow .trend-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-wow .panel__head,
        .student-dashboard-wow .wow-card__head,
        .student-dashboard-wow .wow-card__body,
        .student-dashboard-wow .panel__item,
        .student-dashboard-wow .side-card,
        .student-dashboard-wow .shortcut-card {
            padding-left: 14px;
            padding-right: 14px;
        }

        .student-dashboard-wow .action-card,
        .student-dashboard-wow .stat-card {
            padding: 16px;
        }

        .student-dashboard-wow .subject-mark,
        .student-dashboard-wow .shortcut-icon,
        .student-dashboard-wow .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            flex-basis: 46px;
        }

        .student-dashboard-wow .stat-value {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 380px) {
        .student-dashboard-wow .hero__right {
            grid-template-columns: 1fr;
        }

        .student-dashboard-wow .hero-status {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="student-dashboard-wow">
    <section class="hero">
        <div class="hero__grid">
            <div class="hero__left">
                <span class="hero-badge">✨ Tableau de bord élève</span>

                <h1 class="hero-title">
                    Bonjour, {{ $studentName }}
                    <span>prêt à continuer ? 👋</span>
                </h1>

                <p class="hero-text">
                    Retrouvez votre classe, vos TD, vos cours et les repères essentiels dans un espace
                    plus clair, plus attractif et mieux organisé pour votre progression.
                </p>

                <div class="hero-pills">
                    <span class="hero-pill">Classe : {{ $className }}</span>
                    <span class="hero-pill">TD ouverts : {{ $tdOpenedCount }}</span>
                    <span class="hero-pill">Progression estimée : {{ $progressPercent }}%</span>
                </div>
            </div>

            <div class="hero__right">
                <div class="hero-status">
                    <div class="hero-status__left">
                        <span>État de l’abonnement</span>
                        <strong>{{ $subscriptionState }}</strong>
                    </div>
                    <div class="hero-status__badge">{{ $subscriptionSymbol }}</div>
                </div>

                <article class="hero-panel">
                    <strong>Votre espace</strong>
                    <p>TD, cours et messages accessibles rapidement.</p>
                </article>

                <article class="hero-panel">
                    <strong>Objectif</strong>
                    <p>Continuer vos TD récents et garder le rythme.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="wow-grid">
        <article class="wow-card wow-card--progress">
            <div class="wow-card__head">
                <div>
                    <h2>Radar de progression</h2>
                    <p>Un repère visuel fort pour donner plus de présence au tableau de bord.</p>
                </div>
            </div>

            <div class="wow-card__body">
                <div class="progress-layout">
                    <div class="ring-wrap">
                        <svg class="ring-svg" viewBox="0 0 120 120">
                            <defs>
                                <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#2563eb"></stop>
                                    <stop offset="60%" stop-color="#4f46e5"></stop>
                                    <stop offset="100%" stop-color="#22c55e"></stop>
                                </linearGradient>
                            </defs>
                            <circle class="ring-bg" cx="60" cy="60" r="42"></circle>
                            <circle class="ring-fill" cx="60" cy="60" r="42"></circle>
                        </svg>

                        <div class="ring-center">
                            <strong>{{ $progressPercent }}%</strong>
                            <span>progression</span>
                        </div>
                    </div>

                    <div class="insight-list">
                        @foreach ($subjectInsights as $subject)
                            <div class="insight-row">
                                <div class="insight-top">
                                    <strong>{{ $subject['name'] }}</strong>
                                    <span>{{ $subject['value'] }}%</span>
                                </div>

                                <div class="bar-track">
                                    <span class="bar-fill" style="width: {{ $subject['value'] }}%; background: linear-gradient(90deg, {{ $subject['color'] }}, {{ $subject['color'] }}cc);"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </article>

        <article class="wow-card wow-card--activity">
            <div class="wow-card__head">
                <div>
                    <h2>Activité de la semaine</h2>
                    <p>Une vraie touche visuelle pour renforcer l’effet dashboard moderne.</p>
                </div>
            </div>

            <div class="wow-card__body">
                <div class="activity-layout">
                    <div class="activity-bars">
                        @foreach ($weeklyBars as $bar)
                            <div class="activity-bar">
                                <div class="activity-bar__stick">
                                    <div class="activity-bar__fill" style="height: {{ $bar['value'] }}%;"></div>
                                </div>
                                <div class="activity-bar__value">{{ $bar['value'] }}</div>
                                <div class="activity-bar__label">{{ $bar['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="trend-grid">
                        @foreach ($trendCards as $card)
                            <div class="trend-card trend-card--{{ $card['tone'] }}">
                                <span class="trend-card__icon">{{ $card['icon'] }}</span>
                                <strong>{{ $card['value'] }}</strong>
                                <span>{{ $card['suffix'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="actions-grid">
        <a href="{{ route('student.courses.index') }}" class="action-card action-card--courses">
            <span class="action-card__icon">📘</span>
            <h3>Mes cours</h3>
            <p>Retrouvez vos contenus et reprenez votre parcours d’apprentissage.</p>
            <span>Voir mes cours</span>
        </a>

        <a href="{{ route('student.td.index') }}" class="action-card action-card--td">
            <span class="action-card__icon">📝</span>
            <h3>Mes TD</h3>
            <p>Accédez aux TD disponibles, aux corrigés et aux publications récentes.</p>
            <span>Accéder à mes TD</span>
        </a>

        <a href="{{ route('student.messages.create') }}" class="action-card action-card--messages">
            <span class="action-card__icon">💬</span>
            <h3>Poser une question</h3>
            <p>Écrivez à votre enseignant ou demandez de l’aide sur un TD.</p>
            <span>Ouvrir la messagerie</span>
        </a>

        <a href="{{ route('student.subscription.index') }}" class="action-card action-card--subscription">
            <span class="action-card__icon">💳</span>
            <h3>Mon abonnement</h3>
            <p>Vérifiez votre accès, votre formule et les options disponibles.</p>
            <span>Gérer mon abonnement</span>
        </a>
    </section>

    <section class="stats-grid">
        <article class="stat-card stat-card--neutral">
            <div class="stat-top">
                <div>
                    <span class="stat-label">Cours disponibles</span>
                    <div class="stat-value">{{ $courseCount }}</div>
                </div>
                <span class="stat-icon">📚</span>
            </div>
            <div class="stat-note">Vos contenus récents et accessibles depuis votre classe.</div>
        </article>

        <article class="stat-card stat-card--blue">
            <div class="stat-top">
                <div>
                    <span class="stat-label">TD disponibles</span>
                    <div class="stat-value">{{ $tdCount }}</div>
                </div>
                <span class="stat-icon">📝</span>
            </div>
            <div class="stat-note">Travaux dirigés publiés récemment pour votre classe.</div>
        </article>

        <article class="stat-card stat-card--amber">
            <div class="stat-top">
                <div>
                    <span class="stat-label">TD ouverts</span>
                    <div class="stat-value">{{ $tdOpenedCount }}</div>
                </div>
                <span class="stat-icon">📂</span>
            </div>
            <div class="stat-note">Éléments déjà consultés pour poursuivre sans perdre le fil.</div>
        </article>

        <article class="stat-card stat-card--green">
            <div class="stat-top">
                <div>
                    <span class="stat-label">Progression</span>
                    <div class="stat-value">{{ $progressPercent }}%</div>
                </div>
                <span class="stat-icon">📈</span>
            </div>
            <div class="stat-note">Indication visuelle simple de votre activité récente.</div>
        </article>
    </section>

    <section class="main-grid">
        <section class="panel">
            <div class="panel__head">
                <div>
                    <h2>TD récents</h2>
                    <p>Les dernières publications de votre classe, prêtes à être consultées.</p>
                </div>
                <span>Dernières activités</span>
            </div>

            <div class="panel__list">
                @forelse ($recentTdSets as $td)
                    @php
                        $subjectName = $td->subject->name ?? 'Matière';
                        $initials = $td->subject->initials ?? $subjectInitials($subjectName);
                        $subjectColor = $td->subject->color ?? '#4F46E5';
                        $isPremium = $td->access_level === 'premium';
                    @endphp

                    <div class="panel__item">
                        <div class="panel__item-left">
                            <div class="subject-mark" style="background-color: {{ $subjectColor }};">
                                {{ $initials }}
                            </div>

                            <div class="panel__item-text">
                                <strong>
                                    <a href="{{ route('student.td.show', $td) }}">{{ $td->title }}</a>
                                </strong>
                                <span>{{ $subjectName }}</span>
                            </div>
                        </div>

                        <span class="tag {{ $isPremium ? 'tag--premium' : 'tag--free' }}">
                            {{ $isPremium ? 'Premium' : 'Gratuit' }}
                        </span>
                    </div>
                @empty
                    <div class="empty-state">Aucun TD disponible pour le moment.</div>
                @endforelse
            </div>
        </section>

        <aside class="side">
            <article class="side-card side-card--soft-blue">
                <h3>Repères rapides</h3>
                <p>Gardez sous les yeux les informations essentielles liées à votre espace.</p>

                <div class="side-list">
                    <div>
                        <strong>Classe</strong>
                        <span>{{ $className }}</span>
                    </div>
                    <div>
                        <strong>Abonnement</strong>
                        <span>{{ $subscriptionState }}</span>
                    </div>
                    <div>
                        <strong>TD disponibles</strong>
                        <span>{{ $tdCount }}</span>
                    </div>
                </div>
            </article>

            <article class="side-card side-card--soft-violet">
                <h3>Conseil du moment</h3>
                <p>
                    Commencez par les TD récents, puis revenez sur les matières où vous avez encore besoin
                    d’un meilleur rythme de travail.
                </p>

                <div class="side-note">
                    Une bonne habitude : consulter vos TD, relire le cours lié et poser une question dès qu’un point bloque.
                </div>
            </article>
        </aside>
    </section>

    <section class="shortcuts-grid">
        <a href="{{ route('student.td.index') }}" class="shortcut-card shortcut-card--courses">
            <div class="shortcut-top">
                <div>
                    <h3>Mes TD</h3>
                    <p>Accédez à vos TD, corrigés, niveaux d’accès et dernières publications.</p>
                </div>
                <span class="shortcut-icon">🗂️</span>
            </div>
            <span class="shortcut-link">Ouvrir cet espace</span>
        </a>

        <a href="{{ route('student.messages.create') }}" class="shortcut-card shortcut-card--messages">
            <div class="shortcut-top">
                <div>
                    <h3>Messagerie enseignant</h3>
                    <p>Posez vos questions liées à la matière, au cours ou au TD concerné.</p>
                </div>
                <span class="shortcut-icon">✉️</span>
            </div>
            <span class="shortcut-link">Écrire maintenant</span>
        </a>
    </section>
</div>
@endsection
