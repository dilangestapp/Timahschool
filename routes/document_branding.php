<?php

use App\Http\Controllers\BrandedDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'no.cache'])->prefix('documents-officiels')->name('documents.')->group(function () {
    Route::get('/cours/{course}', [BrandedDocumentController::class, 'course'])->name('course.official');
    Route::get('/cours/{course}/fichier', [BrandedDocumentController::class, 'courseEmbed'])->name('course.embed');
    Route::get('/cours/{course}/telecharger', [BrandedDocumentController::class, 'courseDownload'])->name('course.download');

    Route::get('/td/{td}', [BrandedDocumentController::class, 'td'])->name('td.official');
    Route::get('/td/{td}/fichier', [BrandedDocumentController::class, 'tdEmbed'])->name('td.embed');
    Route::get('/td/{td}/corrige', [BrandedDocumentController::class, 'tdCorrection'])->name('td.correction.official');
    Route::get('/td/{td}/corrige/fichier', [BrandedDocumentController::class, 'tdCorrectionEmbed'])->name('td.correction.embed');
});
