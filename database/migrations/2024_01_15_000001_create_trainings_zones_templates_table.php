<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings_zones_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisatie_id')->constrained('organisaties')->onDelete('cascade');
            $table->string('naam');
            $table->enum('sport_type', ['fietsen', 'lopen', 'beide'])->default('beide');
            $table->enum('berekening_basis', ['lt1', 'lt2', 'max', 'ftp', 'custom'])->default('lt2');
            $table->text('beschrijving')->nullable();
            $table->boolean('is_actief')->default(true);
            $table->boolean('is_systeem')->default(false);
            $table->timestamps();
            
            $table->index(['organisatie_id', 'is_actief']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings_zones_templates');
    }
};
