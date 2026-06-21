<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_weekly_programs')) {
            return;
        }

        Schema::create('teacher_weekly_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->nullable()->constrained('teacher_assignments')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('week_start')->index();
            $table->date('program_date')->index();
            $table->unsignedTinyInteger('weekday')->default(1);
            $table->string('start_time', 10)->nullable();
            $table->string('end_time', 10)->nullable();
            $table->string('activity_type', 40)->default('course');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['teacher_id', 'week_start']);
            $table->index(['teacher_id', 'program_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_weekly_programs');
    }
};
