<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('klanten', function (Blueprint $table) {
            $table->id();
            $table->string('voornaam');
            $table->string('naam');
            $table->string('email')->unique();
            $table->string('telefoon')->nullable();
            $table->date('geboortedatum')->nullable();
            $table->enum('geslacht', ['Man', 'Vrouw', 'Ander'])->nullable();
            $table->string('sport')->nullable();
            $table->enum('niveau', ['recreatief', 'competitie', 'elite'])->nullable();
            $table->text('medische_geschiedenis')->nullable();
            $table->text('doelen')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('klanten');
    }
};