@extends('layouts.admin')

@section('title', 'Abonnements')
@section('page_title', 'Suivi des abonnements')
@section('page_subtitle', 'Supervisez les accès payants, les essais et les abonnements expirés dans une liste plus claire.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>subscriptions</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $items->count() }}</strong><span>abonnements affichés</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'active')->count() }}</strong><span>actifs</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'trial')->count() }}</strong><span>essais</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'expired')->count() }}</strong><span>expirés</span></div>
        </div>

        <details class="admin-collapse-box" {{ $search ? 'open' : '' }}>
            <summary>Rechercher un abonnement</summary>
            <div class="admin-collapse-box__body">
                <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="admin-search-form">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Utilisateur, email, plan ou statut...">
                    <button type="submit" class="btn btn--primary">Rechercher</button>
                    @if($search)
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn--ghost">Réinitialiser</a>
                    @endif
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head">
                <div>
                    <h2>Liste des abonnements</h2>
                    <p>Une ligne par abonnement avec l’utilisateur, le plan, le statut et la période.</p>
                </div>
            </div>

            <div class="admin-clean-list">
                @forelse($items as $item)
                    <article class="admin-clean-row">
                        <div class="admin-clean-title">
                            <strong>{{ $item->user_name ?? 'Utilisateur inconnu' }}</strong>
                            <span>#{{ $item->id }} {{ $item->user_email ? '· ' . $item->user_email : '' }}</span>
                        </div>
                        <div class="admin-clean-meta">
                            <strong>{{ $item->plan_name ?? 'Plan non défini' }}</strong><br>
                            <span class="admin-badge admin-badge--status">{{ $item->status ?? '—' }}</span>
                        </div>
                        <div class="admin-clean-meta">
                            Début : {{ !empty($item->starts_at) ? \Illuminate\Support\Carbon::parse($item->starts_at)->format('d/m/Y H:i') : '—' }}<br>
                            Fin : {{ !empty($item->ends_at) ? \Illuminate\Support\Carbon::parse($item->ends_at)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucun abonnement trouvé.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
