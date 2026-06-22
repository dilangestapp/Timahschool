<?php

use App\Http\Controllers\Teacher\AnnualProgramController;
use App\Http\Controllers\Teacher\TdCorrectionDashboardController;
use App\Http\Middleware\EnsureTeacher;
use Illuminate\Support\Facades\Route;

Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['auth', 'no.cache', EnsureTeacher::class])
    ->group(function () {
        Route::get('/programme-annuel', [AnnualProgramController::class, 'index'])->name('annual-programs.index');
        Route::post('/programme-annuel', [AnnualProgramController::class, 'store'])->name('annual-programs.store');
        Route::post('/programme-annuel/{program}/publish', [AnnualProgramController::class, 'publish'])->name('annual-programs.publish');
        Route::post('/programme-annuel/{program}/archive', [AnnualProgramController::class, 'archive'])->name('annual-programs.archive');
        Route::post('/programme-annuel/{program}/delete', [AnnualProgramController::class, 'delete'])->name('annual-programs.delete');
        Route::post('/programme-annuel/{program}/chapitres', [AnnualProgramController::class, 'storeItem'])->name('annual-programs.items.store');
        Route::post('/programme-annuel/chapitres/{item}/complete', [AnnualProgramController::class, 'completeItem'])->name('annual-programs.items.complete');
        Route::post('/programme-annuel/chapitres/{item}/delete', [AnnualProgramController::class, 'deleteItem'])->name('annual-programs.items.delete');

        Route::get('/td-corrections', [TdCorrectionDashboardController::class, 'index'])->name('td.corrections.index');
    });
