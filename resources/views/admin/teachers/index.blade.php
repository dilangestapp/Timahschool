@extends('layouts.admin')

@section('title', 'Enseignants')
@section('page_title', 'Gestion des enseignants')
@section('page_subtitle', 'Créez les comptes, vérifiez les statuts et gérez les enseignants sans affichage surchargé.')

@section('content')
<div class="admin-compact-page">
    <div class="admin-summary-strip">
        <div class="admin-summary-card"><strong>{{ $teachers->count() }}</strong><span>enseignants trouvés</span></div>
        <div class="admin-summary-card"><strong>{{ $teachers->where('status', 'active')->count() }}</strong><span>actifs</span></div>
        <div class="admin-summary-card"><strong>{{ $teachers->where('status', 'inactive')->count() }}</strong><span>inactifs</span></div>
        <div class="admin-summary-card"><strong>{{ $stats['unassigned'] ?? 0 }}</strong><span>sans affectation</span></div>
    </div>

    <details class="admin-collapse-box">
        <summary>Créer un compte enseignant</summary>
        <div class="admin-collapse-box__body">
            <form method="POST" action="{{ route('admin.teachers.store') }}" class="admin-form">
                @csrf
                <div class="admin-form-grid">
                    <div class="form-group"><label>Nom complet</label><input type="text" name="full_name" value="{{ old('full_name') }}" required></div>
                    <div class="form-group"><label>Nom d'utilisateur</label><input type="text" name="username" value="{{ old('username') }}" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="Facultatif"></div>
                    <div class="form-group"><label>Téléphone</label><input type="text" name="phone" value="{{ old('phone') }}" placeholder="Facultatif"></div>
                    <div class="form-group admin-form-grid__full"><label>Mot de passe initial</label><input type="text" name="password" value="{{ old('password') }}" required></div>
                </div>
                <div class="admin-actions"><button type="submit" class="btn btn--primary">Créer l'enseignant</button></div>
            </form>
        </div>
    </details>

    <section class="admin-list-panel">
        <div class="admin-list-panel__head">
            <div>
                <h2>Comptes enseignants</h2>
                <p>Liste simple avec les informations utiles et les actions essentielles.</p>
            </div>
        </div>

        <div class="admin-clean-list">
            @forelse($teachers as $teacher)
                <article class="admin-clean-row">
                    <div class="admin-clean-title">
                        <strong>{{ $teacher->full_name ?? $teacher->name ?? '-' }}</strong>
                        <span>@{{ $teacher->username }} {{ $teacher->email ? '· ' . $teacher->email : '' }}</span>
                    </div>
                    <div class="admin-clean-meta">
                        <span class="admin-badge">{{ $teacher->status ?? 'active' }}</span><br>
                        <span>{{ $assignmentCounts[$teacher->id] ?? 0 }} affectation(s) · {{ $courseCounts[$teacher->id] ?? 0 }} cours</span>
                    </div>
                    <div class="admin-row-actions">
                        <form method="POST" action="{{ route('admin.teachers.toggle', $teacher->id) }}">
                            @csrf
                            <button type="submit" class="btn btn--ghost">{{ ($teacher->status ?? 'active') === 'active' ? 'Désactiver' : 'Réactiver' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="admin-empty-box">Aucun enseignant enregistré pour le moment.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
