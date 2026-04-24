<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'sort_order',
        'is_public',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_public' => 'boolean',
    ];

    public static function defaults(): array
    {
        return [
            'general' => [
                'platform_name' => 'TIMAH ACADEMY',
                'platform_slogan' => 'Plateforme éducative moderne et premium',
                'support_email' => '',
                'support_phone' => '',
                'support_whatsapp' => '',
                'footer_text' => '',
                'primary_color' => '#315efb',
                'secondary_color' => '#7c3aed',
                'logo_path' => '',
            ],

            'dashboard_admin' => [
                'page_title' => 'Centre de pilotage',
                'page_subtitle' => 'Vue d’ensemble de la plateforme, des contenus et de l’activité.',
                'modules_title' => 'Modules à piloter',
                'modules_text' => 'Accédez rapidement aux espaces clés de gestion.',
                'decision_title' => 'Décisions rapides',
                'decision_text' => 'Gardez la main sur les contenus, les enseignants et les abonnements.',
                'indicators_title' => 'Indicateurs',
                'indicators_text' => 'Suivez les chiffres utiles pour piloter la plateforme.',
                'recent_td_title' => 'Derniers TD',
                'recent_messages_title' => 'Derniers messages',
            ],

            'dashboard_teacher' => [
                'page_title' => 'Bonjour et bon travail',
                'page_subtitle' => 'Retrouvez vos classes, vos TD et les questions à traiter.',
                'assignments_title' => 'Mes affectations',
                'assignments_button' => 'Voir mes classes',
                'assignments_empty' => 'Aucune affectation disponible pour le moment.',
                'latest_td_title' => 'Derniers TD publiés',
                'latest_td_empty' => 'Aucun TD publié récemment.',
                'latest_questions_title' => 'Questions récentes',
                'latest_questions_empty' => 'Aucune question récente pour le moment.',
            ],

            'dashboard_student' => [
                'hero_badge' => '✨ Tableau de bord élève',
                'hero_title' => 'Bonjour, :name',
                'hero_highlight' => 'prêt à continuer ?',
                'hero_text' => 'Retrouvez votre classe, vos TD, vos cours et les repères essentiels dans un espace plus clair, plus attractif et mieux organisé pour votre progression.',
                'workspace_title' => 'Votre espace de travail',
                'workspace_text' => 'Utilisez ce tableau de bord pour accéder rapidement à vos TD, consulter vos cours, poser une question et suivre votre rythme de travail.',
                'goal_title' => 'Objectif du moment',
                'goal_text' => 'Gardez un bon rythme sur vos TD récents et revenez régulièrement sur les matières où vous devez encore progresser.',
                'progress_title' => 'Progression',
                'progress_text' => 'Indication visuelle simple de votre activité récente.',
                'activity_title' => 'Dernières activités',
                'activity_text' => 'Les dernières publications de votre classe, prêtes à être consultées.',
                'td_title' => 'Mes TD',
                'td_text' => 'Accédez aux TD disponibles, aux corrigés et aux publications récentes.',
                'refs_title' => 'Repères rapides',
                'refs_text' => 'Gardez sous les yeux les informations essentielles liées à votre espace.',
                'advice_title' => 'Conseil du moment',
                'advice_text' => 'Avancez régulièrement, posez vos questions et gardez un rythme simple mais constant.',
                'advice_note' => 'Une petite progression régulière vaut mieux qu’une grande pause.',
                'shortcut_td_title' => 'Accéder à mes TD',
                'shortcut_td_text' => 'Continuez votre travail sans perdre le fil.',
                'shortcut_messages_title' => 'Ouvrir la messagerie',
                'shortcut_messages_text' => 'Écrivez à votre enseignant ou demandez de l’aide.',
            ],
        ];
    }

    protected static function ensureTableExists(): bool
    {
        if (Schema::hasTable('platform_settings')) {
            return true;
        }

        try {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('group', 100);
                $table->string('key', 150);
                $table->longText('value')->nullable();
                $table->string('type', 30)->default('text');
                $table->string('label')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_public')->default(false);
                $table->timestamps();

                $table->unique(['group', 'key']);
                $table->index('group');
            });
        } catch (\Throwable $e) {
            return Schema::hasTable('platform_settings');
        }

        return true;
    }

    public static function group(string $group): array
    {
        $defaults = static::defaults()[$group] ?? [];

        if (!static::ensureTableExists()) {
            return $defaults;
        }

        $stored = static::query()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (self $setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })
            ->toArray();

        return array_merge($defaults, $stored);
    }

    public static function value(string $group, string $key, mixed $default = null): mixed
    {
        $settings = static::group($group);

        return $settings[$key] ?? $default;
    }

    public static function publicGroup(string $group): array
    {
        $defaults = static::defaults()[$group] ?? [];

        if (!static::ensureTableExists()) {
            return $defaults;
        }

        $stored = static::query()
            ->where('group', $group)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (self $setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })
            ->toArray();

        return array_merge($defaults, $stored);
    }

    public static function putGroup(string $group, array $values): void
    {
        if (!static::ensureTableExists()) {
            return;
        }

        $order = 1;

        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                [
                    'group' => $group,
                    'key' => $key,
                ],
                [
                    'value' => static::normalizeValue($value),
                    'type' => static::detectType($value),
                    'sort_order' => $order,
                ]
            );

            $order++;
        }
    }

    public static function setGroupValues(string $group, array $values): void
    {
        static::putGroup($group, $values);
    }

    public static function defaultLogoUrl(): string
    {
        return asset('assets/brand/timah-academy-favicon.svg');
    }

    public static function logoUrl(?string $path = null): ?string
    {
        $rawPath = $path;

        if ($rawPath === null || $rawPath === '') {
            $general = static::group('general');
            $rawPath = $general['logo_path'] ?? '';
        }

        if (!$rawPath) {
            return static::defaultLogoUrl();
        }

        if (str_starts_with($rawPath, 'data:image/')) {
            return $rawPath;
        }

        if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
            return $rawPath;
        }

        if (str_starts_with($rawPath, '/')) {
            return asset(ltrim($rawPath, '/'));
        }

        if (file_exists(public_path($rawPath))) {
            return asset($rawPath);
        }

        if (file_exists(public_path('storage/' . ltrim($rawPath, '/')))) {
            return asset('storage/' . ltrim($rawPath, '/'));
        }

        try {
            if (Storage::disk('public')->exists($rawPath)) {
                return Storage::url($rawPath);
            }
        } catch (\Throwable $e) {
        }

        return static::defaultLogoUrl();
    }

    protected static function castValue(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => static::decodeJson($value),
            default => $value,
        };
    }

    protected static function normalizeValue(mixed $value): ?string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    protected static function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value), is_object($value) => 'json',
            default => 'text',
        };
    }

    protected static function decodeJson(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }
}
