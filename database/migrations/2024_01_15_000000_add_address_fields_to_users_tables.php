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
        // Klanten tabel - alleen toevoegen als kolom niet bestaat
        Schema::table('klanten', function (Blueprint $table) {
            if (!Schema::hasColumn('klanten', 'telefoonnummer')) {
                $table->string('telefoonnummer')->nullable()->after('email');
            }
            if (!Schema::hasColumn('klanten', 'straatnaam')) {
                $table->string('straatnaam')->nullable();
            }
            if (!Schema::hasColumn('klanten', 'huisnummer')) {
                $table->string('huisnummer')->nullable();
            }
            if (!Schema::hasColumn('klanten', 'postcode')) {
                $table->string('postcode')->nullable();
            }
            if (!Schema::hasColumn('klanten', 'stad')) {
                $table->string('stad')->nullable();
            }
        });

        // Users tabel - alleen toevoegen als kolom niet bestaat  
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telefoonnummer')) {
                $table->string('telefoonnummer')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'straatnaam')) {
                $table->string('straatnaam')->nullable();
            }
            if (!Schema::hasColumn('users', 'huisnummer')) {
                $table->string('huisnummer')->nullable();
            }
            if (!Schema::hasColumn('users', 'postcode')) {
                $table->string('postcode')->nullable();
            }
            if (!Schema::hasColumn('users', 'stad')) {
                $table->string('stad')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klanten', function (Blueprint $table) {
            $table->dropColumn(['telefoonnummer', 'straatnaam', 'huisnummer', 'postcode', 'stad']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefoonnummer', 'straatnaam', 'huisnummer', 'postcode', 'stad']);
        });
    }
};