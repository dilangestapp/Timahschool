<?php

use App\Http\Controllers\Technical\ManagementController;
use App\Http\Middleware\EnsureTechnicalSupervisor;
use Illuminate\Support\Facades\Route;

Route::prefix('responsable-technique')
    ->name('technical.')
    ->middleware(['auth', 'no.cache', EnsureTechnicalSupervisor::class])
    ->group(function () {
        Route::post('/classes', [ManagementController::class, 'storeClass'])->name('classes.store');
        Route::post('/classes/{class}/update', [ManagementController::class, 'updateClass'])->name('classes.update');

        Route::post('/matieres', [ManagementController::class, 'storeSubject'])->name('subjects.store');
        Route::post('/matieres/{subject}/update', [ManagementController::class, 'updateSubject'])->name('subjects.update');

        Route::post('/enseignants', [ManagementController::class, 'storeTeacher'])->name('teachers.store');
        Route::post('/enseignants/{teacher}/toggle', [ManagementController::class, 'toggleTeacher'])->name('teachers.toggle');

        Route::post('/affectations', [ManagementController::class, 'storeAssignment'])->name('assignments.store');
        Route::post('/affectations/{assignment}/toggle', [ManagementController::class, 'toggleAssignment'])->name('assignments.toggle');
        Route::post('/affectations/{assignment}/delete', [ManagementController::class, 'deleteAssignment'])->name('assignments.delete');

        Route::post('/cours/{course}/publish', [ManagementController::class, 'publishCourse'])->name('courses.publish');
        Route::post('/cours/{course}/archive', [ManagementController::class, 'archiveCourse'])->name('courses.archive');

        Route::post('/td/{td}/publish', [ManagementController::class, 'publishTd'])->name('td.publish');
        Route::post('/td/{td}/archive', [ManagementController::class, 'archiveTd'])->name('td.archive');
    });
