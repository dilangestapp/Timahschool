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
        $isStudent = $user
            && (
                (method_exists($user, 'isStudent') && $user->isStudent())
                || (!method_exists($user, 'isAdmin') || !$user->isAdmin())
                    && (!method_exists($user, 'isTeacher') || !$user->isTeacher())
                    && $user->studentProfile
            );

        if (!$isStudent) {
            abort(403, 'Accès élève refusé.');
        }

        return $next($request);
    }
}
