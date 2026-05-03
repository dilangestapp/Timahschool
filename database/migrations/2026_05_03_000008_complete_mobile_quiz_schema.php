<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_quizzes')) {
            Schema::table('mobile_quizzes', function (Blueprint $table) {
                if (!Schema::hasColumn('mobile_quizzes', 'requires_subscription')) {
                    $table->boolean('requires_subscription')->default(true)->after('pass_score');
                }
            });
        }

        if (!Schema::hasTable('mobile_quiz_questions')) {
            Schema::create('mobile_quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mobile_quiz_id')->constrained('mobile_quizzes')->cascadeOnDelete();
                $table->text('question');
                $table->string('type', 60)->default('qcm');
                $table->json('choices')->nullable();
                $table->string('correct_answer')->nullable();
                $table->text('explanation')->nullable();
                $table->unsignedInteger('points')->default(1);
                $table->unsignedInteger('position')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_quiz_questions');
    }
};
