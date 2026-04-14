<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (!Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();

            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin() && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user && method_exists($user, 'isTeacher') && $user->isTeacher() && Route::has('teacher.dashboard')) {
                return redirect()->route('teacher.dashboard');
            }

            if ($user && method_exists($user, 'isStudent') && $user->isStudent() && Route::has('student.dashboard')) {
                return redirect()->route('student.dashboard');
            }

            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
