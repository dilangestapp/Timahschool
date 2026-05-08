<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pedagogical_bank_items', 'td_sets'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'document_drive_id')) {
                    $table->string('document_drive_id')->nullable()->after('document_path');
                }
                if (!Schema::hasColumn($tableName, 'document_drive_url')) {
                    $table->text('document_drive_url')->nullable()->after('document_drive_id');
                }
                if (!Schema::hasColumn($tableName, 'correction_document_drive_id')) {
                    $table->string('correction_document_drive_id')->nullable()->after('correction_document_path');
                }
                if (!Schema::hasColumn($tableName, 'correction_document_drive_url')) {
                    $table->text('correction_document_drive_url')->nullable()->after('correction_document_drive_id');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['pedagogical_bank_items', 'td_sets'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['document_drive_id', 'document_drive_url', 'correction_document_drive_id', 'correction_document_drive_url'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
