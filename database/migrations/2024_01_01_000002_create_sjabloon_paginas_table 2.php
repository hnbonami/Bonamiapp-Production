<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie controleert of de sjabloon_paginas tabel al bestaat.
     */
    public function up(): void
    {
        // Controleer of de tabel al bestaat
        if (!Schema::hasTable('sjabloon_paginas')) {
            Schema::create('sjabloon_paginas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sjabloon_id');
                $table->integer('pagina_nummer');
                $table->string('achtergrond_url')->nullable();
                $table->longText('inhoud')->nullable();
                $table->timestamps();
                
                // Foreign key constraint
                $table->foreign('sjabloon_id')->references('id')->on('sjablonen')->onDelete('cascade');
            });
            \Log::info('✅ Sjabloon_paginas tabel aangemaakt');
        } else {
            \Log::info('⚠️ Sjabloon_paginas tabel bestaat al - migratie overgeslagen');
            
            // Controleer en voeg ontbrekende kolommen toe indien nodig
            Schema::table('sjabloon_paginas', function (Blueprint $table) {
                if (!Schema::hasColumn('sjabloon_paginas', 'achtergrond_url')) {
                    $table->string('achtergrond_url')->nullable()->after('pagina_nummer');
                    \Log::info('✅ Achtergrond_url kolom toegevoegd aan sjabloon_paginas tabel');
                }
                if (!Schema::hasColumn('sjabloon_paginas', 'inhoud')) {
                    $table->longText('inhoud')->nullable()->after('achtergrond_url');
                    \Log::info('✅ Inhoud kolom toegevoegd aan sjabloon_paginas tabel');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sjabloon_paginas');
    }
};