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
        Schema::create('klant_documenten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klant_id')->constrained('klanten')->onDelete('cascade');
            $table->string('titel');
            $table->text('beschrijving')->nullable();
            $table->string('bestandsnaam'); // Originele bestandsnaam
            $table->string('opgeslagen_naam'); // Unieke naam op server
            $table->string('bestandstype', 50); // pdf, jpeg, png, mp4, etc.
            $table->unsignedBigInteger('bestandsgrootte'); // In bytes
            $table->string('categorie', 100); // verslag, oefenschema, video, foto, overig
            $table->dateTime('upload_datum');
            $table->boolean('gecomprimeerd')->default(false);
            $table->unsignedBigInteger('originele_grootte')->nullable(); // Voor compressie tracking
            $table->timestamps();
            
            // Indexen voor snellere queries
            $table->index('klant_id');
            $table->index('upload_datum');
            $table->index('categorie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klant_documenten');
    }
};
