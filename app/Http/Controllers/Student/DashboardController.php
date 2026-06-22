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
        $className = $this->safeClassName($studentProfile);
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

        $pendingQuizzes = $this->loadPendingQuizzes($user?->id, $classId);

        $totalResources = $allTdSets->count() + $allCourses->count() + $pendingQuizzes->count();
        $consultedResources = $tdOpenedCount;
        $progressPercent = $totalResources > 0 ? min(100, (int) round(($consultedResources / $totalResources) * 100)) : 0;
        $pendingCount = $unopenedTdSets->count() + $pendingQuizzes->count();

        $subjectStats = $allTdSets->merge($allCourses)
            ->groupBy(fn ($item) => $this->subjectName($item))
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
                return $date && method_exists($date, 'isSameDay') && $date->isSameDay($day);
            })->count();

            return [
                'label' => $day->locale('fr')->translatedFormat('D'),
                'date' => $day->format('d/m'),
                'value' => $value,
            ];
        })->values();

        $latestEvents = $allTdSets->map(fn ($td) => [
            'type' => 'TD',
            'title' => $td->title ?? 'TD',
            'subject' => $this->subjectName($td),
            'date' => $this->publicationDate($td),
            'access' => ($td->access_level ?? null) === TdSet::ACCESS_FREE ? 'Gratuit' : 'Premium',
            'route' => route('student.td.show', $td),
        ])->merge($allCourses->map(fn ($course) => [
            'type' => 'Cours',
            'title' => $course->title ?? $course->name ?? 'Cours',
            'subject' => $this->subjectName($course),
            'date' => $this->publicationDate($course),
            'access' => 'Cours',
            'route' => route('student.courses.show', $course),
        ]))->sortByDesc(fn ($item) => optional($item['date'])->timestamp ?? 0)->take(8)->values();

        $pendingReminders = $unopenedTdSets->take(6)->map(fn ($td) => [
            'type' => 'TD non consulté',
            'title' => $td->title ?? 'TD',
            'subject' => $this->subjectName($td),
            'date' => $this->publicationDate($td),
            'priority' => ($td->access_level ?? null) === TdSet::ACCESS_FREE ? 'À ouvrir maintenant' : 'À consulter avec abonnement',
            'route' => route('student.td.show', $td),
        ])->values();

        return view('student.dashboard_v2', [
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
            'latestEvents' => $latestEvents,
            'pendingReminders' => $pendingReminders,
            'studentExamCountdown' => $studentExamCountdown,
        ]);
    }

    protected function loadCourses(?int $classId)
    {
        if (!$classId || !$this->hasTableWithColumns('courses', ['school_class_id'])) {
            return collect();
        }

        return $this->safe(function () use ($classId) {
            $query = Course::query()->where('school_class_id', $classId);

            if ($this->hasColumn('courses', 'status')) {
                $query->where('status', Course::STATUS_PUBLISHED);
            }

            if ($this->hasTableWithColumns('subjects', ['id']) && $this->hasColumn('courses', 'subject_id')) {
                $query->with('subject');
            }

            $query->orderByDesc($this->dateColumn('courses'));

            return $query->take(100)->get();
        }, collect());
    }

    protected function loadTdSets(?int $classId)
    {
        if (!$classId || !$this->hasTableWithColumns('td_sets', ['school_class_id'])) {
            return collect();
        }

        return $this->safe(function () use ($classId) {
            $query = TdSet::query()->where('school_class_id', $classId);

            if ($this->hasColumn('td_sets', 'status')) {
                $query->where('status', TdSet::STATUS_PUBLISHED);
            }

            if ($this->hasTableWithColumns('subjects', ['id']) && $this->hasColumn('td_sets', 'subject_id')) {
                $query->with('subject');
            }

            $query->orderByDesc($this->dateColumn('td_sets'));

            return $query->take(100)->get();
        }, collect());
    }

    protected function loadTdAttempts(?int $studentId, $tdIds)
    {
        $tdIds = collect($tdIds)->filter()->values();

        if (!$studentId || $tdIds->isEmpty() || !$this->hasTableWithColumns('td_attempts', ['student_id', 'td_set_id'])) {
            return collect();
        }

        return $this->safe(function () use ($studentId, $tdIds) {
            return TdAttempt::query()
                ->where('student_id', $studentId)
                ->whereIn('td_set_id', $tdIds)
                ->get();
        }, collect());
    }

    protected function loadPendingQuizzes(?int $studentId, ?int $classId)
    {
        if (!$studentId || !$classId || !class_exists('App\\Models\\Quiz')) {
            return collect();
        }

        if (!$this->hasTableWithColumns('quizzes', ['status']) || !$this->hasTableWithColumns('quiz_attempts', ['user_id'])) {
            return collect();
        }

        return $this->safe(function () use ($studentId, $classId) {
            $quizClass = 'App\\Models\\Quiz';

            return $quizClass::query()
                ->whereHas('subject.classSubject', fn ($q) => $q->where('school_class_id', $classId))
                ->where('status', 'published')
                ->whereDoesntHave('attempts', fn ($q) => $q->where('user_id', $studentId))
                ->take(6)
                ->get();
        }, collect());
    }

    protected function safeClassName($studentProfile): ?string
    {
        if (!$studentProfile || !$this->hasTableWithColumns('school_classes', ['id'])) {
            return null;
        }

        return $this->safe(fn () => $studentProfile->schoolClass?->name);
    }

    protected function safeActiveSubscription($user): ?Subscription
    {
        if (!$user || !$this->hasTableWithColumns('subscriptions', ['user_id', 'status'])) {
            return null;
        }

        return $this->safe(function () use ($user) {
            $query = $user->subscriptions()->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL]);

            if ($this->hasColumn('subscriptions', 'ends_at')) {
                $query->where(function ($builder) {
                    $builder->whereNull('ends_at')->orWhere('ends_at', '>', now());
                });
            }

            return $query->first();
        });
    }

    protected function subjectName($item): string
    {
        if (method_exists($item, 'relationLoaded') && $item->relationLoaded('subject')) {
            return $item->subject->name ?? 'Sans matière';
        }

        return 'Sans matière';
    }

    protected function publicationDate($item)
    {
        return $item->published_at ?? $item->created_at ?? null;
    }

    protected function dateColumn(string $table): string
    {
        foreach (['published_at', 'created_at', 'updated_at', 'id'] as $column) {
            if ($this->hasColumn($table, $column)) {
                return $column;
            }
        }

        return 'id';
    }

    protected function hasTableWithColumns(string $table, array $columns = []): bool
    {
        if (!$this->safe(fn () => Schema::hasTable($table), false)) {
            return false;
        }

        foreach ($columns as $column) {
            if (!$this->hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    protected function hasColumn(string $table, string $column): bool
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
