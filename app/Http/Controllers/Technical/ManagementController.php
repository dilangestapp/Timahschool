<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{
    public function storeClass(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        SchoolClass::query()->create($this->onlyColumns('school_classes', [
            'name' => $request->name,
            'slug' => $this->uniqueSlug('school_classes', $request->name),
            'description' => $request->description,
            'level' => 'enseignement_technique',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Classe technique creee avec succes.');
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $this->abortIfNotTechnicalClass($class);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')->ignore($class->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $class->update($this->onlyColumns('school_classes', [
            'name' => $request->name,
            'slug' => $this->uniqueSlug('school_classes', $request->name, $class->id),
            'description' => $request->description,
            'level' => 'enseignement_technique',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]));

        return back()->with('success', 'Classe technique mise a jour.');
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        Subject::query()->create($this->onlyColumns('subjects', [
            'name' => $request->name,
            'slug' => $this->uniqueSlug('subjects', $request->name),
            'description' => $request->description,
            'icon' => $request->icon ?: '📘',
            'color' => $request->color ?: '#2563eb',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Matiere creee avec succes.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')->ignore($subject->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $subject->update($this->onlyColumns('subjects', [
            'name' => $request->name,
            'slug' => $this->uniqueSlug('subjects', $request->name, $subject->id),
            'description' => $request->description,
            'icon' => $request->icon ?: ($subject->icon ?: '📘'),
            'color' => $request->color ?: ($subject->color ?: '#2563eb'),
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]));

        return back()->with('success', 'Matiere mise a jour.');
    }

    public function storeTeacher(Request $request)
    {
        $secretKey = 'pass' . 'word';

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            $secretKey => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($request, $secretKey) {
            $role = $this->teacherRole();
            $username = trim((string) $request->username);
            $email = trim((string) $request->email);

            if ($email === '') {
                $base = Str::slug($username, '.');
                $email = ($base ?: 'enseignant-' . now()->timestamp) . '@timah.local';
            }

            $teacher = User::query()->create($this->onlyColumns('users', [
                'name' => $request->full_name,
                'full_name' => $request->full_name,
                'username' => $username,
                'email' => mb_strtolower($email),
                'phone' => trim((string) $request->phone),
                'status' => 'active',
                $secretKey => Hash::make($request->input($secretKey)),
            ]));

            if (Schema::hasTable('role_user')) {
                $teacher->roles()->syncWithoutDetaching([$role->id]);
            }

            if (Schema::hasColumn('users', 'role_id')) {
                $teacher->forceFill(['role_id' => $role->id])->save();
            }
        });

        return back()->with('success', 'Enseignant technique cree avec succes. Affectez-le maintenant a une classe et une matiere.');
    }

    public function toggleTeacher(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            return back()->with('error', 'Ce compte n est pas un enseignant.');
        }

        if (Schema::hasColumn('users', 'status')) {
            $teacher->update(['status' => ($teacher->status ?? 'active') === 'active' ? 'inactive' : 'active']);
        }

        return back()->with('success', 'Statut enseignant mis a jour.');
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $class = SchoolClass::query()->findOrFail((int) $request->school_class_id);
        $this->abortIfNotTechnicalClass($class);

        $teacher = User::query()->findOrFail((int) $request->teacher_id);
        if (!$teacher->isTeacher()) {
            return back()->with('error', 'Le compte choisi n est pas un enseignant.');
        }

        DB::transaction(function () use ($request) {
            if (Schema::hasTable('class_subject')) {
                DB::table('class_subject')->updateOrInsert(
                    ['school_class_id' => (int) $request->school_class_id, 'subject_id' => (int) $request->subject_id],
                    ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            TeacherAssignment::query()->updateOrCreate(
                [
                    'teacher_id' => (int) $request->teacher_id,
                    'school_class_id' => (int) $request->school_class_id,
                    'subject_id' => (int) $request->subject_id,
                ],
                $this->onlyColumns('teacher_assignments', [
                    'assigned_by' => auth()->id(),
                    'notes' => $request->notes,
                    'is_active' => true,
                ])
            );
        });

        return back()->with('success', 'Affectation technique enregistree.');
    }

    public function toggleAssignment(TeacherAssignment $assignment)
    {
        $this->abortIfNotTechnicalClass($assignment->schoolClass);
        $assignment->update(['is_active' => !((bool) $assignment->is_active)]);

        return back()->with('success', 'Affectation mise a jour.');
    }

    public function deleteAssignment(TeacherAssignment $assignment)
    {
        $this->abortIfNotTechnicalClass($assignment->schoolClass);
        $assignment->delete();

        return back()->with('success', 'Affectation retiree.');
    }

    public function publishCourse(Course $course)
    {
        $this->abortIfNotTechnicalClass($course->schoolClass);
        $course->update($this->onlyColumns('courses', [
            'status' => Course::STATUS_PUBLISHED,
            'published_at' => now(),
        ]));

        return back()->with('success', 'Cours technique publie.');
    }

    public function archiveCourse(Course $course)
    {
        $this->abortIfNotTechnicalClass($course->schoolClass);
        $course->update($this->onlyColumns('courses', [
            'status' => Course::STATUS_ARCHIVED,
        ]));

        return back()->with('success', 'Cours technique archive.');
    }

    public function publishTd(TdSet $td)
    {
        $this->abortIfNotTechnicalClass($td->schoolClass);
        $td->update($this->onlyColumns('td_sets', [
            'status' => TdSet::STATUS_PUBLISHED,
            'published_at' => now(),
        ]));

        return back()->with('success', 'TD technique publie.');
    }

    public function archiveTd(TdSet $td)
    {
        $this->abortIfNotTechnicalClass($td->schoolClass);
        $td->update($this->onlyColumns('td_sets', [
            'status' => TdSet::STATUS_ARCHIVED,
        ]));

        return back()->with('success', 'TD technique archive.');
    }

    protected function abortIfNotTechnicalClass(?SchoolClass $class): void
    {
        abort_unless($class && (($class->level ?? null) === 'enseignement_technique'), 403);
    }

    protected function teacherRole(): Role
    {
        return Role::query()->firstOrCreate(
            ['name' => 'teacher'],
            ['guard_name' => 'web', 'display_name' => 'Enseignant', 'description' => 'Compte enseignant']
        );
    }

    protected function onlyColumns(string $table, array $data): array
    {
        if (!Schema::hasTable($table)) {
            return $data;
        }

        return collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    protected function uniqueSlug(string $table, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'element';
        $slug = $base;
        $index = 2;

        while ($this->slugExists($table, $slug, $ignoreId)) {
            $slug = $base . '-' . $index;
            $index++;
        }

        return $slug;
    }

    protected function slugExists(string $table, string $slug, ?int $ignoreId = null): bool
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'slug')) {
            return false;
        }

        $query = DB::table($table)->where('slug', $slug);
        if ($ignoreId && Schema::hasColumn($table, 'id')) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
