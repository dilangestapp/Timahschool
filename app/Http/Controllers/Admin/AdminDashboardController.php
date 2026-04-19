<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\SchoolClass;
use App\Models\Subscription;
use App\Models\TdQuestionThread;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $hasAssignments = class_exists(TeacherAssignment::class) && Schema::hasTable('teacher_assignments');
        $hasMessages = class_exists(TeacherMessage::class) && Schema::hasTable('teacher_messages');
        $hasTdSets = Schema::hasTable('td_sets');
        $hasTdQuestions = Schema::hasTable('td_question_threads');

        $stats = [
            'users' => Schema::hasTable('users') ? User::query()->count() : 0,
            'classes' => Schema::hasTable('school_classes') ? SchoolClass::query()->count() : 0,
            'teachers' => Schema::hasTable('users') ? User::query()->get()->filter(fn ($user) => $user->isTeacher())->count() : 0,
            'assignments' => $hasAssignments ? TeacherAssignment::query()->count() : 0,
            'active_assignments' => $hasAssignments ? TeacherAssignment::query()->where('is_active', true)->count() : 0,
            'teacher_messages' => $hasMessages ? TeacherMessage::query()->count() : 0,
            'teacher_unread_messages' => $hasMessages ? TeacherMessage::query()->where('status', TeacherMessage::STATUS_UNREAD)->count() : 0,
            'active_subscriptions' => Schema::hasTable('subscriptions') ? Subscription::query()->where('status', Subscription::STATUS_ACTIVE)->count() : 0,
            'payments_completed' => Schema::hasTable('payments') ? Payment::query()->whereIn('status', ['completed', 'paid', 'success'])->count() : 0,
            'td_total' => $hasTdSets ? TdSet::query()->count() : 0,
            'td_published' => $hasTdSets ? TdSet::query()->where('status', TdSet::STATUS_PUBLISHED)->count() : 0,
            'td_draft' => $hasTdSets ? TdSet::query()->where('status', TdSet::STATUS_DRAFT)->count() : 0,
            'td_questions_open' => $hasTdQuestions ? TdQuestionThread::query()->where('status', TdQuestionThread::STATUS_OPEN)->count() : 0,
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'dashboardText' => PlatformSetting::group('dashboard_admin'),
            'recentTeacherMessages' => $hasMessages
                ? TeacherMessage::query()->with(['teacher', 'student', 'schoolClass', 'subject'])->latest()->take(6)->get()
                : collect(),
            'recentAssignments' => $hasAssignments
                ? TeacherAssignment::query()->with(['teacher', 'schoolClass', 'subject'])->latest()->take(6)->get()
                : collect(),
            'recentTdSets' => $hasTdSets
                ? TdSet::query()->with(['schoolClass', 'subject', 'author'])->latest()->take(6)->get()
                : collect(),
        ]);
    }
}
