<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg unique constraint toe aan email kolom in users tabel
     * Dit voorkomt dubbele emails (bijv. zelfde email als klant EN medewerker)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Maak email kolom unique - voorkomt dubbele emails
            $table->unique('email');
        });
    }

    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }
};