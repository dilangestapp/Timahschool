@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie liée à vos classes')
@section('page_subtitle', 'Vous recevez ici uniquement les messages des élèves qui concernent vos affectations.')

@section('content')
<section class="teacher-chat-page">
    <div class="teacher-chat-layout">
        <aside class="teacher-chat-sidebar">
            <div class="teacher-chat-sidebar__head">
                <h2>Conversations</h2>
                <p>{{ is_countable($messages) ? count($messages) : 0 }} message(s)</p>
            </div>

            <form method="GET" class="teacher-chat-filter">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Non lus</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Lus</option>
                    <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Répondus</option>
                </select>
            </form>

            <div class="teacher-thread-list">
                @forelse($messages as $message)
                    @php
                        $studentName = $message->student->full_name ?? $message->student->name ?? $message->student->username ?? 'Élève';
                        $preview = $message->message ? \Illuminate\Support\Str::limit(strip_tags($message->message), 90) : 'Aucun contenu';
                        $initial = function_exists('mb_substr') ? mb_substr($studentName, 0, 1) : substr($studentName, 0, 1);
                        $title = $message->title ?: ($message->topic ?? 'Sans objet');
                    @endphp

                    <a href="{{ route('teacher.messages.show', $message) }}" class="teacher-thread-card">
                        <div class="teacher-thread-card__avatar">{{ strtoupper($initial) }}</div>
                        <div class="teacher-thread-card__body">
                            <div class="teacher-thread-card__top">
                                <strong>{{ $studentName }}</strong>
                                <span>{{ optional($message->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="teacher-thread-card__meta">
                                {{ $message->schoolClass->name ?? 'Classe inconnue' }} · {{ $message->subject->name ?? 'Matière inconnue' }}
                            </div>
                            <div class="teacher-thread-card__preview">
                                <strong>{{ $title }}</strong>
                                <span>{{ $preview }}</span>
                            </div>
                        </div>
                        <div class="teacher-thread-card__status teacher-thread-card__status--{{ $message->status }}">
                            {{ $message->status }}
                        </div>
                    </a>
                @empty
                    <div class="teacher-empty-state">Aucun message trouvé pour le moment.</div>
                @endforelse
            </div>

            @if(isset($paginator) && $paginator && $paginator->hasPages())
                <div class="teacher-pagination-wrap">
                    {{ $paginator->links() }}
                </div>
            @endif
        </aside>

        <section class="teacher-chat-placeholder">
            <div class="teacher-chat-placeholder__box">
                <h3>Sélectionnez une conversation</h3>
                <p>Ouvrez un message à gauche pour le lire comme une discussion et répondre directement à l'élève.</p>
            </div>
        </section>
    </div>
</section>
@endsection
