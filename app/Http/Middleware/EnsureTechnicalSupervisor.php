<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureTechnicalSupervisor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        $isTechnicalSupervisor = $user && method_exists($user, 'isTechnicalSupervisor') && $user->isTechnicalSupervisor();

        if ($isAdmin || $isTechnicalSupervisor) {
            return $next($request);
        }

        if ($user && method_exists($user, 'isTeacher') && $user->isTeacher() && Route::has('teacher.dashboard')) {
            return redirect()->route('teacher.dashboard')
                ->with('warning', 'Cet espace est réservé au responsable de l’enseignement technique.');
        }

        if ($user && method_exists($user, 'isParent') && $user->isParent() && Route::has('parent.dashboard')) {
            return redirect()->route('parent.dashboard')
                ->with('warning', 'Cet espace est réservé au responsable de l’enseignement technique.');
        }

        if ($user && method_exists($user, 'isStudent') && $user->isStudent() && Route::has('student.dashboard')) {
            return redirect()->route('student.dashboard')
                ->with('warning', 'Cet espace est réservé au responsable de l’enseignement technique.');
        }

        return redirect()->route('home')
            ->with('error', 'Accès responsable technique indisponible pour ce compte.');
    }
}
