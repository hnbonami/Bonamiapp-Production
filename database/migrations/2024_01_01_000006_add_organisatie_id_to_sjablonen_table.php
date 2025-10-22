<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sjablonen', function (Blueprint $table) {
            $table->unsignedBigInteger('organisatie_id')->nullable()->after('id');
            $table->foreign('organisatie_id')->references('id')->on('organisaties')->onDelete('cascade');
            $table->index('organisatie_id');
        });
    }

    public function down(): void
    {
        Schema::table('sjablonen', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropColumn('organisatie_id');
        });
    }
};
