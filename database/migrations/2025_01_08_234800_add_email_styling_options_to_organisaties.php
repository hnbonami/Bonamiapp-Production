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
        Schema::table('organisaties', function (Blueprint $table) {
            // Voeg tekstkleur toe voor email headers
            if (!Schema::hasColumn('organisaties', 'email_text_color')) {
                $table->string('email_text_color')->default('#ffffff')->after('secondary_color');
            }
            
            // Voeg logo positie toe (left, center, right)
            if (!Schema::hasColumn('organisaties', 'email_logo_position')) {
                $table->string('email_logo_position')->default('left')->after('logo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisaties', function (Blueprint $table) {
            if (Schema::hasColumn('organisaties', 'email_text_color')) {
                $table->dropColumn('email_text_color');
            }
            
            if (Schema::hasColumn('organisaties', 'email_logo_position')) {
                $table->dropColumn('email_logo_position');
            }
        });
    }
};