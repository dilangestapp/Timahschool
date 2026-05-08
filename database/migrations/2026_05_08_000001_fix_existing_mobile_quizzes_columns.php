<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mobile_quizzes')) {
            Schema::create('mobile_quizzes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('learning_program_schedule_id')->nullable()->constrained('learning_program_schedules')->nullOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->unsignedSmallInteger('pass_mark')->default(10);
                $table->string('status', 40)->default('published')->index();
                $table->timestamp('opens_at')->nullable()->index();
                $table->timestamp('closes_at')->nullable();
                $table->boolean('requires_subscription')->default(true);
                $table->timestamps();
            });

            return;
        }

        Schema::table('mobile_quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('mobile_quizzes', 'learning_program_schedule_id')) {
                $table->foreignId('learning_program_schedule_id')->nullable()->after('id')->constrained('learning_program_schedules')->nullOnDelete();
            }
            if (!Schema::hasColumn('mobile_quizzes', 'school_class_id')) {
                $table->foreignId('school_class_id')->nullable()->after('learning_program_schedule_id')->constrained('school_classes')->nullOnDelete();
            }
            if (!Schema::hasColumn('mobile_quizzes', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('school_class_id')->constrained('subjects')->nullOnDelete();
            }
            if (!Schema::hasColumn('mobile_quizzes', 'duration_minutes')) {
                $table->unsignedInteger('duration_minutes')->nullable()->after('description');
            }
            if (!Schema::hasColumn('mobile_quizzes', 'pass_mark')) {
                $table->unsignedSmallInteger('pass_mark')->default(10)->after('duration_minutes');
            }
            if (!Schema::hasColumn('mobile_quizzes', 'status')) {
                $table->string('status', 40)->default('published')->index()->after('pass_mark');
            }
            if (!Schema::hasColumn('mobile_quizzes', 'opens_at')) {
                $table->timestamp('opens_at')->nullable()->index()->after('status');
            }
            if (!Schema::hasColumn('mobile_quizzes', 'closes_at')) {
                $table->timestamp('closes_at')->nullable()->after('opens_at');
            }
            if (!Schema::hasColumn('mobile_quizzes', 'requires_subscription')) {
                $table->boolean('requires_subscription')->default(true)->after('closes_at');
            }
        });
    }

    public function down(): void
    {
        // Corrective migration: keep columns to avoid data loss on rollback.
    }
};
