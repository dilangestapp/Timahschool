<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        if ($user && $this->isSecretaryAllowed($request, (int) $user->id)) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->withErrors(['username' => 'Ce compte n\'est pas autorisé sur le portail admin.']);
    }

    private function isSecretaryAllowed(Request $request, int $userId): bool
    {
        if (!Schema::hasTable('pedagogical_responsibilities')) {
            return false;
        }

        $routeName = (string) optional($request->route())->getName();

        $allowedRoutes = [
            'admin.dashboard',
            'admin.organization.index',
            'admin.organization.divisions.store',
            'admin.organization.departments.store',
            'admin.organization.responsibilities.store',
            'admin.organization.notes.store',
            'admin.organization.responsibilities.toggle',
            'admin.organization.notes.status',
            'admin.users.index',
            'admin.users.activity',
            'admin.users.store',
            'admin.users.update',
            'admin.users.delete',
            'admin.teachers.index',
            'admin.teachers.store',
            'admin.teachers.toggle',
            'admin.assignments.index',
            'admin.assignments.store',
            'admin.assignments.toggle',
            'admin.assignments.delete',
            'admin.classes.index',
            'admin.classes.store',
            'admin.classes.update',
            'admin.classes.delete',
            'admin.subjects.index',
            'admin.subjects.store',
            'admin.subjects.update',
            'admin.subjects.delete',
            'admin.courses.index',
            'admin.courses.publish',
            'admin.courses.archive',
            'admin.courses.update',
            'admin.courses.delete',
            'admin.td.index',
            'admin.td.create',
            'admin.td.store',
            'admin.td.edit',
            'admin.td.update',
            'admin.td.publish',
            'admin.td.archive',
            'admin.td.delete',
            'admin.td.document',
            'admin.td.correction_document',
            'admin.td.pdf',
            'admin.td.correction_pdf',
            'admin.learning-program.index',
            'admin.learning-program.store',
            'admin.learning-program.update',
            'admin.learning-program.delete',
            'admin.digital-board.index',
            'admin.digital-board.store',
            'admin.digital-board.publish',
            'admin.digital-board.archive',
            'admin.digital-board.delete',
            'admin.logout',
            'admin.logout.history',
        ];

        if (!in_array($routeName, $allowedRoutes, true)) {
            return false;
        }

        return DB::table('pedagogical_responsibilities')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where('scope_type', 'platform')
            ->where(function ($query) {
                $query->where('role_title', 'like', '%Secrétaire général%')
                    ->orWhere('role_title', 'like', '%Coordinateur général%');
            })
            ->exists();
    }
}
