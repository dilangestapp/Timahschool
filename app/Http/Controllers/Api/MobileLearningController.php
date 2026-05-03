<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiweeklyEvaluation;
use App\Models\DigitalBoardPost;
use App\Models\LearningProgramSchedule;
use App\Models\MobileQuiz;
use App\Models\MobileQuizAttempt;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MobileLearningController extends Controller
{
    public function programDetail(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $item = LearningProgramSchedule::query()->with(['schoolClass', 'subject'])->find($id);
        if (!$item) return response()->json(['status' => 'not_found', 'message' => 'Activité introuvable.'], 404);

        $this->recordProgress($user, $item, 'started');

        return response()->json([
            'status' => 'ok',
            'item' => $this->serializeProgram($item),
            'content' => [
                'title' => $item->title,
                'body' => $item->description ?: 'Le contenu détaillé de cette activité sera complété depuis l’administration TIMAH ACADEMY.',
                'method' => 'Lire la consigne, traiter les exemples, puis continuer avec les exercices proposés.',
            ],
        ]);
    }

    public function completeProgram(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $item = LearningProgramSchedule::query()->find($id);
        if (!$item) return response()->json(['status' => 'not_found', 'message' => 'Activité introuvable.'], 404);

        $this->recordProgress($user, $item, 'completed');

        return response()->json(['status' => 'ok', 'message' => 'Activité marquée comme terminée.']);
    }

    public function boardDetail(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $post = DigitalBoardPost::query()->with('schoolClass')->find($id);
        if (!$post) return response()->json(['status' => 'not_found', 'message' => 'Publication introuvable.'], 404);

        return response()->json(['status' => 'ok', 'item' => $this->serializeBoard($post)]);
    }

    public function evaluationDetail(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $item = BiweeklyEvaluation::query()->with('schoolClass')->find($id);
        if (!$item) return response()->json(['status' => 'not_found', 'message' => 'Évaluation introuvable.'], 404);

        return response()->json(['status' => 'ok', 'item' => $this->serializeEvaluation($item)]);
    }

    public function reportDetail(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $report = ProgressReport::query()->where('student_id', $user->id)->find($id);
        if (!$report) return response()->json(['status' => 'not_found', 'message' => 'Rapport introuvable.'], 404);

        return response()->json(['status' => 'ok', 'item' => $this->serializeReport($report)]);
    }

    public function quizzes(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $this->seedDefaultQuizIfEmpty();

        if (!Schema::hasTable('mobile_quizzes')) {
            return response()->json(['status' => 'ok', 'items' => [], 'message' => 'Module quiz non initialisé.']);
        }

        $classId = $user->studentProfile?->school_class_id;
        $items = MobileQuiz::query()
            ->with(['subject', 'schoolClass'])
            ->whereIn('status', ['published', 'open'])
            ->when($classId, fn ($query) => $query->where(fn ($sub) => $sub->whereNull('school_class_id')->orWhere('school_class_id', $classId)))
            ->latest('opens_at')
            ->latest('id')
            ->get()
            ->map(fn ($quiz) => $this->serializeQuiz($quiz, false));

        return response()->json(['status' => 'ok', 'items' => $items]);
    }

    public function quizDetail(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $quiz = MobileQuiz::query()->with(['questions', 'subject', 'schoolClass'])->find($id);
        if (!$quiz) return response()->json(['status' => 'not_found', 'message' => 'Quiz introuvable.'], 404);

        return response()->json(['status' => 'ok', 'item' => $this->serializeQuiz($quiz, true)]);
    }

    public function submitQuiz(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $quiz = MobileQuiz::query()->with('questions')->find($id);
        if (!$quiz) return response()->json(['status' => 'not_found', 'message' => 'Quiz introuvable.'], 404);

        $answers = $request->input('answers', []);
        if (!is_array($answers)) $answers = [];

        $score = 0;
        $max = 0;
        $corrections = [];

        foreach ($quiz->questions as $question) {
            $max += (int) ($question->points ?? 1);
            $given = (string) ($answers[$question->id] ?? '');
            $correct = (string) ($question->correct_answer ?? '');
            $isCorrect = mb_strtolower(trim($given)) === mb_strtolower(trim($correct));
            if ($isCorrect) $score += (int) ($question->points ?? 1);
            $corrections[] = [
                'question_id' => $question->id,
                'given' => $given,
                'correct_answer' => $correct,
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation,
            ];
        }

        $percentage = $max > 0 ? (int) round(($score / $max) * 100) : 0;

        $attempt = MobileQuizAttempt::query()->create([
            'user_id' => $user->id,
            'mobile_quiz_id' => $quiz->id,
            'answers' => ['answers' => $answers, 'corrections' => $corrections],
            'score' => $score,
            'max_score' => $max,
            'percentage' => $percentage,
            'status' => 'submitted',
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Quiz corrigé automatiquement.',
            'attempt' => [
                'id' => $attempt->id,
                'score' => $score,
                'max_score' => $max,
                'percentage' => $percentage,
                'corrections' => $corrections,
            ],
        ]);
    }

    public function quizHistory(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $items = MobileQuizAttempt::query()
            ->with('quiz')
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->take(30)
            ->get()
            ->map(fn ($attempt) => [
                'id' => $attempt->id,
                'quiz_title' => $attempt->quiz?->title,
                'score' => $attempt->score,
                'max_score' => $attempt->max_score ?? $attempt->total_questions,
                'percentage' => $attempt->percentage ?? null,
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            ]);

        return response()->json(['status' => 'ok', 'items' => $items]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) return $this->unauthenticated();

        $items = collect();
        if (Schema::hasTable('mobile_notifications')) {
            $classId = $user->studentProfile?->school_class_id;
            $items = DB::table('mobile_notifications')
                ->where(function ($query) use ($user, $classId) {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                    if ($classId) {
                        $query->orWhere('school_class_id', $classId);
                    }
                })
                ->where(function ($query) {
                    $query->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get();
        }

        if ($items->isEmpty()) {
            $items = collect([
                ['id' => null, 'title' => 'Bienvenue sur TIMAH ACADEMY', 'message' => 'Votre répétiteur numérique est prêt. Consultez le programme et le babillard.', 'type' => 'info'],
            ]);
        }

        return response()->json(['status' => 'ok', 'items' => $items]);
    }

    private function userFromBearer(Request $request): ?User
    {
        $header = $request->header('Authorization', '');
        $token = Str::startsWith($header, 'Bearer ') ? trim(Str::after($header, 'Bearer ')) : '';
        if ($token === '') return null;
        return User::query()->where('remember_token', hash('sha256', $token))->first();
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json(['status' => 'unauthenticated', 'message' => 'Session mobile expirée. Veuillez vous reconnecter.'], 401);
    }

    private function recordProgress(User $user, LearningProgramSchedule $item, string $status): void
    {
        if (!Schema::hasTable('mobile_activity_progress')) return;
        DB::table('mobile_activity_progress')->updateOrInsert(
            ['user_id' => $user->id, 'learning_program_schedule_id' => $item->id],
            [
                'activity_type' => $item->activity_type,
                'status' => $status,
                'started_at' => DB::raw('COALESCE(started_at, NOW())'),
                'completed_at' => $status === 'completed' ? now() : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function seedDefaultQuizIfEmpty(): void
    {
        if (!Schema::hasTable('mobile_quizzes') || !Schema::hasTable('mobile_quiz_questions')) return;
        if (MobileQuiz::query()->exists()) return;

        $quiz = MobileQuiz::query()->create([
            'title' => 'Quiz de consolidation — Démarrage',
            'description' => 'Premier quiz de test pour valider le fonctionnement du répétiteur numérique.',
            'duration_minutes' => 15,
            'status' => 'published',
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDays(7),
        ]);

        $quiz->questions()->createMany([
            ['question' => 'TIMAH ACADEMY est présenté comme :', 'choices' => ['Une école officielle', 'Un répétiteur numérique', 'Une banque'], 'correct_answer' => 'Un répétiteur numérique', 'explanation' => 'La plateforme est un répétiteur numérique d’accompagnement scolaire.', 'points' => 1, 'position' => 1],
            ['question' => 'La fréquence retenue pour les évaluations de progression est :', 'choices' => ['Chaque jour', 'Toutes les deux semaines', 'Une fois par an'], 'correct_answer' => 'Toutes les deux semaines', 'explanation' => 'Les évaluations bimensuelles permettent un suivi régulier.', 'points' => 1, 'position' => 2],
            ['question' => 'Le canal officiel gratuit des rapports est :', 'choices' => ['Babillard numérique', 'Radio', 'Télévision'], 'correct_answer' => 'Babillard numérique', 'explanation' => 'Le babillard numérique garde les annonces et rapports dans l’application.', 'points' => 1, 'position' => 3],
        ]);
    }

    private function serializeProgram(LearningProgramSchedule $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'type' => $item->activity_type,
            'weekday' => $item->weekday,
            'unlock_time' => $item->unlock_time,
            'unlocks_at' => $item->unlocks_at?->toIso8601String(),
            'closes_at' => $item->closes_at?->toIso8601String(),
            'subject' => $item->subject?->name,
            'class' => $item->schoolClass?->name,
        ];
    }

    private function serializeBoard(DigitalBoardPost $post): array
    {
        return ['id' => $post->id, 'title' => $post->title, 'content' => $post->content, 'type' => $post->type, 'published_at' => $post->published_at?->toIso8601String()];
    }

    private function serializeEvaluation(BiweeklyEvaluation $item): array
    {
        return ['id' => $item->id, 'title' => $item->title, 'description' => $item->description, 'opens_at' => $item->opens_at?->toIso8601String(), 'closes_at' => $item->closes_at?->toIso8601String(), 'duration_minutes' => $item->duration_minutes];
    }

    private function serializeReport(ProgressReport $item): array
    {
        return ['id' => $item->id, 'participation_rate' => $item->participation_rate, 'evaluation_score' => $item->evaluation_score, 'strengths' => $item->strengths, 'weaknesses' => $item->weaknesses, 'recommendations' => $item->recommendations, 'published_at' => $item->published_at?->toIso8601String()];
    }

    private function serializeQuiz(MobileQuiz $quiz, bool $withQuestions): array
    {
        $data = [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'duration_minutes' => $quiz->duration_minutes,
            'pass_mark' => $quiz->pass_mark ?? $quiz->pass_score ?? 10,
            'opens_at' => $quiz->opens_at?->toIso8601String(),
            'closes_at' => $quiz->closes_at?->toIso8601String(),
            'subject' => $quiz->subject?->name,
            'class' => $quiz->schoolClass?->name,
        ];

        if ($withQuestions) {
            $data['questions'] = $quiz->questions->map(fn ($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'choices' => $q->choices ?: [],
                'points' => $q->points,
            ]);
        }

        return $data;
    }
}
