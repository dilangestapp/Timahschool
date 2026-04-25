@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page_title', 'Gestion complète des utilisateurs')
@section('page_subtitle', 'Créez, filtrez, modifiez les comptes et gérez les élèves sans afficher tous les formulaires en même temps.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>users</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $users->count() }}</strong><span>compte(s) affiché(s)</span></div>
            <div class="admin-summary-card"><strong>{{ $users->filter(fn($u) => method_exists($u, 'isAdmin') && $u->isAdmin())->count() }}</strong><span>admins</span></div>
            <div class="admin-summary-card"><strong>{{ $users->filter(fn($u) => method_exists($u, 'isTeacher') && $u->isTeacher())->count() }}</strong><span>enseignants</span></div>
            <div class="admin-summary-card"><strong>{{ $users->filter(fn($u) => method_exists($u, 'isStudent') && $u->isStudent())->count() }}</strong><span>élèves</span></div>
        </div>

        <details class="admin-collapse-box">
            <summary>Créer un nouvel utilisateur</summary>
            <div class="admin-collapse-box__body">
                <form method="POST" action="{{ route('admin.users.store') }}" class="admin-form">
                    @csrf
                    <div class="admin-form-grid">
                        <div class="form-group"><label>Nom complet</label><input type="text" name="full_name" value="{{ old('full_name') }}" required></div>
                        <div class="form-group"><label>Nom d'utilisateur</label><input type="text" name="username" value="{{ old('username') }}" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email') }}"></div>
                        <div class="form-group"><label>Téléphone</label><input type="text" name="phone" value="{{ old('phone') }}"></div>
                        <div class="form-group"><label>Mot de passe</label><input type="text" name="password" value="{{ old('password') }}" required></div>
                        <div class="form-group"><label>Statut</label><select name="status"><option value="active">Actif</option><option value="inactive">Inactif</option></select></div>
                        <div class="form-group admin-form-grid__full"><label>Rôle</label><select name="role_id"><option value="">Sans rôle explicite</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>@endforeach</select></div>
                    </div>
                    <div class="admin-actions"><button type="submit" class="btn btn--primary">Créer l'utilisateur</button></div>
                </form>
            </div>
        </details>

        <details class="admin-collapse-box" {{ $search || $roleFilter ? 'open' : '' }}>
            <summary>Rechercher et filtrer les comptes</summary>
            <div class="admin-collapse-box__body">
                <form method="GET" action="{{ route('admin.users.index') }}" class="admin-search-form admin-search-form--stack">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Nom, email, téléphone, identifiant...">
                    <select name="role"><option value="">Tous les rôles</option>@foreach($roles as $role)<option value="{{ $role->name }}" @selected($roleFilter === $role->name)>{{ $role->display_name ?? $role->name }}</option>@endforeach</select>
                    <button type="submit" class="btn btn--primary">Filtrer</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn--ghost">Réinitialiser</a>
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head"><div><h2>Comptes existants</h2><p>Chaque fiche s’ouvre seulement quand vous devez modifier les détails.</p></div></div>
            <div class="admin-clean-list">
                @forelse($users as $user)
                    @php
                        $isStudent = method_exists($user, 'isStudent') && $user->isStudent();
                        $activeSubscription = $user->subscriptions->first(function ($subscription) {
                            return in_array($subscription->status, ['active', 'trial'], true) && (!$subscription->ends_at || $subscription->ends_at->isFuture());
                        }) ?? $user->subscriptions->first();
                        $roleLabel = $user->roles->first()->display_name ?? $user->roles->first()->name ?? $user->role->display_name ?? $user->role->name ?? 'Sans rôle';
                    @endphp
                    <article class="admin-clean-row">
                        <div class="admin-clean-title">
                            <strong>{{ $user->full_name ?? $user->name ?? $user->username }}</strong>
                            <span>#{{ $user->id }} · @{{ $user->username }} · {{ $user->email ?? 'sans email' }}</span>
                        </div>
                        <div class="admin-clean-meta">
                            <span class="admin-badge">{{ $roleLabel }}</span>
                            <span class="admin-badge">{{ $user->status ?? 'active' }}</span><br>
                            @if($isStudent)
                                <span>Classe : {{ $user->studentProfile->schoolClass->name ?? 'non définie' }} · Plan : {{ $activeSubscription->plan_name ?? 'aucun' }}</span>
                            @else
                                <span>{{ $user->phone ?: 'aucun téléphone' }}</span>
                            @endif
                        </div>
                        <div class="admin-row-actions">
                            <details class="admin-edit-panel">
                                <summary>Modifier</summary>
                                <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="admin-form">
                                    @csrf
                                    <div class="admin-form-grid">
                                        <div class="form-group"><label>Nom complet</label><input type="text" name="full_name" value="{{ $user->full_name ?? $user->name ?? '' }}" required></div>
                                        <div class="form-group"><label>Username</label><input type="text" name="username" value="{{ $user->username ?? '' }}" required></div>
                                        <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ $user->email ?? '' }}"></div>
                                        <div class="form-group"><label>Téléphone</label><input type="text" name="phone" value="{{ $user->phone ?? '' }}"></div>
                                        <div class="form-group"><label>Nouveau mot de passe</label><input type="text" name="password" placeholder="Laisser vide pour conserver"></div>
                                        <div class="form-group"><label>Statut</label><select name="status"><option value="active" @selected(($user->status ?? 'active') === 'active')>Actif</option><option value="inactive" @selected(($user->status ?? '') === 'inactive')>Inactif</option></select></div>
                                        <div class="form-group admin-form-grid__full"><label>Rôle</label><select name="role_id"><option value="">Sans rôle explicite</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(($user->roles->first()->id ?? $user->role->id ?? null) == $role->id)>{{ $role->display_name ?? $role->name }}</option>@endforeach</select></div>
                                    </div>
                                    <div class="admin-actions"><button type="submit" class="btn btn--primary">Enregistrer</button></div>
                                </form>

                                @if($isStudent)
                                    <div class="admin-edit-panel" style="margin-top:14px;">
                                        <strong>Gestion élève</strong>
                                        <form method="POST" action="{{ route('admin.users.student_class.update', $user->id) }}" class="admin-form" style="margin-top:12px;">
                                            @csrf
                                            <div class="admin-form-grid">
                                                <div class="form-group admin-form-grid__full"><label>Classe de l'élève</label><select name="school_class_id"><option value="">Aucune classe</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((int)($user->studentProfile->school_class_id ?? 0) === (int)$class->id)>{{ $class->name }}</option>@endforeach</select></div>
                                            </div>
                                            <div class="admin-actions"><button type="submit" class="btn btn--primary">Changer la classe</button></div>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.student_subscription.update', $user->id) }}" class="admin-form" style="margin-top:14px;">
                                            @csrf
                                            <div class="admin-form-grid">
                                                <div class="form-group"><label>Plan</label><select name="subscription_plan_id"><option value="">Conserver / manuel</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((int)($activeSubscription->subscription_plan_id ?? 0) === (int)$plan->id)>{{ $plan->name }} — {{ $plan->formatted_price ?? number_format((float) $plan->price, 0, ',', ' ') . ' ' . ($plan->currency ?? 'XAF') }}</option>@endforeach</select></div>
                                                <div class="form-group"><label>Statut abonnement</label><select name="status" required>@foreach(['trial' => 'Essai', 'pending' => 'En attente', 'active' => 'Actif', 'expired' => 'Expiré', 'cancelled' => 'Annulé', 'failed' => 'Échoué'] as $value => $label)<option value="{{ $value }}" @selected(($activeSubscription->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
                                                <div class="form-group"><label>Début</label><input type="datetime-local" name="starts_at" value="{{ $activeSubscription?->starts_at ? $activeSubscription->starts_at->format('Y-m-d\\TH:i') : '' }}"></div>
                                                <div class="form-group"><label>Fin</label><input type="datetime-local" name="ends_at" value="{{ $activeSubscription?->ends_at ? $activeSubscription->ends_at->format('Y-m-d\\TH:i') : '' }}"></div>
                                                <div class="form-group admin-form-grid__full"><label>Note</label><input type="text" name="cancellation_reason" value="{{ $activeSubscription->cancellation_reason ?? '' }}" placeholder="Facultatif"></div>
                                            </div>
                                            <div class="admin-actions"><button type="submit" class="btn btn--primary">Mettre à jour l'abonnement</button></div>
                                        </form>
                                    </div>
                                @endif
                            </details>

                            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" onsubmit="return confirm('Supprimer ce compte ?');">@csrf<button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button></form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucun utilisateur trouvé.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
