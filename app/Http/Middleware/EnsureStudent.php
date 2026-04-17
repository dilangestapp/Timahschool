<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        $isTeacher = $user && method_exists($user, 'isTeacher') && $user->isTeacher();
        $hasStudentRole = $user && method_exists($user, 'isStudent') && $user->isStudent();
        $hasStudentProfile = (bool) optional($user)->studentProfile;

        $isStudent = $user && ($hasStudentRole || $hasStudentProfile);

        if (!$isStudent) {
            if ($isTeacher && !$hasStudentProfile && Route::has('teacher.dashboard')) {
                return redirect()->route('teacher.dashboard')
                    ->with('warning', 'Vous êtes connecté en profil enseignant. Redirection vers votre espace enseignant.');
            }

            if ($isAdmin && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Votre compte est administrateur. Utilisez le backoffice pour cette action.');
            }

            return redirect()->route('home')
                ->with('error', 'Accès élève indisponible pour ce compte.');
            abort(403, 'Accès élève refusé.');
        }

        return $next($request);
    }
}
