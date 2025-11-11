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
        Schema::table('sjablonen', function (Blueprint $table) {
            // Verander categorie van ENUM naar VARCHAR(50)
            $table->string('categorie', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sjablonen', function (Blueprint $table) {
            // Herstel naar originele ENUM (indien nodig)
            $table->enum('categorie', ['bikefit', 'inspanningstest'])->change();
        });
    }
};
