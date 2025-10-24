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
        Schema::table('prestaties', function (Blueprint $table) {
            $table->foreignId('organisatie_id')
                ->after('user_id')
                ->nullable()
                ->constrained('organisaties')
                ->onDelete('cascade')
                ->comment('Koppeling naar organisatie voor multi-tenant scheiding');
        });

        // Update bestaande prestaties met organisatie_id van de gebruiker
        DB::statement('
            UPDATE prestaties p
            INNER JOIN users u ON p.user_id = u.id
            SET p.organisatie_id = u.organisatie_id
            WHERE p.organisatie_id IS NULL
        ');

        // Maak organisatie_id NOT NULL na data migratie
        Schema::table('prestaties', function (Blueprint $table) {
            $table->foreignId('organisatie_id')->nullable(false)->change();
        });

        // Voeg index toe voor snellere queries
        Schema::table('prestaties', function (Blueprint $table) {
            $table->index(['organisatie_id', 'user_id', 'datum_prestatie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestaties', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropIndex(['organisatie_id', 'user_id', 'datum_prestatie']);
            $table->dropColumn('organisatie_id');
        });
    }
};
