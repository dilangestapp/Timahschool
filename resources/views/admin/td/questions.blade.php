@extends('layouts.admin')

@section('title', 'Questions TD')
@section('page_title', 'Questions liées aux TD')
@section('page_subtitle', 'Supervision des échanges entre élèves et enseignants autour des TD.')

@section('content')
<section class="admin-panel">
    <div class="admin-panel__head"><h2>Filtres</h2></div>
    <div class="admin-panel__body">
        <form method="GET" class="admin-filters-grid">
            <select name="status">
                <option value="">Tous les statuts</option>
                @foreach(['open' => 'Ouvert', 'answered' => 'Répondu', 'closed' => 'Clos'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="subject_id">
                <option value="">Toutes les matières</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string)($filters['subject_id'] ?? '') === (string)$subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn--primary">Filtrer</button>
        </form>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__head"><h2>Conversations TD</h2></div>
    <div class="admin-panel__body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>TD</th>
                    <th>Élève</th>
                    <th>Enseignant</th>
                    <th>Matière</th>
                    <th>Statut</th>
                    <th>Dernier message</th>
                </tr>
            </thead>
            <tbody>
                @forelse($threads as $thread)
                    <tr>
                        <td><a href="{{ route('admin.td.questions.show', $thread) }}">{{ $thread->tdSet->title ?? '-' }}</a></td>
                        <td>{{ $thread->student->full_name ?? $thread->student->name ?? '-' }}</td>
                        <td>{{ $thread->teacher->full_name ?? $thread->teacher->name ?? '-' }}</td>
                        <td>{{ $thread->subject->name ?? '-' }}</td>
                        <td><span class="admin-badge admin-badge--{{ $thread->status }}">{{ $thread->status }}</span></td>
                        <td>{{ optional($thread->last_message_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucune conversation TD pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-panel__body">{{ $threads->links() }}</div>
</section>
@endsection
