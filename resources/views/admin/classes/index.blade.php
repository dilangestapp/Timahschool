@extends('layouts.admin')

@section('title', 'Classes')
@section('page_title', 'Gestion des classes')
@section('page_subtitle', 'Ajoutez, consultez et modifiez les classes sans afficher tous les grands formulaires en même temps.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>school_classes</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card">
                <strong>{{ $classes->count() }}</strong>
                <span>classes affichées</span>
            </div>
            <div class="admin-summary-card">
                <strong>{{ $classes->where('is_active', 1)->count() }}</strong>
                <span>classes actives</span>
            </div>
            <div class="admin-summary-card">
                <strong>{{ $classes->where('level', 'enseignement_general')->count() }}</strong>
                <span>enseignement général</span>
            </div>
            <div class="admin-summary-card">
                <strong>{{ $classes->where('level', 'enseignement_technique')->count() }}</strong>
                <span>enseignement technique</span>
            </div>
        </div>

        <details class="admin-collapse-box">
            <summary>Ajouter une nouvelle classe</summary>
            <div class="admin-collapse-box__body">
                <form method="POST" action="{{ route('admin.classes.store') }}" class="admin-form">
                    @csrf
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label>Nom de la classe</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: 6e, 2nde C, 1ère F3" required>
                        </div>
                        <div class="form-group">
                            <label>Niveau</label>
                            <select name="level" required>
                                <option value="">Choisir...</option>
                                @foreach($levels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('level') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ordre</label>
                            <input type="number" name="order" value="{{ old('order', 0) }}" min="0">
                        </div>
                        <div class="form-group form-group--check">
                            <label>
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                                Classe active
                            </label>
                        </div>
                        <div class="form-group admin-form-grid__full">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="Petite description de la classe...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <button type="submit" class="btn btn--primary">Ajouter la classe</button>
                    </div>
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head">
                <div>
                    <h2>Classes existantes</h2>
                    <p>Les formulaires restent fermés par défaut pour rendre la page plus lisible.</p>
                </div>
            </div>

            <div class="admin-clean-list">
                @forelse($classes as $class)
                    <article class="admin-clean-row">
                        <div class="admin-clean-title">
                            <strong>{{ $class->name ?? 'Classe sans nom' }}</strong>
                            <span>ID #{{ $class->id }} · Ordre {{ $class->order ?? 0 }}</span>
                        </div>

                        <div class="admin-clean-meta">
                            {{ $levels[$class->level] ?? $class->level ?? 'Niveau non défini' }}<br>
                            <span class="admin-badge">{{ ($class->is_active ?? 0) == 1 ? 'Active' : 'Inactive' }}</span>
                        </div>

                        <div class="admin-row-actions">
                            <details class="admin-edit-panel">
                                <summary>Modifier</summary>
                                <form method="POST" action="{{ route('admin.classes.update', $class->id) }}" class="admin-form">
                                    @csrf
                                    <div class="admin-form-grid">
                                        <div class="form-group">
                                            <label>Nom</label>
                                            <input type="text" name="name" value="{{ $class->name ?? '' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Niveau</label>
                                            <select name="level" required>
                                                @foreach($levels as $value => $label)
                                                    <option value="{{ $value }}" @selected(($class->level ?? '') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Ordre</label>
                                            <input type="number" name="order" value="{{ $class->order ?? 0 }}" min="0">
                                        </div>
                                        <div class="form-group form-group--check">
                                            <label>
                                                <input type="checkbox" name="is_active" value="1" @checked(($class->is_active ?? 0) == 1)>
                                                Active
                                            </label>
                                        </div>
                                        <div class="form-group admin-form-grid__full">
                                            <label>Description</label>
                                            <textarea name="description" rows="3">{{ $class->description ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="admin-actions">
                                        <button type="submit" class="btn btn--primary">Enregistrer</button>
                                    </div>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('admin.classes.delete', $class->id) }}" onsubmit="return confirm('Supprimer cette classe ?');">
                                @csrf
                                <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucune classe trouvée.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
