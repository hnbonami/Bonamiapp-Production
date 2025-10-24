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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Unieke key voor feature (bijv. klantenbeheer, bikefits)');
            $table->string('naam')->comment('Weergavenaam van de feature');
            $table->text('beschrijving')->nullable()->comment('Uitleg wat deze feature doet');
            $table->string('categorie')->default('beheer')->comment('Categorie: beheer, metingen, geavanceerd');
            $table->boolean('is_premium')->default(false)->comment('Is dit een premium feature');
            $table->decimal('prijs_per_maand', 8, 2)->nullable()->comment('Maandelijkse prijs voor premium features');
            $table->integer('sorteer_volgorde')->default(0)->comment('Volgorde voor weergave');
            $table->boolean('is_actief')->default(true)->comment('Is deze feature beschikbaar');
            $table->timestamps();
            
            $table->index('key');
            $table->index('categorie');
            $table->index('is_actief');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};