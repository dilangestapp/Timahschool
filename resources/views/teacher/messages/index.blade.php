@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie')
@section('page_subtitle', 'Conversations élèves, messages de classe, fichiers, réponses et non lus.')

@php
    $threadCollection = collect($threads ?? []);
    $selected = $selectedThread ?? null;
    $selectedMessages = $selected ? collect($selected->messages ?? []) : collect();
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/teacher-messenger.css') }}">
@endpush

@section('content')
<div class="wa-wrap">
    <div class="wa-top">
        <div class="wa-title">
            <h2>Messagerie enseignants</h2>
            <p>Interface de conversation rapide entre l’enseignant et ses élèves.</p>
        </div>
        <div class="wa-stats">
            <span class="wa-pill">{{ $threadCollection->count() }} élève(s)</span>
            <span class="wa-pill">{{ $threadCollection->sum('unread_count') }} non lu(s)</span>
            <span class="wa-pill">{{ $threadCollection->sum('attachment_count') }} fichier(s)</span>
        </div>
    </div>

    @if(collect($assignments ?? [])->isNotEmpty())
        <details class="wa-broadcast">
            <summary>Message à toute une classe</summary>
            <form method="POST" action="{{ route('teacher.messages.broadcast') }}">
                @csrf
                <select name="school_class_id" class="wa-select" required>
                    @foreach(collect($assignments)->unique('school_class_id') as $assignment)
                        <option value="{{ $assignment->school_class_id }}">{{ $assignment->schoolClass->name ?? 'Classe' }}</option>
                    @endforeach
                </select>
                <input type="text" name="message" class="wa-input" placeholder="Message à envoyer à toute la classe" required>
                <button class="wa-btn wa-btn-primary" type="submit">Envoyer</button>
            </form>
        </details>
    @endif

    <div class="wa-shell">
        <aside class="wa-list">
            <div class="wa-list-head">
                <strong>Conversations</strong>
                <input class="wa-search" type="search" placeholder="Rechercher un élève" data-wa-search>
            </div>
            <div class="wa-list-body">
                @forelse($threadCollection as $thread)
                    @php
                        $student = $thread->student;
                        $name = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                        $initials = collect(explode(' ', trim($name)))->filter()->take(2)->map(fn($p)=>mb_substr($p,0,1))->implode('') ?: 'E';
                        $latest = $thread->latest_message;
                    @endphp
                    <a href="{{ route('teacher.messages.index', ['student' => $student->id]) }}" class="wa-thread {{ (int)$selectedStudentId === (int)$student->id ? 'active' : '' }}" data-wa-thread data-name="{{ strtolower($name) }}">
                        <div class="wa-avatar">{{ strtoupper($initials) }}</div>
                        <div class="wa-thread-body">
                            <div class="wa-thread-top"><div class="wa-thread-name">{{ $name }}</div><div class="wa-time">{{ $latest?->created_at?->format('H:i') }}</div></div>
                            <div class="wa-snippet">{{ $latest ? \Illuminate\Support\Str::limit($latest->message, 58) : 'Aucun message' }}</div>
                            <div class="wa-meta">{{ $student->studentProfile->schoolClass->name ?? 'Classe inconnue' }} @if($thread->unread_count)<span class="wa-unread">{{ $thread->unread_count }}</span>@endif</div>
                        </div>
                    </a>
                @empty
                    <div class="wa-empty">Aucun élève trouvé.</div>
                @endforelse
            </div>
        </aside>

        <main class="wa-chat">
            @if($selected)
                @php
                    $student = $selected->student;
                    $studentName = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                    $studentInitials = collect(explode(' ', trim($studentName)))->filter()->take(2)->map(fn($p)=>mb_substr($p,0,1))->implode('') ?: 'E';
                @endphp
                <div class="wa-chat-head">
                    <div class="wa-chat-person"><div class="wa-avatar">{{ strtoupper($studentInitials) }}</div><div><div class="wa-chat-name">{{ $studentName }}</div><div class="wa-chat-sub">{{ $student->studentProfile->schoolClass->name ?? 'Classe inconnue' }}</div></div></div>
                    <a class="wa-btn wa-btn-soft" href="{{ route('teacher.messages.index', ['student' => $student->id, 'refresh' => time()]) }}">Actualiser</a>
                </div>

                <div class="wa-messages" id="wa-messages">
                    @forelse($selectedMessages as $message)
                        @php $isMe = $message->isFromTeacher(); $parent = $message->parentMessage; @endphp
                        <div class="wa-bubble-row {{ $isMe ? 'me' : 'them' }}">
                            <div class="wa-bubble {{ $isMe ? 'me' : 'them' }}">
                                @if($parent)<div class="wa-reply-quote">Réponse à : {{ \Illuminate\Support\Str::limit($parent->message, 80) }}</div>@endif
                                <div class="wa-msg-text">{{ $message->message }}</div>
                                @if($message->attachment_path)
                                    <div class="wa-attach"><a class="wa-attach-doc" href="{{ route('teacher.messages.attachment', ['message' => $message, 'download' => 1]) }}">Pièce jointe : {{ $message->attachment_name }}</a></div>
                                @endif
                                <div class="wa-msg-foot"><span>{{ $message->created_at?->format('d/m H:i') }}</span>@if($isMe)<span class="wa-check">✓✓</span>@endif</div>
                                <div class="wa-actions">
                                    <button class="wa-mini" type="button" data-reply-id="{{ $message->id }}" data-reply-text="{{ e(\Illuminate\Support\Str::limit($message->message, 90)) }}">Répondre</button>
                                    @if($isMe)<form method="POST" action="{{ route('teacher.messages.delete', $message) }}">@csrf<button class="wa-mini" type="submit">Supprimer</button></form>@endif
                                </div>
                            </div>
                        </div>
                        @if(!$message->isFromTeacher() && $message->reply_message)
                            <div class="wa-bubble-row me"><div class="wa-bubble me"><div class="wa-msg-text">{{ $message->reply_message }}</div><div class="wa-msg-foot">{{ $message->replied_at?->format('d/m H:i') }} ✓✓</div></div></div>
                        @endif
                    @empty
                        <div class="wa-empty">Aucune discussion avec cet élève.</div>
                    @endforelse
                </div>

                <div class="wa-composer">
                    <div class="wa-reply-preview" data-reply-preview><strong>Réponse</strong><br><span data-reply-preview-text></span> <button type="button" class="wa-mini" data-reply-cancel>Annuler</button></div>
                    <form class="wa-form" method="POST" action="{{ route('teacher.messages.send') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <input type="hidden" name="teacher_assignment_id" value="{{ $selected->assignment->id ?? '' }}">
                        <input type="hidden" name="parent_message_id" value="" data-reply-field>
                        <label class="wa-btn wa-btn-soft wa-file">Fichier<input type="file" name="attachment"></label>
                        <textarea class="wa-compose-text" name="message" placeholder="Écrire un message"></textarea>
                        <button class="wa-send" type="submit">➤</button>
                    </form>
                </div>
            @else
                <div class="wa-empty">Sélectionnez un élève pour ouvrir une conversation.</div>
            @endif
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/teacher-messenger.js') }}"></script>
@endpush
