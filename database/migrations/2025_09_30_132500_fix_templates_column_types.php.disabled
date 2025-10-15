<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            // Make sure type column can handle longer strings
            $table->string('type', 100)->change();
            
            // Also ensure other columns are the right type
            $table->string('naam', 255)->nullable()->change();
            $table->json('inhoud')->nullable()->change();
            $table->json('variabelen')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });
    }
};