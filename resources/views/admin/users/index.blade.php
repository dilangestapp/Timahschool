@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page_title', 'Gestion complète des utilisateurs')
@section('page_subtitle', 'Créer, modifier, filtrer et supprimer les comptes de la plateforme avec attribution de rôle.')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head admin-section__head--stack">
            <div>
                <h2>Créer un nouvel utilisateur</h2>
                <p class="admin-muted">Nom, identifiant, mot de passe et rôle dès la création.</p>
            </div>
        </div>

        @if($tableMissing)
            <div class="admin-empty-box">La table <strong>users</strong> est introuvable dans la base.</div>
        @else
            <form method="POST" action="{{ route('admin.users.store') }}" class="admin-form">
                @csrf
                <div class="admin-form-grid">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Nom d'utilisateur</label>
                        <input type="text" name="username" value="{{ old('username') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="text" name="password" value="{{ old('password') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="status">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="form-group admin-form-grid__full">
                        <label>Rôle</label>
                        <select name="role_id">
                            <option value="">Sans rôle explicite</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="admin-actions">
                    <button type="submit" class="btn btn--primary">Créer l'utilisateur</button>
                </div>
            </form>
        @endif
    </section>

    <section class="admin-section">
        <div class="admin-section__head"><h2>Filtres et résumé</h2></div>
        <form method="GET" action="{{ route('admin.users.index') }}" class="admin-search-form admin-search-form--stack">
            <input type="text" name="q" value="{{ $search }}" placeholder="Nom, email, téléphone, identifiant...">
            <select name="role">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected($roleFilter === $role->name)>{{ $role->display_name ?? $role->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn--primary">Filtrer</button>
        </form>
        <div class="admin-info-list" style="margin-top:18px;">
            <div class="admin-info-item"><strong>{{ $users->count() }}</strong><span>compte(s) affiché(s)</span></div>
            <div class="admin-info-item"><strong>{{ $users->filter(fn($u) => method_exists($u, 'isAdmin') && $u->isAdmin())->count() }}</strong><span>admins</span></div>
            <div class="admin-info-item"><strong>{{ $users->filter(fn($u) => method_exists($u, 'isTeacher') && $u->isTeacher())->count() }}</strong><span>enseignants</span></div>
            <div class="admin-info-item"><strong>{{ $users->filter(fn($u) => method_exists($u, 'isStudent') && $u->isStudent())->count() }}</strong><span>élèves</span></div>
        </div>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Comptes existants</h2></div>
    @if($tableMissing)
        <div class="admin-empty-box">Impossible d’afficher les comptes.</div>
    @else
        <div class="admin-card-grid">
            @forelse($users as $user)
                <div class="admin-manage-card">
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="admin-form">
                        @csrf
                        <div class="admin-form-grid">
                            <div class="form-group">
                                <label>Nom complet</label>
                                <input type="text" name="full_name" value="{{ $user->full_name ?? $user->name ?? '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" value="{{ $user->username ?? '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="{{ $user->email ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="text" name="phone" value="{{ $user->phone ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Nouveau mot de passe</label>
                                <input type="text" name="password" placeholder="Laisser vide pour conserver">
                            </div>
                            <div class="form-group">
                                <label>Statut</label>
                                <select name="status">
                                    <option value="active" @selected(($user->status ?? 'active') === 'active')>Actif</option>
                                    <option value="inactive" @selected(($user->status ?? '') === 'inactive')>Inactif</option>
                                </select>
                            </div>
                            <div class="form-group admin-form-grid__full">
                                <label>Rôle</label>
                                <select name="role_id">
                                    <option value="">Sans rôle explicite</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected(($user->roles->first()->id ?? $user->role->id ?? null) == $role->id)>{{ $role->display_name ?? $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="admin-actions admin-actions--spread">
                            <span class="admin-badge">#{{ $user->id }} · {{ $user->email ?? 'sans email' }}</span>
                            <button type="submit" class="btn btn--primary">Enregistrer</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" onsubmit="return confirm('Supprimer ce compte ?');">
                        @csrf
                        <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                    </form>
                </div>
            @empty
                <div class="admin-empty-box">Aucun utilisateur trouvé.</div>
            @endforelse
        </div>
    @endif
</section>
@endsection
