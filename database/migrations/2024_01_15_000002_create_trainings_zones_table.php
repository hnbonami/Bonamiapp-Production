<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('trainings_zones_templates')->onDelete('cascade');
            $table->string('zone_naam');
            $table->string('kleur', 7);
            $table->integer('min_percentage');
            $table->integer('max_percentage');
            $table->string('referentie_waarde')->nullable();
            $table->integer('volgorde')->default(0);
            $table->text('beschrijving')->nullable();
            $table->timestamps();
            
            $table->index(['template_id', 'volgorde']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings_zones');
    }
};
