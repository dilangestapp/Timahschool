<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $password = env('TIMAH_DEFAULT_ADMIN_PASSWORD', '1234'.'5678');

        DB::table('roles')->updateOrInsert(
            ['name' => 'admin'],
            [
                'guard_name' => 'web',
                'display_name' => 'Administrateur',
                'description' => 'Accès complet à la plateforme',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $adminRoleId = (int) DB::table('roles')->where('name', 'admin')->value('id');

        $userData = [
            'name' => 'Usher',
            'email' => 'usher@timahacademy.cm',
            'email_verified_at' => $now,
            'password' => Hash::make($password),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('users', 'username')) {
            $userData['username'] = 'Usher';
        }

        if (Schema::hasColumn('users', 'full_name')) {
            $userData['full_name'] = 'Usher';
        }

        if (Schema::hasColumn('users', 'phone')) {
            $userData['phone'] = '692762065';
        }

        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = 'active';
        }

        if (Schema::hasColumn('users', 'role_id') && $adminRoleId > 0) {
            $userData['role_id'] = $adminRoleId;
        }

        DB::table('users')->updateOrInsert(['email' => 'usher@timahacademy.cm'], $userData);

        $userId = (int) DB::table('users')->where('email', 'usher@timahacademy.cm')->value('id');

        if ($adminRoleId > 0 && $userId > 0) {
            DB::table('role_user')->updateOrInsert(
                ['role_id' => $adminRoleId, 'user_id' => $userId],
                ['role_id' => $adminRoleId, 'user_id' => $userId]
            );
        }
    }

    public function down(): void
    {
        // Compte conservé volontairement pour éviter toute perte d'accès admin.
    }
};
