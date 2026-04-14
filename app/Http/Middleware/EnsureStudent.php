<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $isStudent = $user && method_exists($user, 'isStudent') && $user->isStudent();

        if ($isAdmin || $isTeacher || !$isStudent) {
            abort(403, 'Accès élève refusé.');
        }

        return $next($request);
    }
}
