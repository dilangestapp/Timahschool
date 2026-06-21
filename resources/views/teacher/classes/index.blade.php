@extends('layouts.teacher')

@section('title', 'Mes classes affectées')
@section('page_title', 'Mes classes et matières')
@section('page_subtitle', 'Retrouvez vos classes et matières.')

@section('content')
<div class="teacher-class-grid">
    @forelse($cards as $card)
        <article class="teacher-class-card">
            <div class="teacher-class-card__top">
                <div>
                    <h3>{{ $card['assignment']->schoolClass->name ?? '-' }}</h3>
                    <p>{{ $card['assignment']->subject->name ?? '-' }}</p>
                </div>
                <span class="teacher-badge">active</span>
            </div>
            <div class="teacher-class-stats">
                <div><strong>{{ $card['course_count'] ?? 0 }}</strong><span>cours</span></div>
                <div><strong>{{ $card['td_count'] ?? 0 }}</strong><span>TD</span></div>
                <div><strong>{{ $card['open_questions'] ?? 0 }}</strong><span>questions</span></div>
            </div>
        </article>
    @empty
        <div class="teacher-empty">Aucune affectation active.</div>
    @endforelse
</div>
@endsection
