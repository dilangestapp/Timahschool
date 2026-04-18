<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('td_attempts')) {
            return;
        }

        Schema::table('td_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('td_attempts', 'opened_at')) {
                $table->timestamp('opened_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('td_attempts', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('opened_at');
            }
        });

        // Tente d'ajouter l'unicité td_set_id + student_id si absente.
        try {
            Schema::table('td_attempts', function (Blueprint $table) {
                $table->unique(['td_set_id', 'student_id'], 'td_attempts_td_set_id_student_id_unique');
            });
        } catch (\Throwable $e) {
            // Index déjà présent ou impossible à créer à cause de doublons existants.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('td_attempts')) {
            return;
        }

        try {
            Schema::table('td_attempts', function (Blueprint $table) {
                $table->dropUnique('td_attempts_td_set_id_student_id_unique');
            });
        } catch (\Throwable $e) {
            // noop
        }

        Schema::table('td_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('td_attempts', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

            if (Schema::hasColumn('td_attempts', 'opened_at')) {
                $table->dropColumn('opened_at');
            }
        });
    }
};
