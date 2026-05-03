<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('learning_program_schedules')) {
            return;
        }

        Schema::create('learning_program_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('activity_type', 60)->default('course')->index();
            $table->unsignedTinyInteger('week_number')->default(1);
            $table->unsignedTinyInteger('weekday')->default(1);
            $table->time('unlock_time')->nullable();
            $table->timestamp('unlocks_at')->nullable()->index();
            $table->timestamp('closes_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('status', 40)->default('scheduled')->index();
            $table->boolean('requires_subscription')->default(true);
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_program_schedules');
    }
};
