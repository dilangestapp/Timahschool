<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pedagogical_bank_items')) {
            Schema::create('pedagogical_bank_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_class_id')->nullable()->index();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('code')->nullable()->index();
                $table->string('title');
                $table->string('content_type')->default('td')->index();
                $table->string('inferred_class')->nullable();
                $table->string('inferred_subject')->nullable();
                $table->string('theme')->nullable();
                $table->string('document_path')->nullable();
                $table->string('document_name')->nullable();
                $table->string('document_mime')->nullable();
                $table->unsignedBigInteger('document_size')->nullable();
                $table->string('correction_document_path')->nullable();
                $table->string('correction_document_name')->nullable();
                $table->string('correction_document_mime')->nullable();
                $table->unsignedBigInteger('correction_document_size')->nullable();
                $table->string('status')->default('available')->index();
                $table->unsignedInteger('times_used')->default(0);
                $table->timestamp('last_scheduled_at')->nullable();
                $table->unsignedBigInteger('last_td_set_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('td_sets') && !Schema::hasColumn('td_sets', 'pedagogical_bank_item_id')) {
            Schema::table('td_sets', function (Blueprint $table) {
                $table->unsignedBigInteger('pedagogical_bank_item_id')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        // Safe rollback intentionally kept empty for production stability.
    }
};
