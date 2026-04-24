@extends('layouts.admin')

@section('title', 'Connexions utilisateurs')
@section('page_title', 'Utilisateurs connectés et historique')
@section('page_subtitle', 'Suivez les connexions, déconnexions et derniers accès de tous les comptes de la plateforme.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head admin-section__head--stack">
        <div>
            <h2>Vue globale des connexions</h2>
            <p class="admin-muted">Un utilisateur est considéré comme récemment connecté si sa dernière connexion date de moins de 30 minutes.</p>
        </div>
        <form method="GET" action="{{ route('admin.users.activity') }}" class="admin-search-form">
            <input type="text" name="q" value="{{ $search }}" placeholder="Nom, email, téléphone, identifiant...">
            <select name="role">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected($roleFilter === $role->name)>{{ $role->display_name ?? $role->name }}</option>
                @endforeach
            </select>
            <select name="event">
                <option value="">Tous les événements</option>
                <option value="login" @selected($eventFilter === 'login')>Connexions</option>
                <option value="logout" @selected($eventFilter === 'logout')>Déconnexions</option>
            </select>
            <button type="submit" class="btn btn--primary">Filtrer</button>
        </form>
    </div>

    <div class="admin-info-list">
        <div class="admin-info-item"><strong>{{ $users->count() }}</strong><span>utilisateurs suivis</span></div>
        <div class="admin-info-item"><strong>{{ $connectedUsers->count() }}</strong><span>connectés récemment</span></div>
        <div class="admin-info-item"><strong>{{ $activities->count() }}</strong><span>événements affichés</span></div>
        <div class="admin-info-item"><strong>{{ $users->whereNotNull('last_login_at')->count() }}</strong><span>ont déjà connecté</span></div>
    </div>
</section>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Utilisateurs connectés récemment</h2></div>

    @if($connectedUsers->isEmpty())
        <div class="admin-empty-box">Aucun utilisateur connecté récemment.</div>
    @else
        <div class="admin-card-grid">
            @foreach($connectedUsers as $user)
                <article class="admin-manage-card">
                    <div class="admin-actions admin-actions--spread">
                        <div>
                            <h3 style="margin:0 0 6px;">{{ $user->full_name ?? $user->name ?? $user->username }}</h3>
                            <div class="admin-muted">{{ $user->username ?? '-' }} · {{ $user->email ?? 'sans email' }}</div>
                        </div>
                        <span class="admin-badge">En ligne récent</span>
                    </div>
                    <div class="admin-info-list" style="margin-top:14px;">
                        <div class="admin-info-item"><strong>{{ $user->last_login_at?->format('d/m/Y H:i') ?? '-' }}</strong><span>dernière connexion</span></div>
                        <div class="admin-info-item"><strong>{{ $user->last_login_ip ?? '-' }}</strong><span>adresse IP</span></div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Historique des connexions</h2></div>

    @if($activities->isEmpty())
        <div class="admin-empty-box">Aucun historique disponible pour le moment. Les nouvelles connexions seront enregistrées automatiquement.</div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Événement</th>
                        <th>IP</th>
                        <th>Navigateur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        @php($user = $activity->user)
                        <tr>
                            <td>{{ $activity->occurred_at?->format('d/m/Y H:i:s') ?? $activity->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $user->full_name ?? $user->name ?? $user->username ?? 'Utilisateur supprimé' }}</strong>
                                <div class="admin-muted">{{ $user->email ?? '-' }}</div>
                            </td>
                            <td><span class="admin-badge">{{ $activity->event === 'logout' ? 'Déconnexion' : 'Connexion' }}</span></td>
                            <td>{{ $activity->ip_address ?? '-' }}</td>
                            <td><span class="admin-muted">{{ mb_strimwidth($activity->user_agent ?? '-', 0, 90, '...') }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
