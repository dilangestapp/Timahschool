<?php

namespace App\Providers;

use App\Http\Controllers\Admin\AdminTdImportController;
use App\Http\Controllers\Student\DiagnosticController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureStudent;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $forceHttps = filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL)
            || $this->app->environment('production');

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        $adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

        Route::middleware(['web', 'auth', 'no.cache', EnsureAdmin::class])
            ->prefix($adminPath)
            ->name('admin.')
            ->group(function () {
                Route::get('/td/import', [AdminTdImportController::class, 'create'])->name('td.import');
                Route::post('/td/import/analyze', [AdminTdImportController::class, 'analyze'])->name('td.import.analyze');
                Route::post('/td/import/store', [AdminTdImportController::class, 'store'])->name('td.import.store');
            });

        Route::middleware(['web', 'auth', 'no.cache', EnsureStudent::class])
            ->prefix('student')
            ->name('student.')
            ->group(function () {
                Route::get('/diagnostic', [DiagnosticController::class, 'index'])->name('diagnostic.index');
                Route::post('/diagnostic/answer', [DiagnosticController::class, 'answer'])->name('diagnostic.answer');
                Route::get('/diagnostic/result', [DiagnosticController::class, 'result'])->name('diagnostic.result');
            });
    }
}
