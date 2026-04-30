<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('td_sets')) {
            return;
        }

        DB::table('td_sets')
            ->where('slug', 'like', 'td-inf-pa4-001-%')
            ->update([
                'source_label' => 'TD-INF-PA4-001',
                'source_type' => 'manual_content',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Nettoyage conservé volontairement.
    }
};
