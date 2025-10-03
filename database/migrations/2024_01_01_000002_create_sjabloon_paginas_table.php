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
        Schema::create('sjabloon_paginas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sjabloon_id')->constrained('sjablonen')->onDelete('cascade');
            $table->integer('pagina_nummer');
            $table->string('achtergrond_url')->nullable();
            $table->longText('inhoud')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sjabloon_paginas');
    }
};