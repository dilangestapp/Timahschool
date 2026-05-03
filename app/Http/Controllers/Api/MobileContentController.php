<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiweeklyEvaluation;
use App\Models\DigitalBoardPost;
use App\Models\LearningProgramSchedule;
use App\Models\ProgressReport;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MobileContentController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $this->seedDefaultContentIfEmpty();

        $today = $this->programQuery($user)
            ->where(function ($query) {
                $query->whereNull('unlocks_at')
                    ->orWhereDate('unlocks_at', today());
            })
            ->take(6)
            ->get()
            ->map(fn ($item) => $this->serializeProgram($item));

        $program = $this->programQuery($user)
            ->take(12)
            ->get()
            ->map(fn ($item) => $this->serializeProgram($item));

        $board = $this->boardQuery($user)
            ->take(5)
            ->get()
            ->map(fn ($item) => $this->serializeBoardPost($item));

        $nextEvaluation = $this->evaluationQuery($user)->first();
        $latestReport = $this->reportQuery($user)->first();

        return response()->json([
            'status' => 'ok',
            'message' => 'Accueil mobile TIMAH ACADEMY chargé.',
            'user' => $this->serializeUser($user->loadMissing('studentProfile.schoolClass')),
            'subscription' => $this->serializeSubscription($this->activeSubscription($user)),
            'device' => $this->serializeDevice($this->activeDevice($user)),
            'today' => $today,
            'program' => $program,
            'board' => $board,
            'next_evaluation' => $nextEvaluation ? $this->serializeEvaluation($nextEvaluation) : null,
            'latest_report' => $latestReport ? $this->serializeReport($latestReport) : null,
            'empty_state' => [
                'program' => $program->isEmpty() ? 'Aucune activité programmée pour le moment.' : null,
                'board' => $board->isEmpty() ? 'Aucune annonce publiée pour le moment.' : null,
                'reports' => !$latestReport ? 'Aucun rapport publié pour le moment.' : null,
            ],
        ]);
    }

    public function program(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $this->seedDefaultContentIfEmpty();

        $items = $this->programQuery($user)
            ->take(50)
            ->get()
            ->map(fn ($item) => $this->serializeProgram($item));

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty() ? 'Aucune activité programmée pour le moment.' : 'Programme chargé.',
            'items' => $items,
        ]);
    }

    public function board(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $this->seedDefaultContentIfEmpty();

        $items = $this->boardQuery($user)
            ->take(50)
            ->get()
            ->map(fn ($item) => $this->serializeBoardPost($item));

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty() ? 'Aucune publication disponible pour le moment.' : 'Babillard chargé.',
            'items' => $items,
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $items = $this->reportQuery($user)
            ->take(20)
            ->get()
            ->map(fn ($item) => $this->serializeReport($item));

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty()
                ? 'Aucun rapport publié pour le moment. Le prochain rapport sera disponible après l’évaluation bimensuelle.'
                : 'Rapports chargés.',
            'items' => $items,
        ]);
    }

    public function evaluations(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $this->seedDefaultContentIfEmpty();

        $items = $this->evaluationQuery($user)
            ->take(20)
            ->get()
            ->map(fn ($item) => $this->serializeEvaluation($item));

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty() ? 'Aucune évaluation programmée pour le moment.' : 'Évaluations chargées.',
            'items' => $items,
        ]);
    }

    private function userFromBearer(Request $request): ?User
    {
        $header = $request->header('Authorization', '');
        $token = Str::startsWith($header, 'Bearer ') ? trim(Str::after($header, 'Bearer ')) : '';
        if ($token === '') {
            return null;
        }

        return User::query()->where('remember_token', hash('sha256', $token))->first();
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'status' => 'unauthenticated',
            'message' => 'Session mobile expirée. Veuillez vous reconnecter.',
        ], 401);
    }

    private function programQuery(User $user)
    {
        if (!Schema::hasTable('learning_program_schedules')) {
            return collect()->toQuery();
        }

        $classId = $user->studentProfile?->school_class_id;

        return LearningProgramSchedule::query()
            ->with(['schoolClass', 'subject'])
            ->whereIn('status', ['scheduled', 'published'])
            ->when($classId, function ($query) use ($classId) {
                $query->where(function ($sub) use ($classId) {
                    $sub->whereNull('school_class_id')->orWhere('school_class_id', $classId);
                });
            })
            ->orderByRaw('CASE WHEN unlocks_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('unlocks_at')
            ->orderBy('week_number')
            ->orderBy('weekday')
            ->orderBy('unlock_time');
    }

    private function boardQuery(User $user)
    {
        if (!Schema::hasTable('digital_board_posts')) {
            return collect()->toQuery();
        }

        $classId = $user->studentProfile?->school_class_id;

        return DigitalBoardPost::query()
            ->with('schoolClass')
            ->visible()
            ->whereIn('audience', ['all', 'student', 'parent'])
            ->when($classId, function ($query) use ($classId) {
                $query->where(function ($sub) use ($classId) {
                    $sub->whereNull('school_class_id')->orWhere('school_class_id', $classId);
                });
            })
            ->latest('published_at')
            ->latest('id');
    }

    private function reportQuery(User $user)
    {
        if (!Schema::hasTable('progress_reports')) {
            return collect()->toQuery();
        }

        return ProgressReport::query()
            ->with('schoolClass')
            ->where('student_id', $user->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->latest('id');
    }

    private function evaluationQuery(User $user)
    {
        if (!Schema::hasTable('biweekly_evaluations')) {
            return collect()->toQuery();
        }

        $classId = $user->studentProfile?->school_class_id;

        return BiweeklyEvaluation::query()
            ->with('schoolClass')
            ->whereIn('status', ['scheduled', 'published', 'open'])
            ->when($classId, function ($query) use ($classId) {
                $query->where(function ($sub) use ($classId) {
                    $sub->whereNull('school_class_id')->orWhere('school_class_id', $classId);
                });
            })
            ->orderByRaw('CASE WHEN opens_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('opens_at')
            ->orderBy('id');
    }

    private function seedDefaultContentIfEmpty(): void
    {
        if (Schema::hasTable('digital_board_posts') && DigitalBoardPost::query()->count() === 0) {
            $posts = [
                ['Bienvenue sur TIMAH ACADEMY', 'TIMAH ACADEMY est un répétiteur numérique conçu pour accompagner l’apprenant dans ses révisions, ses TD, ses quiz et son suivi de progression.'],
                ['Fonctionnement du répétiteur numérique', 'Les cours se débloquent selon un programme. Les TD renforcent les notions, les quiz vérifient la compréhension et les rapports aident les parents à suivre les progrès.'],
                ['Évaluations bimensuelles et rapports', 'Toutes les deux semaines, une évaluation de progression permet de mesurer les efforts. Le rapport est publié sur le babillard numérique.'],
            ];

            foreach ($posts as [$title, $content]) {
                DigitalBoardPost::query()->create([
                    'title' => $title,
                    'content' => $content,
                    'type' => DigitalBoardPost::TYPE_ANNOUNCEMENT,
                    'audience' => 'all',
                    'status' => DigitalBoardPost::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('learning_program_schedules') && LearningProgramSchedule::query()->count() === 0) {
            $items = [
                [1, 'Mathématiques — Cours programmé', 'course', 'Comprendre une notion clé et traiter des exemples guidés.'],
                [2, 'Français — Cours programmé', 'course', 'Lire, comprendre et s’exercer avec une méthode simple.'],
                [3, 'Anglais — Cours programmé', 'course', 'Renforcer le vocabulaire, la grammaire et l’expression.'],
                [4, 'PCT / Informatique — Cours programmé', 'course', 'Découvrir une notion scientifique ou numérique utile.'],
                [5, 'Révision guidée de la semaine', 'course', 'Reprendre les points importants avant les exercices.'],
                [6, 'TD de la semaine', 'td', 'S’entraîner avec des exercices progressifs.'],
                [7, 'Quiz de consolidation', 'quiz', 'Vérifier rapidement ce qui est compris avant la nouvelle semaine.'],
            ];

            foreach ($items as [$day, $title, $type, $description]) {
                LearningProgramSchedule::query()->create([
                    'title' => $title,
                    'description' => $description,
                    'activity_type' => $type,
                    'week_number' => 1,
                    'weekday' => $day,
                    'unlock_time' => $day >= 6 ? '10:00' : '18:00',
                    'unlocks_at' => now()->startOfWeek()->addDays($day - 1)->setTime($day >= 6 ? 10 : 18, 0),
                    'status' => 'published',
                    'requires_subscription' => true,
                ]);
            }
        }

        if (Schema::hasTable('biweekly_evaluations') && BiweeklyEvaluation::query()->count() === 0) {
            BiweeklyEvaluation::query()->create([
                'title' => 'Évaluation de progression — Période test',
                'description' => 'Évaluation bimensuelle permettant de mesurer les progrès réalisés dans le programme de répétition numérique.',
                'period_starts_at' => now()->startOfWeek(),
                'period_ends_at' => now()->startOfWeek()->addDays(13),
                'opens_at' => now()->startOfWeek()->addDays(13)->setTime(15, 0),
                'closes_at' => now()->startOfWeek()->addDays(13)->setTime(18, 0),
                'duration_minutes' => 120,
                'status' => 'published',
            ]);
        }
    }

    private function serializeProgram(LearningProgramSchedule $item): array
    {
        $now = now();
        $locked = $item->unlocks_at && $item->unlocks_at->gt($now);
        $closed = $item->closes_at && $item->closes_at->lt($now);

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'type' => $item->activity_type,
            'week_number' => $item->week_number,
            'weekday' => $item->weekday,
            'unlock_time' => $item->unlock_time,
            'unlocks_at' => $item->unlocks_at?->toIso8601String(),
            'closes_at' => $item->closes_at?->toIso8601String(),
            'duration_minutes' => $item->duration_minutes,
            'locked' => (bool) $locked,
            'closed' => (bool) $closed,
            'access_status' => $closed ? 'closed' : ($locked ? 'locked' : 'unlocked'),
            'subject' => $item->subject?->name,
            'class' => $item->schoolClass?->name,
        ];
    }

    private function serializeBoardPost(DigitalBoardPost $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'content' => $item->content,
            'type' => $item->type,
            'audience' => $item->audience,
            'published_at' => $item->published_at?->toIso8601String(),
            'class' => $item->schoolClass?->name,
        ];
    }

    private function serializeEvaluation(BiweeklyEvaluation $item): array
    {
        $now = now();
        $open = (!$item->opens_at || $item->opens_at->lte($now)) && (!$item->closes_at || $item->closes_at->gte($now));
        $closed = $item->closes_at && $item->closes_at->lt($now);

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'period_starts_at' => $item->period_starts_at?->toIso8601String(),
            'period_ends_at' => $item->period_ends_at?->toIso8601String(),
            'opens_at' => $item->opens_at?->toIso8601String(),
            'closes_at' => $item->closes_at?->toIso8601String(),
            'duration_minutes' => $item->duration_minutes,
            'status' => $closed ? 'closed' : ($open ? 'open' : 'upcoming'),
            'class' => $item->schoolClass?->name,
        ];
    }

    private function serializeReport(ProgressReport $item): array
    {
        return [
            'id' => $item->id,
            'period_starts_at' => $item->period_starts_at?->toIso8601String(),
            'period_ends_at' => $item->period_ends_at?->toIso8601String(),
            'participation_rate' => $item->participation_rate,
            'evaluation_score' => $item->evaluation_score,
            'courses_done' => $item->courses_done,
            'td_done' => $item->td_done,
            'quizzes_done' => $item->quizzes_done,
            'strengths' => $item->strengths,
            'weaknesses' => $item->weaknesses,
            'recommendations' => $item->recommendations,
            'published_at' => $item->published_at?->toIso8601String(),
            'class' => $item->schoolClass?->name,
        ];
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name ?: $user->name,
            'phone' => $user->phone,
            'class' => $user->studentProfile?->schoolClass?->name,
        ];
    }

    private function activeSubscription(User $user): ?Subscription
    {
        if (!Schema::hasTable('subscriptions')) {
            return null;
        }

        return $user->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    private function serializeSubscription(?Subscription $subscription): ?array
    {
        if (!$subscription) {
            return null;
        }

        return [
            'id' => $subscription->id,
            'plan_name' => $subscription->plan_name,
            'status' => $subscription->status,
            'is_trial' => (bool) $subscription->is_trial,
            'starts_at' => $subscription->starts_at?->toIso8601String(),
            'ends_at' => $subscription->ends_at?->toIso8601String(),
            'is_active' => method_exists($subscription, 'isActive') ? $subscription->isActive() : true,
        ];
    }

    private function activeDevice(User $user): ?object
    {
        if (!Schema::hasTable('mobile_devices')) {
            return null;
        }

        return $user->mobileDevices()->active()->first();
    }

    private function serializeDevice(?object $device): ?array
    {
        if (!$device) {
            return null;
        }

        return [
            'id' => $device->id,
            'device_name' => $device->device_name,
            'device_model' => $device->device_model,
            'platform' => $device->platform,
            'app_version' => $device->app_version,
            'status' => $device->status,
            'first_login_at' => $device->first_login_at?->toIso8601String(),
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ];
    }
}
