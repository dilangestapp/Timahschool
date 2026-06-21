<?php

use Illuminate\Support\Facades\Route;

Route::get('/teacher/courses/{course}/office', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'editor'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.courses.office');

Route::post('/teacher/courses/{course}/convert-content', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'convertContent'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.courses.convert');

Route::post('/teacher/messages/send', [\App\Http\Controllers\Teacher\MessageController::class, 'send'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.messages.send');

Route::post('/teacher/messages/broadcast', [\App\Http\Controllers\Teacher\MessageController::class, 'broadcast'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.messages.broadcast');

Route::post('/teacher/messages/{message}/delete', [\App\Http\Controllers\Teacher\MessageController::class, 'destroy'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.messages.delete');

Route::get('/course-office/{course}/document/{key}', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'file'])
    ->name('onlyoffice.courses.file');

Route::post('/course-office/{course}/save/{key}', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'callback'])
    ->name('onlyoffice.courses.callback');

if (file_exists(base_path('routes/timah_supervision.php'))) {
    require base_path('routes/timah_supervision.php');
}

if (file_exists(base_path('routes/timah_responsible_tb.php'))) {
    require base_path('routes/timah_responsible_tb.php');
}

if (file_exists(base_path('routes/timah_responsible_actions.php'))) {
    require base_path('routes/timah_responsible_actions.php');
}

if (file_exists(base_path('routes/timah_department_management.php'))) {
    require base_path('routes/timah_department_management.php');
}

if (file_exists(base_path('routes/timah_teacher_messages_fix.php'))) {
    require base_path('routes/timah_teacher_messages_fix.php');
}
