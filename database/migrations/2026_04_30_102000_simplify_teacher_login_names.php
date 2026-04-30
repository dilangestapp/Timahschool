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
        $password = env('TIMAH_DEFAULT_TEACHER_PASSWORD', '12345678');

        $teachers = [
            ['old_email' => 'prof_maths@timahacademy.cm', 'email' => 'maths@timahacademy.cm', 'username' => 'maths', 'name' => 'Enseignant Maths'],
            ['old_email' => 'prof_pct@timahacademy.cm', 'email' => 'pct@timahacademy.cm', 'username' => 'pct', 'name' => 'Enseignant PCT'],
            ['old_email' => 'prof_svt@timahacademy.cm', 'email' => 'svt@timahacademy.cm', 'username' => 'svt', 'name' => 'Enseignant SVT'],
            ['old_email' => 'prof_francais@timahacademy.cm', 'email' => 'francais@timahacademy.cm', 'username' => 'francais', 'name' => 'Enseignant Français'],
            ['old_email' => 'prof_litterature@timahacademy.cm', 'email' => 'litterature@timahacademy.cm', 'username' => 'litterature', 'name' => 'Enseignant Littérature'],
            ['old_email' => 'prof_philosophie@timahacademy.cm', 'email' => 'philosophie@timahacademy.cm', 'username' => 'philosophie', 'name' => 'Enseignant Philosophie'],
            ['old_email' => 'prof_hg@timahacademy.cm', 'email' => 'hg@timahacademy.cm', 'username' => 'hg', 'name' => 'Enseignant Histoire-Géographie'],
            ['old_email' => 'prof_anglais@timahacademy.cm', 'email' => 'anglais@timahacademy.cm', 'username' => 'anglais', 'name' => 'Enseignant Anglais'],
            ['old_email' => 'prof_allemand@timahacademy.cm', 'email' => 'allemand@timahacademy.cm', 'username' => 'allemand', 'name' => 'Enseignant Allemand'],
            ['old_email' => 'prof_espagnol@timahacademy.cm', 'email' => 'espagnol@timahacademy.cm', 'username' => 'espagnol', 'name' => 'Enseignant Espagnol'],
            ['old_email' => 'prof_informatique@timahacademy.cm', 'email' => 'informatique@timahacademy.cm', 'username' => 'informatique', 'name' => 'Enseignant Informatique'],
        ];

        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id');

        foreach ($teachers as $teacher) {
            $existing = DB::table('users')->where('email', $teacher['old_email'])->first();
            $newExisting = DB::table('users')->where('email', $teacher['email'])->first();

            $userData = [
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'email_verified_at' => $now,
                'password' => Hash::make($password),
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('users', 'username')) {
                $userData['username'] = $teacher['username'];
            }

            if (Schema::hasColumn('users', 'full_name')) {
                $userData['full_name'] = $teacher['name'];
            }

            if (Schema::hasColumn('users', 'phone')) {
                $userData['phone'] = '692762065';
            }

            if (Schema::hasColumn('users', 'status')) {
                $userData['status'] = 'active';
            }

            if ($existing && !$newExisting) {
                DB::table('users')->where('id', $existing->id)->update($userData);
                $userId = (int) $existing->id;
            } else {
                $userData['created_at'] = $now;
                DB::table('users')->updateOrInsert(['email' => $teacher['email']], $userData);
                $userId = (int) DB::table('users')->where('email', $teacher['email'])->value('id');
            }

            if ($teacherRoleId && $userId) {
                DB::table('role_user')->updateOrInsert(
                    ['role_id' => $teacherRoleId, 'user_id' => $userId],
                    ['role_id' => $teacherRoleId, 'user_id' => $userId]
                );
            }

            if (Schema::hasTable('teacher_profiles') && $userId) {
                DB::table('teacher_profiles')->updateOrInsert(
                    ['user_id' => $userId],
                    [
                        'bio' => $teacher['name'].' - TIMAH ACADEMY',
                        'specialization' => $teacher['username'],
                        'contact_email' => $teacher['email'],
                        'contact_phone' => '692762065',
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Données conservées volontairement.
    }
};
