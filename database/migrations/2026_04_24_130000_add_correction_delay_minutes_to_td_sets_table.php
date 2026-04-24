<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('td_sets') && !Schema::hasColumn('td_sets', 'correction_delay_minutes')) {
            Schema::table('td_sets', function (Blueprint $table) {
                $table->unsignedSmallInteger('correction_delay_minutes')->default(30)->after('access_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('td_sets') && Schema::hasColumn('td_sets', 'correction_delay_minutes')) {
            Schema::table('td_sets', function (Blueprint $table) {
                $table->dropColumn('correction_delay_minutes');
            });
        }
    }
};
