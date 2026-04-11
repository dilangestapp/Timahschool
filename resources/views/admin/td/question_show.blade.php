@extends('layouts.admin')

@section('title', 'Conversation TD')
@section('page_title', 'Conversation TD')
@section('page_subtitle', 'Lecture des échanges élève ↔ enseignant, dans le contexte exact du TD.')

@section('content')
<section class="admin-panel">
    <div class="admin-panel__head"><h2>{{ $thread->tdSet->title ?? '-' }}</h2></div>
    <div class="admin-panel__body">
        <div class="admin-detail-grid">
            <div><strong>Élève</strong><span>{{ $thread->student->full_name ?? $thread->student->name ?? '-' }}</span></div>
            <div><strong>Enseignant</strong><span>{{ $thread->teacher->full_name ?? $thread->teacher->name ?? '-' }}</span></div>
            <div><strong>Matière</strong><span>{{ $thread->subject->name ?? '-' }}</span></div>
            <div><strong>Classe</strong><span>{{ $thread->schoolClass->name ?? '-' }}</span></div>
        </div>
        <div class="teacher-conversation teacher-conversation--admin">
            @forelse($thread->messages as $message)
                <article class="teacher-bubble {{ $message->sender_role === 'teacher' ? 'teacher-bubble--out' : 'teacher-bubble--in' }}">
                    <div class="teacher-bubble__meta">{{ $message->sender->full_name ?? $message->sender->name ?? '-' }} • {{ $message->created_at->format('d/m/Y H:i') }}</div>
                    @if($message->message_html)
                        <div class="teacher-bubble__body">{!! $message->message_html !!}</div>
                    @endif
                    @if($message->attachment_path)
                        <div class="teacher-doc-card">
                            <a href="{{ route('admin.td.messages.attachment', $message) }}">{{ $message->attachment_name }}</a>
                        </div>
                    @endif
                </article>
            @empty
                <div class="teacher-empty-state">Aucun message dans cette conversation.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
