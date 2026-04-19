@extends('layouts.student')

@section('title', 'Messagerie')

@php
    $threads = collect($messages ?? [])->values();
    $threadCount = $threads->count();
    $unreadCount = $threads->filter(fn ($message) => ($message->status ?? '') === 'unread')->count();
    $attachmentCount = $threads->filter(fn ($message) => !empty($message->attachment_path))->count();
    $repliedCount = $threads->filter(fn ($message) => !empty($message->reply_message))->count();

    $statusMap = [
        'unread' => ['label' => 'Non lu', 'class' => 'unread'],
        'read' => ['label' => 'Lu', 'class' => 'read'],
        'answered' => ['label' => 'Répondu', 'class' => 'answered'],
        'closed' => ['label' => 'Fermé', 'class' => 'closed'],
        'pending' => ['label' => 'En attente', 'class' => 'pending'],
    ];

    $firstThread = $threads->first();
    $firstThreadId = $firstThread->id ?? null;
@endphp

@push('styles')
<style>
    .student-messaging-x {
        display: grid;
        gap: 18px;
    }

    .student-messaging-x .page-hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        border: 1px solid rgba(255,255,255,.08);
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.18), transparent 28%),
            radial-gradient(circle at 18% 100%, rgba(56, 189, 248, 0.12), transparent 30%),
            linear-gradient(135deg, #0f172a 0%, #172554 46%, #1d4ed8 100%);
        color: #fff;
        padding: 22px;
        box-shadow: var(--shadow-lg);
    }

    .student-messaging-x .page-hero::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        top: -90px;
        right: -55px;
    }

    .student-messaging-x .page-hero__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.02fr .98fr;
        gap: 18px;
        align-items: center;
    }

    .student-messaging-x .page-hero__left,
    .student-messaging-x .page-hero__right {
        display: grid;
        gap: 12px;
    }

    .student-messaging-x .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.16);
        background: rgba(255,255,255,.10);
        font-size: .82rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .student-messaging-x .page-hero h1 {
        margin: 0;
        font-size: clamp(1.8rem, 3vw, 3rem);
        line-height: 1.02;
        letter-spacing: -0.05em;
        max-width: 11ch;
    }

    .student-messaging-x .page-hero p {
        margin: 0;
        color: rgba(255,255,255,.84);
        line-height: 1.72;
        font-size: .95rem;
        max-width: 62ch;
    }

    .student-messaging-x .hero-quick {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging-x .hero-pill {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        color: #eef6ff;
        font-size: .8rem;
        font-weight: 800;
    }

    .student-messaging-x .hero-right-top {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .student-messaging-x .hero-stat {
        padding: 14px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(10px);
        display: grid;
        gap: 4px;
    }

    .student-messaging-x .hero-stat strong {
        font-size: 1.28rem;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .student-messaging-x .hero-stat span {
        color: rgba(255,255,255,.76);
        font-size: .8rem;
        font-weight: 700;
    }

    .student-messaging-x .hero-right-bottom {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .student-messaging-x .chat-shell {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 18px;
        min-height: 740px;
        align-items: start;
    }

    .student-messaging-x .thread-list-card,
    .student-messaging-x .chat-pane-card {
        border: 1px solid var(--line);
        border-radius: 30px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .student-messaging-x .thread-list-card {
        position: sticky;
        top: 94px;
    }

    .student-messaging-x .thread-list-head,
    .student-messaging-x .chat-pane-head {
        padding: 18px 18px 16px;
        border-bottom: 1px solid var(--line);
        background: rgba(37, 99, 235, 0.03);
    }

    .student-messaging-x .thread-list-head h2,
    .student-messaging-x .chat-pane-head h2 {
        margin: 0;
        font-size: 1.14rem;
        letter-spacing: -0.03em;
    }

    .student-messaging-x .thread-list-head p,
    .student-messaging-x .chat-pane-head p {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: .9rem;
        line-height: 1.6;
    }

    .student-messaging-x .thread-toolbar {
        padding: 14px 18px;
        display: grid;
        gap: 12px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, rgba(37,99,235,.02), transparent);
    }

    .student-messaging-x .search-wrap {
        position: relative;
    }

    .student-messaging-x .search-wrap input {
        width: 100%;
        height: 48px;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--text);
        padding: 0 14px 0 42px;
        outline: none;
        transition: .2s ease;
    }

    .student-messaging-x .search-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    }

    .student-messaging-x .search-wrap svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--muted);
        pointer-events: none;
    }

    .student-messaging-x .thread-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging-x .thread-filter {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--muted);
        font-size: .8rem;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .student-messaging-x .thread-filter.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        box-shadow: 0 10px 24px rgba(37,99,235,.20);
    }

    .student-messaging-x .thread-list {
        display: grid;
        max-height: 600px;
        overflow: auto;
    }

    .student-messaging-x .thread-item {
        width: 100%;
        display: grid;
        gap: 10px;
        padding: 16px 18px;
        border: 0;
        border-bottom: 1px solid var(--line);
        background: transparent;
        text-align: left;
        cursor: pointer;
        transition: .2s ease;
        position: relative;
    }

    .student-messaging-x .thread-item:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .student-messaging-x .thread-item.is-active {
        background: rgba(37, 99, 235, 0.08);
    }

    .student-messaging-x .thread-item.is-active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 12px;
        bottom: 12px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, var(--primary), #4f86ff);
    }

    .student-messaging-x .thread-item.is-hidden {
        display: none;
    }

    .student-messaging-x .thread-item__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .student-messaging-x .thread-item__main {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 0;
    }

    .student-messaging-x .avatar {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        color: #fff;
        font-weight: 900;
        font-size: .96rem;
        letter-spacing: -0.02em;
        box-shadow: var(--shadow-xs);
    }

    .student-messaging-x .thread-item__text {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .student-messaging-x .thread-item__text strong {
        font-size: .98rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-messaging-x .thread-item__meta {
        color: var(--muted);
        font-size: .82rem;
        line-height: 1.45;
    }

    .student-messaging-x .thread-item__time {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .student-messaging-x .thread-item__subject {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .student-messaging-x .thread-snippet {
        color: var(--muted);
        font-size: .86rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .student-messaging-x .badge {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 999px;
        border: 1px solid var(--line);
        font-size: .76rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .student-messaging-x .badge--unread {
        background: rgba(37,99,235,.10);
        color: var(--primary);
        border-color: rgba(37,99,235,.16);
    }

    .student-messaging-x .badge--answered {
        background: rgba(22,163,74,.10);
        color: #15803d;
        border-color: rgba(22,163,74,.18);
    }

    .student-messaging-x .badge--pending {
        background: rgba(245,158,11,.12);
        color: #d97706;
        border-color: rgba(245,158,11,.18);
    }

    .student-messaging-x .badge--closed {
        background: rgba(100,116,139,.12);
        color: var(--muted);
        border-color: var(--line);
    }

    .student-messaging-x .badge--read {
        background: rgba(100,116,139,.10);
        color: var(--muted);
        border-color: var(--line);
    }

    .student-messaging-x .badge--attachment {
        background: rgba(124,58,237,.10);
        color: #7c3aed;
        border-color: rgba(124,58,237,.18);
    }

    .student-messaging-x .chat-pane {
        display: none;
        grid-template-rows: auto 1fr auto;
        min-height: 740px;
    }

    .student-messaging-x .chat-pane.is-active {
        display: grid;
    }

    .student-messaging-x .chat-pane-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .student-messaging-x .chat-pane-head__left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .student-messaging-x .chat-pane-head__text {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .student-messaging-x .chat-pane-head__text strong {
        font-size: 1rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .student-messaging-x .chat-pane-head__text span {
        color: var(--muted);
        font-size: .84rem;
        line-height: 1.45;
    }

    .student-messaging-x .chat-pane-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }

    .student-messaging-x .meta-chip {
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--muted);
        font-size: .76rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
    }

    .student-messaging-x .chat-pane-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging-x .chat-scroll {
        padding: 18px;
        display: grid;
        gap: 14px;
        min-height: 430px;
        max-height: 580px;
        overflow: auto;
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.03), transparent 24%),
            linear-gradient(180deg, rgba(37,99,235,.01), transparent 18%);
    }

    .student-messaging-x .message-group {
        display: grid;
        gap: 12px;
    }

    .student-messaging-x .message-date {
        justify-self: center;
        min-height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--muted);
        font-size: .76rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
    }

    .student-messaging-x .bubble-row {
        display: flex;
    }

    .student-messaging-x .bubble-row--me {
        justify-content: flex-end;
    }

    .student-messaging-x .bubble-row--teacher {
        justify-content: flex-start;
    }

    .student-messaging-x .bubble {
        max-width: min(740px, 88%);
        padding: 16px;
        border-radius: 22px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-xs);
        display: grid;
        gap: 10px;
        position: relative;
    }

    .student-messaging-x .bubble--me {
        background: linear-gradient(180deg, #eef5ff, #f7faff);
        border-color: #d8e6fb;
    }

    .student-messaging-x .bubble--teacher {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    html[data-theme='dark'] .student-messaging-x .bubble--me {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110,161,255,.18);
    }

    .student-messaging-x .bubble__meta {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .student-messaging-x .bubble__title {
        font-size: 1rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-messaging-x .bubble__text {
        color: var(--text);
        line-height: 1.72;
        font-size: .94rem;
        word-break: break-word;
    }

    .student-messaging-x .attachment-box {
        display: grid;
        gap: 10px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.56);
    }

    html[data-theme='dark'] .student-messaging-x .attachment-box {
        background: rgba(15, 23, 42, 0.22);
    }

    .student-messaging-x .attachment-file {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .student-messaging-x .attachment-file__icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(124,58,237,.10);
        color: #7c3aed;
        font-size: 1rem;
        font-weight: 900;
    }

    .student-messaging-x .attachment-file__text {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .student-messaging-x .attachment-file__text strong {
        font-size: .92rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .student-messaging-x .attachment-file__text span {
        color: var(--muted);
        font-size: .8rem;
    }

    .student-messaging-x .attachment-image-link {
        display: block;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--line);
        background: var(--panel);
    }

    .student-messaging-x .attachment-image {
        width: 100%;
        max-height: 280px;
        object-fit: cover;
        display: block;
    }

    .student-messaging-x .attachment-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging-x .attachment-actions a {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--primary);
        font-size: .82rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
    }

    .student-messaging-x .chat-pane-footer {
        padding: 16px 18px;
        border-top: 1px solid var(--line);
        background: linear-gradient(180deg, rgba(37,99,235,.02), transparent);
        display: grid;
        gap: 12px;
    }

    .student-messaging-x .reply-card {
        display: grid;
        gap: 12px;
        padding: 16px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.08), transparent 28%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .student-messaging-x .reply-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .student-messaging-x .reply-card__top strong {
        font-size: 1rem;
        letter-spacing: -0.02em;
    }

    .student-messaging-x .reply-card__top span {
        color: var(--muted);
        font-size: .84rem;
        line-height: 1.5;
    }

    .student-messaging-x .reply-preview {
        min-height: 88px;
        border-radius: 18px;
        border: 1px dashed var(--line);
        background: rgba(37,99,235,.03);
        padding: 14px;
        color: var(--muted);
        font-size: .9rem;
        line-height: 1.65;
    }

    .student-messaging-x .reply-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .student-messaging-x .reply-tools {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .student-messaging-x .tool-pill {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--muted);
        font-size: .8rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
    }

    .student-messaging-x .chat-empty {
        min-height: 100%;
        display: grid;
        place-items: center;
        padding: 28px;
        text-align: center;
        color: var(--muted);
    }

    .student-messaging-x .chat-empty__box {
        max-width: 420px;
        display: grid;
        gap: 12px;
    }

    .student-messaging-x .chat-empty__icon {
        width: 74px;
        height: 74px;
        border-radius: 22px;
        margin: 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37,99,235,.10);
        color: var(--primary);
        font-size: 1.9rem;
        font-weight: 900;
    }

    .student-messaging-x .mobile-back {
        display: none;
        min-height: 40px;
        padding: 0 12px;
        border-radius: 12px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--text);
        font-weight: 800;
        cursor: pointer;
    }

    .student-messaging-x .mobile-compose {
        display: none;
        position: fixed;
        right: 16px;
        bottom: 18px;
        z-index: 35;
        min-height: 52px;
        padding: 0 18px;
        border-radius: 999px;
        border: 0;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        color: #fff;
        font-weight: 900;
        box-shadow: 0 18px 36px rgba(37,99,235,.26);
    }

    @media (max-width: 1180px) {
        .student-messaging-x .page-hero__grid,
        .student-messaging-x .chat-shell {
            grid-template-columns: 1fr;
        }

        .student-messaging-x .hero-right-top {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .student-messaging-x .thread-list-card {
            position: static;
        }

        .student-messaging-x .thread-list,
        .student-messaging-x .chat-scroll {
            max-height: none;
        }

        .student-messaging-x .chat-pane {
            min-height: auto;
        }
    }

    @media (max-width: 720px) {
        .student-messaging-x {
            gap: 16px;
        }

        .student-messaging-x .page-hero,
        .student-messaging-x .thread-list-card,
        .student-messaging-x .chat-pane-card {
            border-radius: 22px;
        }

        .student-messaging-x .page-hero {
            padding: 18px 14px;
        }

        .student-messaging-x .page-hero h1 {
            max-width: none;
            font-size: clamp(1.55rem, 8vw, 2.25rem);
        }

        .student-messaging-x .page-hero p {
            font-size: .9rem;
            line-height: 1.6;
        }

        .student-messaging-x .hero-right-top {
            grid-template-columns: 1fr 1fr;
        }

        .student-messaging-x .hero-right-bottom {
            justify-content: flex-start;
        }

        .student-messaging-x .chat-shell {
            min-height: auto;
        }

        .student-messaging-x .chat-pane-card {
            display: none;
        }

        .student-messaging-x.is-thread-open .thread-list-card {
            display: none;
        }

        .student-messaging-x.is-thread-open .chat-pane-card {
            display: block;
        }

        .student-messaging-x .mobile-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .student-messaging-x .mobile-compose {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .student-messaging-x.is-thread-open .mobile-compose {
            display: none;
        }

        .student-messaging-x .thread-list-head,
        .student-messaging-x .thread-toolbar,
        .student-messaging-x .chat-pane-head,
        .student-messaging-x .chat-scroll,
        .student-messaging-x .chat-pane-footer {
            padding-left: 14px;
            padding-right: 14px;
        }

        .student-messaging-x .bubble {
            max-width: 100%;
        }

        .student-messaging-x .reply-actions {
            align-items: stretch;
        }

        .student-messaging-x .reply-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .student-messaging-x .reply-tools {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .student-messaging-x .hero-right-top {
            grid-template-columns: 1fr;
        }

        .student-messaging-x .thread-item,
        .student-messaging-x .chat-scroll,
        .student-messaging-x .chat-pane-footer {
            padding-left: 12px;
            padding-right: 12px;
        }

        .student-messaging-x .thread-item__main {
            gap: 10px;
        }

        .student-messaging-x .avatar {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            border-radius: 14px;
            font-size: .9rem;
        }

        .student-messaging-x .attachment-image {
            max-height: 210px;
        }
    }
</style>
@endpush

@section('content')
<section class="student-messaging-x" id="studentMessagingX">
    <div class="page-hero">
        <div class="page-hero__grid">
            <div class="page-hero__left">
                <span class="hero-badge">💬 Messagerie élève</span>
                <h1>Mes échanges avec les enseignants</h1>
                <p>Retrouvez vos conversations, les réponses et les pièces jointes dans une interface plus moderne, plus rapide et plus pratique au quotidien.</p>

                <div class="hero-quick">
                    <span class="hero-pill">Lecture rapide</span>
                    <span class="hero-pill">Pièces jointes intégrées</span>
                    <span class="hero-pill">Vue conversation</span>
                </div>
            </div>

            <div class="page-hero__right">
                <div class="hero-right-top">
                    <div class="hero-stat">
                        <strong>{{ $threadCount }}</strong>
                        <span>conversations</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ $unreadCount }}</strong>
                        <span>non lues</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ $repliedCount }}</strong>
                        <span>réponses</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ $attachmentCount }}</strong>
                        <span>pièces jointes</span>
                    </div>
                </div>

                <div class="hero-right-bottom">
                    <a href="{{ route('student.messages.create') }}" class="btn btn--primary">Nouveau message</a>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-shell">
        <section class="thread-list-card">
            <div class="thread-list-head">
                <h2>Conversations</h2>
                <p>Retrouvez vite le bon enseignant, la bonne matière et le bon échange.</p>
            </div>

            <div class="thread-toolbar">
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="threadSearch" placeholder="Rechercher un enseignant, une matière, un message...">
                </div>

                <div class="thread-filters">
                    <button type="button" class="thread-filter is-active" data-filter="all">Toutes</button>
                    <button type="button" class="thread-filter" data-filter="unread">Non lues</button>
                    <button type="button" class="thread-filter" data-filter="answered">Répondues</button>
                    <button type="button" class="thread-filter" data-filter="attachment">Pièces jointes</button>
                </div>
            </div>

            <div class="thread-list" id="threadList">
                @forelse ($threads as $message)
                    @php
                        $teacherName = $message->teacher->full_name ?? $message->teacher->name ?? 'Enseignant';
                        $classLabel = $message->schoolClass->name ?? 'Classe inconnue';
                        $subjectLabel = $message->subject->name ?? 'Matière inconnue';
                        $title = $message->title ?: ($message->topic ?? 'Sans objet');
                        $attachmentUrl = $message->attachment_path ? asset('storage/' . $message->attachment_path) : null;
                        $statusUi = $statusMap[$message->status ?? 'read'] ?? ['label' => ucfirst((string) $message->status), 'class' => 'read'];
                        $snippet = $message->reply_message ?: $message->message;
                        $avatar = collect(explode(' ', trim($teacherName)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
                        $avatar = $avatar !== '' ? $avatar : 'PR';
                    @endphp

                    <button
                        type="button"
                        class="thread-item {{ ($message->id ?? null) === $firstThreadId ? 'is-active' : '' }}"
                        data-thread-id="{{ $message->id }}"
                        data-status="{{ $statusUi['class'] }}"
                        data-has-attachment="{{ $attachmentUrl ? 'yes' : 'no' }}"
                        data-search="{{ strtolower($teacherName . ' ' . $classLabel . ' ' . $subjectLabel . ' ' . $title . ' ' . strip_tags($snippet)) }}"
                    >
                        <div class="thread-item__top">
                            <div class="thread-item__main">
                                <span class="avatar">{{ $avatar }}</span>

                                <div class="thread-item__text">
                                    <strong>{{ $teacherName }}</strong>
                                    <div class="thread-item__meta">{{ $subjectLabel }} · {{ $classLabel }}</div>
                                </div>
                            </div>

                            <span class="thread-item__time">{{ optional($message->created_at)->format('H:i') }}</span>
                        </div>

                        <div class="thread-item__subject">
                            <span class="badge badge--{{ $statusUi['class'] }}">{{ $statusUi['label'] }}</span>
                            @if ($attachmentUrl)
                                <span class="badge badge--attachment">Pièce jointe</span>
                            @endif
                        </div>

                        <div class="thread-snippet">
                            {{ $title }} — {{ \Illuminate\Support\Str::limit(strip_tags($snippet), 85) }}
                        </div>
                    </button>
                @empty
                    <div class="chat-empty">
                        <div class="chat-empty__box">
                            <div class="chat-empty__icon">💬</div>
                            <strong>Aucune conversation pour le moment</strong>
                            <span>Commencez une première discussion avec un enseignant.</span>
                            <a href="{{ route('student.messages.create') }}" class="btn btn--primary">Nouveau message</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="chat-pane-card">
            @if ($threadCount > 0)
                @foreach ($threads as $message)
                    @php
                        $teacherName = $message->teacher->full_name ?? $message->teacher->name ?? 'Enseignant';
                        $classLabel = $message->schoolClass->name ?? 'Classe inconnue';
                        $subjectLabel = $message->subject->name ?? 'Matière inconnue';
                        $title = $message->title ?: ($message->topic ?? 'Sans objet');
                        $attachmentUrl = $message->attachment_path ? asset('storage/' . $message->attachment_path) : null;
                        $attachmentName = $message->attachment_name ?: basename((string) $message->attachment_path);
                        $extension = strtolower(pathinfo((string) $attachmentName, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                        $statusUi = $statusMap[$message->status ?? 'read'] ?? ['label' => ucfirst((string) $message->status), 'class' => 'read'];
                        $avatar = collect(explode(' ', trim($teacherName)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
                        $avatar = $avatar !== '' ? $avatar : 'PR';
                    @endphp

                    <div class="chat-pane {{ ($message->id ?? null) === $firstThreadId ? 'is-active' : '' }}" data-pane-id="{{ $message->id }}">
                        <div class="chat-pane-head">
                            <div class="chat-pane-head__left">
                                <button type="button" class="mobile-back" data-mobile-back>← Retour</button>

                                <span class="avatar">{{ $avatar }}</span>

                                <div class="chat-pane-head__text">
                                    <strong>{{ $teacherName }}</strong>
                                    <span>{{ $subjectLabel }} · {{ $classLabel }}</span>

                                    <div class="chat-pane-meta">
                                        <span class="meta-chip">{{ $classLabel }}</span>
                                        <span class="meta-chip">{{ $subjectLabel }}</span>
                                        <span class="badge badge--{{ $statusUi['class'] }}">{{ $statusUi['label'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-pane-actions">
                                <a href="{{ route('student.messages.create') }}" class="btn btn--ghost">Nouveau message</a>
                            </div>
                        </div>

                        <div class="chat-scroll">
                            <div class="message-group">
                                <div class="message-date">
                                    {{ optional($message->created_at)->format('d/m/Y') }}
                                </div>

                                <div class="bubble-row bubble-row--me">
                                    <div class="bubble bubble--me">
                                        <div class="bubble__meta">Vous · {{ optional($message->created_at)->format('d/m/Y H:i') }}</div>
                                        <div class="bubble__title">{{ $title }}</div>
                                        <div class="bubble__text">{!! nl2br(e($message->message)) !!}</div>

                                        @if ($attachmentUrl)
                                            <div class="attachment-box">
                                                @if ($isImage)
                                                    <a href="{{ $attachmentUrl }}" target="_blank" class="attachment-image-link">
                                                        <img src="{{ $attachmentUrl }}" alt="Pièce jointe" class="attachment-image">
                                                    </a>
                                                @else
                                                    <div class="attachment-file">
                                                        <span class="attachment-file__icon">📎</span>
                                                        <div class="attachment-file__text">
                                                            <strong>{{ $attachmentName }}</strong>
                                                            <span>Fichier joint</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="attachment-actions">
                                                    <a href="{{ $attachmentUrl }}" target="_blank">Ouvrir</a>
                                                    <a href="{{ $attachmentUrl }}" download>Télécharger</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($message->reply_message))
                                    <div class="bubble-row bubble-row--teacher">
                                        <div class="bubble bubble--teacher">
                                            <div class="bubble__meta">{{ $teacherName }} · {{ optional($message->replied_at)->format('d/m/Y H:i') ?: 'Réponse reçue' }}</div>
                                            <div class="bubble__text">{!! nl2br(e($message->reply_message)) !!}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="chat-pane-footer">
                            <div class="reply-card">
                                <div class="reply-card__top">
                                    <div>
                                        <strong>Continuer cet échange</strong>
                                        <span>Préparez votre prochaine demande ou relancez proprement la discussion.</span>
                                    </div>
                                </div>

                                <div class="reply-preview">
                                    Écrivez ici votre prochaine idée, votre question ou votre précision. Pour l’envoi réel,
                                    utilisez le bouton « Nouveau message » ci-dessous afin de conserver le flux actuel du système.
                                </div>

                                <div class="reply-actions">
                                    <div class="reply-tools">
                                        <span class="tool-pill">Texte</span>
                                        <span class="tool-pill">Pièce jointe</span>
                                        <span class="tool-pill">Question claire</span>
                                    </div>

                                    <a href="{{ route('student.messages.create') }}" class="btn btn--primary">Nouveau message</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="chat-empty">
                    <div class="chat-empty__box">
                        <div class="chat-empty__icon">✉️</div>
                        <strong>Aucune conversation sélectionnée</strong>
                        <span>Choisissez une conversation ou démarrez un nouveau message.</span>
                        <a href="{{ route('student.messages.create') }}" class="btn btn--primary">Nouveau message</a>
                    </div>
                </div>
            @endif
        </section>
    </div>

    @if ($threadCount > 0)
        <a href="{{ route('student.messages.create') }}" class="mobile-compose">+ Nouveau message</a>
    @endif
</section>
@endsection

@push('scripts')
<script>
    (function () {
        const root = document.getElementById('studentMessagingX');
        if (!root) return;

        const items = Array.from(root.querySelectorAll('.thread-item'));
        const panes = Array.from(root.querySelectorAll('.chat-pane'));
        const searchInput = root.querySelector('#threadSearch');
        const filterButtons = Array.from(root.querySelectorAll('.thread-filter'));
        const mobileBackButtons = Array.from(root.querySelectorAll('[data-mobile-back]'));

        let currentFilter = 'all';

        const activateThread = (threadId) => {
            items.forEach((item) => {
                item.classList.toggle('is-active', item.dataset.threadId === String(threadId));
            });

            panes.forEach((pane) => {
                pane.classList.toggle('is-active', pane.dataset.paneId === String(threadId));
            });

            if (window.innerWidth <= 720) {
                root.classList.add('is-thread-open');
            }
        };

        const applyFilters = () => {
            const term = (searchInput?.value || '').trim().toLowerCase();

            items.forEach((item) => {
                const matchesSearch = !term || item.dataset.search.includes(term);
                const matchesFilter =
                    currentFilter === 'all' ||
                    (currentFilter === 'attachment' && item.dataset.hasAttachment === 'yes') ||
                    item.dataset.status === currentFilter;

                const visible = matchesSearch && matchesFilter;
                item.classList.toggle('is-hidden', !visible);
            });

            const visibleItems = items.filter((item) => !item.classList.contains('is-hidden'));
            const activeVisible = visibleItems.find((item) => item.classList.contains('is-active'));

            if (!activeVisible && visibleItems.length > 0) {
                activateThread(visibleItems[0].dataset.threadId);
            }
        };

        items.forEach((item) => {
            item.addEventListener('click', () => activateThread(item.dataset.threadId));
        });

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                filterButtons.forEach((btn) => btn.classList.remove('is-active'));
                button.classList.add('is-active');
                currentFilter = button.dataset.filter || 'all';
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        mobileBackButtons.forEach((button) => {
            button.addEventListener('click', () => {
                root.classList.remove('is-thread-open');
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 720) {
                root.classList.remove('is-thread-open');
            }
        });

        const firstVisible = items.find((item) => !item.classList.contains('is-hidden'));
        if (firstVisible) {
            activateThread(firstVisible.dataset.threadId);
        }
    })();
</script>
@endpush
