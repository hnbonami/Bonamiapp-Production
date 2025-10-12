<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de migratie uit voor email_logs tabel
     */
    public function up(): void
    {
        // Controleer of tabel al bestaat voordat we deze aanmaken
        if (!Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->string('recipient_email');
                $table->string('subject');
                $table->longText('body')->nullable();
                $table->string('status')->default('sent'); // sent, failed, pending
                $table->text('error_message')->nullable();
                $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->string('trigger_name')->nullable();
                $table->json('variables')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                
                $table->index(['recipient_email', 'status']);
                $table->index(['status', 'sent_at']);
                $table->index('trigger_name');
            });
        } else {
            // Voeg ontbrekende kolommen toe aan bestaande tabel
            Schema::table('email_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('email_logs', 'template_id')) {
                    $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete()->after('error_message');
                }
                if (!Schema::hasColumn('email_logs', 'trigger_name')) {
                    $table->string('trigger_name')->nullable()->after('template_id');
                }
                if (!Schema::hasColumn('email_logs', 'variables')) {
                    $table->json('variables')->nullable()->after('trigger_name');
                }
                if (!Schema::hasColumn('email_logs', 'sent_at')) {
                    $table->timestamp('sent_at')->nullable()->after('variables');
                }
            });
        }
    }

    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};