@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie liée à vos classes')
@section('page_subtitle', 'Discutez avec vos élèves dans une interface moderne, lisible et praticable sur téléphone comme sur ordinateur.')

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
    .tm-chat {
        display: grid;
        gap: 16px;
    }

    .tm-chat__intro {
        display: grid;
        gap: 12px;
    }

    .tm-chat__intro-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .tm-chat__intro-left {
        display: grid;
        gap: 4px;
    }

    .tm-chat__intro-left h2 {
        margin: 0;
        font-size: clamp(1.45rem, 2.7vw, 2rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
    }

    .tm-chat__intro-left p {
        margin: 0;
        color: var(--teacher-muted);
        line-height: 1.6;
        font-size: .92rem;
    }

    .tm-chat__intro-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tm-pill {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(37,99,235,.08);
        border: 1px solid rgba(37,99,235,.14);
        color: #2563eb;
        font-size: .8rem;
        font-weight: 900;
    }

    .tm-shell {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 16px;
        min-height: 78vh;
        align-items: stretch;
    }

    .tm-list,
    .tm-pane-wrap {
        border: 1px solid var(--teacher-border);
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 16px 36px rgba(15,23,42,.06);
    }

    .tm-list {
        display: grid;
        grid-template-rows: auto 1fr;
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.08), transparent 24%),
            linear-gradient(180deg, #f8fbff, #f2f7ff);
    }

    html[data-theme='dark'] .tm-list {
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.03), transparent 24%),
            linear-gradient(180deg, #0f1c31, #13233d);
    }

    .tm-list__head {
        padding: 18px 18px 16px;
        border-bottom: 1px solid var(--teacher-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: linear-gradient(180deg, rgba(255,255,255,.58), rgba(255,255,255,.18));
        backdrop-filter: blur(8px);
    }

    html[data-theme='dark'] .tm-list__head {
        background: linear-gradient(180deg, rgba(15,23,42,.45), rgba(15,23,42,.18));
    }

    .tm-list__head strong {
        font-size: 1rem;
        letter-spacing: -0.02em;
    }

    .tm-list__head span {
        color: var(--teacher-muted);
        font-size: .82rem;
    }

    .tm-list__body {
        display: grid;
        overflow: auto;
        max-height: 100%;
    }

    .tm-thread {
        width: 100%;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 18px;
        border: 0;
        border-bottom: 1px solid var(--teacher-border);
        background: transparent;
        text-align: left;
        cursor: pointer;
        position: relative;
        transition: .2s ease;
    }

    .tm-thread:hover {
        background: rgba(15, 23, 42, 0.02);
    }

    html[data-theme='dark'] .tm-thread:hover {
        background: rgba(255,255,255,.03);
    }

    .tm-thread.is-active {
        background: linear-gradient(180deg, var(--tm-tone-soft), rgba(255,255,255,0));
        box-shadow: inset 0 0 0 1px var(--tm-tone-border);
    }

    .tm-thread.is-active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 12px;
        bottom: 12px;
        width: 4px;
        border-radius: 999px;
        background: var(--tm-tone-grad);
    }

    .tm-thread__avatar {
        width: 50px;
        height: 50px;
        flex: 0 0 50px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--tm-tone-grad);
        color: #fff;
        font-weight: 900;
        font-size: .96rem;
        box-shadow: 0 10px 22px rgba(15,23,42,.08);
    }

    .tm-thread__body {
        flex: 1;
        min-width: 0;
        display: grid;
        gap: 6px;
    }

    .tm-thread__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .tm-thread__name {
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: -0.02em;
        color: var(--teacher-ink);
    }

    .tm-thread__time {
        color: var(--teacher-muted);
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .tm-thread__meta {
        color: var(--teacher-muted);
        font-size: .82rem;
        line-height: 1.45;
    }

    .tm-thread__snippet {
        color: var(--teacher-muted);
        font-size: .88rem;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tm-thread__badges {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .tm-mini-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 9px;
        border-radius: 999px;
        border: 1px solid var(--teacher-border);
        font-size: .72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .tm-mini-badge--unread {
        background: var(--tm-tone-soft);
        color: var(--tm-tone);
        border-color: var(--tm-tone-border);
    }

    .tm-mini-badge--attachment {
        background: rgba(124,58,237,.10);
        color: #7c3aed;
        border-color: rgba(124,58,237,.18);
    }

    .tm-pane-wrap {
        background: linear-gradient(180deg, var(--tm-chat-bg-top), var(--tm-chat-bg-bottom));
        min-height: 78vh;
        display: grid;
    }

    html[data-theme='dark'] .tm-pane-wrap {
        background: linear-gradient(180deg, #101c31, #13233d);
    }

    .tm-pane {
        display: none;
        grid-template-rows: auto 1fr auto;
        min-height: 100%;
    }

    .tm-pane.is-active {
        display: grid;
    }

    .tm-pane__head {
        padding: 16px 18px;
        border-bottom: 1px solid var(--teacher-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: linear-gradient(180deg, rgba(255,255,255,.70), rgba(255,255,255,.28));
        backdrop-filter: blur(10px);
    }

    html[data-theme='dark'] .tm-pane__head {
        background: linear-gradient(180deg, rgba(15,23,42,.45), rgba(15,23,42,.18));
    }

    .tm-pane__head-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .tm-back {
        display: none;
        min-height: 40px;
        padding: 0 12px;
        border-radius: 12px;
        border: 1px solid var(--teacher-border);
        background: rgba(255,255,255,.7);
        color: var(--teacher-ink);
        font-weight: 800;
        cursor: pointer;
    }

    html[data-theme='dark'] .tm-back {
        background: rgba(15,23,42,.58);
        color: #fff;
    }

    .tm-pane__avatar {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--tm-tone-grad);
        color: #fff;
        font-weight: 900;
        font-size: .96rem;
    }

    .tm-pane__title {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .tm-pane__title strong {
        font-size: 1rem;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .tm-pane__title span {
        color: var(--teacher-muted);
        font-size: .82rem;
        line-height: 1.45;
    }

    .tm-pane__head-tools {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tm-subject-chip {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--tm-tone-soft);
        color: var(--tm-tone);
        border: 1px solid var(--tm-tone-border);
        font-size: .76rem;
        font-weight: 900;
    }

    .tm-pane__body {
        padding: 22px 20px;
        display: grid;
        gap: 18px;
        overflow: auto;
        background:
            radial-gradient(circle at top right, var(--tm-tone-soft), transparent 20%),
            linear-gradient(180deg, rgba(255,255,255,.24), rgba(255,255,255,.06));
    }

    html[data-theme='dark'] .tm-pane__body {
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.03), transparent 22%),
            linear-gradient(180deg, rgba(255,255,255,.01), rgba(255,255,255,.015));
    }

    .tm-day {
        justify-self: center;
        min-height: 34px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid var(--tm-tone-border);
        background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,255,255,.78));
        color: var(--tm-tone);
        font-size: .78rem;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(15,23,42,.05);
        letter-spacing: -0.01em;
    }

    html[data-theme='dark'] .tm-day {
        background: linear-gradient(180deg, rgba(15,23,42,.82), rgba(15,23,42,.62));
        box-shadow: none;
    }

    .tm-row {
        display: flex;
    }

    .tm-row--student {
        justify-content: flex-start;
    }

    .tm-row--teacher {
        justify-content: flex-end;
    }

    .tm-bubble {
        max-width: min(760px, 88%);
        padding: 16px;
        border-radius: 22px;
        border: 1px solid var(--teacher-border);
        box-shadow: 0 10px 24px rgba(15,23,42,.05);
        display: grid;
        gap: 10px;
    }

    .tm-bubble--student {
        background: linear-gradient(180deg, var(--tm-tone-soft), rgba(255,255,255,.96));
        border-color: var(--tm-tone-border);
    }

    .tm-bubble--teacher {
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.88));
    }

    html[data-theme='dark'] .tm-bubble--student {
        background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
    }

    html[data-theme='dark'] .tm-bubble--teacher {
        background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    }

    .tm-bubble__meta {
        color: var(--teacher-muted);
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .tm-bubble__title {
        font-size: .96rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--teacher-ink);
    }

    .tm-bubble__text {
        color: var(--teacher-ink);
        line-height: 1.7;
        font-size: .93rem;
        word-break: break-word;
    }

    .tm-attachment {
        display: grid;
        gap: 10px;
        padding: 12px;
        border-radius: 18px;
        border: 1px solid var(--teacher-border);
        background: rgba(255,255,255,.72);
    }

    html[data-theme='dark'] .tm-attachment {
        background: rgba(15,23,42,.3);
    }

    .tm-attachment__image-link {
        display: block;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--teacher-border);
        background: #fff;
    }

    .tm-attachment__image {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
        display: block;
    }

    .tm-file {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .tm-file__icon {
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

    .tm-file__text {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .tm-file__text strong {
        font-size: .9rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .tm-file__text span {
        color: var(--teacher-muted);
        font-size: .8rem;
    }

    .tm-audio {
        display: grid;
        gap: 8px;
    }

    .tm-audio audio {
        width: 100%;
        border-radius: 14px;
    }

    .tm-audio__badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 10px;
        border-radius: 999px;
        background: rgba(16,185,129,.10);
        color: #047857;
        border: 1px solid rgba(16,185,129,.18);
        font-size: .72rem;
        font-weight: 900;
        width: fit-content;
    }

    .tm-attachment__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tm-attachment__actions a {
        min-height: 32px;
        padding: 0 11px;
        border-radius: 999px;
        border: 1px solid var(--teacher-border);
        background: rgba(255,255,255,.9);
        color: var(--tm-tone);
        font-size: .8rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
    }

    html[data-theme='dark'] .tm-attachment__actions a {
        background: rgba(15,23,42,.65);
    }

    .tm-compose {
        padding: 16px 18px;
        border-top: 1px solid var(--teacher-border);
        background: linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.58));
        backdrop-filter: blur(10px);
        position: sticky;
        bottom: 0;
    }

    html[data-theme='dark'] .tm-compose {
        background: linear-gradient(180deg, rgba(15,23,42,.48), rgba(15,23,42,.24));
    }

    .tm-compose__form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    .tm-compose textarea {
        width: 100%;
        min-height: 110px;
        max-height: 190px;
        resize: vertical;
        border-radius: 20px;
        border: 1px solid var(--tm-tone-border);
        background: rgba(255,255,255,.92);
        color: var(--teacher-ink);
        padding: 16px;
        outline: none;
        transition: .2s ease;
        line-height: 1.6;
        font-size: .95rem;
    }

    html[data-theme='dark'] .tm-compose textarea {
        background: rgba(15,23,42,.58);
    }

    .tm-compose textarea:focus {
        border-color: var(--tm-tone);
        box-shadow: 0 0 0 4px var(--tm-tone-soft);
    }

    .tm-empty {
        min-height: 100%;
        display: grid;
        place-items: center;
        padding: 28px;
        text-align: center;
        color: var(--teacher-muted);
    }

    .tm-empty__box {
        max-width: 420px;
        display: grid;
        gap: 12px;
    }

    .tm-empty__icon {
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

    @media (max-width: 1100px) {
        .tm-shell {
            grid-template-columns: 330px minmax(0, 1fr);
        }
    }

    @media (max-width: 980px) {
        .tm-shell {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .tm-list,
        .tm-pane-wrap {
            min-height: auto;
        }

        .tm-pane-wrap {
            display: none;
        }

        .tm-chat.is-thread-open .tm-list {
            display: none;
        }

        .tm-chat.is-thread-open .tm-pane-wrap {
            display: grid;
        }

        .tm-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .tm-compose__form {
            grid-template-columns: 1fr;
        }

        .tm-compose .teacher-btn,
        .tm-compose .btn {
            width: 100%;
        }
    }

    @media (max-width: 720px) {
        .tm-list,
        .tm-pane-wrap {
            border-radius: 22px;
        }

        .tm-bubble {
            max-width: 100%;
        }

        .tm-pane__head,
        .tm-pane__body,
        .tm-compose,
        .tm-list__head,
        .tm-thread {
            padding-left: 14px;
            padding-right: 14px;
        }

        .tm-compose textarea {
            min-height: 88px;
        }
    }

    @media (max-width: 480px) {
        .tm-thread__avatar,
        .tm-pane__avatar {
            width: 44px;
            height: 44px;
            flex-basis: 44px;
            border-radius: 14px;
            font-size: .88rem;
        }

        .tm-attachment__image {
            max-height: 210px;
        }
    }
</style>
@endpush

@section('content')
<section class="tm-chat" id="tmTeacherChat">
    <div class="tm-chat__intro">
        <div class="tm-chat__intro-top">
            <div class="tm-chat__intro-left">
                <h2>Messagerie enseignants</h2>
                <p>Version mobile-first, plus proche de l’expérience élève, avec une vraie séparation entre la liste et la conversation.</p>
            </div>

            <div class="tm-chat__intro-stats">
                <span class="tm-pill">{{ $threadCollection->count() }} discussion(s)</span>
                <span class="tm-pill">{{ $threadCollection->sum('unread_count') }} non lue(s)</span>
            </div>
        </div>
    </div>

    <div class="tm-shell">
        <aside class="tm-list">
            <div class="tm-list__head">
                <strong>Élèves</strong>
                <span>{{ $threadCollection->count() }} conversation(s)</span>
            </div>

            <div class="tm-list__body">
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
                        class="tm-thread {{ $isActive ? 'is-active' : '' }}"
                        data-thread-key="{{ $thread->thread_key }}"
                        style="--tm-tone: {{ $tone['color'] }}; --tm-tone-soft: {{ $tone['soft'] }}; --tm-tone-border: {{ $tone['border'] }}; --tm-tone-grad: {{ $tone['grad'] }}; --tm-chat-bg-top: {{ $tone['chat'] }}; --tm-chat-bg-bottom: #ffffff;"
                    >
                        <span class="tm-thread__avatar">{{ $avatar }}</span>

                        <span class="tm-thread__body">
                            <span class="tm-thread__top">
                                <span class="tm-thread__name">{{ $studentName }}</span>
                                @if ($time !== '')
                                    <span class="tm-thread__time">{{ $time }}</span>
                                @endif
                            </span>

                            <span class="tm-thread__meta">{{ $subjectName }} · {{ $className }}</span>

                            <span class="tm-thread__snippet">{{ \Illuminate\Support\Str::limit(strip_tags($snippet), 72) }}</span>

                            <span class="tm-thread__badges">
                                @if ($thread->unread_count > 0)
                                    <span class="tm-mini-badge tm-mini-badge--unread">{{ $thread->unread_count }} non lu{{ $thread->unread_count > 1 ? 's' : '' }}</span>
                                @endif

                                @if ($thread->attachment_count > 0)
                                    <span class="tm-mini-badge tm-mini-badge--attachment">Pièce jointe</span>
                                @endif
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="tm-empty">
                        <div class="tm-empty__box">
                            <div class="tm-empty__icon">💬</div>
                            <strong>Aucun message pour le moment</strong>
                            <span>Les messages des élèves apparaîtront ici automatiquement.</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="tm-pane-wrap">
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
                        class="tm-pane {{ $isActive ? 'is-active' : '' }}"
                        data-pane-key="{{ $thread->thread_key }}"
                        style="--tm-tone: {{ $tone['color'] }}; --tm-tone-soft: {{ $tone['soft'] }}; --tm-tone-border: {{ $tone['border'] }}; --tm-tone-grad: {{ $tone['grad'] }}; --tm-chat-bg-top: {{ $tone['chat'] }}; --tm-chat-bg-bottom: #ffffff;"
                    >
                        <div class="tm-pane__head">
                            <div class="tm-pane__head-left">
                                <button type="button" class="tm-back" data-tm-back>← Retour</button>
                                <span class="tm-pane__avatar">{{ $avatar }}</span>

                                <div class="tm-pane__title">
                                    <strong>{{ $studentName }}</strong>
                                    <span>{{ $subjectName }} · {{ $className }}</span>
                                </div>
                            </div>

                            <div class="tm-pane__head-tools">
                                <span class="tm-subject-chip">{{ strtoupper($subjectName) }}</span>
                            </div>
                        </div>

                        <div class="tm-pane__body">
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
                                    $isAnonymousAudio = method_exists($entry, 'isAnonymousAudioAttachment') ? $entry->isAnonymousAudioAttachment() : false;
                                @endphp

                                @if ($entryDateKey !== $currentDate)
                                    @php $currentDate = $entryDateKey; @endphp
                                    <div class="tm-day">{{ $entryDateLabel }}</div>
                                @endif

                                <div class="tm-row tm-row--student">
                                    <div class="tm-bubble tm-bubble--student">
                                        <div class="tm-bubble__meta">{{ $studentName }} · {{ optional($entry->created_at)->format('H:i') }}</div>
                                        <div class="tm-bubble__title">{{ $entry->display_title ?? $entry->title ?? 'Message' }}</div>
                                        <div class="tm-bubble__text">{!! nl2br(e($entry->message)) !!}</div>

                                        @if ($attachmentUrl)
                                            <div class="tm-attachment">
                                                @if ($isImage)
                                                    <a href="{{ $attachmentUrl }}" target="_blank" class="tm-attachment__image-link">
                                                        <img src="{{ $attachmentUrl }}" alt="Pièce jointe" class="tm-attachment__image">
                                                    </a>
                                                @elseif ($isAudio)
                                                    <div class="tm-audio">
                                                        @if ($isAnonymousAudio)
                                                            <span class="tm-audio__badge">Vocal anonymisé</span>
                                                        @endif

                                                        <audio controls preload="metadata">
                                                            <source src="{{ $attachmentUrl }}">
                                                            Votre navigateur ne supporte pas l'audio.
                                                        </audio>
                                                    </div>
                                                @else
                                                    <div class="tm-file">
                                                        <span class="tm-file__icon">📎</span>
                                                        <div class="tm-file__text">
                                                            <strong>{{ $attachmentName }}</strong>
                                                            <span>Fichier joint</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="tm-attachment__actions">
                                                    <a href="{{ $attachmentUrl }}" target="_blank">Ouvrir</a>
                                                    <a href="{{ $attachmentDownloadUrl }}">Télécharger</a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($entry->reply_message))
                                    <div class="tm-row tm-row--teacher">
                                        <div class="tm-bubble tm-bubble--teacher">
                                            <div class="tm-bubble__meta">Vous · {{ optional($entry->replied_at)->format('H:i') ?: 'Réponse' }}</div>
                                            <div class="tm-bubble__text">{!! nl2br(e($entry->reply_message)) !!}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if ($thread->reply_target)
                            <div class="tm-compose">
                                <form method="POST" action="{{ route('teacher.messages.reply', $thread->reply_target) }}" class="tm-compose__form">
                                    @csrf
                                    <textarea name="reply_message" placeholder="Répondre à {{ $studentName }}..." required></textarea>
                                    <button type="submit" class="teacher-btn teacher-btn--primary">Répondre</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="tm-empty">
                    <div class="tm-empty__box">
                        <div class="tm-empty__icon">✉️</div>
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
        const root = document.getElementById('tmTeacherChat');
        if (!root) return;

        const threadButtons = Array.from(root.querySelectorAll('.tm-thread'));
        const panes = Array.from(root.querySelectorAll('.tm-pane'));
        const backButtons = Array.from(root.querySelectorAll('[data-tm-back]'));

        const activateThread = (threadKey) => {
            threadButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.threadKey === String(threadKey));
            });

            panes.forEach((pane) => {
                pane.classList.toggle('is-active', pane.dataset.paneKey === String(threadKey));
            });

            if (window.innerWidth <= 980) {
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
            if (window.innerWidth > 980) {
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
