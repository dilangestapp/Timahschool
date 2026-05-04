<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_learning_profiles')) {
            Schema::create('student_learning_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->string('school_level')->nullable();
                $table->string('main_goal')->nullable();
                $table->string('target_exam')->nullable();
                $table->json('weak_subjects')->nullable();
                $table->json('study_times')->nullable();
                $table->string('preferred_study_time')->nullable();
                $table->string('parent_name')->nullable();
                $table->string('parent_phone')->nullable();
                $table->text('recommendation_title')->nullable();
                $table->text('recommendation_message')->nullable();
                $table->json('recommended_actions')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique('user_id');
                $table->index(['school_level', 'target_exam']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_learning_profiles');
    }
};
