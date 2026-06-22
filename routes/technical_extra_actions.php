<?php

use App\Http\Controllers\Technical\ManagementController;
use App\Http\Middleware\EnsureTechnicalSupervisor;
use Illuminate\Support\Facades\Route;

Route::prefix('responsable-technique')
    ->name('technical.')
    ->middleware(['auth', 'no.cache', EnsureTechnicalSupervisor::class])
    ->group(function () {
        Route::post('/classes/{class}/delete', [ManagementController::class, 'deleteClass'])->name('classes.delete');
        Route::post('/matieres/{subject}/delete', [ManagementController::class, 'deleteSubject'])->name('subjects.delete');
        Route::post('/enseignants/{teacher}/delete', [ManagementController::class, 'deleteTeacher'])->name('teachers.delete');
        Route::post('/cours/{course}/delete', [ManagementController::class, 'deleteCourse'])->name('courses.delete');
        Route::post('/td/{td}/delete', [ManagementController::class, 'deleteTd'])->name('td.delete');
    });
