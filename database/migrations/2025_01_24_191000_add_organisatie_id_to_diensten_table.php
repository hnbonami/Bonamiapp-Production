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
        // Check of kolom al bestaat
        if (!Schema::hasColumn('diensten', 'organisatie_id')) {
            Schema::table('diensten', function (Blueprint $table) {
                $table->foreignId('organisatie_id')
                    ->after('id')
                    ->nullable()
                    ->constrained('organisaties')
                    ->onDelete('cascade')
                    ->comment('Koppeling naar organisatie voor multi-tenant scheiding');
            });
        }

        // Update bestaande diensten: geef ze de organisatie_id van de eerste user die ze heeft
        DB::statement('
            UPDATE diensten d
            LEFT JOIN coach_diensten cd ON d.id = cd.dienst_id
            LEFT JOIN users u ON cd.user_id = u.id
            SET d.organisatie_id = u.organisatie_id
            WHERE d.organisatie_id IS NULL AND u.organisatie_id IS NOT NULL
        ');

        // Voor diensten zonder user koppeling: geef organisatie_id 1 (standaard)
        DB::statement('
            UPDATE diensten
            SET organisatie_id = 1
            WHERE organisatie_id IS NULL
        ');

        // Maak organisatie_id NOT NULL als dat nog niet het geval is
        if (Schema::hasColumn('diensten', 'organisatie_id')) {
            $column = DB::select("SHOW COLUMNS FROM diensten WHERE Field = 'organisatie_id'");
            if ($column && $column[0]->Null === 'YES') {
                Schema::table('diensten', function (Blueprint $table) {
                    $table->foreignId('organisatie_id')->nullable(false)->change();
                });
            }
        }

        // Voeg index toe als die nog niet bestaat
        $indexes = DB::select("SHOW INDEX FROM diensten WHERE Key_name = 'diensten_organisatie_id_is_actief_index'");
        if (empty($indexes)) {
            Schema::table('diensten', function (Blueprint $table) {
                $table->index(['organisatie_id', 'is_actief']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diensten', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropIndex(['organisatie_id', 'is_actief']);
            $table->dropColumn('organisatie_id');
        });
    }
};
