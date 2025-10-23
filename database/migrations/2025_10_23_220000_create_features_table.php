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
            $table->string('key')->unique()->comment('Unieke identifier voor de feature (bijv. bikefits, inspanningstesten)');
            $table->string('naam')->comment('Nederlandse naam van de feature');
            $table->text('beschrijving')->nullable()->comment('Uitleg wat deze feature doet');
            $table->string('categorie')->default('algemeen')->comment('Categorie: metingen, beheer, rapportage, etc.');
            $table->string('icoon')->nullable()->comment('Heroicon naam voor UI');
            $table->boolean('is_premium')->default(false)->comment('Is dit een betaalde feature?');
            $table->decimal('prijs_per_maand', 8, 2)->nullable()->comment('Maandelijkse prijs voor deze feature');
            $table->boolean('is_actief')->default(true)->comment('Is deze feature beschikbaar?');
            $table->integer('sorteer_volgorde')->default(0)->comment('Volgorde in UI');
            $table->timestamps();
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
