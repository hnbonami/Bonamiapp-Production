<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie controleert of de email_triggers tabel al bestaat.
     */
    public function up(): void
    {
        // Controleer of de tabel al bestaat
        if (!Schema::hasTable('email_triggers')) {
            Schema::create('email_triggers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->unsignedBigInteger('email_template_id');
                $table->boolean('is_active')->default(true);
                $table->json('conditions');
                $table->json('settings')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->integer('emails_sent')->default(0);
                $table->timestamps();
                
                // Foreign key constraint
                $table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('cascade');
            });
            \Log::info('✅ Email_triggers tabel aangemaakt');
        } else {
            \Log::info('⚠️ Email_triggers tabel bestaat al - migratie overgeslagen');
            
            // Controleer en voeg ontbrekende kolommen toe indien nodig
            Schema::table('email_triggers', function (Blueprint $table) {
                if (!Schema::hasColumn('email_triggers', 'settings')) {
                    $table->json('settings')->nullable()->after('conditions');
                    \Log::info('✅ Settings kolom toegevoegd aan email_triggers tabel');
                }
                if (!Schema::hasColumn('email_triggers', 'emails_sent')) {
                    $table->integer('emails_sent')->default(0)->after('last_run_at');
                    \Log::info('✅ Emails_sent kolom toegevoegd aan email_triggers tabel');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_triggers');
    }
};