<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medewerkers', function (Blueprint $table) {
            $table->id();
            $table->string('voornaam');
            $table->string('achternaam');
            $table->string('email')->unique();
            $table->string('telefoon')->nullable();
            $table->string('functie')->nullable();
            $table->date('startdatum')->nullable();
            $table->boolean('actief')->default(true);
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medewerkers');
    }
};