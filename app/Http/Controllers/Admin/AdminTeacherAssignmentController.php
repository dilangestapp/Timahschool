<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTeacherAssignmentController extends Controller
{
    use FiltersTableColumns;

    public function index()
    {
        $tableMissing = !$this->hasTableSafe('teacher_assignments');
        $teachers = $this->teacherOptions();
        $classes = $this->classOptions();
        $subjects = $this->subjectOptions();
        $assignments = $tableMissing ? collect() : $this->assignmentRows();

        $coverage = [
            'active' => $assignments->where('is_active', 1)->count(),
            'inactive' => $assignments->where('is_active', 0)->count(),
            'classes_without_teacher' => $this->countClassesWithoutTeacher(),
            'teachers_without_assignment' => $teachers->pluck('id')->diff($assignments->where('is_active', 1)->pluck('teacher_id')->unique())->count(),
        ];

        return view('admin.assignments.index', compact('tableMissing', 'teachers', 'classes', 'subjects', 'assignments', 'coverage'));
    }

    public function store(Request $request)
    {
        if (!$this->hasTableSafe('teacher_assignments')) {
            return back()->with('error', 'La table teacher_assignments est introuvable.');
        }

        $data = $request->validate([
            'teacher_id' => ['required', 'integer'],
            'school_class_id' => ['required', 'integer'],
            'subject_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $exists = DB::table('teacher_assignments')
            ->where('teacher_id', $data['teacher_id'])
            ->where('school_class_id', $data['school_class_id'])
            ->where('subject_id', $data['subject_id'])
            ->first();

        $payload = $this->onlyExistingColumns('teacher_assignments', [
            'teacher_id' => $data['teacher_id'],
            'school_class_id' => $data['school_class_id'],
            'subject_id' => $data['subject_id'],
            'notes' => $data['notes'] ?? null,
            'assigned_by' => auth()->id(),
            'is_active' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        if ($exists) {
            DB::table('teacher_assignments')->where('id', $exists->id)->update($payload);
        } else {
            DB::table('teacher_assignments')->insert($payload);
        }

        return back()->with('success', 'Affectation enregistrée avec succès.');
    }

    public function toggle(int $id)
    {
        if (!$this->hasTableSafe('teacher_assignments')) {
            return back()->with('error', 'La table teacher_assignments est introuvable.');
        }

        $assignment = DB::table('teacher_assignments')->where('id', $id)->first();
        if (!$assignment) {
            return back()->with('error', 'Affectation introuvable.');
        }

        $update = $this->onlyExistingColumns('teacher_assignments', [
            'is_active' => !((int) ($assignment->is_active ?? 0)),
            'assigned_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        DB::table('teacher_assignments')->where('id', $id)->update($update);

        return back()->with('success', 'Statut de l’affectation mis à jour.');
    }

    public function delete(int $id)
    {
        if (!$this->hasTableSafe('teacher_assignments')) {
            return back()->with('error', 'La table teacher_assignments est introuvable.');
        }

        DB::table('teacher_assignments')->where('id', $id)->delete();
        return back()->with('success', 'Affectation supprimée.');
    }

    protected function teacherOptions()
    {
        if (!$this->hasTableSafe('users')) {
            return collect();
        }

        $teacherRoleIds = $this->teacherRoleIds();
        $query = DB::table('users as u')->select('u.id');
        foreach (['full_name', 'name', 'username', 'status'] as $column) {
            if ($this->hasColumnSafe('users', $column)) {
                $query->addSelect('u.' . $column);
            }
        }
        if ($this->hasTableSafe('role_user') && !empty($teacherRoleIds)) {
            $query->join('role_user as ru', 'ru.user_id', '=', 'u.id')->whereIn('ru.role_id', $teacherRoleIds)->distinct();
        }
        return $query->orderBy('u.full_name')->orderBy('u.name')->get()->collect();
    }

    protected function classOptions()
    {
        if (!$this->hasTableSafe('school_classes')) {
            return collect();
        }
        return DB::table('school_classes')->select('id', 'name')->orderBy('order')->orderBy('name')->get()->collect();
    }

    protected function subjectOptions()
    {
        if (!$this->hasTableSafe('subjects')) {
            return collect();
        }
        return DB::table('subjects')->select('id', 'name')->orderBy('order')->orderBy('name')->get()->collect();
    }

    protected function assignmentRows()
    {
        $query = DB::table('teacher_assignments as ta')->select('ta.id', 'ta.teacher_id', 'ta.school_class_id', 'ta.subject_id');
        foreach (['notes', 'is_active', 'created_at'] as $column) {
            if ($this->hasColumnSafe('teacher_assignments', $column)) {
                $query->addSelect('ta.' . $column);
            }
        }
        if ($this->hasTableSafe('users')) {
            $query->leftJoin('users as u', 'u.id', '=', 'ta.teacher_id');
            if ($this->hasColumnSafe('users', 'full_name')) $query->addSelect('u.full_name as teacher_full_name');
            if ($this->hasColumnSafe('users', 'name')) $query->addSelect('u.name as teacher_name');
            if ($this->hasColumnSafe('users', 'username')) $query->addSelect('u.username as teacher_username');
        }
        if ($this->hasTableSafe('school_classes')) {
            $query->leftJoin('school_classes as sc', 'sc.id', '=', 'ta.school_class_id');
            if ($this->hasColumnSafe('school_classes', 'name')) $query->addSelect('sc.name as class_name');
        }
        if ($this->hasTableSafe('subjects')) {
            $query->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id');
            if ($this->hasColumnSafe('subjects', 'name')) $query->addSelect('s.name as subject_name');
        }
        return $query->orderByDesc('ta.id')->get()->collect();
    }

    protected function countClassesWithoutTeacher(): int
    {
        if (!$this->hasTableSafe('school_classes') || !$this->hasTableSafe('teacher_assignments')) {
            return 0;
        }
        $classes = DB::table('school_classes')->pluck('id');
        $assigned = DB::table('teacher_assignments')->when($this->hasColumnSafe('teacher_assignments', 'is_active'), fn ($q) => $q->where('is_active', 1))->pluck('school_class_id')->unique();
        return $classes->diff($assigned)->count();
    }
}
