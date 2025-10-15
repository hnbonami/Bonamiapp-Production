<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie controleert of de email_settings tabel al bestaat.
     */
    public function up(): void
    {
        // Controleer of de tabel al bestaat
        if (!Schema::hasTable('email_settings')) {
            Schema::create('email_settings', function (Blueprint $table) {
                $table->id();
                $table->string('company_name')->default('Bonami Cycling');
                $table->string('logo_path')->nullable();
                $table->string('primary_color')->default('#667eea');
                $table->string('secondary_color')->default('#764ba2');
                $table->string('footer_text')->nullable();
                $table->text('signature')->nullable();
                $table->timestamps();
            });
            \Log::info('✅ Email_settings tabel aangemaakt');
        } else {
            \Log::info('⚠️ Email_settings tabel bestaat al - migratie overgeslagen');
            
            // Controleer en voeg ontbrekende kolommen toe indien nodig
            Schema::table('email_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('email_settings', 'signature')) {
                    $table->text('signature')->nullable()->after('footer_text');
                    \Log::info('✅ Signature kolom toegevoegd aan email_settings tabel');
                }
                if (!Schema::hasColumn('email_settings', 'logo_path')) {
                    $table->string('logo_path')->nullable()->after('company_name');
                    \Log::info('✅ Logo_path kolom toegevoegd aan email_settings tabel');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};