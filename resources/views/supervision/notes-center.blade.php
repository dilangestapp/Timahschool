@extends('layouts.teacher')

@section('title', 'Suivi pédagogique et relances')
@section('page_title', 'Suivi pédagogique et relances')
@section('page_subtitle', 'Traiter, suivre et clôturer les alertes pédagogiques liées à vos responsabilités.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');
    $responsibilities = collect();
    $notes = collect();
    $stats = ['open' => 0, 'follow_up' => 0, 'resolved' => 0, 'urgent' => 0];

    if ($schemaReady) {
        $responsibilities = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')->where('user_id', auth()->id())->where('is_active', true)->get();
        $responsibilityIds = $responsibilities->pluck('id')->all();

        if ($responsibilities->isNotEmpty()) {
            $base = \Illuminate\Support\Facades\DB::table('pedagogical_supervision_notes as n')
                ->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')
                ->leftJoin('pedagogical_responsibilities as pr', 'pr.id', '=', 'n.responsibility_id')
                ->where(function ($query) use ($responsibilityIds) {
                    $query->whereIn('n.responsibility_id', $responsibilityIds)->orWhere('n.author_id', auth()->id());
                });

            $stats['open'] = (clone $base)->where('n.status', 'open')->count();
            $stats['follow_up'] = (clone $base)->where('n.status', 'follow_up')->count();
            $stats['resolved'] = (clone $base)->where('n.status', 'resolved')->count();
            $stats['urgent'] = (clone $base)->where('n.severity', 'urgent')->where('n.status', '!=', 'resolved')->count();

            $notes = $base
                ->select('n.*', 'u.full_name', 'u.name', 'u.username', 'pr.role_title')
                ->orderByRaw("CASE n.status WHEN 'open' THEN 1 WHEN 'follow_up' THEN 2 ELSE 3 END")
                ->orderByRaw("CASE n.severity WHEN 'urgent' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                ->orderByDesc('n.id')
                ->limit(80)
                ->get();
        }
    }

    $statusLabel = fn($status) => match ($status) {
        'open' => 'Ouverte',
        'follow_up' => 'En suivi',
        'resolved' => 'Traitée',
        default => $status ?: '—',
    };

    $severityLabel = fn($severity) => match ($severity) {
        'urgent' => 'Urgent',
        'warning' => 'Attention',
        'info' => 'Info',
        default => $severity ?: 'Info',
    };

    $badgeClass = fn($value) => match ($value) {
        'urgent' => 'note-badge--danger',
        'warning', 'follow_up' => 'note-badge--warning',
        'resolved' => 'note-badge--success',
        'open' => 'note-badge--primary',
        default => 'note-badge--neutral',
    };
@endphp

<style>
    .note-wrap{display:grid;gap:18px}.note-hero{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;background:linear-gradient(135deg,#0f172a,#1d4ed8);border-radius:24px;color:#fff;padding:22px;box-shadow:0 18px 45px rgba(15,23,42,.22)}.note-hero h2{margin:4px 0;font-size:clamp(2rem,5vw,3rem)}.note-hero p{color:#dbeafe;max-width:760px}.note-actions{display:flex;gap:10px;flex-wrap:wrap}.note-btn{display:inline-flex;align-items:center;justify-content:center;min-height:38px;border:0;border-radius:12px;padding:0 12px;font-weight:900;text-decoration:none;background:#fff;color:#0f172a;cursor:pointer}.note-btn--green{background:#16a34a;color:#fff}.note-btn--amber{background:#f59e0b;color:#fff}.note-btn--blue{background:#2563eb;color:#fff}.note-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.note-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.note-card span{display:block;color:#64748b;font-weight:800}.note-card strong{font-size:2rem;color:#0f172a}.note-panel{background:#fff;border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 12px 28px rgba(15,23,42,.05);overflow:hidden}.note-table{width:100%;border-collapse:collapse}.note-table th,.note-table td{padding:13px 14px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.note-table th{background:#f8fafc;color:#334155;font-size:13px;text-transform:uppercase;letter-spacing:.03em}.note-table td{color:#0f172a}.note-muted{display:block;color:#64748b;font-size:13px;margin-top:3px}.note-badge{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:900}.note-badge--danger{background:#ffe4e6;color:#be123c}.note-badge--warning{background:#fff7ed;color:#c2410c}.note-badge--success{background:#dcfce7;color:#166534}.note-badge--primary{background:#dbeafe;color:#1d4ed8}.note-badge--neutral{background:#eef2ff;color:#3730a3}.note-inline-actions{display:flex;gap:7px;flex-wrap:wrap}.note-empty{padding:24px;text-align:center;color:#64748b}.note-footer{color:#64748b;font-weight:800;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}@media(max-width:900px){.note-hero{display:grid}.note-grid{grid-template-columns:1fr 1fr}.note-table{min-width:760px}.note-scroll{overflow-x:auto}}@media(max-width:640px){.note-grid{grid-template-columns:1fr}.note-btn,.note-actions{width:100%}}
</style>

<div class="note-wrap">
    @if(!$schemaReady)
        <section class="note-hero"><div><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore disponibles.</p></div></section>
    @elseif($responsibilities->isEmpty())
        <section class="note-hero"><div><h2>Accès réservé</h2><p>Aucune responsabilité pédagogique active n’est attribuée à ce compte.</p></div><div class="note-actions"><a class="note-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="note-hero">
            <div>
                <span class="note-badge note-badge--primary">Centre de traitement</span>
                <h2>Suivi pédagogique et relances</h2>
                <p>Traitez les alertes pédagogiques sans les confondre avec les notes scolaires : ouvrez, mettez en suivi, clôturez, puis revenez au tableau de bord.</p>
            </div>
            <div class="note-actions">
                <a class="note-btn" href="{{ route('supervision.tb') }}">← Retour TB</a>
                <a class="note-btn note-btn--blue" href="{{ url('/backoffice-access/organization') }}">Administration pédagogique</a>
            </div>
        </section>

        <section class="note-grid">
            <article class="note-card"><span>Alertes ouvertes</span><strong>{{ $stats['open'] }}</strong></article>
            <article class="note-card"><span>En suivi</span><strong>{{ $stats['follow_up'] }}</strong></article>
            <article class="note-card"><span>Alertes traitées</span><strong>{{ $stats['resolved'] }}</strong></article>
            <article class="note-card"><span>Urgentes actives</span><strong>{{ $stats['urgent'] }}</strong></article>
        </section>

        <section class="note-panel">
            <div class="note-scroll">
                <table class="note-table">
                    <thead><tr><th>Objet</th><th>Responsabilité</th><th>Cible</th><th>Niveau</th><th>Statut</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($notes as $note)
                        <tr>
                            <td><strong>{{ $note->title }}</strong><span class="note-muted">{{ $note->message ?: 'Aucun message détaillé.' }}</span></td>
                            <td>{{ $note->role_title ?: 'Responsabilité générale' }}</td>
                            <td>{{ $note->full_name ?: ($note->name ?: ($note->username ?: 'Zone suivie')) }}</td>
                            <td><span class="note-badge {{ $badgeClass($note->severity) }}">{{ $severityLabel($note->severity) }}</span></td>
                            <td><span class="note-badge {{ $badgeClass($note->status) }}">{{ $statusLabel($note->status) }}</span></td>
                            <td>
                                <div class="note-inline-actions">
                                    @if($note->status !== 'follow_up')
                                        <form method="POST" action="{{ route('responsibilities.notes.status', $note->id) }}">@csrf<input type="hidden" name="status" value="follow_up"><button class="note-btn note-btn--amber" type="submit">En suivi</button></form>
                                    @endif
                                    @if($note->status !== 'resolved')
                                        <form method="POST" action="{{ route('responsibilities.notes.status', $note->id) }}">@csrf<input type="hidden" name="status" value="resolved"><button class="note-btn note-btn--green" type="submit">Traité</button></form>
                                    @endif
                                    @if($note->status !== 'open')
                                        <form method="POST" action="{{ route('responsibilities.notes.status', $note->id) }}">@csrf<input type="hidden" name="status" value="open"><button class="note-btn" type="submit">Réouvrir</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="note-empty">Aucune alerte pédagogique à traiter.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="note-footer"><span>Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}</span><span>Une solution Cabrel Tech.</span></div>
    @endif
</div>
@endsection
