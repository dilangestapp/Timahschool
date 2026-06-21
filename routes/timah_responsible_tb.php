<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/supervision/tb', function () {
    if (!auth()->check() || !Schema::hasTable('pedagogical_responsibilities')) {
        return view('supervision.responsible-tb');
    }

    $responsibilities = DB::table('pedagogical_responsibilities')
        ->where('user_id', auth()->id())
        ->where('is_active', true)
        ->orderByDesc('id')
        ->get();

    $hasTitle = fn (string $needle) => $responsibilities->contains(fn ($item) => str_contains((string) ($item->role_title ?? ''), $needle));
    $hasScope = fn (string $scope) => $responsibilities->contains(fn ($item) => ($item->scope_type ?? '') === $scope);

    if ($hasTitle('Superviseur pédagogique')) {
        return redirect()->route('supervisor.pedagogical.dashboard');
    }

    if ($hasTitle('Référent pédagogique')) {
        return redirect()->route('referent.pedagogical.dashboard');
    }

    if ($hasScope('department')) {
        return redirect()->route('responsible.department.dashboard');
    }

    if ($hasScope('division')) {
        return redirect()->route('responsible.division.dashboard');
    }

    if ($hasTitle('Secrétaire général') || $hasTitle('Coordinateur général')) {
        return redirect()->route('secretariat.dashboard');
    }

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
    return view('supervision.department-dashboard');
})->middleware(['auth', 'no.cache'])->name('responsible.department.dashboard');

Route::get('/referent-pedagogique/dashboard', function () {
    return view('supervision.pedagogical-referent');
})->middleware(['auth', 'no.cache'])->name('referent.pedagogical.dashboard');

Route::get('/superviseur-pedagogique/dashboard', function () {
    return view('supervision.pedagogical-supervisor');
})->middleware(['auth', 'no.cache'])->name('supervisor.pedagogical.dashboard');

Route::get('/responsabilites/dashboard', function () {
    return redirect()->route('supervision.tb');
})->middleware(['auth', 'no.cache'])->name('responsibilities.dashboard');
