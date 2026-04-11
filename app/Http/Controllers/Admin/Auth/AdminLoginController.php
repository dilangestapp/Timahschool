<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
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
            'access_code' => 'required|string',
        ], [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'access_code.required' => 'Le code d\'accès admin est obligatoire.',
        ]);

        $expectedCode = (string) config('timahschool.admin_access_code', '');

        if ($expectedCode === '' || !hash_equals($expectedCode, (string) $request->access_code)) {
            return back()
                ->withErrors(['access_code' => 'Code d\'accès admin invalide.'])
                ->onlyInput('username');
        }

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Identifiants administrateur invalides.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['username' => 'Ce compte n\'est pas autorisé sur le portail admin.'])
                ->onlyInput('username');
        }

        try {
            if (Schema::hasColumn($user->getTable(), 'last_login_at')) {
                $updates = [
                    'last_login_at' => now(),
                ];

                if (Schema::hasColumn($user->getTable(), 'last_login_ip')) {
                    $updates['last_login_ip'] = $request->ip();
                }

                $user->forceFill($updates)->save();
            }
        } catch (\Throwable $e) {
            // Ne pas bloquer la connexion
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
