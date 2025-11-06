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
        // Check eerst of kolommen al bestaan voordat we ze toevoegen
        Schema::table('organisatie_brandings', function (Blueprint $table) {
            if (!Schema::hasColumn('organisatie_brandings', 'login_background_image')) {
                $table->string('login_background_image')->nullable();
            }
            if (!Schema::hasColumn('organisatie_brandings', 'login_text_color')) {
                $table->string('login_text_color')->default('#374151');
            }
            if (!Schema::hasColumn('organisatie_brandings', 'login_button_color')) {
                $table->string('login_button_color')->default('#c8e1eb');
            }
            if (!Schema::hasColumn('organisatie_brandings', 'login_button_hover_color')) {
                $table->string('login_button_hover_color')->default('#9bb3bd');
            }
            if (!Schema::hasColumn('organisatie_brandings', 'login_link_color')) {
                $table->string('login_link_color')->default('#c8e1eb');
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
                'login_background_image',
                'login_text_color',
                'login_button_color',
                'login_button_hover_color',
                'login_link_color'
            ]);
        });
    }
};
