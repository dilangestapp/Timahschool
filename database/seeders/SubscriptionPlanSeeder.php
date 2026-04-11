<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Essentiel Mensuel',
                'slug' => 'essentiel-mensuel',
                'description' => 'Pour consulter les cours et réviser les bases pendant 1 mois.',
                'price' => 3000,
                'currency' => 'XAF',
                'duration_unit' => 'month',
                'duration_value' => 1,
                'features' => [
                    'Accès aux cours de votre classe',
                    'Quiz de base',
                    'Suivi simple de progression',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 10,
            ],
            [
                'name' => 'Essentiel Trimestriel',
                'slug' => 'essentiel-trimestriel',
                'description' => 'Même accès Essentiel avec 3 mois de tranquillité.',
                'price' => 8000,
                'currency' => 'XAF',
                'duration_unit' => 'month',
                'duration_value' => 3,
                'features' => [
                    'Accès aux cours de votre classe',
                    'Quiz de base',
                    'Suivi simple de progression',
                    'Économie sur 3 mois',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 20,
            ],
            [
                'name' => 'Essentiel Annuel',
                'slug' => 'essentiel-annuel',
                'description' => 'Accès Essentiel sur toute l’année scolaire.',
                'price' => 28000,
                'currency' => 'XAF',
                'duration_unit' => 'year',
                'duration_value' => 1,
                'features' => [
                    'Accès aux cours de votre classe',
                    'Quiz de base',
                    'Suivi simple de progression',
                    'Accès stable toute l’année',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 30,
            ],
            [
                'name' => 'Standard Mensuel',
                'slug' => 'standard-mensuel',
                'description' => 'Pour l’élève qui suit ses cours et travaille les quiz régulièrement.',
                'price' => 5000,
                'currency' => 'XAF',
                'duration_unit' => 'month',
                'duration_value' => 1,
                'features' => [
                    'Tous les cours de la classe',
                    'Tous les quiz disponibles',
                    'Suivi de progression amélioré',
                    'Historique de travail',
                ],
                'is_active' => true,
                'is_featured' => true,
                'order' => 40,
            ],
            [
                'name' => 'Standard Trimestriel',
                'slug' => 'standard-trimestriel',
                'description' => 'Formule recommandée pour un trimestre complet.',
                'price' => 13500,
                'currency' => 'XAF',
                'duration_unit' => 'month',
                'duration_value' => 3,
                'features' => [
                    'Tous les cours de la classe',
                    'Tous les quiz disponibles',
                    'Suivi de progression amélioré',
                    'Historique de travail',
                    'Meilleur rapport durée/prix',
                ],
                'is_active' => true,
                'is_featured' => true,
                'order' => 50,
            ],
            [
                'name' => 'Standard Annuel',
                'slug' => 'standard-annuel',
                'description' => 'Pour accompagner l’élève sur toute l’année scolaire.',
                'price' => 48000,
                'currency' => 'XAF',
                'duration_unit' => 'year',
                'duration_value' => 1,
                'features' => [
                    'Tous les cours de la classe',
                    'Tous les quiz disponibles',
                    'Suivi de progression amélioré',
                    'Historique de travail',
                    'Accès annuel continu',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 60,
            ],
            [
                'name' => 'Premium Mensuel',
                'slug' => 'premium-mensuel',
                'description' => 'Accès complet : cours, quiz et interactions avancées.',
                'price' => 7000,
                'currency' => 'XAF',
                'duration_unit' => 'month',
                'duration_value' => 1,
                'features' => [
                    'Tous les cours de la classe',
                    'Tous les quiz disponibles',
                    'Accès aux interactions disponibles',
                    'Suivi renforcé',
                    'Traitement prioritaire',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 70,
            ],
            [
                'name' => 'Premium Trimestriel',
                'slug' => 'premium-trimestriel',
                'description' => 'Formule complète sur 3 mois avec économie.',
                'price' => 19000,
                'currency' => 'XAF',
                'duration_unit' => 'month',
                'duration_value' => 3,
                'features' => [
                    'Tous les cours de la classe',
                    'Tous les quiz disponibles',
                    'Accès aux interactions disponibles',
                    'Suivi renforcé',
                    'Traitement prioritaire',
                    'Économie sur 3 mois',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 80,
            ],
            [
                'name' => 'Premium Annuel',
                'slug' => 'premium-annuel',
                'description' => 'Accès Premium sur toute l’année scolaire.',
                'price' => 68000,
                'currency' => 'XAF',
                'duration_unit' => 'year',
                'duration_value' => 1,
                'features' => [
                    'Tous les cours de la classe',
                    'Tous les quiz disponibles',
                    'Accès aux interactions disponibles',
                    'Suivi renforcé',
                    'Traitement prioritaire',
                    'Accès annuel continu',
                ],
                'is_active' => true,
                'is_featured' => false,
                'order' => 90,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command?->info('Plans d\'abonnement Timah School créés ou mis à jour.');
    }
}
