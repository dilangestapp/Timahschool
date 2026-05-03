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
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('questions')->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->unsignedTinyInteger('pass_score')->default(10);
                $table->string('status', 40)->default('published')->index();
                $table->timestamp('opens_at')->nullable();
                $table->timestamp('closes_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mobile_quiz_attempts')) {
            Schema::create('mobile_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('mobile_quiz_id')->constrained('mobile_quizzes')->cascadeOnDelete();
                $table->json('answers')->nullable();
                $table->decimal('score', 5, 2)->default(0);
                $table->unsignedInteger('correct_count')->default(0);
                $table->unsignedInteger('total_questions')->default(0);
                $table->string('status', 40)->default('completed')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mobile_notifications')) {
            Schema::create('mobile_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('type', 60)->default('info')->index();
                $table->string('audience', 60)->default('all')->index();
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
                $table->string('activity_type', 60)->nullable();
                $table->string('status', 40)->default('started')->index();
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->decimal('score', 5, 2)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('meta')->nullable();
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
        Schema::dropIfExists('mobile_quizzes');
    }
};
