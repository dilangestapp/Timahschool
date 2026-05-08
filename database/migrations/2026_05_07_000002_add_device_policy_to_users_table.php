<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'device_policy')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('device_policy', 40)->default('single')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'device_policy')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('device_policy');
            });
        }
    }
};
