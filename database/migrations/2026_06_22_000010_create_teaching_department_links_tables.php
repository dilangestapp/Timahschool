<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teaching_department_school_class')) {
            Schema::create('teaching_department_school_class', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teaching_department_id');
                $table->unsignedBigInteger('school_class_id');
                $table->timestamps();
                $table->unique(['teaching_department_id', 'school_class_id'], 'tdsc_unique');
            });
        }

        if (!Schema::hasTable('teaching_department_subject')) {
            Schema::create('teaching_department_subject', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teaching_department_id');
                $table->unsignedBigInteger('subject_id');
                $table->timestamps();
                $table->unique(['teaching_department_id', 'subject_id'], 'tds_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_department_subject');
        Schema::dropIfExists('teaching_department_school_class');
    }
};
