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
            $table->timestamp('expires_at')->nullable()->comment('Vervaldatum voor trial periodes');
            $table->boolean('is_actief')->default(true)->comment('Is deze feature actief voor deze organisatie?');
            $table->text('notities')->nullable()->comment('Admin notities over deze feature toewijzing');
            $table->timestamps();
            
            // Zorg dat een organisatie een feature maar 1x kan hebben
            $table->unique(['organisatie_id', 'feature_id']);
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
