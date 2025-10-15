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
            // Voeg open_in_new_tab kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('staff_notes', 'open_in_new_tab')) {
                $table->boolean('open_in_new_tab')->default(false)->after('link_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            if (Schema::hasColumn('staff_notes', 'open_in_new_tab')) {
                $table->dropColumn('open_in_new_tab');
            }
        });
    }
};