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
            if (!Schema::hasColumn('courses', 'document_path')) {
                $table->string('document_path')->nullable()->after('thumbnail');
            }
            if (!Schema::hasColumn('courses', 'document_name')) {
                $table->string('document_name')->nullable()->after('document_path');
            }
            if (!Schema::hasColumn('courses', 'document_mime')) {
                $table->string('document_mime', 150)->nullable()->after('document_name');
            }
            if (!Schema::hasColumn('courses', 'document_size')) {
                $table->unsignedBigInteger('document_size')->nullable()->after('document_mime');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            foreach (['document_size', 'document_mime', 'document_name', 'document_path'] as $column) {
                if (Schema::hasColumn('courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
