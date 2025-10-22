<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update de role kolom om de nieuwe waarden te ondersteunen
     * Bestaande waarden: admin, klant, medewerker
     * Nieuwe waarden: superadmin, organisatie_admin, medewerker, klant
     */
    public function up(): void
    {
        // Stap 1: Voeg tijdelijk kolom toe
        if (!Schema::hasColumn('users', 'role_new')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role_new', ['superadmin', 'organisatie_admin', 'admin', 'medewerker', 'klant'])
                    ->nullable()
                    ->after('role');
            });
        }

        // Stap 2: Kopieer waarden van oude kolom naar nieuwe kolom
        DB::statement("UPDATE `users` SET `role_new` = `role`");

        // Stap 3: Verwijder oude role kolom
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Stap 4: Hernoem role_new naar role
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_new', 'role');
        });

        // Stap 5: Update 'admin' users naar 'organisatie_admin' (backwards compatible)
        DB::statement("UPDATE `users` SET `role` = 'organisatie_admin' WHERE `role` = 'admin'");
    }

    /**
     * Draai de migration terug
     */
    public function down(): void
    {
        // Herstel oude role enum (admin, medewerker, klant)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_old', ['admin', 'medewerker', 'klant'])->nullable()->after('role');
        });

        DB::statement("UPDATE `users` SET `role_old` = CASE 
            WHEN `role` IN ('superadmin', 'organisatie_admin') THEN 'admin'
            ELSE `role`
        END");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_old', 'role');
        });
    }
};
