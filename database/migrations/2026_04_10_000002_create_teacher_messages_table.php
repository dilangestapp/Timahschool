<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_messages')) {
            Schema::create('teacher_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_assignment_id')->nullable();
                $table->unsignedBigInteger('teacher_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('school_class_id');
                $table->unsignedBigInteger('subject_id');
                $table->string('title');
                $table->longText('message');
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('status', 30)->default('unread');
                $table->timestamp('read_at')->nullable();
                $table->longText('reply_message')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('teacher_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_messages', 'teacher_assignment_id')) {
                $table->unsignedBigInteger('teacher_assignment_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('teacher_messages', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->after('teacher_assignment_id');
            }

            if (!Schema::hasColumn('teacher_messages', 'student_id')) {
                $table->unsignedBigInteger('student_id')->after('teacher_id');
            }

            if (!Schema::hasColumn('teacher_messages', 'school_class_id')) {
                $table->unsignedBigInteger('school_class_id')->after('student_id');
            }

            if (!Schema::hasColumn('teacher_messages', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->after('school_class_id');
            }

            if (!Schema::hasColumn('teacher_messages', 'title')) {
                $table->string('title')->after('subject_id');
            }

            if (!Schema::hasColumn('teacher_messages', 'message')) {
                $table->longText('message')->after('title');
            }

            if (!Schema::hasColumn('teacher_messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }

            if (!Schema::hasColumn('teacher_messages', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_path');
            }

            if (!Schema::hasColumn('teacher_messages', 'status')) {
                $table->string('status', 30)->default('unread')->after('attachment_name');
            }

            if (!Schema::hasColumn('teacher_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('teacher_messages', 'reply_message')) {
                $table->longText('reply_message')->nullable()->after('read_at');
            }

            if (!Schema::hasColumn('teacher_messages', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('reply_message');
            }

            if (!Schema::hasColumn('teacher_messages', 'created_at') || !Schema::hasColumn('teacher_messages', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('teacher_messages')) {
            return;
        }

        Schema::dropIfExists('teacher_messages');
    }
};
