<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTeacherController extends Controller
{
    use FiltersTableColumns;

    public function index()
    {
        $teachers = collect();
        if ($this->hasTableSafe('users')) {
            $teachers = User::query()->with(['roles', 'role'])->get()->filter(fn ($user) => method_exists($user, 'isTeacher') && $user->isTeacher())->values();
        }

        $stats = [
            'total' => $teachers->count(),
            'active' => $teachers->where('status', 'active')->count(),
            'inactive' => $teachers->where('status', 'inactive')->count(),
            'unassigned' => $this->countUnassigned($teachers),
        ];

        $assignmentCounts = $this->teacherAssignmentCounts();
        $courseCounts = $this->teacherCourseCounts();
        $messageCounts = $this->teacherMessageCounts();

        return view('admin.teachers.index', compact('teachers', 'stats', 'assignmentCounts', 'courseCounts', 'messageCounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($request) {
            $username = trim((string) $request->username);
            $email = trim((string) $request->email);

            if ($email === '') {
                $base = Str::slug($username, '.');
                if ($base === '') {
                    $base = 'enseignant-' . now()->timestamp;
                }
                $email = mb_strtolower($base) . '@timahschool.local';
            }

            $phone = trim((string) $request->phone);

            $teacher = User::query()->create($this->onlyExistingColumns('users', [
                'name' => $request->full_name,
                'full_name' => $request->full_name,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'status' => 'active',
                'password' => Hash::make($request->password),
            ]));

            $teacherRole = Role::query()->whereIn(DB::raw('LOWER(name)'), ['teacher', 'enseignant'])->first();
            if (!$teacherRole) {
                $teacherRole = Role::query()->create($this->onlyExistingColumns('roles', [
                    'name' => 'teacher',
                    'display_name' => 'Enseignant',
                    'guard_name' => 'web',
                    'description' => 'Compte enseignant',
                ]));
            }

            if ($this->hasTableSafe('role_user')) {
                $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);
            }
            if ($this->hasColumnSafe('users', 'role_id')) {
                $teacher->forceFill(['role_id' => $teacherRole->id])->save();
            }
        });

        return back()->with('success', 'Compte enseignant créé avec succès.');
    }

    public function toggle(User $user)
    {
        if ($this->hasColumnSafe('users', 'status')) {
            $user->update(['status' => ($user->status ?? 'active') === 'active' ? 'inactive' : 'active']);
        }

        return back()->with('success', 'Statut du compte enseignant mis à jour.');
    }

    protected function countUnassigned($teachers): int
    {
        if (!$this->hasTableSafe('teacher_assignments')) {
            return 0;
        }

        $assignedIds = DB::table('teacher_assignments')
            ->when($this->hasColumnSafe('teacher_assignments', 'is_active'), fn ($q) => $q->where('is_active', 1))
            ->pluck('teacher_id')
            ->unique();

        return $teachers->pluck('id')->diff($assignedIds)->count();
    }

    protected function teacherAssignmentCounts(): array
    {
        if (!$this->hasTableSafe('teacher_assignments')) {
            return [];
        }

        return DB::table('teacher_assignments')
            ->selectRaw('teacher_id, COUNT(*) as total')
            ->when($this->hasColumnSafe('teacher_assignments', 'is_active'), fn ($q) => $q->where('is_active', 1))
            ->groupBy('teacher_id')
            ->pluck('total', 'teacher_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    protected function teacherCourseCounts(): array
    {
        if (!$this->hasTableSafe('courses') || !$this->hasColumnSafe('courses', 'created_by')) {
            return [];
        }

        return DB::table('courses')
            ->selectRaw('created_by as teacher_id, COUNT(*) as total')
            ->groupBy('created_by')
            ->pluck('total', 'teacher_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    protected function teacherMessageCounts(): array
    {
        if (!$this->hasTableSafe('teacher_messages') || !$this->hasColumnSafe('teacher_messages', 'teacher_id')) {
            return [];
        }

        return DB::table('teacher_messages')
            ->selectRaw('teacher_id, COUNT(*) as total')
            ->groupBy('teacher_id')
            ->pluck('total', 'teacher_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
