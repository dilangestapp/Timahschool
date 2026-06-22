<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('technical_departments')) {
            Schema::create('technical_departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 40)->default('active')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('technical_reminders')) {
            Schema::create('technical_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('target_type', 60)->default('user')->index();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('priority', 30)->default('normal')->index();
                $table->string('status', 40)->default('sent')->index();
                $table->timestamp('due_at')->nullable();
                $table->timestamp('sent_at')->nullable()->index();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_reminders');
        Schema::dropIfExists('technical_departments');
    }
};
