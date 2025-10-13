<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie controleert of de sjablonen tabel al bestaat.
     */
    public function up(): void
    {
        // Controleer of de tabel al bestaat
        if (!Schema::hasTable('sjablonen')) {
            Schema::create('sjablonen', function (Blueprint $table) {
                $table->id();
                $table->string('naam');
                $table->string('categorie');
                $table->string('testtype');
                $table->text('beschrijving')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                
                // Foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            \Log::info('✅ Sjablonen tabel aangemaakt');
        } else {
            \Log::info('⚠️ Sjablonen tabel bestaat al - migratie overgeslagen');
            
            // Controleer en voeg ontbrekende kolommen toe indien nodig
            Schema::table('sjablonen', function (Blueprint $table) {
                if (!Schema::hasColumn('sjablonen', 'beschrijving')) {
                    $table->text('beschrijving')->nullable()->after('testtype');
                    \Log::info('✅ Beschrijving kolom toegevoegd aan sjablonen tabel');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sjablonen');
    }
};