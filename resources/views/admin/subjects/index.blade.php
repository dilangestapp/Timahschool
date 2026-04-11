@extends('layouts.admin')

@section('title', 'Matières')
@section('page_title', 'Gestion des matières')
@section('page_subtitle', 'Créer et structurer les matières qui alimentent les classes, les cours et les affectations.')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head"><h2>Ajouter une matière</h2></div>

        @if($tableMissing)
            <div class="admin-empty-box">La table <strong>subjects</strong> est introuvable dans la base.</div>
        @else
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="admin-form">
                @csrf
                <div class="admin-form-grid">
                    <div class="form-group"><label>Nom</label><input type="text" name="name" value="{{ old('name') }}" required></div>
                    <div class="form-group"><label>Icône</label><input type="text" name="icon" value="{{ old('icon') }}" placeholder="Ex: book-open"></div>
                    <div class="form-group"><label>Couleur</label><input type="text" name="color" value="{{ old('color', '#2563eb') }}" placeholder="#2563eb"></div>
                    <div class="form-group"><label>Ordre</label><input type="number" name="order" value="{{ old('order', 0) }}" min="0"></div>
                    <div class="form-group form-group--check"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Matière active</label></div>
                    <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="4">{{ old('description') }}</textarea></div>
                </div>
                <div class="admin-actions"><button type="submit" class="btn btn--primary">Ajouter la matière</button></div>
            </form>
        @endif
    </section>

    <section class="admin-section">
        <div class="admin-section__head"><h2>Résumé</h2></div>
        <div class="admin-info-list">
            <div class="admin-info-item"><strong>{{ $subjects->count() }}</strong><span>matières trouvées</span></div>
            <div class="admin-info-item"><strong>{{ $subjects->where('is_active', 1)->count() }}</strong><span>actives</span></div>
            <div class="admin-info-item"><strong>{{ $subjects->where('is_active', 0)->count() }}</strong><span>inactives</span></div>
        </div>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Modifier les matières existantes</h2></div>

    @if(!$tableMissing)
        <div class="admin-card-grid">
            @forelse($subjects as $subject)
                <div class="admin-manage-card">
                    <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}" class="admin-form">
                        @csrf
                        <div class="admin-form-grid">
                            <div class="form-group"><label>Nom</label><input type="text" name="name" value="{{ $subject->name ?? '' }}" required></div>
                            <div class="form-group"><label>Icône</label><input type="text" name="icon" value="{{ $subject->icon ?? '' }}"></div>
                            <div class="form-group"><label>Couleur</label><input type="text" name="color" value="{{ $subject->color ?? '#2563eb' }}"></div>
                            <div class="form-group"><label>Ordre</label><input type="number" name="order" value="{{ $subject->order ?? 0 }}" min="0"></div>
                            <div class="form-group form-group--check"><label><input type="checkbox" name="is_active" value="1" @checked(($subject->is_active ?? 0) == 1)> Active</label></div>
                            <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="4">{{ $subject->description ?? '' }}</textarea></div>
                        </div>
                        <div class="admin-actions admin-actions--spread">
                            <span class="admin-badge">ID #{{ $subject->id }}</span>
                            <button type="submit" class="btn btn--primary">Enregistrer</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.subjects.delete', $subject->id) }}" onsubmit="return confirm('Supprimer cette matière ?');">
                        @csrf
                        <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                    </form>
                </div>
            @empty
                <div class="admin-empty-box">Aucune matière trouvée.</div>
            @endforelse
        </div>
    @endif
</section>
@endsection
