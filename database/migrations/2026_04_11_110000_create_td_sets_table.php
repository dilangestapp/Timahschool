<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_sets')) {
            return;
        }

        Schema::create('td_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_class_id')->index();
            $table->unsignedBigInteger('subject_id')->index();
            $table->unsignedBigInteger('teacher_assignment_id')->nullable()->index();
            $table->unsignedBigInteger('author_user_id')->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('chapter_label')->nullable();
            $table->text('summary')->nullable();
            $table->longText('instructions_html')->nullable();
            $table->longText('correction_html')->nullable();
            $table->string('difficulty', 30)->default('medium');
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->string('access_level', 30)->default('free');
            $table->string('td_type', 50)->default('training');
            $table->string('status', 30)->default('draft');
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->string('document_mime')->nullable();
            $table->unsignedBigInteger('document_size')->nullable();
            $table->string('correction_document_path')->nullable();
            $table->string('correction_document_name')->nullable();
            $table->string('correction_document_mime')->nullable();
            $table->unsignedBigInteger('correction_document_size')->nullable();
            $table->string('correction_mode', 50)->default('after_submit');
            $table->timestamp('correction_release_at')->nullable();
            $table->string('source_type', 50)->default('original');
            $table->string('source_label')->nullable();
            $table->string('license_type')->nullable();
            $table->boolean('rights_confirmed')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_sets');
    }
};
