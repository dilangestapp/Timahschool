<?php

use App\Http\Controllers\Admin\AdminStudentAccountController;
use App\Http\Controllers\Teacher\TdQuickEditorController;
use App\Http\Controllers\Teacher\TdSetController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

Route::prefix($adminPath)
    ->name('admin.')
    ->middleware(['auth', 'no.cache', EnsureAdmin::class])
    ->group(function () {
        Route::post('/users/{user}/student-class', [AdminStudentAccountController::class, 'updateClass'])
            ->name('users.student_class.update');

        Route::post('/users/{user}/student-subscription', [AdminStudentAccountController::class, 'updateSubscription'])
            ->name('users.student_subscription.update');
    });

Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['auth', 'no.cache'])
    ->group(function () {
        Route::get('/td/sets/bulk-create', [TdSetController::class, 'bulkCreate'])
            ->name('td.sets.bulk_create');

        Route::post('/td/sets/bulk-store', [TdSetController::class, 'bulkStore'])
            ->name('td.sets.bulk_store');

        Route::get('/td/sets/{td}/editor', [TdQuickEditorController::class, 'edit'])
            ->name('td.sets.editor');

        Route::post('/td/sets/{td}/correction-delay', function (Request $request, TdSet $td) {
            abort_unless($request->user() && method_exists($request->user(), 'isTeacher') && $request->user()->isTeacher(), 403);

            $data = $request->validate([
                'correction_delay_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            ], [
                'correction_delay_minutes.required' => 'Indiquez le temps de traitement du TD.',
                'correction_delay_minutes.min' => 'Le temps ne peut pas être négatif.',
                'correction_delay_minutes.max' => 'Le temps ne peut pas dépasser 24 heures.',
            ]);

            $allowed = TeacherAssignment::query()
                ->where('teacher_id', $request->user()->id)
                ->where('school_class_id', $td->school_class_id)
                ->where('subject_id', $td->subject_id)
                ->where('is_active', true)
                ->exists();

            abort_unless($allowed, 403);

            $td->forceFill([
                'correction_delay_minutes' => (int) $data['correction_delay_minutes'],
            ])->save();

            return back()->with('success', 'Temps de traitement du TD mis à jour.');
        })->name('td.sets.correction_delay.update');
    });
