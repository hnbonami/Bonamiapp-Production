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
        // Skip als de tabel al bestaat
        if (Schema::hasTable('bikefit_custom_results')) {
            return;
        }
        
        Schema::create('bikefit_custom_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bikefit_id')->constrained('bikefits')->onDelete('cascade');
            $table->enum('context', ['prognose', 'voor', 'na']);
            $table->string('field_name'); // zadelhoogte, reach, drop, etc.
            $table->decimal('custom_value', 8, 2)->nullable();
            $table->decimal('original_value', 8, 2)->nullable();
            $table->timestamps();
            
            // Unieke combinatie per bikefit, context en field
            $table->unique(['bikefit_id', 'context', 'field_name']);
            
            // Index voor snelle queries
            $table->index(['bikefit_id', 'context']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bikefit_custom_results');
    }
};