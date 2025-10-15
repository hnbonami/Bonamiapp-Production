<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de migratie uit om email systeem migratie te ondersteunen
     */
    public function up(): void
    {
        // Voeg template_key kolom toe aan email_templates als deze niet bestaat
        if (Schema::hasTable('email_templates') && !Schema::hasColumn('email_templates', 'template_key')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->string('template_key')->nullable()->unique()->after('id');
                $table->index('template_key');
            });
        }
        
        // Voeg trigger_key kolom toe aan email_triggers als deze niet bestaat
        if (Schema::hasTable('email_triggers') && !Schema::hasColumn('email_triggers', 'trigger_key')) {
            Schema::table('email_triggers', function (Blueprint $table) {
                $table->string('trigger_key')->nullable()->unique()->after('id');
                $table->index('trigger_key');
            });
        }
        
        // Voeg ontbrekende kolommen toe aan email_templates
        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('email_templates', 'content')) {
                    $table->longText('content')->nullable()->after('body_html');
                }
                if (!Schema::hasColumn('email_templates', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
                }
            });
        }
        
        // Voeg ontbrekende kolommen toe aan email_triggers
        if (Schema::hasTable('email_triggers')) {
            Schema::table('email_triggers', function (Blueprint $table) {
                if (!Schema::hasColumn('email_triggers', 'trigger_type')) {
                    $table->string('trigger_type')->default('scheduled')->after('description');
                }
                if (!Schema::hasColumn('email_triggers', 'trigger_data')) {
                    $table->json('trigger_data')->nullable()->after('trigger_type');
                }
                if (!Schema::hasColumn('email_triggers', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
                }
            });
        }
    }

    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        // Verwijder toegevoegde kolommen bij rollback
        if (Schema::hasTable('email_templates')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->dropColumn(['template_key', 'content', 'created_by']);
            });
        }
        
        if (Schema::hasTable('email_triggers')) {
            Schema::table('email_triggers', function (Blueprint $table) {
                $table->dropColumn(['trigger_key', 'trigger_type', 'trigger_data', 'created_by']);
            });
        }
    }
};