<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('td_sets')) {
            Schema::create('td_sets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_class_id')->nullable()->index();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->unsignedBigInteger('teacher_assignment_id')->nullable()->index();
                $table->unsignedBigInteger('author_user_id')->nullable()->index();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('chapter_label')->nullable();
                $table->text('summary')->nullable();
                $table->longText('instructions_html')->nullable();
                $table->longText('correction_html')->nullable();
                $table->string('difficulty', 30)->default('medium');
                $table->unsignedInteger('estimated_minutes')->nullable();
                $table->string('access_level', 20)->default('free');
                $table->string('td_type', 40)->default('training');
                $table->string('status', 30)->default('draft');
                $table->string('document_path')->nullable();
                $table->string('document_name')->nullable();
                $table->string('document_mime')->nullable();
                $table->unsignedBigInteger('document_size')->nullable();
                $table->string('correction_document_path')->nullable();
                $table->string('correction_document_name')->nullable();
                $table->string('correction_document_mime')->nullable();
                $table->unsignedBigInteger('correction_document_size')->nullable();
                $table->string('correction_mode', 30)->default('after_submit');
                $table->timestamp('correction_release_at')->nullable();
                $table->string('source_type', 40)->default('original');
                $table->string('source_label')->nullable();
                $table->string('license_type')->nullable();
                $table->boolean('rights_confirmed')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('td_attempts')) {
            Schema::create('td_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('td_set_id')->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->string('status', 30)->default('started');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('correction_unlocked_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('td_question_threads')) {
            Schema::create('td_question_threads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('td_set_id')->index();
                $table->unsignedBigInteger('school_class_id')->nullable()->index();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('teacher_id')->nullable()->index();
                $table->string('status', 30)->default('open');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('td_question_messages')) {
            Schema::create('td_question_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('thread_id')->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->string('sender_role', 30);
                $table->longText('message_html')->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_mime')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // volontairement vide pour éviter les pertes en environnement existant
    }
};
