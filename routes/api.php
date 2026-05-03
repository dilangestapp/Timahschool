<?php

use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('mobile')->name('api.mobile.')->group(function () {
    Route::post('/register', [MobileAuthController::class, 'register'])->name('register');
    Route::post('/login', [MobileAuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me'])->name('me');
        Route::get('/subscription', [MobileAuthController::class, 'subscription'])->name('subscription');
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
