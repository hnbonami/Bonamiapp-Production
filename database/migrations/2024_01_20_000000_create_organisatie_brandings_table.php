<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organisatie_brandings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisatie_id')->constrained('organisaties')->onDelete('cascade');
            
            // Logo's
            $table->string('logo_pad')->nullable()->comment('Pad naar hoofdlogo');
            $table->string('logo_klein_pad')->nullable()->comment('Pad naar klein logo voor mobiel');
            $table->string('rapport_logo_pad')->nullable()->comment('Pad naar logo voor PDF rapporten');
            
            // Kleuren - Primair
            $table->string('primaire_kleur', 7)->default('#3b82f6')->comment('Hoofdkleur applicatie (hex)');
            $table->string('primaire_kleur_hover', 7)->default('#2563eb')->comment('Hover state hoofdkleur');
            $table->string('primaire_kleur_licht', 7)->default('#dbeafe')->comment('Lichte variant primaire kleur');
            
            // Kleuren - Secundair
            $table->string('secundaire_kleur', 7)->default('#6b7280')->comment('Secundaire kleur (hex)');
            $table->string('accent_kleur', 7)->default('#10b981')->comment('Accent kleur voor call-to-actions');
            
            // Achtergronden
            $table->string('achtergrond_kleur', 7)->default('#f9fafb')->comment('Achtergrondkleur applicatie');
            $table->string('kaart_achtergrond', 7)->default('#ffffff')->comment('Achtergrondkleur voor kaarten/cards');
            
            // Tekst kleuren
            $table->string('tekst_kleur_primair', 7)->default('#111827')->comment('Hoofdtekst kleur');
            $table->string('tekst_kleur_secundair', 7)->default('#6b7280')->comment('Secundaire tekst kleur');
            
            // Typografie
            $table->string('font_familie')->default('Inter, system-ui, sans-serif')->comment('Font familie voor applicatie');
            $table->string('font_grootte_basis')->default('16px')->comment('Basis font grootte');
            
            // Rapport specifiek
            $table->string('rapport_achtergrond', 7)->default('#ffffff');
            $table->text('rapport_footer_tekst')->nullable()->comment('Aangepaste footer tekst voor rapporten');
            $table->boolean('toon_logo_in_rapporten')->default(true);
            
            // Navigatie
            $table->string('navbar_achtergrond', 7)->default('#1f2937')->comment('Achtergrondkleur navigatiebalk');
            $table->string('navbar_tekst_kleur', 7)->default('#ffffff')->comment('Tekstkleur navigatiebalk');
            
            // Overige
            $table->boolean('is_actief')->default(false)->comment('Of branding actief is');
            $table->json('custom_css')->nullable()->comment('Optionele custom CSS regels');
            
            $table->timestamps();
            
            // Zorg dat elke organisatie maar 1 branding configuratie heeft
            $table->unique('organisatie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisatie_brandings');
    }
};
