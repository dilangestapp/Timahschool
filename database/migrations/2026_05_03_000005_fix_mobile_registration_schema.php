<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_profiles') && Schema::hasColumn('student_profiles', 'school_class_id')) {
            try {
                DB::statement('ALTER TABLE student_profiles MODIFY school_class_id BIGINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // La colonne est peut-être déjà nullable selon la base. On laisse la migration continuer.
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'email')) {
            try {
                DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
            } catch (\Throwable $e) {
                // Les comptes mobiles utilisent maintenant un email technique, mais on sécurise aussi la colonne.
            }
        }
    }

    public function down(): void
    {
        // Migration de compatibilité : aucune marche arrière destructive.
    }
};
