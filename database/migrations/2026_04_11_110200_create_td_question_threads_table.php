<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_question_threads')) {
            return;
        }

        Schema::create('td_question_threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('td_set_id')->index();
            $table->unsignedBigInteger('school_class_id')->index();
            $table->unsignedBigInteger('subject_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('teacher_id')->index();
            $table->string('status', 30)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_question_threads');
    }
};
