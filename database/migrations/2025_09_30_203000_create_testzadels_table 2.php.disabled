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
        Schema::create('testzadels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klant_id')->constrained('klanten')->onDelete('cascade');
            $table->foreignId('bikefit_id')->nullable()->constrained('bikefits')->onDelete('set null');
            
            // Testzadel details
            $table->string('zadel_merk');
            $table->string('zadel_model');
            $table->string('zadel_type')->nullable(); // Road, MTB, Gravel, etc.
            $table->integer('zadel_breedte')->nullable(); // in mm
            $table->text('zadel_beschrijving')->nullable();
            
            // Uitleenstatus
            $table->enum('status', ['uitgeleend', 'teruggebracht', 'gearchiveerd'])->default('uitgeleend');
            $table->date('uitleen_datum');
            $table->date('verwachte_retour_datum')->nullable();
            $table->date('werkelijke_retour_datum')->nullable();
            
            // Contact & herinneringen
            $table->boolean('herinnering_verstuurd')->default(false);
            $table->timestamp('herinnering_verstuurd_op')->nullable();
            // Virtual column verwijderd - wordt berekend in model
            
            // Extra info
            $table->text('opmerkingen')->nullable();
            $table->string('foto_path')->nullable();
            $table->json('feedback')->nullable(); // Klant feedback na terugbrengen
            
            $table->timestamps();
            
            // Indexen voor snelle queries
            $table->index(['status', 'uitleen_datum']);
            $table->index(['klant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testzadels');
    }
};