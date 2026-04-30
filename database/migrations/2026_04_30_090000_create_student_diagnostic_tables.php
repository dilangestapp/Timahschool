<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('diagnostic_questions')) {
            Schema::create('diagnostic_questions', function (Blueprint $table) {
                $table->id();
                $table->string('category', 80);
                $table->text('question');
                $table->string('type', 40)->default('text');
                $table->json('options')->nullable();
                $table->unsignedInteger('weight')->default(1);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();

                $table->index(['category', 'is_active']);
            });
        }

        if (!Schema::hasTable('diagnostic_sessions')) {
            Schema::create('diagnostic_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('status', 40)->default('in_progress');
                $table->unsignedInteger('current_step')->default(1);
                $table->unsignedInteger('total_questions')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('diagnostic_answers')) {
            Schema::create('diagnostic_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('diagnostic_session_id')->constrained('diagnostic_sessions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('diagnostic_question_id')->nullable()->constrained('diagnostic_questions')->nullOnDelete();
                $table->string('category', 80)->nullable();
                $table->text('question_text');
                $table->longText('answer_text')->nullable();
                $table->integer('answer_score')->nullable();
                $table->json('answer_payload')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'category']);
            });
        }

        if (!Schema::hasTable('student_learning_profiles')) {
            Schema::create('student_learning_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('main_goal')->nullable();
                $table->string('target_exam')->nullable();
                $table->json('weak_subjects')->nullable();
                $table->json('strong_subjects')->nullable();
                $table->string('preferred_learning_style')->nullable();
                $table->string('weekly_availability')->nullable();
                $table->json('confidence_scores')->nullable();
                $table->longText('generated_summary')->nullable();
                $table->longText('teacher_notes')->nullable();
                $table->timestamp('diagnostic_completed_at')->nullable();
                $table->timestamps();
            });
        }

        $this->seedQuestions();
    }

    public function down(): void
    {
        Schema::dropIfExists('student_learning_profiles');
        Schema::dropIfExists('diagnostic_answers');
        Schema::dropIfExists('diagnostic_sessions');
        Schema::dropIfExists('diagnostic_questions');
    }

    private function seedQuestions(): void
    {
        if (!Schema::hasTable('diagnostic_questions')) {
            return;
        }

        if (DB::table('diagnostic_questions')->count() > 0) {
            return;
        }

        $now = now();
        $questions = [
            ['category' => 'objectif', 'question' => 'Quel résultat veux-tu absolument améliorer cette année ?', 'type' => 'text', 'options' => null, 'order' => 1],
            ['category' => 'objectif', 'question' => 'Tu travailles surtout pour préparer un examen, rattraper tes lacunes ou renforcer ton niveau ?', 'type' => 'choice', 'options' => ['Préparer un examen', 'Rattraper mes lacunes', 'Renforcer mon niveau', 'Mieux m’organiser'], 'order' => 2],
            ['category' => 'objectif', 'question' => 'Quelle est ta plus grande priorité scolaire pour les prochaines semaines ?', 'type' => 'text', 'options' => null, 'order' => 3],
            ['category' => 'difficulte', 'question' => 'Dans quelles matières rencontres-tu le plus de difficultés ?', 'type' => 'multi_choice', 'options' => ['Mathématique', 'Physique-Chimie', 'SVT', 'Français', 'Anglais', 'Informatique', 'Histoire-Géographie', 'Philosophie'], 'order' => 4],
            ['category' => 'difficulte', 'question' => 'Quand tu ne comprends pas une leçon, qu’est-ce qui te bloque le plus ?', 'type' => 'choice', 'options' => ['Le cours est trop rapide', 'Les exercices sont difficiles', 'Je manque de méthode', 'Je manque de temps', 'Je n’ose pas poser des questions'], 'order' => 5],
            ['category' => 'difficulte', 'question' => 'Quelle matière aimerais-tu travailler en priorité sur TIMAH ACADEMY ?', 'type' => 'text', 'options' => null, 'order' => 6],
            ['category' => 'confiance', 'question' => 'Sur 10, quel est ton niveau de confiance dans ta matière la plus difficile ?', 'type' => 'score', 'options' => ['1','2','3','4','5','6','7','8','9','10'], 'order' => 7],
            ['category' => 'confiance', 'question' => 'Quand tu vois un exercice difficile, tu te sens plutôt comment ?', 'type' => 'choice', 'options' => ['Je tente directement', 'Je panique un peu', 'J’attends une explication', 'Je saute l’exercice', 'Je cherche un exemple corrigé'], 'order' => 8],
            ['category' => 'methode', 'question' => 'Tu comprends mieux avec quel type d’aide ?', 'type' => 'choice', 'options' => ['Explications simples', 'Exemples corrigés', 'Exercices progressifs', 'Schémas et images', 'Questions à un enseignant'], 'order' => 9],
            ['category' => 'methode', 'question' => 'Comment travailles-tu le plus souvent tes TD ?', 'type' => 'choice', 'options' => ['Seul', 'Avec un camarade', 'Avec un répétiteur', 'Avec un parent', 'Je ne les traite pas souvent'], 'order' => 10],
            ['category' => 'disponibilite', 'question' => 'Combien de temps peux-tu consacrer aux révisions par semaine ?', 'type' => 'choice', 'options' => ['Moins de 2h', '2h à 4h', '5h à 7h', 'Plus de 7h'], 'order' => 11],
            ['category' => 'motivation', 'question' => 'Qu’est-ce qui peut t’aider à rester motivé sur la plateforme ?', 'type' => 'multi_choice', 'options' => ['Encouragements', 'Classement/progression', 'Corrections détaillées', 'Aide rapide d’un enseignant', 'Objectifs par semaine'], 'order' => 12],
        ];

        foreach ($questions as $question) {
            DB::table('diagnostic_questions')->insert([
                'category' => $question['category'],
                'question' => $question['question'],
                'type' => $question['type'],
                'options' => $question['options'] ? json_encode($question['options'], JSON_UNESCAPED_UNICODE) : null,
                'weight' => 1,
                'is_active' => true,
                'order' => $question['order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
