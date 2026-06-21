<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('teacher_messages')) {
            Schema::create('teacher_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_assignment_id')->nullable()->index();
                $table->unsignedBigInteger('teacher_id')->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('school_class_id')->index();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->string('topic')->nullable();
                $table->string('title')->nullable();
                $table->longText('message')->nullable();
                $table->string('direction', 30)->default('student')->index();
                $table->unsignedBigInteger('parent_message_id')->nullable()->index();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_mime')->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->string('status', 30)->default('unread')->index();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->longText('reply_message')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamp('deleted_by_teacher_at')->nullable();
                $table->timestamp('deleted_by_student_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('teacher_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_messages', 'direction')) {
                $table->string('direction', 30)->default('student')->after('message')->index();
            }
            if (!Schema::hasColumn('teacher_messages', 'parent_message_id')) {
                $table->unsignedBigInteger('parent_message_id')->nullable()->after('direction')->index();
            }
            if (!Schema::hasColumn('teacher_messages', 'attachment_mime')) {
                $table->string('attachment_mime')->nullable()->after('attachment_name');
            }
            if (!Schema::hasColumn('teacher_messages', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
            }
            if (!Schema::hasColumn('teacher_messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('teacher_messages', 'deleted_by_teacher_at')) {
                $table->timestamp('deleted_by_teacher_at')->nullable()->after('replied_at');
            }
            if (!Schema::hasColumn('teacher_messages', 'deleted_by_student_at')) {
                $table->timestamp('deleted_by_student_at')->nullable()->after('deleted_by_teacher_at');
            }
        });
    }

    public function down(): void
    {
        // Conservation volontaire des messages.
    }
};
