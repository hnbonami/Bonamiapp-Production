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
        Schema::create('bikefit_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bikefit_id');
            $table->string('image_path');
            $table->string('image_type')->nullable(); // voor, na, mobiliteit, etc.
            $table->integer('position')->default(0);
            $table->text('caption')->nullable();
            $table->timestamps();
            
            $table->foreign('bikefit_id')
                  ->references('id')
                  ->on('bikefits')
                  ->onDelete('cascade');
            
            $table->index('bikefit_id');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bikefit_images');
    }
};