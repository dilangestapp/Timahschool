@extends('layouts.teacher')

@section('title', 'TD et corrigés')
@section('page_title', 'Tableau TD / corrigés')
@section('page_subtitle', 'Suivez les TD publiés, les copies reçues, les copies corrigées, les retards et les corrections à traiter.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head"><div><h2>Suivi des corrections</h2><p class="teacher-muted">Ce tableau sépare clairement les TD et les corrections à faire.</p></div><a href="{{ route('teacher.td.sets.create') }}" class="teacher-btn teacher-btn--primary">+ Nouveau TD</a></div>
    <div class="resp-stats-mini" style="margin-bottom:16px;">
        <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e8eaf6;color:#3949ab;">☑</div><div><div class="resp-mini-value">{{ $stats['td_total'] }}</div><div class="resp-mini-label">TD au total</div></div></div>
        <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e3f2fd;color:#1565c0;">⇣</div><div><div class="resp-mini-value">{{ $stats['submitted'] }}</div><div class="resp-mini-label">copies reçues</div></div></div>
        <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#e0f2f1;color:#00695c;">✓</div><div><div class="resp-mini-value">{{ $stats['corrected'] }}</div><div class="resp-mini-label">copies corrigées</div></div></div>
        <div class="resp-stat-mini"><div class="resp-mini-icon" style="background:#fff3e0;color:#e65100;">!</div><div><div class="resp-mini-value">{{ $stats['pending'] }}</div><div class="resp-mini-label">à corriger</div></div></div>
    </div>

    <div class="teacher-course-list">
        @forelse($rows as $row)
            <div class="teacher-course-row">
                <div>
                    <strong>{{ $row['td']->title }}</strong>
                    <small>{{ $row['td']->schoolClass->name ?? '-' }} · {{ $row['td']->subject->name ?? '-' }} · statut : {{ $row['td']->status }}</small>
                </div>
                <div class="teacher-row-actions">
                    <span class="teacher-pill">{{ $row['submitted'] }} reçues</span>
                    <span class="teacher-pill teacher-pill--alt">{{ $row['corrected'] }} corrigées</span>
                    <span class="teacher-pill" style="background:#fff3e0;color:#e65100;">{{ $row['pending'] }} à corriger</span>
                    <span class="teacher-pill" style="background:#ffebee;color:#c62828;">{{ $row['late'] }} retards</span>
                    <a href="{{ route('teacher.td.sets.edit', $row['td']->id) }}" class="teacher-btn teacher-btn--ghost">Voir / corriger</a>
                </div>
            </div>
        @empty
            <div class="teacher-empty-state"><strong>Aucun TD.</strong><p>Créez un TD pour commencer le suivi des copies et corrections.</p></div>
        @endforelse
    </div>
</section>
@endsection
