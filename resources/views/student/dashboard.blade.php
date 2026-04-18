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
@endphp

@push('styles')
<style>
    .student-dashboard-v4 {
        display: grid;
        gap: 22px;
    }

    .student-dashboard-v4 .hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        border: 1px solid rgba(255,255,255,.08);
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.18), transparent 28%),
            radial-gradient(circle at 15% 100%, rgba(56, 189, 248, 0.14), transparent 32%),
            linear-gradient(135deg, #0f172a 0%, #172554 45%, #1d4ed8 100%);
        color: #fff;
        padding: 28px;
        box-shadow: var(--shadow-lg);
    }

    .student-dashboard-v4 .hero::before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        top: -100px;
        right: -70px;
    }

    .student-dashboard-v4 .hero::after {
        content: "";
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        bottom: -40px;
        left: -20px;
    }

    .student-dashboard-v4 .hero__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 20px;
        align-items: stretch;
    }

    .student-dashboard-v4 .hero__left,
    .student-dashboard-v4 .hero__right {
        display: grid;
        gap: 16px;
    }

    .student-dashboard-v4 .hero-badge {
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

    .student-dashboard-v4 .hero-title {
        margin: 0;
        font-size: clamp(2rem, 3.6vw, 3.6rem);
        line-height: 0.98;
        letter-spacing: -0.05em;
        max-width: 11ch;
    }

    .student-dashboard-v4 .hero-title span {
        display: block;
        color: #dbeafe;
    }

    .student-dashboard-v4 .hero-text {
        margin: 0;
        color: rgba(255,255,255,.84);
        line-height: 1.75;
        font-size: 1rem;
        max-width: 62ch;
    }

    .student-dashboard-v4 .hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-dashboard-v4 .hero-pill {
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

    .student-dashboard-v4 .hero-panels {
        display: grid;
        gap: 14px;
    }

    .student-dashboard-v4 .hero-panel,
    .student-dashboard-v4 .hero-status {
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        padding: 18px;
        backdrop-filter: blur(10px);
    }

    .student-dashboard-v4 .hero-panel strong {
        display: block;
        margin-bottom: 8px;
        font-size: 1.05rem;
        letter-spacing: -0.02em;
    }

    .student-dashboard-v4 .hero-panel p {
        margin: 0;
        color: rgba(255,255,255,.78);
        font-size: .94rem;
        line-height: 1.65;
    }

    .student-dashboard-v4 .hero-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .student-dashboard-v4 .hero-status__left {
        display: grid;
        gap: 4px;
    }

    .student-dashboard-v4 .hero-status__left span {
        color: rgba(255,255,255,.76);
        font-size: .88rem;
    }

    .student-dashboard-v4 .hero-status__left strong {
        font-size: 1.08rem;
        letter-spacing: -0.02em;
    }

    .student-dashboard-v4 .hero-status__badge {
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

    .student-dashboard-v4 .actions-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .student-dashboard-v4 .action-card {
        position: relative;
        overflow: hidden;
        min-height: 154px;
        padding: 18px;
        display: grid;
        gap: 12px;
        border-radius: 28px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-v4 .action-card:hover,
    .student-dashboard-v4 .stat-card:hover,
    .student-dashboard-v4 .panel:hover,
    .student-dashboard-v4 .side-card:hover,
    .student-dashboard-v4 .shortcut-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .student-dashboard-v4 .action-card::before {
        content: "";
        position: absolute;
        width: 96px;
        height: 96px;
        border-radius: 999px;
        top: -26px;
        right: -18px;
        opacity: .8;
    }

    .student-dashboard-v4 .action-card--courses {
        background: linear-gradient(180deg, #ffffff, #f6faff);
        border-color: #d8e6fb;
    }

    .student-dashboard-v4 .action-card--courses::before {
        background: rgba(37, 99, 235, 0.10);
    }

    .student-dashboard-v4 .action-card--td {
        background: linear-gradient(180deg, #fffdf6, #fff7e8);
        border-color: #f6dfae;
    }

    .student-dashboard-v4 .action-card--td::before {
        background: rgba(245, 158, 11, 0.16);
    }

    .student-dashboard-v4 .action-card--messages {
        background: linear-gradient(180deg, #f7fcfb, #ebfaf5);
        border-color: #bde9d8;
    }

    .student-dashboard-v4 .action-card--messages::before {
        background: rgba(22, 163, 74, 0.14);
    }

    .student-dashboard-v4 .action-card--subscription {
        background: linear-gradient(180deg, #faf7ff, #f3ecff);
        border-color: #dac8ff;
    }

    .student-dashboard-v4 .action-card--subscription::before {
        background: rgba(124, 58, 237, 0.14);
    }

    html[data-theme='dark'] .student-dashboard-v4 .action-card--courses {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110, 161, 255, 0.18);
    }

    html[data-theme='dark'] .student-dashboard-v4 .action-card--td {
        background: linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245, 158, 11, 0.22);
    }

    html[data-theme='dark'] .student-dashboard-v4 .action-card--messages {
        background: linear-gradient(180deg, #11231f, #153029);
        border-color: rgba(22, 163, 74, 0.20);
    }

    html[data-theme='dark'] .student-dashboard-v4 .action-card--subscription {
        background: linear-gradient(180deg, #1c1630, #251c3d);
        border-color: rgba(124, 58, 237, 0.22);
    }

    .student-dashboard-v4 .action-card__icon {
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

    .student-dashboard-v4 .action-card--courses .action-card__icon {
        background: rgba(37,99,235,.10);
    }

    .student-dashboard-v4 .action-card--td .action-card__icon {
        background: rgba(245,158,11,.14);
    }

    .student-dashboard-v4 .action-card--messages .action-card__icon {
        background: rgba(22,163,74,.12);
    }

    .student-dashboard-v4 .action-card--subscription .action-card__icon {
        background: rgba(124,58,237,.12);
    }

    .student-dashboard-v4 .action-card h3 {
        margin: 0;
        font-size: 1.04rem;
        letter-spacing: -0.02em;
        color: var(--text);
        position: relative;
        z-index: 1;
    }

    .student-dashboard-v4 .action-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
        font-size: .9rem;
        position: relative;
        z-index: 1;
    }

    .student-dashboard-v4 .action-card span {
        margin-top: auto;
        font-weight: 800;
        font-size: .92rem;
        position: relative;
        z-index: 1;
    }

    .student-dashboard-v4 .action-card--courses span {
        color: #2563eb;
    }

    .student-dashboard-v4 .action-card--td span {
        color: #d97706;
    }

    .student-dashboard-v4 .action-card--messages span {
        color: #15803d;
    }

    .student-dashboard-v4 .action-card--subscription span {
        color: #7c3aed;
    }

    .student-dashboard-v4 .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .student-dashboard-v4 .stat-card {
        padding: 20px;
        display: grid;
        gap: 14px;
        border-radius: 26px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-v4 .stat-card--neutral {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-v4 .stat-card--blue {
        background: linear-gradient(180deg, #f6faff, #eef5ff);
        border-color: #d8e6fb;
    }

    .student-dashboard-v4 .stat-card--amber {
        background: linear-gradient(180deg, #fffdf7, #fff8eb);
        border-color: #f5dfaf;
    }

    .student-dashboard-v4 .stat-card--green {
        background: linear-gradient(180deg, #f7fcfa, #edf9f2);
        border-color: #c7ead7;
    }

    html[data-theme='dark'] .student-dashboard-v4 .stat-card--neutral {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    html[data-theme='dark'] .student-dashboard-v4 .stat-card--blue {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110, 161, 255, 0.18);
    }

    html[data-theme='dark'] .student-dashboard-v4 .stat-card--amber {
        background: linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245, 158, 11, 0.22);
    }

    html[data-theme='dark'] .student-dashboard-v4 .stat-card--green {
        background: linear-gradient(180deg, #11231f, #153029);
        border-color: rgba(22, 163, 74, 0.20);
    }

    .student-dashboard-v4 .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .student-dashboard-v4 .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex: 0 0 52px;
    }

    .student-dashboard-v4 .stat-card--neutral .stat-icon {
        background: rgba(37,99,235,.10);
    }

    .student-dashboard-v4 .stat-card--blue .stat-icon {
        background: rgba(37,99,235,.12);
    }

    .student-dashboard-v4 .stat-card--amber .stat-icon {
        background: rgba(245,158,11,.14);
    }

    .student-dashboard-v4 .stat-card--green .stat-icon {
        background: rgba(22,163,74,.12);
    }

    .student-dashboard-v4 .stat-label {
        display: block;
        font-size: .94rem;
        font-weight: 800;
        color: var(--text);
    }

    .student-dashboard-v4 .stat-value {
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: var(--text);
    }

    .student-dashboard-v4 .stat-note {
        color: var(--muted);
        font-size: .88rem;
        line-height: 1.6;
    }

    .student-dashboard-v4 .main-grid {
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 18px;
        align-items: start;
    }

    .student-dashboard-v4 .panel,
    .student-dashboard-v4 .side-card,
    .student-dashboard-v4 .shortcut-card {
        border: 1px solid var(--line);
        border-radius: 28px;
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .student-dashboard-v4 .panel {
        overflow: hidden;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-v4 .panel__head {
        padding: 22px 22px 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line);
        background: rgba(37, 99, 235, 0.03);
    }

    .student-dashboard-v4 .panel__head h2 {
        margin: 0;
        font-size: 1.45rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-v4 .panel__head p,
    .student-dashboard-v4 .panel__head span {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
    }

    .student-dashboard-v4 .panel__list {
        display: grid;
    }

    .student-dashboard-v4 .panel__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, transparent, transparent);
    }

    .student-dashboard-v4 .panel__item:nth-child(odd) {
        background: rgba(37, 99, 235, 0.02);
    }

    .student-dashboard-v4 .panel__item:last-child {
        border-bottom: 0;
    }

    .student-dashboard-v4 .panel__item-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .student-dashboard-v4 .subject-mark {
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

    .student-dashboard-v4 .panel__item-text {
        min-width: 0;
        display: grid;
        gap: 2px;
    }

    .student-dashboard-v4 .panel__item-text strong {
        font-size: 1rem;
        line-height: 1.35;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-dashboard-v4 .panel__item-text strong a {
        color: inherit;
    }

    .student-dashboard-v4 .panel__item-text span {
        color: var(--muted);
        font-size: .88rem;
    }

    .student-dashboard-v4 .tag {
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

    .student-dashboard-v4 .tag--premium {
        background: rgba(124,58,237,.10);
        color: #7c3aed;
        border-color: rgba(124,58,237,.18);
    }

    .student-dashboard-v4 .tag--free {
        background: rgba(22,163,74,.10);
        color: #15803d;
        border-color: rgba(22,163,74,.18);
    }

    .student-dashboard-v4 .side {
        display: grid;
        gap: 18px;
    }

    .student-dashboard-v4 .side-card {
        padding: 22px;
        display: grid;
        gap: 14px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-v4 .side-card--soft-blue {
        background: linear-gradient(180deg, #f6faff, #eef5ff);
        border-color: #d8e6fb;
    }

    .student-dashboard-v4 .side-card--soft-violet {
        background: linear-gradient(180deg, #faf7ff, #f3ecff);
        border-color: #dac8ff;
    }

    html[data-theme='dark'] .student-dashboard-v4 .side-card--soft-blue {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110, 161, 255, 0.18);
    }

    html[data-theme='dark'] .student-dashboard-v4 .side-card--soft-violet {
        background: linear-gradient(180deg, #1c1630, #251c3d);
        border-color: rgba(124, 58, 237, 0.22);
    }

    .student-dashboard-v4 .side-card h3 {
        margin: 0;
        font-size: 1.15rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-v4 .side-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .student-dashboard-v4 .side-list {
        display: grid;
        gap: 10px;
    }

    .student-dashboard-v4 .side-list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.56);
    }

    html[data-theme='dark'] .student-dashboard-v4 .side-list div {
        background: rgba(15, 23, 42, 0.22);
    }

    .student-dashboard-v4 .side-list div strong {
        font-size: .95rem;
    }

    .student-dashboard-v4 .side-list div span {
        color: var(--muted);
        font-size: .88rem;
    }

    .student-dashboard-v4 .side-note {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.54);
        color: var(--muted);
        line-height: 1.7;
        font-size: .9rem;
    }

    html[data-theme='dark'] .student-dashboard-v4 .side-note {
        background: rgba(15, 23, 42, 0.22);
    }

    .student-dashboard-v4 .shortcuts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .student-dashboard-v4 .shortcut-card {
        padding: 22px;
        display: grid;
        gap: 14px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-dashboard-v4 .shortcut-card--courses {
        background: linear-gradient(180deg, #f7fcfb, #ecfaf4);
        border-color: #c7ead7;
    }

    .student-dashboard-v4 .shortcut-card--messages {
        background: linear-gradient(180deg, #fffdf7, #fff7eb);
        border-color: #f5dfaf;
    }

    html[data-theme='dark'] .student-dashboard-v4 .shortcut-card--courses {
        background: linear-gradient(180deg, #11231f, #153029);
        border-color: rgba(22, 163, 74, 0.20);
    }

    html[data-theme='dark'] .student-dashboard-v4 .shortcut-card--messages {
        background: linear-gradient(180deg, #2b2110, #362915);
        border-color: rgba(245, 158, 11, 0.22);
    }

    .student-dashboard-v4 .shortcut-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .student-dashboard-v4 .shortcut-icon {
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

    html[data-theme='dark'] .student-dashboard-v4 .shortcut-icon {
        background: rgba(255,255,255,.08);
    }

    .student-dashboard-v4 .shortcut-card h3 {
        margin: 0;
        font-size: 1.2rem;
        letter-spacing: -0.03em;
    }

    .student-dashboard-v4 .shortcut-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .student-dashboard-v4 .shortcut-link {
        font-weight: 800;
    }

    .student-dashboard-v4 .shortcut-card--courses .shortcut-link {
        color: #15803d;
    }

    .student-dashboard-v4 .shortcut-card--messages .shortcut-link {
        color: #d97706;
    }

    .student-dashboard-v4 .empty-state {
        padding: 20px 22px;
        color: var(--muted);
        line-height: 1.7;
    }

    @media (max-width: 1180px) {
        .student-dashboard-v4 .hero__grid,
        .student-dashboard-v4 .main-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-v4 .actions-grid,
        .student-dashboard-v4 .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .student-dashboard-v4 {
            gap: 18px;
        }

        .student-dashboard-v4 .hero,
        .student-dashboard-v4 .action-card,
        .student-dashboard-v4 .stat-card,
        .student-dashboard-v4 .side-card,
        .student-dashboard-v4 .shortcut-card {
            border-radius: 22px;
        }

        .student-dashboard-v4 .hero {
            padding: 20px 16px;
        }

        .student-dashboard-v4 .actions-grid,
        .student-dashboard-v4 .stats-grid,
        .student-dashboard-v4 .shortcuts-grid {
            grid-template-columns: 1fr;
        }

        .student-dashboard-v4 .hero-title {
            max-width: none;
            font-size: clamp(1.9rem, 10vw, 2.8rem);
        }

        .student-dashboard-v4 .panel__head,
        .student-dashboard-v4 .panel__item,
        .student-dashboard-v4 .side-card,
        .student-dashboard-v4 .shortcut-card {
            padding-left: 16px;
            padding-right: 16px;
        }

        .student-dashboard-v4 .panel__item {
            align-items: flex-start;
            flex-direction: column;
        }

        .student-dashboard-v4 .panel__item-left {
            width: 100%;
        }

        .student-dashboard-v4 .hero-status {
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .student-dashboard-v4 .hero {
            padding: 18px 14px;
        }

        .student-dashboard-v4 .panel__head,
        .student-dashboard-v4 .panel__item,
        .student-dashboard-v4 .side-card,
        .student-dashboard-v4 .shortcut-card {
            padding-left: 14px;
            padding-right: 14px;
        }

        .student-dashboard-v4 .action-card,
        .student-dashboard-v4 .stat-card {
            padding: 16px;
        }

        .student-dashboard-v4 .hero-badge,
        .student-dashboard-v4 .hero-pill,
        .student-dashboard-v4 .tag {
            font-size: .78rem;
        }

        .student-dashboard-v4 .subject-mark,
        .student-dashboard-v4 .shortcut-icon,
        .student-dashboard-v4 .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            flex-basis: 46px;
        }

        .student-dashboard-v4 .stat-value {
            font-size: 1.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="student-dashboard-v4">
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
                    <strong>Votre espace de travail</strong>
                    <p>
                        Utilisez ce tableau de bord pour accéder rapidement à vos TD, consulter vos cours,
                        poser une question et suivre votre rythme de travail.
                    </p>
                </article>

                <article class="hero-panel">
                    <strong>Objectif du moment</strong>
                    <p>
                        Garder un bon rythme sur vos TD récents et revenir régulièrement sur les matières
                        où vous devez encore progresser.
                    </p>
                </article>
            </div>
        </div>
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
