@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie liée à vos classes')
@section('page_subtitle', 'Discutez avec vos élèves dans une interface moderne, lisible et plus pratique.')

@php
    $threadCollection = collect($threads ?? []);

    $threadPalette = [
        [
            'color' => '#2563eb',
            'soft' => 'rgba(37,99,235,.10)',
            'border' => 'rgba(37,99,235,.18)',
            'grad' => 'linear-gradient(135deg,#2563eb,#4f46e5)',
            'chat' => 'linear-gradient(180deg,#f5f8ff,#eef5ff)',
        ],
        [
            'color' => '#16a34a',
            'soft' => 'rgba(22,163,74,.10)',
            'border' => 'rgba(22,163,74,.18)',
            'grad' => 'linear-gradient(135deg,#16a34a,#14b8a6)',
            'chat' => 'linear-gradient(180deg,#f4fbf7,#edf9f2)',
        ],
        [
            'color' => '#f59e0b',
            'soft' => 'rgba(245,158,11,.12)',
            'border' => 'rgba(245,158,11,.20)',
            'grad' => 'linear-gradient(135deg,#f59e0b,#f97316)',
            'chat' => 'linear-gradient(180deg,#fffaf3,#fff4e6)',
        ],
        [
            'color' => '#7c3aed',
            'soft' => 'rgba(124,58,237,.10)',
            'border' => 'rgba(124,58,237,.18)',
            'grad' => 'linear-gradient(135deg,#7c3aed,#a855f7)',
            'chat' => 'linear-gradient(180deg,#faf7ff,#f3ecff)',
        ],
        [
            'color' => '#ec4899',
            'soft' => 'rgba(236,72,153,.10)',
            'border' => 'rgba(236,72,153,.18)',
            'grad' => 'linear-gradient(135deg,#ec4899,#f43f5e)',
            'chat' => 'linear-gradient(180deg,#fff6fa,#ffeef6)',
        ],
    ];
@endphp

@push('styles')
<style>
    .wa-teacher {
        display: grid;
        gap: 16px;
    }

    .wa-teacher .teacher-btn,
    .wa-teacher .btn {
        min-height: 46px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid var(--line);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 800;
        transition: .2s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .wa-teacher .teacher-btn--primary,
    .wa-teacher .btn--primary {
        background: linear-gradient(135deg, #2563eb, #4f86ff);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 14px 28px rgba(37,99,235,.22);
    }

    .wa-teacher .teacher-btn--ghost,
    .wa-teacher .btn--ghost {
        background: rgba(255,255,255,.72);
        color: var(--text);
        border-color: var(--line);
        backdrop-filter: blur(8px);
    }

    html[data-theme='dark'] .wa-teacher .teacher-btn--ghost,
    html[data-theme='dark'] .wa-teacher .btn--ghost {
        background: rgba(15,23,42,.58);
    }

    .wa-teacher__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .wa-teacher__top-left {
        display: grid;
        gap: 4px;
    }

    .wa-teacher__top-left h1 {
        margin: 0;
        font-size: clamp(1.5rem, 2.6vw, 2.1rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
    }

    .wa-teacher__top-left p {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
        font-size: .92rem;
    }

    .wa-teacher__top-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .wa-top-pill {
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(37,99,235,.08);
        border: 1px solid rgba(37,99,235,.14);
        color: var(--primary);
        font-size: .8rem;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
    }

    .wa-teacher__shell {
        display: grid;
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 16px;
        min-height: 76vh;
        align-items: stretch;
    }

    .wa-teacher__sidebar,
    .wa-teacher__chat {
        border: 1px solid var(--line);
        border-radius: 28px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .wa-teacher__sidebar {
        display: grid;
        grid-template-rows: auto 1fr;
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.08), transparent 24%),
            linear-gradient(180deg, #f8fbff, #f2f7ff);
    }

    html[data-theme='dark'] .wa-teacher__sidebar {
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.03), transparent 24%),
            linear-gradient(180deg, #0f1c31, #13233d);
    }

    .wa-teacher__sidebar-head {
        padding: 16px 16px 14px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: linear-gradient(180deg, rgba(255,255,255,.58), rgba(255,255,255,.18));
        backdrop-filter: blur(8px);
    }

    html[data-theme='dark'] .wa-teacher__sidebar-head {
        background: linear-gradient(180deg, rgba(15,23,42,.45), rgba(15,23,42,.18));
    }

    .wa-teacher__sidebar-head strong {
        font-size: 1rem;
        letter-spacing: -0.02em;
    }

    .wa-teacher__sidebar-head span {
        color: var(--muted);
        font-size: .82rem;
    }

    .wa-teacher__thread-list {
        display: grid;
        overflow: auto;
        max-height: 100%;
    }

    .wa-teacher__thread {
        width: 100%;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border: 0;
        border-bottom: 1px solid var(--line);
        background: transparent;
        text-align: left;
        cursor: pointer;
        transition: .2s ease;
        position: relative;
    }

    .wa-teacher__thread:hover {
        background: rgba(15, 23, 42, 0.02);
    }

    html[data-theme='dark'] .wa-teacher__thread:hover {
        background: rgba(255,255,255,.03);
    }

    .wa-teacher__thread.is-active {
        background: linear-gradient(180deg, var(--wa-tone-soft), rgba(255,255,255,0));
        box-shadow: inset 0 0 0 1px var(--wa-tone-border);
    }

    .wa-teacher__thread.is-active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 4px;
        border-radius: 999px;
        background: var(--wa-tone-grad);
    }

    .wa-teacher__avatar {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--wa-tone-grad);
        color: #fff;
        font-weight: 900;
        font-size: .95rem;
        letter-spacing: -0.02em;
        box-shadow: var(--shadow-xs);
    }

    .wa-teacher__thread-content {
        min-width: 0;
        flex: 1;
        display: grid;
        gap: 6px;
    }

    .wa-teacher__thread-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .wa-teacher__thread-name {
        font-size: .98rem;
        font-weight: 900;
        line-height: 1.25;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .wa-teacher__thread-time {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .wa-teacher__thread-meta {
        color: var(--muted);
        font-size: .8rem;
        line-height: 1.4;
    }

    .wa-teacher__thread-snippet {
        color: var(--muted);
        font-size: .86rem;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .wa-teacher__badges {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .wa-mini-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 9px;
        border-radius: 999px;
        border: 1px solid var(--line);
        font-size: .72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .wa-mini-badge--unread {
        background: var(--wa-tone-soft);
        color: var(--wa-tone);
        border-color: var(--wa-tone-border);
    }

    .wa-mini-badge--attachment {
        background: rgba(124, 58, 237, 0.10);
        color: #7c3aed;
        border-color: rgba(124, 58, 237, 0.18);
    }

    .wa-teacher__chat {
        display: grid;
        min-height: 76vh;
        background: linear-gradient(180deg, var(--wa-chat-bg-top), var(--wa-chat-bg-bottom));
    }

    html[data-theme='dark'] .wa-teacher__chat {
        background: linear-gradient(180deg, #101c31, #13233d);
    }

    .wa-teacher__pane {
        display: none;
        grid-template-rows: auto 1fr auto;
        min-height: 100%;
    }

    .wa-teacher__pane.is-active {
        display: grid;
    }

    .wa-teacher__chat-head {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: linear-gradient(180deg, rgba(255,255,255,.70), rgba(255,255,255,.28));
        backdrop-filter: blur(10px);
    }

    html[data-theme='dark'] .wa-teacher__chat-head {
        background: linear-gradient(180deg, rgba(15,23,42,.45), rgba(15,23,42,.18));
    }

    .wa-teacher__chat-head-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .wa-teacher__back {
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

    .wa-teacher__chat-avatar {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--wa-tone-grad);
        color: #fff;
        font-weight: 900;
        font-size: .95rem;
        letter-spacing: -0.02em;
    }

    .wa-teacher__chat-title {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .wa-teacher__chat-title strong {
        font-size: 1rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .wa-teacher__chat-title span {
        color: var(--muted);
        font-size: .82rem;
        line-height: 1.45;
    }

    .wa-teacher__chat-tools {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .wa-subject-chip {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--wa-tone-soft);
        color: var(--wa-tone);
        border: 1px solid var(--wa-tone-border);
        font-size: .76rem;
        font-weight: 900;
    }

    .wa-teacher__chat-body {
        padding: 20px 18px;
        display: grid;
        gap: 16px;
        overflow: auto;
        background:
            radial-gradient(circle at top right, var(--wa-tone-soft), transparent 20%),
            linear-gradient(180deg, rgba(255,255,255,.24), rgba(255,255,255,.06));
    }

    html[data-theme='dark'] .wa-teacher__chat-body {
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.03), transparent 22%),
            linear-gradient(180deg, rgba(255,255,255,.01), rgba(255,255,255,.015));
    }

    .wa-day {
        justify-self: center;
        min-height: 34px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid var(--wa-tone-border);
        background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,255,255,.78));
        color: var(--wa-tone);
        font-size: .78rem;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.05);
        letter-spacing: -0.01em;
    }

    html[data-theme='dark'] .wa-day {
        background: linear-gradient(180deg, rgba(15,23,42,.82), rgba(15,23,42,.62));
        box-shadow: none;
    }

    .wa-bubble-row {
        display: flex;
    }

    .wa-bubble-row--student {
        justify-content: flex-start;
    }

    .wa-bubble-row--teacher {
        justify-content: flex-end;
    }

    .wa-bubble {
        max-width: min(780px, 84%);
        padding: 14px 15px;
        border-radius: 22px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-xs);
        display: grid;
        gap: 10px;
    }

    .wa-bubble--student {
        background: linear-gradient(180deg, var(--wa-tone-soft), rgba(255,255,255,.96));
        border-color: var(--wa-tone-border);
    }

    html[data-theme='dark'] .wa-bubble--student {
        background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
    }

    .wa-bubble--teacher {
        background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.84));
    }

    html[data-theme='dark'] .wa-bubble--teacher {
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
    }

    .wa-bubble__meta {
        color: var(--muted);
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .wa-bubble__title {
        font-size: .96rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--text);
    }

    .wa-bubble__text {
        color: var(--text);
        line-height: 1.7;
        font-size: .93rem;
        word-break: break-word;
    }

    .wa-attachment {
        display: grid;
        gap: 10px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.70);
    }

    html[data-theme='dark'] .wa-attachment {
        background: rgba(15, 23, 42, 0.22);
    }

    .wa-attachment__image-link {
        display: block;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--line);
        background: var(--panel);
    }

    .wa-attachment__image {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
        display: block;
    }

    .wa-file {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .wa-file__icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(124, 58, 237, 0.10);
        color: #7c3aed;
        font-size: 1rem;
        font-weight: 900;
    }

    .wa-file__text {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .wa-file__text strong {
        font-size: .9rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .wa-file__text span {
        color: var(--muted);
        font-size: .8rem;
    }

    .wa-audio-player {
        width: 100%;
        border-radius: 14px;
    }

    .wa-attachment__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .wa-attachment__actions a {
        min-height: 32px;
        padding: 0 11px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--wa-tone);
        font-size: .8rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
    }

    .wa-teacher__compose {
        padding: 14px 16px;
        border-top: 1px solid var(--line);
        display: flex;
        align-items: flex-end;
        gap: 10px;
        background: linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.58));
        backdrop-filter: blur(10px);
    }

    html[data-theme='dark'] .wa-teacher__compose {
        background: linear-gradient(180deg, rgba(15,23,42,.48), rgba(15,23,42,.24));
    }

    .wa-teacher__compose-field {
        flex: 1;
        min-width: 0;
    }

    .wa-teacher__compose-field textarea {
        width: 100%;
        min-height: 96px;
        max-height: 180px;
        resize: vertical;
        border-radius: 20px;
        border: 1px solid var(--wa-tone-border);
        background: rgba(255,255,255,.88);
        color: var(--text);
        padding: 16px 16px;
        outline: none;
        transition: .2s ease;
        line-height: 1.6;
        font-size: .95rem;
    }

    html[data-theme='dark'] .wa-teacher__compose-field textarea {
        background: rgba(15,23,42,.58);
    }

    .wa-teacher__compose-field textarea:focus {
        border-color: var(--wa-tone);
        box-shadow: 0 0 0 4px var(--wa-tone-soft);
    }

    .wa-empty {
        min-height: 100%;
        display: grid;
        place-items: center;
        padding: 28px;
        text-align: center;
        color: var(--muted);
    }

    .wa-empty__box {
        max-width: 420px;
        display: grid;
        gap: 12px;
    }

    .wa-empty__icon {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        margin: 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37,99,235,.10);
        color: #2563eb;
        font-size: 1.8rem;
        font-weight: 900;
    }

    @media (max-width: 1180px) {
        .wa-teacher__shell {
            grid-template-columns: 320px minmax(0, 1fr);
        }
    }

    @media (max-width: 900px) {
        .wa-teacher__shell {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .wa-teacher__sidebar,
        .wa-teacher__chat {
            min-height: auto;
        }

        .wa-teacher__chat {
            display: none;
        }

        .wa-teacher.is-thread-open .wa-teacher__sidebar {
            display: none;
        }

        .wa-teacher.is-thread-open .wa-teacher__chat {
            display: grid;
        }

        .wa-teacher__back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }

    @media (max-width: 720px) {
        .wa-teacher__top-left h1 {
            font-size: 1.45rem;
        }

        .wa-teacher__sidebar,
        .wa-teacher__chat {
            border-radius: 22px;
        }

        .wa-bubble {
            max-width: 100%;
        }

        .wa-teacher__compose {
            padding: 12px 14px;
        }

        .wa-teacher__compose-field textarea {
            min-height: 84px;
        }
    }

    @media (max-width: 480px) {
        .wa-teacher__thread,
        .wa-teacher__sidebar-head,
        .wa-teacher__chat-head,
        .wa-teacher__chat-body,
        .wa-teacher__compose {
            padding-left: 12px;
            padding-right: 12px;
        }

        .wa-teacher__avatar,
        .wa-teacher__chat-avatar {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            border-radius: 14px;
            font-size: .88rem;
        }

        .wa-attachment__image {
            max-height: 210px;
        }
    }
</style>
@endpush

@section('content')
<section class="wa-teacher" id="waTeacher">
    <div class="wa-teacher__top">
        <div class="wa-teacher__top-left">
            <h1>Messagerie enseignants</h1>
            <p>Choisissez un élève à gauche puis répondez directement dans une interface plus moderne et plus pratique.</p>
        </div>

        <div class="wa-teacher__top-stats">
            <span class="wa-top-pill">{{ $threadCollection->count() }} discussion(s)</span>
            <span class="wa-top-pill">{{ $threadCollection->sum('unread_count') }} non lue(s)</span>
        </div>
    </div>

    <div class="wa-teacher__shell">
        <aside class="wa-teacher__sidebar">
            <div class="wa-teacher__sidebar-head">
                <strong>Élèves</strong>
                <span>{{ $threadCollection->count() }} conversation(s)</span>
            </div>

            <div class="wa-teacher__thread-list">
                @forelse ($threadCollection as $thread)
                    @php
                        $tone = $threadPalette[$loop->index % count($threadPalette)];
                        $studentName = $thread->student->full_name ?? $thread->student->name ?? $thread->student->username ?? 'Élève';
                        $subjectName = $thread->subject->name ?? 'Matière';
                        $className = $thread->schoolClass->name ?? 'Classe';
                        $latestMessage = $thread->latest_message;
                        $snippet = $latestMessage ? ($latestMessage->reply_message ?: $latestMessage->message) : 'Commencer la discussion avec cet élève.';
                        $time = $latestMessage && $latestMessage->created_at ? $latestMessage->created_at->format('H:i') : '';
                        $avatar = collect(explode(' ', trim($studentName)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
                        $avatar = $avatar !== '' ? $avatar : 'EL';
                        $isActive = (string) $thread->thread_key === (string) $selectedThreadKey;
                    @endphp

                    <button
                        type="button"
                        class="wa-teacher__thread {{ $isActive ? 'is-active' : '' }}"
                        data-thread-key="{{ $thread->thread_key }}"
                        style="--wa-tone: {{ $tone['color'] }}; --wa-tone-soft: {{ $tone['soft'] }}; --wa-tone-border: {{ $tone['border'] }}; --wa-tone-grad: {{ $tone['grad'] }}; --wa-chat-bg-top: {{ $tone['chat'] }}; --wa-chat-bg-bottom: #ffffff;"
                    >
                        <span class="wa-teacher__avatar">{{ $avatar }}</span>

                        <span class="wa-teacher__thread-content">
                            <span class="wa-teacher__thread-row">
                                <span class="wa-teacher__thread-name">{{ $studentName }}</span>
                                @if ($time !== '')
                                    <span class="wa-teacher__thread-time">{{ $time }}</span>
                                @endif
                            </span>

                            <span class="wa-teacher__thread-meta">{{ $subjectName }} · {{ $className }}</span>

                            <span class="wa-teacher__thread-snippet">{{ \Illuminate\Support\Str::limit(strip_tags($snippet), 72) }}</span>

                            <span class="wa-teacher__badges">
                                @if ($thread->unread_count > 0)
                                    <span class="wa-mini-badge wa-mini-badge--unread">{{ $thread->unread_count }} non lu{{ $thread->unread_count > 1 ? 's' : '' }}</span>
                                @endif

                                @if ($thread->attachment_count > 0)
                                    <span class="wa-mini-badge wa-mini-badge--attachment">Pièce jointe</span>
                                @endif
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="wa-empty">
                        <div class="wa-empty__box">
                            <div class="wa-empty__icon">💬</div>
                            <strong>Aucun message pour le moment</strong>
                            <span>Les messages des élèves apparaîtront ici automatiquement.</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="wa-teacher__chat">
            @if ($threadCollection->isNotEmpty())
                @foreach ($threadCollection as $thread)
                    @php
                        $tone = $threadPalette[$loop->index % count($threadPalette)];
                        $studentName = $thread->student->full_name ?? $thread->student->name ?? $thread->student->username ?? 'Élève';
                        $subjectName = $thread->subject->name ?? 'Matière';
                        $className = $thread->schoolClass->name ?? 'Classe';
                        $avatar = collect(explode(' ', trim($studentName)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
                        $avatar = $avatar !== '' ? $avatar : 'EL';
                        $isActive = (string) $thread->thread_key === (string) $selectedThreadKey;
                    @endphp

                    <div
                        class="wa-teacher__pane {{ $isActive ? 'is-active' : '' }}"
                        data-pane-key="{{ $thread->thread_key }}"
                        style="--wa-tone: {{ $tone['color'] }}; --wa-tone-soft: {{ $tone['soft'] }}; --wa-tone-border: {{ $tone['border'] }}; --wa-tone-grad: {{ $tone['grad'] }}; --wa-chat-bg-top: {{ $tone['chat'] }}; --wa-chat-bg-bottom: #ffffff;"
                    >
                        <div class="wa-teacher__chat-head">
                            <div class="wa-teacher__chat-head-left">
                                <button type="button" class="wa-teacher__back" data-wa-teacher-back>← Retour</button>
                                <span class="wa-teacher__chat-avatar">{{ $avatar }}</span>

                                <div class="wa-teacher__chat-title">
                                    <strong>{{ $studentName }}</strong>
                                    <span>{{ $subjectName }} · {{ $className }}</span>
                                </div>
                            </div>

                            <div class="wa-teacher__chat-tools">
                                <span class="wa-subject-chip">{{ strtoupper($subjectName) }}</span>
                                @if ($thread->reply_target)
                                    <a href="{{ route('teacher.messages.show', $thread->reply_target) }}" class="teacher-btn teacher-btn--ghost">Voir direct</a>
                                @endif
                            </div>
                        </div>

                        <div class="wa-teacher__chat-body">
                            @php
                                $currentDate = null;
                            @endphp

                            @foreach ($thread->messages as $entry)
                                @php
                                    $entryDateKey = optional($entry->created_at)->format('Y-m-d');
                                    $entryDateLabel = $entry->created_at
                                        ? \Illuminate\Support\Str::ucfirst($entry->created_at->locale('fr')->translatedFormat('D d M Y'))
                                        : '';
                                    $attachmentUrl = $entry->attachment_path ? route('teacher.messages.attachment', $entry) : null;
                                    $attachmentDownloadUrl = $entry->attachment_path ? route('teacher.messages.attachment', ['message' => $entry->id, 'download' => 1]) : null;
                                    $attachmentName = $entry->attachment_name ?: basename((string) $entry->attachment_path);
                                    $isImage = method_exists($entry, 'isImageAttachment') ? $entry->isImageAttachment() : false;
                                    $isAudio = method_exists($entry, 'isAudioAttachment') ? $entry->isAudioAttachment() : false;
                                @endphp

                                @if ($entryDateKey !== $currentDate)
                                    @php $currentDate = $entryDateKey; @endphp
                                    <div class="wa-day">{{ $entryDateLabel }}</div>
                                @endif

                                <div class="wa-bubble-row wa-bubble-row--student">
                                    <div class="wa-bubble wa-bubble--student">
                                        <div class="wa-bubble__meta">{{ $studentName }} · {{ optional($entry->created_at)->format('H:i') }}</div>
                                        <div class="wa-bubble__title">{{ $entry->display_title ?? $entry->title ?? 'Message' }}</div>
                                        <div class="wa-bubble__text">{!! nl2br(e($entry->message)) !!}</div>

                                        @if ($attachmentUrl)
                                            <div class="wa-attachment">
                                                @if ($isImage)
                                                    <a href="{{ $attachmentUrl }}" target="_blank" class="wa-attachment__image-link">
                                                        <img src="{{ $attachmentUrl }}" alt="Pièce jointe" class="wa-attachment__image">
                                                    </a>
                                                @elseif ($isAudio)
                                                    <audio controls preload="none" class="wa-audio-player">
                                                        <source src="{{ $attachmentUrl }}">
                                                        Votre navigateur ne supporte pas l'audio.
                                                    </audio>
                                                @else
                                                    <div class="wa-file">
                                                        <span class="wa-file__icon">📎</span>
                                                        <div class="wa-file__text">
                                                            <strong>{{ $attachmentName }}</strong>
                                                            <span>Fichier joint</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="wa-attachment__actions">
                                                    <a href="{{ $attachmentUrl }}" target="_blank">Ouvrir</a>
                                                    <a href="{{ $attachmentDownloadUrl }}">Télécharger</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($entry->reply_message))
                                    <div class="wa-bubble-row wa-bubble-row--teacher">
                                        <div class="wa-bubble wa-bubble--teacher">
                                            <div class="wa-bubble__meta">Vous · {{ optional($entry->replied_at)->format('H:i') ?: 'Réponse' }}</div>
                                            <div class="wa-bubble__text">{!! nl2br(e($entry->reply_message)) !!}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if ($thread->reply_target)
                            <form method="POST" action="{{ route('teacher.messages.reply', $thread->reply_target) }}" class="wa-teacher__compose">
                                @csrf

                                <div class="wa-teacher__compose-field">
                                    <textarea name="reply_message" placeholder="Répondre à {{ $studentName }}..." required></textarea>
                                </div>

                                <div>
                                    <button type="submit" class="teacher-btn teacher-btn--primary">Répondre</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="wa-empty">
                    <div class="wa-empty__box">
                        <div class="wa-empty__icon">✉️</div>
                        <strong>Aucune conversation disponible</strong>
                        <span>Les nouveaux messages des élèves apparaîtront ici.</span>
                    </div>
                </div>
            @endif
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        const root = document.getElementById('waTeacher');
        if (!root) return;

        const threadButtons = Array.from(root.querySelectorAll('.wa-teacher__thread'));
        const panes = Array.from(root.querySelectorAll('.wa-teacher__pane'));
        const backButtons = Array.from(root.querySelectorAll('[data-wa-teacher-back]'));

        const activateThread = (threadKey) => {
            threadButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.threadKey === String(threadKey));
            });

            panes.forEach((pane) => {
                pane.classList.toggle('is-active', pane.dataset.paneKey === String(threadKey));
            });

            if (window.innerWidth <= 900) {
                root.classList.add('is-thread-open');
            }
        };

        threadButtons.forEach((button) => {
            button.addEventListener('click', () => activateThread(button.dataset.threadKey));
        });

        backButtons.forEach((button) => {
            button.addEventListener('click', () => {
                root.classList.remove('is-thread-open');
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) {
                root.classList.remove('is-thread-open');
            }
        });

        const firstActive = threadButtons.find((button) => button.classList.contains('is-active'));
        if (!firstActive && threadButtons.length > 0) {
            activateThread(threadButtons[0].dataset.threadKey);
        }
    })();
</script>
@endpush
