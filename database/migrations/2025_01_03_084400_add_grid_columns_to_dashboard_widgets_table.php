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
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            // Check of kolommen al bestaan voordat we ze toevoegen
            if (!Schema::hasColumn('dashboard_widgets', 'grid_x')) {
                $table->integer('grid_x')->default(0);
            }
            if (!Schema::hasColumn('dashboard_widgets', 'grid_y')) {
                $table->integer('grid_y')->default(0);
            }
            if (!Schema::hasColumn('dashboard_widgets', 'grid_width')) {
                $table->integer('grid_width')->default(4);
            }
            if (!Schema::hasColumn('dashboard_widgets', 'grid_height')) {
                $table->integer('grid_height')->default(3);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            $table->dropColumn(['grid_x', 'grid_y', 'grid_width', 'grid_height']);
        });
    }
};
