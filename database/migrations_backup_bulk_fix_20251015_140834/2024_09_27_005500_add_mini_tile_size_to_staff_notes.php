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
        Schema::table('staff_notes', function (Blueprint $table) {
            // Update the tile_size enum to include 'mini'
            $table->enum('tile_size', ['mini', 'small', 'medium', 'large', 'banner'])->default('medium')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('tile_size', ['small', 'medium', 'large', 'banner'])->default('medium')->change();
        });
    }
};