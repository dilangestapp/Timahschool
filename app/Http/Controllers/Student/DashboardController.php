<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Support\ExamCountdown;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $studentProfile = $this->safe(fn () => $user?->studentProfile);
        $classId = $studentProfile?->school_class_id;
        $className = $this->safe(fn () => $studentProfile?->schoolClass?->name);
        $studentExamCountdown = $this->safe(fn () => ExamCountdown::forClass($className));

        $allCourses = $this->loadCourses($classId);
        $recentCourses = $allCourses->take(6)->values();

        $allTdSets = $this->loadTdSets($classId);
        $recentTdSets = $allTdSets->take(6)->values();

        $tdAttempts = $this->loadTdAttempts($user?->id, $allTdSets->pluck('id'));
        $tdOpenedCount = $tdAttempts->count();
        $tdCompletedCount = $tdAttempts->where('status', TdAttempt::STATUS_COMPLETED)->count();

        $openedIds = $tdAttempts->pluck('td_set_id')->unique();
        $unopenedTdSets = $allTdSets->whereNotIn('id', $openedIds)->values();
        $pendingQuizzes = collect();

        $totalResources = $allTdSets->count() + $allCourses->count();
        $consultedResources = $tdOpenedCount;
        $progressPercent = $totalResources > 0 ? min(100, (int) round(($consultedResources / $totalResources) * 100)) : 0;
        $pendingCount = $unopenedTdSets->count();

        $subjectStats = collect();
        foreach ($allTdSets->toBase() as $td) {
            $subjectStats->push(['name' => $td->subject->name ?? 'Sans matiere', 'count' => 1]);
        }
        foreach ($allCourses->toBase() as $course) {
            $subjectStats->push(['name' => $course->subject->name ?? 'Sans matiere', 'count' => 1]);
        }
        $subjectStats = $subjectStats->groupBy('name')->map(fn ($items, $name) => ['name' => $name, 'count' => $items->sum('count')])->values();

        $typeStats = collect([
            ['label' => 'TD', 'total' => $allTdSets->count(), 'pending' => $unopenedTdSets->count()],
            ['label' => 'Cours', 'total' => $allCourses->count(), 'pending' => 0],
            ['label' => 'Quiz', 'total' => 0, 'pending' => 0],
        ]);

        $weeklyActivity = collect(range(6, 0))->map(fn ($daysAgo) => [
            'label' => now()->subDays($daysAgo)->locale('fr')->translatedFormat('D'),
            'date' => now()->subDays($daysAgo)->format('d/m'),
            'value' => 0,
        ])->values();

        return view('student.dashboard', [
            'user' => $user,
            'studentProfile' => $studentProfile,
            'subscription' => $this->safeActiveSubscription($user),
            'recentCourses' => $recentCourses,
            'recentTdSets' => $recentTdSets,
            'tdOpenedCount' => $tdOpenedCount,
            'tdCompletedCount' => $tdCompletedCount,
            'pendingQuizzes' => $pendingQuizzes,
            'dashboardText' => $this->safe(fn () => PlatformSetting::group('dashboard_student'), []),
            'allCoursesCount' => $allCourses->count(),
            'allTdCount' => $allTdSets->count(),
            'totalResources' => $totalResources,
            'consultedResources' => $consultedResources,
            'progressPercent' => $progressPercent,
            'pendingCount' => $pendingCount,
            'subjectStats' => $subjectStats,
            'typeStats' => $typeStats,
            'weeklyActivity' => $weeklyActivity,
            'latestEvents' => collect(),
            'pendingReminders' => collect(),
            'studentExamCountdown' => $studentExamCountdown,
        ]);
    }

    protected function loadCourses(?int $classId)
    {
        if (!$classId || !$this->tableReady('courses', ['school_class_id'])) return collect();
        return $this->safe(function () use ($classId) {
            $query = Course::query()->where('school_class_id', $classId);
            if ($this->columnReady('courses', 'status')) $query->where('status', Course::STATUS_PUBLISHED);
            if ($this->columnReady('courses', 'subject_id')) $query->with('subject');
            if ($this->columnReady('courses', 'published_at')) $query->orderByDesc('published_at');
            return $query->take(100)->get();
        }, collect());
    }

    protected function loadTdSets(?int $classId)
    {
        if (!$classId || !$this->tableReady('td_sets', ['school_class_id'])) return collect();
        return $this->safe(function () use ($classId) {
            $query = TdSet::query()->where('school_class_id', $classId);
            if ($this->columnReady('td_sets', 'status')) $query->where('status', TdSet::STATUS_PUBLISHED);
            if ($this->columnReady('td_sets', 'subject_id')) $query->with('subject');
            if ($this->columnReady('td_sets', 'published_at')) $query->orderByDesc('published_at');
            return $query->take(100)->get();
        }, collect());
    }

    protected function loadTdAttempts(?int $studentId, $tdIds)
    {
        $tdIds = collect($tdIds)->filter()->values();
        if (!$studentId || $tdIds->isEmpty() || !$this->tableReady('td_attempts', ['student_id', 'td_set_id'])) return collect();
        return $this->safe(fn () => TdAttempt::query()->where('student_id', $studentId)->whereIn('td_set_id', $tdIds)->get(), collect());
    }

    protected function safeActiveSubscription($user): ?Subscription
    {
        if (!$user || !$this->tableReady('subscriptions', ['user_id', 'status'])) return null;
        return $this->safe(fn () => $user->subscriptions()->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL])->first());
    }

    protected function tableReady(string $table, array $columns = []): bool
    {
        if (!$this->safe(fn () => Schema::hasTable($table), false)) return false;
        foreach ($columns as $column) {
            if (!$this->columnReady($table, $column)) return false;
        }
        return true;
    }

    protected function columnReady(string $table, string $column): bool
    {
        return (bool) $this->safe(fn () => Schema::hasColumn($table, $column), false);
    }

    protected function safe(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            report($e);
            return $default;
        }
    }
}
