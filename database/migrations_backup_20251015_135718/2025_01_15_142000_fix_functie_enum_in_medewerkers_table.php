<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Wijzig functie kolom naar varchar zodat alle waarden kunnen worden opgeslagen
        Schema::table('medewerkers', function (Blueprint $table) {
            $table->string('functie')->change();
        });
        
        // Ook status naar varchar wijzigen voor flexibiliteit
        Schema::table('medewerkers', function (Blueprint $table) {
            $table->string('status')->change();
        });
    }

    public function down()
    {
        Schema::table('medewerkers', function (Blueprint $table) {
            $table->enum('functie', ['fysiotherapeut', 'trainer', 'bikefit_specialist', 'admin', 'manager', 'stagiair'])->change();
            $table->enum('status', ['actief', 'inactief', 'verlof', 'proeftijd'])->change();
        });
    }
};