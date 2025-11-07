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
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            // Voeg login_background_video kolom toe na login_background_image
            $table->string('login_background_video', 500)->nullable()->after('login_background_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            $table->dropColumn('login_background_video');
        });
    }
};