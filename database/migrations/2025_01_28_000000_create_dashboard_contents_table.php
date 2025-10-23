<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Maak dashboard_contents tabel aan
     */
    public function up(): void
    {
        Schema::create('dashboard_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisatie_id')->nullable()->constrained('organisaties')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Content velden
            $table->string('titel');
            $table->text('inhoud');
            $table->enum('type', ['info', 'waarschuwing', 'succes', 'belangrijk'])->default('info');
            $table->string('kleur', 50)->nullable();
            $table->string('icoon', 50)->nullable();
            
            // Link velden (optioneel)
            $table->string('link_url', 500)->nullable();
            $table->string('link_tekst', 100)->nullable();
            
            // Volgorde en status
            $table->integer('volgorde')->default(0);
            $table->boolean('is_actief')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            
            $table->timestamps();
            
            // Indexes voor performance
            $table->index(['organisatie_id', 'is_actief', 'is_archived']);
            $table->index('volgorde');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_contents');
    }
};