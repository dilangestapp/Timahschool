<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('biweekly_evaluations')) {
            Schema::create('biweekly_evaluations', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->timestamp('period_starts_at')->nullable();
                $table->timestamp('period_ends_at')->nullable();
                $table->timestamp('opens_at')->nullable()->index();
                $table->timestamp('closes_at')->nullable();
                $table->unsignedInteger('duration_minutes')->nullable();
                $table->string('status', 40)->default('scheduled')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('progress_reports')) {
            Schema::create('progress_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('biweekly_evaluation_id')->nullable()->constrained('biweekly_evaluations')->nullOnDelete();
                $table->timestamp('period_starts_at')->nullable();
                $table->timestamp('period_ends_at')->nullable();
                $table->unsignedTinyInteger('participation_rate')->default(0);
                $table->decimal('evaluation_score', 5, 2)->nullable();
                $table->unsignedSmallInteger('courses_done')->default(0);
                $table->unsignedSmallInteger('td_done')->default(0);
                $table->unsignedSmallInteger('quizzes_done')->default(0);
                $table->text('strengths')->nullable();
                $table->text('weaknesses')->nullable();
                $table->text('recommendations')->nullable();
                $table->string('status', 40)->default('draft')->index();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
        Schema::dropIfExists('biweekly_evaluations');
    }
};
