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
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mobile_quiz_questions')) {
            Schema::create('mobile_quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mobile_quiz_id')->constrained('mobile_quizzes')->cascadeOnDelete();
                $table->text('question');
                $table->json('choices')->nullable();
                $table->string('correct_answer')->nullable();
                $table->text('explanation')->nullable();
                $table->unsignedSmallInteger('points')->default(1);
                $table->unsignedSmallInteger('order')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mobile_quiz_attempts')) {
            Schema::create('mobile_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mobile_quiz_id')->constrained('mobile_quizzes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->json('answers')->nullable();
                $table->decimal('score', 6, 2)->default(0);
                $table->decimal('max_score', 6, 2)->default(0);
                $table->unsignedTinyInteger('percentage')->default(0);
                $table->string('status', 40)->default('submitted')->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mobile_notifications')) {
            Schema::create('mobile_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->string('audience', 60)->default('all')->index();
                $table->string('type', 60)->default('info')->index();
                $table->string('title');
                $table->text('message');
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mobile_activity_progress')) {
            Schema::create('mobile_activity_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('learning_program_schedule_id')->nullable()->constrained('learning_program_schedules')->nullOnDelete();
                $table->string('activity_type', 60)->default('course');
                $table->string('status', 40)->default('started')->index();
                $table->unsignedInteger('time_spent_seconds')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'learning_program_schedule_id'], 'mobile_progress_user_schedule_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_activity_progress');
        Schema::dropIfExists('mobile_notifications');
        Schema::dropIfExists('mobile_quiz_attempts');
        Schema::dropIfExists('mobile_quiz_questions');
        Schema::dropIfExists('mobile_quizzes');
    }
};
