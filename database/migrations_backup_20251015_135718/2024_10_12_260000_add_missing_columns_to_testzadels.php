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
            // Voeg ontbrekende kolommen toe als ze nog niet bestaan
            if (!Schema::hasColumn('testzadels', 'uitgeleend_op')) {
                $table->datetime('uitgeleend_op')->nullable();
            }
            if (!Schema::hasColumn('testzadels', 'teruggegeven_op')) {
                $table->datetime('teruggegeven_op')->nullable();
            }
            if (!Schema::hasColumn('testzadels', 'type')) {
                $table->string('type')->nullable();
            }
            if (!Schema::hasColumn('testzadels', 'breedte_mm')) {
                $table->integer('breedte_mm')->nullable();
            }
            if (!Schema::hasColumn('testzadels', 'automatisch_herinneringsmails_versturen')) {
                $table->boolean('automatisch_herinneringsmails_versturen')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testzadels', function (Blueprint $table) {
            $table->dropColumn([
                'uitgeleend_op',
                'teruggegeven_op', 
                'type',
                'breedte_mm',
                'automatisch_herinneringsmails_versturen'
            ]);
        });
    }
};