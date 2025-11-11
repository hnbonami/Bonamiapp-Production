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
        Schema::create('organisatie_rapport_instellingen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisatie_id')->constrained('organisaties')->onDelete('cascade');
            
            // Header & Footer
            $table->text('header_tekst')->nullable();
            $table->text('footer_tekst')->nullable();
            
            // Logo's en afbeeldingen
            $table->string('logo_path')->nullable();
            $table->string('voorblad_foto_path')->nullable();
            
            // Teksten
            $table->text('inleidende_tekst')->nullable();
            $table->text('laatste_blad_tekst')->nullable();
            $table->text('disclaimer_tekst')->nullable();
            
            // Branding kleuren
            $table->string('primaire_kleur')->default('#c8e1eb'); // Bonami blauw
            $table->string('secundaire_kleur')->default('#111111'); // Bonami zwart
            
            // Typografie
            $table->enum('lettertype', ['Arial', 'Tahoma', 'Calibri', 'Helvetica'])->default('Arial');
            
            // Paginanummering
            $table->boolean('paginanummering_tonen')->default(true);
            $table->string('paginanummering_positie')->default('rechtsonder'); // rechtsonder, linksboven, etc.
            
            // Contactgegevens
            $table->string('contact_adres')->nullable();
            $table->string('contact_telefoon')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_website')->nullable();
            $table->boolean('contactgegevens_in_footer')->default(true);
            
            // QR Code
            $table->boolean('qr_code_tonen')->default(false);
            $table->string('qr_code_url')->nullable(); // URL waar QR code naartoe linkt
            $table->enum('qr_code_positie', ['rechtsonder', 'linksboven', 'footer'])->default('rechtsonder');
            
            $table->timestamps();
            
            // Elke organisatie mag maar 1 rapport instelling hebben
            $table->unique('organisatie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisatie_rapport_instellingen');
    }
};
