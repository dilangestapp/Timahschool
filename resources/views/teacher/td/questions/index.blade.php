@extends('layouts.teacher')

@section('title', 'Questions TD')
@section('page_title', 'Questions liées aux TD')
@section('page_subtitle', 'Répondez aux questions des élèves sur les TD qui vous sont affectés.')

@section('content')
<div class="teacher-chat-page">
    <div class="teacher-chat-layout">
        <aside class="teacher-chat-sidebar">
            <div class="teacher-chat-sidebar__head"><h2>Conversations TD</h2><p>Questions d’élèves liées à vos TD</p></div>
            <div class="teacher-thread-list">
                @forelse($threads as $thread)
                    <a href="{{ route('teacher.td.questions.show', $thread) }}" class="teacher-thread-card {{ $selected && $selected->id === $thread->id ? 'is-active' : '' }}">
                        <div class="teacher-thread-card__avatar">{{ strtoupper(substr($thread->student->full_name ?? $thread->student->name ?? 'E', 0, 1)) }}</div>
                        <div>
                            <div class="teacher-thread-card__top"><strong>{{ $thread->student->full_name ?? $thread->student->name ?? $thread->student->username ?? '-' }}</strong><span>{{ optional($thread->last_message_at)->format('d/m H:i') }}</span></div>
                            <div class="teacher-thread-card__meta">{{ $thread->subject->name ?? '-' }} · {{ $thread->schoolClass->name ?? '-' }}</div>
                            <div class="teacher-thread-card__preview"><span>{{ $thread->tdSet->title ?? 'TD' }}</span></div>
                        </div>
                        <span class="teacher-thread-card__status teacher-thread-card__status--{{ $thread->status }}">{{ $thread->status }}</span>
                    </a>
                @empty
                    <div class="teacher-empty">Aucune question TD pour le moment.</div>
                @endforelse
            </div>
            <div style="padding: 0 14px 14px;">{{ $threads->links() }}</div>
        </aside>

        <section class="teacher-chat-window">
            @if($selected)
                <div class="teacher-chat-window__head teacher-chat-window__head--between">
                    <div>
                        <h2>{{ $selected->student->full_name ?? $selected->student->name ?? $selected->student->username ?? '-' }}</h2>
                        <p>{{ $selected->tdSet->title ?? 'TD' }} — {{ $selected->subject->name ?? '-' }}</p>
                    </div>
                    <span class="teacher-thread-card__status teacher-thread-card__status--{{ $selected->status }}">{{ $selected->status }}</span>
                </div>
                <div class="teacher-chat-messages">
                    @foreach($selected->messages as $message)
                        <div class="teacher-bubble teacher-bubble--{{ $message->sender_role === 'teacher' ? 'outgoing' : 'incoming' }}">
                            <div class="teacher-bubble__meta">{{ $message->sender->full_name ?? $message->sender->name ?? $message->sender->username ?? '-' }} · {{ $message->created_at->format('d/m/Y H:i') }}</div>
                            <div class="teacher-bubble__body">{!! $message->message_html !!}</div>
                            @if($message->attachment_path)
                                <div class="teacher-bubble__attachment">
                                    <a href="{{ route('teacher.td.questions.attachment', $message) }}">{{ $message->attachment_name ?: 'Pièce jointe' }}</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('teacher.td.questions.reply', $selected) }}" enctype="multipart/form-data" class="teacher-chat-form">
                    @csrf
                    <textarea name="message_html" placeholder="Écrire une réponse..." required></textarea>
                    <div class="teacher-actions teacher-actions--between">
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.txt">
                        <button class="teacher-btn teacher-btn--primary">Envoyer</button>
                    </div>
                </form>
            @else
                <div class="teacher-chat-placeholder"><div class="teacher-chat-placeholder__box"><h2>Choisis une conversation</h2><p>Ouvre une question TD pour lire et répondre.</p></div></div>
            @endif
        </section>
    </div>
</div>
@endsection
