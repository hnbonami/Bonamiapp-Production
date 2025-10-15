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
        Schema::table('users', function (Blueprint $table) {
            // Voeg voornaam kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('users', 'voornaam')) {
                $table->string('voornaam')->nullable()->after('name');
                \Log::info('✅ Voornaam kolom toegevoegd aan users tabel');
            }
            
            // Voeg achternaam kolom toe als deze nog niet bestaat  
            if (!Schema::hasColumn('users', 'achternaam')) {
                $table->string('achternaam')->nullable()->after('voornaam');
                \Log::info('✅ Achternaam kolom toegevoegd aan users tabel');
            }
            
            // Voeg telefoon kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('users', 'telefoon')) {
                $table->string('telefoon')->nullable()->after('email');
                \Log::info('✅ Telefoon kolom toegevoegd aan users tabel');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'voornaam')) {
                $table->dropColumn('voornaam');
            }
            if (Schema::hasColumn('users', 'achternaam')) {
                $table->dropColumn('achternaam');
            }
            if (Schema::hasColumn('users', 'telefoon')) {
                $table->dropColumn('telefoon');
            }
        });
    }
};