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
        Schema::create('prestaties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Coach
            $table->foreignId('dienst_id')->constrained('diensten')->onDelete('cascade');
            $table->string('klant_naam'); // Naam van de klant
            $table->text('omschrijving_dienst')->nullable(); // Extra details
            $table->date('datum_prestatie'); // Datum van de prestatie
            $table->decimal('bruto_prijs', 10, 2); // Bruto bedrag
            $table->decimal('btw_percentage', 5, 2); // BTW %
            $table->decimal('btw_bedrag', 10, 2); // BTW bedrag
            $table->decimal('netto_prijs', 10, 2); // Netto bedrag
            $table->decimal('commissie_percentage', 5, 2); // Commissie %
            $table->decimal('commissie_bedrag', 10, 2); // Commissie bedrag
            $table->boolean('is_gefactureerd')->default(false); // Status facturatie
            $table->string('factuur_nummer')->nullable(); // Factuurnummer
            $table->string('kwartaal', 2); // Q1, Q2, Q3, Q4
            $table->integer('jaar'); // 2025, 2026, etc.
            $table->text('opmerkingen')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes voor snelle queries
            $table->index(['user_id', 'jaar', 'kwartaal']);
            $table->index('datum_prestatie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestaties');
    }
};
