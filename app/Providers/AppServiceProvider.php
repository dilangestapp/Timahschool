<?php

namespace App\Providers;

use App\Http\Controllers\Admin\AdminDatabaseBackupController;
use App\Http\Controllers\Admin\AdminPedagogicalBankController;
use App\Http\Controllers\Admin\AdminTdImportController;
use App\Http\Controllers\Internal\DirectTdImportController;
use App\Http\Controllers\Student\DiagnosticController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureStudent;
use App\Http\Middleware\InjectAdminBankShortcut;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $forceHttps = filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL)
            || $this->app->environment('production');

        if ($forceHttps) {
            URL::forceScheme('https');
        }

        $adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

        Route::middleware(['web', 'auth', 'no.cache', EnsureAdmin::class, InjectAdminBankShortcut::class])
            ->prefix($adminPath)
            ->name('admin.')
            ->group(function () {
                Route::get('/database-backup', [AdminDatabaseBackupController::class, 'index'])->name('database-backup.index');
                Route::get('/database-backup/download', [AdminDatabaseBackupController::class, 'download'])->name('database-backup.download');

                Route::get('/td/import', [AdminTdImportController::class, 'create'])->name('td.import');
                Route::post('/td/import/analyze', [AdminTdImportController::class, 'analyze'])->name('td.import.analyze');
                Route::post('/td/import/store', [AdminTdImportController::class, 'store'])->name('td.import.store');

                Route::get('/pedagogical-bank', [AdminPedagogicalBankController::class, 'index'])->name('pedagogical-bank.index');
                Route::post('/pedagogical-bank', [AdminPedagogicalBankController::class, 'store'])->name('pedagogical-bank.store');
                Route::post('/pedagogical-bank/{item}/update', [AdminPedagogicalBankController::class, 'update'])->name('pedagogical-bank.update');
                Route::post('/pedagogical-bank/{item}/schedule', [AdminPedagogicalBankController::class, 'schedule'])->name('pedagogical-bank.schedule');
                Route::post('/pedagogical-bank/{item}/archive', [AdminPedagogicalBankController::class, 'archive'])->name('pedagogical-bank.archive');
                Route::post('/pedagogical-bank/{item}/restore', [AdminPedagogicalBankController::class, 'restore'])->name('pedagogical-bank.restore');
            });

        Route::middleware(['web', 'auth', 'no.cache', EnsureStudent::class])
            ->prefix('student')
            ->name('student.')
            ->group(function () {
                Route::get('/diagnostic', [DiagnosticController::class, 'index'])->name('diagnostic.index');
                Route::post('/diagnostic/answer', [DiagnosticController::class, 'answer'])->name('diagnostic.answer');
                Route::get('/diagnostic/result', [DiagnosticController::class, 'result'])->name('diagnostic.result');
            });

        Route::middleware(['api'])
            ->prefix('__timah/internal')
            ->name('timah.internal.')
            ->group(function () {
                Route::post('/td/import', [DirectTdImportController::class, 'store'])->name('td.import');
            });
    }
}
