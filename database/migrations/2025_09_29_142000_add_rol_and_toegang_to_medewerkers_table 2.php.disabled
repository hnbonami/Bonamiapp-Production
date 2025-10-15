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
        Schema::table('medewerkers', function (Blueprint $table) {
            $table->string('rol')->nullable()->after('functie');
            $table->string('afdeling')->nullable()->after('rol');
            $table->decimal('salaris', 10, 2)->nullable()->after('afdeling');
            $table->json('toegangsrechten')->nullable()->after('salaris');
            $table->string('toegangsniveau')->default('medewerker')->after('toegangsrechten');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medewerkers', function (Blueprint $table) {
            $table->dropColumn(['rol', 'afdeling', 'salaris', 'toegangsrechten', 'toegangsniveau']);
        });
    }
};