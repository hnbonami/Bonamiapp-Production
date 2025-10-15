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
        Schema::create('personal_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('voornaam')->nullable();
            $table->string('achternaam')->nullable();
            $table->string('email')->nullable();
            $table->string('telefoon')->nullable();
            $table->date('geboortedatum')->nullable();
            $table->decimal('gewicht', 5, 2)->nullable();
            $table->decimal('lengte', 5, 2)->nullable();
            $table->text('medische_info')->nullable();
            $table->text('doelstellingen')->nullable();
            $table->string('sport_ervaring')->nullable();
            $table->text('opmerkingen')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_data');
    }
};