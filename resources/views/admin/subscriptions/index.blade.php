@extends('layouts.admin')

@section('title', 'Abonnements')
@section('page_title', 'Suivi des abonnements')
@section('page_subtitle', 'Superviser les accès payants, les périodes d'essai et les abonnements expirés ou à renouveler.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head admin-section__head--stack">
        <div>
            <h2>Liste des abonnements</h2>
            <p class="admin-muted">Recherche par utilisateur, email, plan ou statut.</p>
        </div>
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="admin-search-form">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un abonnement...">
            <button type="submit" class="btn btn--primary">Rechercher</button>
        </form>
    </div>

    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>subscriptions</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Plan</th>
                        <th>Statut</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>#{{ $item->id }}</td>
                            <td>
                                <strong>{{ $item->user_name ?? '—' }}</strong><br>
                                <span class="admin-muted">{{ $item->user_email ?? '' }}</span>
                            </td>
                            <td>{{ $item->plan_name ?? '—' }}</td>
                            <td><span class="admin-badge admin-badge--status">{{ $item->status ?? '—' }}</span></td>
                            <td>{{ !empty($item->starts_at) ? \Illuminate\Support\Carbon::parse($item->starts_at)->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ !empty($item->ends_at) ? \Illuminate\Support\Carbon::parse($item->ends_at)->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ !empty($item->created_at) ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty">Aucun abonnement trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
