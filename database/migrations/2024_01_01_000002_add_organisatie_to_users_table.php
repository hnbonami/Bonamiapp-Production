<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg organisatie_id toe aan users tabel
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check of kolom al bestaat voordat we toevoegen
            if (!Schema::hasColumn('users', 'organisatie_id')) {
                $table->foreignId('organisatie_id')->nullable()->after('id')->constrained('organisaties')->nullOnDelete();
            }
            
            // Role kolom bestaat al, dus we skippen die
            // Eventueel kun je hier een check doen of de role waarden kloppen
        });
    }

    /**
     * Draai de migration terug
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'organisatie_id')) {
                $table->dropForeign(['organisatie_id']);
                $table->dropColumn('organisatie_id');
            }
        });
    }
};
