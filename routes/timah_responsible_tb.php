<?php

use Illuminate\Support\Facades\Route;

Route::get('/supervision/tb', function () {
    return view('supervision.responsible-tb');
})->middleware(['auth', 'no.cache'])->name('supervision.tb');

Route::get('/secretariat/dashboard', function () {
    return view('supervision.secretary-general');
})->middleware(['auth', 'no.cache'])->name('secretariat.dashboard');

Route::get('/responsable-enseignement/dashboard', function () {
    return view('supervision.responsible-area', [
        'requiredScope' => 'division',
        'dashboardTitle' => 'TB Responsable type d’enseignement',
    ]);
})->middleware(['auth', 'no.cache'])->name('responsible.division.dashboard');

Route::get('/responsable-departement/dashboard', function () {
    return view('supervision.responsible-area', [
        'requiredScope' => 'department',
        'dashboardTitle' => 'TB Responsable département / filière',
    ]);
})->middleware(['auth', 'no.cache'])->name('responsible.department.dashboard');
