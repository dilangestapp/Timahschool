@extends('layouts.admin')

@section('title', 'Plans')
@section('page_title', 'Gestion des plans d\'abonnement')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head">
            <h2>Ajouter un plan</h2>
        </div>

        @if($tableMissing)
            <div class="admin-empty-box">La table <strong>subscription_plans</strong> est introuvable dans la base.</div>
        @else
            <form method="POST" action="{{ route('admin.plans.store') }}" class="admin-form">
                @csrf
                <div class="admin-form-grid">
                    <div class="form-group">
                        <label>Nom du plan</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: Essentiel mensuel" required>
                    </div>
                    <div class="form-group">
                        <label>Prix</label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', 0) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Devise</label>
                        <input type="text" name="currency" value="{{ old('currency', 'XAF') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Unité de durée</label>
                        <select name="duration_unit" required>
                            <option value="month" @selected(old('duration_unit') === 'month')>Mois</option>
                            <option value="week" @selected(old('duration_unit') === 'week')>Semaine</option>
                            <option value="day" @selected(old('duration_unit') === 'day')>Jour</option>
                            <option value="year" @selected(old('duration_unit') === 'year')>Année</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valeur de durée</label>
                        <input type="number" min="1" name="duration_value" value="{{ old('duration_value', 1) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jours d'essai</label>
                        <input type="number" min="0" name="trial_days" value="{{ old('trial_days', 0) }}">
                    </div>
                    <div class="form-group">
                        <label>Ordre d'affichage</label>
                        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}">
                    </div>
                    <div class="form-group form-group--check">
                        <label>
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            Plan actif
                        </label>
                    </div>
                    <div class="form-group form-group--check">
                        <label>
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))>
                            Mettre en avant
                        </label>
                    </div>
                    <div class="form-group admin-form-grid__full">
                        <label>Description</label>
                        <textarea name="description" rows="4" placeholder="Description du plan...">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="admin-actions">
                    <button type="submit" class="btn btn--primary">Ajouter le plan</button>
                </div>
            </form>
        @endif
    </section>

    <section class="admin-section">
        <div class="admin-section__head">
            <h2>Résumé</h2>
        </div>
        <div class="admin-info-list">
            <div class="admin-info-item">
                <strong>{{ $plans->count() }}</strong>
                <span>plans trouvés</span>
            </div>
            <div class="admin-info-item">
                <strong>{{ $plans->where('is_active', 1)->count() }}</strong>
                <span>plans actifs</span>
            </div>
            <div class="admin-info-item">
                <strong>{{ $plans->where('is_featured', 1)->count() }}</strong>
                <span>plans mis en avant</span>
            </div>
        </div>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head">
        <h2>Modifier les plans existants</h2>
    </div>

    @if(!$tableMissing)
        <div class="admin-card-grid">
            @forelse($plans as $plan)
                <div class="admin-manage-card">
                    <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="admin-form">
                        @csrf
                        <div class="admin-form-grid">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="name" value="{{ $plan->name ?? '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Prix</label>
                                <input type="number" step="0.01" min="0" name="price" value="{{ $plan->price ?? 0 }}" required>
                            </div>
                            <div class="form-group">
                                <label>Devise</label>
                                <input type="text" name="currency" value="{{ $plan->currency ?? 'XAF' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Unité</label>
                                <select name="duration_unit" required>
                                    <option value="day" @selected(($plan->duration_unit ?? '') === 'day')>Jour</option>
                                    <option value="week" @selected(($plan->duration_unit ?? '') === 'week')>Semaine</option>
                                    <option value="month" @selected(($plan->duration_unit ?? '') === 'month')>Mois</option>
                                    <option value="year" @selected(($plan->duration_unit ?? '') === 'year')>Année</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Valeur</label>
                                <input type="number" min="1" name="duration_value" value="{{ $plan->duration_value ?? 1 }}" required>
                            </div>
                            <div class="form-group">
                                <label>Jours essai</label>
                                <input type="number" min="0" name="trial_days" value="{{ $plan->trial_days ?? 0 }}">
                            </div>
                            <div class="form-group">
                                <label>Ordre</label>
                                <input type="number" min="0" name="sort_order" value="{{ $plan->sort_order ?? 0 }}">
                            </div>
                            <div class="form-group form-group--check">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" @checked(($plan->is_active ?? 0) == 1)>
                                    Actif
                                </label>
                            </div>
                            <div class="form-group form-group--check">
                                <label>
                                    <input type="checkbox" name="is_featured" value="1" @checked(($plan->is_featured ?? 0) == 1)>
                                    Mis en avant
                                </label>
                            </div>
                            <div class="form-group admin-form-grid__full">
                                <label>Description</label>
                                <textarea name="description" rows="4">{{ $plan->description ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="admin-actions admin-actions--spread">
                            <span class="admin-badge">ID #{{ $plan->id }}</span>
                            <div class="admin-actions">
                                <button type="submit" class="btn btn--primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.plans.delete', $plan->id) }}" onsubmit="return confirm('Supprimer ce plan ?');">
                        @csrf
                        <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                    </form>
                </div>
            @empty
                <div class="admin-empty-box">Aucun plan trouvé.</div>
            @endforelse
        </div>
    @endif
</section>
@endsection
