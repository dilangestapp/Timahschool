@extends('layouts.admin')

@section('title', 'Appareils mobiles')
@section('page_title', 'Contrôle des appareils mobiles')
@section('page_subtitle', 'Appliquez la règle commerciale : 1 numéro WhatsApp = 1 compte = 1 abonnement = 1 appareil autorisé.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>mobile_devices</strong> est introuvable. Lancez les migrations Railway pour activer ce module.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $items->count() }}</strong><span>appareils affichés</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'active')->count() }}</strong><span>actifs</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'blocked')->count() }}</strong><span>bloqués</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'replaced')->count() }}</strong><span>remplacés</span></div>
        </div>

        <details class="admin-collapse-box" {{ $search ? 'open' : '' }}>
            <summary>Rechercher un appareil</summary>
            <div class="admin-collapse-box__body">
                <form method="GET" action="{{ route('admin.mobile-devices.index') }}" class="admin-search-form">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Nom, téléphone, appareil, modèle ou statut...">
                    <button type="submit" class="btn btn--primary">Rechercher</button>
                    @if($search)
                        <a href="{{ route('admin.mobile-devices.index') }}" class="btn btn--ghost">Réinitialiser</a>
                    @endif
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head">
                <div>
                    <h2>Appareils liés aux comptes</h2>
                    <p>Un compte actif ne doit garder qu’un seul appareil autorisé. Le transfert passe par l’administration.</p>
                </div>
            </div>

            <div class="admin-clean-list">
                @forelse($items as $device)
                    @php
                        $user = $device->user;
                        $userName = $user?->full_name ?: ($user?->name ?: ($user?->username ?: 'Compte non lié'));
                        $statusClass = match($device->status) {
                            'active' => 'admin-badge--success',
                            'blocked' => 'admin-badge--warning',
                            'replaced' => 'admin-badge--trial',
                            default => '',
                        };
                    @endphp
                    <article class="admin-clean-row">
                        <div class="admin-clean-title">
                            <strong>{{ $userName }}</strong>
                            <span>{{ $device->phone ?: ($user?->phone ?: 'Téléphone absent') }} · {{ $device->device_name ?: 'Appareil sans nom' }}</span>
                        </div>

                        <div class="admin-clean-meta">
                            <strong>{{ $device->device_model ?: 'Modèle non renseigné' }}</strong><br>
                            <span class="admin-badge {{ $statusClass }}">{{ $device->status }}</span>
                            <span class="admin-badge">{{ $device->platform ?: 'mobile' }}</span>
                        </div>

                        <div class="admin-period-pill">
                            <span><b>Activation :</b> {{ $device->first_login_at ? $device->first_login_at->format('d/m/Y H:i') : '—' }}</span>
                            <span><b>Dernière activité :</b> {{ $device->last_seen_at ? $device->last_seen_at->format('d/m/Y H:i') : '—' }}</span>
                        </div>

                        <div class="admin-row-actions">
                            @if($device->status === 'active')
                                <form method="POST" action="{{ route('admin.mobile-devices.replace', $device) }}" onsubmit="return confirm('Réinitialiser cet appareil pour permettre un transfert ?');">
                                    @csrf
                                    <button type="submit" class="btn btn--primary">Réinitialiser</button>
                                </form>
                                <form method="POST" action="{{ route('admin.mobile-devices.block', $device) }}" onsubmit="return confirm('Bloquer cet appareil ?');">
                                    @csrf
                                    <button type="submit" class="btn btn--ghost admin-btn-danger">Bloquer</button>
                                </form>
                            @else
                                <span class="admin-badge">Action déjà appliquée</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucun appareil mobile enregistré pour le moment.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
