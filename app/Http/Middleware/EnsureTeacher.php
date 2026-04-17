<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isTeacher = $user && method_exists($user, 'isTeacher') && $user->isTeacher();
        $isStudent = $user && method_exists($user, 'isStudent') && $user->isStudent();
        $hasStudentProfile = (bool) optional($user)->studentProfile;
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        if (!$isTeacher) {
            if (($isStudent || $hasStudentProfile) && Route::has('student.dashboard')) {
                return redirect()->route('student.dashboard')
                    ->with('warning', 'Vous êtes connecté en profil élève. Redirection vers votre espace élève.');
            }

            if ($isAdmin && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Votre compte est administrateur. Utilisez le backoffice pour cette action.');
            }

            return redirect()->route('home')
                ->with('error', 'Accès enseignant indisponible pour ce compte.');
        }

        return $next($request);
    }
}
