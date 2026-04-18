<?php

use App\Http\Controllers\Admin\AdminClassController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHomepageController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminTdController;
use App\Http\Controllers\Admin\AdminTeacherAssignmentController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\Auth\AdminSetupController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\MessageController as StudentMessageController;
use App\Http\Controllers\Student\SubscriptionController;
use App\Http\Controllers\Student\TdController as StudentTdController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\MessageController as TeacherMessageController;
use App\Http\Controllers\Teacher\TdQuestionController as TeacherTdQuestionController;
use App\Http\Controllers\Teacher\TdSetController as TeacherTdSetController;
use App\Http\Controllers\Teacher\TdSourceController as TeacherTdSourceController;
use App\Http\Controllers\Webhook\NotchPayWebhookController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureStudent;
use App\Http\Middleware\EnsureTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['guest', 'no.cache'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::get('/logout', function (Request $request) {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = $request->user();

    if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user && method_exists($user, 'isTeacher') && $user->isTeacher()) {
        return redirect()->route('teacher.dashboard');
    }

    return redirect()->route('student.dashboard');
})->middleware(['no.cache'])->name('logout.history');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware(['auth', 'no.cache']);

$adminPath = trim((string) config('timahschool.admin_path', 'backoffice-access'), '/');

Route::prefix($adminPath)->name('admin.')->group(function () {
    Route::get('/setup-admin', [AdminSetupController::class, 'showSetupForm'])->middleware('no.cache')->name('setup');
    Route::post('/setup-admin', [AdminSetupController::class, 'storeSetupForm'])->middleware('no.cache')->name('setup.store');

    Route::get('/logout', function (Request $request) {
        $user = $request->user();

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    })->middleware('no.cache')->name('logout.history');

    Route::middleware(['guest', 'no.cache'])->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'no.cache', EnsureAdmin::class])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/homepage', [AdminHomepageController::class, 'edit'])->name('homepage.edit');
        Route::post('/homepage', [AdminHomepageController::class, 'update'])->name('homepage.update');
        Route::post('/homepage/messages', [AdminHomepageController::class, 'storeMessage'])->name('homepage.messages.store');
        Route::post('/homepage/messages/{message}/update', [AdminHomepageController::class, 'updateMessage'])->name('homepage.messages.update');
        Route::post('/homepage/messages/{message}/delete', [AdminHomepageController::class, 'deleteMessage'])->name('homepage.messages.delete');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/update', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/delete', [AdminUserController::class, 'delete'])->name('users.delete');
        Route::get('/teachers', [AdminTeacherController::class, 'index'])->name('teachers.index');
        Route::post('/teachers', [AdminTeacherController::class, 'store'])->name('teachers.store');
        Route::post('/teachers/{user}/toggle', [AdminTeacherController::class, 'toggle'])->name('teachers.toggle');
        Route::get('/assignments', [AdminTeacherAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments', [AdminTeacherAssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/{assignment}/toggle', [AdminTeacherAssignmentController::class, 'toggle'])->name('assignments.toggle');
        Route::post('/assignments/{assignment}/delete', [AdminTeacherAssignmentController::class, 'destroy'])->name('assignments.delete');
        Route::get('/classes', [AdminClassController::class, 'index'])->name('classes.index');
        Route::post('/classes', [AdminClassController::class, 'store'])->name('classes.store');
        Route::post('/classes/{id}/update', [AdminClassController::class, 'update'])->name('classes.update');
        Route::post('/classes/{id}/delete', [AdminClassController::class, 'delete'])->name('classes.delete');
        Route::get('/subjects', [AdminSubjectController::class, 'index'])->name('subjects.index');
        Route::post('/subjects', [AdminSubjectController::class, 'store'])->name('subjects.store');
        Route::post('/subjects/{id}/update', [AdminSubjectController::class, 'update'])->name('subjects.update');
        Route::post('/subjects/{id}/delete', [AdminSubjectController::class, 'delete'])->name('subjects.delete');
        Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::post('/courses/{id}/publish', [AdminCourseController::class, 'publish'])->name('courses.publish');
        Route::post('/courses/{id}/archive', [AdminCourseController::class, 'archive'])->name('courses.archive');
        Route::post('/courses/{id}/update', [AdminCourseController::class, 'update'])->name('courses.update');
        Route::post('/courses/{id}/delete', [AdminCourseController::class, 'delete'])->name('courses.delete');
        Route::get('/td/sets', [AdminTdController::class, 'index'])->name('td.index');
        Route::get('/td/sets/create', [AdminTdController::class, 'create'])->name('td.create');
        Route::post('/td/sets', [AdminTdController::class, 'store'])->name('td.store');
        Route::get('/td/sets/{td}/edit', [AdminTdController::class, 'edit'])->name('td.edit');
        Route::post('/td/sets/{td}/update', [AdminTdController::class, 'update'])->name('td.update');
        Route::post('/td/sets/{td}/publish', [AdminTdController::class, 'publish'])->name('td.publish');
        Route::post('/td/sets/{td}/archive', [AdminTdController::class, 'archive'])->name('td.archive');
        Route::post('/td/sets/{td}/delete', [AdminTdController::class, 'delete'])->name('td.delete');
        Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
        Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
        Route::post('/plans/{id}/update', [AdminPlanController::class, 'update'])->name('plans.update');
        Route::post('/plans/{id}/delete', [AdminPlanController::class, 'delete'])->name('plans.delete');
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/{id}/update', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::post('/subscriptions/{id}/delete', [AdminSubscriptionController::class, 'delete'])->name('subscriptions.delete');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{id}/update', [AdminPaymentController::class, 'update'])->name('payments.update');
        Route::post('/payments/{id}/delete', [AdminPaymentController::class, 'delete'])->name('payments.delete');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    });
});

Route::middleware(['auth', 'no.cache', EnsureTeacher::class])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes.index');
    Route::get('/courses', [TeacherCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [TeacherCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [TeacherCourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [TeacherCourseController::class, 'edit'])->name('courses.edit');
    Route::post('/courses/{course}/update', [TeacherCourseController::class, 'update'])->name('courses.update');
    Route::post('/courses/{course}/publish', [TeacherCourseController::class, 'publish'])->name('courses.publish');
    Route::post('/courses/{course}/archive', [TeacherCourseController::class, 'archive'])->name('courses.archive');
    Route::post('/courses/{course}/delete', [TeacherCourseController::class, 'destroy'])->name('courses.delete');
    Route::get('/courses/{course}/document', [TeacherCourseController::class, 'document'])->name('courses.document');
    Route::get('/courses/{course}/document/download', [TeacherCourseController::class, 'downloadDocument'])->name('courses.document.download');
    Route::get('/td/sets', [TeacherTdSetController::class, 'index'])->name('td.sets.index');
    Route::get('/td/sets/create', [TeacherTdSetController::class, 'create'])->name('td.sets.create');
    Route::post('/td/sets', [TeacherTdSetController::class, 'store'])->name('td.sets.store');
    Route::get('/td/sets/{td}/edit', [TeacherTdSetController::class, 'edit'])->name('td.sets.edit');
    Route::post('/td/sets/{td}/update', [TeacherTdSetController::class, 'update'])->name('td.sets.update');
    Route::post('/td/sets/{td}/publish', [TeacherTdSetController::class, 'publish'])->name('td.sets.publish');
    Route::post('/td/sets/{td}/archive', [TeacherTdSetController::class, 'archive'])->name('td.sets.archive');
    Route::post('/td/sets/{td}/delete', [TeacherTdSetController::class, 'destroy'])->name('td.sets.delete');
    Route::get('/td/sets/{td}/document', [TeacherTdSetController::class, 'document'])->name('td.sets.document');
    Route::get('/td/sets/{td}/correction-document', [TeacherTdSetController::class, 'correctionDocument'])->name('td.sets.correction_document');
    Route::get('/td/questions', [TeacherTdQuestionController::class, 'index'])->name('td.questions.index');
    Route::get('/td/questions/{thread}', [TeacherTdQuestionController::class, 'show'])->name('td.questions.show');
    Route::post('/td/questions/{thread}/reply', [TeacherTdQuestionController::class, 'reply'])->name('td.questions.reply');
    Route::get('/td/messages/{message}/attachment', [TeacherTdQuestionController::class, 'attachment'])->name('td.questions.attachment');
    Route::get('/td/sources', [TeacherTdSourceController::class, 'index'])->name('td.sources.index');
    Route::get('/td/sources/create', [TeacherTdSourceController::class, 'create'])->name('td.sources.create');
    Route::post('/td/sources', [TeacherTdSourceController::class, 'store'])->name('td.sources.store');
    Route::get('/td/sources/{tdSource}', [TeacherTdSourceController::class, 'show'])->name('td.sources.show');
    Route::post('/td/sources/{tdSource}/analyze', [TeacherTdSourceController::class, 'analyze'])->name('td.sources.analyze');
    Route::post('/td/sources/{tdSource}/generate', [TeacherTdSourceController::class, 'generate'])->name('td.sources.generate');
    Route::get('/td/sources/{tdSource}/file', [TeacherTdSourceController::class, 'file'])->name('td.sources.file');
    Route::get('/messages', [TeacherMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [TeacherMessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [TeacherMessageController::class, 'reply'])->name('messages.reply');
    Route::get('/messages/{message}/attachment', [TeacherMessageController::class, 'attachment'])->name('messages.attachment');
});

Route::middleware(['auth', 'no.cache', EnsureStudent::class])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/expired', [SubscriptionController::class, 'expired'])->name('expired');
        Route::get('/required', [SubscriptionController::class, 'required'])->name('required');
        Route::get('/pending', [SubscriptionController::class, 'pending'])->name('pending');
        Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::post('/pay/{plan}', [SubscriptionController::class, 'processPayment'])->name('pay');
    });

    Route::middleware('subscribed')->group(function () {
        Route::get('/courses', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/document', [StudentCourseController::class, 'document'])->name('courses.document');
        Route::get('/courses/{course}/document/download', [StudentCourseController::class, 'downloadDocument'])->name('courses.document.download');

        Route::get('/td', [StudentTdController::class, 'index'])->name('td.index');
        Route::get('/td/{td}', [StudentTdController::class, 'show'])->name('td.show');
        Route::post('/td/{td}/complete', [StudentTdController::class, 'complete'])->name('td.complete');
        Route::post('/td/{td}/ask', [StudentTdController::class, 'ask'])->name('td.ask');
        Route::get('/td/{td}/document', [StudentTdController::class, 'document'])->name('td.document');
        Route::get('/td/{td}/correction-document', [StudentTdController::class, 'correctionDocument'])->name('td.correction_document');
        Route::get('/td/messages/{message}/attachment', [StudentTdController::class, 'attachment'])->name('td.attachment');
    });

    Route::get('/messages', [StudentMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [StudentMessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [StudentMessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}/attachment', [StudentMessageController::class, 'attachment'])->name('messages.attachment');
});

Route::get('/payment/callback', [SubscriptionController::class, 'callback'])
    ->name('payment.callback')
    ->middleware('auth');

Route::post('/webhook/notchpay', [NotchPayWebhookController::class, 'handle'])->name('webhook.notchpay');
