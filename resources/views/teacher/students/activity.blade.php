@extends('layouts.teacher')

@section('title', 'Élèves connectés')
@section('page_title', 'Élèves connectés')
@section('page_subtitle', 'Suivi des élèves des classes où vous êtes affecté.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Suivi des élèves</h2>
            <p class="teacher-muted">Cette page affiche uniquement les élèves des classes affectées à votre compte enseignant.</p>
        </div>
        <form method="GET" action="{{ route('teacher.students.activity') }}" class="teacher-filter-inline">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un élève">
            <select name="event">
                <option value="">Tous les événements</option>
                <option value="login" @selected($eventFilter === 'login')>Connexions</option>
                <option value="logout" @selected($eventFilter === 'logout')>Déconnexions</option>
            </select>
            <button type="submit" class="teacher-btn teacher-btn--primary">Filtrer</button>
        </form>
    </div>

    <div class="teacher-stats-grid">
        <div class="teacher-stat-card"><strong>{{ $students->count() }}</strong><span>élèves suivis</span></div>
        <div class="teacher-stat-card"><strong>{{ $connectedStudents->count() }}</strong><span>connectés récemment</span></div>
        <div class="teacher-stat-card"><strong>{{ $classIds->count() }}</strong><span>classes affectées</span></div>
        <div class="teacher-stat-card"><strong>{{ $activities->count() }}</strong><span>événements affichés</span></div>
    </div>
</section>

<section class="teacher-section" style="margin-top:22px;">
    <div class="teacher-section__head"><h2>Élèves connectés récemment</h2></div>

    @if($connectedStudents->isEmpty())
        <div class="teacher-empty-row">Aucun élève connecté récemment.</div>
    @else
        <div class="teacher-card-grid">
            @foreach($connectedStudents as $profile)
                @php($studentUser = $profile->user)
                <article class="teacher-card">
                    <h3 style="margin:0 0 8px;">{{ $studentUser->full_name ?? $studentUser->name ?? $studentUser->username }}</h3>
                    <p class="teacher-muted" style="margin:0 0 12px;">{{ $profile->schoolClass->name ?? 'Classe non définie' }} · {{ $studentUser->email ?? 'sans email' }}</p>
                    <div class="teacher-actions">
                        <span class="teacher-badge teacher-badge--published">Connecté récent</span>
                        <span class="teacher-badge teacher-badge--free">{{ $studentUser->last_login_at?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="teacher-section" style="margin-top:22px;">
    <div class="teacher-section__head"><h2>Historique des élèves</h2></div>

    @if($activities->isEmpty())
        <div class="teacher-empty-row">Aucun historique disponible pour les élèves de vos classes.</div>
    @else
        <div class="teacher-table-wrap">
            <table class="teacher-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Événement</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        @php($studentUser = $activity->user)
                        <tr>
                            <td>{{ $activity->occurred_at?->format('d/m/Y H:i:s') ?? $activity->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $studentUser->full_name ?? $studentUser->name ?? $studentUser->username ?? 'Élève supprimé' }}</strong>
                                <div class="teacher-muted">{{ $studentUser->email ?? '-' }}</div>
                            </td>
                            <td>{{ $studentUser->studentProfile->schoolClass->name ?? '-' }}</td>
                            <td><span class="teacher-badge teacher-badge--{{ $activity->event === 'logout' ? 'draft' : 'published' }}">{{ $activity->event === 'logout' ? 'Déconnexion' : 'Connexion' }}</span></td>
                            <td>{{ $activity->ip_address ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
