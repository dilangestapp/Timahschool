@extends('layouts.student')

@section('title', 'Tableau de bord')

@section('content')
<section class="status-card">
    <div class="status-card__inner">
        <div class="status-card__head">
            <div>
                <h1>Bonjour, {{ auth()->user()->full_name }} 👋</h1>
                <p>Classe : <strong style="color:#fff;">{{ $studentProfile->schoolClass->name }}</strong></p>
            </div>
            <div class="status-chip">
                <strong>{{ $subscription && $subscription->isActive() ? '✓' : '!' }}</strong>
                <span>{{ $subscription && $subscription->isActive() ? 'Actif' : 'Inactif' }}</span>
            </div>
        </div>
    </div>
</section>

<div class="student-dashboard-actions">
    <a href="{{ route('student.courses.index') }}" class="btn btn--ghost">Voir mes cours</a>
    <a href="{{ route('student.td.index') }}" class="btn btn--primary">Accéder à mes TD</a>
    <a href="{{ route('student.messages.create') }}" class="btn btn--ghost">Poser une question</a>
    <a href="{{ route('student.subscription.index') }}" class="btn btn--ghost">Gérer mon abonnement</a>
</div>

<div class="card-grid">
    <article class="metric-card"><div class="metric-icon metric-icon--blue">📚</div><div><div class="metric-label">Cours disponibles</div><div class="metric-value">{{ $recentCourses->count() }}</div></div></article>
    <article class="metric-card"><div class="metric-icon metric-icon--indigo">📝</div><div><div class="metric-label">TD disponibles</div><div class="metric-value">{{ $recentTdSets->count() }}</div></div></article>
    <article class="metric-card"><div class="metric-icon metric-icon--violet">📂</div><div><div class="metric-label">TD ouverts</div><div class="metric-value">{{ $tdOpenedCount }}</div></div></article>
    <article class="metric-card"><div class="metric-icon metric-icon--green">📈</div><div><div class="metric-label">Progression</div><div class="metric-value">0%</div></div></article>
</div>

<section class="panel">
    <div class="panel__head">
        <h2>TD récents</h2>
        <span class="muted">Dernières publications de votre classe</span>
    </div>
    <div class="panel__body">
        @forelse($recentTdSets as $td)
            <div class="list-item">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div class="subject-mark" style="background-color: {{ $td->subject->color ?? '#4F46E5' }};">{{ $td->subject->initials ?? 'TD' }}</div>
                    <div>
                        <strong><a href="{{ route('student.td.show', $td) }}">{{ $td->title }}</a></strong>
                        <div class="muted">{{ $td->subject->name }}</div>
                    </div>
                </div>
                <div class="muted">{{ $td->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</div>
            </div>
        @empty
            <div class="empty-state">Aucun TD disponible pour le moment.</div>
        @endforelse
    </div>
</section>

<div class="shortcut-grid">
    <a href="{{ route('student.td.index') }}" class="shortcut-card shortcut-card--primary">
        <div class="shortcut-card__head"><div><h3>Mes TD</h3><p>Accédez aux TD, corrigés et questions.</p></div><div class="shortcut-icon">📝</div></div>
    </a>
    <a href="{{ route('student.messages.create') }}" class="shortcut-card">
        <div class="shortcut-card__head"><div><h3>Messagerie enseignant</h3><p>Posez vos questions liées à la matière ou au TD concerné.</p></div><div class="shortcut-icon">✉️</div></div>
    </a>
</div>
@endsection
