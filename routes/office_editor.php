<?php

use Illuminate\Support\Facades\Route;

Route::get('/teacher/courses/{course}/office', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'editor'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.courses.office');

Route::get('/course-office/{course}/document/{key}', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'file'])
    ->name('onlyoffice.courses.file');

Route::post('/course-office/{course}/save/{key}', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'callback'])
    ->name('onlyoffice.courses.callback');
