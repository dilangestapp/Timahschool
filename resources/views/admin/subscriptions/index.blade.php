@extends('layouts.admin')

@section('title', 'Abonnements')
@section('page_title', 'Suivi des abonnements')
@section('page_subtitle', 'Supervisez les accès payants, les essais et les abonnements expirés dans une interface claire et professionnelle.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>subscriptions</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $items->count() }}</strong><span>abonnements affichés</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'active')->count() }}</strong><span>actifs</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'trial')->count() }}</strong><span>essais gratuits</span></div>
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
                    <p>Chaque abonnement affiche le vrai compte lié et peut être modifié manuellement.</p>
                </div>
            </div>

            <div class="admin-clean-list">
                @forelse($items as $item)
                    @php
                        $user = $item->user;
                        $userName = $user?->full_name ?: ($user?->name ?: ($user?->username ?: 'Compte non lié'));
                        $userUsername = $user?->username ? '@' . $user->username : 'identifiant absent';
                        $userEmail = $user?->email ?: '';
                        $initial = mb_strtoupper(mb_substr($userName, 0, 1));
                        $status = $item->status ?? '—';
                        $statusClass = match($status) {
                            'active' => 'admin-badge--success',
                            'trial' => 'admin-badge--trial',
                            'expired', 'cancelled', 'failed' => 'admin-badge--warning',
                            default => '',
                        };
                    @endphp
                    <article class="admin-clean-row admin-subscription-row">
                        <div class="admin-title-with-avatar">
                            <div class="admin-subscription-avatar">{{ $initial }}</div>
                            <div class="admin-clean-title">
                                <strong>{{ $userName }}</strong>
                                <span>#{{ $item->id }} · {{ $userUsername }} {{ $userEmail ? '· ' . $userEmail : '' }}</span>
                            </div>
                        </div>

                        <div class="admin-clean-meta">
                            <strong>{{ $item->plan?->name ?? $item->plan_name ?? 'Plan non défini' }}</strong><br>
                            <span class="admin-badge {{ $statusClass }}">{{ $status }}</span>
                            @if($item->is_trial)
                                <span class="admin-badge admin-badge--trial">essai</span>
                            @endif
                        </div>

                        <div class="admin-period-pill">
                            <span><b>Début :</b> {{ !empty($item->starts_at) ? \Illuminate\Support\Carbon::parse($item->starts_at)->format('d/m/Y H:i') : '—' }}</span>
                            <span><b>Fin :</b> {{ !empty($item->ends_at) ? \Illuminate\Support\Carbon::parse($item->ends_at)->format('d/m/Y H:i') : '—' }}</span>
                        </div>

                        <div class="admin-row-actions">
                            <details class="admin-edit-panel">
                                <summary>Gérer</summary>
                                <form method="POST" action="{{ route('admin.subscriptions.update', $item->id) }}" class="admin-form">
                                    @csrf
                                    <div class="admin-form-grid">
                                        <div class="form-group">
                                            <label>Plan existant</label>
                                            <select name="subscription_plan_id">
                                                <option value="">Aucun plan lié / manuel</option>
                                                @foreach($plans as $plan)
                                                    <option value="{{ $plan->id }}" @selected((int)($item->subscription_plan_id ?? 0) === (int)$plan->id)>
                                                        {{ $plan->name }} — {{ $plan->formatted_price ?? number_format((float) $plan->price, 0, ',', ' ') . ' ' . ($plan->currency ?? 'XAF') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Nom du plan manuel</label>
                                            <input type="text" name="plan_name" value="{{ old('plan_name', $item->plan_name) }}" placeholder="Ex: Essentiel Mensuel">
                                        </div>
                                        <div class="form-group">
                                            <label>Statut</label>
                                            <select name="status" required>
                                                @foreach(['trial' => 'Essai', 'pending' => 'En attente', 'active' => 'Actif', 'expired' => 'Expiré', 'cancelled' => 'Annulé', 'failed' => 'Échoué'] as $value => $label)
                                                    <option value="{{ $value }}" @selected(($item->status ?? '') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group form-group--check">
                                            <label><input type="checkbox" name="is_trial" value="1" @checked($item->is_trial)> Marquer comme essai gratuit</label>
                                        </div>
                                        <div class="form-group">
                                            <label>Début</label>
                                            <input type="datetime-local" name="starts_at" value="{{ $item->starts_at ? $item->starts_at->format('Y-m-d\\TH:i') : '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Fin</label>
                                            <input type="datetime-local" name="ends_at" value="{{ $item->ends_at ? $item->ends_at->format('Y-m-d\\TH:i') : '' }}">
                                        </div>
                                        <div class="form-group admin-form-grid__full">
                                            <label>Note / raison</label>
                                            <input type="text" name="cancellation_reason" value="{{ old('cancellation_reason', $item->cancellation_reason) }}" placeholder="Note interne facultative">
                                        </div>
                                    </div>
                                    <div class="admin-actions">
                                        <button type="submit" class="btn btn--primary">Enregistrer l’abonnement</button>
                                    </div>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('admin.subscriptions.delete', $item->id) }}" onsubmit="return confirm('Supprimer cet abonnement ?');">
                                @csrf
                                <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                            </form>
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
