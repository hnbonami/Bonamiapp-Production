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
        Schema::table('prestaties', function (Blueprint $table) {
            $table->boolean('is_uitgevoerd')->default(false)->after('opmerkingen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestaties', function (Blueprint $table) {
            $table->dropColumn('is_uitgevoerd');
        });
    }
};
