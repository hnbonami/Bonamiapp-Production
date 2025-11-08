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
        // Check of de kolom al bestaat voordat we deze toevoegen
        if (!Schema::hasColumn('email_templates', 'slug')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('email_templates', 'slug')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};