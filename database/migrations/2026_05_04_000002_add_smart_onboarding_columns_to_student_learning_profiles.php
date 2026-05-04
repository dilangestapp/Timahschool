<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_learning_profiles')) {
            return;
        }

        Schema::table('student_learning_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_learning_profiles', 'school_class_id')) {
                $table->unsignedBigInteger('school_class_id')->nullable()->after('user_id');
                $table->index('school_class_id');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'school_level')) {
                $table->string('school_level')->nullable()->after('school_class_id');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'study_times')) {
                $table->json('study_times')->nullable()->after('weak_subjects');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'preferred_study_time')) {
                $table->string('preferred_study_time')->nullable()->after('study_times');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'parent_name')) {
                $table->string('parent_name')->nullable()->after('preferred_study_time');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'parent_phone')) {
                $table->string('parent_phone')->nullable()->after('parent_name');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'recommendation_title')) {
                $table->text('recommendation_title')->nullable()->after('parent_phone');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'recommendation_message')) {
                $table->text('recommendation_message')->nullable()->after('recommendation_title');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'recommended_actions')) {
                $table->json('recommended_actions')->nullable()->after('recommendation_message');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'generated_summary')) {
                $table->text('generated_summary')->nullable()->after('recommended_actions');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'diagnostic_completed_at')) {
                $table->timestamp('diagnostic_completed_at')->nullable()->after('generated_summary');
            }
            if (!Schema::hasColumn('student_learning_profiles', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('diagnostic_completed_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_learning_profiles')) {
            return;
        }

        Schema::table('student_learning_profiles', function (Blueprint $table) {
            foreach ([
                'school_class_id',
                'school_level',
                'study_times',
                'preferred_study_time',
                'parent_name',
                'parent_phone',
                'recommendation_title',
                'recommendation_message',
                'recommended_actions',
                'generated_summary',
                'diagnostic_completed_at',
                'completed_at',
            ] as $column) {
                if (Schema::hasColumn('student_learning_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
