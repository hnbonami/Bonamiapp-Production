<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Wijzig niveau kolom naar varchar zodat alle waarden kunnen worden opgeslagen
        Schema::table('klanten', function (Blueprint $table) {
            $table->string('niveau')->nullable()->change();
        });
    }

    public function down()
    {
        // Revert back to original
        Schema::table('klanten', function (Blueprint $table) {
            $table->enum('niveau', ['beginner', 'gevorderd', 'expert'])->nullable()->change();
        });
    }
};