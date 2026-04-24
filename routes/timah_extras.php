<?php

use App\Http\Controllers\Admin\AdminStudentAccountController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

$adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

Route::prefix($adminPath)
    ->name('admin.')
    ->middleware(['auth', 'no.cache', EnsureAdmin::class])
    ->group(function () {
        Route::post('/users/{user}/student-class', [AdminStudentAccountController::class, 'updateClass'])
            ->name('users.student_class.update');

        Route::post('/users/{user}/student-subscription', [AdminStudentAccountController::class, 'updateSubscription'])
            ->name('users.student_subscription.update');
    });
