<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'is_downloadable')) {
                $table->boolean('is_downloadable')->default(true)->after('document_size');
            }

            if (!Schema::hasColumn('courses', 'mobile_access')) {
                $table->string('mobile_access', 40)->default('subscription')->after('is_downloadable');
            }

            if (!Schema::hasColumn('courses', 'estimated_minutes')) {
                $table->unsignedInteger('estimated_minutes')->nullable()->after('mobile_access');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            foreach (['estimated_minutes', 'mobile_access', 'is_downloadable'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
