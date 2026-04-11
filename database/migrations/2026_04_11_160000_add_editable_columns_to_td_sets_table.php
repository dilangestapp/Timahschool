<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('td_sets')) {
            return;
        }

        Schema::table('td_sets', function (Blueprint $table) {
            if (!Schema::hasColumn('td_sets', 'editable_html')) {
                $table->longText('editable_html')->nullable()->after('document_size');
            }
            if (!Schema::hasColumn('td_sets', 'editable_text')) {
                $table->longText('editable_text')->nullable()->after('editable_html');
            }
            if (!Schema::hasColumn('td_sets', 'has_editable_version')) {
                $table->boolean('has_editable_version')->default(false)->after('editable_text');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('td_sets')) {
            return;
        }

        Schema::table('td_sets', function (Blueprint $table) {
            $drops = [];
            foreach (['has_editable_version', 'editable_text', 'editable_html'] as $column) {
                if (Schema::hasColumn('td_sets', $column)) {
                    $drops[] = $column;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
