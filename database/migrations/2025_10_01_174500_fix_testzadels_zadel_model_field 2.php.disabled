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
        Schema::table('testzadels', function (Blueprint $table) {
            // Make zadel_model nullable or add default value
            $table->string('zadel_model')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testzadels', function (Blueprint $table) {
            $table->string('zadel_model')->nullable(false)->change();
        });
    }
};