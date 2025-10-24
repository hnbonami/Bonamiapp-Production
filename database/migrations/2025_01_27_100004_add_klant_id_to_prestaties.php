<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestaties', function (Blueprint $table) {
            $table->foreignId('klant_id')->nullable()->after('dienst_id')->constrained('klanten')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('prestaties', function (Blueprint $table) {
            $table->dropForeign(['klant_id']);
            $table->dropColumn('klant_id');
        });
    }
};
