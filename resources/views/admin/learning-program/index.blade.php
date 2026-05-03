@extends('layouts.admin')

@section('title', 'Programme de répétition')
@section('page_title', 'Programme de répétition numérique')
@section('page_subtitle', 'Planifiez les cours, TD, quiz et évaluations sans présenter TIMAH ACADEMY comme une école administrative.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>learning_program_schedules</strong> est introuvable. Lancez les migrations Railway pour activer ce module.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $items->count() }}</strong><span>activités</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('activity_type', 'course')->count() }}</strong><span>cours</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('activity_type', 'td')->count() }}</strong><span>TD</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('activity_type', 'evaluation')->count() }}</strong><span>évaluations</span></div>
        </div>

        <details class="admin-collapse-box" open>
            <summary>Programmer une activité</summary>
            <div class="admin-collapse-box__body">
                <form method="POST" action="{{ route('admin.learning-program.store') }}" class="admin-form">
                    @csrf
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label>Titre</label>
                            <input type="text" name="title" required placeholder="Ex: Mathématiques — Fractions">
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="activity_type" required>
                                <option value="course">Cours programmé</option>
                                <option value="td">TD / Exercices</option>
                                <option value="quiz">Quiz</option>
                                <option value="evaluation">Évaluation bimensuelle</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Classe / niveau</label>
                            <select name="school_class_id">
                                <option value="">Tous les niveaux</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Matière</label>
                            <select name="subject_id">
                                <option value="">Toutes les matières</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Semaine</label>
                            <input type="number" name="week_number" value="1" min="1" max="52" required>
                        </div>
                        <div class="form-group">
                            <label>Jour</label>
                            <select name="weekday" required>
                                <option value="1">Lundi</option>
                                <option value="2">Mardi</option>
                                <option value="3">Mercredi</option>
                                <option value="4">Jeudi</option>
                                <option value="5">Vendredi</option>
                                <option value="6">Samedi</option>
                                <option value="7">Dimanche</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Heure de déblocage</label>
                            <input type="time" name="unlock_time" value="18:00">
                        </div>
                        <div class="form-group">
                            <label>Date/heure exacte de déblocage</label>
                            <input type="datetime-local" name="unlocks_at">
                        </div>
                        <div class="form-group">
                            <label>Date/heure de clôture</label>
                            <input type="datetime-local" name="closes_at">
                        </div>
                        <div class="form-group">
                            <label>Durée en minutes</label>
                            <input type="number" name="duration_minutes" min="1" placeholder="Ex: 60">
                        </div>
                        <div class="form-group">
                            <label>Statut</label>
                            <select name="status" required>
                                <option value="scheduled">Programmé</option>
                                <option value="published">Publié</option>
                                <option value="draft">Brouillon</option>
                                <option value="archived">Archivé</option>
                            </select>
                        </div>
                        <div class="form-group form-group--check">
                            <label><input type="checkbox" name="requires_subscription" value="1" checked> Nécessite un abonnement/essai actif</label>
                        </div>
                        <div class="form-group admin-form-grid__full">
                            <label>Description</label>
                            <textarea name="description" rows="4" placeholder="Objectif pédagogique, consignes, rappel ou message visible..."></textarea>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <button type="submit" class="btn btn--primary">Programmer l’activité</button>
                    </div>
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head">
                <div>
                    <h2>Planning pédagogique</h2>
                    <p>Cours en semaine, TD le week-end, quiz de consolidation et évaluation toutes les deux semaines.</p>
                </div>
            </div>

            <div class="admin-clean-list">
                @forelse($items as $schedule)
                    @php
                        $days = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
                        $statusClass = match($schedule->status) {
                            'published' => 'admin-badge--success',
                            'scheduled' => 'admin-badge--trial',
                            'archived' => 'admin-badge--warning',
                            default => '',
                        };
                    @endphp
                    <article class="admin-clean-row">
                        <div class="admin-clean-title">
                            <strong>{{ $schedule->title }}</strong>
                            <span>{{ $schedule->schoolClass?->name ?? 'Tous les niveaux' }} · {{ $schedule->subject?->name ?? 'Toutes matières' }}</span>
                            @if($schedule->description)
                                <p style="margin:.65rem 0 0;color:var(--muted);line-height:1.55;">{{ \Illuminate\Support\Str::limit($schedule->description, 180) }}</p>
                            @endif
                        </div>
                        <div class="admin-clean-meta">
                            <span class="admin-badge {{ $statusClass }}">{{ $schedule->status }}</span>
                            <span class="admin-badge">{{ $schedule->activity_type }}</span><br>
                            <small>Semaine {{ $schedule->week_number }} · {{ $days[$schedule->weekday] ?? 'Jour ' . $schedule->weekday }} · {{ $schedule->unlock_time ?: 'heure libre' }}</small>
                        </div>
                        <div class="admin-period-pill">
                            <span><b>Déblocage :</b> {{ $schedule->unlocks_at ? $schedule->unlocks_at->format('d/m/Y H:i') : 'Selon jour/heure' }}</span>
                            <span><b>Clôture :</b> {{ $schedule->closes_at ? $schedule->closes_at->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="admin-row-actions">
                            <details class="admin-subscription-manage">
                                <summary>Modifier</summary>
                                <form method="POST" action="{{ route('admin.learning-program.update', $schedule) }}" class="admin-form">
                                    @csrf
                                    <input type="text" name="title" value="{{ $schedule->title }}" required>
                                    <select name="activity_type" required>
                                        @foreach(['course' => 'Cours', 'td' => 'TD', 'quiz' => 'Quiz', 'evaluation' => 'Évaluation'] as $value => $label)
                                            <option value="{{ $value }}" @selected($schedule->activity_type === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="week_number" value="{{ $schedule->week_number }}" min="1" max="52" required>
                                    <input type="number" name="weekday" value="{{ $schedule->weekday }}" min="1" max="7" required>
                                    <input type="time" name="unlock_time" value="{{ $schedule->unlock_time }}">
                                    <input type="datetime-local" name="unlocks_at" value="{{ $schedule->unlocks_at ? $schedule->unlocks_at->format('Y-m-d\\TH:i') : '' }}">
                                    <input type="datetime-local" name="closes_at" value="{{ $schedule->closes_at ? $schedule->closes_at->format('Y-m-d\\TH:i') : '' }}">
                                    <input type="number" name="duration_minutes" value="{{ $schedule->duration_minutes }}" min="1">
                                    <select name="status" required>
                                        @foreach(['scheduled' => 'Programmé', 'published' => 'Publié', 'draft' => 'Brouillon', 'archived' => 'Archivé'] as $value => $label)
                                            <option value="{{ $value }}" @selected($schedule->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <label><input type="checkbox" name="requires_subscription" value="1" @checked($schedule->requires_subscription)> Abonnement requis</label>
                                    <textarea name="description" rows="3">{{ $schedule->description }}</textarea>
                                    <button type="submit" class="btn btn--primary">Enregistrer</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.learning-program.delete', $schedule) }}" onsubmit="return confirm('Supprimer cette programmation ?');">
                                @csrf
                                <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucune activité programmée.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
