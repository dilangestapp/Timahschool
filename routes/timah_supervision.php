<?php

use App\Http\Controllers\Admin\AdminPedagogicalSupervisionController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

$adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

Route::prefix($adminPath)
    ->name('admin.')
    ->middleware(['auth', 'no.cache', EnsureAdmin::class])
    ->group(function () {
        Route::get('/organization', [AdminPedagogicalSupervisionController::class, 'index'])->name('organization.index');
        Route::post('/organization/divisions', [AdminPedagogicalSupervisionController::class, 'storeDivision'])->name('organization.divisions.store');
        Route::post('/organization/departments', [AdminPedagogicalSupervisionController::class, 'storeDepartment'])->name('organization.departments.store');
        Route::post('/organization/responsibilities', [AdminPedagogicalSupervisionController::class, 'storeResponsibility'])->name('organization.responsibilities.store');
        Route::post('/organization/notes', [AdminPedagogicalSupervisionController::class, 'storeNote'])->name('organization.notes.store');
        Route::post('/organization/responsibilities/{responsibility}/toggle', [AdminPedagogicalSupervisionController::class, 'toggleResponsibility'])->name('organization.responsibilities.toggle');
        Route::post('/organization/notes/{note}/status', [AdminPedagogicalSupervisionController::class, 'updateNoteStatus'])->name('organization.notes.status');
    });
