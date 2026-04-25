@extends('layouts.admin')

@section('title', 'Matières')
@section('page_title', 'Gestion des matières')
@section('page_subtitle', 'Créez, consultez et modifiez les matières sans afficher tous les formulaires en même temps.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>subjects</strong> est introuvable dans la base.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $subjects->count() }}</strong><span>matières trouvées</span></div>
            <div class="admin-summary-card"><strong>{{ $subjects->where('is_active', 1)->count() }}</strong><span>actives</span></div>
            <div class="admin-summary-card"><strong>{{ $subjects->where('is_active', 0)->count() }}</strong><span>inactives</span></div>
            <div class="admin-summary-card"><strong>{{ $subjects->max('order') ?? 0 }}</strong><span>ordre maximal</span></div>
        </div>

        <details class="admin-collapse-box">
            <summary>Ajouter une matière</summary>
            <div class="admin-collapse-box__body">
                <form method="POST" action="{{ route('admin.subjects.store') }}" class="admin-form">
                    @csrf
                    <div class="admin-form-grid">
                        <div class="form-group"><label>Nom</label><input type="text" name="name" value="{{ old('name') }}" required></div>
                        <div class="form-group"><label>Icône</label><input type="text" name="icon" value="{{ old('icon') }}" placeholder="Ex: book-open"></div>
                        <div class="form-group"><label>Couleur</label><input type="text" name="color" value="{{ old('color', '#2563eb') }}" placeholder="#2563eb"></div>
                        <div class="form-group"><label>Ordre</label><input type="number" name="order" value="{{ old('order', 0) }}" min="0"></div>
                        <div class="form-group form-group--check"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Matière active</label></div>
                        <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3">{{ old('description') }}</textarea></div>
                    </div>
                    <div class="admin-actions"><button type="submit" class="btn btn--primary">Ajouter la matière</button></div>
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head"><div><h2>Matières existantes</h2><p>Le formulaire de modification s’ouvre uniquement sur la matière choisie.</p></div></div>
            <div class="admin-clean-list">
                @forelse($subjects as $subject)
                    <article class="admin-clean-row">
                        <div class="admin-clean-title"><strong>{{ $subject->name ?? 'Matière sans nom' }}</strong><span>ID #{{ $subject->id }} · Ordre {{ $subject->order ?? 0 }}</span></div>
                        <div class="admin-clean-meta"><span class="admin-badge">{{ ($subject->is_active ?? 0) == 1 ? 'Active' : 'Inactive' }}</span><br><span>{{ $subject->color ?? '#2563eb' }} · {{ $subject->icon ?: 'sans icône' }}</span></div>
                        <div class="admin-row-actions">
                            <details class="admin-edit-panel">
                                <summary>Modifier</summary>
                                <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}" class="admin-form">
                                    @csrf
                                    <div class="admin-form-grid">
                                        <div class="form-group"><label>Nom</label><input type="text" name="name" value="{{ $subject->name ?? '' }}" required></div>
                                        <div class="form-group"><label>Icône</label><input type="text" name="icon" value="{{ $subject->icon ?? '' }}"></div>
                                        <div class="form-group"><label>Couleur</label><input type="text" name="color" value="{{ $subject->color ?? '#2563eb' }}"></div>
                                        <div class="form-group"><label>Ordre</label><input type="number" name="order" value="{{ $subject->order ?? 0 }}" min="0"></div>
                                        <div class="form-group form-group--check"><label><input type="checkbox" name="is_active" value="1" @checked(($subject->is_active ?? 0) == 1)> Active</label></div>
                                        <div class="form-group admin-form-grid__full"><label>Description</label><textarea name="description" rows="3">{{ $subject->description ?? '' }}</textarea></div>
                                    </div>
                                    <div class="admin-actions"><button type="submit" class="btn btn--primary">Enregistrer</button></div>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.subjects.delete', $subject->id) }}" onsubmit="return confirm('Supprimer cette matière ?');">@csrf<button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button></form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucune matière trouvée.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
