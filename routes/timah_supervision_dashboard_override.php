<?php

use Illuminate\Support\Facades\Route;

Route::get('/supervision/dashboard', function () {
    return view('supervision.responsible-tb');
})->middleware(['auth', 'no.cache']);
