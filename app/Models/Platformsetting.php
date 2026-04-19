<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function defaults(): array
    {
        return [
            'general' => [
                'platform_name' => 'TIMAH ACADEMY',
                'platform_slogan' => "Apprendre aujourd'hui, réussir demain.",
                'support_email' => 'support@timahacademy.com',
                'support_phone' => '+237 6 00 00 00 00',
                'support_whatsapp' => '+237 6 00 00 00 00',
                'footer_text' => 'Plateforme éducative moderne pour cours, TD, quiz et suivi.',
                'primary_color' => '#315efb',
                'secondary_color' => '#7c3aed',
            ],

            'dashboard_admin' => [
                'page_title' => 'Tableau de bord administrateur',
                'page_subtitle' => 'Vue globale des activités TIMAH ACADEMY : utilisateurs, pédagogique et monétisation.',
                'modules_title' => 'Modules de pilotage',
                'modules_text' => 'Contrôle rapide de l’administration pédagogique et opérationnelle.',
                'decision_title' => 'Colonne décisionnelle',
                'decision_text' => 'Actions à fort impact pour gagner du temps au quotidien.',
                'indicators_title' => 'Indicateurs TD',
                'indicators_text' => 'État du module TD en temps réel.',
                'recent_td_title' => 'Derniers TD',
                'recent_messages_title' => 'Derniers messages enseignants',
            ],

            'dashboard_teacher' => [
                'page_title' => 'Tableau de bord enseignant',
                'page_subtitle' => 'Suivez vos classes, vos TD et les questions des élèves depuis une vue claire et rapide.',
                'assignments_title' => 'Mes affectations',
                'assignments_button' => 'Voir toutes mes classes',
                'assignments_empty' => 'Aucune affectation active.',
                'latest_td_title' => 'Derniers TD',
                'latest_td_empty' => 'Aucun TD pour le moment.',
                'latest_questions_title' => 'Dernières questions TD',
                'latest_questions_empty' => 'Aucune question TD pour le moment.',
            ],

            'dashboard_student' => [
                'hero_badge' => '✨ Tableau de bord élève',
                'hero_title' => 'Bonjour, :name',
                'hero_highlight' => 'prêt à continuer ? 👋',
                'hero_text' => 'Retrouvez votre classe, vos TD, vos cours et les repères essentiels dans un espace plus clair, plus attractif et mieux organisé pour votre progression.',
                'workspace_title' => 'Votre espace',
                'workspace_text' => 'TD, cours et messages accessibles rapidement.',
                'goal_title' => 'Objectif',
                'goal_text' => 'Continuer vos TD récents et garder le rythme.',
                'progress_title' => 'Radar de progression',
                'progress_text' => 'Un repère visuel fort pour donner plus de présence au tableau de bord.',
                'activity_title' => 'Activité de la semaine',
                'activity_text' => 'Une vraie touche visuelle pour renforcer l’effet dashboard moderne.',
                'td_title' => 'TD récents',
                'td_text' => 'Les dernières publications de votre classe, prêtes à être consultées.',
                'refs_title' => 'Repères rapides',
                'refs_text' => 'Gardez sous les yeux les informations essentielles liées à votre espace.',
                'advice_title' => 'Conseil du moment',
                'advice_text' => 'Commencez par les TD récents, puis revenez sur les matières où vous avez encore besoin d’un meilleur rythme de travail.',
                'advice_note' => 'Une bonne habitude : consulter vos TD, relire le cours lié et poser une question dès qu’un point bloque.',
                'shortcut_td_title' => 'Mes TD',
                'shortcut_td_text' => 'Accédez à vos TD, corrigés, niveaux d’accès et dernières publications.',
                'shortcut_messages_title' => 'Messagerie enseignant',
                'shortcut_messages_text' => 'Posez vos questions liées à la matière, au cours ou au TD concerné.',
            ],
        ];
    }

    public static function group(string $key): array
    {
        $defaults = static::defaults()[$key] ?? [];

        if (!Schema::hasTable('platform_settings')) {
            return $defaults;
        }

        return Cache::rememberForever("platform_settings.{$key}", function () use ($key, $defaults) {
            $setting = static::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $defaults]
            );

            return array_replace($defaults, $setting->value ?? []);
        });
    }

    public static function putGroup(string $key, array $value): void
    {
        if (!Schema::hasTable('platform_settings')) {
            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("platform_settings.{$key}");
    }
}
