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
            // Make all problematic fields nullable
            if (Schema::hasColumn('testzadels', 'zadel_model')) {
                $table->string('zadel_model')->nullable()->change();
            }
            if (Schema::hasColumn('testzadels', 'zadel_merk')) {
                $table->string('zadel_merk')->nullable()->change();
            }
            if (Schema::hasColumn('testzadels', 'zadel_type')) {
                $table->string('zadel_type')->nullable()->change();
            }
            if (Schema::hasColumn('testzadels', 'zadel_breedte')) {
                $table->integer('zadel_breedte')->nullable()->change();
            }
            if (Schema::hasColumn('testzadels', 'opmerkingen')) {
                $table->text('opmerkingen')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testzadels', function (Blueprint $table) {
            // Revert changes if needed
            if (Schema::hasColumn('testzadels', 'zadel_model')) {
                $table->string('zadel_model')->nullable(false)->change();
            }
            if (Schema::hasColumn('testzadels', 'zadel_merk')) {
                $table->string('zadel_merk')->nullable(false)->change();
            }
            if (Schema::hasColumn('testzadels', 'zadel_type')) {
                $table->string('zadel_type')->nullable(false)->change();
            }
            if (Schema::hasColumn('testzadels', 'zadel_breedte')) {
                $table->string('zadel_breedte')->nullable(false)->change();
            }
            if (Schema::hasColumn('testzadels', 'opmerkingen')) {
                $table->text('opmerkingen')->nullable(false)->change();
            }
        });
    }
};