<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('parent_profiles')) {
            Schema::create('parent_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('full_name')->nullable();
                $table->string('phone', 60)->nullable()->index();
                $table->string('profession')->nullable();
                $table->string('address')->nullable();
                $table->string('status', 40)->default('pending')->index();
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
                $table->unique('user_id');
            });
        }

        if (!Schema::hasTable('student_parent')) {
            Schema::create('student_parent', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('parent_id')->index();
                $table->string('relationship', 60)->default('parent');
                $table->boolean('is_primary')->default(true);
                $table->timestamps();
                $table->unique(['student_id', 'parent_id'], 'student_parent_unique');
            });
        }

        if (!Schema::hasTable('course_progress')) {
            Schema::create('course_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->string('status', 40)->default('not_started')->index();
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['course_id', 'student_id'], 'course_progress_unique');
            });
        }

        if (Schema::hasTable('mobile_notifications')) {
            Schema::table('mobile_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('mobile_notifications', 'target_type')) {
                    $table->string('target_type')->nullable()->after('message');
                }
                if (!Schema::hasColumn('mobile_notifications', 'target_id')) {
                    $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
                }
                if (!Schema::hasColumn('mobile_notifications', 'data')) {
                    $table->json('data')->nullable()->after('message');
                }
                if (!Schema::hasColumn('mobile_notifications', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('data');
                }
                if (!Schema::hasColumn('mobile_notifications', 'seen_at')) {
                    $table->timestamp('seen_at')->nullable()->after('read_at');
                }
            });
        }

        if (Schema::hasTable('roles') && !DB::table('roles')->where('name', 'parent')->exists()) {
            $payload = ['name' => 'parent', 'created_at' => now(), 'updated_at' => now()];
            if (Schema::hasColumn('roles', 'guard_name')) $payload['guard_name'] = 'web';
            if (Schema::hasColumn('roles', 'display_name')) $payload['display_name'] = 'Parent';
            if (Schema::hasColumn('roles', 'description')) $payload['description'] = 'Compte parent TIMAH ACADEMY';
            DB::table('roles')->insert($payload);
        }
    }

    public function down(): void
    {
        // Données conservées volontairement.
    }
};
