<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_transformations')) {
            return;
        }

        Schema::create('td_transformations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('td_source_id')->index();
            $table->unsignedBigInteger('author_user_id')->index();
            $table->string('variant_type', 40)->default('similar');
            $table->longText('generation_notes')->nullable();
            $table->string('transformed_title');
            $table->text('transformed_summary')->nullable();
            $table->longText('transformed_instructions_html')->nullable();
            $table->longText('transformed_correction_html')->nullable();
            $table->string('transformed_chapter_label')->nullable();
            $table->string('transformed_difficulty', 30)->default('medium');
            $table->json('transformed_structure_json')->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedBigInteger('td_set_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_transformations');
    }
};
