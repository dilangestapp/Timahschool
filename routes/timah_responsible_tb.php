<?php

use Illuminate\Support\Facades\Route;

Route::get('/supervision/tb', function () {
    return view('supervision.responsible-tb');
})->middleware(['auth', 'no.cache'])->name('supervision.tb');

Route::get('/secretariat/dashboard', function () {
    return view('supervision.secretary-general');
})->middleware(['auth', 'no.cache'])->name('secretariat.dashboard');
