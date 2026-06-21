@extends('layouts.teacher')

@section('title', 'Programmation hebdomadaire')
@section('page_title', 'Programmation de la semaine')
@section('page_subtitle', 'Planifiez vos cours, TD, révisions, corrections et évaluations pour chaque classe.')

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head">
        <div>
            <h2>Semaine du {{ $weekStart->format('d/m/Y') }}</h2>
            <p class="teacher-muted">Chaque enseignant prépare ici son programme pédagogique de la semaine.</p>
        </div>
        <div class="teacher-actions-inline">
            <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.weekly-program.index', ['week_start' => $previousWeek->toDateString()]) }}">Semaine précédente</a>
            <a class="teacher-btn teacher-btn--ghost" href="{{ route('teacher.weekly-program.index') }}">Cette semaine</a>
            <a class="teacher-btn teacher-btn--primary" href="{{ route('teacher.weekly-program.index', ['week_start' => $nextWeek->toDateString()]) }}">Semaine suivante</a>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="teacher-empty-state"><strong>Aucune affectation active.</strong><p>L’enseignant doit être affecté à une classe et une matière avant de programmer sa semaine.</p></div>
    @else
        <form method="POST" action="{{ route('teacher.weekly-program.store') }}" class="teacher-form-card" style="margin-bottom:18px;">
            @csrf
            <div class="teacher-form-grid teacher-form-grid--two">
                <div class="teacher-form-group teacher-form-group--full">
                    <label>Classe / matière</label>
                    <select name="teacher_assignment_id" class="teacher-select" required>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}">{{ $assignment->schoolClass->name ?? 'Classe' }} — {{ $assignment->subject->name ?? 'Matière' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="teacher-form-group"><label>Date</label><input class="teacher-input" type="date" name="program_date" value="{{ now()->toDateString() }}" required></div>
                <div class="teacher-form-group"><label>Type</label><select name="activity_type" class="teacher-select" required>@foreach($types as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                <div class="teacher-form-group"><label>Début</label><input class="teacher-input" type="time" name="start_time" value="18:00"></div>
                <div class="teacher-form-group"><label>Fin</label><input class="teacher-input" type="time" name="end_time" value="19:00"></div>
                <div class="teacher-form-group teacher-form-group--full"><label>Titre</label><input class="teacher-input" type="text" name="title" placeholder="Ex : Équations du premier degré" required></div>
                <div class="teacher-form-group teacher-form-group--full"><label>Description</label><textarea class="teacher-textarea" name="description" rows="3"></textarea></div>
                <div class="teacher-form-group"><label>Statut</label><select name="status" class="teacher-select" required>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected($key === 'published')>{{ $label }}</option>@endforeach</select></div>
            </div>
            <div class="teacher-form-actions"><button type="submit" class="teacher-btn teacher-btn--primary">Programmer cette activité</button></div>
        </form>
    @endif

    <div class="teacher-grid teacher-grid--two">
        @foreach($weekDays as $day)
            @php $items = $programs->filter(fn($item) => optional($item->program_date)->toDateString() === $day['date']->toDateString()); @endphp
            <article class="teacher-card">
                <strong>{{ $day['label'] }}</strong>
                <p class="teacher-muted">{{ $day['date']->format('d/m/Y') }}</p>
                <div style="display:grid;gap:10px;margin-top:10px;">
                    @forelse($items as $program)
                        <div style="border:1px solid rgba(148,163,184,.25);border-radius:16px;padding:12px;background:rgba(255,255,255,.74);">
                            <strong>{{ $program->title }}</strong>
                            <p class="teacher-muted" style="margin:.25rem 0;">{{ $types[$program->activity_type] ?? $program->activity_type }} · {{ $statuses[$program->status] ?? $program->status }}</p>
                            <p class="teacher-muted" style="margin:.25rem 0;">{{ $program->start_time ?: '--:--' }} @if($program->end_time) - {{ $program->end_time }} @endif</p>
                            <p class="teacher-muted" style="margin:.25rem 0;">{{ $program->schoolClass->name ?? '-' }} · {{ $program->subject->name ?? '-' }}</p>
                            @if($program->description)<p class="teacher-muted">{{ $program->description }}</p>@endif
                            <div class="teacher-actions-inline">
                                <form method="POST" action="{{ route('teacher.weekly-program.status', $program) }}">@csrf<input type="hidden" name="status" value="published"><button class="teacher-btn teacher-btn--ghost" type="submit">Publier</button></form>
                                <form method="POST" action="{{ route('teacher.weekly-program.status', $program) }}">@csrf<input type="hidden" name="status" value="done"><button class="teacher-btn teacher-btn--primary" type="submit">Terminer</button></form>
                            </div>
                        </div>
                    @empty
                        <div class="teacher-empty-row">Aucune activité programmée.</div>
                    @endforelse
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
