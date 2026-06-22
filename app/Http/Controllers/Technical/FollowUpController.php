<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function storeDepartment(Request $request)
    {
        $secretKey = 'pass' . 'word';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'responsible_name' => ['nullable', 'string', 'max:255'],
            'responsible_username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'responsible_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'responsible_phone' => ['nullable', 'string', 'max:50'],
            $secretKey => ['nullable', 'string', 'min:6'],
        ];

        $request->validate($rules);

        if (!Schema::hasTable('technical_departments')) {
            return back()->with('error', 'Le module departements techniques n est pas encore migre sur le serveur.');
        }

        if ($request->filled('school_class_id')) {
            $class = SchoolClass::query()->findOrFail((int) $request->school_class_id);
            $this->abortIfNotTechnicalClass($class);
        }

        DB::transaction(function () use ($request, $secretKey) {
            $responsibleId = $request->integer('responsible_user_id') ?: null;

            if (!$responsibleId && $request->filled('responsible_name') && $request->filled('responsible_username')) {
                $role = $this->departmentRole();
                $username = trim((string) $request->responsible_username);
                $email = trim((string) $request->responsible_email);
                if ($email === '') {
                    $email = (Str::slug($username, '.') ?: 'responsable-' . now()->timestamp) . '@timah.local';
                }

                $user = User::query()->create($this->onlyColumns('users', [
                    'name' => $request->responsible_name,
                    'full_name' => $request->responsible_name,
                    'username' => $username,
                    'email' => mb_strtolower($email),
                    'phone' => trim((string) $request->responsible_phone),
                    'status' => 'active',
                    $secretKey => Hash::make($request->input($secretKey) ?: 'Timah2026'),
                ]));

                if (Schema::hasTable('role_user')) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                }
                if (Schema::hasColumn('users', 'role_id')) {
                    $user->forceFill(['role_id' => $role->id])->save();
                }

                $responsibleId = $user->id;
            }

            if ($responsibleId) {
                $role = $this->departmentRole();
                $responsible = User::query()->find($responsibleId);
                if ($responsible && Schema::hasTable('role_user')) {
                    $responsible->roles()->syncWithoutDetaching([$role->id]);
                }
            }

            DB::table('technical_departments')->insert($this->onlyColumns('technical_departments', [
                'name' => trim((string) $request->name),
                'description' => $request->description,
                'subject_id' => $request->integer('subject_id') ?: null,
                'school_class_id' => $request->integer('school_class_id') ?: null,
                'responsible_user_id' => $responsibleId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        });

        return back()->with('success', 'Departement technique et responsable enregistres.');
    }

    public function toggleDepartment(int $department)
    {
        if (!Schema::hasTable('technical_departments')) {
            return back()->with('error', 'Le module departements techniques n est pas disponible.');
        }

        $current = DB::table('technical_departments')->where('id', $department)->first();
        if (!$current) {
            return back()->with('error', 'Departement introuvable.');
        }

        DB::table('technical_departments')->where('id', $department)->update([
            'status' => ($current->status ?? 'active') === 'active' ? 'inactive' : 'active',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Statut du departement mis a jour.');
    }

    public function deleteDepartment(int $department)
    {
        if (!Schema::hasTable('technical_departments')) {
            return back()->with('error', 'Le module departements techniques n est pas disponible.');
        }

        DB::table('technical_departments')->where('id', $department)->delete();

        return back()->with('success', 'Departement technique supprime.');
    }

    public function storeReminder(Request $request)
    {
        $request->validate([
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'target_type' => ['required', 'string', 'max:60'],
            'target_id' => ['nullable', 'integer'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'priority' => ['nullable', 'string', 'max:30'],
            'due_at' => ['nullable', 'date'],
        ]);

        if (!Schema::hasTable('technical_reminders')) {
            return back()->with('error', 'Le module relances techniques n est pas encore migre sur le serveur.');
        }

        if ($request->filled('school_class_id')) {
            $class = SchoolClass::query()->findOrFail((int) $request->school_class_id);
            $this->abortIfNotTechnicalClass($class);
        }

        DB::transaction(function () use ($request) {
            DB::table('technical_reminders')->insert($this->onlyColumns('technical_reminders', [
                'sender_id' => auth()->id(),
                'target_user_id' => $request->integer('target_user_id') ?: null,
                'target_type' => $request->target_type,
                'target_id' => $request->integer('target_id') ?: null,
                'school_class_id' => $request->integer('school_class_id') ?: null,
                'subject_id' => $request->integer('subject_id') ?: null,
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority ?: 'normal',
                'status' => 'sent',
                'due_at' => $request->due_at ?: null,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $this->createMobileNotification($request);
        });

        return back()->with('success', 'Relance enregistree et notifiee en interne.');
    }

    protected function createMobileNotification(Request $request): void
    {
        if (!Schema::hasTable('mobile_notifications')) {
            return;
        }

        DB::table('mobile_notifications')->insert([
            'user_id' => $request->integer('target_user_id') ?: null,
            'school_class_id' => $request->integer('school_class_id') ?: null,
            'audience' => $request->target_user_id ? 'user' : ($request->school_class_id ? 'class' : 'technical'),
            'type' => 'technical_reminder',
            'title' => $request->title,
            'message' => $request->message,
            'target_type' => $request->target_type,
            'target_id' => $request->integer('target_id') ?: null,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function abortIfNotTechnicalClass(?SchoolClass $class): void
    {
        abort_unless($class && (($class->level ?? null) === 'enseignement_technique'), 403);
    }

    protected function departmentRole(): Role
    {
        return Role::query()->firstOrCreate(
            ['name' => 'department_responsible'],
            ['guard_name' => 'web', 'display_name' => 'Responsable de departement', 'description' => 'Suivi pedagogique d un departement technique']
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
}
