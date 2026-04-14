<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminLoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            $currentUser = Auth::user();

            if ($currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($currentUser && method_exists($currentUser, 'isTeacher') && $currentUser->isTeacher()) {
                return redirect()->route('teacher.dashboard')
                    ->with('warning', 'Vous êtes déjà connecté en tant qu’enseignant.');
            }

            return redirect()->route('student.dashboard')
                ->with('warning', 'Vous êtes déjà connecté avec un compte non administrateur.');
        }

        return view('admin.auth.login', [
            'adminPath' => trim((string) config('timahschool.admin_path', 'backoffice-access'), '/'),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Le nom d\'utilisateur admin est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (Auth::check()) {
            $currentUser = Auth::user();

            if ($currentUser && method_exists($currentUser, 'isAdmin') && $currentUser->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($currentUser && method_exists($currentUser, 'isTeacher') && $currentUser->isTeacher()) {
                return redirect()->route('teacher.dashboard')
                    ->with('warning', 'Déconnectez-vous de votre session enseignant avant une connexion admin.');
            }

            return redirect()->route('student.dashboard')
                ->with('warning', 'Déconnectez-vous de votre session actuelle avant une connexion admin.');
        }

        $user = $this->findAdminUser((string) $request->input('username'));

        if (!$user) {
            return back()
                ->withErrors(['username' => 'Compte administrateur introuvable.'])
                ->onlyInput('username');
        }

        if (!Hash::check((string) $request->input('password'), (string) $user->password)) {
            return back()
                ->withErrors(['username' => 'Identifiants administrateur invalides.'])
                ->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        try {
            $table = $user->getTable();

            if (Schema::hasColumn($table, 'last_login_at')) {
                $updates = [
                    'last_login_at' => now(),
                ];

                if (Schema::hasColumn($table, 'last_login_ip')) {
                    $updates['last_login_ip'] = $request->ip();
                }

                $user->forceFill($updates)->save();
            }
        } catch (\Throwable $e) {
            // Ne pas bloquer la connexion admin si ces colonnes n'existent pas.
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $this->clearCurrentSession($request);

        return redirect()->route('admin.login');
    }

    protected function findAdminUser(string $identifier): ?User
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $userModel = new User();
        $table = $userModel->getTable();

        $loginColumns = [];

        foreach (['username', 'email', 'name'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $loginColumns[] = $column;
            }
        }

        if (empty($loginColumns)) {
            return null;
        }

        $query = User::query()->with(['role', 'roles']);

        $query->where(function ($subQuery) use ($loginColumns, $identifier) {
            foreach ($loginColumns as $index => $column) {
                if ($index === 0) {
                    $subQuery->where($column, $identifier);
                } else {
                    $subQuery->orWhere($column, $identifier);
                }
            }
        });

        $user = $query->first();

        if (!$user) {
            return null;
        }

        if (!method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            return null;
        }

        return $user;
    }

    protected function clearCurrentSession(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
