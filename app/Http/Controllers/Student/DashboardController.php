<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PlatformSetting;
use App\Models\TdAttempt;
use App\Models\TdSet;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $studentProfile = $user->studentProfile;
        $classId = $studentProfile?->school_class_id;

        $recentCourses = collect();
        $allCourses = collect();
        if ($classId && Schema::hasTable('courses')) {
            $courseQuery = Course::query()
                ->where('school_class_id', $classId)
                ->where('status', 'published')
                ->with('subject')
                ->latest();

            $allCourses = (clone $courseQuery)->take(100)->get();
            $recentCourses = $allCourses->take(6)->values();
        }

        $recentTdSets = collect();
        $allTdSets = collect();
        $tdAttempts = collect();
        $tdOpenedCount = 0;
        $tdCompletedCount = 0;
        $unopenedTdSets = collect();

        if ($classId && Schema::hasTable('td_sets')) {
            $tdQuery = TdSet::query()
                ->where('school_class_id', $classId)
                ->where('status', TdSet::STATUS_PUBLISHED)
                ->with('subject')
                ->latest('published_at');

            $allTdSets = (clone $tdQuery)->take(100)->get();
            $recentTdSets = $allTdSets->take(6)->values();

            if (Schema::hasTable('td_attempts')) {
                $tdAttempts = TdAttempt::query()
                    ->where('student_id', $user->id)
                    ->whereIn('td_set_id', $allTdSets->pluck('id'))
                    ->get();

                $tdOpenedCount = $tdAttempts->count();
                $tdCompletedCount = $tdAttempts->where('status', TdAttempt::STATUS_COMPLETED)->count();
            }

            $openedIds = $tdAttempts->pluck('td_set_id')->unique();
            $unopenedTdSets = $allTdSets->whereNotIn('id', $openedIds)->values();
        }

        $pendingQuizzes = collect();
        if (class_exists('App\\Models\\Quiz') && Schema::hasTable('quizzes') && Schema::hasTable('quiz_attempts') && $classId) {
            $quizModel = app('App\\Models\\Quiz');
            $pendingQuizzes = $quizModel::query()
                ->whereHas('subject.classSubject', fn ($q) => $q->where('school_class_id', $classId))
                ->where('status', 'published')
                ->whereDoesntHave('attempts', fn ($q) => $q->where('user_id', $user->id))
                ->take(6)
                ->get();
        }

        $totalResources = $allTdSets->count() + $allCourses->count() + $pendingQuizzes->count();
        $consultedResources = $tdOpenedCount;
        $progressPercent = $totalResources > 0 ? min(100, (int) round(($consultedResources / $totalResources) * 100)) : 0;
        $pendingCount = $unopenedTdSets->count() + $pendingQuizzes->count();

        $subjectStats = $allTdSets->merge($allCourses)
            ->groupBy(fn ($item) => $item->subject->name ?? 'Sans matière')
            ->map(fn ($items, $name) => ['name' => $name, 'count' => $items->count()])
            ->sortByDesc('count')
            ->take(6)
            ->values();

        $typeStats = collect([
            ['label' => 'TD', 'total' => $allTdSets->count(), 'pending' => $unopenedTdSets->count()],
            ['label' => 'Cours', 'total' => $allCourses->count(), 'pending' => 0],
            ['label' => 'Quiz', 'total' => $pendingQuizzes->count(), 'pending' => $pendingQuizzes->count()],
        ]);

        $weeklyActivity = collect(range(6, 0))->map(function ($daysAgo) use ($tdAttempts) {
            $day = now()->subDays($daysAgo);
            $value = $tdAttempts->filter(function ($attempt) use ($day) {
                $date = $attempt->opened_at ?: $attempt->created_at;
                return $date && $date->isSameDay($day);
            })->count();
            return ['label' => $day->locale('fr')->translatedFormat('D'), 'date' => $day->format('d/m'), 'value' => $value];
        })->push([
            'label' => now()->locale('fr')->translatedFormat('D'),
            'date' => now()->format('d/m'),
            'value' => $tdAttempts->filter(function ($attempt) {
                $date = $attempt->opened_at ?: $attempt->created_at;
                return $date && $date->isSameDay(now());
            })->count(),
        ]);

        $latestEvents = $allTdSets->map(fn ($td) => [
            'type' => 'TD',
            'title' => $td->title,
            'subject' => $td->subject->name ?? 'Matière',
            'date' => $td->published_at ?: $td->created_at,
            'access' => $td->access_level === TdSet::ACCESS_FREE ? 'Gratuit' : 'Premium',
            'route' => route('student.td.show', $td),
        ])->merge($allCourses->map(fn ($course) => [
            'type' => 'Cours',
            'title' => $course->title ?? $course->name ?? 'Cours',
            'subject' => $course->subject->name ?? 'Matière',
            'date' => $course->published_at ?: $course->created_at,
            'access' => 'Cours',
            'route' => route('student.courses.show', $course),
        ]))->sortByDesc(fn ($item) => optional($item['date'])->timestamp ?? 0)->take(8)->values();

        $pendingReminders = $unopenedTdSets->take(6)->map(fn ($td) => [
            'type' => 'TD non consulté',
            'title' => $td->title,
            'subject' => $td->subject->name ?? 'Matière',
            'date' => $td->published_at ?: $td->created_at,
            'priority' => $td->access_level === TdSet::ACCESS_FREE ? 'À ouvrir maintenant' : 'À consulter avec abonnement',
            'route' => route('student.td.show', $td),
        ])->values();

        return view('student.dashboard_v2', [
            'user' => $user,
            'studentProfile' => $studentProfile,
            'subscription' => $user->activeSubscription,
            'recentCourses' => $recentCourses,
            'recentTdSets' => $recentTdSets,
            'tdOpenedCount' => $tdOpenedCount,
            'tdCompletedCount' => $tdCompletedCount,
            'pendingQuizzes' => $pendingQuizzes,
            'dashboardText' => PlatformSetting::group('dashboard_student'),
            'allCoursesCount' => $allCourses->count(),
            'allTdCount' => $allTdSets->count(),
            'totalResources' => $totalResources,
            'consultedResources' => $consultedResources,
            'progressPercent' => $progressPercent,
            'pendingCount' => $pendingCount,
            'subjectStats' => $subjectStats,
            'typeStats' => $typeStats,
            'weeklyActivity' => $weeklyActivity,
            'latestEvents' => $latestEvents,
            'pendingReminders' => $pendingReminders,
        ]);
    }
}
