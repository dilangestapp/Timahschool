@extends('layouts.student')

@section('title', 'Profil pédagogique')

@section('content')
@php
    $weakSubjects = $profile->weak_subjects ?? [];
    $scores = $profile->confidence_scores ?? [];
@endphp

<section style="display:grid;gap:20px;max-width:980px;margin:0 auto;">
    <div class="panel" style="padding:28px;border-radius:30px;background:linear-gradient(135deg,#eff6ff,#ffffff);border:1px solid var(--line);box-shadow:var(--shadow-lg);">
        <div style="display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <div style="display:inline-flex;padding:8px 12px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:900;font-size:.82rem;margin-bottom:14px;">Profil prêt</div>
                <h1 style="margin:0 0 10px;font-size:clamp(1.7rem,4vw,2.5rem);letter-spacing:-.05em;">Ton profil pédagogique</h1>
                <p style="margin:0;color:var(--muted);line-height:1.75;max-width:720px;">Ces informations aident la plateforme et les enseignants à mieux comprendre tes priorités.</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="topbar-btn topbar-btn--primary">Aller au tableau de bord</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
        <article style="padding:22px;border-radius:24px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);">
            <h2 style="margin:0 0 12px;font-size:1.1rem;">Objectif principal</h2>
            <p style="margin:0;color:var(--muted);line-height:1.7;">{{ $profile->main_goal ?: 'Objectif à préciser avec les prochains échanges.' }}</p>
        </article>

        <article style="padding:22px;border-radius:24px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);">
            <h2 style="margin:0 0 12px;font-size:1.1rem;">Méthode préférée</h2>
            <p style="margin:0;color:var(--muted);line-height:1.7;">{{ $profile->preferred_learning_style ?: 'À confirmer.' }}</p>
        </article>

        <article style="padding:22px;border-radius:24px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);">
            <h2 style="margin:0 0 12px;font-size:1.1rem;">Matières prioritaires</h2>
            @if(!empty($weakSubjects))
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach($weakSubjects as $subject)
                        <span style="padding:8px 10px;border-radius:999px;background:var(--primary-soft);color:var(--primary);font-weight:900;font-size:.86rem;">{{ $subject }}</span>
                    @endforeach
                </div>
            @else
                <p style="margin:0;color:var(--muted);">À observer avec les premiers TD.</p>
            @endif
        </article>

        <article style="padding:22px;border-radius:24px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);">
            <h2 style="margin:0 0 12px;font-size:1.1rem;">Disponibilité</h2>
            <p style="margin:0;color:var(--muted);line-height:1.7;">{{ $profile->weekly_availability ?: 'Non précisée.' }}</p>
        </article>
    </div>

    <article style="padding:24px;border-radius:26px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);">
        <h2 style="margin:0 0 14px;font-size:1.25rem;">Synthèse pour le suivi</h2>
        <div style="white-space:pre-wrap;color:var(--muted);line-height:1.8;">{{ $profile->generated_summary }}</div>
    </article>
</section>

<style>
@media (max-width: 760px) {
    section > div[style*='grid-template-columns'] { grid-template-columns: 1fr !important; }
}
</style>
@endsection
