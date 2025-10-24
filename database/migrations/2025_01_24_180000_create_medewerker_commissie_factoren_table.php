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
        Schema::create('medewerker_commissie_factoren', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Commissie factoren (kunnen gecombineerd worden)
            $table->decimal('diploma_factor', 5, 2)->default(0)->comment('Bonus percentage voor diploma (bijv. +5%)');
            $table->decimal('ervaring_factor', 5, 2)->default(0)->comment('Bonus percentage voor ervaring (bijv. +10%)');
            $table->decimal('ancienniteit_factor', 5, 2)->default(0)->comment('Bonus percentage voor anciënniteit (bijv. +8%)');
            
            // Optioneel: dienst-specifieke override
            $table->foreignId('dienst_id')->nullable()->constrained('diensten')->onDelete('cascade');
            $table->decimal('custom_commissie_percentage', 5, 2)->nullable()->comment('Override voor specifieke dienst');
            
            // Metadata
            $table->text('opmerking')->nullable();
            $table->boolean('is_actief')->default(true);
            $table->timestamps();
            
            // Unieke combinatie: één record per user (algemeen) of per user+dienst (specifiek)
            $table->unique(['user_id', 'dienst_id'], 'user_dienst_unique');
            
            // Index voor snelle lookups
            $table->index(['user_id', 'is_actief']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medewerker_commissie_factoren');
    }
};
