<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiweeklyEvaluation;
use App\Models\DigitalBoardPost;
use App\Models\LearningProgramSchedule;
use App\Models\MobileQuiz;
use App\Models\MobileQuizQuestion;
use App\Models\ProgressReport;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminMobileAcademyController extends Controller
{
    public function index(Request $request)
    {
        $classes = Schema::hasTable('school_classes') ? SchoolClass::query()->orderBy('order')->orderBy('name')->get() : collect();
        $subjects = Schema::hasTable('subjects') ? Subject::query()->orderBy('order')->orderBy('name')->get() : collect();
        $students = Schema::hasTable('users') ? User::query()->whereHas('roles', function ($q) {
            $q->whereRaw('LOWER(name) IN (?, ?, ?)', ['student', 'eleve', 'élève']);
        })->orderBy('full_name')->orderBy('name')->take(200)->get() : collect();

        if ($students->isEmpty() && Schema::hasTable('users')) {
            $students = User::query()->orderByDesc('id')->take(200)->get();
        }

        $programs = Schema::hasTable('learning_program_schedules')
            ? LearningProgramSchedule::query()->with(['schoolClass', 'subject'])->latest('id')->take(30)->get()
            : collect();

        $posts = Schema::hasTable('digital_board_posts')
            ? DigitalBoardPost::query()->with('schoolClass')->latest('id')->take(30)->get()
            : collect();

        $quizzes = Schema::hasTable('mobile_quizzes')
            ? MobileQuiz::query()->with(['questions', 'schoolClass', 'subject'])->latest('id')->take(30)->get()
            : collect();

        $evaluations = Schema::hasTable('biweekly_evaluations')
            ? BiweeklyEvaluation::query()->with('schoolClass')->latest('id')->take(30)->get()
            : collect();

        $reports = Schema::hasTable('progress_reports')
            ? ProgressReport::query()->with(['student', 'schoolClass', 'evaluation'])->latest('id')->take(30)->get()
            : collect();

        $notifications = Schema::hasTable('mobile_notifications')
            ? DB::table('mobile_notifications')->latest('id')->take(30)->get()
            : collect();

        $missingTables = collect([
            'learning_program_schedules',
            'digital_board_posts',
            'biweekly_evaluations',
            'progress_reports',
            'mobile_quizzes',
            'mobile_quiz_questions',
            'mobile_quiz_attempts',
            'mobile_notifications',
            'mobile_activity_progress',
        ])->reject(fn ($table) => Schema::hasTable($table))->values();

        return view('admin.mobile-academy.index', compact(
            'classes',
            'subjects',
            'students',
            'programs',
            'posts',
            'quizzes',
            'evaluations',
            'reports',
            'notifications',
            'missingTables'
        ));
    }

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'activity_type' => ['required', 'string', 'max:60'],
            'week_number' => ['required', 'integer', 'min:1', 'max:52'],
            'weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'unlock_time' => ['nullable', 'date_format:H:i'],
            'unlocks_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string', 'max:40'],
        ]);

        $data['requires_subscription'] = $request->boolean('requires_subscription', true);
        LearningProgramSchedule::query()->create($this->cleanNullableIds($data));

        return back()->with('success', 'Activité mobile ajoutée au programme.');
    }

    public function deleteProgram(LearningProgramSchedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Activité mobile supprimée.');
    }

    public function storeBoard(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'string', 'max:60'],
            'audience' => ['required', 'string', 'max:60'],
            'school_class_id' => ['nullable', 'integer'],
            'status' => ['required', 'string', 'max:40'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data = $this->cleanNullableIds($data);
        $data['author_id'] = auth()->id();
        if (($data['status'] ?? '') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        DigitalBoardPost::query()->create($data);

        return back()->with('success', 'Publication ajoutée au babillard mobile.');
    }

    public function deleteBoard(DigitalBoardPost $post)
    {
        $post->delete();
        return back()->with('success', 'Publication supprimée.');
    }

    public function storeEvaluation(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'integer'],
            'period_starts_at' => ['nullable', 'date'],
            'period_ends_at' => ['nullable', 'date'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string', 'max:40'],
        ]);

        BiweeklyEvaluation::query()->create($this->cleanNullableIds($data));

        return back()->with('success', 'Évaluation bimensuelle ajoutée.');
    }

    public function deleteEvaluation(BiweeklyEvaluation $evaluation)
    {
        $evaluation->delete();
        return back()->with('success', 'Évaluation supprimée.');
    }

    public function storeQuiz(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'learning_program_schedule_id' => ['nullable', 'integer'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'pass_mark' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'max:40'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
        ]);

        MobileQuiz::query()->create($this->cleanNullableIds($data));

        return back()->with('success', 'Quiz mobile créé. Ajoutez maintenant les questions.');
    }

    public function deleteQuiz(MobileQuiz $quiz)
    {
        $quiz->delete();
        return back()->with('success', 'Quiz supprimé.');
    }

    public function storeQuizQuestion(Request $request, MobileQuiz $quiz)
    {
        $data = $request->validate([
            'question' => ['required', 'string'],
            'choices_text' => ['required', 'string'],
            'correct_answer' => ['required', 'string', 'max:255'],
            'explanation' => ['nullable', 'string'],
            'points' => ['nullable', 'integer', 'min:1'],
        ]);

        $choices = collect(preg_split('/\r\n|\r|\n/', $data['choices_text']))
            ->map(fn ($choice) => trim($choice))
            ->filter()
            ->values()
            ->all();

        MobileQuizQuestion::query()->create([
            'mobile_quiz_id' => $quiz->id,
            'question' => $data['question'],
            'choices' => $choices,
            'correct_answer' => $data['correct_answer'],
            'explanation' => $data['explanation'] ?? null,
            'points' => $data['points'] ?? 1,
            'order' => ($quiz->questions()->count() + 1),
        ]);

        return back()->with('success', 'Question ajoutée au quiz.');
    }

    public function deleteQuizQuestion(MobileQuizQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Question supprimée.');
    }

    public function storeReport(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'school_class_id' => ['nullable', 'integer'],
            'biweekly_evaluation_id' => ['nullable', 'integer'],
            'period_starts_at' => ['nullable', 'date'],
            'period_ends_at' => ['nullable', 'date'],
            'participation_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'evaluation_score' => ['nullable', 'numeric', 'min:0'],
            'courses_done' => ['nullable', 'integer', 'min:0'],
            'td_done' => ['nullable', 'integer', 'min:0'],
            'quizzes_done' => ['nullable', 'integer', 'min:0'],
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:40'],
        ]);

        $data = $this->cleanNullableIds($data);
        if (($data['status'] ?? '') === 'published') {
            $data['published_at'] = now();
            $student = User::query()->find($data['student_id']);
            DigitalBoardPost::query()->create([
                'author_id' => auth()->id(),
                'title' => 'Rapport de progression disponible',
                'content' => 'Un nouveau rapport de progression est disponible pour ' . ($student?->full_name ?: $student?->name ?: 'un apprenant') . '.',
                'type' => 'report',
                'audience' => 'parent',
                'school_class_id' => $data['school_class_id'] ?? null,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        ProgressReport::query()->create($data);

        return back()->with('success', 'Rapport de progression créé.');
    }

    public function deleteReport(ProgressReport $report)
    {
        $report->delete();
        return back()->with('success', 'Rapport supprimé.');
    }

    public function storeNotification(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'max:60'],
            'audience' => ['required', 'string', 'max:60'],
            'school_class_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'target_type' => ['nullable', 'string', 'max:60'],
            'target_id' => ['nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data = $this->cleanNullableIds($data);
        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        DB::table('mobile_notifications')->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return back()->with('success', 'Notification interne publiée.');
    }

    public function deleteNotification(int $id)
    {
        if (Schema::hasTable('mobile_notifications')) {
            DB::table('mobile_notifications')->where('id', $id)->delete();
        }

        return back()->with('success', 'Notification supprimée.');
    }

    private function cleanNullableIds(array $data): array
    {
        foreach (['school_class_id', 'subject_id', 'learning_program_schedule_id', 'biweekly_evaluation_id', 'user_id', 'target_id'] as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
