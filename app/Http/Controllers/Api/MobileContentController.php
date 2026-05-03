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
use Illuminate\Support\Collection;
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

        $user->loadMissing('studentProfile.schoolClass');
        $this->seedDefaultContentIfEmpty();

        $allProgram = $this->programItems($user);
        $todayWeekday = (int) now()->isoWeekday();
        $today = $allProgram
            ->filter(fn ($item) => (int) ($item->weekday ?? 0) === $todayWeekday || $this->isCurrentlyOpen($item))
            ->values()
            ->take(8)
            ->map(fn ($item) => $this->serializeProgram($item));

        if ($today->isEmpty()) {
            $today = $allProgram
                ->filter(fn ($item) => !$this->isClosed($item))
                ->values()
                ->take(3)
                ->map(fn ($item) => $this->serializeProgram($item));
        }

        $program = $allProgram
            ->take(14)
            ->map(fn ($item) => $this->serializeProgram($item));

        $board = $this->boardItems($user)
            ->take(8)
            ->map(fn ($item) => $this->serializeBoardPost($item));

        $evaluations = $this->evaluationItems($user);
        $nextEvaluation = $evaluations->first();
        $latestReport = $this->reportItems($user)->first();
        $subscription = $this->activeSubscription($user);

        return response()->json([
            'status' => 'ok',
            'message' => 'Accueil mobile TIMAH ACADEMY chargé.',
            'user' => $this->serializeUser($user),
            'subscription' => $this->serializeSubscription($subscription),
            'access' => $this->serializeAccess($subscription),
            'device' => $this->serializeDevice($this->activeDevice($user)),
            'summary' => [
                'today_count' => $today->count(),
                'week_program_count' => $program->count(),
                'board_count' => $board->count(),
                'evaluations_count' => $evaluations->count(),
                'has_report' => (bool) $latestReport,
            ],
            'today' => $today,
            'program' => $program,
            'board' => $board,
            'next_evaluation' => $nextEvaluation ? $this->serializeEvaluation($nextEvaluation) : $this->fallbackEvaluation(),
            'latest_report' => $latestReport ? $this->serializeReport($latestReport) : $this->fallbackReport(),
            'quick_actions' => [
                ['title' => 'Continuer le programme', 'subtitle' => 'Ouvrir les activités disponibles aujourd’hui.', 'target' => 'program'],
                ['title' => 'Lire le babillard', 'subtitle' => 'Voir les annonces et rappels importants.', 'target' => 'board'],
                ['title' => 'Préparer l’évaluation', 'subtitle' => 'Consulter la prochaine évaluation de progression.', 'target' => 'evaluations'],
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

        $items = $this->programItems($user)
            ->take(60)
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

        $items = $this->boardItems($user)
            ->take(50)
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

        $items = $this->reportItems($user)
            ->take(20)
            ->map(fn ($item) => $this->serializeReport($item));

        if ($items->isEmpty()) {
            $items = collect([$this->fallbackReport()]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Rapports chargés.',
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

        $items = $this->evaluationItems($user)
            ->take(20)
            ->map(fn ($item) => $this->serializeEvaluation($item));

        if ($items->isEmpty()) {
            $items = collect([$this->fallbackEvaluation()]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Évaluations chargées.',
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

    private function programItems(User $user): Collection
    {
        if (!Schema::hasTable('learning_program_schedules')) {
            return collect();
        }

        $classId = $user->studentProfile?->school_class_id;

        return LearningProgramSchedule::query()
            ->with(['schoolClass', 'subject'])
            ->whereIn('status', ['scheduled', 'published', 'open'])
            ->when($classId, function ($query) use ($classId) {
                $query->where(function ($sub) use ($classId) {
                    $sub->whereNull('school_class_id')->orWhere('school_class_id', $classId);
                });
            })
            ->orderBy('week_number')
            ->orderBy('weekday')
            ->orderBy('unlock_time')
            ->orderBy('id')
            ->get();
    }

    private function boardItems(User $user): Collection
    {
        if (!Schema::hasTable('digital_board_posts')) {
            return collect();
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
            ->latest('id')
            ->get();
    }

    private function reportItems(User $user): Collection
    {
        if (!Schema::hasTable('progress_reports')) {
            return collect();
        }

        return ProgressReport::query()
            ->with('schoolClass')
            ->where('student_id', $user->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->latest('id')
            ->get();
    }

    private function evaluationItems(User $user): Collection
    {
        if (!Schema::hasTable('biweekly_evaluations')) {
            return collect();
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
            ->orderBy('id')
            ->get();
    }

    private function seedDefaultContentIfEmpty(): void
    {
        if (Schema::hasTable('digital_board_posts') && DigitalBoardPost::query()->count() === 0) {
            $posts = [
                ['Bienvenue sur TIMAH ACADEMY', 'TIMAH ACADEMY est un répétiteur numérique conçu pour accompagner l’apprenant dans ses révisions, ses TD, ses quiz et son suivi de progression.'],
                ['Comment utiliser la plateforme', 'Chaque semaine, l’apprenant suit les cours programmés, traite les TD, répond aux quiz et prépare l’évaluation de progression.'],
                ['Suivi parent', 'Les parents consultent les rapports publiés dans l’application. WhatsApp sert seulement à prévenir gratuitement lorsqu’un rapport ou un rappel est disponible.'],
                ['Règle d’accès mobile', 'Un numéro WhatsApp correspond à un seul compte, un seul essai gratuit de 24h, un abonnement actif et un appareil autorisé.'],
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
                [1, 'Mathématiques — Cours programmé', 'course', 'Comprendre la notion du jour avec une explication simple, puis traiter des exemples guidés.', '18:00'],
                [2, 'Français — Cours programmé', 'course', 'Lire, comprendre, analyser et s’exercer avec une méthode progressive.', '18:00'],
                [3, 'Anglais — Cours programmé', 'course', 'Renforcer le vocabulaire, la grammaire et l’expression écrite ou orale.', '18:00'],
                [4, 'PCT / Informatique — Cours programmé', 'course', 'Découvrir une notion scientifique ou numérique utile et l’appliquer.', '18:00'],
                [5, 'Révision guidée de la semaine', 'revision', 'Reprendre les points importants avant les exercices du week-end.', '18:00'],
                [6, 'TD de la semaine', 'td', 'S’entraîner avec des exercices progressifs pour consolider les acquis.', '10:00'],
                [7, 'Quiz de consolidation', 'quiz', 'Vérifier rapidement ce qui est compris avant la nouvelle semaine.', '15:00'],
            ];

            foreach ($items as [$day, $title, $type, $description, $time]) {
                [$hour, $minute] = array_map('intval', explode(':', $time));
                LearningProgramSchedule::query()->create([
                    'title' => $title,
                    'description' => $description,
                    'activity_type' => $type,
                    'week_number' => 1,
                    'weekday' => $day,
                    'unlock_time' => $time,
                    'unlocks_at' => now()->startOfWeek()->addDays($day - 1)->setTime($hour, $minute),
                    'duration_minutes' => $type === 'td' ? 120 : 60,
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
        $locked = $this->isLocked($item);
        $closed = $this->isClosed($item);

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'type' => $item->activity_type,
            'type_label' => $this->activityLabel($item->activity_type),
            'week_number' => $item->week_number,
            'weekday' => $item->weekday,
            'weekday_label' => $this->weekdayLabel((int) $item->weekday),
            'unlock_time' => $item->unlock_time,
            'unlocks_at' => $item->unlocks_at?->toIso8601String(),
            'closes_at' => $item->closes_at?->toIso8601String(),
            'duration_minutes' => $item->duration_minutes,
            'locked' => $locked,
            'closed' => $closed,
            'access_status' => $closed ? 'closed' : ($locked ? 'locked' : 'unlocked'),
            'access_label' => $closed ? 'Clôturé' : ($locked ? 'Verrouillé' : 'Disponible'),
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
            'status_label' => $closed ? 'Clôturée' : ($open ? 'Ouverte' : 'À venir'),
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

    private function serializeAccess(?Subscription $subscription): array
    {
        if (!$subscription) {
            return [
                'status' => 'expired',
                'label' => 'Accès expiré',
                'message' => 'Votre accès complet est expiré. Contact WhatsApp abonnement : 670 00 00 00.',
                'can_access' => false,
            ];
        }

        $isTrial = (bool) $subscription->is_trial;

        return [
            'status' => $subscription->status,
            'label' => $isTrial ? 'Essai gratuit actif' : 'Abonnement actif',
            'message' => $isTrial ? 'Votre essai gratuit de 24h est actif.' : 'Votre abonnement TIMAH ACADEMY est actif.',
            'can_access' => true,
            'ends_at' => $subscription->ends_at?->toIso8601String(),
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

    private function fallbackEvaluation(): array
    {
        return [
            'id' => null,
            'title' => 'Évaluation bimensuelle à venir',
            'description' => 'La prochaine évaluation de progression sera publiée dans l’application dès que le programme sera validé.',
            'status' => 'upcoming',
            'status_label' => 'À venir',
            'duration_minutes' => 120,
        ];
    }

    private function fallbackReport(): array
    {
        return [
            'id' => null,
            'participation_rate' => 0,
            'evaluation_score' => null,
            'courses_done' => 0,
            'td_done' => 0,
            'quizzes_done' => 0,
            'strengths' => 'Aucun rapport publié pour le moment.',
            'weaknesses' => null,
            'recommendations' => 'Le prochain rapport sera disponible après l’évaluation bimensuelle.',
            'published_at' => null,
        ];
    }

    private function isLocked(LearningProgramSchedule $item): bool
    {
        return $item->unlocks_at && $item->unlocks_at->gt(now());
    }

    private function isClosed(LearningProgramSchedule $item): bool
    {
        return $item->closes_at && $item->closes_at->lt(now());
    }

    private function isCurrentlyOpen(LearningProgramSchedule $item): bool
    {
        return !$this->isLocked($item) && !$this->isClosed($item);
    }

    private function weekdayLabel(int $weekday): string
    {
        return [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche',
        ][$weekday] ?? 'Jour ' . $weekday;
    }

    private function activityLabel(?string $type): string
    {
        return [
            'course' => 'Cours programmé',
            'td' => 'TD',
            'quiz' => 'Quiz',
            'evaluation' => 'Évaluation',
            'revision' => 'Révision guidée',
        ][$type] ?? 'Activité';
    }
}
