<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminUserActivityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $roleFilter = trim((string) $request->get('role', ''));
        $eventFilter = trim((string) $request->get('event', ''));

        $roles = Schema::hasTable('roles')
            ? Role::query()->orderByRaw('COALESCE(display_name, name) asc')->get()
            : collect();

        $users = Schema::hasTable('users')
            ? User::query()
                ->with(['roles', 'role', 'studentProfile.schoolClass'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        foreach (['name', 'full_name', 'username', 'email', 'phone', 'status'] as $column) {
                            if (Schema::hasColumn('users', $column)) {
                                $sub->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }
                    });
                })
                ->get()
                ->filter(function ($user) use ($roleFilter) {
                    return $roleFilter === '' || (method_exists($user, 'hasRole') && $user->hasRole($roleFilter));
                })
                ->sortByDesc(function ($user) {
                    return optional($user->last_login_at)->timestamp ?? 0;
                })
                ->values()
            : collect();

        $activities = Schema::hasTable('user_login_activities')
            ? UserLoginActivity::query()
                ->with(['user.roles', 'user.role', 'user.studentProfile.schoolClass'])
                ->when($eventFilter !== '', fn ($query) => $query->where('event', $eventFilter))
                ->when($search !== '', function ($query) use ($search) {
                    $query->whereHas('user', function ($sub) use ($search) {
                        foreach (['name', 'full_name', 'username', 'email', 'phone'] as $column) {
                            if (Schema::hasColumn('users', $column)) {
                                $sub->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }
                    });
                })
                ->latest('occurred_at')
                ->limit(150)
                ->get()
            : collect();

        $connectedUsers = $users->filter(function ($user) {
            if (!$user->last_login_at) {
                return false;
            }

            return $user->last_login_at->greaterThanOrEqualTo(now()->subMinutes(30));
        })->values();

        return view('admin.users.activity', compact(
            'users',
            'roles',
            'activities',
            'connectedUsers',
            'search',
            'roleFilter',
            'eventFilter'
        ));
    }
}
