<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Voeg login pagina branding kolommen toe (alleen als ze nog niet bestaan)
     */
    public function up(): void
    {
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            // Check welke kolommen al bestaan en voeg alleen ontbrekende toe
            if (!Schema::hasColumn('organisatie_brandings', 'login_logo')) {
                $table->string('login_logo')->nullable()->after('logo_pad');
            }
            
            if (!Schema::hasColumn('organisatie_brandings', 'login_background_image')) {
                $table->string('login_background_image')->nullable()->after('login_logo');
            }
            
            if (!Schema::hasColumn('organisatie_brandings', 'login_background_video')) {
                $table->string('login_background_video')->nullable()->after('login_background_image');
            }
            
            if (!Schema::hasColumn('organisatie_brandings', 'login_text_color')) {
                $table->string('login_text_color', 7)->default('#374151')->after('login_background_video');
            }
            
            if (!Schema::hasColumn('organisatie_brandings', 'login_button_color')) {
                $table->string('login_button_color', 7)->default('#7fb432')->after('login_text_color');
            }
            
            if (!Schema::hasColumn('organisatie_brandings', 'login_button_hover_color')) {
                $table->string('login_button_hover_color', 7)->default('#6a9929')->after('login_button_color');
            }
            
            if (!Schema::hasColumn('organisatie_brandings', 'login_link_color')) {
                $table->string('login_link_color', 7)->default('#374151')->after('login_button_hover_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            $table->dropColumn([
                'login_logo',
                'login_background_image',
                'login_background_video',
                'login_text_color',
                'login_button_color',
                'login_button_hover_color',
                'login_link_color'
            ]);
        });
    }
};