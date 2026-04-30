@extends('layouts.admin')

@section('title', 'Import TD rapide')
@section('page_title', 'Import TD rapide')
@section('page_subtitle', 'Collez un bloc généré, vérifiez les informations détectées, puis importez le TD dans la plateforme.')

@section('content')
@php
    $parsed = session('parsed_td_import') ?? $parsed ?? null;
@endphp

<div class="admin-compact-page">
    <section class="admin-list-panel">
        <div class="admin-list-panel__head">
            <div>
                <h2>Coller un TD prêt à importer</h2>
                <p>Le bloc doit contenir au minimum : CLASSE, MATIÈRE, TITRE, CONTENU_TD. Le corrigé peut être ajouté avec CORRIGE.</p>
            </div>
            <a href="{{ route('admin.td.index') }}" class="btn btn--ghost">Retour aux TD</a>
        </div>

        @if(session('success'))
            <div class="alert" style="background:#ecfdf3;border:1px solid #bbf7d0;color:#166534;margin-bottom:18px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;margin-bottom:18px;">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;margin-bottom:18px;">{{ session('info') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.td.import.analyze') }}" class="admin-form">
            @csrf
            <div class="form-group">
                <label>Bloc d’import TIMAH ACADEMY</label>
                <textarea name="import_block" rows="18" placeholder="IMPORT_TIMAH_ACADEMY

CLASSE : Première A
MATIÈRE : Mathématique
TITRE : TD - Équations du second degré
STATUT : publié

CONTENU_TD :
Collez ici le contenu complet du TD...

CORRIGE :
Collez ici le corrigé complet...

FIN_IMPORT_TIMAH_ACADEMY" required>{{ old('import_block') }}</textarea>
                @error('import_block')<small style="color:#b91c1c;">{{ $message }}</small>@enderror
            </div>

            <div class="admin-actions">
                <button class="btn btn--primary">Analyser le bloc</button>
            </div>
        </form>
    </section>

    @if($parsed)
        <section class="admin-list-panel" style="margin-top:20px;">
            <div class="admin-list-panel__head">
                <div>
                    <h2>Résultat de l’analyse</h2>
                    <p>Vérifiez les éléments détectés avant de valider l’import.</p>
                </div>
            </div>

            <div class="admin-summary-strip" style="margin-bottom:18px;">
                <div class="admin-summary-card"><strong>{{ $parsed['class'] ?: '-' }}</strong><span>Classe</span></div>
                <div class="admin-summary-card"><strong>{{ $parsed['subject'] ?: '-' }}</strong><span>Matière</span></div>
                <div class="admin-summary-card"><strong>{{ $parsed['status'] === 'published' ? 'Publié' : 'Brouillon' }}</strong><span>Statut</span></div>
                <div class="admin-summary-card"><strong>{{ $parsed['correction'] ? 'Oui' : 'Non' }}</strong><span>Corrigé</span></div>
            </div>

            <div class="admin-clean-list">
                <article class="admin-td-card">
                    <div class="admin-td-card__main">
                        <div class="admin-clean-title">
                            <strong>{{ $parsed['title'] ?: 'Titre non détecté' }}</strong>
                            <span>{{ $parsed['chapter'] ?: 'Aucun chapitre détecté' }}</span>
                        </div>

                        <details class="admin-collapse-box" open>
                            <summary>Aperçu du TD</summary>
                            <div class="admin-collapse-box__body" style="white-space:pre-wrap;line-height:1.75;max-height:320px;overflow:auto;">{{ $parsed['content'] ?: 'Contenu non détecté.' }}</div>
                        </details>

                        <details class="admin-collapse-box" style="margin-top:12px;">
                            <summary>Aperçu du corrigé</summary>
                            <div class="admin-collapse-box__body" style="white-space:pre-wrap;line-height:1.75;max-height:320px;overflow:auto;">{{ $parsed['correction'] ?: 'Corrigé non détecté.' }}</div>
                        </details>

                        <form method="POST" action="{{ route('admin.td.import.store') }}" class="admin-form" style="margin-top:16px;">
                            @csrf
                            <input type="hidden" name="import_block" value="{{ old('import_block') }}">

                            <div class="admin-form-grid">
                                <div class="form-group">
                                    <label>Statut final</label>
                                    <select name="force_status">
                                        <option value="draft" @selected(($parsed['status'] ?? 'draft') !== 'published')>Brouillon</option>
                                        <option value="published" @selected(($parsed['status'] ?? '') === 'published')>Publier immédiatement</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Accès</label>
                                    <select name="access_level">
                                        <option value="free">Gratuit</option>
                                        <option value="premium">Premium</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Difficulté</label>
                                    <select name="difficulty">
                                        <option value="easy">Facile</option>
                                        <option value="medium" selected>Moyen</option>
                                        <option value="hard">Difficile</option>
                                        <option value="exam">Type examen</option>
                                    </select>
                                </div>
                            </div>

                            <div class="admin-actions">
                                <button class="btn btn--primary" onclick="return confirm('Importer ce TD dans la base ?');">Importer dans TIMAH ACADEMY</button>
                            </div>
                        </form>
                    </div>
                </article>
            </div>
        </section>
    @endif
</div>
@endsection
