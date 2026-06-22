<?php

use App\Http\Controllers\BrandedDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/student/courses/{course}', [BrandedDocumentController::class, 'course'])
    ->middleware(['auth', 'no.cache'])
    ->name('student.courses.direct-official');
