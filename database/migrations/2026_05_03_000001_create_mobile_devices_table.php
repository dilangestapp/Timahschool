<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_devices')) {
            return;
        }

        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 40)->nullable()->index();
            $table->string('device_id', 191)->index();
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('app_version', 80)->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->timestamp('first_login_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_devices');
    }
};
