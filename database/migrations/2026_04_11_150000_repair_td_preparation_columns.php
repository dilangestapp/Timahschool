<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_sources')) {
            Schema::table('td_sources', function (Blueprint $table) {
                if (!Schema::hasColumn('td_sources', 'teacher_assignment_id')) {
                    $table->unsignedBigInteger('teacher_assignment_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sources', 'uploaded_by')) {
                    $table->unsignedBigInteger('uploaded_by')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sources', 'source_kind')) {
                    $table->string('source_kind', 40)->default('text');
                }
                if (!Schema::hasColumn('td_sources', 'title')) {
                    $table->string('title')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'source_url')) {
                    $table->string('source_url')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'source_label')) {
                    $table->string('source_label')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'raw_text')) {
                    $table->longText('raw_text')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'extracted_text')) {
                    $table->longText('extracted_text')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'source_file_path')) {
                    $table->string('source_file_path')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'source_file_name')) {
                    $table->string('source_file_name')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'source_file_mime')) {
                    $table->string('source_file_mime')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'source_file_size')) {
                    $table->unsignedBigInteger('source_file_size')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'detected_school_class_id')) {
                    $table->unsignedBigInteger('detected_school_class_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sources', 'detected_subject_id')) {
                    $table->unsignedBigInteger('detected_subject_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sources', 'detected_chapter_label')) {
                    $table->string('detected_chapter_label')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'detected_difficulty')) {
                    $table->string('detected_difficulty', 30)->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'detected_structure_json')) {
                    $table->json('detected_structure_json')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'analysis_notes')) {
                    $table->longText('analysis_notes')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'prompt_ready_text')) {
                    $table->longText('prompt_ready_text')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'prompt_package_json')) {
                    $table->json('prompt_package_json')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'prepared_at')) {
                    $table->timestamp('prepared_at')->nullable();
                }
                if (!Schema::hasColumn('td_sources', 'status')) {
                    $table->string('status', 30)->default('imported');
                }
                if (!Schema::hasColumn('td_sources', 'rights_confirmed')) {
                    $table->boolean('rights_confirmed')->default(false);
                }
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
        } else {
            Schema::table('td_source_visuals', function (Blueprint $table) {
                if (!Schema::hasColumn('td_source_visuals', 'file_name')) {
                    $table->string('file_name')->nullable();
                }
                if (!Schema::hasColumn('td_source_visuals', 'file_mime')) {
                    $table->string('file_mime')->nullable();
                }
                if (!Schema::hasColumn('td_source_visuals', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable();
                }
                if (!Schema::hasColumn('td_source_visuals', 'exercise_label')) {
                    $table->string('exercise_label')->nullable();
                }
                if (!Schema::hasColumn('td_source_visuals', 'visual_role')) {
                    $table->string('visual_role', 30)->default('useful');
                }
                if (!Schema::hasColumn('td_source_visuals', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('td_source_visuals', 'position')) {
                    $table->unsignedInteger('position')->default(0);
                }
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
        } else {
            Schema::table('td_transformations', function (Blueprint $table) {
                if (!Schema::hasColumn('td_transformations', 'td_source_id')) {
                    $table->unsignedBigInteger('td_source_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_transformations', 'author_user_id')) {
                    $table->unsignedBigInteger('author_user_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_transformations', 'variant_type')) {
                    $table->string('variant_type', 40)->default('chatgpt_reworked');
                }
                if (!Schema::hasColumn('td_transformations', 'generation_notes')) {
                    $table->longText('generation_notes')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'prompt_snapshot')) {
                    $table->longText('prompt_snapshot')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'generated_title')) {
                    $table->string('generated_title')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'generated_summary')) {
                    $table->text('generated_summary')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'generated_instructions_html')) {
                    $table->longText('generated_instructions_html')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'generated_correction_html')) {
                    $table->longText('generated_correction_html')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'generated_structure_json')) {
                    $table->json('generated_structure_json')->nullable();
                }
                if (!Schema::hasColumn('td_transformations', 'status')) {
                    $table->string('status', 30)->default('imported');
                }
                if (!Schema::hasColumn('td_transformations', 'td_set_id')) {
                    $table->unsignedBigInteger('td_set_id')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('td_sets')) {
            Schema::table('td_sets', function (Blueprint $table) {
                if (!Schema::hasColumn('td_sets', 'td_source_id')) {
                    $table->unsignedBigInteger('td_source_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sets', 'td_transformation_id')) {
                    $table->unsignedBigInteger('td_transformation_id')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sets', 'review_notes')) {
                    $table->longText('review_notes')->nullable();
                }
                if (!Schema::hasColumn('td_sets', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable();
                }
                if (!Schema::hasColumn('td_sets', 'validated_at')) {
                    $table->timestamp('validated_at')->nullable();
                }
                if (!Schema::hasColumn('td_sets', 'validated_by')) {
                    $table->unsignedBigInteger('validated_by')->nullable()->index();
                }
                if (!Schema::hasColumn('td_sets', 'generation_mode')) {
                    $table->string('generation_mode', 40)->default('manual');
                }
            });
        }
    }

    public function down(): void
    {
        // volontairement vide pour éviter les pertes sur base existante
    }
};
