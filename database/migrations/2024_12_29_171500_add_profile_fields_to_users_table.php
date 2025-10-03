<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Voeg avatar_path toe als die nog niet bestaat
            if (!Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('email_verified_at');
            }
            
            // Voeg profiel velden toe als ze nog niet bestaan
            if (!Schema::hasColumn('users', 'geboortedatum')) {
                $table->date('geboortedatum')->nullable()->after('avatar_path');
            }
            
            if (!Schema::hasColumn('users', 'adres')) {
                $table->string('adres')->nullable()->after('geboortedatum');
            }
            
            if (!Schema::hasColumn('users', 'stad')) {
                $table->string('stad')->nullable()->after('adres');
            }
            
            if (!Schema::hasColumn('users', 'postcode')) {
                $table->string('postcode', 10)->nullable()->after('stad');
            }
            
            if (!Schema::hasColumn('users', 'telefoon')) {
                $table->string('telefoon')->nullable()->after('postcode');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path',
                'geboortedatum', 
                'adres',
                'stad',
                'postcode',
                'telefoon'
            ]);
        });
    }
};