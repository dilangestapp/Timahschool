<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
                return redirect()->route('teacher.dashboard');
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

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'Identifiants invalides.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        try {
            if ($user && Schema::hasColumn($user->getTable(), 'last_login_at')) {
                $updates = ['last_login_at' => now()];

                if (Schema::hasColumn($user->getTable(), 'last_login_ip')) {
                    $updates['last_login_ip'] = $request->ip();
                }

                $user->forceFill($updates)->save();
            }
        } catch (\Throwable $e) {
            // Ne pas bloquer la connexion
        }

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'username' => 'Le compte administrateur doit se connecter via le portail admin sécurisé.',
            ]);
        }

        if ($user && method_exists($user, 'isTeacher') && $user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
