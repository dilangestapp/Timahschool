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
        'enseignement_technique' => 'Enseignement technique',
        'primaire' => 'Primaire',
        'anglophone' => 'Anglophone',
        'exam' => 'Classes d’examen',
    ];

    public function classes()
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);

        $department = $context['department'];
        $this->backfillLegacyLinks($department);

        $levels = $this->levelsForDepartment($department);
        $classIds = $this->departmentClassIds($department);
        $subjectIds = $this->departmentSubjectIds($department);

        $classes = collect();
        if (Schema::hasTable('school_classes')) {
            $query = DB::table('school_classes')->orderBy('level')->orderBy('order')->orderBy('name');
            if ($classIds->isNotEmpty()) {
                $query->whereIn('id', $classIds->all());
            } else {
                $query->whereRaw('1 = 0');
            }
            $classes = $query->get();
        }

        $subjects = collect();
        if (Schema::hasTable('subjects')) {
            $query = DB::table('subjects')->orderBy('order')->orderBy('name');
            if ($subjectIds->isNotEmpty()) {
                $query->whereIn('id', $subjectIds->all());
            } else {
                $query->whereRaw('1 = 0');
            }
            $subjects = $query->get();
        }

        return view('supervision.department-classes', [
            'department' => $department,
            'linkedClassId' => $department->school_class_id ?? null,
            'linkedSubjectId' => $department->subject_id ?? null,
            'linkedClassIds' => $classIds->all(),
            'linkedSubjectIds' => $subjectIds->all(),
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

        $department = $context['department'];
        $levels = $this->levelsForDepartment($department);

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

        $this->attachDepartmentClass($department->id, $id);
        $this->setLegacyColumnIfEmpty($department->id, 'school_class_id', $id);

        return back()->with('success', 'Classe créée et ajoutée au département. Les anciennes classes restent liées.');
    }

    public function updateClass(Request $request, int $class)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('school_classes'), 404);

        $department = $context['department'];
        $levels = $this->levelsForDepartment($department);

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

        $this->attachDepartmentClass($department->id, $class);
        $this->setLegacyColumnIfEmpty($department->id, 'school_class_id', $class);

        return back()->with('success', 'Classe mise à jour et conservée dans le département.');
    }

    public function storeSubject(Request $request)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('subjects'), 404);

        $department = $context['department'];

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

        $this->attachDepartmentSubject($department->id, $id);
        $this->setLegacyColumnIfEmpty($department->id, 'subject_id', $id);

        return back()->with('success', 'Matière créée et ajoutée au département. Les anciennes matières restent liées.');
    }

    public function updateSubject(Request $request, int $subject)
    {
        $context = $this->departmentContext();
        abort_unless($context['allowed'], 403);
        abort_unless(Schema::hasTable('subjects'), 404);

        $department = $context['department'];
        $subjectIds = $this->departmentSubjectIds($department);
        abort_unless($subjectIds->contains($subject) || (int) ($department->subject_id ?? 0) === $subject, 403);

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

        $this->attachDepartmentSubject($department->id, $subject);
        $this->setLegacyColumnIfEmpty($department->id, 'subject_id', $subject);

        return back()->with('success', 'Matière mise à jour et conservée dans le département.');
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
        $haystack = $this->departmentHaystack($department);
        if (str_contains($haystack, 'tech')) return ['enseignement_technique' => 'Enseignement technique'];
        if (str_contains($haystack, 'general') || str_contains($haystack, 'général')) return ['enseignement_general' => 'Enseignement général'];
        if (str_contains($haystack, 'primaire') || str_contains($haystack, 'primary')) return ['primaire' => 'Primaire'];
        return [];
    }

    private function departmentHaystack(object $department): string
    {
        $divisionType = '';
        if (($department->teaching_division_id ?? null) && Schema::hasTable('teaching_divisions')) {
            $divisionType = (string) DB::table('teaching_divisions')->where('id', $department->teaching_division_id)->value('type');
        }
        return Str::lower(($department->name ?? '') . ' ' . ($department->code ?? '') . ' ' . $divisionType);
    }

    private function departmentClassIds(object $department): \Illuminate\Support\Collection
    {
        $ids = collect();
        if (Schema::hasTable('teaching_department_school_class')) {
            $ids = DB::table('teaching_department_school_class')->where('teaching_department_id', $department->id)->pluck('school_class_id');
        }
        if ($ids->isEmpty() && ($department->school_class_id ?? null)) {
            $ids = collect([(int) $department->school_class_id]);
        }
        return $ids->map(fn ($id) => (int) $id)->unique()->values();
    }

    private function departmentSubjectIds(object $department): \Illuminate\Support\Collection
    {
        $ids = collect();
        if (Schema::hasTable('teaching_department_subject')) {
            $ids = DB::table('teaching_department_subject')->where('teaching_department_id', $department->id)->pluck('subject_id');
        }
        if ($ids->isEmpty() && ($department->subject_id ?? null)) {
            $ids = collect([(int) $department->subject_id]);
        }
        return $ids->map(fn ($id) => (int) $id)->unique()->values();
    }

    private function attachDepartmentClass(int $departmentId, int $classId): void
    {
        if (Schema::hasTable('teaching_department_school_class')) {
            DB::table('teaching_department_school_class')->updateOrInsert(
                ['teaching_department_id' => $departmentId, 'school_class_id' => $classId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function attachDepartmentSubject(int $departmentId, int $subjectId): void
    {
        if (Schema::hasTable('teaching_department_subject')) {
            DB::table('teaching_department_subject')->updateOrInsert(
                ['teaching_department_id' => $departmentId, 'subject_id' => $subjectId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function backfillLegacyLinks(object $department): void
    {
        if (($department->school_class_id ?? null)) {
            $this->attachDepartmentClass($department->id, (int) $department->school_class_id);
        }
        if (($department->subject_id ?? null)) {
            $this->attachDepartmentSubject($department->id, (int) $department->subject_id);
        }
    }

    private function setLegacyColumnIfEmpty(int $departmentId, string $column, int $value): void
    {
        if (Schema::hasTable('teaching_departments') && Schema::hasColumn('teaching_departments', $column)) {
            $current = DB::table('teaching_departments')->where('id', $departmentId)->value($column);
            if (!$current) {
                DB::table('teaching_departments')->where('id', $departmentId)->update([$column => $value, 'updated_at' => now()]);
            }
        }
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
