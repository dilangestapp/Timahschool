<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_login_activities')) {
            return;
        }

        Schema::create('user_login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 30)->default('login');
            $table->string('ip_address', 80)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('guard', 60)->nullable();
            $table->string('session_id', 191)->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'event']);
            $table->index(['event', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_activities');
    }
};
