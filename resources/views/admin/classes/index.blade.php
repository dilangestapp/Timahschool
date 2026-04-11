@extends('layouts.admin')

@section('title', 'Classes')
@section('page_title', 'Gestion des classes')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head">
            <h2>Ajouter une classe</h2>
        </div>

        @if($tableMissing)
            <div class="admin-empty-box">La table <strong>school_classes</strong> est introuvable dans la base.</div>
        @else
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
                        <textarea name="description" rows="4" placeholder="Petite description de la classe...">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="admin-actions">
                    <button type="submit" class="btn btn--primary">Ajouter la classe</button>
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
                <strong>{{ $classes->count() }}</strong>
                <span>classes affichées</span>
            </div>
            <div class="admin-info-item">
                <strong>{{ $classes->where('is_active', 1)->count() }}</strong>
                <span>classes actives</span>
            </div>
            <div class="admin-info-item">
                <strong>{{ $classes->where('level', 'enseignement_general')->count() }}</strong>
                <span>enseignement général</span>
            </div>
            <div class="admin-info-item">
                <strong>{{ $classes->where('level', 'enseignement_technique')->count() }}</strong>
                <span>enseignement technique</span>
            </div>
        </div>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head">
        <h2>Modifier les classes existantes</h2>
    </div>

    @if(!$tableMissing)
        <div class="admin-card-grid">
            @forelse($classes as $class)
                <div class="admin-manage-card">
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
                                <textarea name="description" rows="4">{{ $class->description ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="admin-actions admin-actions--spread">
                            <span class="admin-badge">ID #{{ $class->id }}</span>
                            <div class="admin-actions">
                                <button type="submit" class="btn btn--primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.classes.delete', $class->id) }}" onsubmit="return confirm('Supprimer cette classe ?');">
                        @csrf
                        <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                    </form>
                </div>
            @empty
                <div class="admin-empty-box">Aucune classe trouvée.</div>
            @endforelse
        </div>
    @endif
</section>
@endsection
