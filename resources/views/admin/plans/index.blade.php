@extends('layouts.admin')

@section('title', 'Plans')
@section('page_title', 'Gestion des plans d\'abonnement')
@section('page_subtitle', 'Créez, consultez et modifiez les formules sans afficher tous les grands formulaires en même temps.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>subscription_plans</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $plans->count() }}</strong><span>plans trouvés</span></div>
            <div class="admin-summary-card"><strong>{{ $plans->where('is_active', 1)->count() }}</strong><span>plans actifs</span></div>
            <div class="admin-summary-card"><strong>{{ $plans->where('is_featured', 1)->count() }}</strong><span>mis en avant</span></div>
            <div class="admin-summary-card"><strong>{{ number_format((float) $plans->max('price'), 0, ',', ' ') }}</strong><span>prix le plus élevé</span></div>
        </div>

        <details class="admin-collapse-box">
            <summary>Ajouter un plan</summary>
            <div class="admin-collapse-box__body">
                <form method="POST" action="{{ route('admin.plans.store') }}" class="admin-form">
                    @csrf
                    <div class="admin-form-grid">
                        <div class="form-group"><label>Nom du plan</label><input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: Essentiel mensuel" required></div>
                        <div class="form-group"><label>Prix</label><input type="number" step="0.01" min="0" name="price" value="{{ old('price', 0) }}" required></div>
                        <div class="form-group"><label>Devise</label><input type="text" name="currency" value="{{ old('currency', 'XAF') }}" required></div>
                        <div class="form-group"><label>Unité de durée</label><select name="duration_unit" required><option value="month" @selected(old('duration_unit') === 'month')>Mois</option><option value="week" @selected(old('duration_unit') === 'week')>Semaine</option><option value="day" @selected(old('duration_unit') === 'day')>Jour</option><option value="year" @selected(old('duration_unit') === 'year')>Année</option></select></div>
                        <div class="form-group"><label>Valeur de durée</label><input type="number" min="1" name="duration_value" value="{{ old('duration_value', 1) }}" required></div>
                        <div class="form-group"><label>Jours d'essai</label><input type="number" min="0" name="trial_days" value="{{ old('trial_days', 0) }}"></div>
                        <div class="form-group"><label>Ordre d'affichage</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}"></div>
                        <div class="form-group form-group--check"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Plan actif</label></div>
                        <div class="form-group form-group--check"><label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))> Mettre en avant</label></div>
                        <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3" placeholder="Description du plan...">{{ old('description') }}</textarea></div>
                    </div>
                    <div class="admin-actions"><button type="submit" class="btn btn--primary">Ajouter le plan</button></div>
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head"><div><h2>Plans existants</h2><p>Les détails de modification restent fermés jusqu’au clic sur Modifier.</p></div></div>
            <div class="admin-clean-list">
                @forelse($plans as $plan)
                    <article class="admin-clean-row">
                        <div class="admin-clean-title"><strong>{{ $plan->name ?? 'Plan sans nom' }}</strong><span>ID #{{ $plan->id }} · Ordre {{ $plan->sort_order ?? 0 }}</span></div>
                        <div class="admin-clean-meta"><strong>{{ number_format((float) ($plan->price ?? 0), 0, ',', ' ') }} {{ $plan->currency ?? 'XAF' }}</strong><br><span>{{ $plan->duration_value ?? 1 }} {{ $plan->duration_unit ?? 'mois' }} · {{ ($plan->is_active ?? 0) ? 'actif' : 'inactif' }} {{ ($plan->is_featured ?? 0) ? '· mis en avant' : '' }}</span></div>
                        <div class="admin-row-actions">
                            <details class="admin-edit-panel">
                                <summary>Modifier</summary>
                                <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="admin-form">
                                    @csrf
                                    <div class="admin-form-grid">
                                        <div class="form-group"><label>Nom</label><input type="text" name="name" value="{{ $plan->name ?? '' }}" required></div>
                                        <div class="form-group"><label>Prix</label><input type="number" step="0.01" min="0" name="price" value="{{ $plan->price ?? 0 }}" required></div>
                                        <div class="form-group"><label>Devise</label><input type="text" name="currency" value="{{ $plan->currency ?? 'XAF' }}" required></div>
                                        <div class="form-group"><label>Unité</label><select name="duration_unit" required><option value="day" @selected(($plan->duration_unit ?? '') === 'day')>Jour</option><option value="week" @selected(($plan->duration_unit ?? '') === 'week')>Semaine</option><option value="month" @selected(($plan->duration_unit ?? '') === 'month')>Mois</option><option value="year" @selected(($plan->duration_unit ?? '') === 'year')>Année</option></select></div>
                                        <div class="form-group"><label>Valeur</label><input type="number" min="1" name="duration_value" value="{{ $plan->duration_value ?? 1 }}" required></div>
                                        <div class="form-group"><label>Jours essai</label><input type="number" min="0" name="trial_days" value="{{ $plan->trial_days ?? 0 }}"></div>
                                        <div class="form-group"><label>Ordre</label><input type="number" min="0" name="sort_order" value="{{ $plan->sort_order ?? 0 }}"></div>
                                        <div class="form-group form-group--check"><label><input type="checkbox" name="is_active" value="1" @checked(($plan->is_active ?? 0) == 1)> Actif</label></div>
                                        <div class="form-group form-group--check"><label><input type="checkbox" name="is_featured" value="1" @checked(($plan->is_featured ?? 0) == 1)> Mis en avant</label></div>
                                        <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3">{{ $plan->description ?? '' }}</textarea></div>
                                    </div>
                                    <div class="admin-actions"><button type="submit" class="btn btn--primary">Enregistrer</button></div>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.plans.delete', $plan->id) }}" onsubmit="return confirm('Supprimer ce plan ?');">@csrf<button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button></form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucun plan trouvé.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
