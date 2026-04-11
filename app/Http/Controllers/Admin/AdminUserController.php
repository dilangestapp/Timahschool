<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    use FiltersTableColumns;

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $roleFilter = trim((string) $request->get('role', ''));
        $tableMissing = !$this->hasTableSafe('users');

        $roles = $this->hasTableSafe('roles') ? Role::query()->orderByRaw('COALESCE(display_name, name) asc')->get() : collect();

        $users = $tableMissing
            ? collect()
            : User::query()
                ->with(['roles', 'role'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        foreach (['name', 'full_name', 'username', 'email', 'phone', 'status'] as $column) {
                            if ($this->hasColumnSafe('users', $column)) {
                                $sub->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }
                    });
                })
                ->get()
                ->filter(function ($user) use ($roleFilter) {
                    if ($roleFilter === '') {
                        return true;
                    }
                    return method_exists($user, 'hasRole') ? $user->hasRole($roleFilter) : false;
                })
                ->sortByDesc('id')
                ->values();

        return view('admin.users.index', compact('search', 'roleFilter', 'roles', 'users', 'tableMissing'));
    }

    public function store(Request $request)
    {
        if (!$this->hasTableSafe('users')) {
            return back()->with('error', 'La table users est introuvable.');
        }

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
            'status' => ['nullable', 'string', 'max:50'],
            'role_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::query()->create($this->onlyExistingColumns('users', [
                'name' => $request->full_name,
                'full_name' => $request->full_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status ?: 'active',
                'password' => Hash::make($request->password),
            ]));

            $this->syncUserRole($user, $request->role_id);
        });

        return back()->with('success', 'Utilisateur créé avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $user = User::query()->findOrFail($id);

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['nullable', 'string', 'max:50'],
            'role_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($request, $user) {
            $data = [
                'name' => $request->full_name,
                'full_name' => $request->full_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status ?: 'active',
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($this->onlyExistingColumns('users', $data));
            $this->syncUserRole($user, $request->role_id);
        });

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    public function delete(int $id)
    {
        $user = User::query()->findOrFail($id);
        if ((int) auth()->id() === (int) $user->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte connecté.');
        }

        DB::transaction(function () use ($user) {
            try {
                $user->roles()->detach();
            } catch (\Throwable $e) {
            }
            $user->delete();
        });

        return back()->with('success', 'Utilisateur supprimé.');
    }

    protected function syncUserRole(User $user, $roleId): void
    {
        if (!$this->hasTableSafe('roles') || !$this->hasTableSafe('role_user')) {
            return;
        }

        if ($roleId) {
            $user->roles()->sync([(int) $roleId]);
            if ($this->hasColumnSafe('users', 'role_id')) {
                $user->forceFill(['role_id' => (int) $roleId])->save();
            }
            return;
        }

        $user->roles()->detach();
        if ($this->hasColumnSafe('users', 'role_id')) {
            $user->forceFill(['role_id' => null])->save();
        }
    }
}
