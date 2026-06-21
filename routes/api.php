<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileContentController;
use App\Http\Controllers\Api\MobileCourseController;
use App\Http\Controllers\Api\MobileLearningController;
use App\Http\Controllers\Api\MobileTdController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/__railway/mobile-setup/timah2026', function () {
    if (!app()->environment('production')) {
        abort(404);
    }

    $steps = [];

    foreach (['migrate --force', 'db:seed --force'] as $command) {
        try {
            Artisan::call($command);
            $steps[$command] = trim(Artisan::output()) ?: 'OK';
        } catch (Throwable $e) {
            $steps[$command] = 'ERREUR : ' . $e->getMessage();
        }
    }

    return response()->json([
        'status' => 'done',
        'database' => config('database.default'),
        'host' => config('database.connections.mysql.host'),
        'database_name' => config('database.connections.mysql.database'),
        'steps' => $steps,
    ]);
});

Route::prefix('mobile')->name('api.mobile.')->group(function () {
    Route::post('/register', [MobileAuthController::class, 'register'])->name('register');
    Route::post('/login', [MobileAuthController::class, 'login'])->name('login');

    Route::get('/me', [MobileAuthController::class, 'me'])->name('me');
    Route::get('/subscription', [MobileAuthController::class, 'subscription'])->name('subscription');
    Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');

    Route::get('/home', [MobileContentController::class, 'home'])->name('home');
    Route::get('/program', [MobileContentController::class, 'program'])->name('program');
    Route::get('/board', [MobileContentController::class, 'board'])->name('board');
    Route::get('/reports', [MobileContentController::class, 'reports'])->name('reports');
    Route::get('/evaluations', [MobileContentController::class, 'evaluations'])->name('evaluations');

    Route::get('/courses', [MobileCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{id}', [MobileCourseController::class, 'show'])->whereNumber('id')->name('courses.show');
    Route::post('/courses/{id}/complete', [MobileCourseController::class, 'complete'])->whereNumber('id')->name('courses.complete');
    Route::get('/courses/{id}/document', [MobileCourseController::class, 'document'])->whereNumber('id')->name('courses.document');
    Route::get('/courses/{id}/download', [MobileCourseController::class, 'download'])->whereNumber('id')->name('courses.download');

    Route::get('/program/{id}', [MobileLearningController::class, 'programDetail'])->whereNumber('id')->name('program.detail');
    Route::post('/program/{id}/complete', [MobileLearningController::class, 'completeProgram'])->whereNumber('id')->name('program.complete');
    Route::get('/board/{id}', [MobileLearningController::class, 'boardDetail'])->whereNumber('id')->name('board.detail');
    Route::get('/evaluations/{id}', [MobileLearningController::class, 'evaluationDetail'])->whereNumber('id')->name('evaluations.detail');
    Route::get('/reports/{id}', [MobileLearningController::class, 'reportDetail'])->whereNumber('id')->name('reports.detail');

    Route::get('/td', [MobileTdController::class, 'index'])->name('td.index');
    Route::get('/td/{id}', [MobileTdController::class, 'show'])->whereNumber('id')->name('td.show');
    Route::get('/td/{id}/document', [MobileTdController::class, 'document'])->whereNumber('id')->name('td.document');
    Route::get('/td/{id}/correction-document', [MobileTdController::class, 'correctionDocument'])->whereNumber('id')->name('td.correction_document');
    Route::post('/td/{id}/complete', [MobileTdController::class, 'complete'])->whereNumber('id')->name('td.complete');

    Route::get('/quizzes', [MobileLearningController::class, 'quizzes'])->name('quizzes.index');
    Route::get('/quizzes/{id}', [MobileLearningController::class, 'quizDetail'])->whereNumber('id')->name('quizzes.show');
    Route::post('/quizzes/{id}/submit', [MobileLearningController::class, 'submitQuiz'])->whereNumber('id')->name('quizzes.submit');
    Route::get('/quizzes-history', [MobileLearningController::class, 'quizHistory'])->name('quizzes.history');
    Route::get('/notifications', [MobileLearningController::class, 'notifications'])->name('notifications');
});
