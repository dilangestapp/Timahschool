@extends('layouts.admin')

@section('title', 'Paiements')
@section('page_title', 'Suivi des paiements')
@section('page_subtitle', 'Contrôler les encaissements, les références et les paiements en attente ou en échec.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head admin-section__head--stack">
        <div>
            <h2>Liste des paiements</h2>
            <p class="admin-muted">Recherche par référence, téléphone, utilisateur ou statut.</p>
        </div>
        <form method="GET" action="{{ route('admin.payments.index') }}" class="admin-search-form">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un paiement...">
            <button type="submit" class="btn btn--primary">Rechercher</button>
        </form>
    </div>

    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>payments</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Référence</th>
                        <th>Utilisateur</th>
                        <th>Plan</th>
                        <th>Montant</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Erreur</th>
                        <th>Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>#{{ $item->id }}</td>
                            <td>{{ $item->notchpay_reference ?? '—' }}</td>
                            <td>
                                <strong>{{ $item->user_name ?? '—' }}</strong><br>
                                <span class="admin-muted">{{ $item->user_email ?? '' }}</span>
                            </td>
                            <td>{{ $item->plan_name ?? '—' }}</td>
                            <td>{{ number_format((float) ($item->amount ?? 0), 0, ',', ' ') }} {{ $item->currency ?? 'XAF' }}</td>
                            <td>{{ $item->phone_number ?? '—' }}</td>
                            <td><span class="admin-badge admin-badge--status">{{ $item->status ?? '—' }}</span></td>
                            <td>{{ $item->failure_reason ?? '—' }}</td>
                            <td>{{ !empty($item->created_at) ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="admin-empty">Aucun paiement trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
