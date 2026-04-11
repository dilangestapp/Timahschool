<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait FiltersTableColumns
{
    protected function hasTableSafe(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function hasColumnSafe(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function onlyExistingColumns(string $table, array $data): array
    {
        if (!$this->hasTableSafe($table)) {
            return [];
        }

        $filtered = [];
        foreach ($data as $column => $value) {
            if ($this->hasColumnSafe($table, $column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    protected function roleIdsByNames(array $names): array
    {
        if (!$this->hasTableSafe('roles')) {
            return [];
        }

        $names = array_map(static fn ($name) => mb_strtolower(trim((string) $name)), $names);

        return DB::table('roles')
            ->whereIn(DB::raw('LOWER(name)'), $names)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    protected function teacherRoleIds(): array
    {
        return $this->roleIdsByNames(['teacher', 'enseignant']);
    }

    protected function studentRoleIds(): array
    {
        return $this->roleIdsByNames(['student', 'eleve', 'élève']);
    }

    protected function adminRoleIds(): array
    {
        return $this->roleIdsByNames(['admin']);
    }

    protected function formatMoney($amount, string $currency = 'XAF'): string
    {
        return number_format((float) $amount, 0, ',', ' ') . ' ' . $currency;
    }
}
