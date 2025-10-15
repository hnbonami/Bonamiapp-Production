<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Wijzig testtype kolom van ENUM naar VARCHAR voor meer flexibiliteit
        DB::statement("ALTER TABLE inspanningstests MODIFY testtype VARCHAR(50) NOT NULL DEFAULT 'fietstest'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Zet terug naar originele ENUM (alleen als alle data compatible is)
        DB::statement("ALTER TABLE inspanningstests MODIFY testtype ENUM('VO2max','FTP','Lactaat','Ramp','Wingate') NOT NULL DEFAULT 'VO2max'");
    }
};
