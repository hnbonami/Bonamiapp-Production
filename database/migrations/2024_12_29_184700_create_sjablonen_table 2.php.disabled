<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sjablonen', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->enum('categorie', ['bikefit', 'inspanningstest']);
            $table->string('testtype')->nullable();
            $table->text('beschrijving')->nullable();
            $table->longText('inhoud')->nullable(); // Simpel: 1 sjabloon = 1 tekst
            $table->boolean('is_actief')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sjablonen');
    }
};