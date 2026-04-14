@extends('layouts.admin')

@section('title', 'Affectations enseignants')
@section('page_title', 'Affectation des enseignants')
@section('page_subtitle', 'Le contrôle des affectations détermine ce que chaque enseignant peut réellement gérer dans la plateforme.')

@section('content')
<div class="admin-grid-2 admin-grid-2--wide-left">
    <section class="admin-section">
        <div class="admin-section__head">
            <h2>Nouvelle affectation</h2>
        </div>
        <form method="POST" action="{{ route('admin.assignments.store') }}" class="admin-form">
            @csrf
            <div class="admin-form-grid">
                <div class="form-group">
                    <label>Enseignant</label>
                    <select name="teacher_id" required>
                        <option value="">Choisir...</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name ?? $teacher->name ?? $teacher->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Classe</label>
                    <select name="school_class_id" required>
                        <option value="">Choisir...</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Matière</label>
                    <select name="subject_id" required>
                        <option value="">Choisir...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group admin-form-grid__full">
                    <label>Notes</label>
                    <textarea name="notes" rows="4" placeholder="Ex: Responsable des cours et du suivi de devoirs."></textarea>
                </div>
            </div>
            <div class="admin-actions">
                <button type="submit" class="btn btn--primary">Enregistrer l'affectation</button>
            </div>
        </form>
    </section>

    <section class="admin-section">
        <div class="admin-section__head"><h2>Rappel métier</h2></div>
        <ul class="admin-list">
            <li>L’admin affecte les enseignants aux classes et matières.</li>
            <li>L’enseignant ne voit ensuite que ses classes affectées.</li>
            <li>Les messages élèves vont seulement aux enseignants concernés.</li>
            <li>Une affectation inactive retire l’accès enseignant au module concerné.</li>
        </ul>
    </section>
</div>

<section class="admin-section" style="margin-top:22px;">
    <div class="admin-section__head"><h2>Affectations existantes</h2></div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Enseignant</th>
                    <th>Classe</th>
                    <th>Matière</th>
                    <th>Notes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->teacher_full_name ?? $assignment->teacher_name ?? $assignment->teacher_username ?? '-' }}</td>
                        <td>{{ $assignment->class_name ?? '-' }}</td>
                        <td>{{ $assignment->subject_name ?? '-' }}</td>
                        <td>{{ $assignment->notes ?: '—' }}</td>
                        <td><span class="admin-badge">{{ $assignment->is_active ? 'active' : 'inactive' }}</span></td>
                        <td>
                            <div class="admin-actions">
                                <form method="POST" action="{{ route('admin.assignments.toggle', $assignment->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn--ghost">{{ $assignment->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.assignments.delete', $assignment->id) }}" onsubmit="return confirm('Supprimer cette affectation ?');">
                                    @csrf
                                    <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty">Aucune affectation trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
