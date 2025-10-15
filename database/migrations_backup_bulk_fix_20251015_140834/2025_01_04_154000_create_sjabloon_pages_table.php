<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip als de tabel al bestaat
        if (Schema::hasTable('sjabloon_pages')) {
            return;
        }
        
        Schema::create('sjabloon_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sjabloon_id')->constrained('sjablonen')->onDelete('cascade');
            $table->integer('page_number')->default(1);
            $table->longText('content')->nullable();
            $table->string('url')->nullable();
            $table->string('background_image')->nullable();
            $table->boolean('is_url_page')->default(false);
            $table->timestamps();
            
            $table->index(['sjabloon_id', 'page_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sjabloon_pages');
    }
};