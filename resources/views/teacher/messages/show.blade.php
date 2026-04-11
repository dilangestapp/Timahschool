@extends('layouts.teacher')

@section('title', 'Discussion avec un élève')
@section('page_title', 'Discussion avec l’élève')
@section('page_subtitle', 'Répondez directement depuis cette page.')

@section('content')
@php
    $studentName = $message->student->full_name ?? $message->student->name ?? $message->student->username ?? 'Élève';
    $subjectName = $message->subject->name ?? 'Matière inconnue';
    $className = $message->schoolClass->name ?? 'Classe inconnue';
    $title = $message->title ?: ($message->topic ?? 'Sans objet');
    $attachmentName = $message->attachment_name ?? basename((string) $message->attachment_path);
    $attachmentRoute = $message->attachment_path ? route('teacher.messages.attachment', ['message' => $message]) : null;
@endphp

<section class="teacher-chat-page">
    <div class="teacher-chat-layout teacher-chat-layout--single">
        <section class="teacher-chat-window">
            <div class="teacher-chat-window__head teacher-chat-window__head--between">
                <div>
                    <h2>{{ $studentName }}</h2>
                    <p>{{ $className }} · {{ $subjectName }}</p>
                </div>
                <a href="{{ route('teacher.messages.index') }}" class="teacher-btn teacher-btn--primary teacher-chat-back">Retour à la liste</a>
            </div>

            <div class="teacher-chat-thread">
                <div class="chat-bubble chat-bubble--student">
                    <div class="chat-bubble__meta">
                        <strong>{{ $studentName }}</strong>
                        <span>{{ optional($message->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="chat-bubble__title">{{ $title }}</div>
                    <div class="chat-bubble__text">{!! nl2br(e($message->message)) !!}</div>

                    @if($message->attachment_path)
                        <div class="chat-attachment">
                            <div class="chat-attachment__header">
                                <strong>Pièce jointe</strong>
                                <span>{{ $attachmentName }}</span>
                            </div>

                            @if($message->isImageAttachment())
                                <button
                                    type="button"
                                    class="chat-image-thumb"
                                    data-image-src="{{ $attachmentRoute }}"
                                    data-image-name="{{ $attachmentName }}"
                                >
                                    <img src="{{ $attachmentRoute }}" alt="{{ $attachmentName }}" class="chat-attachment__image">
                                    <span class="chat-image-thumb__label">Afficher en grand</span>
                                </button>
                            @else
                                <div class="chat-attachment__file">
                                    <div class="chat-file-chip">{{ strtoupper($message->attachmentExtension() ?? 'FILE') }}</div>
                                    <div class="chat-attachment__file-body">
                                        <strong>{{ $attachmentName }}</strong>
                                        <p>Document joint</p>
                                    </div>
                                </div>
                            @endif

                            <div class="chat-attachment__actions">
                                <a href="{{ $attachmentRoute }}" target="_blank">Ouvrir</a>
                                <a href="{{ route('teacher.messages.attachment', ['message' => $message, 'download' => 1]) }}">Télécharger</a>
                            </div>
                        </div>
                    @endif
                </div>

                @if($message->reply_message)
                    <div class="chat-bubble chat-bubble--teacher">
                        <div class="chat-bubble__meta">
                            <strong>Vous</strong>
                            <span>{{ optional($message->replied_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="chat-bubble__text">{!! nl2br(e($message->reply_message)) !!}</div>
                    </div>
                @endif
            </div>

            <div class="teacher-reply-box">
                <h3>Répondre</h3>
                <form method="POST" action="{{ route('teacher.messages.reply', $message) }}" class="teacher-reply-form">
                    @csrf
                    <textarea name="reply_message" rows="5" placeholder="Écrivez votre réponse ici..." required>{{ old('reply_message', $message->reply_message) }}</textarea>
                    <div class="teacher-reply-actions">
                        <button type="submit" class="teacher-btn teacher-btn--primary">Envoyer la réponse</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</section>

<div id="teacher-image-lightbox" class="teacher-image-lightbox" hidden>
    <button type="button" class="teacher-image-lightbox__backdrop" data-close-teacher-lightbox aria-label="Fermer"></button>
    <div class="teacher-image-lightbox__dialog">
        <div class="teacher-image-lightbox__top">
            <strong id="teacher-image-lightbox-title">Aperçu</strong>
            <button type="button" class="teacher-image-lightbox__close" data-close-teacher-lightbox>×</button>
        </div>
        <div class="teacher-image-lightbox__content">
            <img id="teacher-image-lightbox-img" src="" alt="Aperçu pièce jointe">
        </div>
    </div>
</div>

<script>
(function () {
    const lightbox = document.getElementById('teacher-image-lightbox');
    const image = document.getElementById('teacher-image-lightbox-img');
    const title = document.getElementById('teacher-image-lightbox-title');

    document.querySelectorAll('.chat-image-thumb').forEach((button) => {
        button.addEventListener('click', () => {
            image.src = button.dataset.imageSrc;
            image.alt = button.dataset.imageName || 'Pièce jointe';
            title.textContent = button.dataset.imageName || 'Aperçu';
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
        });
    });

    lightbox?.querySelectorAll('[data-close-teacher-lightbox]').forEach((button) => {
        button.addEventListener('click', () => {
            lightbox.hidden = true;
            image.src = '';
            document.body.style.overflow = '';
        });
    });
})();
</script>
@endsection
