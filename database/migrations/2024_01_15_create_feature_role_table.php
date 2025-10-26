<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run de migrations - Feature-Role koppeling
     * Een rol kan meerdere features hebben
     */
    public function up(): void
    {
        Schema::create('feature_role', function (Blueprint $table) {
            $table->id();
            $table->string('role_key'); // 'admin', 'medewerker', 'klant'
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->boolean('is_actief')->default(true);
            $table->timestamps();
            
            // Unieke combinatie van rol en feature
            $table->unique(['role_key', 'feature_id']);
            
            // Index voor snellere queries
            $table->index('role_key');
            $table->index('feature_id');
        });
    }

    /**
     * Reverse de migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_role');
    }
};
