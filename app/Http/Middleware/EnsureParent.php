<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureParent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isParent = $user && method_exists($user, 'isParent') && $user->isParent();
        $isTeacher = $user && method_exists($user, 'isTeacher') && $user->isTeacher();
        $isStudent = $user && method_exists($user, 'isStudent') && $user->isStudent();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        if (!$isParent) {
            if ($isTeacher && Route::has('teacher.dashboard')) {
                return redirect()->route('teacher.dashboard');
            }
            if ($isStudent && Route::has('student.dashboard')) {
                return redirect()->route('student.dashboard');
            }
            if ($isAdmin && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home')->with('error', 'Accès parent indisponible pour ce compte.');
        }

        return $next($request);
    }
}
