<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de migratie uit voor email triggers tabel
     */
    public function up(): void
    {
        // Maak email_triggers tabel als deze niet bestaat
        if (!Schema::hasTable('email_triggers')) {
            Schema::create('email_triggers', function (Blueprint $table) {
                $table->id();
                $table->string('trigger_key')->unique()->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('trigger_type')->default('scheduled');
                $table->json('trigger_data')->nullable();
                $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->json('conditions')->nullable();
                $table->json('settings')->nullable();
                $table->integer('emails_sent')->default(0);
                $table->timestamp('last_run_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                
                $table->index(['trigger_type', 'is_active']);
                $table->index('trigger_key');
            });
        } else {
            // Voeg ontbrekende kolommen toe aan bestaande tabel
            Schema::table('email_triggers', function (Blueprint $table) {
                // Check en voeg elke kolom afzonderlijk toe
                if (!Schema::hasColumn('email_triggers', 'trigger_key')) {
                    $table->string('trigger_key')->unique()->nullable()->after('id');
                }
                if (!Schema::hasColumn('email_triggers', 'trigger_type')) {
                    $table->string('trigger_type')->default('scheduled')->after('description');
                }
                if (!Schema::hasColumn('email_triggers', 'trigger_data')) {
                    $table->json('trigger_data')->nullable()->after('trigger_type');
                }
                if (!Schema::hasColumn('email_triggers', 'conditions')) {
                    $table->json('conditions')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('email_triggers', 'settings')) {
                    $table->json('settings')->nullable()->after('conditions');
                }
                if (!Schema::hasColumn('email_triggers', 'emails_sent')) {
                    $table->integer('emails_sent')->default(0)->after('settings');
                }
                if (!Schema::hasColumn('email_triggers', 'last_run_at')) {
                    $table->timestamp('last_run_at')->nullable()->after('emails_sent');
                }
                if (!Schema::hasColumn('email_triggers', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('last_run_at');
                }
            });
            
            // Voeg indexes toe als ze niet bestaan
            try {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->index(['trigger_type', 'is_active']);
                    $table->index('trigger_key');
                });
            } catch (\Exception $e) {
                // Indexes bestaan mogelijk al, negeer errors
            }
        }
    }    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        Schema::dropIfExists('email_triggers');
    }
};