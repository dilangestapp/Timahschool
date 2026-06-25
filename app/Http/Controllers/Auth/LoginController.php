<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\UserActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if (method_exists($user, 'isTechnicalSupervisor') && $user->isTechnicalSupervisor()) {
                return redirect()->route('technical.dashboard');
            }

            if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
                return redirect()->route('teacher.dashboard');
            }

            if (method_exists($user, 'isParent') && $user->isParent()) {
                return redirect()->route('parent.dashboard');
            }

            return redirect()->route('student.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $user = $this->findUserForLogin($identifier);

        if (!$user || !Hash::check($password, (string) $user->password)) {
            return back()->withErrors(['username' => 'Identifiants invalides.'])->onlyInput('username');
        }

        if (($user->status ?? 'active') !== 'active') {
            return back()->withErrors(['username' => 'Ce compte est bloqué ou inactif. Contactez l’administration.'])->onlyInput('username');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->route('admin.login')->withErrors([
                'username' => 'Le compte administrateur doit se connecter via le portail admin sécurisé.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        try {
            if ($user && Schema::hasColumn($user->getTable(), 'last_login_at')) {
                $updates = ['last_login_at' => now()];
                if (Schema::hasColumn($user->getTable(), 'last_login_ip')) {
                    $updates['last_login_ip'] = $request->ip();
                }
                $user->forceFill($updates)->save();
            }
        } catch (\Throwable $e) {
            // Ne pas bloquer la connexion.
        }

        UserActivityRecorder::record($user, $request, 'login', 'web');

        if ($user && method_exists($user, 'isTechnicalSupervisor') && $user->isTechnicalSupervisor()) {
            return redirect()->route('technical.dashboard');
        }

        if ($user && method_exists($user, 'isTeacher') && $user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        if ($user && method_exists($user, 'isParent') && $user->isParent()) {
            return redirect()->route('parent.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        UserActivityRecorder::record($user, $request, 'logout', 'web');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function findUserForLogin(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $table = (new User())->getTable();
        $columns = Schema::getColumnListing($table);
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $identifier);

        return User::query()
            ->with(['role', 'roles', 'studentProfile', 'parentProfile'])
            ->where(function ($query) use ($columns, $identifier, $normalizedPhone) {
                foreach (['username', 'email', 'name', 'full_name'] as $column) {
                    if (in_array($column, $columns, true)) {
                        $query->orWhere($column, $identifier);
                    }
                }

                if ($normalizedPhone !== '' && in_array('phone', $columns, true)) {
                    $query->orWhere('phone', $normalizedPhone)
                        ->orWhere('phone', $identifier);
                }
            })
            ->first();
    }
}
