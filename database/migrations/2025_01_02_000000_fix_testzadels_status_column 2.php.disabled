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
            // Change status column to allow longer values
            $table->string('status', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testzadels', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
    }
};