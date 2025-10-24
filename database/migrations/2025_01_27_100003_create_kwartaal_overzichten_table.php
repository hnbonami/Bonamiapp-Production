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
        Schema::create('kwartaal_overzichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Coach
            $table->integer('jaar'); // 2025, 2026
            $table->string('kwartaal', 2); // Q1, Q2, Q3, Q4
            $table->decimal('totaal_bruto', 10, 2)->default(0);
            $table->decimal('totaal_btw', 10, 2)->default(0);
            $table->decimal('totaal_netto', 10, 2)->default(0);
            $table->decimal('totaal_commissie', 10, 2)->default(0);
            $table->integer('aantal_prestaties')->default(0);
            $table->boolean('is_afgesloten')->default(false); // Locked voor bewerking
            $table->date('afgesloten_op')->nullable();
            $table->timestamps();

            // Unieke combinatie per coach, jaar en kwartaal
            $table->unique(['user_id', 'jaar', 'kwartaal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwartaal_overzichten');
    }
};
