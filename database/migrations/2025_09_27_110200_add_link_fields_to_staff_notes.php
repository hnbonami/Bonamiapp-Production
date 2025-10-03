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
            $table->string('link_url', 500)->nullable()->after('image_path');
            $table->boolean('open_in_new_tab')->default(false)->after('link_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropColumn(['link_url', 'open_in_new_tab']);
        });
    }
};