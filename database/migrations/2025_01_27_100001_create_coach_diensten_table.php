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
        Schema::create('coach_diensten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Coach
            $table->foreignId('dienst_id')->constrained('diensten')->onDelete('cascade');
            $table->decimal('custom_prijs', 10, 2)->nullable(); // Optioneel, anders standaard prijs
            $table->decimal('commissie_percentage', 5, 2)->default(0.00); // Commissie %
            $table->boolean('is_actief')->default(true);
            $table->timestamps();

            // Unieke combinatie per coach en dienst
            $table->unique(['user_id', 'dienst_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_diensten');
    }
};
