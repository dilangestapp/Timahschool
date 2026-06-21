<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teaching_divisions')) {
            Schema::create('teaching_divisions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type', 60)->default('general')->index();
                $table->text('description')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('teaching_departments')) {
            Schema::create('teaching_departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teaching_division_id')->nullable()->constrained('teaching_divisions')->nullOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('code', 30)->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();

                $table->index(['teaching_division_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('pedagogical_responsibilities')) {
            Schema::create('pedagogical_responsibilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role_title', 120);
                $table->string('scope_type', 40)->default('platform')->index();
                $table->foreignId('teaching_division_id')->nullable()->constrained('teaching_divisions')->nullOnDelete();
                $table->foreignId('teaching_department_id')->nullable()->constrained('teaching_departments')->nullOnDelete();
                $table->boolean('can_view_reports')->default(true);
                $table->boolean('can_send_alerts')->default(true);
                $table->boolean('can_validate_content')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->text('notes')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamps();

                $table->index(['scope_type', 'is_active']);
                $table->index(['user_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('pedagogical_supervision_notes')) {
            Schema::create('pedagogical_supervision_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('responsibility_id')->nullable()->constrained('pedagogical_responsibilities')->nullOnDelete();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('teaching_division_id')->nullable()->constrained('teaching_divisions')->nullOnDelete();
                $table->foreignId('teaching_department_id')->nullable()->constrained('teaching_departments')->nullOnDelete();
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('severity', 30)->default('info')->index();
                $table->string('status', 30)->default('open')->index();
                $table->timestamp('due_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'severity']);
            });
        }

        if (Schema::hasTable('teaching_divisions') && DB::table('teaching_divisions')->count() === 0) {
            DB::table('teaching_divisions')->insert([
                ['name' => 'Enseignement général francophone', 'slug' => 'enseignement-general-francophone', 'type' => 'general', 'description' => 'Suivi des classes et matières de l’enseignement général francophone.', 'order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Enseignement technique', 'slug' => 'enseignement-technique', 'type' => 'technical', 'description' => 'Suivi des filières techniques, ateliers, spécialités et matières professionnelles.', 'order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Enseignement anglophone', 'slug' => 'enseignement-anglophone', 'type' => 'anglophone', 'description' => 'Suivi des classes, contenus et enseignants du système anglophone.', 'order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Primaire et maternelle', 'slug' => 'primaire-et-maternelle', 'type' => 'primary', 'description' => 'Suivi des contenus destinés aux petits niveaux et à l’école primaire.', 'order' => 40, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Classes d’examen', 'slug' => 'classes-examen', 'type' => 'exam', 'description' => 'Contrôle renforcé des classes d’examen, TD, révisions et corrections.', 'order' => 50, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Schema::hasTable('teaching_departments') && DB::table('teaching_departments')->count() === 0) {
            $technicalId = DB::table('teaching_divisions')->where('slug', 'enseignement-technique')->value('id');
            $generalId = DB::table('teaching_divisions')->where('slug', 'enseignement-general-francophone')->value('id');
            DB::table('teaching_departments')->insert([
                ['teaching_division_id' => $technicalId, 'name' => 'Département électrotechnique', 'slug' => 'departement-electrotechnique', 'code' => 'ELT', 'description' => 'Suivi des contenus, TD et révisions liés à l’électrotechnique.', 'order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['teaching_division_id' => $technicalId, 'name' => 'Département informatique', 'slug' => 'departement-informatique', 'code' => 'INFO', 'description' => 'Suivi des contenus informatiques, travaux pratiques, TD et corrections.', 'order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['teaching_division_id' => $technicalId, 'name' => 'Département comptabilité', 'slug' => 'departement-comptabilite', 'code' => 'CPT', 'description' => 'Suivi des contenus commerciaux, comptables et économiques.', 'order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['teaching_division_id' => $generalId, 'name' => 'Département mathématiques', 'slug' => 'departement-mathematiques', 'code' => 'MATH', 'description' => 'Suivi des cours, exercices, TD et corrections en mathématiques.', 'order' => 40, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['teaching_division_id' => $generalId, 'name' => 'Département français', 'slug' => 'departement-francais', 'code' => 'FR', 'description' => 'Suivi des contenus de langue française, expression écrite et littérature.', 'order' => 50, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pedagogical_supervision_notes');
        Schema::dropIfExists('pedagogical_responsibilities');
        Schema::dropIfExists('teaching_departments');
        Schema::dropIfExists('teaching_divisions');
    }
};
