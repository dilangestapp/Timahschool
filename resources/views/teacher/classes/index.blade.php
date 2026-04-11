@extends('layouts.teacher')

@section('title', 'Mes classes affectées')
@section('page_title', 'Mes classes et matières')
@section('page_subtitle', 'Cette page résume exactement ce que vous êtes autorisé à gérer.')

@section('content')
<div class="teacher-class-grid">
    @forelse($cards as $card)
        <article class="teacher-class-card">
            <div class="teacher-class-card__top">
                <div>
                    <h3>{{ $card['assignment']->schoolClass->name ?? '-' }}</h3>
                    <p>{{ $card['assignment']->subject->name ?? '-' }}</p>
                </div>
                <span class="teacher-badge">{{ $card['assignment']->is_active ? 'active' : 'inactive' }}</span>
            </div>
            <div class="teacher-class-stats">
                <div><strong>{{ $card['course_count'] }}</strong><span>cours liés</span></div>
                <div><strong>{{ $card['unread_messages'] }}</strong><span>messages non lus</span></div>
            </div>
            <p class="teacher-muted">{{ $card['assignment']->notes ?: 'Aucune note d’affectation.' }}</p>
        </article>
    @empty
        <div class="teacher-empty">Aucune affectation active pour le moment.</div>
    @endforelse
</div>
@endsection
