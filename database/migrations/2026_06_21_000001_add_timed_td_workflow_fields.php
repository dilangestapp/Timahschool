<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('td_sets')) {
            Schema::table('td_sets', function (Blueprint $table) {
                if (!Schema::hasColumn('td_sets', 'opens_at')) {
                    $table->timestamp('opens_at')->nullable()->after('published_at')->index();
                }
                if (!Schema::hasColumn('td_sets', 'closes_at')) {
                    $table->timestamp('closes_at')->nullable()->after('opens_at')->index();
                }
                if (!Schema::hasColumn('td_sets', 'duration_minutes')) {
                    $table->unsignedInteger('duration_minutes')->nullable()->after('closes_at');
                }
                if (!Schema::hasColumn('td_sets', 'penalty_label')) {
                    $table->string('penalty_label')->nullable()->after('duration_minutes');
                }
                if (!Schema::hasColumn('td_sets', 'allow_makeup')) {
                    $table->boolean('allow_makeup')->default(true)->after('penalty_label');
                }
                if (!Schema::hasColumn('td_sets', 'submission_type')) {
                    $table->string('submission_type', 40)->default('manual_photo_pdf')->after('allow_makeup');
                }
            });
        }

        if (Schema::hasTable('td_attempts')) {
            Schema::table('td_attempts', function (Blueprint $table) {
                if (!Schema::hasColumn('td_attempts', 'submission_deadline_at')) {
                    $table->timestamp('submission_deadline_at')->nullable()->after('opened_at')->index();
                }
                if (!Schema::hasColumn('td_attempts', 'expired_at')) {
                    $table->timestamp('expired_at')->nullable()->after('submitted_at');
                }
                if (!Schema::hasColumn('td_attempts', 'missed_at')) {
                    $table->timestamp('missed_at')->nullable()->after('expired_at');
                }
                if (!Schema::hasColumn('td_attempts', 'submitted_document_path')) {
                    $table->string('submitted_document_path')->nullable()->after('correction_unlocked_at');
                }
                if (!Schema::hasColumn('td_attempts', 'submitted_document_name')) {
                    $table->string('submitted_document_name')->nullable()->after('submitted_document_path');
                }
                if (!Schema::hasColumn('td_attempts', 'submitted_document_mime')) {
                    $table->string('submitted_document_mime')->nullable()->after('submitted_document_name');
                }
                if (!Schema::hasColumn('td_attempts', 'submitted_document_size')) {
                    $table->unsignedBigInteger('submitted_document_size')->nullable()->after('submitted_document_mime');
                }
                if (!Schema::hasColumn('td_attempts', 'makeup_status')) {
                    $table->string('makeup_status', 30)->nullable()->after('submitted_document_size')->index();
                }
                if (!Schema::hasColumn('td_attempts', 'makeup_reason')) {
                    $table->text('makeup_reason')->nullable()->after('makeup_status');
                }
                if (!Schema::hasColumn('td_attempts', 'makeup_requested_at')) {
                    $table->timestamp('makeup_requested_at')->nullable()->after('makeup_reason');
                }
                if (!Schema::hasColumn('td_attempts', 'makeup_decided_at')) {
                    $table->timestamp('makeup_decided_at')->nullable()->after('makeup_requested_at');
                }
                if (!Schema::hasColumn('td_attempts', 'score')) {
                    $table->decimal('score', 8, 2)->nullable()->after('makeup_decided_at');
                }
                if (!Schema::hasColumn('td_attempts', 'max_score')) {
                    $table->decimal('max_score', 8, 2)->nullable()->after('score');
                }
                if (!Schema::hasColumn('td_attempts', 'teacher_feedback')) {
                    $table->text('teacher_feedback')->nullable()->after('max_score');
                }
                if (!Schema::hasColumn('td_attempts', 'correction_grid')) {
                    $table->json('correction_grid')->nullable()->after('teacher_feedback');
                }
                if (!Schema::hasColumn('td_attempts', 'annotations')) {
                    $table->json('annotations')->nullable()->after('correction_grid');
                }
            });
        }
    }

    public function down(): void
    {
        // Conservation volontaire des données de suivi pédagogique.
    }
};
