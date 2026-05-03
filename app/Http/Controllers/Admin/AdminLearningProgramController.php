<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningProgramSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLearningProgramController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $tableMissing = !Schema::hasTable('learning_program_schedules');

        $items = $tableMissing
            ? collect()
            : LearningProgramSchedule::query()
                ->with(['schoolClass', 'subject'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('activity_type', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                })
                ->orderBy('unlocks_at')
                ->orderBy('weekday')
                ->get();

        $classes = Schema::hasTable('school_classes')
            ? SchoolClass::query()->orderBy('order')->orderBy('name')->get()
            : collect();

        $subjects = Schema::hasTable('subjects')
            ? Subject::query()->orderBy('order')->orderBy('name')->get()
            : collect();

        return view('admin.learning-program.index', compact('items', 'classes', 'subjects', 'search', 'tableMissing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'activity_type' => ['required', 'string', 'max:60'],
            'week_number' => ['required', 'integer', 'min:1', 'max:52'],
            'weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'unlock_time' => ['nullable', 'date_format:H:i'],
            'unlocks_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string', 'max:40'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $data['requires_subscription'] = $request->boolean('requires_subscription', true);
        LearningProgramSchedule::query()->create($data);

        return back()->with('success', 'Activité programmée avec succès.');
    }

    public function update(Request $request, LearningProgramSchedule $schedule)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'activity_type' => ['required', 'string', 'max:60'],
            'week_number' => ['required', 'integer', 'min:1', 'max:52'],
            'weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'unlock_time' => ['nullable', 'date_format:H:i'],
            'unlocks_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string', 'max:40'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $data['requires_subscription'] = $request->boolean('requires_subscription', true);
        $schedule->update($data);

        return back()->with('success', 'Programmation mise à jour.');
    }

    public function delete(LearningProgramSchedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Activité programmée supprimée.');
    }
}
