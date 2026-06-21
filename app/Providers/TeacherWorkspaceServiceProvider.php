<?php

namespace App\Providers;

use App\Http\Controllers\Teacher\WeeklyProgramController;
use App\Http\Middleware\EnsureTeacher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TeacherWorkspaceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', 'auth', 'no.cache', EnsureTeacher::class])
            ->prefix('teacher')
            ->name('teacher.')
            ->group(function () {
                Route::get('/weekly-program', [WeeklyProgramController::class, 'index'])->name('weekly-program.index');
                Route::post('/weekly-program', [WeeklyProgramController::class, 'store'])->name('weekly-program.store');
                Route::post('/weekly-program/{program}/update', [WeeklyProgramController::class, 'update'])->name('weekly-program.update');
                Route::post('/weekly-program/{program}/status', [WeeklyProgramController::class, 'status'])->name('weekly-program.status');
            });
    }
}
