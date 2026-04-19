<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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

    public $timestamps = true;

    protected $casts = [
        'sort_order' => 'integer',
        'is_public' => 'boolean',
    ];

    public static function group(string $group): array
    {
        if (!Schema::hasTable('platform_settings')) {
            return [];
        }

        return static::query()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (self $setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    public static function value(string $group, string $key, mixed $default = null): mixed
    {
        $settings = static::group($group);

        return $settings[$key] ?? $default;
    }

    public static function publicGroup(string $group): array
    {
        if (!Schema::hasTable('platform_settings')) {
            return [];
        }

        return static::query()
            ->where('group', $group)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (self $setting) {
                return [$setting->key => static::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    public static function setGroupValues(string $group, array $values): void
    {
        if (!Schema::hasTable('platform_settings')) {
            return;
        }

        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                [
                    'group' => $group,
                    'key' => $key,
                ],
                [
                    'value' => static::normalizeValue($value),
                    'type' => static::detectType($value),
                ]
            );
        }
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
