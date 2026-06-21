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
    private array $allLevels = [
        'enseignement_general' => 'Enseignement général',
        'secondaire_general' => 'Secondaire général',
        'general' => 'Général',
        'enseignement_technique' => 'Enseignement technique',
        'secondaire_technique' => 'Secondaire technique',
        'technical' => 'Technique',
        'technique' => 'Technique',
        'primaire' => 'Primaire',
        'primary' => 'Primaire',
        'anglophone' => 'Anglophone',
        'exam' => 'Classes d’examen',
    ];

    public function classes()
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);

        $department = $context['department'];
        $levels = $this->levelsForDepartment($department);

        $classes = collect();
        if (Schema::hasTable('school_classes')) {
            $classesQuery = DB::table('school_classes')->orderBy('level')->orderBy('order')->orderBy('name');
            if (!empty($levels) && Schema::hasColumn('school_classes', 'level')) {
                $classesQuery->whereIn('level', array_keys($levels));
            } elseif ($department->school_class_id ?? null) {
                $classesQuery->where('id', $department->school_class_id);
            } else {
                $classesQuery->whereRaw('1 = 0');
            }
            $classes = $classesQuery->get();
        }

        $subjects = collect();
        if (Schema::hasTable('subjects') && ($department->subject_id ?? null)) {
            $subjects = DB::table('subjects')
                ->where('id', $department->subject_id)
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        return view('supervision.department-classes', [
            'department' => $department,
            'linkedClassId' => $department->school_class_id ?? null,
            'linkedSubjectId' => $department->subject_id ?? null,
            'classes' => $classes,
            'subjects' => $subjects,
            'levels' => $levels ?: ['enseignement_technique' => 'Enseignement technique'],
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

        $levels = $this->levelsForDepartment($context['department']);
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')],
            'level' => ['required', Rule::in(array_keys($levels ?: $this->allLevels))],
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

        $levels = $this->levelsForDepartment($context['department']);
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')->ignore($class)],
            'level' => ['required', Rule::in(array_keys($levels ?: $this->allLevels))],
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

        abort_unless((int) ($context['department']->subject_id ?? 0) === $subject, 403);

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

        return back()->with('success', 'Matière du département mise à jour.');
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

    private function levelsForDepartment(object $department): array
    {
        $divisionType = '';
        if (($department->teaching_division_id ?? null) && Schema::hasTable('teaching_divisions')) {
            $divisionType = (string) DB::table('teaching_divisions')->where('id', $department->teaching_division_id)->value('type');
        }

        $haystack = Str::lower(($department->name ?? '') . ' ' . ($department->code ?? '') . ' ' . $divisionType);

        if (str_contains($haystack, 'tech')) {
            return [
                'enseignement_technique' => 'Enseignement technique',
                'secondaire_technique' => 'Secondaire technique',
                'technical' => 'Technique',
                'technique' => 'Technique',
            ];
        }

        if (str_contains($haystack, 'general') || str_contains($haystack, 'général')) {
            return [
                'enseignement_general' => 'Enseignement général',
                'secondaire_general' => 'Secondaire général',
                'general' => 'Général',
            ];
        }

        if (str_contains($haystack, 'primaire') || str_contains($haystack, 'primary')) {
            return ['primaire' => 'Primaire', 'primary' => 'Primaire'];
        }

        return [];
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
