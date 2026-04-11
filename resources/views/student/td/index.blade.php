@extends('layouts.student')

@section('title', 'Mes TD')

@section('content')
<section class="panel td-panel">
    <div class="panel__head td-panel__head">
        <div>
            <h2>Mes TD</h2>
            <span class="muted">Choisissez une matière puis ouvrez le TD pour le consulter, le télécharger et voir son corrigé.</span>
        </div>
        <form method="GET" class="td-filter-form">
            <select name="subject_id">
                <option value="">Toutes les matières</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string)($filters['subject_id'] ?? '') === (string)$subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <select name="access_level">
                <option value="">Tous les accès</option>
                <option value="free" @selected(($filters['access_level'] ?? '') === 'free')>Gratuit</option>
                <option value="premium" @selected(($filters['access_level'] ?? '') === 'premium')>Premium</option>
            </select>
            <button class="btn btn--ghost">Filtrer</button>
        </form>
    </div>
    <div class="td-card-grid">
        @forelse($sets as $td)
            <article class="td-card">
                <div class="td-card__head">
                    <span class="td-badge td-badge--{{ $td->access_level }}">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span>
                    <span class="td-badge td-badge--difficulty">{{ $td->difficulty }}</span>
                </div>
                <h3>{{ $td->title }}</h3>
                <p class="muted">{{ $td->subject->name ?? '-' }} · {{ $td->schoolClass->name ?? '-' }}</p>
                <p class="muted">{{ $td->chapter_label ?: 'Sans chapitre' }}</p>
                <div class="td-card__actions">
                    <a href="{{ route('student.td.show', $td) }}" class="btn btn--primary">Ouvrir</a>
                </div>
            </article>
        @empty
            <div class="empty-state">Aucun TD disponible pour le moment.</div>
        @endforelse
    </div>
    <div style="margin-top:16px;">{{ $sets->links() }}</div>
</section>
@endsection
