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
        Schema::table('organisatie_branding', function (Blueprint $table) {
            // Login pagina personalisatie - voeg toe aan einde van tabel
            $table->string('login_background_image')->nullable();
            $table->string('login_text_color')->default('#374151'); // Default grijs
            $table->string('login_button_color')->default('#c8e1eb'); // Default blauw
            $table->string('login_button_hover_color')->default('#9bb3bd'); // Default donkerder blauw
            $table->string('login_link_color')->default('#c8e1eb'); // Default blauw
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisatie_branding', function (Blueprint $table) {
            $table->dropColumn([
                'login_background_image',
                'login_text_color',
                'login_button_color',
                'login_button_hover_color',
                'login_link_color'
            ]);
        });
    }
};
