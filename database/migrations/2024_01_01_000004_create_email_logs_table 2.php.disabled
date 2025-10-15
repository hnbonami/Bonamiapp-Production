<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie controleert of de email_logs tabel al bestaat.
     */
    public function up(): void
    {
        // Controleer of de tabel al bestaat
        if (!Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('email_template_id')->nullable();
                $table->unsignedBigInteger('email_trigger_id')->nullable();
                $table->string('recipient_email');
                $table->string('recipient_name')->nullable();
                $table->string('subject');
                $table->text('body_html');
                $table->enum('status', ['pending', 'sent', 'failed', 'bounced']);
                $table->string('trigger_type')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('clicked_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('email_template_id')->references('id')->on('email_templates')->onDelete('set null');
                $table->foreign('email_trigger_id')->references('id')->on('email_triggers')->onDelete('set null');
            });
            \Log::info('✅ Email_logs tabel aangemaakt');
        } else {
            \Log::info('⚠️ Email_logs tabel bestaat al - migratie overgeslagen');
            
            // Controleer en voeg ontbrekende kolommen toe indien nodig
            Schema::table('email_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('email_logs', 'trigger_type')) {
                    $table->string('trigger_type')->nullable()->after('status');
                    \Log::info('✅ Trigger_type kolom toegevoegd aan email_logs tabel');
                }
                if (!Schema::hasColumn('email_logs', 'metadata')) {
                    $table->json('metadata')->nullable()->after('clicked_at');
                    \Log::info('✅ Metadata kolom toegevoegd aan email_logs tabel');
                }
                if (!Schema::hasColumn('email_logs', 'opened_at')) {
                    $table->timestamp('opened_at')->nullable()->after('sent_at');
                    \Log::info('✅ Opened_at kolom toegevoegd aan email_logs tabel');
                }
                if (!Schema::hasColumn('email_logs', 'clicked_at')) {
                    $table->timestamp('clicked_at')->nullable()->after('opened_at');
                    \Log::info('✅ Clicked_at kolom toegevoegd aan email_logs tabel');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};