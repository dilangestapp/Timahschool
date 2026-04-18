<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_attempts')) {
            return;
        }

        Schema::create('td_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('td_set_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('status', 30)->default('started');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('correction_unlocked_at')->nullable();
            $table->timestamps();
            $table->unique(['td_set_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_attempts');
    }
};
