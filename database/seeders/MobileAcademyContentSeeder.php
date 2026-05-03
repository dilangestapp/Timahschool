<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileAcademyContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        if (Schema::hasTable('digital_board_posts') && DB::table('digital_board_posts')->count() === 0) {
            DB::table('digital_board_posts')->insert([
                [
                    'title' => 'Bienvenue sur TIMAH ACADEMY',
                    'content' => 'TIMAH ACADEMY est un répétiteur numérique conçu pour accompagner les apprenants dans les révisions, les TD, les quiz, les évaluations de progression et le suivi parent.',
                    'type' => 'announcement',
                    'audience' => 'all',
                    'status' => 'published',
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title' => 'Programme de répétition numérique',
                    'content' => 'Les cours sont programmés en semaine. Les TD sont prévus le week-end. Les quiz consolident les acquis et les évaluations mesurent la progression toutes les deux semaines.',
                    'type' => 'announcement',
                    'audience' => 'all',
                    'status' => 'published',
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title' => 'Suivi parent et rapports',
                    'content' => 'Les rapports de progression seront publiés sur le babillard numérique. WhatsApp servira seulement à prévenir les parents gratuitement lorsqu’un rapport est disponible.',
                    'type' => 'announcement',
                    'audience' => 'all',
                    'status' => 'published',
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'title' => 'Règle commerciale mobile',
                    'content' => 'Un numéro WhatsApp correspond à un compte, un essai gratuit de 24h, un abonnement actif et un appareil autorisé. En cas de changement de téléphone, contactez l’administration.',
                    'type' => 'announcement',
                    'audience' => 'all',
                    'status' => 'published',
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        if (Schema::hasTable('learning_program_schedules') && DB::table('learning_program_schedules')->count() === 0) {
            $weekStart = now()->startOfWeek();
            $items = [
                [1, 'Mathématiques — Cours programmé', 'course', 'Comprendre la notion du jour avec des explications simples et des exemples guidés.', '18:00', 60],
                [2, 'Français — Cours programmé', 'course', 'Lire, comprendre, analyser et s’exercer avec une méthode progressive.', '18:00', 60],
                [3, 'Anglais — Cours programmé', 'course', 'Renforcer le vocabulaire, la grammaire et l’expression.', '18:00', 60],
                [4, 'PCT / Informatique — Cours programmé', 'course', 'Découvrir une notion scientifique ou numérique utile et l’appliquer.', '18:00', 60],
                [5, 'Révision guidée de la semaine', 'revision', 'Reprendre les points importants avant les exercices du week-end.', '18:00', 60],
                [6, 'TD de la semaine', 'td', 'S’entraîner avec des exercices progressifs pour consolider les acquis.', '10:00', 120],
                [7, 'Quiz de consolidation', 'quiz', 'Vérifier rapidement ce qui est compris avant la nouvelle semaine.', '15:00', 30],
            ];

            foreach ($items as [$weekday, $title, $type, $description, $time, $duration]) {
                [$hour, $minute] = array_map('intval', explode(':', $time));
                DB::table('learning_program_schedules')->insert([
                    'title' => $title,
                    'description' => $description,
                    'activity_type' => $type,
                    'week_number' => 1,
                    'weekday' => $weekday,
                    'unlock_time' => $time,
                    'unlocks_at' => $weekStart->copy()->addDays($weekday - 1)->setTime($hour, $minute),
                    'duration_minutes' => $duration,
                    'status' => 'published',
                    'requires_subscription' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('biweekly_evaluations') && DB::table('biweekly_evaluations')->count() === 0) {
            DB::table('biweekly_evaluations')->insert([
                'title' => 'Évaluation de progression — Période test',
                'description' => 'Évaluation bimensuelle permettant de mesurer les progrès réalisés dans le programme de répétition numérique.',
                'period_starts_at' => now()->startOfWeek(),
                'period_ends_at' => now()->startOfWeek()->addDays(13),
                'opens_at' => now()->startOfWeek()->addDays(13)->setTime(15, 0),
                'closes_at' => now()->startOfWeek()->addDays(13)->setTime(18, 0),
                'duration_minutes' => 120,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('mobile_quizzes') && Schema::hasTable('mobile_quiz_questions') && DB::table('mobile_quizzes')->count() === 0) {
            $quizId = DB::table('mobile_quizzes')->insertGetId([
                'title' => 'Quiz de consolidation — Démarrage',
                'description' => 'Premier quiz de test pour valider le fonctionnement du répétiteur numérique.',
                'duration_minutes' => 15,
                'pass_mark' => 2,
                'status' => 'published',
                'opens_at' => now()->subHour(),
                'closes_at' => now()->addDays(7),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('mobile_quiz_questions')->insert([
                [
                    'mobile_quiz_id' => $quizId,
                    'question' => 'TIMAH ACADEMY est présenté comme :',
                    'choices' => json_encode(['Une école officielle', 'Un répétiteur numérique', 'Une banque']),
                    'correct_answer' => 'Un répétiteur numérique',
                    'explanation' => 'La plateforme est un répétiteur numérique d’accompagnement scolaire.',
                    'points' => 1,
                    'order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'mobile_quiz_id' => $quizId,
                    'question' => 'La fréquence retenue pour les évaluations de progression est :',
                    'choices' => json_encode(['Chaque jour', 'Toutes les deux semaines', 'Une fois par an']),
                    'correct_answer' => 'Toutes les deux semaines',
                    'explanation' => 'Les évaluations bimensuelles permettent un suivi régulier.',
                    'points' => 1,
                    'order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'mobile_quiz_id' => $quizId,
                    'question' => 'Le canal officiel gratuit des rapports est :',
                    'choices' => json_encode(['Babillard numérique', 'Radio', 'Télévision']),
                    'correct_answer' => 'Babillard numérique',
                    'explanation' => 'Le babillard numérique garde les annonces et rapports dans l’application.',
                    'points' => 1,
                    'order' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}
