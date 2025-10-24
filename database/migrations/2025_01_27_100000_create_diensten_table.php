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
        Schema::create('diensten', function (Blueprint $table) {
            $table->id();
            $table->string('naam'); // bijv. "Bikefit", "Inspanningstest"
            $table->text('beschrijving')->nullable();
            $table->decimal('standaard_prijs', 10, 2); // Bruto prijs
            $table->decimal('btw_percentage', 5, 2)->default(21.00); // BTW %
            $table->boolean('is_actief')->default(true);
            $table->integer('sorteer_volgorde')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diensten');
    }
};
