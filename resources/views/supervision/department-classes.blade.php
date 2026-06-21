@extends('layouts.teacher')

@section('title', 'Classes du département')
@section('page_title', 'Classes du département')
@section('page_subtitle', 'Créer et modifier les classes depuis l’espace responsable, sans passer par l’interface admin.')

@section('content')
<style>
    .dm-wrap{display:grid;gap:18px}.dm-hero{background:#0f2a69;color:#fff;border-radius:24px;padding:20px}.dm-hero h2{margin:6px 0;font-size:2.2rem}.dm-hero p{color:#dbeafe}.dm-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}.dm-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 13px;border:0;border-radius:12px;background:#0f2a69;color:#fff;text-decoration:none;font-weight:900;cursor:pointer}.dm-btn--white{background:#fff;color:#0f172a}.dm-btn--green{background:#16a34a}.dm-grid{display:grid;grid-template-columns:360px 1fr;gap:18px}.dm-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.dm-card h3{margin-top:0}.dm-form{display:grid;gap:10px}.dm-form label{display:grid;gap:5px;font-weight:800;color:#334155}.dm-form input,.dm-form select,.dm-form textarea{border:1px solid #cbd5e1;border-radius:12px;padding:10px}.dm-row{border:1px solid #e5e7eb;border-radius:14px;padding:12px;margin-bottom:10px;background:#f8fafc}.dm-row strong{display:block;color:#0f172a}.dm-row small{color:#64748b}.dm-inline{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}.dm-badge{display:inline-flex;padding:4px 8px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:900;font-size:12px;margin-top:5px}@media(max-width:980px){.dm-grid{grid-template-columns:1fr}.dm-inline{grid-template-columns:1fr}.dm-btn{width:100%}}
</style>

<div class="dm-wrap">
    <section class="dm-hero">
        <span>Département / filière</span>
        <h2>{{ $department->name ?? 'Département' }}</h2>
        <p>Les classes créées ou modifiées ici sont enregistrées dans la même table que l’admin. Les changements seront donc visibles partout dans TIMAH ACADEMY.</p>
        <div class="dm-actions">
            <a class="dm-btn dm-btn--white" href="{{ route('responsible.department.dashboard') }}">← Retour TB département</a>
            <a class="dm-btn dm-btn--green" href="{{ route('department.subjects.index') }}">Gérer les matières</a>
        </div>
    </section>

    <section class="dm-grid">
        <div class="dm-card">
            <h3>Créer une classe</h3>
            <form class="dm-form" method="POST" action="{{ route('department.classes.store') }}">
                @csrf
                <label>Nom de la classe<input name="name" required placeholder="Exemple : Première F3"></label>
                <label>Niveau<select name="level">@foreach($levels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                <label>Ordre<input type="number" name="order" value="0"></label>
                <label>Description<textarea name="description"></textarea></label>
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="dm-btn dm-btn--green" type="submit">Créer et lier au département</button>
            </form>
        </div>

        <div class="dm-card">
            <h3>Classes disponibles</h3>
            @forelse($classes as $class)
                <div class="dm-row">
                    <strong>{{ $class->name }}</strong>
                    <small>{{ $class->level ?? 'Niveau non défini' }}</small>
                    @if((int) $linkedClassId === (int) $class->id)<span class="dm-badge">Liée au département</span>@endif
                    <form method="POST" action="{{ route('department.classes.update', $class->id) }}" class="dm-inline">
                        @csrf
                        <input name="name" value="{{ $class->name }}" required>
                        <select name="level">@foreach($levels as $value => $label)<option value="{{ $value }}" @selected(($class->level ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                        <input type="number" name="order" value="{{ $class->order ?? 0 }}">
                        <input name="description" value="{{ $class->description ?? '' }}" placeholder="Description">
                        <label><input type="checkbox" name="is_active" value="1" @checked((bool)($class->is_active ?? true))> Active</label>
                        <button class="dm-btn" type="submit">Modifier / lier</button>
                    </form>
                </div>
            @empty
                <p>Aucune classe disponible.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
