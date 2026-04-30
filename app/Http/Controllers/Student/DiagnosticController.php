<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DiagnosticAnswer;
use App\Models\DiagnosticQuestion;
use App\Models\DiagnosticSession;
use App\Models\StudentLearningProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiagnosticController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profile = StudentLearningProfile::where('user_id', $user->id)->first();

        if ($profile && $profile->diagnostic_completed_at) {
            return view('student.diagnostic.result', compact('profile'));
        }

        $session = $this->getOrCreateSession($user->id);
        $question = $this->currentQuestion($session);

        return view('student.diagnostic.index', compact('session', 'question'));
    }

    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => ['required', 'integer', 'exists:diagnostic_questions,id'],
            'answer' => ['required'],
        ], [
            'answer.required' => 'Réponds à cette question avant de continuer.',
        ]);

        $user = auth()->user();
        $session = $this->getOrCreateSession($user->id);
        $question = DiagnosticQuestion::findOrFail((int) $request->question_id);
        $answer = $request->input('answer');
        $answerText = is_array($answer) ? implode(', ', array_filter($answer)) : trim((string) $answer);
        $score = $question->type === 'score' ? (int) $answerText : null;

        DiagnosticAnswer::updateOrCreate(
            [
                'diagnostic_session_id' => $session->id,
                'user_id' => $user->id,
                'diagnostic_question_id' => $question->id,
            ],
            [
                'category' => $question->category,
                'question_text' => $question->question,
                'answer_text' => $answerText,
                'answer_score' => $score,
                'answer_payload' => is_array($answer) ? $answer : null,
            ]
        );

        $answeredCount = DiagnosticAnswer::where('diagnostic_session_id', $session->id)->count();
        $session->update(['current_step' => $answeredCount + 1]);

        if ($answeredCount >= $session->total_questions) {
            $this->completeSession($session);
            return redirect()->route('student.diagnostic.result')->with('success', 'Ton profil pédagogique est prêt.');
        }

        return redirect()->route('student.diagnostic.index');
    }

    public function result()
    {
        $profile = StudentLearningProfile::where('user_id', auth()->id())->first();

        if (!$profile) {
            return redirect()->route('student.diagnostic.index');
        }

        return view('student.diagnostic.result', compact('profile'));
    }

    protected function getOrCreateSession(int $userId): DiagnosticSession
    {
        $session = DiagnosticSession::where('user_id', $userId)
            ->where('status', DiagnosticSession::STATUS_IN_PROGRESS)
            ->latest('id')
            ->first();

        if ($session) {
            return $session;
        }

        $questionCount = DiagnosticQuestion::where('is_active', true)->count();

        return DiagnosticSession::create([
            'user_id' => $userId,
            'status' => DiagnosticSession::STATUS_IN_PROGRESS,
            'current_step' => 1,
            'total_questions' => min(8, max(1, $questionCount)),
            'started_at' => now(),
        ]);
    }

    protected function currentQuestion(DiagnosticSession $session): ?DiagnosticQuestion
    {
        $answeredIds = DiagnosticAnswer::where('diagnostic_session_id', $session->id)
            ->pluck('diagnostic_question_id')
            ->filter()
            ->all();

        $categories = ['objectif', 'difficulte', 'confiance', 'methode', 'disponibilite', 'motivation'];
        $answeredCategories = DiagnosticAnswer::where('diagnostic_session_id', $session->id)->pluck('category')->all();
        $preferredCategory = collect($categories)->first(fn ($cat) => !in_array($cat, $answeredCategories, true));

        $query = DiagnosticQuestion::where('is_active', true)->whereNotIn('id', $answeredIds);

        if ($preferredCategory) {
            $candidate = (clone $query)->where('category', $preferredCategory)->inRandomOrder()->first();
            if ($candidate) {
                return $candidate;
            }
        }

        return $query->inRandomOrder()->first();
    }

    protected function completeSession(DiagnosticSession $session): void
    {
        DB::transaction(function () use ($session) {
            $answers = DiagnosticAnswer::where('diagnostic_session_id', $session->id)->get();

            $weakSubjects = $this->extractSubjects($answers->where('category', 'difficulte')->pluck('answer_text')->implode(', '));
            $confidenceScores = $answers->where('category', 'confiance')->pluck('answer_score')->filter()->values()->all();
            $mainGoal = $answers->where('category', 'objectif')->pluck('answer_text')->filter()->first();
            $style = $answers->where('category', 'methode')->pluck('answer_text')->filter()->first();
            $availability = $answers->where('category', 'disponibilite')->pluck('answer_text')->filter()->first();

            $summary = $this->summary($mainGoal, $weakSubjects, $style, $availability, $confidenceScores);

            StudentLearningProfile::updateOrCreate(
                ['user_id' => $session->user_id],
                [
                    'main_goal' => $mainGoal,
                    'target_exam' => $mainGoal && Str::contains(Str::lower($mainGoal), 'examen') ? $mainGoal : null,
                    'weak_subjects' => $weakSubjects,
                    'strong_subjects' => [],
                    'preferred_learning_style' => $style,
                    'weekly_availability' => $availability,
                    'confidence_scores' => $confidenceScores,
                    'generated_summary' => $summary,
                    'diagnostic_completed_at' => now(),
                ]
            );

            $session->update([
                'status' => DiagnosticSession::STATUS_COMPLETED,
                'completed_at' => now(),
                'current_step' => $session->total_questions,
            ]);
        });
    }

    protected function extractSubjects(string $text): array
    {
        $subjects = ['Mathématique', 'Physique-Chimie', 'SVT', 'Français', 'Anglais', 'Informatique', 'Histoire-Géographie', 'Philosophie'];
        $found = [];
        foreach ($subjects as $subject) {
            if (Str::contains(Str::lower($text), Str::lower($subject))) {
                $found[] = $subject;
            }
        }
        return array_values(array_unique($found));
    }

    protected function summary(?string $goal, array $weakSubjects, ?string $style, ?string $availability, array $scores): string
    {
        $parts = [];
        $parts[] = $goal ? 'Objectif principal : '.$goal.'.' : 'Objectif principal à préciser progressivement.';
        $parts[] = $weakSubjects ? 'Matières prioritaires : '.implode(', ', $weakSubjects).'.' : 'Matières prioritaires à observer avec les premiers TD.';
        $parts[] = $style ? 'Méthode préférée : '.$style.'.' : 'Méthode préférée à confirmer.';
        $parts[] = $availability ? 'Disponibilité annoncée : '.$availability.'.' : 'Disponibilité non précisée.';
        if ($scores) {
            $parts[] = 'Niveau de confiance moyen déclaré : '.round(array_sum($scores) / count($scores), 1).'/10.';
        }
        return implode("\n", $parts);
    }
}
