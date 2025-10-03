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
        Schema::table('personal_data', function (Blueprint $table) {
            $table->string('adres')->nullable()->after('telefoon');
            $table->string('stad')->nullable()->after('adres');
            $table->string('postcode', 10)->nullable()->after('stad');
            $table->string('land')->nullable()->after('postcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_data', function (Blueprint $table) {
            $table->dropColumn(['adres', 'stad', 'postcode', 'land']);
        });
    }
};