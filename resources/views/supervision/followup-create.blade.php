@extends('layouts.teacher')

@section('title', 'Créer une alerte pédagogique')
@section('page_title', 'Créer une alerte pédagogique')
@section('page_subtitle', 'Créer une relance ou une alerte de suivi pédagogique interne, sans confusion avec les notes scolaires.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('pedagogical_supervision_notes');
    $responsibilities = collect();
    $users = collect();

    if ($schemaReady) {
        $responsibilities = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();

        if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
            $users = \Illuminate\Support\Facades\DB::table('users')
                ->select('id', 'full_name', 'name', 'username', 'phone')
                ->orderByRaw('COALESCE(full_name, name, username) asc')
                ->limit(200)
                ->get();
        }
    }
@endphp

<style>
    .follow-wrap{display:grid;gap:18px}.follow-hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);border-radius:24px;color:#fff;padding:22px;box-shadow:0 18px 45px rgba(15,23,42,.22)}.follow-hero h2{margin:6px 0;font-size:clamp(2rem,5vw,3rem)}.follow-hero p{color:#dbeafe;max-width:820px}.follow-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}.follow-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:14px;border:0;background:#fff;color:#0f172a;text-decoration:none;font-weight:900;cursor:pointer}.follow-btn--green{background:#16a34a;color:#fff}.follow-card{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:18px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.follow-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}.follow-form label{display:grid;gap:6px;color:#334155;font-weight:900}.follow-form input,.follow-form select,.follow-form textarea{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:12px;padding:11px;background:#fff;color:#0f172a}.follow-form textarea{min-height:130px}.follow-wide{grid-column:1/-1}.follow-note{border:1px solid #bfdbfe;background:#eff6ff;border-radius:16px;padding:14px;color:#0f172a}.follow-note strong{display:block;margin-bottom:4px}.follow-empty{padding:18px;border-radius:16px;background:#f8fafc;color:#64748b;text-align:center}@media(max-width:760px){.follow-form{grid-template-columns:1fr}.follow-btn,.follow-actions{width:100%}}
</style>

<div class="follow-wrap">
    @if(!$schemaReady)
        <section class="follow-hero"><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore installées.</p></section>
    @elseif($responsibilities->isEmpty())
        <section class="follow-hero"><h2>Accès réservé</h2><p>Aucune responsabilité pédagogique active n’est attribuée à ce compte.</p><div class="follow-actions"><a class="follow-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="follow-hero">
            <h2>Créer une alerte pédagogique</h2>
            <p>Une alerte pédagogique sert à signaler un retard, une question sans réponse, un TD manquant, un contenu à améliorer ou une relance à suivre. Ce n’est pas une note scolaire d’élève.</p>
            <div class="follow-actions">
                <a class="follow-btn" href="{{ route('responsibilities.followups.index') }}">← Suivi pédagogique</a>
                <a class="follow-btn" href="{{ route('supervision.tb') }}">Retour TB</a>
            </div>
        </section>

        <div class="follow-note">
            <strong>Exemples d’alertes utiles</strong>
            TD non publié, cours resté en brouillon, question élève sans réponse, corrigé incomplet, enseignant à relancer, département en retard.
        </div>

        <section class="follow-card">
            <form method="POST" action="{{ route('supervision.notes.store') }}" class="follow-form">
                @csrf
                <label>Responsabilité concernée
                    <select name="responsibility_id" required>
                        @foreach($responsibilities as $responsibility)
                            <option value="{{ $responsibility->id }}">{{ $responsibility->role_title }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Niveau de priorité
                    <select name="severity">
                        <option value="info">Info</option>
                        <option value="warning">Attention</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </label>
                <label class="follow-wide">Personne ciblée, si nécessaire
                    <select name="target_user_id">
                        <option value="">Aucune personne précise</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name ?: ($user->name ?: $user->username) }} {{ $user->phone ? '— '.$user->phone : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="follow-wide">Objet de l’alerte
                    <input name="title" required placeholder="Exemple : TD non publié cette semaine">
                </label>
                <label class="follow-wide">Message détaillé
                    <textarea name="message" placeholder="Explique clairement le problème, la relance ou l’action attendue."></textarea>
                </label>
                <div class="follow-wide"><button type="submit" class="follow-btn follow-btn--green">Créer l’alerte</button></div>
            </form>
        </section>
    @endif
</div>
@endsection
