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
        Schema::table('staff_notes', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('staff_notes', 'visibility')) {
                $table->enum('visibility', ['staff', 'all'])->default('staff')->after('content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};