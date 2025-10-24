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
        Schema::create('organisatie_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisatie_id')->constrained('organisaties')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->boolean('is_actief')->default(true)->comment('Is deze feature actief voor deze organisatie');
            $table->timestamp('expires_at')->nullable()->comment('Vervaldatum voor trial/tijdelijke toegang');
            $table->text('notities')->nullable()->comment('Interne notities over deze feature toewijzing');
            $table->timestamps();
            
            // Unieke combinatie: één feature per organisatie
            $table->unique(['organisatie_id', 'feature_id']);
            
            $table->index('organisatie_id');
            $table->index('feature_id');
            $table->index('is_actief');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisatie_features');
    }
};