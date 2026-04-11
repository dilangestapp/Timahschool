<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_sources')) {
            return;
        }

        Schema::create('td_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_assignment_id')->nullable()->index();
            $table->unsignedBigInteger('uploaded_by')->index();
            $table->string('source_kind', 40)->default('text');
            $table->string('title')->nullable();
            $table->string('source_url')->nullable();
            $table->string('source_label')->nullable();
            $table->longText('prompt_text')->nullable();
            $table->longText('raw_text')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('source_file_path')->nullable();
            $table->string('source_file_name')->nullable();
            $table->string('source_file_mime')->nullable();
            $table->unsignedBigInteger('source_file_size')->nullable();
            $table->unsignedBigInteger('detected_school_class_id')->nullable()->index();
            $table->unsignedBigInteger('detected_subject_id')->nullable()->index();
            $table->string('detected_chapter_label')->nullable();
            $table->string('detected_difficulty', 30)->nullable();
            $table->json('detected_structure_json')->nullable();
            $table->longText('analysis_notes')->nullable();
            $table->string('status', 30)->default('imported');
            $table->boolean('rights_confirmed')->default(false);
            $table->timestamp('last_analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_sources');
    }
};
