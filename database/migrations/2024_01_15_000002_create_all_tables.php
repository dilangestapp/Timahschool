<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLES SANS CLÉS ÉTRANGÈRES D'ABORD
        
        // Classes
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('level');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Matières
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Plans d'abonnement
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('duration_unit', ['day', 'week', 'month', 'year']);
            $table->integer('duration_value');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Paiements (sans la clé étrangère subscription_plan_id pour l'instant)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('notchpay_reference')->unique();
            $table->string('notchpay_transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded']);
            $table->string('payment_method')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('notchpay_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // 2. TABLES AVEC CLÉS ÉTRANGÈRES ENSUITE

        // Pivot classe-matière
        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_class_id', 'subject_id']);
        });

        // Profils élèves
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_class_id')->constrained();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('trial_used')->default(false);
            $table->timestamps();
        });

        // Profils enseignants
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->string('specialization')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Abonnements (sans la clé payment_id pour l'instant)
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->nullable()->constrained();
            $table->string('plan_name')->nullable();
            $table->enum('status', ['trial', 'pending', 'active', 'expired', 'cancelled', 'failed']);
            $table->boolean('is_trial')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable(); // Sans contrainte pour l'instant
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('ends_at');
        });

        // Cours
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('school_class_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->string('level')->default('beginner');
            $table->string('thumbnail')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // FAQ
        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->json('keywords')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. AJOUTER LES CLÉS ÉTRANGÈRES MANQUANTES APRÈS

        // Ajouter payment_id à payments (subscription_plan_id)
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->constrained()->after('user_id');
        });

        // Ajouter la clé étrangère payment_id à subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_items');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('class_subject');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('school_classes');
    }
};