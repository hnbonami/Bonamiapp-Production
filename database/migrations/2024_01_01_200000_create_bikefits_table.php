<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bikefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klant_id')->constrained('klanten')->onDelete('cascade');
            $table->date('datum');
            $table->text('opmerkingen')->nullable();
            $table->json('metingen')->nullable();
            $table->json('aanpassingen')->nullable();
            $table->string('type_fitting')->nullable();
            $table->text('aanbevelingen')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bikefits');
    }
};