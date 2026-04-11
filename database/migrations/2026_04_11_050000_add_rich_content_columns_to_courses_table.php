<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'content_html')) {
                $table->longText('content_html')->nullable()->after('document_size');
            }

            if (!Schema::hasColumn('courses', 'content_text')) {
                $table->longText('content_text')->nullable()->after('content_html');
            }

            if (!Schema::hasColumn('courses', 'editor_type')) {
                $table->string('editor_type', 50)->nullable()->after('content_text');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            foreach (['editor_type', 'content_text', 'content_html'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
