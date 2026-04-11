<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('td_sets')) {
            return;
        }

        Schema::table('td_sets', function (Blueprint $table) {
            if (!Schema::hasColumn('td_sets', 'td_source_id')) {
                $table->unsignedBigInteger('td_source_id')->nullable()->after('author_user_id')->index();
            }
            if (!Schema::hasColumn('td_sets', 'td_transformation_id')) {
                $table->unsignedBigInteger('td_transformation_id')->nullable()->after('td_source_id')->index();
            }
            if (!Schema::hasColumn('td_sets', 'review_notes')) {
                $table->longText('review_notes')->nullable()->after('rights_confirmed');
            }
            if (!Schema::hasColumn('td_sets', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('review_notes');
            }
            if (!Schema::hasColumn('td_sets', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('td_sets', 'validated_by')) {
                $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at')->index();
            }
            if (!Schema::hasColumn('td_sets', 'generation_mode')) {
                $table->string('generation_mode', 40)->default('manual')->after('validated_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('td_sets')) {
            return;
        }

        Schema::table('td_sets', function (Blueprint $table) {
            foreach (['td_source_id', 'td_transformation_id', 'review_notes', 'submitted_at', 'validated_at', 'validated_by', 'generation_mode'] as $column) {
                if (Schema::hasColumn('td_sets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
