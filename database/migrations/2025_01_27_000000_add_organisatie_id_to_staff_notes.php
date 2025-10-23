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
            // Voeg organisatie_id toe voor multi-tenant filtering
            $table->unsignedBigInteger('organisatie_id')->nullable()->after('user_id');
            
            // Foreign key constraint
            $table->foreign('organisatie_id')
                  ->references('id')
                  ->on('organisaties')
                  ->onDelete('cascade');
            
            // Index voor snellere queries
            $table->index('organisatie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropIndex(['organisatie_id']);
            $table->dropColumn('organisatie_id');
        });
    }
};