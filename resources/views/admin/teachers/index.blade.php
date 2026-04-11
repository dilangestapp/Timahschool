@extends('layouts.admin')

@section('title', 'Enseignants')
@section('page_title', 'Gestion des enseignants')
@section('page_subtitle', 'Créer, activer, suivre l’activité et repérer les enseignants encore non affectés.')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head">
            <h2>Créer un compte enseignant</h2>
        </div>
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="admin-form">
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
                <div class="form-group admin-form-grid__full">
                    <label>Mot de passe initial</label>
                    <input type="text" name="password" value="{{ old('password') }}" required>
                </div>
            </div>
            <div class="admin-actions">
                <button type="submit" class="btn btn--primary">Créer l'enseignant</button>
            </div>
        </form>
    </section>

    <section class="admin-section">
        <div class="admin-section__head">
            <h2>Résumé</h2>
        </div>
        <div class="admin-info-list">
            <div class="admin-info-item"><strong>{{ $teachers->count() }}</strong><span>enseignants trouvés</span></div>
            <div class="admin-info-item"><strong>{{ $teachers->where('status', 'active')->count() }}</strong><span>actifs</span></div>
            <div class="admin-info-item"><strong>{{ $teachers->where('status', 'inactive')->count() }}</strong><span>inactifs</span></div>
        </div>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Comptes enseignants</h2></div>
    <div class="admin-card-grid">
        @forelse($teachers as $teacher)
            <div class="admin-manage-card">
                <h3>{{ $teacher->full_name ?? $teacher->name ?? '-' }}</h3>
                <p class="admin-muted" style="margin-top:6px;">@{{ $teacher->username }} {{ $teacher->email ? '· ' . $teacher->email : '' }}</p>
                <div class="admin-actions admin-actions--spread">
                    <span class="admin-badge">{{ $teacher->status ?? 'active' }}</span>
                    <form method="POST" action="{{ route('admin.teachers.toggle', $teacher->id) }}">
                        @csrf
                        <button type="submit" class="btn btn--ghost">{{ ($teacher->status ?? 'active') === 'active' ? 'Désactiver' : 'Réactiver' }}</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="admin-empty-box">Aucun enseignant enregistré pour le moment.</div>
        @endforelse
    </div>
</section>
@endsection
