<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sjabloon_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sjabloon_id')->constrained('sjablonen')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->integer('page_number')->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['sjabloon_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sjabloon_pages');
    }
};
