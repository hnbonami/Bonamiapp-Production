<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefoon', 20)->nullable()->after('email');
            $table->date('geboortedatum')->nullable()->after('telefoon');
            $table->string('adres')->nullable()->after('geboortedatum');
            $table->string('stad', 100)->nullable()->after('adres');
            $table->string('postcode', 10)->nullable()->after('stad');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefoon', 'geboortedatum', 'adres', 'stad', 'postcode']);
        });
    }
};