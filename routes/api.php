<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileContentController;
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
});
