<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('annual_programs')) {
            Schema::create('annual_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
                $table->string('school_year', 20)->default('2026-2027')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status', 40)->default('draft')->index();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('annual_program_items')) {
            Schema::create('annual_program_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('annual_program_id')->constrained('annual_programs')->cascadeOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
                $table->foreignId('td_set_id')->nullable()->constrained('td_sets')->nullOnDelete();
                $table->string('period_label', 80)->nullable();
                $table->string('chapter_title');
                $table->text('objectives')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->string('status', 40)->default('planned')->index();
                $table->date('starts_on')->nullable();
                $table->date('ends_on')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('message_voice_downloads')) {
            Schema::create('message_voice_downloads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('message_table', 80)->default('teacher_messages');
                $table->unsignedBigInteger('message_id');
                $table->string('file_path');
                $table->string('local_cache_key')->nullable();
                $table->timestamp('downloaded_at')->nullable();
                $table->timestamps();
                $table->index(['message_table', 'message_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_voice_downloads');
        Schema::dropIfExists('annual_program_items');
        Schema::dropIfExists('annual_programs');
    }
};
