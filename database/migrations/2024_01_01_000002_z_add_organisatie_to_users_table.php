<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voeg organisatie_id toe aan users tabel (na role update)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check of kolom al bestaat voordat we toevoegen
            if (!Schema::hasColumn('users', 'organisatie_id')) {
                $table->foreignId('organisatie_id')->nullable()->after('id')->constrained('organisaties')->nullOnDelete();
            }
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
