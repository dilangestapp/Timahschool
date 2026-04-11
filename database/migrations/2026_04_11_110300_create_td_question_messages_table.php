<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_question_messages')) {
            return;
        }

        Schema::create('td_question_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id')->index();
            $table->unsignedBigInteger('sender_id')->index();
            $table->string('sender_role', 20);
            $table->longText('message_html')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_question_messages');
    }
};
