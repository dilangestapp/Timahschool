<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSetupController extends Controller
{
    public function showSetupForm()
    {
        if ($this->adminAlreadyExists()) {
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Un compte administrateur existe déjà.']);
        }

        return view('admin.auth.setup-admin', [
            'adminPath' => trim((string) config('timahschool.admin_path', 'backoffice-access'), '/'),
        ]);
    }

    public function storeSetupForm(Request $request)
    {
        if ($this->adminAlreadyExists()) {
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Un compte administrateur existe déjà.']);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        if (Schema::hasColumn('users', 'email')) {
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email'];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Le nom est obligatoire.',
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.unique' => 'Ce nom d\'utilisateur existe déjà.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email est invalide.',
            'email.unique' => 'Cet email existe déjà.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        DB::transaction(function () use ($validated) {
            $adminRoleId = $this->getOrCreateAdminRoleId();

            $userData = [];

            if (Schema::hasColumn('users', 'name')) {
                $userData['name'] = $validated['name'];
            }

            if (Schema::hasColumn('users', 'full_name')) {
                $userData['full_name'] = $validated['name'];
            }

            if (Schema::hasColumn('users', 'username')) {
                $userData['username'] = $validated['username'];
            }

            if (Schema::hasColumn('users', 'email')) {
                $userData['email'] = $validated['email'];
            }

            if (Schema::hasColumn('users', 'status')) {
                $userData['status'] = 'active';
            }

            if (Schema::hasColumn('users', 'password')) {
                $userData['password'] = Hash::make($validated['password']);
            }

            if (Schema::hasColumn('users', 'remember_token')) {
                $userData['remember_token'] = null;
            }

            if (Schema::hasColumn('users', 'role_id') && $adminRoleId !== null) {
                $userData['role_id'] = $adminRoleId;
            }

            if (Schema::hasColumn('users', 'created_at')) {
                $userData['created_at'] = now();
            }

            if (Schema::hasColumn('users', 'updated_at')) {
                $userData['updated_at'] = now();
            }

            $userId = DB::table('users')->insertGetId($userData);

            if (
                $adminRoleId !== null &&
                Schema::hasTable('role_user') &&
                Schema::hasColumn('role_user', 'role_id') &&
                Schema::hasColumn('role_user', 'user_id')
            ) {
                DB::table('role_user')->insert([
                    'role_id' => $adminRoleId,
                    'user_id' => $userId,
                ]);
            }
        });

        return redirect()->route('admin.login')->with('status', 'Compte administrateur créé avec succès. Vous pouvez maintenant vous connecter.');
    }

    protected function adminAlreadyExists(): bool
    {
        if (!Schema::hasTable('users')) {
            return false;
        }

        $adminRoleId = $this->findAdminRoleId();

        if ($adminRoleId === null) {
            return false;
        }

        if (Schema::hasColumn('users', 'role_id') && DB::table('users')->where('role_id', $adminRoleId)->exists()) {
            return true;
        }

        if (
            Schema::hasTable('role_user') &&
            Schema::hasColumn('role_user', 'role_id') &&
            DB::table('role_user')->where('role_id', $adminRoleId)->exists()
        ) {
            return true;
        }

        return false;
    }

    protected function findAdminRoleId(): ?int
    {
        if (!Schema::hasTable('roles')) {
            return null;
        }

        $roleId = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', ['admin'])
            ->value('id');

        return $roleId ? (int) $roleId : null;
    }

    protected function getOrCreateAdminRoleId(): ?int
    {
        $existingId = $this->findAdminRoleId();

        if ($existingId !== null) {
            return $existingId;
        }

        if (!Schema::hasTable('roles')) {
            return null;
        }

        $data = ['name' => 'admin'];

        if (Schema::hasColumn('roles', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('roles', 'updated_at')) {
            $data['updated_at'] = now();
        }

        $id = DB::table('roles')->insertGetId($data);

        return $id ? (int) $id : null;
    }
}
