<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maak de organisaties tabel aan voor multi-tenancy support
     */
    public function up(): void
    {
        Schema::create('organisaties', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->string('email')->unique();
            $table->string('telefoon')->nullable();
            $table->string('adres')->nullable();
            $table->string('postcode')->nullable();
            $table->string('plaats')->nullable();
            $table->string('btw_nummer')->nullable();
            $table->string('logo_path')->nullable();
            $table->enum('status', ['actief', 'inactief', 'trial'])->default('trial');
            $table->date('trial_eindigt_op')->nullable();
            $table->decimal('maandelijkse_prijs', 8, 2)->default(0);
            $table->text('notities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Draai de migration terug
     */
    public function down(): void
    {
        Schema::dropIfExists('organisaties');
    }
};
