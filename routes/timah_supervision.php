<?php

use App\Http\Controllers\Admin\AdminPedagogicalSupervisionController;
use App\Http\Controllers\SupervisionDashboardController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

Route::prefix($adminPath)
    ->name('admin.')
    ->middleware(['auth', 'no.cache', EnsureAdmin::class])
    ->group(function () {
        Route::get('/organization', [AdminPedagogicalSupervisionController::class, 'index'])->name('organization.index');
        Route::post('/organization/divisions', [AdminPedagogicalSupervisionController::class, 'storeDivision'])->name('organization.divisions.store');
        Route::post('/organization/departments', [AdminPedagogicalSupervisionController::class, 'storeDepartment'])->name('organization.departments.store');
        Route::post('/organization/responsibilities', [AdminPedagogicalSupervisionController::class, 'storeResponsibility'])->name('organization.responsibilities.store');
        Route::post('/organization/notes', [AdminPedagogicalSupervisionController::class, 'storeNote'])->name('organization.notes.store');
        Route::post('/organization/responsibilities/{responsibility}/toggle', [AdminPedagogicalSupervisionController::class, 'toggleResponsibility'])->name('organization.responsibilities.toggle');
        Route::post('/organization/notes/{note}/status', [AdminPedagogicalSupervisionController::class, 'updateNoteStatus'])->name('organization.notes.status');
    });

Route::prefix('supervision')
    ->name('supervision.')
    ->middleware(['auth', 'no.cache'])
    ->group(function () {
        Route::get('/dashboard', function () {
            $schemaReady = Schema::hasTable('pedagogical_responsibilities') && Schema::hasTable('pedagogical_supervision_notes');

            $empty = ['schemaReady' => false, 'responsibilities' => collect(), 'activeResponsibility' => null, 'areaTitle' => 'Supervision pédagogique', 'stats' => [], 'teachers' => collect(), 'courses' => collect(), 'tdSets' => collect(), 'questions' => collect(), 'notes' => collect()];
            if (!$schemaReady) {
                return view('supervision.dashboard', $empty);
            }

            $responsibilities = DB::table('pedagogical_responsibilities as pr')
                ->leftJoin('teaching_divisions as d', 'd.id', '=', 'pr.teaching_division_id')
                ->leftJoin('teaching_departments as dep', 'dep.id', '=', 'pr.teaching_department_id')
                ->where('pr.user_id', auth()->id())
                ->where('pr.is_active', true)
                ->select('pr.*', 'd.name as division_name', 'dep.name as department_name')
                ->get();

            abort_if($responsibilities->isEmpty(), 403, 'Aucune responsabilité pédagogique active ne vous est attribuée.');

            $active = $responsibilities->firstWhere('id', (int) request('responsibility')) ?: $responsibilities->first();
            $areaTitle = $active->department_name ?: ($active->division_name ?: 'Plateforme entière');

            $teachers = Schema::hasTable('teacher_assignments') && Schema::hasTable('users')
                ? DB::table('teacher_assignments as ta')->join('users as u', 'u.id', '=', 'ta.teacher_id')->leftJoin('school_classes as c', 'c.id', '=', 'ta.school_class_id')->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')->where('ta.is_active', true)->select('u.id', 'u.full_name', 'u.name', 'u.username', 'c.name as class_name', 's.name as subject_name')->orderByDesc('ta.id')->limit(12)->get()
                : collect();

            $courses = Schema::hasTable('courses')
                ? DB::table('courses as item')->leftJoin('school_classes as c', 'c.id', '=', 'item.school_class_id')->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')->select('item.id', 'item.title', 'item.status', 'c.name as class_name', 's.name as subject_name')->orderByDesc('item.id')->limit(10)->get()
                : collect();

            $tdSets = Schema::hasTable('td_sets')
                ? DB::table('td_sets as item')->leftJoin('school_classes as c', 'c.id', '=', 'item.school_class_id')->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')->select('item.id', 'item.title', 'item.status', 'c.name as class_name', 's.name as subject_name')->orderByDesc('item.id')->limit(10)->get()
                : collect();

            $questions = collect();
            if (Schema::hasTable('td_question_threads')) {
                $questionQuery = DB::table('td_question_threads as q');
                if (Schema::hasColumn('td_question_threads', 'school_class_id') && Schema::hasTable('school_classes')) {
                    $questionQuery->leftJoin('school_classes as c', 'c.id', '=', 'q.school_class_id');
                }
                if (Schema::hasColumn('td_question_threads', 'subject_id') && Schema::hasTable('subjects')) {
                    $questionQuery->leftJoin('subjects as s', 's.id', '=', 'q.subject_id');
                }
                $questions = $questionQuery
                    ->select('q.id', 'q.status', DB::raw(Schema::hasColumn('td_question_threads', 'school_class_id') ? 'c.name as class_name' : 'NULL as class_name'), DB::raw(Schema::hasColumn('td_question_threads', 'subject_id') ? 's.name as subject_name' : 'NULL as subject_name'))
                    ->orderByDesc('q.id')
                    ->limit(10)
                    ->get();
            }

            $notes = DB::table('pedagogical_supervision_notes as n')->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')->where('n.responsibility_id', $active->id)->select('n.*', 'u.full_name', 'u.name', 'u.username')->orderByDesc('n.id')->limit(12)->get();

            return view('supervision.dashboard', [
                'schemaReady' => true,
                'responsibilities' => $responsibilities,
                'activeResponsibility' => $active,
                'areaTitle' => $areaTitle,
                'stats' => [
                    'teachers' => $teachers->pluck('id')->unique()->count(),
                    'courses_published' => Schema::hasTable('courses') ? DB::table('courses')->where('status', 'published')->count() : 0,
                    'td_published' => Schema::hasTable('td_sets') ? DB::table('td_sets')->where('status', 'published')->count() : 0,
                    'questions_open' => Schema::hasTable('td_question_threads') ? DB::table('td_question_threads')->where('status', 'open')->count() : 0,
                ],
                'teachers' => $teachers,
                'courses' => $courses,
                'tdSets' => $tdSets,
                'questions' => $questions,
                'notes' => $notes,
            ]);
        })->name('dashboard');

        Route::post('/notes', [SupervisionDashboardController::class, 'storeNote'])->name('notes.store');
    });
