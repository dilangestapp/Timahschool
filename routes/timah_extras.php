<?php

use App\Http\Controllers\Admin\AdminMobileAcademyController;
use App\Http\Controllers\Admin\AdminStudentAccountController;
use App\Http\Controllers\Teacher\TdQuickEditorController;
use App\Http\Controllers\Teacher\TdSetController;
use App\Http\Middleware\EnsureAdmin;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

Route::prefix($adminPath)
    ->name('admin.')
    ->middleware(['auth', 'no.cache', EnsureAdmin::class])
    ->group(function () {
        Route::prefix('mobile-academy')->name('mobile-academy.')->group(function () {
            Route::get('/', function () {
                $base = url('/backoffice-access/mobile-academy');
                $token = csrf_token();
                $count = fn ($table) => Schema::hasTable($table) ? DB::table($table)->count() : 0;
                $rows = fn ($table) => Schema::hasTable($table) ? DB::table($table)->orderByDesc('id')->limit(10)->get() : collect();
                $classes = Schema::hasTable('school_classes') ? DB::table('school_classes')->select('id','name')->orderBy('name')->get() : collect();
                $subjects = Schema::hasTable('subjects') ? DB::table('subjects')->select('id','name')->orderBy('name')->get() : collect();
                $students = Schema::hasTable('users') ? DB::table('users')->select('id','name','full_name','username','phone')->orderByDesc('id')->limit(100)->get() : collect();
                $optClass = '<option value="">Tous</option>'; foreach ($classes as $c) { $optClass .= '<option value="'.e($c->id).'">'.e($c->name).'</option>'; }
                $optSubject = '<option value="">Toutes</option>'; foreach ($subjects as $s) { $optSubject .= '<option value="'.e($s->id).'">'.e($s->name).'</option>'; }
                $optStudent = ''; foreach ($students as $s) { $name = $s->full_name ?: ($s->name ?: ($s->username ?: ('Utilisateur #'.$s->id))); $optStudent .= '<option value="'.e($s->id).'">'.e($name.' '.$s->phone).'</option>'; }
                if ($optStudent === '') $optStudent = '<option value="">Aucun utilisateur</option>';
                $list = function ($items, $textField = 'description') {
                    if ($items->isEmpty()) return '<p class="muted">Aucune donnée.</p>';
                    $html = '';
                    foreach ($items as $item) {
                        $title = $item->title ?? ($item->name ?? ('Élément #'.($item->id ?? '')));
                        $text = $item->{$textField} ?? ($item->content ?? ($item->message ?? ''));
                        $html .= '<div class="item"><b>'.e($title).'</b><small>'.e($item->status ?? $item->type ?? '').'</small><p>'.e(mb_strimwidth((string) $text,0,150,'...')).'</p></div>';
                    }
                    return $html;
                };
                $programs = $rows('learning_program_schedules'); $posts = $rows('digital_board_posts'); $quizzes = $rows('mobile_quizzes'); $evaluations = $rows('biweekly_evaluations'); $reports = $rows('progress_reports'); $notifications = $rows('mobile_notifications');
                return response('<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mobile Academy</title><style>body{font-family:Arial;background:#f5f7fb;margin:0;color:#111827}.wrap{max-width:1150px;margin:auto;padding:24px}.top{display:flex;justify-content:space-between;gap:12px;align-items:center}a.btn,button{background:#2563eb;color:white;border:0;border-radius:12px;padding:11px 14px;text-decoration:none;font-weight:700}.ghost{background:white!important;color:#111827!important;border:1px solid #dbe3ef!important}.stats{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin:18px 0}.stat,.box{background:white;border:1px solid #dbe3ef;border-radius:18px;padding:16px;box-shadow:0 10px 24px #0f172a12}.stat b{font-size:26px;display:block}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.form{display:grid;grid-template-columns:1fr 1fr;gap:10px}.full{grid-column:1/-1}input,select,textarea{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:10px}label{font-weight:700;font-size:13px}.item{border-top:1px solid #e5edf7;padding:12px 0}.item small{float:right;background:#eef2ff;border-radius:999px;padding:5px 8px}.muted{color:#64748b}@media(max-width:800px){.stats,.grid,.form{grid-template-columns:1fr}.top{display:block}}</style></head><body><div class="wrap"><div class="top"><div><h1>TIMAH ACADEMY Mobile</h1><p>Centre de pilotage stable.</p></div><div><a class="btn ghost" href="/backoffice-access/dashboard">Dashboard</a> <a class="btn ghost" href="/backoffice-access/learning-program">Programme</a> <a class="btn ghost" href="/backoffice-access/mobile-devices">Appareils</a></div></div><div class="stats"><div class="stat"><b>'.$count('learning_program_schedules').'</b>Activités</div><div class="stat"><b>'.$count('digital_board_posts').'</b>Annonces</div><div class="stat"><b>'.$count('mobile_quizzes').'</b>Quiz</div><div class="stat"><b>'.$count('biweekly_evaluations').'</b>Évaluations</div><div class="stat"><b>'.$count('progress_reports').'</b>Rapports</div><div class="stat"><b>'.$count('mobile_notifications').'</b>Notifications</div></div><div class="grid"><section class="box"><h2>Programme</h2><form class="form" method="post" action="'.$base.'/program"><input type="hidden" name="_token" value="'.$token.'"><label>Titre<input name="title" required></label><label>Type<select name="activity_type"><option value="course">Cours</option><option value="td">TD</option><option value="quiz">Quiz</option><option value="revision">Révision</option></select></label><label>Classe<select name="school_class_id">'.$optClass.'</select></label><label>Matière<select name="subject_id">'.$optSubject.'</select></label><label>Semaine<input type="number" name="week_number" value="1"></label><label>Jour<select name="weekday"><option value="1">Lundi</option><option value="2">Mardi</option><option value="3">Mercredi</option><option value="4">Jeudi</option><option value="5">Vendredi</option><option value="6">Samedi</option><option value="7">Dimanche</option></select></label><label>Heure<input type="time" name="unlock_time" value="18:00"></label><label>Durée<input type="number" name="duration_minutes" value="60"></label><label>Statut<select name="status"><option value="published">Publié</option><option value="scheduled">Programmé</option><option value="draft">Brouillon</option></select></label><label class="full">Description<textarea name="description"></textarea></label><p><button>Ajouter</button></p></form>'.$list($programs).'</section><section class="box"><h2>Babillard</h2><form class="form" method="post" action="'.$base.'/board"><input type="hidden" name="_token" value="'.$token.'"><label>Titre<input name="title" required></label><label>Type<select name="type"><option value="announcement">Annonce</option><option value="report">Rapport</option></select></label><label>Public<select name="audience"><option value="all">Tous</option><option value="student">Élèves</option><option value="parent">Parents</option></select></label><label>Statut<select name="status"><option value="published">Publié</option><option value="draft">Brouillon</option></select></label><label class="full">Message<textarea name="content" required></textarea></label><p><button>Publier</button></p></form>'.$list($posts,'content').'</section><section class="box"><h2>Quiz</h2><form class="form" method="post" action="'.$base.'/quizzes"><input type="hidden" name="_token" value="'.$token.'"><label>Titre<input name="title" required></label><label>Durée<input type="number" name="duration_minutes" value="15"></label><label>Note minimale<input type="number" name="pass_mark" value="10"></label><label>Statut<select name="status"><option value="published">Publié</option><option value="draft">Brouillon</option></select></label><label class="full">Description<textarea name="description"></textarea></label><p><button>Créer</button></p></form>'.$list($quizzes).'</section><section class="box"><h2>Évaluations</h2><form class="form" method="post" action="'.$base.'/evaluations"><input type="hidden" name="_token" value="'.$token.'"><label>Titre<input name="title" required></label><label>Statut<select name="status"><option value="published">Publié</option><option value="scheduled">Programmé</option><option value="draft">Brouillon</option></select></label><label>Ouverture<input type="datetime-local" name="opens_at"></label><label>Clôture<input type="datetime-local" name="closes_at"></label><label>Durée<input type="number" name="duration_minutes" value="120"></label><label class="full">Description<textarea name="description"></textarea></label><p><button>Ajouter</button></p></form>'.$list($evaluations).'</section><section class="box"><h2>Rapports</h2><form class="form" method="post" action="'.$base.'/reports"><input type="hidden" name="_token" value="'.$token.'"><label>Élève<select name="student_id">'.$optStudent.'</select></label><label>Participation<input type="number" name="participation_rate" value="0"></label><label>Score<input type="number" step="0.01" name="evaluation_score"></label><label>Statut<select name="status"><option value="published">Publié</option><option value="draft">Brouillon</option></select></label><label class="full">Recommandations<textarea name="recommendations"></textarea></label><p><button>Publier</button></p></form>'.$list($reports,'recommendations').'</section><section class="box"><h2>Notifications</h2><form class="form" method="post" action="'.$base.'/notifications"><input type="hidden" name="_token" value="'.$token.'"><label>Titre<input name="title" required></label><label>Type<select name="type"><option value="info">Info</option><option value="course">Cours</option><option value="quiz">Quiz</option><option value="report">Rapport</option></select></label><label>Public<select name="audience"><option value="all">Tous</option><option value="student">Élèves</option><option value="parent">Parents</option></select></label><label class="full">Message<textarea name="message" required></textarea></label><p><button>Publier</button></p></form>'.$list($notifications,'message').'</section></div></div></body></html>');
            })->name('index');
            Route::post('/program', [AdminMobileAcademyController::class, 'storeProgram'])->name('program.store');
            Route::post('/board', [AdminMobileAcademyController::class, 'storeBoard'])->name('board.store');
            Route::post('/evaluations', [AdminMobileAcademyController::class, 'storeEvaluation'])->name('evaluations.store');
            Route::post('/quizzes', [AdminMobileAcademyController::class, 'storeQuiz'])->name('quizzes.store');
            Route::post('/reports', [AdminMobileAcademyController::class, 'storeReport'])->name('reports.store');
            Route::post('/notifications', [AdminMobileAcademyController::class, 'storeNotification'])->name('notifications.store');
        });

        Route::post('/users/{user}/student-class', [AdminStudentAccountController::class, 'updateClass'])->name('users.student_class.update');
        Route::post('/users/{user}/student-subscription', [AdminStudentAccountController::class, 'updateSubscription'])->name('users.student_subscription.update');
    });

Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'no.cache'])->group(function () {
    Route::get('/td/sets/bulk-create', [TdSetController::class, 'bulkCreate'])->name('td.sets.bulk_create');
    Route::post('/td/sets/bulk-store', [TdSetController::class, 'bulkStore'])->name('td.sets.bulk_store');
    Route::get('/td/sets/{td}/editor', [TdQuickEditorController::class, 'edit'])->name('td.sets.editor');
    Route::post('/td/sets/{td}/correction-delay', function (Request $request, TdSet $td) {
        abort_unless($request->user() && method_exists($request->user(), 'isTeacher') && $request->user()->isTeacher(), 403);
        $data = $request->validate(['correction_delay_minutes' => ['required', 'integer', 'min:0', 'max:1440']]);
        $allowed = TeacherAssignment::query()->where('teacher_id', $request->user()->id)->where('school_class_id', $td->school_class_id)->where('subject_id', $td->subject_id)->where('is_active', true)->exists();
        abort_unless($allowed, 403);
        $td->forceFill(['correction_delay_minutes' => (int) $data['correction_delay_minutes']])->save();
        return back()->with('success', 'Temps de traitement du TD mis à jour.');
    })->name('td.sets.correction_delay.update');
});
