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
        Schema::table('email_settings', function (Blueprint $table) {
            // Voeg organisatie_id kolom toe
            $table->unsignedBigInteger('organisatie_id')->nullable()->after('id');
            
            // Voeg foreign key constraint toe
            $table->foreign('organisatie_id')
                  ->references('id')
                  ->on('organisaties')
                  ->onDelete('cascade');
            
            // Voeg index toe voor snellere queries
            $table->index('organisatie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            // Verwijder foreign key en index eerst
            $table->dropForeign(['organisatie_id']);
            $table->dropIndex(['organisatie_id']);
            
            // Verwijder de kolom
            $table->dropColumn('organisatie_id');
        });
    }
};
