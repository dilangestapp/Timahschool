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
                $table->longText('content_html')->nullable()->after('objectives');
            }

            if (!Schema::hasColumn('courses', 'content_text')) {
                $table->text('content_text')->nullable()->after('content_html');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('courses', 'content_text')) {
                $drops[] = 'content_text';
            }

            if (Schema::hasColumn('courses', 'content_html')) {
                $drops[] = 'content_html';
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
