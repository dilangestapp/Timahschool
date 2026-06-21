<?php

use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/teacher/messages', function (Request $request) {
    $assignments = Schema::hasTable('teacher_assignments')
        ? TeacherAssignment::query()
            ->with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->get()
        : collect();

    $classIds = $assignments->pluck('school_class_id')->filter()->unique()->values();

    $students = collect();
    if ($classIds->isNotEmpty() && Schema::hasTable('student_profiles') && Schema::hasColumn('student_profiles', 'school_class_id')) {
        $students = User::query()
            ->with(['studentProfile.schoolClass'])
            ->whereHas('studentProfile', fn ($query) => $query->whereIn('school_class_id', $classIds))
            ->orderBy('full_name')
            ->orderBy('name')
            ->get();
    }

    $messages = collect();
    if (Schema::hasTable('teacher_messages') && $assignments->isNotEmpty()) {
        $query = TeacherMessage::query()
            ->with(['student.studentProfile.schoolClass', 'subject', 'schoolClass', 'teacher'])
            ->where('teacher_id', auth()->id());

        if (Schema::hasColumn('teacher_messages', 'school_class_id') && $classIds->isNotEmpty()) {
            $query->whereIn('school_class_id', $classIds);
        }

        if (Schema::hasColumn('teacher_messages', 'deleted_by_teacher_at')) {
            $query->whereNull('deleted_by_teacher_at');
        }

        if (Schema::hasColumn('teacher_messages', 'created_at')) {
            $query->orderBy('created_at');
        }

        $messages = $query->get();
    }

    $threads = $students->map(function ($student) use ($messages, $assignments) {
        $studentMessages = $messages->where('student_id', $student->id)->sortBy('created_at')->values();
        $latest = $studentMessages->last();

        return (object) [
            'student' => $student,
            'assignment' => $assignments->firstWhere('school_class_id', optional($student->studentProfile)->school_class_id) ?: $assignments->first(),
            'messages' => $studentMessages,
            'latest_message' => $latest,
            'unread_count' => $studentMessages
                ->where('direction', TeacherMessage::DIRECTION_STUDENT)
                ->where('status', TeacherMessage::STATUS_UNREAD)
                ->count(),
            'attachment_count' => $studentMessages->filter(fn ($message) => !empty($message->attachment_path))->count(),
            'sort_timestamp' => $latest?->created_at?->timestamp ?? 0,
        ];
    })->sortByDesc('sort_timestamp')->values();

    $selectedStudentId = (int) $request->query('student');
    if (!$threads->contains(fn ($thread) => (int) $thread->student->id === $selectedStudentId)) {
        $selectedStudentId = (int) optional($threads->first())->student->id;
    }

    if ($selectedStudentId && Schema::hasTable('teacher_messages')) {
        TeacherMessage::query()
            ->where('teacher_id', auth()->id())
            ->where('student_id', $selectedStudentId)
            ->where('direction', TeacherMessage::DIRECTION_STUDENT)
            ->where('status', TeacherMessage::STATUS_UNREAD)
            ->update(['status' => TeacherMessage::STATUS_READ, 'read_at' => now()]);
    }

    return view('teacher.messages.safe', [
        'threads' => $threads,
        'selectedStudentId' => $selectedStudentId,
        'selectedThread' => $threads->first(fn ($thread) => (int) $thread->student->id === $selectedStudentId),
        'assignments' => $assignments,
    ]);
})->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])->name('teacher.messages.safe.index');

Route::get('/teacher/courses/{course}/office', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'editor'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.courses.office');

Route::post('/teacher/courses/{course}/convert-content', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'convertContent'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.courses.convert');

Route::post('/teacher/messages/send', [\App\Http\Controllers\Teacher\MessageController::class, 'send'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.messages.send');

Route::post('/teacher/messages/broadcast', [\App\Http\Controllers\Teacher\MessageController::class, 'broadcast'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.messages.broadcast');

Route::post('/teacher/messages/{message}/delete', [\App\Http\Controllers\Teacher\MessageController::class, 'destroy'])
    ->middleware(['auth', 'no.cache', \App\Http\Middleware\EnsureTeacher::class])
    ->name('teacher.messages.delete');

Route::get('/course-office/{course}/document/{key}', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'file'])
    ->name('onlyoffice.courses.file');

Route::post('/course-office/{course}/save/{key}', [\App\Http\Controllers\Teacher\CourseOfficeController::class, 'callback'])
    ->name('onlyoffice.courses.callback');

if (file_exists(base_path('routes/timah_supervision.php'))) {
    require base_path('routes/timah_supervision.php');
}

if (file_exists(base_path('routes/timah_responsible_tb.php'))) {
    require base_path('routes/timah_responsible_tb.php');
}

if (file_exists(base_path('routes/timah_responsible_actions.php'))) {
    require base_path('routes/timah_responsible_actions.php');
}

if (file_exists(base_path('routes/timah_department_management.php'))) {
    require base_path('routes/timah_department_management.php');
}
