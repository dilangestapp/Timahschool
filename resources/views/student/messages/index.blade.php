@extends('layouts.student')

@section('title', 'Messagerie')

@section('content')
<section class="student-chat-page">
    <div class="student-chat-wrap">
        <div class="student-chat-head">
            <div>
                <h1>Mes échanges avec les enseignants</h1>
                <p>Suivez vos messages, les réponses et les pièces jointes dans une vue plus claire.</p>
            </div>
            <a href="{{ route('student.messages.create') }}" class="btn btn--primary">Nouveau message</a>
        </div>

        <div class="student-chat-list">
            @forelse($messages as $message)
                @php
                    $teacherName = $message->teacher->full_name ?? $message->teacher->name ?? 'Enseignant';
                    $attachmentUrl = $message->attachment_path ? asset('storage/' . $message->attachment_path) : null;
                    $attachmentName = $message->attachment_name ?: basename((string) $message->attachment_path);
                    $extension = strtolower(pathinfo((string) $attachmentName, PATHINFO_EXTENSION));
                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                    $title = $message->title ?: ($message->topic ?? 'Sans objet');
                @endphp

                <article class="student-thread-card">
                    <header class="student-thread-card__head">
                        <div>
                            <strong>{{ $teacherName }}</strong>
                            <div class="muted">{{ $message->schoolClass->name ?? 'Classe inconnue' }} · {{ $message->subject->name ?? 'Matière inconnue' }}</div>
                        </div>
                        <span class="student-thread-card__status student-thread-card__status--{{ $message->status }}">{{ $message->status }}</span>
                    </header>

                    <div class="student-bubble student-bubble--me">
                        <div class="student-bubble__meta">Vous · {{ optional($message->created_at)->format('d/m/Y H:i') }}</div>
                        <div class="student-bubble__title">{{ $title }}</div>
                        <div class="student-bubble__text">{!! nl2br(e($message->message)) !!}</div>

                        @if($attachmentUrl)
                            <div class="student-attachment">
                                @if($isImage)
                                    <a href="{{ $attachmentUrl }}" target="_blank" class="student-attachment__image-link">
                                        <img src="{{ $attachmentUrl }}" alt="Pièce jointe" class="student-attachment__image">
                                    </a>
                                    <div class="student-attachment__actions">
                                        <a href="{{ $attachmentUrl }}" target="_blank">Voir en grand</a>
                                        <a href="{{ $attachmentUrl }}" download>Télécharger</a>
                                    </div>
                                @else
                                    <div class="student-attachment__file">
                                        <strong>{{ $attachmentName }}</strong>
                                        <div class="student-attachment__actions">
                                            <a href="{{ $attachmentUrl }}" target="_blank">Ouvrir</a>
                                            <a href="{{ $attachmentUrl }}" download>Télécharger</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if(!empty($message->reply_message))
                        <div class="student-bubble student-bubble--teacher">
                            <div class="student-bubble__meta">{{ $teacherName }} · {{ optional($message->replied_at)->format('d/m/Y H:i') }}</div>
                            <div class="student-bubble__text">{!! nl2br(e($message->reply_message)) !!}</div>
                        </div>
                    @endif
                </article>
            @empty
                <div class="empty-state">Aucune conversation pour le moment.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
