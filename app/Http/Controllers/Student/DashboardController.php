<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TdAttempt;
use App\Models\TdSet;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $studentProfile = $user->studentProfile;

        $recentCourses = collect();
        if ($studentProfile && $studentProfile->school_class_id) {
            $recentCourses = Course::query()
                ->where('school_class_id', $studentProfile->school_class_id)
                ->where('status', 'published')
                ->with('subject')
                ->latest()
                ->take(4)
                ->get();
        }

        $recentTdSets = collect();
        $tdOpenedCount = 0;
        if (Schema::hasTable('td_sets') && $studentProfile && $studentProfile->school_class_id) {
            $recentTdSets = TdSet::query()
                ->where('school_class_id', $studentProfile->school_class_id)
                ->where('status', TdSet::STATUS_PUBLISHED)
                ->with('subject')
                ->latest('published_at')
                ->take(4)
                ->get();

            if (Schema::hasTable('td_attempts')) {
                $tdOpenedCount = TdAttempt::query()->where('student_id', $user->id)->count();
            }
        }

        $pendingQuizzes = collect();
        if (
            class_exists('App\\Models\\Quiz')
            && Schema::hasTable('quizzes')
            && Schema::hasTable('quiz_attempts')
            && $studentProfile
            && $studentProfile->school_class_id
        ) {
            $quizModel = app('App\\Models\\Quiz');

            $pendingQuizzes = $quizModel::query()
                ->whereHas('subject.classSubject', function ($q) use ($studentProfile) {
                    $q->where('school_class_id', $studentProfile->school_class_id);
                })
                ->where('status', 'published')
                ->whereDoesntHave('attempts', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->take(3)
                ->get();
        }

        return view('student.dashboard', [
            'user' => $user,
            'studentProfile' => $studentProfile,
            'subscription' => $user->activeSubscription,
            'recentCourses' => $recentCourses,
            'recentTdSets' => $recentTdSets,
            'tdOpenedCount' => $tdOpenedCount,
            'pendingQuizzes' => $pendingQuizzes,
        ]);
    }
}
