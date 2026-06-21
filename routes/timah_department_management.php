<?php

use App\Http\Controllers\Supervision\DepartmentManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/responsable-departement/classes', [DepartmentManagementController::class, 'classes'])
    ->middleware(['auth', 'no.cache'])
    ->name('department.classes.index');

Route::post('/responsable-departement/classes', [DepartmentManagementController::class, 'storeClass'])
    ->middleware(['auth', 'no.cache'])
    ->name('department.classes.store');

Route::post('/responsable-departement/classes/{class}/update', [DepartmentManagementController::class, 'updateClass'])
    ->middleware(['auth', 'no.cache'])
    ->name('department.classes.update');

Route::get('/responsable-departement/matieres', [DepartmentManagementController::class, 'subjects'])
    ->middleware(['auth', 'no.cache'])
    ->name('department.subjects.index');

Route::post('/responsable-departement/matieres', [DepartmentManagementController::class, 'storeSubject'])
    ->middleware(['auth', 'no.cache'])
    ->name('department.subjects.store');

Route::post('/responsable-departement/matieres/{subject}/update', [DepartmentManagementController::class, 'updateSubject'])
    ->middleware(['auth', 'no.cache'])
    ->name('department.subjects.update');
