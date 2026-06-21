<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\TeacherWeeklyProgram;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class WeeklyProgramController extends Controller
{
    public function index(Request $request)
    {
        $teacher = auth()->user();
        $weekStart = $this->weekStart($request->get('week_start'));
        $assignments = $this->assignments($teacher->id);

        $programs = Schema::hasTable('teacher_weekly_programs')
            ? TeacherWeeklyProgram::query()
                ->with(['schoolClass', 'subject', 'assignment'])
                ->where('teacher_id', $teacher->id)
                ->whereDate('week_start', $weekStart->toDateString())
                ->orderBy('program_date')
                ->orderBy('start_time')
                ->orderBy('id')
                ->get()
            : collect();

        return view('teacher.weekly-program.index', [
            'teacher' => $teacher,
            'assignments' => $assignments,
            'programs' => $programs,
            'weekStart' => $weekStart,
            'previousWeek' => $weekStart->copy()->subWeek(),
            'nextWeek' => $weekStart->copy()->addWeek(),
            'weekDays' => $this->weekDays($weekStart),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $teacherId = auth()->id();
        $assignments = $this->assignments($teacherId)->keyBy('id');
        $data = $this->validated($request);

        abort_unless($assignments->has((int) $data['teacher_assignment_id']), 403);
        $assignment = $assignments[(int) $data['teacher_assignment_id']];
        $programDate = Carbon::parse($data['program_date'])->startOfDay();
        $weekStart = $programDate->copy()->startOfWeek();

        TeacherWeeklyProgram::query()->create([
            'teacher_id' => $teacherId,
            'teacher_assignment_id' => $assignment->id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'week_start' => $weekStart->toDateString(),
            'program_date' => $programDate->toDateString(),
            'weekday' => $programDate->isoWeekday(),
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'activity_type' => $data['activity_type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'teacher_notes' => $data['teacher_notes'] ?? null,
            'status' => $data['status'],
            'created_by' => $teacherId,
        ]);

        return redirect()->route('teacher.weekly-program.index', ['week_start' => $weekStart->toDateString()])->with('success', 'Activité programmée pour la semaine.');
    }

    public function update(Request $request, TeacherWeeklyProgram $program)
    {
        $this->authorizeProgram($program);
        $teacherId = auth()->id();
        $assignments = $this->assignments($teacherId)->keyBy('id');
        $data = $this->validated($request);

        abort_unless($assignments->has((int) $data['teacher_assignment_id']), 403);
        $assignment = $assignments[(int) $data['teacher_assignment_id']];
        $programDate = Carbon::parse($data['program_date'])->startOfDay();
        $weekStart = $programDate->copy()->startOfWeek();

        $program->update([
            'teacher_assignment_id' => $assignment->id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'week_start' => $weekStart->toDateString(),
            'program_date' => $programDate->toDateString(),
            'weekday' => $programDate->isoWeekday(),
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'activity_type' => $data['activity_type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'teacher_notes' => $data['teacher_notes'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('teacher.weekly-program.index', ['week_start' => $weekStart->toDateString()])->with('success', 'Programme mis à jour.');
    }

    public function status(Request $request, TeacherWeeklyProgram $program)
    {
        $this->authorizeProgram($program);
        $data = $request->validate([
            'status' => ['required', 'in:draft,published,done,cancelled'],
        ]);

        $program->update(['status' => $data['status']]);

        return back()->with('success', 'Statut du programme mis à jour.');
    }

    public function destroy(TeacherWeeklyProgram $program)
    {
        $this->authorizeProgram($program);
        $program->delete();

        return back()->with('success', 'Activité retirée du programme.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'teacher_assignment_id' => ['required', 'integer'],
            'program_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'activity_type' => ['required', 'in:course,td,revision,evaluation,correction,live,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'teacher_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,done,cancelled'],
        ]);
    }

    protected function authorizeProgram(TeacherWeeklyProgram $program): void
    {
        abort_unless((int) $program->teacher_id === (int) auth()->id(), 403);
    }

    protected function assignments(int $teacherId): Collection
    {
        if (!Schema::hasTable('teacher_assignments')) {
            return collect();
        }

        return TeacherAssignment::query()
            ->with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();
    }

    protected function weekStart(?string $value): Carbon
    {
        try {
            return $value ? Carbon::parse($value)->startOfWeek() : now()->startOfWeek();
        } catch (\Throwable $e) {
            return now()->startOfWeek();
        }
    }

    protected function weekDays(Carbon $weekStart): array
    {
        $labels = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        return collect(range(0, 6))->map(function (int $offset) use ($weekStart, $labels) {
            $date = $weekStart->copy()->addDays($offset);

            return [
                'label' => $labels[$offset],
                'date' => $date,
            ];
        })->all();
    }

    protected function types(): array
    {
        return [
            'course' => 'Cours',
            'td' => 'TD',
            'revision' => 'Révision',
            'evaluation' => 'Évaluation',
            'correction' => 'Correction',
            'live' => 'Séance en direct',
            'other' => 'Autre activité',
        ];
    }

    protected function statuses(): array
    {
        return [
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'done' => 'Terminé',
            'cancelled' => 'Annulé',
        ];
    }
}
