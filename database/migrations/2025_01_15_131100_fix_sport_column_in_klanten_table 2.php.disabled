<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Wijzig sport kolom naar varchar zodat alle waarden kunnen worden opgeslagen
        Schema::table('klanten', function (Blueprint $table) {
            $table->string('sport')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('klanten', function (Blueprint $table) {
            $table->enum('sport', ['fietsen', 'lopen', 'triathlon'])->nullable()->change();
        });
    }
};