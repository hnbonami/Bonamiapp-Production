<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists to prevent duplicate creation
        if (!Schema::hasTable('sjablonen')) {
            Schema::create('sjablonen', function (Blueprint $table) {
                $table->id();
                $table->string('naam');
                $table->enum('categorie', ['bikefit', 'inspanningstest']);
                $table->string('testtype')->nullable(); // Voor koppeling aan bikefit/inspanningstest testtype veld
                $table->text('beschrijving')->nullable();
                $table->boolean('is_actief')->default(true);
                $table->timestamps();
                
                $table->index(['categorie', 'testtype']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sjablonen');
    }
};