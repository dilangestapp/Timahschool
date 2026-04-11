<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('td_sources')) {
            Schema::create('td_sources', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_assignment_id')->nullable()->index();
                $table->unsignedBigInteger('uploaded_by')->index();
                $table->string('source_kind', 40)->default('text');
                $table->string('title')->nullable();
                $table->string('source_url')->nullable();
                $table->string('source_label')->nullable();
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
                $table->longText('prompt_ready_text')->nullable();
                $table->json('prompt_package_json')->nullable();
                $table->timestamp('prepared_at')->nullable();
                $table->string('status', 30)->default('imported');
                $table->boolean('rights_confirmed')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('td_source_visuals')) {
            Schema::create('td_source_visuals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('td_source_id')->index();
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->string('file_mime')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('exercise_label')->nullable();
                $table->string('visual_role', 30)->default('useful');
                $table->text('notes')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('td_transformations')) {
            Schema::create('td_transformations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('td_source_id')->index();
                $table->unsignedBigInteger('author_user_id')->index();
                $table->string('variant_type', 40)->default('chatgpt_reworked');
                $table->longText('generation_notes')->nullable();
                $table->longText('prompt_snapshot')->nullable();
                $table->string('generated_title');
                $table->text('generated_summary')->nullable();
                $table->longText('generated_instructions_html')->nullable();
                $table->longText('generated_correction_html')->nullable();
                $table->json('generated_structure_json')->nullable();
                $table->string('status', 30)->default('imported');
                $table->unsignedBigInteger('td_set_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('td_sets')) {
            Schema::table('td_sets', function (Blueprint $table) {
                if (!Schema::hasColumn('td_sets', 'td_source_id')) {
                    $table->unsignedBigInteger('td_source_id')->nullable()->after('author_user_id')->index();
                }
                if (!Schema::hasColumn('td_sets', 'td_transformation_id')) {
                    $table->unsignedBigInteger('td_transformation_id')->nullable()->after('td_source_id')->index();
                }
                if (!Schema::hasColumn('td_sets', 'review_notes')) {
                    $table->longText('review_notes')->nullable()->after('rights_confirmed');
                }
                if (!Schema::hasColumn('td_sets', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('review_notes');
                }
                if (!Schema::hasColumn('td_sets', 'validated_at')) {
                    $table->timestamp('validated_at')->nullable()->after('submitted_at');
                }
                if (!Schema::hasColumn('td_sets', 'validated_by')) {
                    $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at')->index();
                }
                if (!Schema::hasColumn('td_sets', 'generation_mode')) {
                    $table->string('generation_mode', 40)->default('manual')->after('validated_by');
                }
            });
        }
    }

    public function down(): void
    {
        // volontairement vide pour éviter les pertes en environnement existant
    }
};
