<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        DB::table('roles')->updateOrInsert(
            ['name' => 'technical_supervisor'],
            [
                'guard_name' => 'web',
                'display_name' => 'Responsable Enseignement Technique',
                'description' => 'Suivi pédagogique de l’enseignement technique.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // Donnée conservée pour protéger les comptes existants.
    }
};
