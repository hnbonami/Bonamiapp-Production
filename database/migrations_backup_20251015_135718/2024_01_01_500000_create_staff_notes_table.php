<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_notes', function (Blueprint $table) {
            $table->id();
            $table->string('titel');
            $table->text('inhoud')->nullable();
            $table->enum('prioriteit', ['laag', 'normaal', 'hoog', 'urgent'])->default('normaal');
            $table->enum('status', ['open', 'in_behandeling', 'afgerond'])->default('open');
            $table->boolean('is_new')->default(true);
            $table->foreignId('toegewezen_aan')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('aangemaakt_door')->constrained('users')->onDelete('cascade');
            $table->timestamp('gelezen_op')->nullable();
            $table->date('vervaldatum')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_notes');
    }
};