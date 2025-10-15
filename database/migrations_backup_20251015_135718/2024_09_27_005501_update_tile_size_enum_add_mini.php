<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum using raw SQL since Laravel has issues with enum modifications
        DB::statement("ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE staff_notes MODIFY COLUMN tile_size ENUM('small', 'medium', 'large', 'banner') DEFAULT 'medium'");
    }
};