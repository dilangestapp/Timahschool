<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $this->ensureRoles($now);

        $adminId = $this->firstAdminId();

        $classes = [
            ['name' => '3ème', 'slug' => '3eme', 'level' => 'secondaire_general', 'order' => 1],
            ['name' => 'Première A4 Allemand', 'slug' => 'premiere-a4-allemand', 'level' => 'secondaire_general', 'order' => 2],
            ['name' => 'Première A4 Espagnol', 'slug' => 'premiere-a4-espagnol', 'level' => 'secondaire_general', 'order' => 3],
            ['name' => 'Première C', 'slug' => 'premiere-c', 'level' => 'secondaire_general', 'order' => 4],
            ['name' => 'Première D', 'slug' => 'premiere-d', 'level' => 'secondaire_general', 'order' => 5],
            ['name' => 'Terminale A4 Allemand', 'slug' => 'terminale-a4-allemand', 'level' => 'secondaire_general', 'order' => 6],
            ['name' => 'Terminale A4 Espagnol', 'slug' => 'terminale-a4-espagnol', 'level' => 'secondaire_general', 'order' => 7],
            ['name' => 'Terminale C', 'slug' => 'terminale-c', 'level' => 'secondaire_general', 'order' => 8],
            ['name' => 'Terminale D', 'slug' => 'terminale-d', 'level' => 'secondaire_general', 'order' => 9],
        ];

        foreach ($classes as $class) {
            DB::table('school_classes')->updateOrInsert(
                ['slug' => $class['slug']],
                [
                    'name' => $class['name'],
                    'description' => 'Classe '.$class['name'].' - TIMAH ACADEMY',
                    'level' => $class['level'],
                    'order' => $class['order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $subjects = [
            ['name' => 'Mathématiques', 'slug' => 'mathematiques', 'teacher_username' => 'prof_maths', 'teacher_name' => 'Prof Maths', 'icon' => '∑', 'color' => '#2563eb', 'order' => 1],
            ['name' => 'Physique-Chimie', 'slug' => 'physique-chimie', 'teacher_username' => 'prof_pct', 'teacher_name' => 'Prof PCT', 'icon' => '⚗️', 'color' => '#7c3aed', 'order' => 2],
            ['name' => 'SVT', 'slug' => 'svt', 'teacher_username' => 'prof_svt', 'teacher_name' => 'Prof SVT', 'icon' => '🌱', 'color' => '#16a34a', 'order' => 3],
            ['name' => 'Français', 'slug' => 'francais', 'teacher_username' => 'prof_francais', 'teacher_name' => 'Prof Français', 'icon' => '📘', 'color' => '#0891b2', 'order' => 4],
            ['name' => 'Littérature', 'slug' => 'litterature', 'teacher_username' => 'prof_litterature', 'teacher_name' => 'Prof Littérature', 'icon' => '📚', 'color' => '#be123c', 'order' => 5],
            ['name' => 'Philosophie', 'slug' => 'philosophie', 'teacher_username' => 'prof_philosophie', 'teacher_name' => 'Prof Philosophie', 'icon' => '🧠', 'color' => '#9333ea', 'order' => 6],
            ['name' => 'Histoire-Géographie', 'slug' => 'histoire-geographie', 'teacher_username' => 'prof_hg', 'teacher_name' => 'Prof Histoire-Géographie', 'icon' => '🌍', 'color' => '#ca8a04', 'order' => 7],
            ['name' => 'Anglais', 'slug' => 'anglais', 'teacher_username' => 'prof_anglais', 'teacher_name' => 'Prof Anglais', 'icon' => '🇬🇧', 'color' => '#ea580c', 'order' => 8],
            ['name' => 'Allemand', 'slug' => 'allemand', 'teacher_username' => 'prof_allemand', 'teacher_name' => 'Prof Allemand', 'icon' => '🇩🇪', 'color' => '#111827', 'order' => 9],
            ['name' => 'Espagnol', 'slug' => 'espagnol', 'teacher_username' => 'prof_espagnol', 'teacher_name' => 'Prof Espagnol', 'icon' => '🇪🇸', 'color' => '#dc2626', 'order' => 10],
            ['name' => 'Informatique', 'slug' => 'informatique', 'teacher_username' => 'prof_informatique', 'teacher_name' => 'Prof Informatique', 'icon' => '💻', 'color' => '#0f172a', 'order' => 11],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['slug' => $subject['slug']],
                [
                    'name' => $subject['name'],
                    'description' => 'Matière '.$subject['name'].' - TIMAH ACADEMY',
                    'icon' => $subject['icon'],
                    'color' => $subject['color'],
                    'order' => $subject['order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $classIds = DB::table('school_classes')->whereIn('slug', array_column($classes, 'slug'))->pluck('id', 'slug');
        $subjectIds = DB::table('subjects')->whereIn('slug', array_column($subjects, 'slug'))->pluck('id', 'slug');

        foreach ($subjects as $subject) {
            $teacherId = $this->ensureTeacher(
                $subject['teacher_username'],
                $subject['teacher_name'],
                $subject['slug'],
                $now
            );

            $subjectId = $subjectIds[$subject['slug']] ?? null;

            if (!$subjectId) {
                continue;
            }

            foreach ($classIds as $classId) {
                DB::table('class_subject')->updateOrInsert(
                    ['school_class_id' => $classId, 'subject_id' => $subjectId],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );

                DB::table('teacher_assignments')->updateOrInsert(
                    ['teacher_id' => $teacherId, 'school_class_id' => $classId, 'subject_id' => $subjectId],
                    [
                        'assigned_by' => $adminId,
                        'notes' => 'Affectation automatique de base TIMAH ACADEMY',
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
        // Données de base conservées volontairement.
    }

    private function ensureRoles($now): void
    {
        $roles = [
            'admin' => ['display_name' => 'Administrateur', 'description' => 'Accès complet à la plateforme'],
            'teacher' => ['display_name' => 'Enseignant', 'description' => 'Gestion des cours, TD et échanges élèves'],
            'student' => ['display_name' => 'Élève', 'description' => 'Accès aux cours, TD et messagerie'],
        ];

        foreach ($roles as $name => $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                [
                    'guard_name' => 'web',
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function ensureTeacher(string $username, string $name, string $specialization, $now): int
    {
        $email = $username.'@timahacademy.cm';

        $userData = [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $now,
            'password' => Hash::make('12345678'),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('users', 'username')) {
            $userData['username'] = $username;
        }
        if (Schema::hasColumn('users', 'full_name')) {
            $userData['full_name'] = $name;
        }
        if (Schema::hasColumn('users', 'phone')) {
            $userData['phone'] = '692762065';
        }
        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = 'active';
        }

        DB::table('users')->updateOrInsert(['email' => $email], $userData);

        $teacherId = (int) DB::table('users')->where('email', $email)->value('id');
        $teacherRoleId = (int) DB::table('roles')->where('name', 'teacher')->value('id');

        if ($teacherRoleId) {
            DB::table('role_user')->updateOrInsert(
                ['role_id' => $teacherRoleId, 'user_id' => $teacherId],
                ['role_id' => $teacherRoleId, 'user_id' => $teacherId]
            );
        }

        if (Schema::hasTable('teacher_profiles')) {
            DB::table('teacher_profiles')->updateOrInsert(
                ['user_id' => $teacherId],
                [
                    'bio' => $name.' - TIMAH ACADEMY',
                    'specialization' => $specialization,
                    'contact_email' => $email,
                    'contact_phone' => '692762065',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        return $teacherId;
    }

    private function firstAdminId(): ?int
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($adminRoleId) {
            $adminId = DB::table('role_user')->where('role_id', $adminRoleId)->value('user_id');
            if ($adminId) {
                return (int) $adminId;
            }
        }

        return DB::table('users')->orderBy('id')->value('id');
    }
};
