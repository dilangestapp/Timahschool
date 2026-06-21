<?php

namespace App\Http\Controllers\Supervision;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DepartmentManagementController extends Controller
{
    private array $levels = [
        'enseignement_general' => 'Enseignement général',
        'enseignement_technique' => 'Enseignement technique',
    ];

    public function classes()
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);

        $classes = Schema::hasTable('school_classes') ? DB::table('school_classes')->orderBy('level')->orderBy('order')->orderBy('name')->get() : collect();
        $subjects = Schema::hasTable('subjects') ? DB::table('subjects')->orderBy('order')->orderBy('name')->get() : collect();

        return view('supervision.department-classes', [
            'department' => $context['department'],
            'linkedClassId' => $context['department']->school_class_id ?? null,
            'linkedSubjectId' => $context['department']->subject_id ?? null,
            'classes' => $classes,
            'subjects' => $subjects,
            'levels' => $this->levels,
        ]);
    }

    public function subjects()
    {
        return $this->classes();
    }

    public function storeClass(Request $request)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('school_classes'), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')],
            'level' => ['required', Rule::in(array_keys($this->levels))],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $id = DB::table('school_classes')->insertGetId($this->onlyExistingColumns('school_classes', [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->linkDepartmentColumn($context['department']->id, 'school_class_id', $id);

        return back()->with('success', 'Classe créée et liée au département. Elle est aussi visible côté admin.');
    }

    public function updateClass(Request $request, int $class)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('school_classes'), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')->ignore($class)],
            'level' => ['required', Rule::in(array_keys($this->levels))],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::table('school_classes')->where('id', $class)->update($this->onlyExistingColumns('school_classes', [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ]));

        $this->linkDepartmentColumn($context['department']->id, 'school_class_id', $class);

        return back()->with('success', 'Classe mise à jour et liée au département.');
    }

    public function storeSubject(Request $request)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('subjects'), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $id = DB::table('subjects')->insertGetId($this->onlyExistingColumns('subjects', [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?: '#2563eb',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->linkDepartmentColumn($context['department']->id, 'subject_id', $id);

        return back()->with('success', 'Matière créée et liée au département. Elle est aussi visible côté admin.');
    }

    public function updateSubject(Request $request, int $subject)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('subjects'), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')->ignore($subject)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::table('subjects')->where('id', $subject)->update($this->onlyExistingColumns('subjects', [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?: '#2563eb',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ]));

        $this->linkDepartmentColumn($context['department']->id, 'subject_id', $subject);

        return back()->with('success', 'Matière mise à jour et liée au département.');
    }

    private function departmentContext(): array
    {
        if (!auth()->check() || !Schema::hasTable('pedagogical_responsibilities') || !Schema::hasTable('teaching_departments')) {
            return ['allowed' => false, 'department' => null];
        }

        $responsibility = DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('scope_type', 'department')
                    ->orWhere('role_title', 'like', '%Responsable de département%')
                    ->orWhere('role_title', 'like', '%filière%');
            })
            ->orderByDesc('id')
            ->first();

        if (!$responsibility || !$responsibility->teaching_department_id) {
            return ['allowed' => false, 'department' => null];
        }

        $department = DB::table('teaching_departments')->where('id', $responsibility->teaching_department_id)->first();

        return ['allowed' => (bool) $department, 'department' => $department];
    }

    private function linkDepartmentColumn(int $departmentId, string $column, int $value): void
    {
        if (Schema::hasTable('teaching_departments') && Schema::hasColumn('teaching_departments', $column)) {
            DB::table('teaching_departments')->where('id', $departmentId)->update([$column => $value, 'updated_at' => now()]);
        }
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
