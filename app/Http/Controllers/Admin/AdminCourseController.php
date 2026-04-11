<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCourseController extends Controller
{
    use FiltersTableColumns;

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $classId = (int) $request->get('class_id', 0);
        $subjectId = (int) $request->get('subject_id', 0);
        $tableMissing = !$this->hasTableSafe('courses');

        $subjects = $this->hasTableSafe('subjects') ? Subject::query()->orderBy('name')->get() : collect();
        $classes = $this->hasTableSafe('school_classes') ? SchoolClass::query()->orderBy('order')->orderBy('name')->get() : collect();

        $courses = $tableMissing ? collect() : Course::query()
            ->with(['subject', 'schoolClass'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    foreach (['title', 'description', 'status', 'level'] as $column) {
                        if ($this->hasColumnSafe('courses', $column)) {
                            $sub->orWhere($column, 'like', '%' . $search . '%');
                        }
                    }
                });
            })
            ->when($status !== '' && $this->hasColumnSafe('courses', 'status'), fn ($q) => $q->where('status', $status))
            ->when($classId > 0 && $this->hasColumnSafe('courses', 'school_class_id'), fn ($q) => $q->where('school_class_id', $classId))
            ->when($subjectId > 0 && $this->hasColumnSafe('courses', 'subject_id'), fn ($q) => $q->where('subject_id', $subjectId))
            ->latest()
            ->get();

        $summary = [
            'total' => $courses->count(),
            'draft' => $courses->where('status', 'draft')->count(),
            'published' => $courses->where('status', 'published')->count(),
            'archived' => $courses->where('status', 'archived')->count(),
        ];

        return view('admin.courses.index', compact('search', 'status', 'classId', 'subjectId', 'tableMissing', 'subjects', 'classes', 'courses', 'summary'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('courses', 'title')],
            'subject_id' => ['required', 'integer'],
            'school_class_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'level' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Course::STATUS_DRAFT, Course::STATUS_PUBLISHED, Course::STATUS_ARCHIVED])],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        Course::query()->create($this->onlyExistingColumns('courses', [
            'subject_id' => (int) $request->subject_id,
            'school_class_id' => (int) $request->school_class_id,
            'created_by' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'objectives' => $request->objectives,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'status' => $request->status,
            'published_at' => $request->status === Course::STATUS_PUBLISHED ? now() : null,
        ]));

        return back()->with('success', 'Cours ajouté avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $course = Course::query()->findOrFail($id);
        $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('courses', 'title')->ignore($course->id)],
            'subject_id' => ['required', 'integer'],
            'school_class_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'level' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Course::STATUS_DRAFT, Course::STATUS_PUBLISHED, Course::STATUS_ARCHIVED])],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $course->update($this->onlyExistingColumns('courses', [
            'subject_id' => (int) $request->subject_id,
            'school_class_id' => (int) $request->school_class_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'objectives' => $request->objectives,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'status' => $request->status,
            'published_at' => $request->status === Course::STATUS_PUBLISHED ? ($course->published_at ?: now()) : null,
        ]));

        return back()->with('success', 'Cours mis à jour.');
    }

    public function publish(int $id)
    {
        $course = Course::query()->findOrFail($id);
        $course->update($this->onlyExistingColumns('courses', ['status' => Course::STATUS_PUBLISHED, 'published_at' => now()]));
        return back()->with('success', 'Cours publié.');
    }

    public function archive(int $id)
    {
        $course = Course::query()->findOrFail($id);
        $course->update($this->onlyExistingColumns('courses', ['status' => Course::STATUS_ARCHIVED]));
        return back()->with('success', 'Cours archivé.');
    }

    public function delete(int $id)
    {
        Course::query()->findOrFail($id)->delete();
        return back()->with('success', 'Cours supprimé.');
    }
}
