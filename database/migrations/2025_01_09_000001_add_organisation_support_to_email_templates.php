<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg organisatie-ondersteuning toe aan email templates
     * 
     * Dit maakt het mogelijk om:
     * - Standaard Performance Pulse templates te hebben (organisatie_id = NULL)
     * - Organisatie-specifieke custom templates te maken
     * - Fallback logica te implementeren
     */
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Organisatie relatie - NULL = standaard Performance Pulse template
            $table->unsignedBigInteger('organisatie_id')->nullable()->after('id');
            
            // Markeer standaard system templates
            $table->boolean('is_default')->default(false)->after('is_active');
            
            // Optionele parent template relatie (voor overerving)
            $table->unsignedBigInteger('parent_template_id')->nullable()->after('is_default');
            
            // Indexes voor snelle queries
            $table->index('organisatie_id', 'idx_email_templates_organisatie_id');
            $table->index(['type', 'organisatie_id'], 'idx_email_templates_type_org');
            $table->index('is_default', 'idx_email_templates_is_default');
        });
        
        \Log::info('✅ Email templates migration: organisatie support toegevoegd');
    }

    /**
     * Rollback de wijzigingen
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Drop indexes eerst
            $table->dropIndex('idx_email_templates_organisatie_id');
            $table->dropIndex('idx_email_templates_type_org');
            $table->dropIndex('idx_email_templates_is_default');
            
            // Drop kolommen
            $table->dropColumn(['organisatie_id', 'is_default', 'parent_template_id']);
        });
        
        \Log::info('⚠️ Email templates migration: organisatie support verwijderd');
    }
};
