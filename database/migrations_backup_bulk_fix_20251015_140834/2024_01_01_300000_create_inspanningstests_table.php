<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inspanningstests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klant_id')->constrained('klanten')->onDelete('cascade');
            $table->date('datum');
            $table->enum('testtype', ['VO2max', 'FTP', 'Lactaat', 'Ramp', 'Wingate'])->default('VO2max');
            $table->integer('max_wattage')->nullable();
            $table->integer('max_heartrate')->nullable();
            $table->decimal('vo2_max', 5, 2)->nullable();
            $table->integer('ftp')->nullable();
            $table->json('data_punten')->nullable();
            $table->text('conclusies')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inspanningstests');
    }
};