<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // DISABLED - This migration caused conflicts
        // Replaced by 2025_01_01_143002_minimal_uitleensysteem_fields
        \Log::info('Skipping problematic migration 2024_10_01_000003_update_testzadels_table');
        return;
    }

    public function down()
    {
        // DISABLED
        return;
    }
};