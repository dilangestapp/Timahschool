<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

        if ($adminPath !== '' && ($request->is($adminPath) || $request->is($adminPath . '/*'))) {
            return Route::has('admin.login') ? route('admin.login') : route('login');
        }

        return route('login');
    }
}
