<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sjabloon_paginas')) {
            Schema::create('sjabloon_paginas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sjabloon_id')->constrained('sjablonen')->onDelete('cascade');
                $table->integer('pagina_nummer')->default(1);
                $table->longText('inhoud')->nullable();
                $table->string('achtergrond_url')->nullable(); // Voor /backgrounds/1.png
                $table->boolean('is_url_pagina')->default(false);
                $table->string('externe_url')->nullable(); // Voor URL paginas
                $table->timestamps();
                
                $table->index(['sjabloon_id', 'pagina_nummer']);
                $table->unique(['sjabloon_id', 'pagina_nummer']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sjabloon_paginas');
    }
};