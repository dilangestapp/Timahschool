<?php

namespace App\Support;

use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ParentAccountManager
{
    public function linkParentToStudent(User $student, ?string $parentName, ?string $parentPhone): ?User
    {
        if (!Schema::hasTable('parent_profiles') || !Schema::hasTable('student_parent')) {
            return null;
        }

        $phone = $this->normalizePhone((string) $parentPhone);
        $name = trim((string) $parentName);

        if ($phone === '') {
            return null;
        }

        $parent = User::query()->where('phone', $phone)->first();
        if (!$parent) {
            $parent = User::query()->create($this->parentUserPayload($name !== '' ? $name : 'Parent', $phone));
        }

        $this->attachParentRole($parent);

        ParentProfile::query()->updateOrCreate(
            ['user_id' => $parent->id],
            [
                'full_name' => $name !== '' ? $name : ($parent->full_name ?: $parent->name),
                'phone' => $phone,
                'status' => ParentProfile::STATUS_ACTIVE,
                'activated_at' => DB::raw('COALESCE(activated_at, NOW())'),
            ]
        );

        DB::table('student_parent')->updateOrInsert(
            ['student_id' => $student->id, 'parent_id' => $parent->id],
            ['relationship' => 'parent', 'is_primary' => true, 'updated_at' => now(), 'created_at' => now()]
        );

        return $parent;
    }

    private function parentUserPayload(string $name, string $phone): array
    {
        $columns = Schema::getColumnListing('users');
        $payload = [];
        $put = function (string $column, mixed $value) use (&$payload, $columns) {
            if (in_array($column, $columns, true)) {
                $payload[$column] = $value;
            }
        };

        $put('name', $name);
        $put('full_name', $name);
        $put('username', $this->uniqueUsername('parent' . preg_replace('/\D+/', '', $phone)));
        $put('phone', $phone);
        $put('email', $this->uniqueEmail('parent_' . (preg_replace('/\D+/', '', $phone) ?: time()) . '@timahacademy.local'));
        $put('status', 'active');
        $put('password', Hash::make($phone));

        return $payload;
    }

    private function attachParentRole(User $user): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user')) {
            return;
        }

        $role = Role::query()->where('name', 'parent')->first();
        if (!$role) {
            $payload = ['name' => 'parent'];
            if (Schema::hasColumn('roles', 'guard_name')) $payload['guard_name'] = 'web';
            if (Schema::hasColumn('roles', 'display_name')) $payload['display_name'] = 'Parent';
            if (Schema::hasColumn('roles', 'description')) $payload['description'] = 'Compte parent TIMAH ACADEMY';
            $role = Role::query()->create($payload);
        }

        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    private function uniqueUsername(string $base): string
    {
        $base = trim($base) !== '' ? $base : 'parent';
        $username = $base;
        $i = 1;
        while (User::query()->where('username', $username)->exists()) {
            $i++;
            $username = $base . '_' . $i;
        }
        return $username;
    }

    private function uniqueEmail(string $base): string
    {
        $email = $base;
        $i = 1;
        while (User::query()->where('email', $email)->exists()) {
            $i++;
            $email = preg_replace('/@/', '_' . $i . '@', $base, 1) ?: ('parent_' . time() . '_' . $i . '@timahacademy.local');
        }
        return $email;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }
}
