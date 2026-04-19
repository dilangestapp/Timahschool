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
    .student-messaging {
        display: grid;
        gap: 18px;
    }

    .student-messaging .page-hero {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.18), transparent 28%),
            radial-gradient(circle at 15% 100%, rgba(56, 189, 248, 0.12), transparent 32%),
            linear-gradient(135deg, #0f172a 0%, #172554 45%, #1d4ed8 100%);
        color: #fff;
        padding: 24px;
        box-shadow: var(--shadow-lg);
    }

    .student-messaging .page-hero::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        top: -90px;
        right: -60px;
    }

    .student-messaging .page-hero__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1.06fr .94fr;
        gap: 18px;
        align-items: start;
    }

    .student-messaging .page-hero__left,
    .student-messaging .page-hero__right {
        display: grid;
        gap: 14px;
    }

    .student-messaging .hero-badge {
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

    .student-messaging .page-hero h1 {
        margin: 0;
        font-size: clamp(1.9rem, 3.3vw, 3.2rem);
        line-height: 1.02;
        letter-spacing: -0.05em;
        max-width: 12ch;
    }

    .student-messaging .page-hero p {
        margin: 0;
        color: rgba(255,255,255,.84);
        line-height: 1.72;
        font-size: .98rem;
        max-width: 62ch;
    }

    .student-messaging .hero-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .student-messaging .hero-stat {
        padding: 14px 16px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(10px);
        display: grid;
        gap: 4px;
    }

    .student-messaging .hero-stat strong {
        font-size: 1.35rem;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .student-messaging .hero-stat span {
        color: rgba(255,255,255,.76);
        font-size: .82rem;
        font-weight: 700;
    }

    .student-messaging .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-end;
    }

    .student-messaging .hero-actions .btn {
        box-shadow: 0 14px 28px rgba(0, 0, 0, .14);
    }

    .student-messaging .chat-shell {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 18px;
        min-height: 720px;
    }

    .student-messaging .thread-list-card,
    .student-messaging .chat-pane-card {
        border: 1px solid var(--line);
        border-radius: 30px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .student-messaging .thread-list-head,
    .student-messaging .chat-pane-head {
        padding: 18px 18px 16px;
        border-bottom: 1px solid var(--line);
        background: rgba(37, 99, 235, 0.03);
    }

    .student-messaging .thread-list-head h2,
    .student-messaging .chat-pane-head h2 {
        margin: 0;
        font-size: 1.18rem;
        letter-spacing: -0.03em;
    }

    .student-messaging .thread-list-head p,
    .student-messaging .chat-pane-head p {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: .9rem;
        line-height: 1.6;
    }

    .student-messaging .thread-toolbar {
        padding: 14px 18px;
        display: grid;
        gap: 12px;
        border-bottom: 1px solid var(--line);
    }

    .student-messaging .search-wrap {
        position: relative;
    }

    .student-messaging .search-wrap input {
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

    .student-messaging .search-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
    }

    .student-messaging .search-wrap svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--muted);
        pointer-events: none;
    }

    .student-messaging .thread-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging .thread-filter {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--muted);
        font-size: .82rem;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .student-messaging .thread-filter.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        box-shadow: 0 10px 24px rgba(37,99,235,.20);
    }

    .student-messaging .thread-list {
        display: grid;
        max-height: 560px;
        overflow: auto;
    }

    .student-messaging .thread-item {
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
    }

    .student-messaging .thread-item:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .student-messaging .thread-item.is-active {
        background: rgba(37, 99, 235, 0.08);
    }

    .student-messaging .thread-item.is-hidden {
        display: none;
    }

    .student-messaging .thread-item__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .student-messaging .thread-item__main {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 0;
    }

    .student-messaging .avatar {
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

    .student-messaging .thread-item__text {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .student-messaging .thread-item__text strong {
        font-size: .98rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-messaging .thread-item__meta {
        color: var(--muted);
        font-size: .82rem;
        line-height: 1.45;
    }

    .student-messaging .thread-item__time {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .student-messaging .thread-item__subject {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .student-messaging .thread-snippet {
        color: var(--muted);
        font-size: .86rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .student-messaging .badge {
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

    .student-messaging .badge--unread {
        background: rgba(37,99,235,.10);
        color: var(--primary);
        border-color: rgba(37,99,235,.16);
    }

    .student-messaging .badge--answered {
        background: rgba(22,163,74,.10);
        color: #15803d;
        border-color: rgba(22,163,74,.18);
    }

    .student-messaging .badge--pending {
        background: rgba(245,158,11,.12);
        color: #d97706;
        border-color: rgba(245,158,11,.18);
    }

    .student-messaging .badge--closed {
        background: rgba(100,116,139,.12);
        color: var(--muted);
        border-color: var(--line);
    }

    .student-messaging .badge--read {
        background: rgba(100,116,139,.10);
        color: var(--muted);
        border-color: var(--line);
    }

    .student-messaging .badge--attachment {
        background: rgba(124,58,237,.10);
        color: #7c3aed;
        border-color: rgba(124,58,237,.18);
    }

    .student-messaging .chat-pane {
        display: none;
        grid-template-rows: auto 1fr auto;
        min-height: 100%;
    }

    .student-messaging .chat-pane.is-active {
        display: grid;
    }

    .student-messaging .chat-pane-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .student-messaging .chat-pane-head__left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .student-messaging .chat-pane-head__text {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .student-messaging .chat-pane-head__text strong {
        font-size: 1rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .student-messaging .chat-pane-head__text span {
        color: var(--muted);
        font-size: .84rem;
        line-height: 1.45;
    }

    .student-messaging .chat-pane-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging .chat-scroll {
        padding: 18px;
        display: grid;
        gap: 14px;
        min-height: 420px;
        max-height: 560px;
        overflow: auto;
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.03), transparent 24%),
            linear-gradient(180deg, rgba(37,99,235,.01), transparent 18%);
    }

    .student-messaging .message-group {
        display: grid;
        gap: 12px;
    }

    .student-messaging .message-date {
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

    .student-messaging .bubble-row {
        display: flex;
    }

    .student-messaging .bubble-row--me {
        justify-content: flex-end;
    }

    .student-messaging .bubble-row--teacher {
        justify-content: flex-start;
    }

    .student-messaging .bubble {
        max-width: min(720px, 88%);
        padding: 16px;
        border-radius: 22px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-xs);
        display: grid;
        gap: 10px;
    }

    .student-messaging .bubble--me {
        background: linear-gradient(180deg, #eef5ff, #f7faff);
        border-color: #d8e6fb;
    }

    .student-messaging .bubble--teacher {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    html[data-theme='dark'] .student-messaging .bubble--me {
        background: linear-gradient(180deg, #10203a, #142a46);
        border-color: rgba(110,161,255,.18);
    }

    .student-messaging .bubble__meta {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .student-messaging .bubble__title {
        font-size: 1rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .student-messaging .bubble__text {
        color: var(--text);
        line-height: 1.72;
        font-size: .94rem;
        word-break: break-word;
    }

    .student-messaging .attachment-box {
        display: grid;
        gap: 10px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.56);
    }

    html[data-theme='dark'] .student-messaging .attachment-box {
        background: rgba(15, 23, 42, 0.22);
    }

    .student-messaging .attachment-file {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .student-messaging .attachment-file__icon {
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

    .student-messaging .attachment-file__text {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .student-messaging .attachment-file__text strong {
        font-size: .92rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .student-messaging .attachment-file__text span {
        color: var(--muted);
        font-size: .8rem;
    }

    .student-messaging .attachment-image-link {
        display: block;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--line);
        background: var(--panel);
    }

    .student-messaging .attachment-image {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
        display: block;
    }

    .student-messaging .attachment-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .student-messaging .attachment-actions a {
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

    .student-messaging .chat-pane-footer {
        padding: 16px 18px;
        border-top: 1px solid var(--line);
        background: linear-gradient(180deg, rgba(37,99,235,.02), transparent);
        display: grid;
        gap: 12px;
    }

    .student-messaging .composer {
        display: grid;
        gap: 10px;
    }

    .student-messaging .composer textarea {
        width: 100%;
        min-height: 110px;
        resize: vertical;
        border-radius: 20px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--text);
        padding: 14px 16px;
        outline: none;
        transition: .2s ease;
    }

    .student-messaging .composer textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37,99,235,.10);
    }

    .student-messaging .composer-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .student-messaging .composer-hint {
        color: var(--muted);
        font-size: .84rem;
        line-height: 1.5;
    }

    .student-messaging .chat-empty {
        min-height: 100%;
        display: grid;
        place-items: center;
        padding: 28px;
        text-align: center;
        color: var(--muted);
    }

    .student-messaging .chat-empty__box {
        max-width: 420px;
        display: grid;
        gap: 12px;
    }

    .student-messaging .chat-empty__icon {
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

    .student-messaging .mobile-back {
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

    .student-messaging .mobile-compose {
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
        .student-messaging .page-hero__grid,
        .student-messaging .chat-shell {
            grid-template-columns: 1fr;
        }

        .student-messaging .hero-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .student-messaging .thread-list {
            max-height: none;
        }

        .student-messaging .chat-scroll {
            max-height: none;
        }
    }

    @media (max-width: 720px) {
        .student-messaging {
            gap: 16px;
        }

        .student-messaging .page-hero,
        .student-messaging .thread-list-card,
        .student-messaging .chat-pane-card {
            border-radius: 22px;
        }

        .student-messaging .page-hero {
            padding: 18px 14px;
        }

        .student-messaging .page-hero h1 {
            max-width: none;
            font-size: clamp(1.6rem, 8vw, 2.35rem);
        }

        .student-messaging .page-hero p {
            font-size: .9rem;
            line-height: 1.6;
        }

        .student-messaging .hero-actions {
            justify-content: flex-start;
        }

        .student-messaging .hero-stats {
            grid-template-columns: 1fr 1fr;
        }

        .student-messaging .chat-shell {
            min-height: auto;
        }

        .student-messaging .thread-list-card,
        .student-messaging .chat-pane-card {
            min-height: auto;
        }

        .student-messaging .chat-pane-card {
            display: none;
        }

        .student-messaging.is-thread-open .thread-list-card {
            display: none;
        }

        .student-messaging.is-thread-open .chat-pane-card {
            display: block;
        }

        .student-messaging .mobile-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .student-messaging .mobile-compose {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .student-messaging.is-thread-open .mobile-compose {
            display: none;
        }

        .student-messaging .thread-list-head,
        .student-messaging .thread-toolbar,
        .student-messaging .chat-pane-head,
        .student-messaging .chat-pane-footer,
        .student-messaging .chat-scroll,
        .student-messaging .wow-card__body,
        .student-messaging .panel__item {
            padding-left: 14px;
            padding-right: 14px;
        }

        .student-messaging .bubble {
            max-width: 100%;
        }

        .student-messaging .composer-actions {
            align-items: stretch;
        }

        .student-messaging .composer-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .student-messaging .hero-stats {
            grid-template-columns: 1fr;
        }

        .student-messaging .thread-item,
        .student-messaging .chat-scroll,
        .student-messaging .chat-pane-footer {
            padding-left: 12px;
            padding-right: 12px;
        }

        .student-messaging .thread-item__main {
            gap: 10px;
        }

        .student-messaging .avatar {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            border-radius: 14px;
            font-size: .9rem;
        }

        .student-messaging .attachment-image {
            max-height: 210px;
        }
    }
</style>
@endpush

@section('content')
<section class="student-messaging" id="studentMessaging">
    <div class="page-hero">
        <div class="page-hero__grid">
            <div class="page-hero__left">
                <span class="hero-badge">💬 Messagerie élève</span>
                <h1>Mes échanges avec les enseignants</h1>
                <p>Retrouvez vos conversations, les réponses et les pièces jointes dans une vue plus moderne, plus rapide et plus pratique.</p>
            </div>

            <div class="page-hero__right">
                <div class="hero-stats">
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

                <div class="hero-actions">
                    <a href="{{ route('student.messages.create') }}" class="btn btn--primary">Nouveau message</a>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-shell">
        <section class="thread-list-card">
            <div class="thread-list-head">
                <h2>Conversations</h2>
                <p>Recherchez, filtrez et ouvrez rapidement la bonne discussion.</p>
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
                        $attachmentName = $message->attachment_name ?: basename((string) $message->attachment_path);
                        $extension = strtolower(pathinfo((string) $attachmentName, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
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
                                </div>
                            </div>

                            <div class="chat-pane-actions">
                                <span class="badge badge--{{ $statusUi['class'] }}">{{ $statusUi['label'] }}</span>
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
                            <div class="composer">
                                <textarea placeholder="Pour continuer cet échange, utilisez le bouton Nouveau message ci-dessous."></textarea>

                                <div class="composer-actions">
                                    <div class="composer-hint">
                                        Interface modernisée de messagerie. Pour envoyer un nouveau message, utilisez l’action prévue.
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
        const root = document.getElementById('studentMessaging');
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
