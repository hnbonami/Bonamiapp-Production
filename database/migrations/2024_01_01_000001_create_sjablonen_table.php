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
        Schema::create('sjablonen', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->string('categorie'); // bikefit, inspanningstest
            $table->string('testtype'); // standaard_bikefit, professionele_bikefit, etc.
            $table->text('beschrijving')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sjablonen');
    }
};