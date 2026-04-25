<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminClassController extends Controller
{
    use FiltersTableColumns;

    protected array $levels = [
        'enseignement_general' => 'Enseignement général',
        'enseignement_technique' => 'Enseignement technique',
    ];

    public function index()
    {
        $tableMissing = !$this->hasTableSafe('school_classes');
        $classes = $tableMissing
            ? collect()
            : SchoolClass::query()->orderBy('level')->orderBy('order')->orderBy('name')->get();

        return view('admin.classes.index', [
            'tableMissing' => $tableMissing,
            'classes' => $classes,
            'levels' => $this->levels,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')],
            'level' => ['required', Rule::in(array_keys($this->levels))],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        SchoolClass::query()->create($this->onlyExistingColumns('school_classes', $data));

        return back()->with('success', 'Classe ajoutée avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $class = SchoolClass::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')->ignore($class->id)],
            'level' => ['required', Rule::in(array_keys($this->levels))],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        $class->update($this->onlyExistingColumns('school_classes', $data));

        return back()->with('success', 'Classe mise à jour.');
    }

    public function delete(int $id)
    {
        $class = SchoolClass::query()->findOrFail($id);
        $linkedCounts = $this->linkedCounts($class->id);
        $totalLinks = array_sum($linkedCounts);

        if ($totalLinks > 0) {
            if ($this->hasColumnSafe('school_classes', 'is_active')) {
                $class->update(['is_active' => false]);
            }

            $details = [];
            if (($linkedCounts['students'] ?? 0) > 0) {
                $details[] = $linkedCounts['students'] . ' élève(s)';
            }
            if (($linkedCounts['assignments'] ?? 0) > 0) {
                $details[] = $linkedCounts['assignments'] . ' affectation(s) enseignant';
            }
            if (($linkedCounts['courses'] ?? 0) > 0) {
                $details[] = $linkedCounts['courses'] . ' cours';
            }
            if (($linkedCounts['td'] ?? 0) > 0) {
                $details[] = $linkedCounts['td'] . ' TD';
            }

            return back()->with(
                'warning',
                'Impossible de supprimer cette classe car elle est encore liée à ' . implode(', ', $details) . '. Elle a été désactivée pour éviter de casser les comptes existants.'
            );
        }

        $class->delete();

        return back()->with('success', 'Classe supprimée.');
    }

    protected function linkedCounts(int $classId): array
    {
        return [
            'students' => $this->countTableLinks('student_profiles', 'school_class_id', $classId),
            'assignments' => $this->countTableLinks('teacher_assignments', 'school_class_id', $classId),
            'courses' => $this->countTableLinks('courses', 'school_class_id', $classId),
            'td' => $this->countTableLinks('td_sets', 'school_class_id', $classId),
        ];
    }

    protected function countTableLinks(string $table, string $column, int $classId): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)->where($column, $classId)->count();
    }
}
