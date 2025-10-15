<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check en voeg kolommen toe als ze ontbreken
        if (!Schema::hasColumn('klanten', 'huisnummer')) {
            Schema::table('klanten', function (Blueprint $table) {
                $table->string('huisnummer')->nullable()->after('straatnaam');
            });
        }
        
        if (!Schema::hasColumn('klanten', 'telefoonnummer')) {
            Schema::table('klanten', function (Blueprint $table) {
                $table->string('telefoonnummer')->nullable()->after('email');
            });
        }
    }

    public function down()
    {
        Schema::table('klanten', function (Blueprint $table) {
            if (Schema::hasColumn('klanten', 'huisnummer')) {
                $table->dropColumn('huisnummer');
            }
            if (Schema::hasColumn('klanten', 'telefoonnummer')) {
                $table->dropColumn('telefoonnummer');
            }
        });
    }
};